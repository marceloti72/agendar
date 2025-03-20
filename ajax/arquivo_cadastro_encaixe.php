<?php
@session_start();
require_once("../sistema/conexao.php");

// Verificação de segurança básica (muito importante)
if (!isset($_POST['nome'], $_POST['whatsapp'], $_POST['data'], $_POST['profissional'], $_POST['id_conta'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit;
}

$nome = $_POST['nome'];
$whatsapp = $_POST['whatsapp'];
$data = $_POST['data'];
$profissional = $_POST['profissional'];
$id_conta = $_POST['id_conta'];


//Verificação se é numero de WhatsApp válido
$whatsappRegex = '/^\(\d{2}\)\s?\d{4,5}-\d{4}$/';
 if (!preg_match($whatsappRegex, $whatsapp)) {
     http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Número de WhatsApp inválido!']);
    exit;
}

// Sanitização (proteção contra SQL injection)
$nome = $pdo->quote($nome);
$whatsapp = $pdo->quote($whatsapp);
$data = $pdo->quote($data);
$profissional = $pdo->quote($profissional);
$id_conta = $pdo->quote($id_conta);

// Inserção no banco de dados
try {
    $query = $pdo->prepare("INSERT INTO encaixe (nome, whatsapp, data, profissional, alertado, id_conta) VALUES ($nome, $whatsapp, $data, $profissional, 'Não', $id_conta)");
    $query->execute();

    // Resposta de sucesso
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Erro ao inserir no banco de dados: ' . $e->getMessage()]);
    // Em produção, *NUNCA* mostre o erro detalhado do banco de dados para o usuário.
    // Registre o erro em um log para depuração.
}

?>