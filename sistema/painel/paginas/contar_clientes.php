<?php
require_once("../conexao.php");

try {
    $query = $pdo->query("SELECT COUNT(*) as total FROM clientes WHERE id_conta = :id_conta");
    $query->bindValue(':id_conta', $_SESSION['id_conta']);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['total' => $result['total']]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao contar clientes: ' . $e->getMessage()]);
}
?>