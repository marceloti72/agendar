<?php
// Garante que erros PHP não quebrem a saída HTML esperada pelo AJAX
// É melhor configurar isso no php.ini principal para produção
// ini_set('display_errors', 0);
// error_reporting(0);

require_once("../../../conexao.php"); // Ajuste o caminho conforme necessário
@session_start();

// Verifica sessão ANTES de qualquer saída
if (!isset($_SESSION['id_conta'])) {
    // Não use echo aqui se o JS espera HTML. Logar e sair é uma opção.
    error_log("listar_servicos_com_uso: Sessão inválida.");
    // Retorna uma mensagem de erro HTML para o AJAX exibir
    echo '<p class="text-danger text-center small mt-2">Sessão expirada. Faça login novamente.</p>';
    exit;
}
$id_conta_corrente = $_SESSION['id_conta'];

// Pega IDs via POST (mais seguro que GET para ações internas)
$id_assinante = isset($_POST['id_assinante']) ? (int)$_POST['id_assinante'] : 0;
$id_receber = isset($_POST['id_receber']) ? (int)$_POST['id_receber'] : 0; // ID do ciclo atual

// Validação inicial
if ($id_assinante <= 0 || $id_receber <= 0) {
    echo '<p class="text-danger text-center small mt-2">Erro: IDs inválidos fornecidos.</p>';
    exit;
}

// Mensagem padrão caso nada seja encontrado
$output = '<p class="text-muted text-center small mt-2">Nenhum serviço encontrado para este plano ou ciclo.</p>';

