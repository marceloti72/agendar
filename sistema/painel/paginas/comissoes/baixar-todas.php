<?php
require_once("../../../conexao.php");
$tabela = 'pagar';
@session_start();
$id_conta = $_SESSION['id_conta'];
$id_usuario = $_SESSION['id_usuario'];

$dataInicial = @$_POST['data_inicial'];
$dataFinal = @$_POST['data_final'];
$funcionario = @$_POST['id_funcionario'];

$pdo->query("UPDATE $tabela SET pago = 'Sim', usuario_baixa = '$id_usuario', data_pgto = curDate() where data_lanc >= '$dataInicial' and data_lanc <= '$dataFinal' and pago = 'Não' and funcionario LIKE '$funcionario' and tipo = 'Comissão' and id_conta = '$id_conta'");

echo 'Baixado com Sucesso';
