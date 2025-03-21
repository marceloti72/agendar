<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../sistema/conexao.php");
$tabela = 'pagar';

$id_usuario = $_POST['id_usuario'];
$id = $_POST['id'];


$pdo->query("UPDATE $tabela SET pago = 'Sim', usuario_baixa = '$id_usuario', data_pgto = curDate() where id = '$id' and id_conta = '$id_conta'");

echo 'Baixado com Sucesso';
 ?>