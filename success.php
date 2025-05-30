<?php
// Configurações iniciais
header('Content-Type: text/html; charset=utf-8');
session_start();

// Inclui a biblioteca do Stripe
require 'vendor/autoload.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;

// Obtém o session_id da URL
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;

$email = 'carregando...';
$trialDays = 15;
$trialEndDate = date('d/m/Y', strtotime("+$trialDays days"));
$defaultPassword = '123';

// Configurações do Stripe
Stripe::setApiKey('sua_chave_secreta_do_stripe'); // Substitua pela sua chave secreta

if ($session_id) {
    try {
        // Busca os dados da sessão
        $session = Session::retrieve($session_id, [
            'expand' => ['customer', 'subscription', 'payment_intent.payment_method'],
        ]);

        $customer = $session->customer;
        $subscription = $session->subscription;
        $paymentMethod = $session->payment_intent->payment_method;

        $email = htmlspecialchars($customer->email ?: 'email_nao_disponivel@example.com');
        $telefone = htmlspecialchars($customer->phone ?: '11999999999');
        $nomeCliente = htmlspecialchars($customer->name ?: 'Cliente_' . rand(100000, 999999));
        $formaPgto = $paymentMethod->card ? $paymentMethod->card->brand : 'desconhecida';
        $cpf = htmlspecialchars($customer->metadata->cpf ?: '12345678900');

        $priceId = $subscription->items->data[0]->price->id;
        $plano = ($priceId === 'price_1RTZKzJEPhV4vIDM8SLdZ1Gx' || $priceId === 'price_1RU5HjJEPhV4vIDMTnrVVyxT') ? 1 : 2;
        $frequencia = ($priceId === 'price_1RU5HjJEPhV4vIDMTnrVVyxT' || $priceId === 'price_1RUE3OJEPhV4vIDMzNgVl1jY') ? 365 : 30;
        $valor = $subscription->items->data[0]->price->unit_amount / 100;
        $dataAtual = date('Y-m-d H:i:s');

        $numeroAleatorio = rand(100000, 999999);
        $nomeConfig = 'teste' . $numeroAleatorio;
        $username = $nomeConfig;

        // Conexão com o banco de dados (ajuste conforme sua configuração)
        $db_servidor = 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
        $db_usuario = 'skysee';
        $db_senha = '9vtYvJly8PK6zHahjPUg';
        $db_nome = 'barbearia';
        $db_nome2 = 'sistema_gestao';

        try {
            $pdo = new PDO("mysql:host=$db_servidor;dbname=$db_nome;charset=utf8", $db_usuario, $db_senha);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilita tratamento de erros
        } catch (PDOException $e) {
            die("Erro ao conectar com o banco de dados 'barbearia': " . $e->getMessage());
        }

        try {
            $pdo2 = new PDO("mysql:host=$db_servidor;dbname=$db_nome2;charset=utf8", $db_usuario, $db_senha);
            $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilita tratamento de erros
        } catch (PDOException $e) {
            die("Erro ao conectar com o banco de dados 'sistema_gestao': " . $e->getMessage());
        }

        // Inserir na tabela config
        $stmt = $pdo->prepare("INSERT INTO config (nome, email, username, telefone_whatsapp, token, ativo, email_manuia, data_cadastro, plano) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeConfig, $email, $username, $telefone, 'f4QGNF6L4KhSNvEWP1VTHaDAI57bDTEj89Kemni1iZckHne3j9', 'teste', 'rtcorretora@gmail.com', $dataAtual, $plano]);
        $idConta = $pdo->lastInsertId();

        // Inserir na tabela usuarios
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, username, email, cpf, senha, nivel, data, ativo, telefone, atendimento, id_conta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeCliente, $username, $email, $cpf, $defaultPassword, 'administrador', $dataAtual, 'teste', $telefone, 'Sim', $idConta]);

        // Inserir na tabela clientes
        $stmt = $pdo2->prepare("INSERT INTO clientes (nome, cpf, telefone, email, data_cad, ativo, data_pgto, valor, frequencia, plano, forma_pgto, pago, id_conta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeCliente, $cpf, $telefone, $email, $dataAtual, 'teste', $dataAtual, $valor, $frequencia, $plano, $formaPgto, 'Sim', $idConta]);

        // Fechar conexões
        $stmt = null;
        $pdo = null;
        $pdo2 = null;

        echo "<!-- Dados registrados com sucesso para ID Conta: $idConta -->"; // Log invisível para depuração
    } catch (Exception $e) {
        $email = 'email_nao_disponivel@example.com'; // Fallback em caso de erro
        echo "<!-- Erro ao processar sessão: " . $e->getMessage() . " -->"; // Log invisível para depuração
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Concluído</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 15px;
        }
        .credentials {
            font-size: 16px;
            color: #333;
            margin-bottom: 10px;
        }
        .bold {
            font-weight: bold;
            color: #4a148c;
        }
        .note {
            font-size: 14px;
            color: #dc3545;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .login-button {
            background-color: #4a148c;
            color: #fff;
            padding: 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            display: inline-block;
        }
        .bottom-spacer {
            height: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">Cadastro Concluído com Sucesso!</div>
        <div class="message">
            Seu cadastro foi efetuado com sucesso. Você possui um período de teste gratuito que termina em 
            <span class="bold"><?php echo $trialEndDate; ?></span>.
        </div>
        <div class="message">Para acessar o app, utilize as seguintes credenciais:</div>
        <div class="credentials">
            <span class="bold">Login:</span> <?php echo $email; ?>
        </div>
        <div class="credentials">
            <span class="bold">Senha:</span> <?php echo $defaultPassword; ?>
        </div>
        <div class="note">
            *Observação: Por favor, troque sua senha assim que acessar o app, no seu perfil.
        </div>
        <a href="https://agendar.skysee.com.br" class="login-button">Ir para a Página de Login</a>
        <div class="bottom-spacer"></div>
    </div>
</body>
</html>