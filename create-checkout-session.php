<?php
// Forçar codificação UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
ini_set('default_charset', 'UTF-8');

require_once './vendor/autoload.php'; // Ajuste o caminho para o autoload do Stripe

use Stripe\Stripe;
use Stripe\Checkout\Session;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$response = ['error' => 'Erro desconhecido'];

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $priceId = isset($input['priceId']) ? $input['priceId'] : null;
    $coupon = isset($input['coupon']) ? $input['coupon'] : null;

    if (!$priceId) {
        http_response_code(400);
        $response['error'] = 'priceId é obrigatório';
        error_log('Erro: priceId é obrigatório');
        echo json_encode($response);
        exit;
    }

    // Configurar Stripe
    $stripeKey = getenv('STRIPE_SECRET_KEY');
    if (empty($stripeKey)) {
        throw new Exception('Chave secreta do Stripe não configurada. Verifique a variável de ambiente STRIPE_SECRET_KEY.');
    }
    Stripe::setApiKey($stripeKey);
    Stripe::setApiVersion('2023-10-16');

    $trialPeriodDays = $coupon ? 30 : 7;


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