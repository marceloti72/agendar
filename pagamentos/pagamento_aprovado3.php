<?php

if ($id_pg != null) {
	if (@$porc_servico > 0) {
		echo 'Faça o pagamento antes de ir para o agendamento';
		exit();
	}
	require("../sistema/conexao.php");
	$valor_pago = '0';
	$query = $pdo->query("SELECT * FROM agendamentos where id = '$id_pg'");
} else {
	$query = $pdo->query("SELECT * FROM agendamentos where ref_pix = '$ref_pix'");
}
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$cliente = $res[0]['cliente'];
$servico = $res[0]['servico'];
$funcionario = $res[0]['funcionario'];
$data = $res[0]['data'];
$hora = $res[0]['hora'];
$obs = $res[0]['obs'];
$data_lanc = $res[0]['data_lanc'];
$usuario = $res[0]['usuario'];
$status = $res[0]['status'];
$hash = $res[0]['hash'];
$ref_pix = $res[0]['ref_pix'];
$data_agd = $res[0]['data'];
$hora_do_agd = $res[0]['hora'];

if (@$forma_pgto == "pix") {
	$forma_pgto = "Pix";
} else {
	$forma_pgto = "Cartão de Crédito";
}

$query = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$nome_serv = @$res[0]['nome'];
$tempo = @$res[0]['tempo'];

$servico_conc = $nome_serv . " (Site)";

if ($id_pg == "") {
	$pdo->query("INSERT INTO receber SET descricao = '$servico_conc', tipo = 'Serviço', valor = '$valor_pago', data_lanc = CURDATE(), data_venc = CURDATE(), data_pgto = CURDATE(), usuario_lanc = '0', usuario_baixa = '0', foto = 'sem-foto.jpg', pessoa = '$cliente', pago = 'Sim', servico = '$servico', funcionario = '$funcionario', obs = '', pgto = '$forma_pgto', referencia = '$ult_id', id_conta = '$id_conta'");
}
?>
