<?php
require_once("../../../conexao.php");
@session_start();

header('Content-Type: application/json');
// Define a estrutura de resposta padrão
$response = ['success' => false, 'message' => 'Erro ao buscar dados.', 'assinante' => null, 'plano' => null, 'servicos' => [], 'historico' => []];

// Validações iniciais
if (!isset($_SESSION['id_conta'])) {
    $response['message'] = 'Sessão inválida.';
    echo json_encode($response);
    exit;
}
$id_conta_corrente = $_SESSION['id_conta'];

$id_assinante = isset($_GET['id_assinante']) ? (int)$_GET['id_assinante'] : 0;

if ($id_assinante <= 0) {
    $response['message'] = 'ID do assinante inválido.';
    echo json_encode($response);
    exit;
}

try {
    // Query Principal para buscar dados do assinante, cliente, plano e ID da próxima conta pendente
    $query_ass = $pdo->prepare("
        SELECT
            a.id, a.id_cliente, a.id_plano, a.data_cadastro, a.data_vencimento, a.ativo,
            c.nome, c.cpf, c.telefone, c.email,
            p.nome as nome_plano, p.preco_mensal, p.preco_anual,
            (SELECT r.frequencia
             FROM receber r
             WHERE r.cliente = a.id AND r.id_conta = a.id_conta AND r.tipo = 'Assinatura'
             ORDER BY r.data_venc DESC, r.id DESC
             LIMIT 1
            ) as frequencia_atual,
            (SELECT r_pag.id
             FROM receber r_pag
             WHERE r_pag.cliente = a.id AND r_pag.id_conta = a.id_conta AND r_pag.pago = 'Não' AND r_pag.tipo = 'Assinatura'
             ORDER BY r_pag.data_venc ASC, r_pag.id ASC LIMIT 1
            ) as id_receber_pendente
        FROM assinantes a
        INNER JOIN clientes c ON a.id_cliente = c.id AND a.id_conta = c.id_conta
        LEFT JOIN planos p ON a.id_plano = p.id AND a.id_conta = p.id_conta
        WHERE a.id = :id_assinante AND a.id_conta = :id_conta
    ");
    $query_ass->execute([':id_assinante' => $id_assinante, ':id_conta' => $id_conta_corrente]);
    $assinante = $query_ass->fetch(PDO::FETCH_ASSOC);

    if ($assinante) {
        $id_plano_atual = $assinante['id_plano'];
        $id_receber_atual_pendente = $assinante['id_receber_pendente'];
        $frequencia_ciclo_atual = $assinante['frequencia_atual']; // Added missing variable definition
        $response['assinante'] = $assinante;

        // Simplifica dados do plano principal
        $response['plano'] = [
            'id' => $id_plano_atual,
            'nome' => $assinante['nome_plano'],
            'preco_mensal' => $assinante['preco_mensal'],
            'preco_anual' => $assinante['preco_anual']
        ];

        $servicos_com_uso = [];
        if ($id_plano_atual !== null && $id_receber_atual_pendente !== null) {
            // Busca os serviços incluídos no plano
            $query_serv = $pdo->prepare("
                SELECT s.id as id_servico, s.nome as nome_servico, ps.quantidade as limite_base
                FROM planos_servicos ps
                JOIN servicos s ON ps.id_servico = s.id
                WHERE ps.id_plano = :id_plano AND ps.id_conta = :id_conta
                ORDER BY s.nome ASC
            ");
            $query_serv->execute([':id_plano' => $id_plano_atual, ':id_conta' => $id_conta_corrente]);
            $servicos_do_plano = $query_serv->fetchAll(PDO::FETCH_ASSOC);

            if (count($servicos_do_plano) > 0) {
                $query_uso = $pdo->prepare("
                    SELECT SUM(quantidade_usada) as total_usado
                    FROM assinantes_servicos_usados
                    WHERE id_assinante = :id_ass AND id_servico = :id_serv AND id_conta = :id_conta AND id_receber_associado = :id_rec
                ");

                foreach ($servicos_do_plano as $serv) {
                    $id_servico_atual = $serv['id_servico'];
                    $limite_base = (int)($serv['limite_base'] ?? 0);
                    $nome_servico = htmlspecialchars($serv['nome_servico']);

                    // Calcula o limite a ser exibido
                    $limite_para_exibir = $limite_base;
                    if ($frequencia_ciclo_atual == 365 && $limite_base > 0) {
                        $limite_para_exibir = $limite_base * 12;
                    } elseif ($limite_base == 0) {
                        $limite_para_exibir = 0;
                    }

                    $query_uso->execute([
                        ':id_ass' => $id_assinante,
                        ':id_serv' => $id_servico_atual,
                        ':id_conta' => $id_conta_corrente,
                        ':id_rec' => $id_receber_atual_pendente
                    ]);
                    $uso = $query_uso->fetch();
                    $usados = $uso && isset($uso['total_usado']) ? (int)$uso['total_usado'] : 0;

                    $servicos_com_uso[] = [
                        'id_servico' => $id_servico_atual,
                        'nome_servico' => $nome_servico,
                        'limite' => $limite_para_exibir,
                        'usados' => $usados
                    ];
                }
            }
        }
        $response['servicos'] = $servicos_com_uso;

        // Histórico de Pagamentos
        $historico_pagamentos = [];
        $query_hist = $pdo->prepare("
            SELECT id, descricao, valor, multa, juros,
                   subtotal,
                   data_lanc,
                   data_venc,
                   data_pgto,
                   pago,
                   pgto as forma_pgto
            FROM receber
            WHERE cliente = :id_assinante AND id_conta = :id_conta AND tipo = 'Assinatura'
            ORDER BY data_venc DESC, id DESC
        ");
        $query_hist->execute([':id_assinante' => $id_assinante, ':id_conta' => $id_conta_corrente]);
        $historico_pagamentos_raw = $query_hist->fetchAll(PDO::FETCH_ASSOC);

        foreach ($historico_pagamentos_raw as $pagamento) {
            $pagamento['dias_atraso'] = null;

            if (
                $pagamento['pago'] === 'Sim' &&
                !empty($pagamento['data_pgto']) &&
                $pagamento['data_pgto'] !== null &&
                !empty($pagamento['data_venc']) &&
                $pagamento['data_venc'] !== null
            ) {
                $dataPgtoObj = DateTime::createFromFormat('Y-m-d', $pagamento['data_pgto']);
                $dataVencObj = DateTime::createFromFormat('Y-m-d', $pagamento['data_venc']);

                if ($dataPgtoObj instanceof DateTime && $dataVencObj instanceof DateTime) {
                    $dataPgtoObj->setTime(0, 0, 0);
                    $dataVencObj->setTime(0, 0, 0);

                    if ($dataPgtoObj > $dataVencObj) {
                        $intervalo = $dataVencObj->diff($dataPgtoObj);
                        $pagamento['dias_atraso'] = $intervalo->days;
                    } else {
                        $pagamento['dias_atraso'] = 0;
                    }
                } else {
                    error_log("Erro ao converter datas ('{$pagamento['data_pgto']}', '{$pagamento['data_venc']}') para DateTime para receber ID {$pagamento['id']}");
                }
            }
            $historico_pagamentos[] = $pagamento;
        }
        $response['historico'] = $historico_pagamentos;

        $response['success'] = true;
        $response['message'] = 'Dados carregados.';
    } else {
        $response['message'] = 'Assinante não encontrado.';
    }

} catch (PDOException $e) {
    $response['message'] = 'Erro de Banco de Dados. Verifique os logs.';
    error_log("Erro SQL em buscar_detalhes_assinante: " . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = 'Erro inesperado. Verifique os logs.';
    error_log("Erro Geral em buscar_detalhes_assinante: " . $e->getMessage());
}

echo json_encode($response);
?>