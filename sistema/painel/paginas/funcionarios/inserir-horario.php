<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'horarios';

$id = $_POST['id'];
$horario = $_POST['horario'];
$data = @$_POST['data'];

if($data == ""){
	$pdo->query("INSERT INTO $tabela SET horario = '$horario', funcionario = '$id', id_conta = '$id_conta'");
}else{
	$pdo->query("INSERT INTO $tabela SET horario = '$horario', funcionario = '$id', data = '$data', id_conta = $id_conta'");
}



echo 'Salvo com Sucesso';
 ?>