<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Log inicial
file_put_contents(__DIR__ . '/debug_exportar.txt', date('Y-m-d H:i:s') . " - Início do script\n", FILE_APPEND);

try {
    // Tenta iniciar sessão
    @session_start();
    file_put_contents(__DIR__ . '/debug_exportar.txt', date('Y-m-d H:i:s') . " - Sessão iniciada\n", FILE_APPEND);

    // Verifica sessão
    if (!isset($_SESSION['id_conta'])) {
        throw new Exception('Sessão id_conta não definida');
    }
    $id_conta = $_SESSION['id_conta'];
    file_put_contents(__DIR__ . '/debug_exportar.txt', date('Y-m-d H:i:s') . " - ID Conta: $id_conta\n", FILE_APPEND);

    // Verifica e carrega conexão
    $conexaoPath = __DIR__ . '/../../../conexao.php'; // /var/www/markai/conexao.php
    if (!file_exists($conexaoPath)) {
        throw new Exception("Arquivo conexao.php não encontrado em: $conexaoPath");
    }
    require_once($conexaoPath);
    file_put_contents(__DIR__ . '/debug_exportar.txt', date('Y-m-d H:i:s') . " - Conexão carregada\n", FILE_APPEND);

    // Testa PDO
    $pdo->query("SELECT 1");
    file_put_contents(__DIR__ . '/debug_exportar.txt', date('Y-m-d H:i:s') . " - PDO OK\n", FILE_APPEND);

    // Verifica e carrega PhpSpreadsheet
    // Tente diferentes caminhos para vendor/autoload.php
    $possibleVendorPaths = [
        __DIR__ . '/../../../vendor/autoload.php', // /var/www/markai/vendor/autoload.php
        __DIR__ . '/../../vendor/autoload.php',    // /var/www/markai/sistema/vendor/autoload.php
        __DIR__ . '/../../../../vendor/autoload.php' // /var/www/vendor/autoload.php
    ];

    $vendorPath = null;
    foreach ($possibleVendorPaths as $path) {
        if (file_exists($path)) {
            $vendorPath = $path;
            break;
        }
    }

    if (!$vendorPath) {
        $pathsTried = implode(", ", $possibleVendorPaths);
        throw new Exception("Arquivo vendor/autoload.php não encontrado. Caminhos tentados: $pathsTried");
    }
    require $vendorPath;
    file_put_contents(__DIR__ . '/debug_exportar.txt', date('Y-m-d H:i:s') . " - Vendor carregado em: $vendorPath\n", FILE_APPEND);

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    // Consulta os dados
    $query = $pdo->prepare("SELECT nome, telefone, email, endereco, data_nasc, cpf FROM clientes WHERE id_conta = ?");
    $query->execute([$id_conta]);
    $clientes = $query->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents(__DIR__ . '/debug_exportar.txt', date('Y-m-d H:i:s') . " - Clientes encontrados: " . count($clientes) . "\n", FILE_APPEND);

    if (empty($clientes)) {
        throw new Exception('Nenhum cliente encontrado para exportação');
    }

    // Cria planilha
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Cabeçalhos
    $sheet->setCellValue('A1', 'Nome');
    $sheet->setCellValue('B1', 'Telefone');
    $sheet->setCellValue('C1', 'Email');
    $sheet->setCellValue('D1', 'Endereço');
    $sheet->setCellValue('E1', 'Data de Nascimento');
    $sheet->setCellValue('F1', 'CPF');

    // Preenche dados
    $rowNumber = 2;
    foreach ($clientes as $cliente) {
        $sheet->setCellValue('A' . $rowNumber, $cliente['nome'] ?? '');
        $sheet->setCellValue('B' . $rowNumber, $cliente['telefone'] ?? '');
        $sheet->setCellValue('C' . $rowNumber, $cliente['email'] ?? '');
        $sheet->setCellValue('D' . $rowNumber, $cliente['endereco'] ?? '');
        $sheet->setCellValue('E' . $rowNumber, $cliente['data_nasc'] ?? '');
        $sheet->setCellValue('F' . $rowNumber, $cliente['cpf'] ?? '');
        $rowNumber++;
    }
    file_put_contents(__DIR__ . '/debug_exportar.txt', date('Y-m-d H:i:s') . " - Planilha preenchida\n", FILE_APPEND);

    // Limpa output
    if (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();

    // Define headers
    $filename = 'clientes_exportados_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: 0');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    file_put_contents(__DIR__ . '/debug_exportar.txt', date('Y-m-d H:i:s') . " - Download iniciado\n", FILE_APPEND);
    ob_end_flush();
    exit;

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/debug_exportar.txt', date('Y-m-d H:i:s') . " - ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
    if (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao exportar dados: ' . $e->getMessage()]);
    exit;
}
?>