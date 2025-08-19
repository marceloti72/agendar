<?php
@session_start();
$id_conta = $_SESSION['id_conta'];
$tabela = 'agendamentos';
require_once("../../../conexao.php");

@session_start();
$usuario_logado = $_SESSION['id_usuario'];

$cliente = $_POST['cliente'];
$data = $_POST['data'];
$hora = @$_POST['hora'];
$obs = $_POST['obs'];
$id = $_POST['id'];
$funcionario = @$_SESSION['id_usuario'];
$servico = $_POST['servico'];
$data_agd = $_POST['data'];
$hora_do_agd = @$_POST['hora'];
$hash = '';

if (@$hora == "") {
	echo 'Selecione um Hora antes de agendar!';
	exit();
}

$query = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$intervalo = $res[0]['intervalo'];

$query = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$tempo = $res[0]['tempo'];
$nome_servico = $res[0]['nome'];


$hora_minutos = strtotime("+$tempo minutes", strtotime($hora));
$hora_final_servico = date('H:i:s', $hora_minutos);

$nova_hora = $hora;

$diasemana = array("Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sabado");
$diasemana_numero = date('w', strtotime($data));
$dia_procurado = $diasemana[$diasemana_numero];

//percorrer os dias da semana que ele trabalha
$query = $pdo->query("SELECT * FROM dias where funcionario = '$funcionario' and dia = '$dia_procurado' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if (@count($res) == 0) {
	echo 'Este Funcionário não trabalha neste Dia!';
	exit();
} else {
	$inicio = $res[0]['inicio'];
	$final = $res[0]['final'];
	$inicio_almoco = $res[0]['inicio_almoco'];
	$final_almoco = $res[0]['final_almoco'];
}


$dataF = implode('/', array_reverse(explode('-', $data)));
$horaF = date("H:i", strtotime($hora));




while (strtotime($nova_hora) < strtotime($hora_final_servico)) {

	$hora_minutos = strtotime("+$intervalo minutes", strtotime($nova_hora));
	$nova_hora = date('H:i:s', $hora_minutos);

	//VERIFICAR NA TABELA HORARIOS AGD SE TEM O HORARIO NESSA DATA
	$query_agd = $pdo->query("SELECT * FROM horarios_agd where data = '$data' and funcionario = '$funcionario' and horario = '$nova_hora' and id_conta = '$id_conta'");
	$res_agd = $query_agd->fetchAll(PDO::FETCH_ASSOC);
	if (@count($res_agd) > 0) {
		echo 'Este serviço demora cerca de ' . $tempo . ' minutos, precisa escolher outro horário, pois neste horários não temos disponibilidade devido a outros agendamentos!';
		exit();
	}



	//VERIFICAR NA TABELA AGENDAMENTOS SE TEM O HORARIO NESSA DATA e se tem um intervalo entre o horario marcado e o proximo agendado nessa tabela
	$query_agd = $pdo->query("SELECT * FROM agendamentos where data = '$data' and funcionario = '$funcionario' and hora = '$nova_hora' and id_conta = '$id_conta'");
	$res_agd = $query_agd->fetchAll(PDO::FETCH_ASSOC);
	if (@count($res_agd) > 0) {
		if ($tempo <= $intervalo) {
		} else {
			if ($hora_final_servico == $res_agd[0]['hora']) {
			} else {
				echo 'Este serviço demora cerca de ' . $tempo . ' minutos, precisa escolher outro horário, pois neste horários não temos disponibilidade devido a outros agendamentos!';
				exit();
			}
		}
	}


	if (strtotime($nova_hora) > strtotime($inicio_almoco) and strtotime($nova_hora) < strtotime($final_almoco)) {
		echo 'Este serviço demora cerca de ' . $tempo . ' minutos, precisa escolher outro horário, pois neste horários não temos disponibilidade devido ao horário de almoço!';
		exit();
	}
}


