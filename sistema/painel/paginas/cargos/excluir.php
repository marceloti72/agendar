<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'cargos';

$id = $_POST['id'];
$pdo->query("DELETE from $tabela where id = '$id' and id_conta = '$id_conta'");
echo 'Excluído com Sucesso';
 ?>