<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");

$id_usuario = $_POST['idusuario'];
$id_permissao = $_POST['idpermissao'];


$query = $pdo->query("SELECT * FROM usuarios_permissoes where permissao = '$id_permissao' and usuario = '$id_usuario' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	$pdo->query("DELETE FROM usuarios_permissoes where permissao = '$id_permissao' and usuario = '$id_usuario' and id_conta = '$id_conta'");
}else{
	$pdo->query("INSERT INTO usuarios_permissoes SET permissao = '$id_permissao', usuario = '$id_usuario', id_conta = '$id_conta'");
}
?>