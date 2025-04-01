<?php 
require_once("../sistema/conexao.php");
@session_start();
$telefone2 = $_POST['telefone'];
$nome = $_POST['nome'];

if(!empty($_POST['funcionario'])){
	$funcionario = $_POST['funcionario'];
}else{
	$funcionario = $_SESSION['id_usuario'];
}

$hora = @$_POST['hora'];
$servico = $_POST['servico'];
$obs = $_POST['obs'];
$data = @$_POST['data'];
$data_agd = @$_POST['data'];
$hora_do_agd = @$_POST['hora'];
$id = @$_POST['id'];

$data_agd2 = implode('/', array_reverse(explode('-', $data_agd)));

$hash = "";

$mensagem_not = $nome_cliente;
 	//$titulo_not = 'Novo Agendamento ' . $dataF . ' - ' . $horaF;
 	//$id_usu = $usuario_logado;
 	require('../api/notid.php');

	exit();

$tel_cli = $_POST['telefone'];

$query = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$intervalo = $res[0]['intervalo'];
$tel_func = $res[0]['telefone'];
$nome_func = $res[0]['nome'];

$query = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$tempo = $res[0]['tempo'];
$nome_servico = $res[0]['nome'];


$hora_minutos = @strtotime("+$tempo minutes", @strtotime($hora));			
$hora_final_servico = date('H:i:s', $hora_minutos);

$nova_hora = $hora;



$diasemana = array("Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sabado");
$diasemana_numero = date('w', @strtotime($data));
$dia_procurado = $diasemana[$diasemana_numero];

//percorrer os dias da semana que ele trabalha
$query = $pdo->query("SELECT * FROM dias where funcionario = '$funcionario' and dia = '$dia_procurado' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) == 0){
		echo 'Este Profissional não trabalha neste Dia!';
	exit();
}else{
	$inicio = $res[0]['inicio'];
	$final = $res[0]['final'];
	$inicio_almoco = $res[0]['inicio_almoco'];
	$final_almoco = $res[0]['final_almoco'];
	
}

//verificar se possui essa data nos dias bloqueio geral
$query = $pdo->query("SELECT * FROM dias_bloqueio where funcionario = '0' and data = '$data_agd' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
	echo 'Não estaremos funcionando nesta Data!';
	exit();
}

//verificar se possui essa data nos dias bloqueio func
$query = $pdo->query("SELECT * FROM dias_bloqueio where funcionario = '$funcionario'  and data = '$data_agd' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
		echo 'Este Profissional não irá trabalhar nesta Data, selecione outra data ou escolhar outro Profissional!';
		exit();
}

while (@strtotime($nova_hora) < @strtotime($hora_final_servico)){
		
		$hora_minutos = @strtotime("+$intervalo minutes", @strtotime($nova_hora));			
		$nova_hora = date('H:i:s', $hora_minutos);		
		
		//VERIFICAR NA TABELA HORARIOS AGD SE TEM O HORARIO NESSA DATA
		$query_agd = $pdo->query("SELECT * FROM horarios_agd where data = '$data' and funcionario = '$funcionario' and horario = '$nova_hora' and id_conta = '$id_conta'");
		$res_agd = $query_agd->fetchAll(PDO::FETCH_ASSOC);
		if(@count($res_agd) > 0){
			echo 'Este serviço demora cerca de '.$tempo.' minutos, precisa escolher outro horário, pois neste horários não temos disponibilidade devido a outros agendamentos!';
			exit();
		}



		//VERIFICAR NA TABELA AGENDAMENTOS SE TEM O HORARIO NESSA DATA e se tem um intervalo entre o horario marcado e o proximo agendado nessa tabela
		$query_agd = $pdo->query("SELECT * FROM agendamentos where data = '$data' and funcionario = '$funcionario' and hora = '$nova_hora' and id_conta = '$id_conta'");
		$res_agd = $query_agd->fetchAll(PDO::FETCH_ASSOC);
		if(@count($res_agd) > 0){
			if($tempo <= $intervalo){

			}else{
				if($hora_final_servico == $res_agd[0]['hora']){
					
				}else{
					echo 'Este serviço demora cerca de '.$tempo.' minutos, precisa escolher outro horário, pois neste horários não temos disponibilidade devido a outros agendamentos!';
						exit();
				}
				
			}
			
		}


		if(@strtotime($nova_hora) > @strtotime($inicio_almoco) and @strtotime($nova_hora) < @strtotime($final_almoco)){
		echo 'Este serviço demora cerca de '.$tempo.' minutos, precisa escolher outro horário, pois neste horários não temos disponibilidade devido ao horário de almoço!';
			exit();
	}

}



//@$_SESSION['telefone'] = $telefone2;

if($hora == ""){
	echo 'Escolha um Horário para Agendar!';
	exit();
}

if($data < date('Y-m-d')){
	echo 'Escolha uma data igual ou maior que Hoje!';
	exit();
}

