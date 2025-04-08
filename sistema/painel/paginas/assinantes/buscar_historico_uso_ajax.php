<?php
// buscar_historico_uso_ajax.php
require_once("../../../conexao.php");
@session_start();

// Permissões e validações
if (!isset($_SESSION['id_conta'])) {
    echo '<p class="text-danger text-center small">Sessão inválida.</p>'; exit;
}
$id_conta_corrente = $_SESSION['id_conta'];

$id_assinante = isset($_POST['id_assinante']) ? (int)$_POST['id_assinante'] : 0;
$id_receber = isset($_POST['id_receber']) ? (int)$_POST['id_receber'] : 0;
// $id_conta_form = isset($_POST['id_conta']) ? (int)$_POST['id_conta'] : 0; // Vem da sessão

if ($id_assinante <= 0 || $id_receber <= 0 /* || $id_conta_form !== $id_conta_corrente */) {
    echo '<p class="text-danger text-center small">Dados inválidos para buscar histórico.</p>'; exit;
}

// --- Lógica para Buscar Histórico DETALHADO de USO de Serviços ---
$output = '<p class="text-muted small mt-2 text-center">Nenhum uso registrado neste ciclo de cobrança.</p>'; // Padrão

try {
    $query_hist_uso = $pdo->prepare("
        SELECT usu.data_uso, usu.quantidade_usada, usu.observacao, s.nome as nome_servico_usado
        FROM assinantes_servicos_usados usu
        JOIN servicos s ON usu.id_servico = s.id
        WHERE usu.id_assinante = :id_ass
          AND usu.id_conta = :id_conta
          AND usu.id_receber_associado = :id_rec -- Filtra pelo ID do ciclo atual/pendente
        ORDER BY usu.data_uso DESC -- Mais recentes primeiro
    ");
    $query_hist_uso->execute([
        ':id_ass' => $id_assinante,
        ':id_conta' => $id_conta_corrente,
        ':id_rec' => $id_receber
    ]);
    $historico_uso = $query_hist_uso->fetchAll(PDO::FETCH_ASSOC);

    if (count($historico_uso) > 0) {
        $output = '<table class="table table-sm table-striped small mt-2">'; // Tabela para detalhes
        $output .= '<thead><tr><th>Data/Hora</th><th>Serviço</th><th class="text-center">Qtd</th><th>Obs</th></tr></thead>';
        $output .= '<tbody>';
        foreach ($historico_uso as $uso) {
            // Formata data/hora
            $data_uso_fmt = date('d/m/Y H:i', strtotime($uso['data_uso']));
            $nome_serv_usado = htmlspecialchars($uso['nome_servico_usado']);
            $qtd_usada = htmlspecialchars($uso['quantidade_usada']);
            $obs_usada = !empty($uso['observacao']) ? htmlspecialchars($uso['observacao']) : '-';

            $output .= '<tr>';
            $output .= '<td>' . $data_uso_fmt . '</td>';
            $output .= '<td>' . $nome_serv_usado . '</td>';
            $output .= '<td class="text-center">' . $qtd_usada . '</td>';
            $output .= '<td>' . $obs_usada . '</td>';
            $output .= '</tr>';
        }
        $output .= '</tbody></table>';
    }
    // Se não houver resultados, a mensagem padrão inicial é usada.

} catch (PDOException $e) {
     error_log("Erro SQL buscar_historico_uso_ajax (Assinante {$id_assinante}, Receber {$id_receber}): " . $e->getMessage());
     $output = '<p class="text-danger text-center small mt-2">Erro ao buscar histórico de uso.</p>';
}

echo $output; // Envia diretamente o HTML da tabela ou a mensagem
?>