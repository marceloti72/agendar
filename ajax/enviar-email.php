<?php
require_once("../sistema/conexao.php");

$url = "https://" . $_SERVER['HTTP_HOST'] . "/";
$url = explode("//", $url);

// $nome = $_POST['nome'];
// $telefone = $_POST['telefone'];
// $mensagem = $_POST['mensagem'];
// $dest = $_POST['email'];


// if ($url[1] != 'localhost/') {    

// $mensagem_corpo = "Nome: " . $nome . "\r\n\r\n" .
//                   "Telefone: " . $telefone . "\r\n\r\n" .
//                   "Mensagem:\r\n\r\n" . $mensagem;

// $cabecalhos = "From: maroni.alimentos01@gmail.com" .
//               "Reply-To: " . $dest . "\r\n" .
//               "Content-Type: text/plain; charset=UTF-8\r\n" .
//               "X-Mailer: PHP/" . phpversion();

// if (mail($dest, $assunto, $mensagem_corpo, $cabecalhos)) {
//     echo "E-mail enviado com sucesso!";
// } else {
//     echo "Erro ao enviar o e-mail.";
// }
// }



$nome_sistema_maiusculo = mb_strtoupper($nome_sistema);

$telefone = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);
// Enviar Notificação ao funcionario por whatsapp
$mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
$mensagem .= '*Contato pelo Site!* 😃%0A';
$mensagem .= 'Cliente: ' . $_POST['nome'] . '%0A';
$mensagem .= 'Telefone: ' . $_POST['telefone'] . '%0A';
$mensagem .= 'E-mail: ' . $_POST['email'] . '%0A';
$mensagem .= 'Mensagem: ' . $_POST['mensagem'] . '%0A';

require('api-texto.php');

echo 'Enviado com Sucesso';
