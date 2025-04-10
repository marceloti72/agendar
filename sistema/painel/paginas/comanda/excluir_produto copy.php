<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'receber';


header('Content-Type: application/json'); // Define tipo de resposta
// Resposta padrão inicializada como erro
$response = ['success' => false, 'message' => 'Erro desconhecido ao excluir produto.'];

$id_conta = $_SESSION['id_conta'];

$id = isset($_POST['id_receber']) ? $_POST['id_receber'] : 0;

// Valida o ID recebido
if ($id <= 0) {
    $response['message'] = 'ID inválido para exclusão.';
    echo json_encode($response);
    exit;
}

$query = $pdo->query("SELECT * FROM receber where id = '$id' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$produto = $res[0]['produto'];
$quantidade = $res[0]['quantidade'];

$query = $pdo->query("SELECT * FROM produtos where id = '$produto' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$estoque = $res[0]['estoque'];

//atualizar estoque do produto
$total_estoque = $estoque + $quantidade;
$pdo->query("UPDATE produtos SET estoque = '$total_estoque' WHERE id = '$produto' and id_conta = '$id_conta'");

$pdo->query("DELETE from $tabela where id = '$id' and id_conta = '$id_conta'");

$response['success'] = true;
$response['message'] = 'Produto removido com sucesso!';

echo json_encode($response);
 ?>