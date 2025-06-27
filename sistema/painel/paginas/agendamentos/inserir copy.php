<?php
header('Content-Type: application/json');
$tabela = 'agendamentos';
require_once("../../../conexao.php");

@session_start();
$id_conta = $_SESSION['id_conta'];
$usuario_logado = $_SESSION['id_usuario'];

$cliente = $_POST['cliente'];
$data = $_POST['data'];
$hora = @$_POST['hora'];
$obs = $_POST['obs'];
$id = $_POST['id'];
$funcionario = $_POST['funcionario'];
$servico = $_POST['servico'];
$data_agd = $_POST['data'];
$hora_do_agd = @$_POST['hora'];
$hash = '';
$quantidade_a_usar = 1;
$response['success'] = true;

if (@$hora == "") {
	echo 'Selecione um Horário antes de agendar!';
	exit();
}


$query = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$intervalo = $res[0]['intervalo'];
$tel_func = $res[0]['telefone'];

$query = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$tempo = $res[0]['tempo'];
$nome_servico = $res[0]['nome'];
$valor = $res[0]['valor'];


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


// --- 3. VERIFICAÇÃO DE ASSINATURA (ADICIONADO) ---
$coberto_pela_assinatura = false;
$mensagem_assinatura = 'Salvo com sucesso!'; // Mensagem para o alert final
$id_assinante_encontrado = null;
$id_receber_ciclo_atual = null;
$id_plano_servico_encontrado = null;
$mensagem_saida = '';

 // a. Cliente tem assinatura ativa?
 $query_find_ass = $pdo->prepare("SELECT id, id_plano FROM assinantes WHERE id_cliente = :id_cliente AND id_conta = :id_conta AND ativo = 1 AND data_vencimento >= CURDATE()");
 $query_find_ass->execute([':id_cliente' => $cliente, ':id_conta' => $id_conta]);
 $assinante_info = $query_find_ass->fetch(PDO::FETCH_ASSOC);

 if ($assinante_info) { // É assinante ativo
	 $id_assinante_encontrado = $assinante_info['id'];
	 $id_plano_assinante = $assinante_info['id_plano'];

	 // b. Serviço está no plano? Qual limite base (mensal)?
	 $query_limite = $pdo->prepare("SELECT id, quantidade FROM planos_servicos WHERE id_plano = :id_plano AND id_servico = :id_servico AND id_conta = :id_conta");
	 $query_limite->execute([':id_plano' => $id_plano_assinante, ':id_servico' => $servico, ':id_conta' => $id_conta]);
	 $limite_info = $query_limite->fetch();

	 if ($limite_info) { // Serviço incluído
		 $limite_base = (int)$limite_info['quantidade'];
		 $id_plano_servico_encontrado = $limite_info['id'];

		 // c. Encontra ciclo atual (cobrança pendente da ASSINATURA)
		 // *** VERIFIQUE A FK: 'cliente' ou 'pessoa'? Assumindo 'cliente' = assinantes.id ***
		 $query_rec = $pdo->prepare("SELECT id, frequencia FROM receber WHERE cliente = :id_ass AND id_conta = :id_conta AND pago = 'Não' AND tipo = 'Assinatura' ORDER BY data_venc ASC, id ASC LIMIT 1");
		 $query_rec->execute([':id_ass' => $id_assinante_encontrado, ':id_conta' => $id_conta]);
		 $rec_atual = $query_rec->fetch();

		 if ($rec_atual) { // Ciclo encontrado
			 $id_receber_ciclo_atual = $rec_atual['id'];
			 $frequencia_ciclo = (int)$rec_atual['frequencia'];

			 // d. Calcula limite real do ciclo (anual * 12)
			 $limite_ciclo = $limite_base;
			 if ($frequencia_ciclo == 365 && $limite_base > 0) { $limite_ciclo = $limite_base * 12; }
			 elseif ($limite_base == 0) { $limite_ciclo = 0; }

			 // e. Conta uso atual neste ciclo
			 $usados_atualmente = 0;
			 if ($limite_ciclo !== 0) {
				 $query_uso = $pdo->prepare("SELECT SUM(quantidade_usada) as total_usado FROM assinantes_servicos_usados WHERE id_assinante = :id_ass AND id_servico = :id_serv AND id_receber_associado = :id_rec AND id_conta = :id_conta");
				 $query_uso->execute([':id_ass' => $id_assinante_encontrado, ':id_serv' => $servico, ':id_rec' => $id_receber_ciclo_atual, ':id_conta' => $id_conta]);
				 $uso_info = $query_uso->fetch();
				 $usados_atualmente = $uso_info ? (int)$uso_info['total_usado'] : 0;
			 }

			 // f. Verifica se há saldo
			 if ($limite_ciclo === 0 || ($usados_atualmente + $quantidade_a_usar) <= $limite_ciclo) {
				 $coberto_pela_assinatura = true; // Define que está coberto!
				 $novo_uso_num = $usados_atualmente + $quantidade_a_usar;
				 $limite_texto = ($limite_ciclo === 0) ? "Ilimitado" : $limite_ciclo;
				 $mensagem_assinatura = " Cliente Assinante (Uso Serviço: {$novo_uso_num} / {$limite_texto})"; // Mensagem específica
			 } else { $mensagem_assinatura = " (Assinante: Limite Atingido {$usados_atualmente} / {$limite_ciclo})"; }
		 } else { $mensagem_assinatura = " (Assinante: Ciclo não localizado)"; }
	 } // else: serviço não incluído no plano
 } // else: não é assinante ativo

 
