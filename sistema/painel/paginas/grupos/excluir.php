<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'grupo_acessos';

$id = $_POST['id'];

$query2 = $pdo->query("SELECT * FROM acessos where grupo = '$id' and id_conta = '$id_conta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$total_reg2 = @count($res2);
if($total_reg2 > 0){
	echo 'Não é possível excluir o registro, pois existem acessos relacionados a ele primeiro exclua os acessos e depois exclua esse grupo!';
	exit();
}

$pdo->query("DELETE from $tabela where id = '$id' and id_conta = '$id_conta'");
echo 'Excluído com Sucesso';
?>