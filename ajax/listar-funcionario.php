<?php 
require_once("../sistema/conexao.php");

$funcionario = $_POST['func'];

$query = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
	$nome = $res[0]['nome'];
		
}

echo $nome;

?>

