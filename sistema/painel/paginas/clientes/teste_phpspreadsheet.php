<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/teste_errors.log');

// Log inicial
file_put_contents(__DIR__ . '/teste_phpspreadsheet.txt', date('Y-m-d H:i:s') . " - Início do teste\n", FILE_APPEND);

try {
    // Verifica e carrega PhpSpreadsheet
    $vendorPath = __DIR__ . '/../../../vendor/autoload.php'; // Ajuste conforme necessário
    if (!file_exists($vendorPath)) {
        throw new Exception("Arquivo vendor/autoload.php não encontrado em: $vendorPath");
    }
    require $vendorPath;
    file_put_contents(__DIR__ . '/teste_phpspreadsheet.txt', date('Y-m-d H:i:s') . " - Vendor carregado\n", FILE_APPEND);

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    // Testa criação da planilha
    $spreadsheet = new Spreadsheet();
    file_put_contents(__DIR__ . '/teste_phpspreadsheet.txt', date('Y-m-d H:i:s') . " - Planilha criada\n", FILE_APPEND);

    $sheet = $spreadsheet->getActiveSheet();
    file_put_contents(__DIR__ . '/teste_phpspreadsheet.txt', date('Y-m-d H:i:s') . " - Planilha ativa obtida\n", FILE_APPEND);

    // Testa escrita simples
    $sheet->setCellValue('A1', 'Teste');
    file_put_contents(__DIR__ . '/teste_phpspreadsheet.txt', date('Y-m-d H:i:s') . " - Célula preenchida\n", FILE_APPEND);

    // Testa salvamento
    $writer = new Xlsx($spreadsheet);
    $filename = 'teste_' . date('Y-m-d_H-i-s') . '.xlsx';
    if (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    file_put_contents(__DIR__ . '/teste_phpspreadsheet.txt', date('Y-m-d H:i:s') . " - Download iniciado\n", FILE_APPEND);
    ob_end_flush();
    exit;

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/teste_phpspreadsheet.txt', date('Y-m-d H:i:s') . " - ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
    if (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Erro no teste: ' . $e->getMessage()]);
    exit;
}
?>