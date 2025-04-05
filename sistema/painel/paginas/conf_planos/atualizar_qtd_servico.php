<?php
require_once("../../../conexao.php");
@session_start();
$id_conta_corrente = @$_SESSION['id_conta'];

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Erro desconhecido.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido.';
    echo json_encode($response);
    exit;
}

$id_plano_servico = isset($_POST['id_plano_servico']) ? (int)$_POST['id_plano_servico'] : 0;
$quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : -1; // Usa -1 para detectar se foi enviado
$id_conta_form = isset($_POST['id_conta']) ? (int)$_POST['id_conta'] : 0;

// Validação
if ($id_plano_servico <= 0 || $quantidade < 0 ) {
    $response['message'] = 'Dados inválidos para atualizar quantidade.';
    echo json_encode($response);
    exit;
}

try {
    // Atualiza a quantidade da ligação específica
    $query = $pdo->prepare("UPDATE planos_servicos SET quantidade = :quantidade WHERE id = :id_ps AND id_conta = :id_conta");
    $query->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
    $query->bindValue(':id_ps', $id_plano_servico, PDO::PARAM_INT);
    $query->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query->execute();

    // rowCount pode ser 0 se o valor for o mesmo, então consideramos sucesso se não houver exceção
    $response['success'] = true;
    $response['message'] = 'Quantidade atualizada.';


} catch (PDOException $e) {
    $response['message'] = 'Erro ao atualizar quantidade: ' . $e->getMessage();
    error_log("Erro SQL em atualizar_qtd_servico: " . $e->getMessage());
}

echo json_encode($response);
?>