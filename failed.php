<?php
// Início direto sem espaços ou caracteres invisíveis
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Forçar codificação UTF-8 no PHP
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
ini_set('default_charset', 'UTF-8');

// Configurações iniciais
header('Content-Type: text/html; charset=utf-8');
session_start();

// Este arquivo é uma página de exibição de erro de pagamento.
// Ele não processa webhooks Stripe ou insere dados no banco de dados.
// A lógica para determinar se o pagamento falhou e redirecionar para esta página
// deve ser implementada no seu backend (onde você processa as respostas da Stripe).

// Variáveis para a mensagem de erro (podem ser passadas via GET ou POST se necessário)
$errorMessage = "O seu pagamento não foi processado com sucesso. Por favor, verifique os dados do seu cartão ou tente novamente.";
$suggestedAction = "Clique no botão abaixo para tentar novamente ou entre em contato com o nosso suporte.";

// Você pode adicionar lógica aqui para personalizar a mensagem de erro
// com base em parâmetros GET, por exemplo:
// if (isset($_GET['reason']) && $_GET['reason'] === 'card_declined') {
//     $errorMessage = "O seu cartão foi recusado. Por favor, tente com outro cartão ou entre em contato com seu banco.";
// }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Mal Sucedido</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #dc3545, #c82333); /* Gradiente vermelho para falha */
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .container {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); /* Sombra suave e profunda */
            text-align: center;
            max-width: 550px;
            width: 100%;
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-error {
            color: #dc3545; /* Cor vermelha para o ícone de erro */
            font-size: 80px; /* Ícone grande */
            margin-bottom: 25px;
            animation: shake 0.5s; /* Animação sutil de "tremor" */
        }
        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            50% { transform: translateX(10px); }
            75% { transform: translateX(-10px); }
            100% { transform: translateX(0); }
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            font-size: 17px;
            color: #555;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .action-button {
            background: linear-gradient(45deg, #007bff, #0056b3); /* Gradiente azul para ação */
            color: #fff;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            margin-top: 20px;
        }
        .action-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <i class="fas fa-times-circle icon-error"></i> <!-- Ícone de "X" ou "círculo com X" -->
        <div class="title">Pagamento Mal Sucedido!</div>
        <div class="message">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <div class="message">
            <?php echo htmlspecialchars($suggestedAction); ?>
        </div>
        <!-- O link abaixo deve levar o usuário de volta à página de pagamento ou a uma página de suporte -->
        <a href="intent://agendar.skysee.com.br#Intent;scheme=https;package=com.example.app;end" class="action-button">Voltar ao APP</a>
    </div>
</body>
</html>
