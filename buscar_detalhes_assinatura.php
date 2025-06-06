<?php
require_once("sistema/conexao2.php"); // Ajuste o caminho
@session_start();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Erro desconhecido.',
    'cliente_id' => null, // Adicionado para retornar o ID encontrado
    'cliente_nome' => null,
    'plano_nome' => null,
    'proximo_vencimento' => null,
    'servicos' => []
];

// Verifica sessão
$id_conta_corrente = isset($_SESSION['id_conta']) ? filter_var($_SESSION['id_conta'], FILTER_VALIDATE_INT) : null;
if ($id_conta_corrente === null) {
    $response['message'] = 'Sessão inválida.';
    echo json_encode($response); exit;
}

// Pega e valida TELEFONE da URL (?telefone=...)
$telefone_input = isset($_GET['telefone']) ? trim($_GET['telefone']) : '';
if (empty($telefone_input)) {
    $response['message'] = 'Número de telefone não fornecido.';
    echo json_encode($response); exit;
}
$senha_input = isset($_GET['s']) ? trim($_GET['s']) : '';
if (empty($senha_input)) {
    $response['message'] = 'Senha não informada!';
    echo json_encode($response); exit;
}

// ** CRUCIAL: Limpa o telefone para bater com o formato do seu BD **
// Este exemplo remove tudo exceto dígitos. Adapte se seu BD armazena com máscara!
$telefone_limpo = preg_replace('/\D/', '', $telefone_input);
if (strlen($telefone_limpo) < 10) { // Validação Mínima (DDD + 8 digitos)
     $response['message'] = 'Formato de telefone inválido.';
     echo json_encode($response); exit;
}

