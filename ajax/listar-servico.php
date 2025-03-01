<?php 
require_once("../sistema/conexao.php");

$servico = $_POST['serv'];

$query = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
	$nome = $res[0]['nome'];
		
}

echo $nome;

?>

