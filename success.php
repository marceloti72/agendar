<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Forçar codificação UTF-8 no PHP
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
ini_set('default_charset', 'UTF-8');

// Configurações iniciais
header('Content-Type: text/plain; charset=utf-8');
session_start();

// Inclui a biblioteca do Stripe
require './vendor/autoload.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;

// Configurações do Stripe
Stripe::setApiKey('sk_test_51RTXIZQwVYKsR3u1YtG7aK6S7d4sOg3Pnw8nKlXQNRBEFRGOncTdr0850Ddp1px4FRC0XuL29MaKyoy3JFiZh0Wa00reKEwQHt');
$endpoint_secret = 'whsec_aiXk2ZhwnDfOepwrRIoRNFDkC3g5Ok3e'; // Confirme essa chave no painel do Stripe

// Processamento de webhook (POST do Stripe)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

    // Log para depuração
    file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - Payload: " . $payload . "\n", FILE_APPEND);
    file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - Signature Header: " . $sig_header . "\n", FILE_APPEND);

    try {
        $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $session_id = $session->id;
            file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - Session ID: " . $session_id . "\n", FILE_APPEND);
            // Prosseguir com o processamento abaixo
        }
        http_response_code(200);
        echo "Webhook received successfully";
        exit;
    } catch (\Exception $e) {
        http_response_code(400);
        echo "Webhook error: " . $e->getMessage();
        file_put_contents('error_log.txt', date('Y-m-d H:i:s') . ' - Webhook Error: ' . $e->getMessage() . "\n", FILE_APPEND);
        exit;
    }
}

// Obtém o session_id da URL (GET) ou webhook
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : (isset($session_id) ? $session_id : (isset($_POST['session_id']) ? $_POST['session_id'] : null));

// Verifica se o session_id já foi processado (logo no início)
if ($session_id && isset($_SESSION['processed_session_ids'][$session_id])) {
    $email = $_SESSION['processed_session_ids'][$session_id]['email'];
    $defaultPassword = $_SESSION['processed_session_ids'][$session_id]['password'];
    $trialDays = 15;
    $trialEndDate = date('d/m/Y', strtotime("+$trialDays days"));

    // Exibir mensagem de cadastro já concluído
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cadastro Já Concluído</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #28a745;
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
                color: #28a745;
            }
            .note {
                font-size: 14px;
                color: #dc3545;
                margin-top: 20px;
                margin-bottom: 30px;
            }
            .login-button {
                background-color: #28a745;
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
            <div class="title">Cadastro Já Concluído</div>
            <div class="message">
                Este cadastro já foi concluído anteriormente. Você possui um período de teste gratuito que termina em 
                <span class="bold">' . htmlspecialchars($trialEndDate) . '</span>.
            </div>
            <div class="message">Utilize as seguintes credenciais para acessar o app:</div>
            <div class="credentials">
                <span class="bold">Login:</span> ' . htmlspecialchars($email) . '
            </div>
            <div class="credentials">
                <span class="bold">Senha:</span> ' . htmlspecialchars($defaultPassword) . '
            </div>
            <div class="note">
                *Observação: Acesse o seu perfil e as configurações do sistema assim que entrar no APP para inserir seus dados corretamente.
            </div>
            <a href="intent://agendar.skysee.com.br#Intent;scheme=https;package=com.example.app;end" class="login-button">Ir para o APP</a>
            <div class="bottom-spacer"></div>
        </div>
    </body>
    </html>';
    exit;
}

