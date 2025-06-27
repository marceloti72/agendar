<?php
require_once("../sistema/conexao.php");

//header('Content-Type: application/json'); // Define tipo de resposta como JSON
$response = ['success' => false, 'message' => 'Erro desconhecido ao excluir agendamento.'];

// --- Validações Iniciais (Sessão, Método POST) ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido.';
    echo json_encode($response); exit;
}
if (!isset($_SESSION['id_conta'])) {
    $response['message'] = 'Sessão inválida ou expirada.';
    echo json_encode($response); exit;
}
$id_conta = $_SESSION['id_conta'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    $response['message'] = 'ID do agendamento inválido.';
    echo json_encode($response); exit;
}

// --- Inicia Transação ---
$pdo->beginTransaction();

try {
    // 1. Busca informações do agendamento
    $query = $pdo->prepare("SELECT cliente, funcionario, data, hora, servico, hash, comanda_id FROM agendamentos WHERE id = :id AND id_conta = :id_conta");
    $query->execute([':id' => $id, ':id_conta' => $id_conta]);
    $res = $query->fetchAll(PDO::FETCH_ASSOC);

    if (count($res) == 0) {
        throw new Exception("Agendamento ID {$id} não encontrado.");
    }

    $cliente = $res[0]['cliente'];
    $usuario = $res[0]['funcionario'];
    $data = $res[0]['data'];
    $hora = $res[0]['hora'];
    $servico = $res[0]['servico'];
    $hash = $res[0]['hash'];
    $comanda = $res[0]['comanda_id'];

    $dataF = implode('/', array_reverse(explode('-', $data)));
    $horaF = date("H:i", strtotime($hora));

    // 2. Busca informações do cliente
    $query = $pdo->prepare("SELECT nome, telefone FROM clientes WHERE id = :cliente AND id_conta = :id_conta");
    $query->execute([':cliente' => $cliente, ':id_conta' => $id_conta]);
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    if (count($res) == 0) {
        throw new Exception("Cliente ID {$cliente} não encontrado.");
    }
    $nome_cliente = $res[0]['nome'];
    $telefone = $res[0]['telefone'];

    // 3. Verifica e deleta registro de uso de serviço para assinantes
    $query_receber = $pdo->prepare("SELECT cliente, valor2, tipo, servico FROM receber WHERE comanda = :comanda AND id_conta = :id_conta");
    $query_receber->execute([':comanda' => $comanda, ':id_conta' => $id_conta]);
    $item = $query_receber->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $id_assinante = $item['cliente'];
        $valor2 = $item['valor2'];
        $tipo = $item['tipo'];
        $id_servico = $item['servico'];

        if ($id_assinante !== null && $id_assinante > 0 && $valor2 == 0 && $tipo === 'Serviço') {
            $query_del_uso = $pdo->prepare("DELETE FROM assinantes_servicos_usados WHERE id_assinante = :id_assinante AND id_servico = :id_servico AND id_conta = :id_conta ORDER BY id DESC LIMIT 1");
            $query_del_uso->bindValue(':id_assinante', $id_assinante, PDO::PARAM_INT);
            $query_del_uso->bindValue(':id_servico', $id_servico, PDO::PARAM_INT);
            $query_del_uso->bindValue(':id_conta', $id_conta, PDO::PARAM_INT);
            $query_del_uso->execute();
            if ($query_del_uso->rowCount() <= 0) {
                error_log("Aviso: Nenhum registro encontrado em 'assinantes_servicos_usados' para id_assinante {$id_assinante}, id_servico {$id_servico} ao excluir comanda ID {$comanda}.");
            }
        }
    } else {
        error_log("Aviso: Nenhum registro encontrado em 'receber' para comanda ID {$comanda}.");
    }

    // 4. Exclui registros relacionados
    $query = $pdo->prepare("DELETE FROM agendamentos WHERE id = :id AND id_conta = :id_conta");
    $query->execute([':id' => $id, ':id_conta' => $id_conta]);
    if ($query->rowCount() <= 0) {
        throw new Exception("Falha ao excluir agendamento ID {$id}.");
    }

    $query = $pdo->prepare("DELETE FROM horarios_agd WHERE agendamento = :id AND id_conta = :id_conta");
    $query->execute([':id' => $id, ':id_conta' => $id_conta]);
    if ($query->rowCount() <= 0) {
        error_log("Aviso: Nenhum horário adicional excluído para agendamento ID {$id}.");
    }

    $query = $pdo->prepare("DELETE FROM comandas WHERE id = :comanda AND id_conta = :id_conta");
    $query->execute([':comanda' => $comanda, ':id_conta' => $id_conta]);
    if ($query->rowCount() <= 0) {
        error_log("Aviso: Nenhuma comanda excluída para ID {$comanda}.");
    }

    $query = $pdo->prepare("DELETE FROM pagar WHERE comanda = :comanda AND id_conta = :id_conta");
    $query->execute([':comanda' => $comanda, ':id_conta' => $id_conta]);
    if ($query->rowCount() <= 0) {
        error_log("Aviso: Nenhuma comissão excluída para comanda ID {$comanda}.");
    }

    $query = $pdo->prepare("DELETE FROM receber WHERE comanda = :comanda AND id_conta = :id_conta");
    $query->execute([':comanda' => $comanda, ':id_conta' => $id_conta]);
    if ($query->rowCount() <= 0) {
        error_log("Aviso: Nenhum registro excluído da tabela 'receber' para comanda ID {$comanda}.");
    }

    // 5. Notificação de cancelamento (se aplicável)
    if ($msg_agendamento == 'Sim') {
        $query = $pdo->prepare("SELECT nome, telefone FROM usuarios WHERE id = :usuario AND id_conta = :id_conta");
        $query->execute([':usuario' => $usuario, ':id_conta' => $id_conta]);
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $nome_func = $res[0]['nome'] ?? 'Sem Nome';
        $tel_func = $res[0]['telefone'] ?? '';

        $query = $pdo->prepare("SELECT nome FROM servicos WHERE id = :servico AND id_conta = :id_conta");
        $query->execute([':servico' => $servico, ':id_conta' => $id_conta]);
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $nome_serv = $res[0]['nome'] ?? 'Não Lançado';

        $nome_sistema_maiusculo = mb_strtoupper($nome_sistema);

        $mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
        $mensagem .= '_*Agendamento Cancelado*_ 🚨%0A';
        $mensagem .= 'Profissional: *' . $nome_func . '* %0A';
        $mensagem .= 'Serviço: *' . $nome_serv . '* %0A';
        $mensagem .= 'Data: *' . $dataF . '* %0A';
        $mensagem .= 'Hora: *' . $horaF . '* %0A';
        $mensagem .= 'Cliente: *' . $nome_cliente . '* %0A';

        $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefone);
        require('api-texto.php');

        if ($tel_func != $whatsapp_sistema) {
            $telefone = '55' . preg_replace('/[ ()-]+/', '', $tel_func);
            require('api-texto.php');
        }

        // ENVIO PARA EMPRESA
        $telefone = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);
        require('api-texto.php');

        if ($hash != "") {
            require('agendar-delete.php');
        }
    }

    // 6. Notificação de encaixe (se aplicável)
    if ($encaixe == 'Sim') {
        $link = $url . 'agendamentos?u=' . $username;

        $query = $pdo->prepare("SELECT nome, whatsapp FROM encaixe WHERE data = :data AND profissional = :profissional AND id_conta = :id_conta");
        $query->execute([':data' => $data, ':profissional' => $usuario, ':id_conta' => $id_conta]);
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $num_clientes = count($res);

        if ($num_clientes > 0) {
            $clientes_info = '';
            foreach ($res as $cliente_encaixe) {
                $mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
                $mensagem .= '*_Horário disponível_* 📆%0A%0A';
                $mensagem .= 'Olá ' . $cliente_encaixe['nome'] . ', uma vaga foi liberada. Corra para agendar! 😃%0A%0A';
                $mensagem .= 'Profissional: *' . $nome_func . '* %0A';
                $mensagem .= 'Data: *' . $dataF . '* %0A';
                $mensagem .= 'Hora: *' . $horaF . '* %0A';
                $mensagem .= 'Link de agendamento: %0A';
                $mensagem .= $link . ' %0A';

                $telefone = '55' . preg_replace('/[ ()-]+/', '', $cliente_encaixe['whatsapp']);
                require('api-texto.php');

                $clientes_info .= '✅ ' . htmlspecialchars($cliente_encaixe['nome']) . ', ' . htmlspecialchars($cliente_encaixe['whatsapp']) . '%0A';
            }

            $mens = $num_clientes . ' cliente' . ($num_clientes > 1 ? 's' : '') . ' que estava' . ($num_clientes > 1 ? 'm' : '') . ' aguardando encaixe para essa data e profissional fora' . ($num_clientes > 1 ? 'm' : '') . ' alertado' . ($num_clientes > 1 ? 's' : '') . '.%0A%0A' . $clientes_info;

            $mensagem = '*_Alerta de Encaixe_* 🚨%0A%0A';
            $mensagem .= 'Profissional: *' . $nome_func . '* %0A';
            $mensagem .= 'Data: *' . $dataF . '* %0A';
            $mensagem .= 'Hora: *' . $horaF . '* %0A%0A';
            $mensagem .= $mens . ' %0A';

            $telefone = '55' . preg_replace('/[ ()-]+/', '', $tel_func);
            require('api-texto.php');

            $telefone = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);
            require('api-texto.php');
        }
    }

    // 7. Confirma transação
    $pdo->commit();
    //$response['success'] = true;
    //$response['message'] = 'Cancelado com Sucesso';

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro de Banco de Dados ao excluir: ' . $e->getMessage();
    error_log("Erro PDO em excluir_agendamento.php: " . $e->getMessage() . " | ID Agendamento: " . $id);
    echo json_encode($response); exit;
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral em excluir_agendamento.php: " . $e->getMessage() . " | ID Agendamento: " . $id);
    echo json_encode($response); exit;
}

// --- Envia Resposta JSON Final ---
//echo json_encode($response);
echo 'Cancelado com Sucesso';
?>