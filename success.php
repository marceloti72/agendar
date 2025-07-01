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
header('Content-Type: text/html; charset=utf-8'); // Para requisições GET (páginas HTML)
session_start();

// Inclui a biblioteca do Stripe
require './vendor/autoload.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\Subscription;

// Configurações do Stripe
Stripe::setApiKey('sk_test_51RTXIZQwVYKsR3u1YtG7aK6S7d4sOg3Pnw8nKlXQNRBEFRGOncTdr0850Ddp1px4FRC0XuL29MaKyoy3JFiZh0Wa00reKEwQHt');
$endpoint_secret = 'whsec_aiXk2ZhwnDfOepwrRIoRNFDkC3g5Ok3e'; // Confirme essa chave no painel do Stripe

// Processamento de requisições POST (webhook ou formulário)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se é uma submissão do formulário de novo email
    if (isset($_POST['new_email'])) {
        $session_id = $_POST['session_id'] ?? null;
        $email = htmlspecialchars($_POST['new_email']);
        // Aqui você pode adicionar lógica para processar o novo email (se necessário antes de continuar)
        // Por enquanto, apenas continua o fluxo com o novo email
    } else {
        // Processamento de webhook (POST do Stripe)
        // Log inicial para depuração
        file_put_contents('/var/www/agendar/webhook_log.txt', date('Y-m-d H:i:s') . " - POST request received\n", FILE_APPEND);
        
        if (!isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            file_put_contents('/var/www/agendar/webhook_log.txt', date('Y-m-d H:i:s') . " - Stripe-Signature header missing\n", FILE_APPEND);
            http_response_code(404);
            echo "Stripe-Signature header missing";
            exit;
        }

        ob_start(); // Evitar saída acidental
        header('Content-Type: text/plain; charset=utf-8');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        // Log para depuração
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

// Verifica se o session_id já foi processado (logo no início)
if ($session_id && isset($_SESSION['processed_session_ids'][$session_id])) {
    $email = $_SESSION['processed_session_ids'][$session_id]['email'];
    $defaultPassword = $_SESSION['processed_session_ids'][$session_id]['password'];
    $trialDays = 15;
    $trialEndDate = date('d/m/Y', strtotime("+$trialDays days"));

    // Exibir mensagem de cadastro já concluído
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

// Inicializa variáveis com valores padrão para evitar erros de "undefined variable"
$email = 'carregando...';
$trialDays = 15;
$trialEndDate = date('d/m/Y', strtotime("+$trialDays days"));
// Gera senha aleatória de 6 dígitos
$defaultPassword = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Verifica se o session_id é válido antes de prosseguir
if (!$session_id) {
    // Exibir mensagem de erro para session_id inválido
    echo '<!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erro</title>
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
        </style>
    </head>
    <body>
        <div class="container">
            <div class="title">Erro</div>
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
        // Busca os dados da sessão com expansão dos itens da assinatura
        $session = Session::retrieve($session_id, [
            'expand' => ['customer', 'subscription', 'payment_intent.payment_method', 'subscription.items'],
        ]);

        $customer = $session->customer;
        $subscription = $session->subscription;
        $paymentMethod = $session->payment_intent ? $session->payment_intent->payment_method : null;

        // Obter o customer_id
        $customer_id = is_string($customer) ? $customer : ($customer->id ?? 'desconhecido');

        // Prioriza customer_details para email, nome e telefone (fornecidos no Checkout)
        $email = htmlspecialchars($session->customer_details->email ?? $customer->email ?? 'email_nao_disponivel@example.com');
        $nomeCliente = htmlspecialchars($session->customer_details->name ?? $customer->name ?? 'Cliente_' . rand(100000, 999999));
        $telefoneRaw = $session->customer_details->phone ?? '11999999999'; // Usa o telefone coletado no checkout
        // Função para formatar o telefone como (99) 99999-9999
        $telefone = formatPhoneNumber($telefoneRaw);
        $formaPgto = $paymentMethod && $paymentMethod->card ? $paymentMethod->card->brand : 'desconhecida';
        $cpf = rand(1000000000, 99999999999);

        // Obter o status da assinatura e mapear para português
        $subscription_status = is_string($subscription) ? 'desconhecido' : ($subscription->status ?? 'desconhecido');
        $status_map = [
            'trialing' => 'período de teste',
            'active' => 'ativo',
            'canceled' => 'cancelado',
            'past_due' => 'atrasado',
            'unpaid' => 'não pago',
            'incomplete' => 'incompleto',
            'incomplete_expired' => 'incompleto expirado',
            'desconhecido' => 'desconhecido'
        ];
        $customer_status = $status_map[$subscription_status] ?? 'desconhecido';

        // Log para depuração do status e ID da assinatura
        file_put_contents('/var/www/agendar/session_log.txt', date('Y-m-d H:i:s') . " - Subscription Status: " . $subscription_status . ", Mapped Status: " . $customer_status . ", Subscription ID: " . ($subscription->id ?? 'não disponível') . "\n", FILE_APPEND);

        // Log para depuração do valor
        file_put_contents('/var/www/agendar/session_log.txt', date('Y-m-d H:i:s') . " - Session Data: " . print_r($session, true) . "\n", FILE_APPEND);

        // Verificar se há uma assinatura e extrair o valor corretamente
        $valor = 0; // Valor padrão
        $priceId = null;

        // Verificar se $subscription é uma string (ID) ou um objeto
        if ($subscription) {
            if (is_string($subscription)) {
                // Se $subscription for uma string (ID), buscar o objeto completo
                $subscription = Subscription::retrieve($subscription, ['expand' => ['items']]);
                file_put_contents('/var/www/agendar/session_log.txt', date('Y-m-d H:i:s') . " - Subscription fetched from ID: " . $subscription->id . "\n", FILE_APPEND);
            }

            // Agora $subscription deve ser um objeto, podemos acessar seus dados
            if (isset($subscription->items->data[0]->price->unit_amount)) {
                $priceId = $subscription->items->data[0]->price->id;
                $valor = $subscription->items->data[0]->price->unit_amount / 100;
                file_put_contents('/var/www/agendar/session_log.txt', date('Y-m-d H:i:s') . " - Price ID: " . $priceId . ", Valor: " . $valor . "\n", FILE_APPEND);
            }
        }

        // Se não houver assinatura ou o valor ainda for 0, usar amount_total
        if ($valor == 0 && isset($session->amount_total)) {
            $valor = $session->amount_total / 100;
            file_put_contents('/var/www/agendar/session_log.txt', date('Y-m-d H:i:s') . " - Amount Total: " . $valor . "\n", FILE_APPEND);
        }

        // Se ainda assim o valor for 0, registrar um erro no log
        if ($valor == 0) {
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
            // Exibir mensagem de erro e formulário para novo email
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

        // Processar novo email do formulário (já capturado no início do POST, se aplicável)
        if (isset($_POST['new_email'])) {
            $email = htmlspecialchars($_POST['new_email']);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM config WHERE email = ?");
            $stmt->execute([$email]);
            $emailExists = $stmt->fetchColumn();

            if ($emailExists) {
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
        $stmt = $pdo->prepare("INSERT INTO config (nome, email, username, telefone_whatsapp, token, ativo, email_menuia, data_cadastro, plano, api, id_cliente_stripe, id_assinatura_stripe, senha_menuia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeConfig, $email, $username, $telefone, 'f4QGNF6L4KhSNvEWP1VTHaDAI57bDTEj89Kemni1iZckHne3j9', 'teste', 'rtcorretora@gmail.com', $dataAtual, $plano, 'Sim', $customer_id, $subscription->id, 'mof36001']);
        $idConta = $pdo->lastInsertId();

        // Inserir na tabela usuarios
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, username, email, cpf, senha, nivel, data, ativo, telefone, atendimento, id_conta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeCliente, $username, $email, $cpf, $defaultPassword, 'administrador', $dataAtual, 'teste', $telefone, 'Sim', $idConta]);

        // Inserir na tabela clientes, incluindo o id_cliente_stripe, status e id_assinatura_stripe
        $stmt = $pdo2->prepare("INSERT INTO clientes (nome, cpf, telefone, email, data_cad, ativo, data_pgto, valor, frequencia, plano, forma_pgto, pago, id_conta, id_cliente_stripe, usuario, servidor, banco, senha, status, id_assinatura_stripe) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeCliente, $cpf, $telefone, $email, $dataAtual, 'teste', $dataAtual, $valor, $frequencia, $plano, $formaPgto, 'Sim', $idConta, $customer_id, 'skysee', 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com', 'barbearia', '9vtYvJly8PK6zHahjPUg', $customer_status, $subscription->id]);

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
        // Log do erro para depuração
        file_put_contents('/var/www/agendar/error_log.txt', date('Y-m-d H:i:s') . " - Erro ao processar sessão: " . $e->getMessage() . "\n", FILE_APPEND);
        
        // Define valores padrão para evitar erros no HTML
        $email = 'email_nao_disponivel@example.com';
        $defaultPassword = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $trialDays = 15;
        $trialEndDate = date('d/m/Y', strtotime("+$trialDays days"));

        // Se for um webhook, retorna erro
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            header('Content-Type: text/plain; charset=utf-8');
            http_response_code(404);
            echo "Webhook error: " . $e->getMessage();
            exit;
        }

        // Para requisições GET, continua para exibir o HTML abaixo com os valores padrão
        echo "<!-- Erro ao processar sessão: " . $e->getMessage() . " -->"; // Log invisível para depuração
    }
}

// Função para formatar o telefone no formato (99) 99999-9999
function formatPhoneNumber($phone) {
    // Remove caracteres não numéricos e o código do país (+55)
    $phone = preg_replace('/[^0-9]/', '', $phone);
    $phone = preg_replace('/^55/', '', $phone); // Remove +55 do início, se presente

    // Verifica se o número tem pelo menos 10 dígitos (DDD + número)
    if (strlen($phone) == 10) {
        // Formato: (DD) DDDDD-DDDD
        return sprintf('(%02d) %05d-%04d', substr($phone, 0, 2), substr($phone, 2, 5), substr($phone, 7, 4));
    } elseif (strlen($phone) == 11) {
        // Formato: (DD) DDDDD-DDDD (com 9 dígitos)
        return sprintf('(%02d) %04d-%04d', substr($phone, 0, 2), substr($phone, 2, 5), substr($phone, 7, 4));
    } else {
        // Retorna o valor padrão se o formato não for válido
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