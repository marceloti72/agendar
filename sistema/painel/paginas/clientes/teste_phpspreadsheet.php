<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/teste_phpspreadsheet_errors.log');

// Log inicial
$logFile = __DIR__ . '/teste_phpspreadsheet.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Teste iniciado\n", FILE_APPEND);

// Verifica permissões
if (!is_writable(__DIR__)) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERRO: Diretório não gravável: " . __DIR__ . "\n", FILE_APPEND);
    die(json_encode(['error' => 'Diretório não gravável: ' . __DIR__]));
}

// Testa vendor/autoload.php
$vendorPath = __DIR__ . '/../../../../vendor/autoload.php';
if (!file_exists($vendorPath)) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERRO: vendor/autoload.php não encontrado em: $vendorPath\n", FILE_APPEND);
    die(json_encode(['error' => "vendor/autoload.php não encontrado em: $vendorPath"]));
}
require $vendorPath;
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Vendor carregado\n", FILE_APPEND);

try {
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    // Log versão do PHP
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Versão do PHP: " . PHP_VERSION . "\n", FILE_APPEND);

    // Testa criação da planilha
    $spreadsheet = new Spreadsheet();
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Planilha criada\n", FILE_APPEND);

    $sheet = $spreadsheet->getActiveSheet();
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Planilha ativa obtida\n", FILE_APPEND);

    $sheet->setCellValue('A1', 'Teste Simples');
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Célula preenchida\n", FILE_APPEND);

    if (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();
    $filename = 'teste_simples_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: 0');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Download iniciado\n", FILE_APPEND);
    ob_end_flush();
    exit;
} catch (Exception $e) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
    if (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Erro no PhpSpreadsheet: ' . $e->getMessage()]);
    exit;
}
?>