<?php
// Início direto sem espaços ou caracteres invisíveis
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Forçar codificação UTF-8 no PHP
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
ini_set('default_charset', 'UTF-8');

// Configurações iniciais
header('Content-Type: text/html; charset=utf-8');
session_start();

// Inclui a biblioteca do Stripe
require './vendor/autoload.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\Subscription;

// Configurações do Stripe
Stripe::setApiKey('sk_test_51RTXIZQwVYKsR3u1YtG7aK6S7d4sOg3Pnw8nKlXQNRBEFRGOncTdr0850Ddp1px4FRC0XuL29MaKyoy3JFiZh0Wa00reKEwQHt');
$endpoint_secret = 'whsec_aiXk2ZhwnDfOepwrRIoRNFDkC3g5Ok3e';

// Processamento de requisições POST (webhook ou formulário)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_email'])) {
        $session_id = $_POST['session_id'] ?? null;
        $email = htmlspecialchars($_POST['new_email']);
    } else {
        file_put_contents('/var/www/agendar/webhook_log.txt', date('Y-m-d H:i:s') . " - POST request received\n", FILE_APPEND);
        
        if (!isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            file_put_contents('/var/www/agendar/webhook_log.txt', date('Y-m-d H:i:s') . " - Stripe-Signature header missing\n", FILE_APPEND);
            http_response_code(404);
            echo "Stripe-Signature header missing";
            exit;
        }

        ob_start();
        header('Content-Type: text/plain; charset=utf-8');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        file_put_contents('/var/www/agendar/webhook_log.txt', date('Y-m-d H:i:s') . " - Payload: " . $payload . "\n", FILE_APPEND);
        file_put_contents('/var/www/agendar/webhook_log.txt', date('Y-m-d H:i:s') . " - Signature Header: " . $sig_header . "\n", FILE_APPEND);
        file_put_contents('/var/www/agendar/webhook_log.txt', date('Y-m-d H:i:s') . " - Response Headers: " . print_r(headers_list(), true) . "\n", FILE_APPEND);

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                $session_id = $session->id;
                file_put_contents('/var/www/agendar/webhook_log.txt', date('Y-m-d H:i:s') . " - Session ID: " . $session_id . "\n", FILE_APPEND);
            }
            http_response_code(200);
            ob_end_clean();
            echo "Webhook received successfully";
            exit;
        } catch (\Exception $e) {
            http_response_code(404);
            ob_end_clean();
            echo "Webhook error: " . $e->getMessage();
            file_put_contents('/var/www/agendar/error_log.txt', date('Y-m-d H:i:s') . ' - Webhook Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            exit;
        }
    }
}

// Obtém o session_id da URL (GET) ou webhook
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : (isset($session_id) ? $session_id : (isset($_POST['session_id']) ? $_POST['session_id'] : null));

