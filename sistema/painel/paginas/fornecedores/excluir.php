<?php 
require_once("../../../conexao.php");
$tabela = 'fornecedores';

$id = $_POST['id'];
$pdo->query("DELETE from $tabela where id = '$id' and id_conta = '$id_conta'");
echo 'Excluído com Sucesso';
 ?>