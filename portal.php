<?php
require_once("sistema/conexao.php");

// Inclui a biblioteca do Stripe
require './vendor/autoload.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\Subscription;

// Carrega as variáveis de ambiente do arquivo .env manualmente
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    } else {
        error_log('Erro: Arquivo .env não encontrado em: ' . $envFile);
    }

    // Configurar Stripe
    $stripeKey = getenv('STRIPE_SECRET_KEY');
    if (empty($stripeKey)) {
        throw new Exception('Chave secreta do Stripe não configurada. Verifique a variável de ambiente STRIPE_SECRET_KEY.');
    }
    Stripe::setApiKey($stripeKey);
    Stripe::setApiVersion('2023-10-16');

header('Content-Type: application/json');

// Supondo que você já tem o ID do cliente logado
// Use a forma correta do seu sistema (sessão, cookie, etc.)
$customerId = $cliente_stripe; 

try {
    // Crie a sessão do Portal do Cliente
    $session = \Stripe\BillingPortal\Session::create([
        'customer' => $customerId,
        'return_url' => 'https://www.markai.skysee.com.br/login.php',
    ]);

    // Redireciona o cliente para o Portal da Stripe
    header("Location: " . $session->url);
    exit;

} catch (\Stripe\Exception\ApiErrorException $e) {
    // Trate erros da API Stripe, como um customer ID inválido
    // Para simplificar, vamos apenas mostrar a mensagem de erro
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>