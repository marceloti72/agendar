<?php
header('Content-Type: application/json');
require_once("../sistema/conexao.php");
@session_start();
$id_conta = $_SESSION['id_conta'];


$telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
$id_conta = isset($_POST['id_conta']) ? intval($_POST['id_conta']) : 0;

if (empty($telefone)) {
    echo json_encode(['error' => 'Telefone ou ID da conta inválido']);
    exit;
}

// Check if client exists
$query_cliente = $pdo->prepare("SELECT id, nome, cpf, email FROM clientes WHERE telefone = :telefone AND id_conta = :id_conta");
$query_cliente->execute([':telefone' => $telefone, ':id_conta' => $id_conta]);
$cliente = $query_cliente->fetch(PDO::FETCH_ASSOC);

$response = [];

if ($cliente) {
    $response['cliente'] = [
        'id' => $cliente['id'],
        'nome' => $cliente['nome'],
        'cpf' => $cliente['cpf'],
        'email' => $cliente['email']
    ];

    // Check if client is already a subscriber
    $query_assinante = $pdo->prepare("SELECT COUNT(*) as total FROM assinantes WHERE id_cliente = :id_cliente AND id_conta = :id_conta");
    $query_assinante->execute([':id_cliente' => $cliente['id'], ':id_conta' => $id_conta]);
    $assinante = $query_assinante->fetch(PDO::FETCH_ASSOC);

    $response['is_assinante'] = $assinante['total'] > 0;
} else {
    $response['cliente'] = null;
}

echo json_encode($response);
exit;
?>