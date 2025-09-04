<?php
session_start();
require_once("../../../conexao.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id_conta']) || !isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Por favor, faça login novamente.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

if (!isset($_POST['caixa_id']) || !isset($_POST['sangria_valor'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos. Por favor, forneça o ID do caixa e o valor da sangria.']);
    exit;
}

$id_caixa = intval($_POST['caixa_id']);
$valor_sangria = floatval($_POST['sangria_valor']);

if ($valor_sangria <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'O valor da sangria deve ser maior que zero.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Busca o valor atual de sangrias do caixa
    $sql_fetch = "SELECT sangrias FROM caixa WHERE id = :id_caixa AND id_conta = :id_conta AND data_fechamento IS NULL";
    $stmt_fetch = $pdo->prepare($sql_fetch);
    $stmt_fetch->bindParam(':id_caixa', $id_caixa, PDO::PARAM_INT);
    $stmt_fetch->bindParam(':id_conta', $_SESSION['id_conta'], PDO::PARAM_INT);
    $stmt_fetch->execute();
    $current_sangrias_data = $stmt_fetch->fetch(PDO::FETCH_ASSOC);

    if (!$current_sangrias_data) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Caixa não encontrado ou já fechado.']);
        exit;
    }

    $current_sangrias = $current_sangrias_data['sangrias'] ?? 0;
    $new_sangrias_total = $current_sangrias + $valor_sangria;

    // 2. Atualiza o valor de sangrias na tabela caixa
    $sql_update = "UPDATE caixa SET sangrias = :new_sangrias WHERE id = :id_caixa";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':new_sangrias', $new_sangrias_total);
    $stmt_update->bindParam(':id_caixa', $id_caixa, PDO::PARAM_INT);
    $stmt_update->execute();

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Sangria registrada com sucesso!', 'new_sangrias_total' => $new_sangrias_total]);

} catch(PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Erro ao registrar sangria: " . $e->getMessage()]);
}
?>
