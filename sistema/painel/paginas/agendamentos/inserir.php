<?php
header('Content-Type: application/json');
$tabela = 'agendamentos';
require_once("../../../conexao.php");

@session_start();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Garante que erros do PDO sejam lançados

$id_conta = $_SESSION['id_conta'];
$usuario_logado = $_SESSION['id_usuario'];

$cliente = $_POST['cliente'];
$data = $_POST['data'];
$hora = @$_POST['hora'];
$obs = $_POST['obs'];
$id = $_POST['id'];
$funcionario = $_POST['funcionario'];
$servico = $_POST['servico'];
$data_agd = $_POST['data'];
$hora_do_agd = @$_POST['hora'];
$hash = '';
$quantidade_a_usar = 1;
$response = [
    'success' => false,
    'message' => 'Erro desconhecido ao processar solicitação.',
    'valor_cobrado' => null,
    'tipo_registro' => 'Erro',
    'detalhe_assinatura' => null,
    'nova_comanda_id' => null
];

// Log de entrada
error_log("Dados de entrada: cliente=$cliente, funcionario=$funcionario, servico=$servico, data=$data, hora=$hora, id_conta=$id_conta");

// Validações iniciais
if (@$hora == "") {
    $response['message'] = 'Selecione um Horário antes de agendar!';
    error_log($response['message']);
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método de requisição inválido.';
    error_log($response['message']);
    echo json_encode($response);
    exit();
}

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_conta'])) {
    $response['message'] = 'Sessão inválida ou expirada. Faça login novamente.';
    error_log($response['message']);
    echo json_encode($response);
    exit();
}