$nome_sistema_maiusculo = mb_strtoupper($nome_sistema);

//pegar nome do cliente
$query = $pdo->query("SELECT * FROM clientes where id = '$cliente' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$nome_cliente = $res[0]['nome'];
$telefone = $res[0]['telefone'];

if ($api == 'Sim') {
	 $mensagem_not = $nome_cliente;
	 $titulo_not = 'Novo Agendamento ' . $dataF . ' - ' . $horaF;
	 $id_usu = $funcionario;
	 require('../../../../api/notid.php');


	$telefone = '55' . preg_replace('/[ ()-]+/', '', $tel_func);
	// Enviar Notificação ao funcionario por whatsapp
	$mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
	$mensagem .= '*Confirmação de Agendamento* 📆%0A';
	$mensagem .= 'Cliente: ' . $nome_cliente . '%0A';
	$mensagem .= 'Data: ' . $dataF . '%0A';
	$mensagem .= 'Hora: ' . $horaF . '%0A';
	$mensagem .= 'Serviço: ' . $nome_servico . '%0A';

	require('../../../../ajax/api-texto.php');


	if ($msg_agendamento == 'Sim') {

		//agendar o alerta de confirmação
		$hora_atual = date('H:i:s');
		$data_atual = date('Y-m-d');
		$hora_minutos = strtotime("-$minutos_aviso minutes", strtotime($hora));
		$nova_hora = date('H:i:s', $hora_minutos);


		$telefone = '55' . preg_replace('/[ ()-]+/', '', $telefone);
	}
}

$query = $pdo->prepare("INSERT INTO $tabela SET funcionario = '$funcionario', cliente = '$cliente', hora = '$hora', data = '$data_agd', usuario = '$usuario_logado', status = 'Agendado', obs = :obs, data_lanc = curDate(), servico = '$servico', origem = 'Site', hash = '$hash', id_conta = '$id_conta'");

$query->bindValue(":obs", "$obs");
$query->execute();


$ult_id = $pdo->lastInsertId();


try {
    $query2 = $pdo->prepare("INSERT INTO comandas SET cliente = :cliente, valor = :valor, data = :data, hora = :hora, funcionario = :funcionario, status = 'Aberta', obs = :obs, pago = 'Não', id_conta = :id_conta");

    $query2->bindValue(":cliente", "$cliente");
    $query2->bindValue(":valor", "$valor");
    $query2->bindValue(":obs", "Comanda criada para agendamento ID " . $ult_id); // Corrigido aqui: use . para concatenar strings
    $query2->bindValue(":hora", "$hora");
    $query2->bindValue(":id_conta", "$id_conta");
    $query2->bindValue(":funcionario", "$usuario_logado");
    $query2->bindValue(":data", "$data_agd");
    
    $query2->execute();    

} catch (PDOException $e) {    
    echo "Erro ao inserir comanda: " . $e->getMessage();
   
}

$id_comanda = $pdo->lastInsertId();


$pdo->query("UPDATE agendamentos SET comanda_id = '$id_comanda' WHERE id = '$ult_id' and id_conta = '$id_conta'");

if ($api == 'Sim') {
	if ($msg_agendamento == 'Sim') {
		if (strtotime($hora_atual) < strtotime($nova_hora) or strtotime($data_atual) != strtotime($data_agd)) {
			$mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
			$mensagem .= '*Confirmação de Agendamento* 📆%0A';
			$mensagem .= 'Data: ' . $dataF . '%0A';
			$mensagem .= 'Hora: ' . $horaF . '%0A';
			$mensagem .= 'Serviço: ' . $nome_servico . '%0A%0A';
			$mensagem .= '_(1 para *CONFIRMAR*, 2 para *CANCELAR*)_';
			$id_envio = $ult_id;
			$data_envio = $data_agd . ' ' . $nova_hora;

			if ($minutos_aviso > 0) {
				require("../../../../ajax/confirmacao.php");
				//require("../../../../ajax/chat_confirma.php");
				$id_hash = $id;
				$pdo->query("UPDATE agendamentos SET hash = '$id_hash' WHERE id = '$ult_id' and id_conta = '$id_conta'");
			}
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


$response['success'] = true;


//echo 'Salvo com Sucesso';
