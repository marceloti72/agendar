<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'cargos';

$id = $_POST['id'];
$nome = $_POST['nome'];


//validar nome
$query = $pdo->query("SELECT * from $tabela where nome = '$nome' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Nome já Cadastrado, escolha outro!!';
	exit();
}


if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, id_conta = '$id_conta'");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET nome = :nome WHERE id = '$id' and id_conta = '$id_conta'");
}

$query->bindValue(":nome", "$nome");
$query->execute();

echo 'Salvo com Sucesso';
 ?>