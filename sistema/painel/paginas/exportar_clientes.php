<?php
// Ativa exibição de erros para depuração (remova em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log'); // Log em arquivo no mesmo diretório

@session_start();

// Log inicial
file_put_contents('debug_exportar.txt', date('Y-m-d H:i:s') . " - Script iniciado\n", FILE_APPEND);

try {
    // Verifica sessão
    if (!isset($_SESSION['id_conta'])) {
        throw new Exception('Sessão id_conta não definida');
    }
    $id_conta = $_SESSION['id_conta'];
    file_put_contents('debug_exportar.txt', "ID Conta: $id_conta\n", FILE_APPEND);

    // Carrega conexão (ajuste o caminho se necessário)
    require_once("../conexao.php");
    file_put_contents('debug_exportar.txt', "Conexão carregada\n", FILE_APPEND);

    // Testa PDO
    $pdo->query("SELECT 1");
    file_put_contents('debug_exportar.txt', "PDO OK\n", FILE_APPEND);

    // Carrega PhpSpreadsheet (ajuste o caminho se necessário - teste com './vendor/' se estiver na raiz)
    $vendorPath = '../../../vendor/autoload.php'; // Ajuste conforme sua estrutura
    if (!file_exists($vendorPath)) {
        throw new Exception("Arquivo vendor/autoload.php não encontrado em: $vendorPath");
    }
    require $vendorPath;
    file_put_contents('debug_exportar.txt', "Vendor carregado\n", FILE_APPEND);

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    // Consulta os dados
    $query = $pdo->prepare("SELECT nome, telefone, email, endereco, data_nasc, cpf FROM clientes WHERE id_conta = ?");
    $query->execute([$id_conta]);
    $clientes = $query->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('debug_exportar.txt', "Clientes encontrados: " . count($clientes) . "\n", FILE_APPEND);

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
    file_put_contents('debug_exportar.txt', "Planilha preenchida\n", FILE_APPEND);

    // Limpa output e define headers
    if (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();

    $filename = 'clientes_exportados_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: 0');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    ob_end_flush();
    file_put_contents('debug_exportar.txt', "Download iniciado\n", FILE_APPEND);
    exit;

} catch (Exception $e) {
    // Log do erro
    file_put_contents('debug_exportar.txt', "ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Limpa output
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    echo "Erro 500: " . $e->getMessage();
    exit;
}
?>