<?php
require_once("../../../conexao.php");
$tabela = 'receber';
@session_start();
$id_usuario = $_SESSION['id_usuario'];

$data_atual = date('Y-m-d');

$id = $_POST['id'];

$query = $pdo->query("SELECT * FROM $tabela where id = '$id' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$funcionario = $res[0]['funcionario'];
$servico = $res[0]['servico'];
$cliente = $res[0]['pessoa'];
$descricao = 'Comissão - ' . $res[0]['descricao'];
$valor_conta = $res[0]['valor'];
$pgto = $res[0]['pgto'];

$query = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$valor = $res[0]['valor'];
$comissao = $res[0]['comissao'];
$dias_retorno = $res[0]['dias_retorno'];
$nome_servico = $res[0]['nome'];

$data_retorno = date('Y-m-d', strtotime("+$dias_retorno days", strtotime($data_atual)));

$query = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$comissao_func = $res[0]['comissao'];

if ($comissao_func > 0) {
	$comissao = $comissao_func;
}

if ($tipo_comissao == 'Porcentagem') {
	$valor_comissao = ($comissao * $valor_conta) / 100;
} else {
	$valor_comissao = $comissao;
}


$query = $pdo->query("SELECT * FROM formas_pgto where nome = '$pgto' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$valor_taxa = @$res[0]['taxa'];

if ($valor_taxa > 0) {
	if ($taxa_sistema == 'Cliente') {
		$valor_serv = $valor_serv + $valor_serv * ($valor_taxa / 100);
	} else {
		$valor_serv = $valor_serv - $valor_serv * ($valor_taxa / 100);
	}
}



$pdo->query("UPDATE $tabela SET pago = 'Sim', usuario_baixa = '$id_usuario', data_pgto = curDate() where id = '$id' and id_conta = '$id_conta'");

if ($lanc_comissao != 'Sempre') {
	//lançar a conta a pagar para a comissão do funcionário
	$pdo->query("INSERT INTO pagar SET descricao = '$descricao', tipo = 'Comissão', valor = '$valor_comissao', data_lanc = curDate(), data_venc = curDate(), usuario_lanc = '$id_usuario', foto = 'sem-foto.jpg', pago = 'Não', funcionario = '$funcionario', servico = '$servico', cliente = '$cliente', id_conta = '$id_conta'");
}

echo 'Baixado com Sucesso';




//dados do cliente
$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente' and id_conta = '$id_conta' order by id desc limit 2");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$total_cartoes = $res2[0]['cartoes'];
$telefone = $res2[0]['telefone'];
$nome_cliente = $res2[0]['nome'];

$query = $pdo->query("SELECT * FROM agendamentos where cliente = '$cliente' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
	for ($i = 0; $i < $total_reg; $i++) {
		$hash = $res[$i]['hash'];
		if ($hash != "") {
			require('../../../../ajax/api-excluir.php');
		}
	}
}

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
