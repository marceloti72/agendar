<?php
session_start();
require_once("../../conexao.php"); // Ajuste o caminho conforme necessário

// Configurações do Mercado Pago
require 'vendor/autoload.php'; // Certifique-se de ter o SDK do Mercado Pago instalado via Composer
MercadoPago\SDK::setAccessToken('SEU_ACCESS_TOKEN'); // Substitua pelo seu token de produção ou teste

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Erro desconhecido'];

try {
    $id_conta = $_SESSION['id_conta'];
    $comanda_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($comanda_id <= 0) {
        $response['message'] = 'ID da comanda inválido';
        echo json_encode($response);
        exit;
    }

    // Buscar dados da comanda
    $query = $pdo->prepare("SELECT * FROM comandas WHERE id = :id AND id_conta = :id_conta");
    $query->bindValue(':id', $comanda_id);
    $query->bindValue(':id_conta', $id_conta);
    $query->execute();
    $comanda = $query->fetch(PDO::FETCH_ASSOC);

    if (!$comanda) {
        $response['message'] = 'Comanda não encontrada';
        echo json_encode($response);
        exit;
    }

    $valor = $comanda['valor_total'];

    // Criar preferência de pagamento no Mercado Pago
    $payment = new MercadoPago\Payment();
    $payment->transaction_amount = (float)$valor;
    $payment->description = "Pagamento da comanda #$comanda_id";
    $payment->payment_method_id = "pix";
    $payment->payer = [
        'email' => 'cliente@exemplo.com', // Substitua pelo e-mail do cliente, se disponível
    ];

    $payment->save();

    if ($payment->id && $payment->point_of_interaction) {
        $qr_code = $payment->point_of_interaction->transaction_data->qr_code;
        $response = [
            'success' => true,
            'qr_code' => $qr_code
        ];
    } else {
        $response['message'] = 'Erro ao gerar QR Code no Mercado Pago';
    }
} catch (Exception $e) {
    $response['message'] = 'Erro: ' . $e->getMessage();
}

echo json_encode($response);
?>