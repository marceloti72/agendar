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
        
        $count_imported = 0; // Contador de clientes importados com sucesso
        $count_skipped = 0; // Contador de clientes ignorados por telefone repetido
        
        // Prepara a query de verificação de telefone
        $sql_check = "SELECT id FROM clientes WHERE telefone = :telefone AND id_conta = :id_conta";
        $stmt_check = $pdo->prepare($sql_check);
        
        // Prepara a query de inserção
        $sql_insert = "INSERT INTO clientes (nome, telefone, email, endereco, data_nasc, data_cad, cpf, id_conta) 
                       VALUES (:nome, :telefone, :email, :endereco, :data_nasc, :data_cad, :cpf, :id_conta)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $data_cad = date('Y-m-d');

        foreach ($rows as $row) {
            $nome = $row[0] ?? '';
            $telefone = formatarTelefone($row[1] ?? '');
            $email = $row[2] ?? '';
            $endereco = $row[3] ?? '';
            $data_nasc = $row[4] ? date('Y-m-d', strtotime($row[4])) : null;
            $cpf = preg_replace('/\D/', '', $row[5] ?? '');
            
            // 1. Verifica se o telefone já existe
            $stmt_check->execute([
                ':telefone' => $telefone,
                ':id_conta' => $id_conta
            ]);
            $cliente_existente = $stmt_check->fetchColumn();

            // Se o telefone já existe, pula para a próxima iteração
            if ($cliente_existente) {
                $count_skipped++;
                continue; // Pula o restante do loop para esta linha
            }

            // 2. Se o telefone não existe, executa a inserção
            if ($stmt_insert->execute([
                ':nome' => $nome,
                ':telefone' => $telefone,
                ':email' => $email,
                ':endereco' => $endereco,
                ':data_nasc' => $data_nasc,
                ':data_cad' => $data_cad,
                ':cpf' => $cpf,
                ':id_conta' => $id_conta
            ])) {
                $count_imported++;
            }
        }

        $response['status'] = 'success';
        $response['message'] = 'Dados importados com sucesso!';
        $response['imported_count'] = $count_imported;
        $response['skipped_count'] = $count_skipped; // Adiciona a contagem de ignorados

    } catch (Exception $e) {
        $response['message'] = "Erro ao importar dados: " . $e->getMessage();
    }
} else {
    $response['message'] = "Erro: Nenhum arquivo foi enviado ou ocorreu um erro no upload.";
}

echo json_encode($response);