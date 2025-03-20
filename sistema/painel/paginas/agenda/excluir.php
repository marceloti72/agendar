<?php
$tabela = 'agendamentos';
require_once("../../../conexao.php");

$id = $_POST['id'];

$query = $pdo->query("SELECT * FROM $tabela where id = '$id' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$cliente = $res[0]['cliente'];
$usuario = $res[0]['funcionario'] . '';
$data = $res[0]['data'];
$hora = $res[0]['hora'];
$servico = $res[0]['servico'];
$hash = $res[0]['hash'];

$dataF = implode('/', array_reverse(explode('-', $data)));
$horaF = date("H:i", strtotime($hora));

$query = $pdo->query("SELECT * FROM clientes where id = '$cliente' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$nome_cliente = $res[0]['nome'];
$telefone = $res[0]['telefone'];

$pdo->query("DELETE FROM $tabela where id = '$id' and id_conta = '$id_conta'");
$pdo->query("DELETE FROM horarios_agd where agendamento = '$id' and id_conta = '$id_conta'");

echo 'Excluído com Sucesso';

if ($hash != "") {
	require('../../../../ajax/agendar-delete.php');
}



if ($msg_agendamento == 'Sim') {

	$query = $pdo->query("SELECT * FROM usuarios where id = '$usuario' and id_conta = '$id_conta' ");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$nome_func = $res[0]['nome'];
	$tel_func = $res[0]['telefone'];

	$query = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta' ");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$nome_serv = $res[0]['nome'];

	$nome_sistema_maiusculo = mb_strtoupper($nome_sistema);

    $mensagem = '*'.$nome_sistema_maiusculo.'*%0A%0A';
	$mensagem .= '_Agendamento Cancelado_ ❌%0A';
	$mensagem .= 'Profissional: *' . $nome_func . '* %0A';
	$mensagem .= 'Serviço: *' . $nome_serv . '* %0A';
	$mensagem .= 'Data: *' . $dataF . '* %0A';
	$mensagem .= 'Hora: *' . $horaF . '* %0A';
	$mensagem .= 'Cliente: *' . $nome_cliente . '* %0A';

	$telefone = '55' . preg_replace('/[ ()-]+/', '', $telefone);

	require('../../../../ajax/api-texto.php');

	//avisar o profissional
	$telefone = '55' . preg_replace('/[ ()-]+/', '', $tel_func);
	require('../../../../ajax/api-texto.php');

	
}

// if ($api == 'Sim') {
// 	$mensagem_not = $nome_cliente;
// 	$titulo_not = 'Agendamento Cancelado ' . $dataF . ' - ' . $horaF;
// 	$id_usu = $usuario;
// 	require('../../../../api/notid.php');
// }


if($encaixe == 'Sim'){
	
	$link = $url.'agendamentos?u='.$username;

	$query = $pdo->prepare("SELECT * FROM encaixe WHERE data = :data AND profissional = :profissional AND id_conta = :id_conta");
	$query->execute([':data' => $data, ':profissional' => $usuario, ':id_conta' => $id_conta]);
	$res = $query->fetchAll(PDO::FETCH_ASSOC);

	$num_clientes = count($res);

	if ($num_clientes > 0) {
		$clientes_info = '';
		foreach ($res as $cliente) {
			$mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
			$mensagem .= '*_Horário disponível_* 📆%0A%0A';
			$mensagem .= 'Olá '. $cliente['nome'] .', uma vaga foi liberada. Corra para agendar! 😃%0A%0A';
			$mensagem .= 'Profissional: *' . $nome_func . '* %0A';		
			$mensagem .= 'Data: *' . $dataF . '* %0A';
			$mensagem .= 'Hora: *' . $horaF . '* %0A';
			$mensagem .= 'Link de agendamento:  %0A';
			$mensagem .= $link . ' %0A';


			//avisar o profissional
			$telefone = '55' . preg_replace('/[ ()-]+/', '', $cliente['whatsapp']);
			require('../../../../ajax/api-texto.php');

			$clientes_info .= '✅ ' . htmlspecialchars($cliente['nome']) . ', ' . htmlspecialchars($cliente['whatsapp']) . '%0A';
		}


		$mens = $num_clientes . ' cliente' . ($num_clientes > 1 ? 's' : '') . ' que estava' . ($num_clientes > 1 ? 'm' : '') . ' aguardando encaixe para essa data e profissional fora' . ($num_clientes > 1 ? 'm' : '') . ' alertado' . ($num_clientes > 1 ? 's' : '') . '.%0A%0A' . $clientes_info;

		$mensagem = '*_Alerta de Encaixe_* 🚨%0A%0A';
		$mensagem .= 'Profissional: *' . $nome_func . '* %0A';		
		$mensagem .= 'Data: *' . $dataF . '* %0A';
		$mensagem .= 'Hora: *' . $horaF . '* %0A%0A';
		$mensagem .= $mens.' %0A';
		
		
		//avisar o empresa
		$telefone = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);
		require('../../../../ajax/api-texto.php');

		echo 'Excluído com Sucesso';
	}else{
		echo 'Excluído com Sucesso';
	}
}else{
	echo 'Excluído com Sucesso';
}
