<?php
$tabela = 'receber';
require_once("../../../conexao.php");
$data_atual = date('Y-m-d');

@session_start();
$id_conta = $_SESSION['id_conta'];
$usuario_logado = @$_SESSION['id_usuario'];

$cliente = $_POST['cliente'];
$data_pgto = $_POST['data_pgto'];
$id = @$_POST['id'];
$valor_serv = $_POST['valor_serv'];
$valor_serv = str_replace(',', '.', $valor_serv);
$funcionario = $_POST['funcionario'];
$servico = $_POST['servico'];
$obs = $_POST['obs'];
$pgto = @$_POST['pgto'];

$valor_serv_restante = $_POST['valor_serv_agd_restante'];
$pgto_restante = $_POST['pgto_restante'];
$data_pgto_restante = $_POST['data_pgto_restante'];

if ($valor_serv_restante == "") {
	$valor_serv_restante = 0;
}

$valor_total_servico = $valor_serv + $valor_serv_restante;


$query = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$valor = $res[0]['valor'];
$comissao = $res[0]['comissao'];
$descricao = $res[0]['nome'];
$descricao2 = 'Comissão - ' . $res[0]['nome'];
$dias_retorno = $res[0]['dias_retorno'];
$data_retorno = date('Y-m-d', strtotime("+$dias_retorno days", strtotime($data_atual)));
$nome_servico = $res[0]['nome'];

//dados do cliente
$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente' and id_conta = '$id_conta' order by id desc limit 2");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$telefone = $res2[0]['telefone'];
$nome_cliente = $res2[0]['nome'];

$query = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$comissao_func = $res[0]['comissao'];

if ($comissao_func > 0) {
	$comissao = $comissao_func;
}

if ($tipo_comissao == 'Porcentagem') {
	$valor_comissao = ($comissao * $valor_total_servico) / 100;
} else {
	$valor_comissao = $comissao;
}

