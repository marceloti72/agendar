<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");

$func = $_POST['func'];

$query = $pdo->query("SELECT * FROM servicos_func where funcionario = '$func' and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
	for($i=0; $i < @count($res); $i++){
		$serv = $res[$i]['servico'];

		$query2 = $pdo->query("SELECT * FROM servicos where id = '$serv' and ativo = 'Sim' and id_conta = '$id_conta' ");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);	
		$nome_func = $res2[0]['nome'];

		echo '<option value="'.$serv.'">'.$nome_func.'</option>';
	}		
}else{
	echo '<option value="">Nenhum Serviço</option>';
}


?>

