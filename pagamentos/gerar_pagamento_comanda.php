<?php
include("config.php");
require("../sistema/conexao.php");

header('Content-Type: application/json');

$id_comanda = $_GET['id_comanda'];
if (!$id_comanda) {
    echo json_encode(['error' => 'ID da comanda não fornecido']);
    exit();
}

$url = "https://" . $_SERVER['HTTP_HOST'] . "/";

// Consultar a tabela receber para obter o id_receber
$query = $pdo->query("SELECT * FROM receber WHERE comanda = '$id_comanda' AND tipo = 'Comanda'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);

if ($total_reg == 0) {
    echo json_encode(['error' => 'Comanda não encontrada na tabela receber']);
    exit();
}

$id_receber = $res[0]['id'];
$paymentUrl = $url . "pagamentos/index.php?id_receber=$id_receber";

echo json_encode(['url' => $paymentUrl]);
?>