try {
    // 1. Descobre o plano do assinante e a frequência do ciclo atual
    $id_plano = null;
    $frequencia_ciclo = 0;

    // Pega o ID do plano do assinante
    $query_ass_plano = $pdo->prepare("SELECT id_plano FROM assinantes WHERE id = :id_ass AND id_conta = :id_conta");
    $query_ass_plano->execute([':id_ass' => $id_assinante, ':id_conta' => $id_conta_corrente]);
    $plano_ass = $query_ass_plano->fetch();
    if ($plano_ass) {
        $id_plano = $plano_ass['id_plano'];
    }

    // Pega a frequência diretamente da conta a receber atual (ciclo)
    $query_freq = $pdo->prepare("SELECT frequencia FROM receber WHERE id = :id_rec AND id_conta = :id_conta");
    $query_freq->execute([':id_rec' => $id_receber, ':id_conta' => $id_conta_corrente]);
    $rec_info = $query_freq->fetch();
    if ($rec_info) {
         $frequencia_ciclo = (int)$rec_info['frequencia'];
    }

    // Só continua se tivermos um plano e uma frequência válidos para o ciclo
    if ($id_plano !== null && ($frequencia_ciclo == 30 || $frequencia_ciclo == 365)) {

        $html_output_resumo = ''; // Para o resumo Uso/Limite
        $html_output_historico = ''; // Para a tabela de histórico

        // --- 2. Busca Serviços do Plano (Limite Base) e Calcula Uso/Limite do Ciclo ---
        $query_serv_plano = $pdo->prepare("
            SELECT s.id as id_servico, s.nome as nome_servico, ps.quantidade as limite_base
            FROM planos_servicos ps
            JOIN servicos s ON ps.id_servico = s.id
            WHERE ps.id_plano = :id_plano AND ps.id_conta = :id_conta
            ORDER BY s.nome ASC
        ");
        $query_serv_plano->execute([':id_plano' => $id_plano, ':id_conta' => $id_conta_corrente]);
        $servicos_do_plano = $query_serv_plano->fetchAll(PDO::FETCH_ASSOC);

        if (count($servicos_do_plano) > 0) {
            $html_output_resumo = '<ul class="list-group list-group-flush mb-3">'; // Lista para o resumo
            $query_uso = $pdo->prepare("
                SELECT SUM(quantidade_usada) as total_usado
                FROM assinantes_servicos_usados
                WHERE id_assinante = :id_ass AND id_servico = :id_serv AND id_conta = :id_conta AND id_receber_associado = :id_rec
            ");

            foreach ($servicos_do_plano as $serv) {
                $id_servico_atual = $serv['id_servico'];
                $limite_base = (int)$serv['limite_base']; // Limite salvo no BD (assumido mensal)
                $nome_servico = htmlspecialchars($serv['nome_servico']);

                // Calcula o limite REAL para este ciclo
                $limite_ciclo = $limite_base; // Assume mensal por padrão ou ilimitado se base for 0
                if ($frequencia_ciclo == 365 && $limite_base > 0) { // Se ciclo é ANUAL e não ilimitado
                    $limite_ciclo = $limite_base * 12; // Multiplica por 12
                }

                // Conta o uso neste ciclo
                $query_uso->execute([
                    ':id_ass' => $id_assinante, ':id_serv' => $id_servico_atual,
                    ':id_conta' => $id_conta_corrente, ':id_rec' => $id_receber
                ]);
                $res_uso = $query_uso->fetch();
                $usados = $res_uso ? (int)$res_uso['total_usado'] : 0;

                // Monta a exibição do resumo
                $texto_limite_ciclo = ($limite_ciclo === 0) ? 'Ilimitado' : $limite_ciclo;
                $uso_texto = $usados . ' / ' . $texto_limite_ciclo;
                if ($limite_ciclo === 0) { $uso_texto = 'Ilimitado (Usados: ' . $usados . ')'; }
                $classe_limite = ($limite_ciclo > 0 && $usados >= $limite_ciclo) ? 'text-danger font-weight-bold' : 'text-muted';

                $html_output_resumo .= '<li class="list-group-item d-flex justify-content-between align-items-center py-1 px-0 border-0">'; // Simplificado
                $html_output_resumo .= '<span><i class="fas fa-check text-success mr-1"></i>' . $nome_servico . '</span>';
                $html_output_resumo .= '<small class="' . $classe_limite . '">(' . $uso_texto . ')</small>';
                $html_output_resumo .= '</li>';
            }
            $html_output_resumo .= '</ul><hr>'; // Fim do resumo
        } else {
            // Usa a mensagem padrão "Nenhum serviço..."
            $html_output_resumo = $output;
        }

        // --- 3. Busca o Histórico de Uso DETALHADO para este ciclo ---
        $query_hist_uso = $pdo->prepare("
            SELECT usu.data_uso, usu.quantidade_usada, usu.observacao, s.nome as nome_servico_usado
            FROM assinantes_servicos_usados usu
            JOIN servicos s ON usu.id_servico = s.id
            WHERE usu.id_assinante = :id_ass
              AND usu.id_conta = :id_conta
              AND usu.id_receber_associado = :id_rec -- Filtra pelo ID do ciclo atual
            ORDER BY usu.data_uso DESC -- Mais recentes primeiro
        ");
        $query_hist_uso->execute([
            ':id_ass' => $id_assinante,
            ':id_conta' => $id_conta_corrente,
            ':id_rec' => $id_receber
        ]);
        $historico_uso = $query_hist_uso->fetchAll(PDO::FETCH_ASSOC);

        // Monta o HTML do histórico detalhado
        $html_output_historico = '<h6>Histórico de Uso (Ciclo Atual)</h6>';
        if (count($historico_uso) > 0) {
            $html_output_historico .= '<table class="table table-sm table-striped small mt-2">';
            $html_output_historico .= '<thead><tr><th>Data/Hora</th><th>Serviço</th><th class="text-center">Qtd</th><th>Obs</th></tr></thead>';
            $html_output_historico .= '<tbody>';
            foreach ($historico_uso as $uso) {
                // Formata data/hora
                $data_uso_fmt = date('d/m/Y H:i', strtotime($uso['data_uso']));
                $nome_serv_usado = htmlspecialchars($uso['nome_servico_usado']);
                $qtd_usada = htmlspecialchars($uso['quantidade_usada']);
                $obs_usada = !empty($uso['observacao']) ? htmlspecialchars($uso['observacao']) : '-';

                $html_output_historico .= '<tr>';
                $html_output_historico .= '<td>' . $data_uso_fmt . '</td>';
                $html_output_historico .= '<td>' . $nome_serv_usado . '</td>';
                $html_output_historico .= '<td class="text-center">' . $qtd_usada . '</td>';
                $html_output_historico .= '<td>' . $obs_usada . '</td>';
                $html_output_historico .= '</tr>';
            }
            $html_output_historico .= '</tbody></table>';
        } else {
            $html_output_historico .= '<p class="text-muted small mt-2">Nenhum uso registrado neste ciclo de cobrança.</p>';
        }

        // Combina as duas partes do HTML para a saída final
        $output = $html_output_resumo . $html_output_historico;

    } else { // else do if ($id_plano !== null && $frequencia_ciclo > 0)
         $output = '<p class="text-danger text-center small mt-2">Não foi possível determinar o plano ou a frequência da cobrança atual.</p>';
    }

} catch (PDOException $e) {
    error_log("Erro listar_servicos_com_uso: " . $e->getMessage());
    $output = '<p class="text-danger text-center small mt-2">Erro ao carregar dados dos serviços.</p>';
}

// Envia o HTML gerado
echo $output;
?>