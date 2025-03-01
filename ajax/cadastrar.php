<?php 
require_once("../sistema/conexao.php");
$tabela = 'clientes';

$nome = $_POST['nome'];
$telefone = $_POST['telefone'];

//validar tel
$query = $pdo->query("SELECT * from $tabela where telefone = '$telefone' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
	echo 'Telefone já Cadastrado, você já está cadastrado!!';
	exit();
}

$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, telefone = :telefone, data_cad = curDate(), cartoes = '0', alertado = 'Não', id_conta = :id_conta");

$query->bindValue(":nome", "$nome");
$query->bindValue(":telefone", "$telefone");
$query->bindValue(":id_conta", "$id_conta");
$query->execute();

echo 'Salvo com Sucesso';

 ?>