// Verifica se o session_id já foi processado
if ($session_id && isset($_SESSION['processed_session_ids'][$session_id])) {
    $email = $_SESSION['processed_session_ids'][$session_id]['email'];
    $defaultPassword = $_SESSION['processed_session_ids'][$session_id]['password'];
    $trialDays = 15;
    $trialEndDate = date('d/m/Y', strtotime("+$trialDays days"));

    // HTML para "Cadastro Já Concluído" com estilo elegante
    echo '<!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cadastro Já Concluído</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            body {
                font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #28a745, #218838); /* Gradiente verde */
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
                box-shadow: 0 10px 30px rgba(0,0,0,0.15); /* Sombra mais suave e profunda */
                text-align: center;
                max-width: 550px;
                width: 100%;
                animation: fadeIn 0.8s ease-out;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .icon-success {
                color: #28a745;
                font-size: 80px; /* Ícone grande */
                margin-bottom: 25px;
                animation: bounceIn 0.6s ease-out;
            }
            @keyframes bounceIn {
                from, 20%, 40%, 60%, 80%, to {
                    animation-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
                }
                0% { opacity: 0; transform: scale3d(0.3, 0.3, 0.3); }
                20% { transform: scale3d(1.1, 1.1, 1.1); }
                40% { transform: scale3d(0.9, 0.9, 0.9); }
                60% { opacity: 1; transform: scale3d(1.03, 1.03, 1.03); }
                80% { transform: scale3d(0.97, 0.97, 0.97); }
                to { opacity: 1; transform: scale3d(1, 1, 1); }
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
                margin-bottom: 15px;
                line-height: 1.6;
            }
            .credentials {
                font-size: 17px;
                color: #333;
                margin-bottom: 12px;
            }
            .bold-highlight {
                font-weight: bold;
                color: #28a745; /* Cor de destaque verde */
            }
            .note {
                font-size: 14px;
                color: #dc3545; /* Vermelho para observações importantes */
                margin-top: 25px;
                margin-bottom: 35px;
                line-height: 1.5;
            }
            .login-button {
                background: linear-gradient(45deg, #28a745, #218838); /* Gradiente no botão */
                color: #fff;
                padding: 15px 30px;
                border-radius: 8px;
                text-decoration: none;
                font-size: 18px;
                font-weight: bold;
                display: inline-block;
                transition: all 0.3s ease;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
            .login-button:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <i class="fas fa-check-circle icon-success"></i>
            <div class="title">Cadastro Já Concluído!</div>
            <div class="message">
                Este cadastro já foi concluído anteriormente. Você possui um período de teste gratuito que termina em 
                <span class="bold-highlight">' . htmlspecialchars($trialEndDate) . '</span>.
            </div>
            <div class="message">Utilize as seguintes credenciais para acessar o app:</div>
            <div class="credentials">
                <span class="bold-highlight">Login:</span> ' . htmlspecialchars($email) . '
            </div>
            <div class="credentials">
                <span class="bold-highlight">Senha:</span> ' . htmlspecialchars($defaultPassword) . '
            </div>
            <div class="note">
                🚨 Acesse o seu perfil e as configurações do sistema assim que entrar no APP para inserir seus dados corretamente.
            </div>
            <a href="intent://agendar.skysee.com.br#Intent;scheme=https;package=com.example.app;end" class="login-button">Ir para o APP</a>
        </div>
    </body>
    </html>';
    exit;
}

// Inicializa variáveis
$email = 'carregando...';
$trialDays = 15;
$trialEndDate = date('d/m/Y', strtotime("+$trialDays days"));
$defaultPassword = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Verifica se o session_id é válido
if (!$session_id) {
    // HTML para "Erro" com estilo elegante
    echo '<!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erro</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            body {
                font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #dc3545, #c82333); /* Gradiente vermelho para erro */
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
                box-shadow: 0 10px 30px rgba(0,0,0,0.15);
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
                color: #dc3545;
                font-size: 80px;
                margin-bottom: 25px;
                animation: shake 0.5s;
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
                margin-bottom: 15px;
                line-height: 1.6;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <i class="fas fa-exclamation-triangle icon-error"></i>
            <div class="title">Erro!</div>
            <div class="message">
                Session ID não fornecido ou inválido. Por favor, tente novamente.
            </div>
        </div>
    </body>
    </html>';
    exit;
}

// Controle para evitar duplicação por session_id
if ($session_id) {
    try {
        // Busca os dados da sessão com expansão completa
        $session = Session::retrieve($session_id, [
            'expand' => ['customer', 'subscription', 'payment_intent.payment_method', 'subscription.items'],
        ]);

        $customer = $session->customer;
        $subscription = $session->subscription;
        $paymentMethod = $session->payment_intent ? $session->payment_intent->payment_method : null;

        // Obter o customer_id
        $customer_id = is_string($customer) ? $customer : ($customer->id ?? 'desconhecido');

        // Prioriza customer_details para email, nome e telefone
        $email = htmlspecialchars($session->customer_details->email ?? $customer->email ?? 'email_nao_disponivel@example.com');
        $nomeCliente = htmlspecialchars($session->customer_details->name ?? $customer->name ?? 'Cliente_' . rand(100000, 999999));
        $telefoneRaw = $session->customer_details->phone ?? '11999999999';
        $telefone = formatPhoneNumber($telefoneRaw);
        $formaPgto = $paymentMethod && $paymentMethod->card ? $paymentMethod->card->brand : 'desconhecida';
        $cpf = rand(1000000000, 99999999999);

        // Forçar obtenção do objeto completo da assinatura
        if ($subscription && is_string($subscription)) {
            $subscription = Subscription::retrieve($subscription, ['expand' => ['items']]);
            file_put_contents('/var/www/agendar/session_log.txt', date('Y-m-d H:i:s') . " - Subscription fetched from ID: " . $subscription->id . "\n", FILE_APPEND);
        }

        // Log detalhado do objeto subscription
        file_put_contents('/var/www/agendar/session_log.txt', date('Y-m-d H:i:s') . " - Subscription Object: " . print_r($subscription, true) . "\n", FILE_APPEND);

        // Verificar e mapear o status da assinatura
        $subscription_status = is_object($subscription) && isset($subscription->status) ? $subscription->status : 'desconhecido';
        $status_map = [
            'trialing' => 'em teste',
            'active' => 'ativo',
            'canceled' => 'cancelado',
            'past_due' => 'atrasado',
            'unpaid' => 'não pago',
            'incomplete' => 'incompleto',
            'incomplete_expired' => 'incompleto expirado',
            'desconhecido' => 'desconhecido'
        ];
        $customer_status = $status_map[$subscription_status] ?? 'desconhecido';

        // Log detalhado do status
        file_put_contents('/var/www/agendar/session_log.txt', date('Y-m-d H:i:s') . " - Subscription Status: " . $subscription_status . ", Mapped Status: " . $customer_status . ", Subscription ID: " . ($subscription->id ?? 'não disponível') . "\n", FILE_APPEND);

        // Verificar valor da assinatura
        $valor = 0;
        $priceId = null;

        if (is_object($subscription) && isset($subscription->items->data[0]->price->unit_amount)) {
            $priceId = $subscription->items->data[0]->price->id;
            $valor = $subscription->items->data[0]->price->unit_amount / 100;
            file_put_contents('/var/www/agendar/session_log.txt', date('Y-m-d H:i:s') . " - Price ID: " . $priceId . ", Valor: " . $valor . "\n", FILE_APPEND);
        } elseif (isset($session->amount_total)) {
            $valor = $session->amount_total / 100;
            file_put_contents('/var/www/agendar/session_log.txt', date('Y-m-d H:i:s') . " - Amount Total: " . $valor . "\n", FILE_APPEND);
        } else {
            file_put_contents('/var/www/agendar/session_log.txt', date('Y-m-d H:i:s') . " - No subscription or amount_total found.\n", FILE_APPEND);
        }

        $plano = ($priceId === 'price_1RTXujQwVYKsR3u1RPS4YJ2k' || $priceId === 'price_1RUErpQwVYKsR3u108WBjSM6') ? 1 : 2;
        $frequencia = ($priceId === 'price_1RUErpQwVYKsR3u108WBjSM6' || $priceId === 'price_1RUEtsQwVYKsR3u1EtM51sF2') ? 365 : 30;
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
            file_put_contents('/var/www/agendar/error_log.txt', date('Y-m-d H:i:s') . " - Erro ao conectar com o banco 'barbearia': " . $e->getMessage() . "\n", FILE_APPEND);
            throw new Exception("Erro ao conectar com o banco de dados 'barbearia': " . $e->getMessage());
        }

        try {
            $pdo2 = new PDO("mysql:host=$db_servidor;dbname=$db_nome2;charset=utf8", $db_usuario, $db_senha);
            $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            file_put_contents('/var/www/agendar/error_log.txt', date('Y-m-d H:i:s') . " - Erro ao conectar com o banco 'gestao_sistemas': " . $e->getMessage() . "\n", FILE_APPEND);
            throw new Exception("Erro ao conectar com o banco de dados 'gestao_sistemas': " . $e->getMessage());
        }

        // Verificar se o email já existe na tabela config
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM config WHERE email = ?");
        $stmt->execute([$email]);
        $emailExists = $stmt->fetchColumn();

        if ($emailExists && !isset($_POST['new_email'])) {
            // HTML para "Email Já Cadastrado" com estilo elegante
            echo '<!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Email Já Cadastrado</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
                <style>
                    body {
                        font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
                        background: linear-gradient(135deg, #ffc107, #e0a800); /* Gradiente amarelo para alerta */
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
                        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                        text-align: center;
                        max-width: 550px;
                        width: 100%;
                        animation: fadeIn 0.8s ease-out;
                    }
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(-20px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    .icon-warning {
                        color: #ffc107;
                        font-size: 80px;
                        margin-bottom: 25px;
                        animation: pulse 1.5s infinite;
                    }
                    @keyframes pulse {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.05); }
                        100% { transform: scale(1); }
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
                    .input-field {
                        width: calc(100% - 20px); /* Ajuste para padding */
                        padding: 12px;
                        margin-bottom: 20px;
                        border: 1px solid #ccc;
                        border-radius: 8px;
                        font-size: 16px;
                        box-sizing: border-box; /* Garante que padding não aumente a largura */
                    }
                    .submit-button {
                        background: linear-gradient(45deg, #007bff, #0056b3); /* Gradiente azul */
                        color: #fff;
                        padding: 15px 30px;
                        border-radius: 8px;
                        text-decoration: none;
                        font-size: 18px;
                        font-weight: bold;
                        display: inline-block;
                        border: none;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                    }
                    .submit-button:hover {
                        transform: translateY(-3px);
                        box-shadow: 0 8px 20px rgba(0,0,0,0.25);
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <i class="fas fa-exclamation-circle icon-warning"></i>
                    <div class="title">Email Já Cadastrado</div>
                    <div class="message">
                        O email <strong style="color: #dc3545;">' . htmlspecialchars($email) . '</strong> já está cadastrado. Por favor, insira outro email para continuar.
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
                // HTML para "Email Já Cadastrado" (segunda vez) com estilo elegante
                echo '<!DOCTYPE html>
                <html lang="pt-BR">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Email Já Cadastrado</title>
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
                    <style>
                        body {
                            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
                            background: linear-gradient(135deg, #ffc107, #e0a800);
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
                            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                            text-align: center;
                            max-width: 550px;
                            width: 100%;
                            animation: fadeIn 0.8s ease-out;
                        }
                        @keyframes fadeIn {
                            from { opacity: 0; transform: translateY(-20px); }
                            to { opacity: 1; transform: translateY(0); }
                        }
                        .icon-warning {
                            color: #ffc107;
                            font-size: 80px;
                            margin-bottom: 25px;
                            animation: pulse 1.5s infinite;
                        }
                        @keyframes pulse {
                            0% { transform: scale(1); }
                            50% { transform: scale(1.05); }
                            100% { transform: scale(1); }
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
                        .input-field {
                            width: calc(100% - 20px);
                            padding: 12px;
                            margin-bottom: 20px;
                            border: 1px solid #ccc;
                            border-radius: 8px;
                            font-size: 16px;
                            box-sizing: border-box;
                        }
                        .submit-button {
                            background: linear-gradient(45deg, #007bff, #0056b3);
                            color: #fff;
                            padding: 15px 30px;
                            border-radius: 8px;
                            text-decoration: none;
                            font-size: 18px;
                            font-weight: bold;
                            display: inline-block;
                            border: none;
                            cursor: pointer;
                            transition: all 0.3s ease;
                            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                        }
                        .submit-button:hover {
                            transform: translateY(-3px);
                            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <i class="fas fa-exclamation-circle icon-warning"></i>
                        <div class="title">Email Já Cadastrado</div>
                        <div class="message">
                            O email <strong style="color: #dc3545;">' . htmlspecialchars($email) . '</strong> também já está cadastrado. Por favor, insira outro email.
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
        $stmt = $pdo->prepare("INSERT INTO config (nome, email, username, telefone_whatsapp, token, ativo, email_menuia, data_cadastro, plano, api, id_cliente_stripe, id_assinatura_stripe, senha_menuia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeConfig, $email, $username, $telefone, 'f4QGNF6L4KhSNvEWP1VTHaDAI57bDTEj89Kemni1iZckHne3j9', 'teste', 'rtcorretora@gmail.com', $dataAtual, $plano, 'Sim', $customer_id, $subscription->id, 'mof36001']);
        $idConta = $pdo->lastInsertId();

        // Inserir na tabela usuarios
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, username, email, cpf, senha, nivel, data, ativo, telefone, atendimento, id_conta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeCliente, $username, $email, $cpf, $defaultPassword, 'administrador', $dataAtual, 'teste', $telefone, 'Sim', $idConta]);

        // Inserir na tabela clientes
        $stmt = $pdo2->prepare("INSERT INTO clientes (nome, cpf, telefone, email, data_cad, ativo, data_pgto, valor, frequencia, plano, forma_pgto, pago, id_conta, id_cliente_stripe, usuario, servidor, banco, senha, status, id_assinatura_stripe, plan_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeCliente, $cpf, $telefone, $email, $dataAtual, 'teste', $dataAtual, $valor, $frequencia, $plano, $formaPgto, 'Sim', $idConta, $customer_id, 'skysee', 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com', 'barbearia', '9vtYvJly8PK6zHahjPUg', $customer_status, $subscription->id, $priceId]);

        // Armazena o session_id processado na sessão
        $_SESSION['processed_session_ids'][$session_id] = [
            'email' => $email,
            'password' => $defaultPassword
        ];

        // Fechar conexões
        $stmt = null;
        $pdo = null;
        $pdo2 = null;

        echo "<!-- Dados registrados com sucesso para ID Conta: $idConta -->";
    } catch (Exception $e) {
        file_put_contents('/var/www/agendar/error_log.txt', date('Y-m-d H:i:s') . " - Erro ao processar sessão: " . $e->getMessage() . "\n", FILE_APPEND);
        
        $email = 'email_nao_disponivel@example.com';
        $defaultPassword = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $trialDays = 15;
        $trialEndDate = date('d/m/Y', strtotime("+$trialDays days"));

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            header('Content-Type: text/plain; charset=utf-8');
            http_response_code(404);
            echo "Webhook error: " . $e->getMessage();
            exit;
        }

        echo "<!-- Erro ao processar sessão: " . $e->getMessage() . " -->";
    }
}

// Função para formatar o telefone
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    $phone = preg_replace('/^55/', '', $phone);

    if (strlen($phone) == 10) {
        return sprintf('(%02d) %05d-%04d', substr($phone, 0, 2), substr($phone, 2, 5), substr($phone, 7, 4));
    } elseif (strlen($phone) == 11) {
        return sprintf('(%02d) %04d-%04d', substr($phone, 0, 2), substr($phone, 2, 5), substr($phone, 7, 4));
    } else {
        return '11999999999';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Concluído</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #28a745, #218838); /* Gradiente verde */
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); /* Sombra mais suave e profunda */
            text-align: center;
            max-width: 550px;
            width: 100%;
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-success {
            color: #28a745;
            font-size: 80px; /* Ícone grande */
            margin-bottom: 25px;
            animation: bounceIn 0.6s ease-out;
        }
        @keyframes bounceIn {
            0% { opacity: 0; transform: scale3d(0.3, 0.3, 0.3); }
            20% { transform: scale3d(1.1, 1.1, 1.1); }
            40% { transform: scale3d(0.9, 0.9, 0.9); }
            60% { opacity: 1; transform: scale3d(1.03, 1.03, 1.03); }
            80% { transform: scale3d(0.97, 0.97, 0.97); }
            to { opacity: 1; transform: scale3d(1, 1, 1); }
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
            margin-bottom: 15px;
            line-height: 1.6;
        }
        .credentials {
            font-size: 17px;
            color: #333;
            margin-bottom: 12px;
        }
        .bold-highlight {
            font-weight: bold;
            color: #28a745; /* Cor de destaque verde */
        }
        .note {
            font-size: 14px;
            color: #dc3545; /* Vermelho para observações importantes */
            margin-top: 25px;
            margin-bottom: 35px;
            line-height: 1.5;
        }
        .login-button {
            background: linear-gradient(45deg, #28a745, #218838); /* Gradiente no botão */
            color: #fff;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .login-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <i class="fas fa-check-circle icon-success"></i>
        <div class="title">Cadastro Concluído com Sucesso!</div>
        <div class="message">
            Seu cadastro foi efetuado com sucesso. Você possui um período de teste gratuito que termina em 
            <span class="bold-highlight"><?php echo $trialEndDate; ?></span>.
        </div>
        <div class="message">Para acessar o app, utilize as seguintes credenciais:</div>
        <div class="credentials">
            <span class="bold-highlight">Login:</span> <?php echo $email; ?>
        </div>
        <div class="credentials">
            <span class="bold-highlight">Senha:</span> <?php echo $defaultPassword; ?>
        </div>
        <div class="note">
            *Observação: Acesse o seu perfil e as configurações do sistema assim que entrar no APP para inserir seus dados corretamente.
        </div>
        <a href="intent://agendar.skysee.com.br#Intent;scheme=https;package=com.example.app;end" class="login-button">Ir para o APP</a>
    </div>
</body>
</html>
