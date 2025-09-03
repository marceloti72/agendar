<?php
session_start();
require_once("../../../conexao.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id_conta'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Por favor, faça login novamente.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

if (!isset($_POST['valor_abertura'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valor de abertura não fornecido.']);
    exit;
}

$id_conta = $_SESSION['id_conta'];
$operator = $_SESSION['id_usuario'];
$opening_date = date('Y-m-d H:i:s');
$opening_value = floatval($_POST['valor_abertura']);
$opening_user = $_SESSION['id_usuario'];
$obs = trim($_POST['obs'] ?? '');

try {
    // Verifica se o caixa já está aberto para evitar duplicação
    $sql_check = "SELECT id FROM caixa WHERE id_conta = :id_conta AND data_fechamento IS NULL LIMIT 1";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
    $stmt_check->execute();
    if ($stmt_check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Já existe um caixa aberto.']);
        exit;
    }

    $pdo->beginTransaction();
    
    $sql = "INSERT INTO caixa (operador, data_abertura, valor_abertura, usuario_abertura, obs, id_conta)
            VALUES (:operador, :data_abertura, :valor_abertura, :usuario_abertura, :obs, :id_conta)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':operador', $operator, PDO::PARAM_INT);
    $stmt->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
    $stmt->bindParam(':data_abertura', $opening_date);
    $stmt->bindParam(':valor_abertura', $opening_value);
    $stmt->bindParam(':usuario_abertura', $opening_user, PDO::PARAM_INT);
    $stmt->bindParam(':obs', $obs);
    $stmt->execute();
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Caixa aberto com sucesso! 🎉']);
    
} catch(PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Erro ao abrir caixa: " . $e->getMessage()]);
}
?>
