<?php
require_once('../sistema/conexao.php'); // Sua conexão PDO
@session_start(); // Se precisar associar ao usuário logado

// Pega os dados JSON enviados pelo cliente
$json = file_get_contents('php://input');
$subscription = json_decode($json, true); // Decodifica como array associativo

// Validação básica
if (!isset($subscription['endpoint']) || !isset($subscription['keys']['p256dh']) || !isset($subscription['keys']['auth'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Dados de inscrição inválidos.']);
    exit;
}

$endpoint = $subscription['endpoint'];
$p256dh = $subscription['keys']['p256dh'];
$auth = $subscription['keys']['auth'];
$id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null; // Opcional: associar ao usuário

try {
    // Use INSERT ... ON DUPLICATE KEY UPDATE para evitar duplicatas se o endpoint for único
    // Crie uma tabela 'push_subscriptions' com colunas: id, user_id (opcional), endpoint (UNIQUE), p256dh, auth, data_cadastro
    $sql = "INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth, data_cadastro)
            VALUES (:user_id, :endpoint, :p256dh, :auth, NOW())
            ON DUPLICATE KEY UPDATE p256dh = :p256dh_update, auth = :auth_update"; // Atualiza se o endpoint já existe

    $query = $pdo->prepare($sql);
    $query->bindValue(':user_id', $id_usuario); // Pode ser NULL
    $query->bindValue(':endpoint', $endpoint);
    $query->bindValue(':p256dh', $p256dh);
    $query->bindValue(':auth', $auth);
    // Bind para a parte UPDATE
    $query->bindValue(':p256dh_update', $p256dh);
    $query->bindValue(':auth_update', $auth);

    $query->execute();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
     error_log("Erro ao salvar inscrição push: " . $e->getMessage()); // Loga o erro
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar inscrição no banco de dados.']);
}
?>