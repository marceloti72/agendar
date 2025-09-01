<?php
@session_start();
require_once("../../../conexao.php");

$id_conta = $_SESSION['id_conta'];

try {
    $query = $pdo->prepare("SELECT COUNT(*) as total FROM clientes WHERE id_conta = ?");
    $query->execute([$id_conta]);
    $result = $query->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['total' => $result['total']]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao contar clientes: ' . $e->getMessage()]);
}
?>