try {
    // 1. Buscar Cliente pelo Telefone
    // ** Adapte a coluna 'telefone' se o nome for diferente no seu BD **
    $query_cli = $pdo->prepare("SELECT id, nome FROM clientes WHERE telefone = :telefone AND id_conta = :id_conta");
    $query_cli->execute([':telefone' => $telefone_input, ':id_conta' => $id_conta_corrente]);
    $clientes_encontrados = $query_cli->fetchAll(PDO::FETCH_ASSOC);

    if (count($clientes_encontrados) == 0) {
        $response['message'] = "Nenhum cliente encontrado com o telefone: " . $telefone_input; // Mostra o tel original
        echo json_encode($response); exit;
    }

    if (count($clientes_encontrados) > 1) {
        $response['message'] = "Múltiplos clientes encontrados com este telefone. Verifique o cadastro.";
        echo json_encode($response); exit;
    }

    // Cliente único encontrado!
    $cliente_info = $clientes_encontrados[0];
    $cliente_id = $cliente_info['id'];
    $response['cliente_id'] = $cliente_id; // Retorna o ID encontrado
    $response['cliente_nome'] = $cliente_info['nome'];

    // --- O restante do script continua como antes, usando o $cliente_id encontrado ---

    // 2. Buscar assinatura ATIVA do cliente
    $query_ass = $pdo->prepare("SELECT a.id, a.id_plano, a.senha, p.nome as nome_plano
                                FROM assinantes a
                                JOIN planos p ON a.id_plano = p.id
                                WHERE a.id_cliente = :id_cliente
                                AND a.id_conta = :id_conta
                                AND a.ativo = 1
                                AND a.data_vencimento >= CURDATE()");
    $query_ass->execute([':id_cliente' => $cliente_id, ':id_conta' => $id_conta_corrente]); // Usa $cliente_id
    $assinante_info = $query_ass->fetch(PDO::FETCH_ASSOC);

    if (!$assinante_info) {
        $response['success'] = true;
        $response['message'] = 'Cliente localizado, mas não possui assinatura ativa.';
        $response['plano_nome'] = 'Nenhum';
        echo json_encode($response); exit;
    }
    
    if ($assinante_info['senha'] != $senha_input) {        
        $response['message'] = 'Cliente localizado, mas a senha esta incorreta.';
        echo json_encode($response); exit;
    }

    $id_assinante_encontrado = $assinante_info['id'];
    $id_plano_assinante = $assinante_info['id_plano'];
    $response['plano_nome'] = $assinante_info['nome_plano'];

    // 3. Buscar o PRÓXIMO ciclo de pagamento PENDENTE em 'receber'
    $id_receber_ciclo_atual = null;
    $frequencia_ciclo = 0;
    $proximo_venc = null;

    // ** VERIFIQUE SE 'pessoa' é a coluna correta para ligar a clientes.id **
    $query_rec = $pdo->prepare("SELECT id, frequencia, data_venc
                                FROM receber
                                WHERE pessoa = :id_cliente
                                AND id_conta = :id_conta
                                AND pago = 'Não'
                                AND tipo = 'Assinatura'
                                ORDER BY data_venc ASC, id ASC
                                LIMIT 1");
    $query_rec->execute([':id_cliente' => $cliente_id, ':id_conta' => $id_conta_corrente]); // Usa $cliente_id
    $rec_atual = $query_rec->fetch(PDO::FETCH_ASSOC);

    if ($rec_atual) {
        $id_receber_ciclo_atual = $rec_atual['id'];
        $frequencia_ciclo = (int)$rec_atual['frequencia'];
        $proximo_venc = $rec_atual['data_venc'];
        $response['proximo_vencimento'] = $proximo_venc;
    } else {
         $query_venc_ass = $pdo->prepare("SELECT data_vencimento FROM assinantes WHERE id = :id_ass");
         $query_venc_ass->execute([':id_ass' => $id_assinante_encontrado]);
         $venc_ass_info = $query_venc_ass->fetch(PDO::FETCH_ASSOC);
         $response['proximo_vencimento'] = $venc_ass_info ? $venc_ass_info['data_vencimento'] : null;
         error_log("Aviso: Ciclo de assinatura pendente não encontrado para assinante ID {$id_assinante_encontrado} / Cliente ID {$cliente_id}. Uso não calculado.");
    }

    // 4. Buscar serviços do plano e calcular uso/limite (SÓ SE ACHOU CICLO)
    if ($id_receber_ciclo_atual) {
        $query_ps = $pdo->prepare("SELECT ps.id_servico, ps.quantidade as limite_base, s.nome as nome_servico
                                   FROM planos_servicos ps
                                   JOIN servicos s ON ps.id_servico = s.id
                                   WHERE ps.id_plano = :id_plano AND ps.id_conta = :id_conta");
        $query_ps->execute([':id_plano' => $id_plano_assinante, ':id_conta' => $id_conta_corrente]);
        $servicos_plano = $query_ps->fetchAll(PDO::FETCH_ASSOC);

        foreach ($servicos_plano as $servico) {
            $servico_atual_id = $servico['id_servico'];
            $limite_base = (int)$servico['limite_base'];

            $limite_ciclo = $limite_base;
            if ($frequencia_ciclo == 365 && $limite_base > 0) { $limite_ciclo = $limite_base * 12; }
            elseif ($limite_base == 0) { $limite_ciclo = 0; }

            $uso_atual = 0;
            if ($limite_ciclo !== 0) {
                $query_uso = $pdo->prepare("SELECT SUM(quantidade_usada) as total_usado
                                           FROM assinantes_servicos_usados
                                           WHERE id_assinante = :id_ass
                                           AND id_servico = :id_serv
                                           AND id_receber_associado = :id_rec
                                           AND id_conta = :id_conta");
                $query_uso->execute([
                    ':id_ass' => $id_assinante_encontrado,
                    ':id_serv' => $servico_atual_id,
                    ':id_rec' => $id_receber_ciclo_atual,
                    ':id_conta' => $id_conta_corrente
                ]);
                $uso_info = $query_uso->fetch(PDO::FETCH_ASSOC);
                $uso_atual = $uso_info ? (int)$uso_info['total_usado'] : 0;
            }

            $response['servicos'][] = [
                'nome' => $servico['nome_servico'],
                'uso_atual' => $uso_atual,
                'limite_ciclo' => $limite_ciclo
            ];
        }
    } else {
        $response['message'] = $response['message'] ? $response['message'] : 'Ciclo de pagamento pendente não encontrado. Não foi possível calcular o uso atual dos serviços.';
    }

    $response['success'] = true;
     if (empty($response['message']) || $response['message'] == 'Erro desconhecido.') { // Evita sobrescrever mensagens de aviso
        $response['message'] = 'Detalhes carregados.';
     }


} catch (PDOException $e) {
    $response['message'] = 'Erro de Banco de Dados: Verifique os logs.'; // Mensagem mais segura para o usuário
    error_log("Erro PDO em buscar_detalhes_assinatura por telefone: " . $e->getMessage() . " | Telefone: " . $telefone_input);
} catch (Exception $e) {
    $response['message'] = 'Erro: ' . $e->getMessage();
     error_log("Erro Geral em buscar_detalhes_assinatura por telefone: " . $e->getMessage() . " | Telefone: " . $telefone_input);
}

echo json_encode($response);
?>