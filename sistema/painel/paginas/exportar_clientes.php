<?php
@session_start();
require_once("../conexao.php");
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// ID da conta vindo da sessão
$id_conta = $_SESSION['id_conta'];

try {
    // Consulta os dados dos clientes
    $query = $pdo->prepare("SELECT nome, telefone, email, endereco, data_nasc, cpf FROM clientes WHERE id_conta = :id_conta");
    $query->bindValue(':id_conta', $id_conta);
    $query->execute();
    $clientes = $query->fetchAll(PDO::FETCH_ASSOC);

    // Cria uma nova planilha
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
        $sheet->setCellValue('A' . $rowNumber, $cliente['nome']);
        $sheet->setCellValue('B' . $rowNumber, $cliente['telefone']);
        $sheet->setCellValue('C' . $rowNumber, $cliente['email']);
        $sheet->setCellValue('D' . $rowNumber, $cliente['endereco']);
        $sheet->setCellValue('E' . $rowNumber, $cliente['data_nasc']);
        $sheet->setCellValue('F' . $rowNumber, $cliente['cpf']);
        $rowNumber++;
    }

    // Salva o arquivo
    $writer = new Xlsx($spreadsheet);
    $filename = 'clientes_exportados_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    echo "Erro ao exportar dados: " . $e->getMessage();
}
?>