<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");

$quantidade = $_POST['quant'];
$produto = $_POST['produto'];

$query = $pdo->query("SELECT * FROM produtos where id = '$produto' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$valor = $res[0]['valor_venda'];

echo $valor * $quantidade;
 ?>