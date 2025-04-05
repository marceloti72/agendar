<?php
require_once("../../../conexao.php");
@session_start();

header('Content-Type: application/json');
// Define a estrutura de resposta padrão
$response = ['success' => false, 'message' => 'Erro ao buscar dados.', 'assinante' => null, 'plano' => null, 'servicos' => [], 'historico' => []];

// Validações iniciais
if (!isset($_SESSION['id_conta'])) {
    $response['message'] = 'Sessão inválida.'; echo json_encode($response); exit;
}
$id_conta_corrente = $_SESSION['id_conta'];

$id_assinante = isset($_GET['id_assinante']) ? (int)$_GET['id_assinante'] : 0;

if ($id_assinante <= 0) {
     $response['message'] = 'ID do assinante inválido.';
    echo json_encode($response); exit;
}

try {
    // --- Query Principal para buscar dados do assinante, cliente, plano ---
    // --- E ID da próxima conta pendente ---
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
            -- Pega o ID da conta a receber pendente mais próxima
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
    // --- Fim da Query Principal ---

    if ($assinante) {
        $id_plano_atual = $assinante['id_plano'];
        // Guarda o ID da conta pendente atual (pode ser null se não houver)
        $id_receber_atual_pendente = $assinante['id_receber_pendente'];
        $response['assinante'] = $assinante;

        // Simplifica dados do plano principal
        $response['plano'] = [
             'id' => $id_plano_atual,
             'nome' => $assinante['nome_plano'],
             'preco_mensal' => $assinante['preco_mensal'],
             'preco_anual' => $assinante['preco_anual']
         ];

        // --- INÍCIO: Lógica para Calcular Uso dos Serviços (Usando id_receber_pendente) ---

        $servicos_com_uso = [];

        // Só busca serviços e uso se houver um plano E uma conta pendente associada
        if ($id_plano_atual !== null && $id_receber_atual_pendente !== null) {
            // Busca os serviços incluídos no plano (limite e nome)
            $query_serv = $pdo->prepare("
                SELECT s.id as id_servico, s.nome as nome_servico, ps.quantidade as limite
                FROM planos_servicos ps
                JOIN servicos s ON ps.id_servico = s.id
                WHERE ps.id_plano = :id_plano AND ps.id_conta = :id_conta
                ORDER BY s.nome ASC
            ");
            $query_serv->execute([':id_plano' => $id_plano_atual, ':id_conta' => $id_conta_corrente]);
            $servicos_do_plano = $query_serv->fetchAll(PDO::FETCH_ASSOC);

            if (count($servicos_do_plano) > 0) {
                // Prepara a query para contar o uso ASSOCIADO À CONTA PENDENTE
                $query_uso = $pdo->prepare("
                    SELECT SUM(quantidade_usada) as total_usado
                    FROM assinantes_servicos_usados
                    WHERE id_assinante = :id_ass
                      AND id_servico = :id_serv
                      AND id_conta = :id_conta
                      AND id_receber_associado = :id_rec -- <<-- FILTRA PELO ID DA COBRANÇA
                ");

                // Para cada serviço do plano, conta quantos foram usados neste ciclo
                foreach ($servicos_do_plano as $serv) {
                    $query_uso->execute([
                        ':id_ass' => $id_assinante,
                        ':id_serv' => $serv['id_servico'],
                        ':id_conta' => $id_conta_corrente,
                        ':id_rec' => $id_receber_atual_pendente // Usa o ID da cobrança pendente
                    ]);
                    $uso = $query_uso->fetch();
                    // Adiciona a contagem de uso ao array do serviço
                    $serv['usados'] = $uso && isset($uso['total_usado']) ? (int)$uso['total_usado'] : 0;
                    $servicos_com_uso[] = $serv; // Adiciona ao array final
                }
            }
            // Se $servicos_do_plano for vazio, $servicos_com_uso continuará vazio
            $response['servicos'] = $servicos_com_uso;

            // --- INÍCIO: Lógica para Buscar Histórico de Pagamentos ---
       $historico_pagamentos = [];
       try {
           // Query CORRIGIDA para usar data_venc e pgto, e cliente como FK
           $query_hist = $pdo->prepare("
               SELECT id, descricao, valor, multa, juros,
                      subtotal,        -- Mantido, pode ser útil               
                      data_lanc,
                      data_venc,       -- CORRIGIDO
                      data_pgto,
                      pago,
                      pgto as forma_pgto -- CORRIGIDO (alias para manter consistência no PHP/JS)
               FROM receber
               WHERE cliente = :id_assinante AND id_conta = :id_conta AND tipo = 'Assinatura' -- CORRIGIDO para cliente = :id_assinante
               ORDER BY data_venc DESC, id DESC -- CORRIGIDO
           ");
           $query_hist->execute([':id_assinante' => $id_assinante, ':id_conta' => $id_conta_corrente]);
           $historico_pagamentos_raw = $query_hist->fetchAll(PDO::FETCH_ASSOC);

           // Calcula dias em atraso para registros pagos
           foreach ($historico_pagamentos_raw as $pagamento) {
               $pagamento['dias_atraso'] = null; // Inicializa

               // Verifica se está pago E se as datas são válidas e diferentes de '0000-00-00'
               // Usa 'data_venc' CORRIGIDO
               if (
                   $pagamento['pago'] == 'Sim' &&
                   !empty($pagamento['data_pgto']) && $pagamento['data_pgto'] != null &&
                   !empty($pagamento['data_venc']) && $pagamento['data_venc'] != null
               ) {
                   // Tenta criar objetos DateTime
                   $dataPgtoObj = DateTime::createFromFormat('Y-m-d', $pagamento['data_pgto']);
                   $dataVencObj = DateTime::createFromFormat('Y-m-d', $pagamento['data_venc']); // CORRIGIDO

                   // Verifica se AMBOS os objetos foram criados com sucesso
                   if ($dataPgtoObj instanceof DateTime && $dataVencObj instanceof DateTime) {
                       $dataPgtoObj->setTime(0, 0, 0);
                       $dataVencObj->setTime(0, 0, 0);

                       if ($dataPgtoObj > $dataVencObj) {
                           $intervalo = $dataVencObj->diff($dataPgtoObj);
                           $pagamento['dias_atraso'] = $intervalo->days;
                       } else {
                            $pagamento['dias_atraso'] = 0; // Pago em dia ou adiantado
                       }
                   } else {
                        error_log("Erro ao converter datas ('{$pagamento['data_pgto']}', '{$pagamento['data_venc']}') para DateTime para receber ID {$pagamento['id']}");
                   }
               } // Fim if pago e datas válidas

               $historico_pagamentos[] = $pagamento; // Adiciona ao array final
           }
            // Adiciona o histórico processado à resposta JSON
           $response['historico'] = $historico_pagamentos;

        } catch (PDOException $e) {
            error_log("Erro SQL ao buscar histórico de pagamentos para assinante ID {$id_assinante}: " . $e->getMessage());
            $response['historico'] = [];
            $response['message'] = 'Erro ao buscar histórico de pagamentos.';
            $response['success'] = false;
        }
        // --- FIM: Lógica para Buscar Histórico de Pagamentos ---
         // --- FIM: Lógica para Buscar Histórico de Pagamentos ---

        } else { // Se não achou plano ou não achou cobrança pendente
            $response['servicos'] = []; // Retorna array vazio de serviços
            if ($id_plano_atual === null) {
                 $response['message'] = 'Assinante sem plano definido.';
                 error_log("Aviso: Assinante ID {$id_assinante} não tem um id_plano válido associado.");
            } elseif ($id_receber_atual_pendente === null) {
                 $response['message'] = 'Dados carregados. Nenhuma cobrança pendente encontrada para calcular uso atual.';
                 error_log("Aviso: Nenhuma cobrança pendente (receber.id) encontrada para assinante ID {$id_assinante} ao calcular uso.");
            }
        }
        // --- FIM: Lógica para Calcular Uso dos Serviços ---

        $response['success'] = true;
        // Ajusta a mensagem se a mensagem de erro não foi sobrescrita acima
         if(empty($response['message']) || $response['message'] == 'Erro ao buscar dados.') {
             $response['message'] = 'Dados carregados.';
         }


    } else {
        $response['message'] = 'Assinante não encontrado.';
    }

} catch (PDOException $e) {
    $response['message'] = 'Erro de Banco de Dados. Verifique os logs.'; // Mensagem genérica
    error_log("Erro SQL em buscar_detalhes_assinante: " . $e->getMessage()); // Log detalhado
} catch (Exception $e) {
     $response['message'] = 'Erro inesperado. Verifique os logs.'; // Mensagem genérica
     error_log("Erro Geral em buscar_detalhes_assinante: " . $e->getMessage());
}

echo json_encode($response);
?>