$query = $pdo->query("SELECT * FROM formas_pgto where nome = '$pgto' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$valor_taxa = $res[0]['taxa'];

if ($valor_taxa > 0 and strtotime($data_pgto) <=  strtotime($data_atual)) {
	if ($taxa_sistema == 'Cliente') {
		$valor_serv = $valor_serv + $valor_serv * ($valor_taxa / 100);
	} else {
		$valor_serv = $valor_serv - $valor_serv * ($valor_taxa / 100);
	}
}




$query = $pdo->query("SELECT * FROM formas_pgto where nome = '$pgto_restante' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$valor_taxa = @$res[0]['taxa'];

if ($valor_taxa > 0 and strtotime($data_pgto_restante) <=  strtotime($data_atual)) {
	if ($taxa_sistema == 'Cliente') {
		$valor_serv_restante = $valor_serv_restante + $valor_serv_restante * ($valor_taxa / 100);
	} else {
		$valor_serv_restante = $valor_serv_restante - $valor_serv_restante * ($valor_taxa / 100);
	}
}



if (strtotime($data_pgto) <=  strtotime($data_atual)) {
	$pago = 'Sim';
	$data_pgto2 = $data_pgto;
	$usuario_baixa = $usuario_logado;

	//lançar a conta a pagar para a comissão do funcionário
	$pdo->query("INSERT INTO pagar SET descricao = '$descricao2', tipo = 'Comissão', valor = '$valor_comissao', data_lanc = '$data_pgto', data_venc = '$data_pgto', usuario_lanc = '$usuario_logado', foto = 'sem-foto.jpg', pago = 'Não', funcionario = '$funcionario', servico = '$servico', cliente = '$cliente', id_conta = '$id_conta'");

	


	$telefone = '55' . preg_replace('/[ ()-]+/', '', $telefone);
	if ($msg_agendamento == 'Sim') {
		//agendar mensagem de retorno
		$link_agenda = $url.'agendar/agendamentos?u='.$username;
		$nome_sistema_maiusculo = mb_strtoupper($nome_sistema);

        $mensagem = '*'.$nome_sistema_maiusculo.'*%0A%0A';
		$mensagem .= 'Olá '.$nome_cliente.', tudo bem! 😃%0A';
		$mensagem .= 'Seu último serviço: '.$nome_servico.'%0A%0A';
		$mensagem .= 'Queremos ouvir você!%0A';
		$mensagem .= 'Como foi seu último serviço de conosco?%0A';
		$mensagem .= 'Você teria alguma sugestão de melhoria? Você é muito importante pra gente!%0A';
		$mensagem .= 'Faz um tempo que não nós vemos você aqui. Quando você vai dar aquele tapa no visual? Você merece o que há de melhor, conheça nossos pacotes de desconto. *Promoção Especial apenas hoje!* 👇%0A';
		$mensagem .= 'Acesse e agende: '.$link_agenda;
		
		$data_mensagem = $data_retorno . ' 08:00:00';
		require('../../../../ajax/api-agendar.php');
	}
} else {
	$pago = 'Não';
	$data_pgto2 = '';
	$usuario_baixa = 0;


	if ($lanc_comissao == 'Sempre') {
		//lançar a conta a pagar para a comissão do funcionário
		$pdo->query("INSERT INTO pagar SET descricao = '$descricao2', tipo = 'Comissão', valor = '$valor_comissao', data_lanc = '$data_pgto', data_venc = '$data_pgto', usuario_lanc = '$usuario_logado', foto = 'sem-foto.jpg', pago = 'Não', funcionario = '$funcionario', servico = '$servico', cliente = '$cliente', id_conta = '$id_conta'");
	}
}



if ($valor_serv_restante > 0) {
	if (strtotime($data_pgto_restante) <=  strtotime($data_atual)) {
		$pago_restante = 'Sim';
		$data_pgto2_restante = $data_pgto;
		$usuario_baixa_restante = $usuario_logado;
	} else {
		$pago_restante = 'Não';
		$data_pgto2_restante = '';
		$usuario_baixa_restante = 0;
	}

	//lançar o restante
	$pdo->query("INSERT INTO $tabela SET descricao = '$descricao', tipo = 'Serviço', valor = '$valor_serv_restante', data_lanc = curDate(), data_venc = '$data_pgto_restante', data_pgto = '$data_pgto2_restante', usuario_lanc = '$usuario_logado', usuario_baixa = '$usuario_baixa', foto = 'sem-foto.jpg', pessoa = '$cliente', pago = '$pago_restante', servico = '$servico', funcionario = '$funcionario', obs = '$obs', pgto = '$pgto_restante', id_conta = '$id_conta'");
}



$query2 = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$dias_retorno = $res2[0]['dias_retorno'];
$nome_servico = $res2[0]['nome'];

//dados do cliente
$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente' and id_conta = '$id_conta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$total_cartoes = $res2[0]['cartoes'];
$cartoes = $total_cartoes + 1;
$data_retorno = date('Y-m-d', strtotime("+$dias_retorno days", strtotime($data_atual)));

$pdo->query("INSERT INTO $tabela SET descricao = '$nome_servico', tipo = 'Serviço', valor = '$valor_serv', data_lanc = curDate(), data_venc = '$data_pgto', data_pgto = '$data_pgto2', usuario_lanc = '$usuario_logado', usuario_baixa = '$usuario_baixa', foto = 'sem-foto.jpg', pessoa = '$cliente', pago = '$pago', servico = '$servico', funcionario = '$funcionario', obs = '$obs', pgto = '$pgto', id_conta = '$id_conta'");


$pdo->query("UPDATE clientes SET cartoes = '$cartoes', data_retorno = '$data_retorno', ultimo_servico = '$servico', alertado = 'Não' where id = '$cliente' and id_conta = '$id_conta'");

echo 'Salvo com Sucesso';
