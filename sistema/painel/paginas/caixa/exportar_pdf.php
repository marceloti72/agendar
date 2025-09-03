<?php
ob_start();

session_start();
require_once("../../../conexao.php");
require_once '../../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['id_conta'])) {
    // Redireciona para o login se a sessão não estiver definida
    header('Location: ../login.php');
    exit;
}

$id_conta = $_SESSION['id_conta'];

try {
    $sql = "SELECT
                c.data_abertura,
                c.data_fechamento,
                c.valor_abertura,
                c.valor_fechamento,
                c.sangrias,
                u_op.nome as operador_nome,
                u_ab.nome as usuario_abertura_nome,
                u_fe.nome as usuario_fechamento_nome,
                c.obs
            FROM caixa c
            JOIN usuarios u_op ON c.operador = u_op.id
            JOIN usuarios u_ab ON c.usuario_abertura = u_ab.id
            LEFT JOIN usuarios u_fe ON c.usuario_fechamento = u_fe.id
            WHERE c.id_conta = :id_conta
            ORDER BY c.data_abertura DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
    $stmt->execute();
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cria o HTML para o PDF
    $html = '<style>
                body { font-family: sans-serif; font-size: 10px; }
                h1 { color: #333; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                tr:nth-child(even) { background-color: #f9f9f9; }
            </style>';
    $html .= '<h1>Relatório Histórico de Caixas</h1>';
    $html .= '<table>
                <thead>
                    <tr>
                        <th>Data Abertura</th>
                        <th>Data Fechamento</th>
                        <th>Operador</th>
                        <th>Usuário Abertura</th>
                        <th>Usuário Fechamento</th>
                        <th>Valor Abertura (R$)</th>
                        <th>Valor Fechamento (R$)</th>
                        <th>Sangrias (R$)</th>
                        <th>Quebra (R$)</th>
                        <th>Observações</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($report_data as $item) {
        $quebra = ($item['valor_fechamento'] !== null) ? ($item['valor_fechamento'] - $item['valor_abertura'] - ($item['sangrias'] ?? 0)) : null;

        $html .= '<tr>
                    <td>' . date('d/m/Y', strtotime($item['data_abertura'])) . '</td>
                    <td>' . ($item['data_fechamento'] ? date('d/m/Y', strtotime($item['data_fechamento'])) : '-') . '</td>
                    <td>' . htmlspecialchars($item['operador_nome']) . '</td>
                    <td>' . htmlspecialchars($item['usuario_abertura_nome']) . '</td>
                    <td>' . htmlspecialchars($item['usuario_fechamento_nome'] ?? '-') . '</td>
                    <td>' . number_format($item['valor_abertura'], 2, ',', '.') . '</td>
                    <td>' . ($item['valor_fechamento'] ? number_format($item['valor_fechamento'], 2, ',', '.') : '-') . '</td>
                    <td>' . ($item['sangrias'] ? number_format($item['sangrias'], 2, ',', '.') : '-') . '</td>
                    <td>' . ($quebra ? number_format($quebra, 2, ',', '.') : '-') . '</td>
                    <td>' . htmlspecialchars($item['obs'] ?? '-') . '</td>
                </tr>';
    }

    $html .= '</tbody></table>';

    // Configura e gera o PDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // Limpa o buffer de saída antes de enviar os cabeçalhos
    ob_end_clean();
    $dompdf->stream('relatorio_caixa.pdf', ['Attachment' => true]);
    exit;

} catch(PDOException $e) {
    echo 'Erro ao carregar relatório para exportação: ' . $e->getMessage();
    exit;
}
