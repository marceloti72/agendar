<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'servicos';

$id = $_POST['id'];

$query = $pdo->query("SELECT * FROM $tabela where id = '$id' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$foto = $res[0]['foto'];

if($foto != "sem-foto.jpg"){
	@unlink('../../img/servicos/'.$foto);
}

$pdo->query("DELETE from $tabela where id = '$id' and id_conta = '$id_conta'");
echo 'Excluído com Sucesso';
 ?>