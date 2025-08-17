<?php
require_once './vendor/autoload.php'; // Ajuste o caminho para o autoload do Stripe

use Stripe\Stripe;
use Stripe\Checkout\Session;

header('Content-Type: application/json');

$response = ['error' => 'Erro desconhecido'];

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $priceId = isset($input['priceId']) ? $input['priceId'] : null;
    $coupon = isset($input['coupon']) ? $input['coupon'] : null;

    if (!$priceId) {
        http_response_code(400);
        $response['error'] = 'priceId é obrigatório';
        echo json_encode($response);
        exit;
    }
    
    // Configurações do Stripe
    Stripe::setApiKey(getenv('STRIPE_SECRET_KEY')); // Pega a chave da variável de ambiente
    $endpoint_secret = getenv('STRIPE_WEBHOOK_SECRET'); // Pega o segredo do webhook da variável de ambiente
    Stripe::setApiVersion('2023-10-16');

    $trialPeriodDays = $coupon ? 30 : 15;

    // Criar sessão de checkout
    $session = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [
            [
                'price' => $priceId,
                'quantity' => 1,
            ],
        ],
        'subscription_data' => [
            'trial_period_days' => $trialPeriodDays,
        ],
        'mode' => 'subscription',
        'success_url' => "https://markai.skysee.com.br/success.php?session_id={CHECKOUT_SESSION_ID}" . ($coupon ? "&coupon=$coupon" : ''),
        'cancel_url' => 'https://markai.skysee.com.br/cancel',
        'phone_number_collection' => [
            'enabled' => true,
        ],
    ]);

    error_log('Sessão de checkout criada: ' . $session->url);
    echo json_encode(['url' => $session->url]);
} catch (Exception $e) {
    error_log('Erro ao criar sessão de checkout: ' . $e->getMessage());
    http_response_code(500);
    $response['error'] = 'Erro ao criar sessão de checkout: ' . $e->getMessage();
    echo json_encode($response);
}
?>