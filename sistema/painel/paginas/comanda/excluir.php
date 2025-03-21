<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'comandas';
$id = $_POST['id'];

$query2 = $pdo->query("SELECT * FROM receber where comanda = '$id' and id_conta = '$id_conta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$total_reg2 = @count($res2);
if($total_reg2 > 0){
	$pdo->query("DELETE from receber where comanda = '$id' and id_conta = '$id_conta'");
	// echo 'Primeiro exclua produtos e serviços desta comanda para depois excluir a comanda!';
	// exit();
}

$pdo->query("DELETE from $tabela where id = '$id' and id_conta = '$id_conta'");
echo 'Excluído com Sucesso';
 ?>