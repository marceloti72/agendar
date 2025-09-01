<?php
// Aumenta o limite de tempo de execução e memória para exportações grandes.
set_time_limit(300);
ini_set('memory_limit', '512M');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Retorna uma resposta em formato JSON para erros, caso o script falhe antes de enviar o arquivo.
header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => ''];

try {
    @session_start();

    // Verifica se a sessão e a conexão existem.
    if (!isset($_SESSION['id_conta'])) {
        throw new Exception('Sessão id_conta não definida');
    }
    $id_conta = $_SESSION['id_conta'];

    $conexaoPath = __DIR__ . '/../../../conexao.php';
    if (!file_exists($conexaoPath)) {
        throw new Exception("Arquivo conexao.php não encontrado em: $conexaoPath");
    }
    require_once($conexaoPath);

    // Verifica se a biblioteca PhpSpreadsheet foi carregada.
    $vendorPath = __DIR__ . '/../../../../vendor/autoload.php';
    if (!file_exists($vendorPath)) {
        throw new Exception("Arquivo vendor/autoload.php não encontrado em: $vendorPath");
    }
    require $vendorPath;

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    // Consulta os dados
    $query = $pdo->prepare("SELECT nome, telefone, email, endereco, data_nasc, cpf FROM clientes WHERE id_conta = :id_conta");
    $query->bindValue(':id_conta', $id_conta, PDO::PARAM_INT);
    $query->execute();
    $clientes = $query->fetchAll(PDO::FETCH_ASSOC);

    if (empty($clientes)) {
        throw new Exception('Nenhum cliente encontrado para exportação');
    }

    // Cria a planilha
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Define os cabeçalhos
    $sheet->setCellValue('A1', 'Nome');
    $sheet->setCellValue('B1', 'Telefone');
    $sheet->setCellValue('C1', 'Email');
    $sheet->setCellValue('D1', 'Endereço');
    $sheet->setCellValue('E1', 'Data de Nascimento');
    $sheet->setCellValue('F1', 'CPF');

    // Preenche os dados
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

    // Limpa o output e define os headers para download
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    $filename = 'clientes_exportados_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    exit;

} catch (Exception $e) {
    // Em caso de erro, retorna uma resposta JSON
    $response['message'] = "Erro ao exportar dados: " . $e->getMessage() . " na linha " . $e->getLine();
    http_response_code(500);
    echo json_encode($response);
    exit;
}
?>