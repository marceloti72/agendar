<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'dias_bloqueio';


$data = $_POST['data'];
$func = $_POST['id'];

$query = $pdo->query("SELECT * FROM $tabela where data = '$data' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	echo 'Data já adicionada!';
	exit();
}

$pdo->query("INSERT INTO $tabela SET data = '$data', funcionario = '0', usuario = '$func', id_conta = '$id_conta'");

echo 'Salvo com Sucesso';
 ?>