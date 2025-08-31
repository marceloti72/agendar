<?php
require_once("../conexao.php");
require '../../../vendor/autoload.php';

$id_conta = $_SESSION['id_conta'];

use PhpOffice\PhpSpreadsheet\IOFactory;


// Função para aplicar máscara no telefone
function formatarTelefone($telefone) {
    $telefone = preg_replace('/\D/', '', $telefone); // Remove caracteres não numéricos
    if (strlen($telefone) == 11) {
        return sprintf('(%s) %s-%s', substr($telefone, 0, 2), substr($telefone, 2, 5), substr($telefone, 7, 4));
    }
    return $telefone; // Retorna sem formatação se não for possível
}

// Caminho do arquivo Excel
$inputFileName = 'clientes.xlsx';

try {
    // Carrega a planilha
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    // Ignora a primeira linha (cabeçalho)
    array_shift($rows);

    // Prepara a query de inserção
    $sql = "INSERT INTO clientes (nome, telefone, email, endereco, data_nasc, data_cad, cpf, id_conta) 
            VALUES (:nome, :telefone, :email, :endereco, :data_nasc, :data_cad, :cpf, :id_conta)";
    $stmt = $pdo->prepare($sql);

    // Data atual para data_cad
    $data_cad = date('Y-m-d');

    // Itera sobre as linhas da planilha
    foreach ($rows as $row) {
        $nome = $row[0] ?? '';
        $telefone = formatarTelefone($row[1] ?? '');
        $email = $row[2] ?? '';
        $endereco = $row[3] ?? '';
        $data_nasc = $row[4] ? date('Y-m-d', strtotime($row[4])) : null; // Converte a data
        $cpf = preg_replace('/\D/', '', $row[5] ?? ''); // Remove formatação do CPF

        // Executa a inserção
        $stmt->execute([
            ':nome' => $nome,
            ':telefone' => $telefone,
            ':email' => $email,
            ':endereco' => $endereco,
            ':data_nasc' => $data_nasc,
            ':data_cad' => $data_cad,
            ':cpf' => $cpf,
            ':id_conta' => $id_conta
        ]);
    }

    echo "Dados importados com sucesso!";
} catch (Exception $e) {
    echo "Erro ao importar dados: " . $e->getMessage();
}
?>