$cliente_id = filter_var($cliente, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$funcionario_id = filter_var($funcionario, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$servico_id = filter_var($servico, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;

if (!$cliente_id || !$funcionario_id || !$servico_id) {
    $response['message'] = 'Dados inválidos: Cliente, Funcionário ou Serviço não informado corretamente.';
    error_log($response['message']);
    echo json_encode($response);
    exit();
}

// Busca informações do funcionário
$query = $pdo->query("SELECT * FROM usuarios WHERE id = '$funcionario' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$intervalo = $res[0]['intervalo'];
$tel_func = $res[0]['telefone'];

// Busca informações do serviço
$query = $pdo->query("SELECT nome, valor, comissao, tempo FROM servicos WHERE id = '$servico' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$tempo = $res[0]['tempo'];
$nome_servico = $res[0]['nome'];
$valor_servico_original = $res[0]['valor'];
$comissao_servico = $res[0]['comissao'];

$hora_minutos = strtotime("+$tempo minutes", strtotime($hora));
$hora_final_servico = date('H:i:s', $hora_minutos);
$nova_hora = $hora;

$diasemana = ["Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado"];
$diasemana_numero = date('w', strtotime($data));
$dia_procurado = $diasemana[$diasemana_numero];

// Verifica se o funcionário trabalha no dia
$query = $pdo->query("SELECT * FROM dias WHERE funcionario = '$funcionario' AND dia = '$dia_procurado' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if (@count($res) == 0) {
    $response['message'] = 'Este Funcionário não trabalha neste Dia!';
    error_log($response['message']);
    echo json_encode($response);
    exit();
} else {
    $inicio = $res[0]['inicio'];
    $final = $res[0]['final'];
    $inicio_almoco = $res[0]['inicio_almoco'];
    $final_almoco = $res[0]['final_almoco'];
}

$dataF = implode('/', array_reverse(explode('-', $data)));
$horaF = date("H:i", strtotime($hora));

// Verifica disponibilidade de horários
while (strtotime($nova_hora) < strtotime($hora_final_servico)) {
    $hora_minutos = strtotime("+$intervalo minutes", strtotime($nova_hora));
    $nova_hora = date('H:i:s', $hora_minutos);

    $query_agd = $pdo->query("SELECT * FROM horarios_agd WHERE data = '$data' AND funcionario = '$funcionario' AND horario = '$nova_hora' AND id_conta = '$id_conta'");
    $res_agd = $query_agd->fetchAll(PDO::FETCH_ASSOC);
    if (@count($res_agd) > 0) {
        $response['message'] = "Este serviço demora cerca de $tempo minutos, precisa escolher outro horário, pois neste horário não temos disponibilidade devido a outros agendamentos!";
        error_log($response['message']);
        echo json_encode($response);
        exit();
    }

    $query_agd = $pdo->query("SELECT * FROM agendamentos WHERE data = '$data' AND funcionario = '$funcionario' AND hora = '$nova_hora' AND id_conta = '$id_conta'");
    $res_agd = $query_agd->fetchAll(PDO::FETCH_ASSOC);
    if (@count($res_agd) > 0) {
        if ($tempo <= $intervalo) {
            // Ok, intervalo suficiente
        } else {
            if ($hora_final_servico == $res_agd[0]['hora']) {
                // Ok, coincide com o final
            } else {
                $response['message'] = "Este serviço demora cerca de $tempo minutos, precisa escolher outro horário, pois neste horário não temos disponibilidade devido a outros agendamentos!";
                error_log($response['message']);
                echo json_encode($response);
                exit();
            }
        }
    }

    if (strtotime($nova_hora) > strtotime($inicio_almoco) && strtotime($nova_hora) < strtotime($final_almoco)) {
        $response['message'] = "Este serviço demora cerca de $tempo minutos, precisa escolher outro horário, pois neste horário não temos disponibilidade devido ao horário de almoço!";
        error_log($response['message']);
        echo json_encode($response);
        exit();
    }
}

// Valida horário
$query = $pdo->query("SELECT * FROM $tabela WHERE data = '$data' AND hora = '$hora' AND funcionario = '$funcionario' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0 && $res[0]['id'] != $id) {
    $response['message'] = 'Este horário não está disponível!';
    error_log($response['message']);
    echo json_encode($response);
    exit();
}

// Verificação de Assinatura
$coberto_pela_assinatura = false;
$mensagem_assinatura = '';
$id_assinante_encontrado = null;
$id_receber_ciclo_atual = null;
$id_plano_servico_encontrado = null;

$query_find_ass = $pdo->prepare("SELECT id, id_plano FROM assinantes WHERE id_cliente = :id_cliente AND id_conta = :id_conta AND ativo = 1 AND data_vencimento >= CURDATE()");
$query_find_ass->execute([':id_cliente' => $cliente_id, ':id_conta' => $id_conta]);
$assinante_info = $query_find_ass->fetch(PDO::FETCH_ASSOC);

if ($assinante_info) {
    $id_assinante_encontrado = $assinante_info['id'];
    $id_plano_assinante = $assinante_info['id_plano'];

    $query_limite = $pdo->prepare("SELECT id, quantidade FROM planos_servicos WHERE id_plano = :id_plano AND id_servico = :id_servico AND id_conta = :id_conta");
    $query_limite->execute([':id_plano' => $id_plano_assinante, ':id_servico' => $servico_id, ':id_conta' => $id_conta]);
    $limite_info = $query_limite->fetch();

    if ($limite_info) {
        $limite_base = (int)$limite_info['quantidade'];
        $id_plano_servico_encontrado = $limite_info['id'];

        $query_rec = $pdo->prepare("SELECT id, frequencia FROM receber WHERE cliente = :id_ass AND id_conta = :id_conta AND pago = 'Não' AND tipo = 'Assinatura' ORDER BY data_venc ASC, id ASC LIMIT 1");
        $query_rec->execute([':id_ass' => $id_assinante_encontrado, ':id_conta' => $id_conta]);
        $rec_atual = $query_rec->fetch();

        if ($rec_atual) {
            $id_receber_ciclo_atual = $rec_atual['id'];
            $frequencia_ciclo = (int)$rec_atual['frequencia'];

            $limite_ciclo = $limite_base;
            if ($frequencia_ciclo == 365 && $limite_base > 0) {
                $limite_ciclo = $limite_base * 12;
            } elseif ($limite_base == 0) {
                $limite_ciclo = 0;
            }

            $usados_atualmente = 0;
            if ($limite_ciclo !== 0) {
                $query_uso = $pdo->prepare("SELECT SUM(quantidade_usada) as total_usado FROM assinantes_servicos_usados WHERE id_assinante = :id_ass AND id_servico = :id_serv AND id_receber_associado = :id_rec AND id_conta = :id_conta");
                $query_uso->execute([':id_ass' => $id_assinante_encontrado, ':id_serv' => $servico_id, ':id_rec' => $id_receber_ciclo_atual, ':id_conta' => $id_conta]);
                $uso_info = $query_uso->fetch();
                $usados_atualmente = $uso_info ? (int)$uso_info['total_usado'] : 0;
            }

            if ($limite_ciclo === 0 || ($usados_atualmente + $quantidade_a_usar) <= $limite_ciclo) {
                $coberto_pela_assinatura = true;
                $novo_uso_num = $usados_atualmente + $quantidade_a_usar;
                $limite_texto = ($limite_ciclo === 0) ? "Ilimitado" : $limite_ciclo;
                $mensagem_assinatura = " Cliente Assinante (Uso Serviço: {$novo_uso_num} / {$limite_texto})";
            } else {
                $mensagem_assinatura = " (Assinante: Limite Atingido {$usados_atualmente} / {$limite_ciclo})";
            }
        } else {
            $mensagem_assinatura = " (Assinante: Ciclo não localizado)";
        }
    }
}

$nome_sistema_maiusculo = mb_strtoupper($nome_sistema);

// Busca nome do cliente
$query = $pdo->query("SELECT nome, telefone FROM clientes WHERE id = '$cliente' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$nome_cliente = $res[0]['nome'];
$telefone = $res[0]['telefone'];

// Notificações via API (comentar temporariamente para teste)
if ($api == 'Sim') {
    $mensagem_not = $nome_cliente;
    $titulo_not = 'Novo Agendamento ' . $dataF . ' - ' . $horaF;
    $id_usu = $funcionario;
    // require('../../../../api/notid.php');

    $telefone = '55' . preg_replace('/[ ()-]+/', '', $tel_func);
    $mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
    $mensagem .= '*Confirmação de Agendamento* 📆%0A';
    $mensagem .= 'Cliente: ' . $nome_cliente . '%0A';
    $mensagem .= 'Data: ' . $dataF . '%0A';
    $mensagem .= 'Hora: ' . $horaF . '%0A';
    $mensagem .= 'Serviço: ' . $nome_servico . '%0A';

     require('../../../../ajax/api-texto.php');

    if ($msg_agendamento == 'Sim') {
        $hora_atual = date('H:i:s');
        $data_atual = date('Y-m-d');
        $hora_minutos = strtotime("-$minutos_aviso minutes", strtotime($hora));
        $nova_hora = date('H:i:s', $hora_minutos);
        $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefone);
    }
}

// Inicia transação
$pdo->beginTransaction();

try {
    // Insere agendamento
    $query = $pdo->prepare("INSERT INTO $tabela SET funcionario = :funcionario, cliente = :cliente, hora = :hora, data = :data_agd, usuario = :usuario, status = 'Agendado', obs = :obs, data_lanc = CURDATE(), servico = :servico, origem = 'Site', hash = :hash, id_conta = :id_conta");
    $query->bindValue(":funcionario", $funcionario_id, PDO::PARAM_INT);
    $query->bindValue(":cliente", $cliente_id, PDO::PARAM_INT);
    $query->bindValue(":hora", $hora);
    $query->bindValue(":data_agd", $data_agd);
    $query->bindValue(":usuario", $usuario_logado, PDO::PARAM_INT);
    $query->bindValue(":obs", $obs);
    $query->bindValue(":servico", $servico_id, PDO::PARAM_INT);
    $query->bindValue(":hash", $hash);
    $query->bindValue(":id_conta", $id_conta, PDO::PARAM_INT);
    $query->execute();
    error_log("Inserção em agendamentos: Linhas afetadas: " . $query->rowCount());
    $ult_id = $pdo->lastInsertId();

    // Insere comanda
    $query2 = $pdo->prepare("INSERT INTO comandas SET cliente = :cliente, valor = :valor, data = :data, hora = :hora, funcionario = :funcionario, status = 'Aberta', obs = :obs, pago = 'Não', id_conta = :id_conta");
    $query2->bindValue(":cliente", $cliente_id, PDO::PARAM_INT);
    $query2->bindValue(":valor", $coberto_pela_assinatura ? 0.00 : $valor_servico_original);
    $query2->bindValue(":obs", "Comanda criada para agendamento ID $ult_id");
    $query2->bindValue(":hora", $hora);
    $query2->bindValue(":id_conta", $id_conta, PDO::PARAM_INT);
    $query2->bindValue(":funcionario", $usuario_logado, PDO::PARAM_INT);
    $query2->bindValue(":data", $data_agd);
    $query2->execute();
    error_log("Inserção em comandas: Linhas afetadas: " . $query2->rowCount());
    $id_comanda = $pdo->lastInsertId();

    // Atualiza agendamento com ID da comanda
    $query3 = $pdo->prepare("UPDATE agendamentos SET comanda_id = :comanda_id WHERE id = :id AND id_conta = :id_conta");
    $query3->bindValue(":comanda_id", $id_comanda, PDO::PARAM_INT);
    $query3->bindValue(":id", $ult_id, PDO::PARAM_INT);
    $query3->bindValue(":id_conta", $id_conta, PDO::PARAM_INT);
    $query3->execute();
    error_log("Atualização em agendamentos: Linhas afetadas: " . $query3->rowCount());

    // Calcula comissão
    $query_f = $pdo->prepare("SELECT comissao FROM usuarios WHERE id = :id_funcionario AND id_conta = :id_conta");
    $query_f->execute([':id_funcionario' => $funcionario_id, ':id_conta' => $id_conta]);
    $func_info = $query_f->fetch(PDO::FETCH_ASSOC);
    $comissao_taxa = $comissao_servico;
    if ($func_info && isset($func_info['comissao']) && $func_info['comissao'] > 0) {
        $comissao_taxa = $func_info['comissao'];
    }
    global $tipo_comissao;
    if (!isset($tipo_comissao)) {
        $tipo_comissao = $tipo_comissao_global ?? 'Porcentagem';
    }
    $valor_comissao = ($tipo_comissao == 'Porcentagem') ? (($comissao_taxa * $valor_servico_original) / 100) : $comissao_taxa;
    $descricao_pagar = 'Comissão - ' . $nome_servico;

    // Insere na tabela receber
    $valor_final_receber = $coberto_pela_assinatura ? 0.00 : $valor_servico_original;
    $descricao_final_receber = $coberto_pela_assinatura ? $nome_servico . " (Assinatura)" : $nome_servico;
    $pago_receber = 'Não';
    $tipo_receber = 'Serviço';

    $query_receber = $pdo->prepare("INSERT INTO receber SET descricao = :desc, tipo = :tipo, valor = :val, data_lanc = CURDATE(), data_venc = CURDATE(), usuario_lanc = :user_lanc, foto = 'sem-foto.jpg', cliente = :cli, pessoa = :pessoa, pago = :pago, referencia: referencia, id_agenda = :id_agenda, servico = :serv, funcionario = :func, func_comanda = :user_comanda, comanda = :comanda_id, id_conta = :id_conta, valor2 = :val");
    $query_receber->bindValue(':desc', $descricao_final_receber);
    $query_receber->bindValue(':tipo', $tipo_receber);
    $query_receber->bindValue(':val', $valor_final_receber);
    $query_receber->bindValue(':user_lanc', $usuario_logado, PDO::PARAM_INT);
    $query_receber->bindValue(':cli', $id_assinante_encontrado, PDO::PARAM_INT);
    $query_receber->bindValue(':pessoa', $cliente_id, PDO::PARAM_INT);
    $query_receber->bindValue(':pago', $pago_receber);
    $query_receber->bindValue(':referencia', $ult_id);
    $query_receber->bindValue(':id_agenda', $ult_id);
    $query_receber->bindValue(':serv', $servico_id, PDO::PARAM_INT);
    $query_receber->bindValue(':func', $funcionario_id, PDO::PARAM_INT);
    $query_receber->bindValue(':user_comanda', $usuario_logado, PDO::PARAM_INT);
    $query_receber->bindValue(':comanda_id', $id_comanda, PDO::PARAM_INT);
    $query_receber->bindValue(':id_conta', $id_conta, PDO::PARAM_INT);
    $query_receber->execute();
    error_log("Inserção em receber: Linhas afetadas: " . $query_receber->rowCount());
    $ult_id_receber = $pdo->lastInsertId();

    // Registra uso para assinantes
    if ($coberto_pela_assinatura) {
        if (empty($id_assinante_encontrado) || empty($id_receber_ciclo_atual)) {
            throw new Exception("Erro interno: IDs faltando para registrar uso coberto.");
        }
        $query_insert_uso = $pdo->prepare("INSERT INTO assinantes_servicos_usados (id_assinante, id_servico, id_plano_servico, id_receber_associado, quantidade_usada, data_uso, id_usuario_registro, id_conta, observacao) VALUES (:id_ass, :id_serv, :id_ps, :id_rec, :qtd, NOW(), :id_user, :id_conta, :obs)");
        $query_insert_uso->bindValue(':id_ass', $id_assinante_encontrado, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_serv', $servico_id, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_ps', $id_plano_servico_encontrado, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_rec', $id_receber_ciclo_atual, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':qtd', $quantidade_a_usar, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_user', $usuario_logado, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_conta', $id_conta, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':obs', "Comanda ID:$id_comanda (Coberto)", PDO::PARAM_STR);
        $query_insert_uso->execute();
        error_log("Inserção em assinantes_servicos_usados: Linhas afetadas: " . $query_insert_uso->rowCount());
    }

    // Insere comissão na tabela pagar
    $query_pagar = $pdo->prepare("INSERT INTO pagar SET descricao = :desc, tipo = 'Comissão', valor = :val, data_lanc = CURDATE(), data_venc = CURDATE(), usuario_lanc = :user_lanc, foto = 'sem-foto.jpg', pago = 'Não', funcionario = :func, servico = :serv, cliente = :cli, id_ref = :id_ref, id_conta = :id_conta, comanda = :comanda");
    $query_pagar->bindValue(':desc', $descricao_pagar);
    $query_pagar->bindValue(':val', $valor_comissao);
    $query_pagar->bindValue(':user_lanc', $usuario_logado, PDO::PARAM_INT);
    $query_pagar->bindValue(':func', $funcionario_id, PDO::PARAM_INT);
    $query_pagar->bindValue(':serv', $servico_id, PDO::PARAM_INT);
    $query_pagar->bindValue(':cli', $cliente_id, PDO::PARAM_INT);
    $query_pagar->bindValue(':id_ref', $ult_id_receber, PDO::PARAM_INT);
    $query_pagar->bindValue(':id_conta', $id_conta, PDO::PARAM_INT);
    $query_pagar->bindValue(':comanda', $id_comanda, PDO::PARAM_INT);
    $query_pagar->execute();
    error_log("Inserção em pagar: Linhas afetadas: " . $query_pagar->rowCount());

    // Insere horários adicionais
    $hora = $hora_do_agd;
    while (strtotime($hora) < strtotime($hora_final_servico)) {
        $hora_minutos = strtotime("+$intervalo minutes", strtotime($hora));
        $hora = date('H:i:s', $hora_minutos);
        if (strtotime($hora) < strtotime($hora_final_servico)) {
            $query = $pdo->prepare("INSERT INTO horarios_agd SET agendamento = :agendamento, horario = :horario, funcionario = :funcionario, data = :data, id_conta = :id_conta");
            $query->bindValue(':agendamento', $ult_id, PDO::PARAM_INT);
            $query->bindValue(':horario', $hora);
            $query->bindValue(':funcionario', $funcionario_id, PDO::PARAM_INT);
            $query->bindValue(':data', $data_agd);
            $query->bindValue(':id_conta', $id_conta, PDO::PARAM_INT);
            $query->execute();
            error_log("Inserção em horarios_agd: Linhas afetadas: " . $query->rowCount());
        }
    }

    // Confirmação de agendamento via API (comentar temporariamente para teste)
    if ($api == 'Sim' && $msg_agendamento == 'Sim') {
        if (strtotime($hora_atual) < strtotime($nova_hora) || strtotime($data_atual) != strtotime($data_agd)) {
            $mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
            $mensagem .= '*Confirmação de Agendamento* 📆%0A';
            $mensagem .= 'Data: ' . $dataF . '%0A';
            $mensagem .= 'Hora: ' . $horaF . '%0A';
            $mensagem .= 'Serviço: ' . $nome_servico . '%0A%0A';
            $mensagem .= '_(1 para *CONFIRMAR*, 2 para *CANCELAR*)_';
            $id_envio = $ult_id;
            $data_envio = $data_agd . ' ' . $nova_hora;

            if ($minutos_aviso > 0) {
                require("../../../../ajax/confirmacao.php");
                $id_hash = $id;
                $query = $pdo->prepare("UPDATE agendamentos SET hash = :hash WHERE id = :id AND id_conta = :id_conta");
                $query->bindValue(':hash', $id_hash);
                $query->bindValue(':id', $ult_id, PDO::PARAM_INT);
                $query->bindValue(':id_conta', $id_conta, PDO::PARAM_INT);
                $query->execute();
                error_log("Atualização de hash em agendamentos: Linhas afetadas: " . $query->rowCount());
            }
        }
    }

    // Define resposta JSON
    $response['success'] = true;
    $response['valor_cobrado'] = $valor_final_receber;
    $response['tipo_registro'] = $coberto_pela_assinatura ? 'Assinante' : 'Serviço';
    $response['detalhe_assinatura'] = trim($mensagem_assinatura);
    $response['message'] = $coberto_pela_assinatura ? 'Serviço Coberto pela Assinatura' : 'Serviço Lançado com Sucesso';
    $response['nova_comanda_id'] = $id_comanda;

    // Confirma transação
    error_log("Antes do commit");
    $pdo->commit();
    error_log("Depois do commit");

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro no Banco de Dados: ' . $e->getMessage();
    error_log("Erro PDO: " . $e->getMessage() . " | Dados: " . print_r($_POST, true));
    echo json_encode($response);
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral: " . $e->getMessage() . " | Dados: " . print_r($_POST, true));
    echo json_encode($response);
    exit();
}

error_log("Resposta final: " . json_encode($response));
echo json_encode($response);
?>