<?php 
$tabela = 'agendamentos';
require_once("../../../conexao.php");

@session_start();
$usuario_logado = @$_SESSION['id'].'';

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

if(@$hora == ""){
	echo 'Selecione um Horário antes de agendar!';
	exit();
}


$query = $pdo->query("SELECT * FROM usuarios where id = '$funcionario'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$intervalo = $res[0]['intervalo'];
$tel_func = $res[0]['telefone'];

$query = $pdo->query("SELECT * FROM servicos where id = '$servico'");
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
$query = $pdo->query("SELECT * FROM dias where funcionario = '$funcionario' and dia = '$dia_procurado'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) == 0){
		echo 'Este Funcionário não trabalha neste Dia!';
	exit();
}else{
	$inicio = $res[0]['inicio'];
	$final = $res[0]['final'];
	$inicio_almoco = $res[0]['inicio_almoco'];
	$final_almoco = $res[0]['final_almoco'];
}



$dataF = implode('/', array_reverse(explode('-', $data)));
$horaF = date("H:i", strtotime($hora));




while (strtotime($nova_hora) < strtotime($hora_final_servico)){
		
		$hora_minutos = strtotime("+$intervalo minutes", strtotime($nova_hora));			
		$nova_hora = date('H:i:s', $hora_minutos);		
		
		//VERIFICAR NA TABELA HORARIOS AGD SE TEM O HORARIO NESSA DATA
		$query_agd = $pdo->query("SELECT * FROM horarios_agd where data = '$data' and funcionario = '$funcionario' and horario = '$nova_hora'");
		$res_agd = $query_agd->fetchAll(PDO::FETCH_ASSOC);
		if(@count($res_agd) > 0){
			echo 'Este serviço demora cerca de '.$tempo.' minutos, precisa escolher outro horário, pois neste horários não temos disponibilidade devido a outros agendamentos!';
			exit();
		}



		//VERIFICAR NA TABELA AGENDAMENTOS SE TEM O HORARIO NESSA DATA e se tem um intervalo entre o horario marcado e o proximo agendado nessa tabela
		$query_agd = $pdo->query("SELECT * FROM agendamentos where data = '$data' and funcionario = '$funcionario' and hora = '$nova_hora'");
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


		if(strtotime($nova_hora) > strtotime($inicio_almoco) and strtotime($nova_hora) < strtotime($final_almoco)){
		echo 'Este serviço demora cerca de '.$tempo.' minutos, precisa escolher outro horário, pois neste horários não temos disponibilidade devido ao horário de almoço!';
			exit();
	}

}


//validar horario
$query = $pdo->query("SELECT * FROM $tabela where data = '$data' and hora = '$hora' and funcionario = '$funcionario'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0 and $res[0]['id'] != $id){
	echo 'Este horário não está disponível!';
	exit();
}



$nome_sistema_maiusculo = mb_strtoupper($nome_sistema);

//pegar nome do cliente
$query = $pdo->query("SELECT * FROM clientes where id = '$cliente'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$nome_cliente = $res[0]['nome'];
$telefone = $res[0]['telefone'];

if($not_sistema == 'Sim'){
	$mensagem_not = $nome_cliente;
	$titulo_not = 'Novo Agendamento '.$dataF.' - '.$horaF;
	$id_usu = $funcionario;
	require('../../../../api/notid.php');
} 

$telefone = '55'.preg_replace('/[ ()-]+/' , '' , $tel_func);
// Enviar Notificação ao funcionario por whatsapp
$mensagem = '*'.$nome_sistema_maiusculo.'*%0A%0A';
$mensagem .= '*Confirmação de Agendamento* 📆%0A';
$mensagem .= 'Cliente: '.$nome_cliente.'%0A';
$mensagem .= 'Data: '.$dataF.'%0A';
$mensagem .= 'Hora: '.$horaF.'%0A';
$mensagem .= 'Serviço: '.$nome_servico.'%0A';

require('../../../../ajax/api-texto.php');


if($msg_agendamento == 'Api'){

//agendar o alerta de confirmação
$hora_atual = date('H:i:s');
$data_atual = date('Y-m-d');
$hora_minutos = strtotime("-$minutos_aviso minutes", strtotime($hora));
$nova_hora = date('H:i:s', $hora_minutos);


$telefone = '55'.preg_replace('/[ ()-]+/' , '' , $telefone);


}


$query = $pdo->prepare("INSERT INTO $tabela SET funcionario = '$funcionario', cliente = '$cliente', hora = '$hora', data = '$data_agd', usuario = '$usuario_logado', status = 'Agendado', obs = :obs, data_lanc = curDate(), servico = '$servico', origem = 'Loja', hash = '$hash'");

$query->bindValue(":obs", "$obs");
$query->execute();


$ult_id = $pdo->lastInsertId();

if($msg_agendamento == 'Api'){
if(strtotime($hora_atual) < strtotime($nova_hora) or strtotime($data_atual) != strtotime($data_agd)){
		$mensagem = '*'.$nome_sistema_maiusculo.'*%0A%0A';
		$mensagem .= '*Confirmação de Agendamento* 📆%0A';	
		$mensagem .= 'Data: '.$dataF.'%0A';
		$mensagem .= 'Hora: '.$horaF.'%0A';
		$mensagem .= 'Serviço: '.$nome_servico.'%0A%0A';	
		$mensagem .= '_(1 para *CONFIRMAR*, 2 para *CANCELAR*)_';
		$id_envio = $ult_id;
		$data_envio = $data_agd.' '.$nova_hora;
				
		if($minutos_aviso > 0){
			require("../../../../ajax/confirmacao.php");
			//require("../../../../ajax/chat_confirma.php");
			$id_hash = $id;		
			$pdo->query("UPDATE agendamentos SET hash = '$id_hash' WHERE id = '$ult_id'");		
		}
	
}
}

while (strtotime($hora) < strtotime($hora_final_servico)){
		
		$hora_minutos = strtotime("+$intervalo minutes", strtotime($hora));			
		$hora = date('H:i:s', $hora_minutos);

		if(strtotime($hora) < strtotime($hora_final_servico)){
			$query = $pdo->query("INSERT INTO horarios_agd SET agendamento = '$ult_id', horario = '$hora', funcionario = '$funcionario', data = '$data_agd'");
		}
	

}


echo 'Salvo com Sucesso'; 

?>