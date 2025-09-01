<?php
require_once("../../../conexao.php");
require '../../../../vendor/autoload.php';

$id_conta = $_SESSION['id_conta'];

use PhpOffice\PhpSpreadsheet\IOFactory;

// Retorna uma resposta em formato JSON
header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => ''];

// Função para aplicar máscara no telefone
function formatarTelefone($telefone) {
    $telefone = preg_replace('/\D/', '', $telefone);
    if (strlen($telefone) == 11) {
        return sprintf('(%s) %s-%s', substr($telefone, 0, 2), substr($telefone, 2, 5), substr($telefone, 7, 4));
    }
    return $telefone;
}

if (isset($_FILES['arquivo_excel']) && $_FILES['arquivo_excel']['error'] === UPLOAD_ERR_OK) {
    $inputFileName = $_FILES['arquivo_excel']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        array_shift($rows); // Ignora o cabeçalho
        
        $count = 0; // Contador de importações
        $sql = "INSERT INTO clientes (nome, telefone, email, endereco, data_nasc, data_cad, cpf, id_conta) 
                VALUES (:nome, :telefone, :email, :endereco, :data_nasc, :data_cad, :cpf, :id_conta)";
        $stmt = $pdo->prepare($sql);
        $data_cad = date('Y-m-d');

        foreach ($rows as $row) {
            $nome = $row[0] ?? '';
            $telefone = formatarTelefone($row[1] ?? '');
            $email = $row[2] ?? '';
            $endereco = $row[3] ?? '';
            $data_nasc = $row[4] ? date('Y-m-d', strtotime($row[4])) : null;
            $cpf = preg_replace('/\D/', '', $row[5] ?? '');

            // Executa a inserção e incrementa o contador se for bem-sucedido
            if ($stmt->execute([
                ':nome' => $nome,
                ':telefone' => $telefone,
                ':email' => $email,
                ':endereco' => $endereco,
                ':data_nasc' => $data_nasc,
                ':data_cad' => $data_cad,
                ':cpf' => $cpf,
                ':id_conta' => $id_conta
            ])) {
                $count++;
            }
        }
        $response['status'] = 'success';
        $response['message'] = 'Dados importados com sucesso!';
        $response['imported_count'] = $count;

    } catch (Exception $e) {
        $response['message'] = "Erro ao importar dados: " . $e->getMessage();
    }
} else {
    $response['message'] = "Erro: Nenhum arquivo foi enviado ou ocorreu um erro no upload.";
}

echo json_encode($response);
?>