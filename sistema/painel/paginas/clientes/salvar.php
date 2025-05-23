<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
$id_usuario = $_SESSION['id_usuario'];
require_once("../../../conexao.php");
$tabela = 'clientes';

$id = $_POST['id'];
$nome = $_POST['nome'];
$telefone = $_POST['telefone'];
$data_nasc = $_POST['data_nasc'];
$endereco = $_POST['endereco'];
$cartoes = $_POST['cartao'];
$cpf = $_POST['cpf'];

//validar email
$query = $pdo->query("SELECT * from $tabela where telefone = '$telefone' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Telefone já Cadastrado, escolha outro!!';
	exit();
}


if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, telefone = :telefone, data_cad = curDate(), data_nasc = '$data_nasc', cartoes = '$cartoes', endereco = :endereco, alertado = 'Não', cpf = :cpf, origem = :origem, id_conta = '$id_conta'");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, telefone = :telefone, data_nasc = '$data_nasc', cartoes = '$cartoes', endereco = :endereco, cpf = :cpf, origem = :origem WHERE id = '$id' and id_conta = '$id_conta'");
}

$query->bindValue(":nome", "$nome");
$query->bindValue(":telefone", "$telefone");
$query->bindValue(":endereco", "$endereco");
$query->bindValue(":cpf", "$cpf");
$query->bindValue(":origem", "$id_usuario");
$query->execute();

echo 'Salvo com Sucesso';
 ?>