<?php 
require_once("../../sistema/conexao.php");
$tabela = 'receber';

$id_usuario = $_POST['id_usuario'];
$id = $_POST['id'];

$pdo->query("UPDATE $tabela SET pago = 'Sim', usuario_baixa = '$id_usuario', data_pgto = curDate() where id = '$id' and id_conta = '$id_conta'");

echo 'Baixado com Sucesso';
 ?>