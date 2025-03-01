<?php
require_once("../sistema/conexao.php");

$url = "https://" . $_SERVER['HTTP_HOST'] . "/";
$url = explode("//", $url);


if ($url[1] != 'localhost/') {

    $remetente = $email_sistema;
    $assunto = 'Contato - ' . $nome_sistema;

    $mensagem = utf8_decode('Nome: ' . $_POST['nome'] . "\r\n" . "\r\n" . 'Telefone: ' . $_POST['telefone'] . "\r\n" . "\r\n" . 'Mensagem: ' . "\r\n" . "\r\n" . $_POST['mensagem']);
    $dest = $_POST['email'];
    $cabecalhos = "From: " . $dest;

    mail($remetente, $assunto, $mensagem, $cabecalhos);
}



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
