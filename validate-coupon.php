<?php
// Conexão com o banco de dados
$db_servidor = 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
$db_usuario = 'skysee';
$db_senha = '9vtYvJly8PK6zHahjPUg';        
$db_nome2 = 'gestao_sistemas';        

try {
    $pdo2 = new PDO("mysql:host=$db_servidor;dbname=$db_nome2;charset=utf8", $db_usuario, $db_senha);
    $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    file_put_contents('/var/www/markai/error_log.txt', date('Y-m-d H:i:s') . " - Erro ao conectar com o banco 'gestao_sistemas': " . $e->getMessage() . "\n", FILE_APPEND);
    throw new Exception("Erro ao conectar com o banco de dados 'gestao_sistemas': " . $e->getMessage());
}

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
    $query = $pdo2->prepare('SELECT * FROM usuarios WHERE cupom = :coupon');
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