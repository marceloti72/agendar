<?php 
//require_once("../conexao.php");
@session_start();
date_default_timezone_set('America/Sao_Paulo');
//xxkxkxkxkxkx

$hora = date('H:i');
$data = date('Y-m-d');

$tabela = 'contatos';

$nome = $_POST['nome'];
$telefone = $_POST['telefone'];

$sino = json_decode('"\ud83d\udd14"');


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;  


require '../vendor/autoload.php';

$mail = new PHPMailer(true);

$url = "https://" . $_SERVER['HTTP_HOST'] . "/";
$url = explode("//", $url);

if ($url[1] =! 'localhost/') {
	try {
		//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
		$mail->CharSet = 'UTF-8';
		$mail->isSMTP();
		$mail->Host = 'email-ssl.com.br';
		$mail->SMTPAuth = true;
		$mail->Username = 'contato@skysee.com.br';
		$mail->Password = 'x,H,6,$B6!b[';
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Port = 587;

		$mail->setFrom('contato@skysee.com.br');
		$mail->addAddress('contato@skysee.com.br');    

		
		$mail->isHTML(true);                                 
		$mail->Subject = 'Pedido de contato';    
		$mail->Body .= 'Houve um pedido de contato pelo site:<br>';     
		$mail->Body .= 'Nome: <b>'.$nome.'</b><br>';
		$mail->Body .= 'Telefone: <b>'.$telefone.'</b><br><br>';   
		
		$mail->send();
		
		// echo 'Email enviado com sucesso!';
	} catch (Exception $e) {
		echo "Erro: E-mail não enviado com sucesso. Error PHPMailer: {$mail->ErrorInfo}";
		//echo "Erro: E-mail não enviado com sucesso.<br>";
	}
}

$tel = '(22)99883-8694';

$telefone_envio = '55'.preg_replace('/[ ()-]+/' , '' , $tel);

$mensagem = $sino.' *Pedido de Contato*%0A%0A';
$mensagem.= 'Houve um pedido de contato para o sistema AGENDAR:%0A';
$mensagem.= 'Nome: *'.$nome.'*%0A'; 
$mensagem.= 'Telefone: *'.$telefone.'*%0A';     

require("api-texto-ass.php");


echo 'Enviado com Sucesso';

 ?>