//validar horario
$query = $pdo->query("SELECT * FROM agendamentos where data = '$data' and hora = '$hora' and funcionario = '$funcionario' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0 and $res[0]['id'] != $id){
	echo 'Este horário não está disponível!';
	exit();
}

//Cadastrar o cliente caso não tenha cadastro
$query = $pdo->query("SELECT * FROM clientes where telefone LIKE '$telefone2' and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) == 0){
	$query = $pdo->prepare("INSERT INTO clientes SET nome = :nome, telefone = :telefone, data_cad = curDate(), cartoes = '0', alertado = 'Não', origem = 'Site', id_conta = :id_conta");

	$query->bindValue(":nome", "$nome");
	$query->bindValue(":telefone", "$telefone2");	
	$query->bindValue(":id_conta", "$id_conta");	
	$query->execute();
	$id_cliente = $pdo->lastInsertId();

}else{
	$id_cliente = $res[0]['id'];
}


//excluir agendamentos temporarios deste cliente
//$pdo->query("DELETE FROM agendamentos_temp where cliente = '$id_cliente'");

$nome_sistema_maiusculo = mb_strtoupper($nome_sistema);

$telefone = '55'.preg_replace('/[ ()-]+/' , '' , $tel_func);
// Enviar Notificação ao funcionario por whatsapp
$mensagem = '*'.$nome_sistema_maiusculo.'*%0A%0A';
$mensagem .= '*Novo agendamento pelo site!* 📆%0A';
$mensagem .= 'Cliente: '.$nome.'%0A';
$mensagem .= 'Data: '.$data_agd2.'%0A';
$mensagem .= 'Hora: '.$hora_do_agd.'%0A';
$mensagem .= 'Serviço: '.$nome_servico.'%0A';

require('api-texto.php');


$telefone = '55'.preg_replace('/[ ()-]+/' , '' , $whatsapp_sistema);
// Enviar Notificação ao funcionario por whatsapp
$mensagem = '*Novo agendamento pelo site!* 📆%0A';
$mensagem .= 'Cliente: '.$nome.'%0A';
$mensagem .= 'Data: '.$data_agd2.'%0A';
$mensagem .= 'Hora: '.$hora_do_agd.'%0A';
$mensagem .= 'Serviço: '.$nome_servico.'%0A';
$mensagem .= 'Profissional: '.$nome_func.'%0A';

require('api-texto.php');

$telefone = '55'.preg_replace('/[ ()-]+/' , '' , $telefone2);
// Enviar Notificação ao cliente por whatsapp
$mensagem = '*'.$nome_sistema_maiusculo.'*%0A%0A';
$mensagem .= 'Seu agendamento foi realizado com sucesso! 😀%0A';
$mensagem .= 'Data: '.$data_agd2.'%0A';
$mensagem .= 'Hora: '.$hora_do_agd.'%0A';
$mensagem .= 'Serviço: '.$nome_servico.'%0A';
$mensagem .= 'Profissional: '.$nome_func.'%0A';

require('api-texto.php');


if($msg_agendamento == 'Sim'){
	
		$mensagem = '*'.$nome_sistema_maiusculo.'*%0A%0A';
		$mensagem .= '*Confirmação de Agendamento* 📆%0A';	
		$mensagem .= 'Data: '.$data_agd2.'%0A';
		$mensagem .= 'Hora: '.$hora_do_agd.'%0A';
		$mensagem .= 'Serviço: '.$nome_servico.'%0A';
		$mensagem .= 'Profissional: '.$nome_func.'%0A%0A';	
		$mensagem .= '_(1 para *CONFIRMAR*, 2 para *CANCELAR*)_';
		//$id_envio = $ult_id;
		$data_envio = $data_agd.' '.$nova_hora;
				
		if($minutos_aviso > 0){
			require("confirmacao.php");
			//require("../../../../ajax/chat_confirma.php");
			$id_hash = $id;
		}else{
			$id_hash = '';
		}
	
	}
					

	//marcar o agendamento
	$query = $pdo->prepare("INSERT INTO agendamentos SET funcionario = '$funcionario', cliente = '$id_cliente', hora = '$hora', data = '$data_agd', usuario = '$funcionario', status = 'Agendado', obs = :obs, data_lanc = curDate(), servico = '$servico', hash = '$id_hash', origem = 'Site', id_conta = :id_conta");
	$query->bindValue(":obs", "$obs");
	$query->bindValue(":id_conta", "$id_conta");
	$query->execute();

	$ult_id = $pdo->lastInsertId();
	echo 'Pré Agendado*'.$ult_id;


	while (strtotime($hora) < strtotime($hora_final_servico)){

		$hora_minutos = strtotime("+$intervalo minutes", strtotime($hora));			
		$hora = date('H:i:s', $hora_minutos);

		if(strtotime($hora) < strtotime($hora_final_servico)){
			$query = $pdo->query("INSERT INTO horarios_agd SET agendamento = '$ult_id', horario = '$hora', funcionario = '$funcionario', data = '$data_agd', id_conta = '$id_conta'");
		}
	

}
				
		


?>