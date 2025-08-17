<?php
require_once 'sistema/conexao.php'; // Ajuste o caminho conforme necessário

header('Content-Type: application/json');

$response = ['error' => 'Erro desconhecido'];

try {
    // Receber o corpo da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    $coupon = isset($input['coupon']) ? trim($input['coupon']) : null;

    // Validar se o cupom foi fornecido
    if (!$coupon) {
        http_response_code(400);
        $response['error'] = 'Cupom é obrigatório';
        error_log('Erro: cupom é obrigatório');
        echo json_encode($response);
        exit;
    }

    // Consultar o cupom na tabela usuarios
    error_log('Verificando cupom: ' . $coupon);
    $query = $pdo->prepare('SELECT * FROM usuarios WHERE cupom = :coupon');
    $query->bindValue(':coupon', $coupon, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        error_log('Cupom válido encontrado: ' . $coupon);
        echo json_encode(['valid' => true]);
    } else {
        error_log('Cupom inválido: ' . $coupon);
        http_response_code(400);
        $response['error'] = 'Cupom não existente';
        echo json_encode($response);
    }
} catch (Exception $e) {
    error_log('Erro ao validar cupom: ' . $e->getMessage());
    http_response_code(500);
    $response['error'] = 'Erro ao validar cupom: ' . $e->getMessage();
    echo json_encode($response);
}
?>