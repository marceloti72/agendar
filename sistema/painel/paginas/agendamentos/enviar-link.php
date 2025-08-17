<?php
session_start();
require_once("../../conexao.php"); // Ajuste o caminho conforme necessário

// Configurações do Mercado Pago
require 'vendor/autoload.php'; // Certifique-se de ter o SDK do Mercado Pago instalado
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

    // Criar preferência de pagamento
    $preference = new MercadoPago\Preference();
    $item = new MercadoPago\Item();
    $item->title = "Comanda #$comanda_id";
    $item->quantity = 1;
    $item->unit_price = (float)$valor;
    $preference->items = [$item];
    $preference->back_urls = [
        'success' => 'https://seusite.com/sucesso',
        'failure' => 'https://seusite.com/falha',
        'pending' => 'https://seusite.com/pendente'
    ];
    $preference->auto_return = 'approved';

    $preference->save();

    if ($preference->id) {
        $response = [
            'success' => true,
            'message' => 'Link de pagamento gerado com sucesso',
            'link' => $preference->init_point
        ];
    } else {
        $response['message'] = 'Erro ao gerar link de pagamento';
    }
} catch (Exception $e) {
    $response['message'] = 'Erro: ' . $e->getMessage();
}

echo json_encode($response);
?>