$email = 'carregando...';
$trialDays = 15;
$trialEndDate = date('d/m/Y', strtotime("+$trialDays days"));
// Gera senha aleatória de 6 dígitos
$defaultPassword = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Controle para evitar duplicação por session_id
if ($session_id) {
    try {
        // Busca os dados da sessão
        $session = Session::retrieve($session_id, [
            'expand' => ['customer', 'subscription', 'payment_intent.payment_method'],
        ]);

        $customer = $session->customer;
        $subscription = $session->subscription;
        $paymentMethod = $session->payment_intent ? $session->payment_intent->payment_method : null;

        // Prioriza customer_details para email e nome (fornecidos no Checkout)
        $email = htmlspecialchars($session->customer_details->email ?? $customer->email ?? 'email_nao_disponivel@example.com');
        $nomeCliente = htmlspecialchars($session->customer_details->name ?? $customer->name ?? 'Cliente_' . rand(100000, 999999));
        $telefone = htmlspecialchars($customer->phone ?? '11999999999');
        $formaPgto = $paymentMethod && $paymentMethod->card ? $paymentMethod->card->brand : 'desconhecida';
        $cpf = htmlspecialchars($customer->metadata->cpf ?? '12345678900');

        $priceId = $subscription->items->data[0]->price->id;
        $plano = ($priceId === 'price_1RTZKzJEPhV4vIDM8SLdZ1Gx' || $priceId === 'price_1RU5HjJEPhV4vIDMTnrVVyxT') ? 1 : 2;
        $frequencia = ($priceId === 'price_1RU5HjJEPhV4vIDMTnrVVyxT' || $priceId === 'price_1RUE3OJEPhV4vIDMzNgVl1jY') ? 365 : 30;
        $valor = $subscription->items->data[0]->price->unit_amount / 100;
        $dataAtual = date('Y-m-d H:i:s');

        $numeroAleatorio = rand(100000, 999999);
        $nomeConfig = 'teste' . $numeroAleatorio;
        $username = $nomeConfig;

        // Conexão com o banco de dados
        $db_servidor = 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
        $db_usuario = 'skysee';
        $db_senha = '9vtYvJly8PK6zHahjPUg';
        $db_nome = 'barbearia';
        $db_nome2 = 'gestao_sistemas';

        try {
            $pdo = new PDO("mysql:host=$db_servidor;dbname=$db_nome;charset=utf8", $db_usuario, $db_senha);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro ao conectar com o banco de dados 'barbearia': " . $e->getMessage());
        }

        try {
            $pdo2 = new PDO("mysql:host=$db_servidor;dbname=$db_nome2;charset=utf8", $db_usuario, $db_senha);
            $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro ao conectar com o banco de dados 'sistema_gestao': " . $e->getMessage());
        }

        // Verificar se o email já existe na tabela config
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM config WHERE email = ?");
        $stmt->execute([$email]);
        $emailExists = $stmt->fetchColumn();

        if ($emailExists && !isset($_POST['new_email'])) {
            // Exibir mensagem de erro e formulário para novo email
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Email Já Cadastrado</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #28a745;
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
                        color: #dc3545;
                        margin-bottom: 15px;
                    }
                    .input-field {
                        width: 100%;
                        padding: 10px;
                        margin-bottom: 15px;
                        border: 1px solid #ccc;
                        border-radius: 5px;
                        font-size: 16px;
                    }
                    .submit-button {
                        background-color: #28a745;
                        color: #fff;
                        padding: 12px;
                        border-radius: 5px;
                        text-decoration: none;
                        font-size: 16px;
                        font-weight: bold;
                        display: inline-block;
                        border: none;
                        cursor: pointer;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="title">Email Já Cadastrado</div>
                    <div class="message">
                        O email <strong>' . htmlspecialchars($email) . '</strong> já está cadastrado. Por favor, insira outro email para continuar.
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" name="session_id" value="' . htmlspecialchars($session_id) . '">
                        <input type="email" name="new_email" class="input-field" placeholder="Novo email" required>
                        <button type="submit" class="submit-button">Continuar</button>
                    </form>
                </div>
            </body>
            </html>';
            exit;
        }

        // Processar novo email do formulário
        if (isset($_POST['new_email'])) {
            $email = htmlspecialchars($_POST['new_email']);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM config WHERE email = ?");
            $stmt->execute([$email]);
            $emailExists = $stmt->fetchColumn();

            if ($emailExists) {
                header('Content-Type: text/html; charset=utf-8');
                echo '<!DOCTYPE html>
                <html lang="pt-BR">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Email Já Cadastrado</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #28a745;
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
                            color: #dc3545;
                            margin-bottom: 15px;
                        }
                        .input-field {
                            width: 100%;
                            padding: 10px;
                            margin-bottom: 15px;
                            border: 1px solid #ccc;
                            border-radius: 5px;
                            font-size: 16px;
                        }
                        .submit-button {
                            background-color: #28a745;
                            color: #fff;
                            padding: 12px;
                            border-radius: 5px;
                            text-decoration: none;
                            font-size: 16px;
                            font-weight: bold;
                            display: inline-block;
                            border: none;
                            cursor: pointer;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="title">Email Já Cadastrado</div>
                        <div class="message">
                            O email <strong>' . htmlspecialchars($email) . '</strong> também já está cadastrado. Por favor, insira outro email.
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="session_id" value="' . htmlspecialchars($session_id) . '">
                            <input type="email" name="new_email" class="input-field" placeholder="Novo email" required>
                            <button type="submit" class="submit-button">Continuar</button>
                        </form>
                    </div>
                </body>
                </html>';
                exit;
            }
        }

        // Inserir na tabela config
        $stmt = $pdo->prepare("INSERT INTO config (nome, email, username, telefone_whatsapp, token, ativo, email_menuia, data_cadastro, plano) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeConfig, $email, $username, $telefone, 'f4QGNF6L4KhSNvEWP1VTHaDAI57bDTEj89Kemni1iZckHne3j9', 'teste', 'rtcorretora@gmail.com', $dataAtual, $plano]);
        $idConta = $pdo->lastInsertId();

        // Inserir na tabela usuarios
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, username, email, cpf, senha, nivel, data, ativo, telefone, atendimento, id_conta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeCliente, $username, $email, $cpf, $defaultPassword, 'administrador', $dataAtual, 'teste', $telefone, 'Sim', $idConta]);

        // Inserir na tabela clientes
        $stmt = $pdo2->prepare("INSERT INTO clientes (nome, cpf, telefone, email, data_cad, ativo, data_pgto, valor, frequencia, plano, forma_pgto, pago, id_conta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeCliente, $cpf, $telefone, $email, $dataAtual, 'teste', $dataAtual, $valor, $frequencia, $plano, $formaPgto, 'Sim', $idConta]);

        // Armazena o session_id processado na sessão
        $_SESSION['processed_session_ids'][$session_id] = [
            'email' => $email,
            'password' => $defaultPassword
        ];

        // Fechar conexões
        $stmt = null;
        $pdo = null;
        $pdo2 = null;

        echo "<!-- Dados registrados com sucesso para ID Conta: $idConta -->"; // Log invisível para depuração
    } catch (Exception $e) {
        $email = 'email_nao_disponivel@example.com'; // Fallback em caso de erro
        echo "<!-- Erro ao processar sessão: " . $e->getMessage() . " -->"; // Log invisível para depuração
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            http_response_code(400);
            echo "Webhook error: " . $e->getMessage();
            exit;
        }
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
            background-color: #28a745; /* Fundo verde */
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
            color: #28a745; /* Cor verde para destaque */
        }
        .note {
            font-size: 14px;
            color: #dc3545;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .login-button {
            background-color: #28a745; /* Botão verde */
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
            *Observação: Acesse o seu perfil e as configurações do sistema assim que entrar no APP para inserir seus dados corretamente.
        </div>
        <a href="intent://agendar.skysee.com.br#Intent;scheme=https;package=com.example.app;end" class="login-button">Ir para o APP</a>
        <div class="bottom-spacer"></div>
    </div>
</body>
</html>