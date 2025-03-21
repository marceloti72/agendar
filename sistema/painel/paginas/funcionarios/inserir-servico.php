<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'servicos_func';

$id = $_POST['id'];
$servico = $_POST['servico'];
$func = $_POST['id'];

$query = $pdo->query("SELECT * FROM $tabela where funcionario = '$func' and servico = '$servico' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	echo 'Serviço já adicionado ao Funcionário!';
	exit();
}

$pdo->query("INSERT INTO $tabela SET servico = '$servico', funcionario = '$func', id_conta = '$id_conta'");

echo 'Salvo com Sucesso';
 ?>