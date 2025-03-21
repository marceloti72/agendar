<?php
require_once("../../../conexao.php");

$dataMes = Date('m');
$dataDia = Date('d');
$dataAno = Date('Y');
$data_atual = date('Y-m-d');

$data_semana = date('Y/m/d', strtotime("-7 days", strtotime($data_atual)));

@session_start();
$id_conta = $_SESSION['id_conta'];
$id_usuario = $_SESSION['id_usuario'];

$clientes = $_POST['cli'];

// Buscar os Contatos que serão enviados
if ($clientes == "Teste") {
	$resultado = $pdo->query("SELECT telefone FROM usuarios where nivel = 'Administrador' and telefone != '' and id_conta = '$id_conta'");
} else if ($clientes == "Aniversáriantes Mês") {
	$resultado = $pdo->query("SELECT telefone FROM clientes where month(data_nasc) = '$dataMes'  and telefone != '' and id_conta = '$id_conta'");
} else if ($clientes == "Aniversáriantes Dia") {
	$resultado = $pdo->query("SELECT telefone FROM clientes where month(data_nasc) = '$dataMes' and day(data_nasc) = '$dataDia' and telefone != '' and id_conta = '$id_conta'");
} else if ($clientes == "Clientes Mês") {
	$resultado = $pdo->query("SELECT telefone FROM clientes where month(data_cad) = '$dataMes' and year(data_cad) = '$dataAno' and telefone != '' and id_conta = '$id_conta'");
} else if ($clientes == "Clientes Semana") {
	$resultado = $pdo->query("SELECT telefone FROM clientes where data_cad >= '$data_semana' and telefone != '' and id_conta = '$id_conta'");
} else {
	$resultado = $pdo->query("SELECT telefone FROM clientes where telefone != '' and id_conta = '$id_conta'");
}

$res = $resultado->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);

echo $total_reg;
