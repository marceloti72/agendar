<?php
$tabela = 'comandas';
require_once("../../../conexao.php");
$data_atual = date('Y-m-d');

@session_start();
$id_conta = $_SESSION['id_conta'];
$usuario_logado = @$_SESSION['id_usuario'];

$cliente = $_POST['cliente'];
$valor = $_POST['valor_serv'];
$id = @$_POST['id'];
$obs = @$_POST['obs'];

$hora_atual = date('H:i:s'); 


if ($valor == "" or $valor <= 0) {
	echo 'Insira serviço ou produto';
	exit();
}

if ($id == "") {
	$query = $pdo->prepare("INSERT INTO $tabela SET cliente = :cliente, valor = :valor, data = curDate(), hora = :hora, funcionario = '$usuario_logado', status = 'Aberta', obs = :obs, id_conta = '$id_conta'");
} else {
	$query = $pdo->prepare("UPDATE $tabela SET cliente = :cliente, valor = :valor, obs = :obs WHERE id = '$id' and id_conta = '$id_conta'");
}



$query->bindValue(":cliente", "$cliente");
$query->bindValue(":valor", "$valor");
$query->bindValue(":obs", "$obs");
$query->bindValue(":hora", "$hora_atual");
$query->execute();
$ult_id = $pdo->lastInsertId();

$pdo->query("UPDATE receber SET comanda = '$ult_id' WHERE func_comanda = '$usuario_logado' and comanda = 0 and id_conta = '$id_conta' ");

//echo 'Salvo com Sucesso*' . $ult_id;
echo 'Salvo com Sucesso';