//validar horario
$query = $pdo->query("SELECT * FROM $tabela where data = '$data' and hora = '$hora' and funcionario = '$funcionario' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0 and $res[0]['id'] != $id) {
	echo 'Este horário não está disponível!';
	exit();
}





echo 'Salvo com Sucesso';


//pegar nome do cliente
$query = $pdo->query("SELECT * FROM clientes where id = '$cliente' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$nome_cliente = $res[0]['nome'];
$telefone = $res[0]['telefone'];

// if ($api == 'Sim') {
 	// $mensagem_not = $nome_cliente;
 	// $titulo_not = 'Novo Agendamento ' . $dataF . ' - ' . $horaF;
 	// $id_usu = $usuario_logado;
 	// require('../../../../api/notid.php');
// }


if ($msg_agendamento == 'Sim') {

	//agendar o alerta de confirmação
	$hora_atual = date('H:i:s');
	$data_atual = date('Y-m-d');
	$hora_minutos = strtotime("-$minutos_aviso minutes", strtotime($hora));
	$nova_hora = date('H:i:s', $hora_minutos);


	$telefone = '55' . preg_replace('/[ ()-]+/', '', $telefone);
}


$query = $pdo->prepare("INSERT INTO $tabela SET funcionario = '$funcionario', cliente = '$cliente', hora = '$hora', data = '$data_agd', usuario = '$usuario_logado', status = 'Agendado', obs = :obs, data_lanc = curDate(), servico = '$servico', origem = 'Loja', hash = '$hash', id_conta = '$id_conta'");

$query->bindValue(":obs", "$obs");
$query->execute();


$ult_id = $pdo->lastInsertId();


$query2 = $pdo->prepare("INSERT INTO comandas SET cliente = :cliente, valor = :valor, data = curDate(), hora = :hora, funcionario = :funcionario, status = 'Aberta', obs = :obs, pago = 'Não', id_conta = :id_conta");

$query2->bindValue(":cliente", "$cliente");
$query2->bindValue(":valor", "$valor");
$query2->bindValue(":obs", "Comanda criada para agendamento ID "+"$ult_id");
$query2->bindValue(":hora", "$hora");
$query2->bindValue(":id_conta", "$id_conta");
$query2->bindValue(":funcionario", "$usuario_logado");
$query2->execute();

$id_comanda = $pdo->lastInsertId();


$pdo->query("UPDATE agendamentos SET comanda_id = '$id_comanda' WHERE id = '$ult_id' and id_conta = '$id_conta'");


if ($msg_agendamento == 'Sim') {
	if (strtotime($hora_atual) < strtotime($nova_hora) or strtotime($data_atual) != strtotime($data_agd)) {
		$nome_sistema_maiusculo = mb_strtoupper($nome_sistema);

		$mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
		$mensagem .= '*Olá, estamos passando aqui para lembrar de seu Agendamento* 📆%0A';
		$mensagem .= 'Data: ' . $dataF . '%0A';
		$mensagem .= 'Hora: ' . $horaF . '%0A';
		$mensagem .= 'Serviço: ' . $nome_servico . '%0A%0A';
		$mensagem .= 'Aguardamos você! 😃';
		$id_envio = $ult_id;
		$data_envio = $data_agd . ' ' . $nova_hora;

		if ($minutos_aviso > 0) {
			require("../../../../ajax/confirmacao.php");
			$id_hash = $id;
			$pdo->query("UPDATE agendamentos SET hash = '$id_hash' WHERE id = '$ult_id' and id_conta = '$id_conta'");
		}
	}
}

while (strtotime($hora) < strtotime($hora_final_servico)) {

	$hora_minutos = strtotime("+$intervalo minutes", strtotime($hora));
	$hora = date('H:i:s', $hora_minutos);

	if (strtotime($hora) < strtotime($hora_final_servico)) {
		$query = $pdo->query("INSERT INTO horarios_agd SET agendamento = '$ult_id', horario = '$hora', funcionario = '$funcionario', data = '$data_agd', id_conta = '$id_conta'");
	}
}
