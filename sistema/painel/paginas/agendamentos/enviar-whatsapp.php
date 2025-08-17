<?php
session_start();
require_once("../../conexao.php"); // Ajuste o caminho conforme necessário

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

    // Buscar dados da comanda e cliente
    $query = $pdo->prepare("SELECT c.*, cl.telefone FROM comandas c JOIN clientes cl ON c.cliente = cl.id WHERE c.id = :id AND c.id_conta = :id_conta");
    $query->bindValue(':id', $comanda_id);
    $query->bindValue(':id_conta', $id_conta);
    $query->execute();
    $comanda = $query->fetch(PDO::FETCH_ASSOC);

    if (!$comanda) {
        $response['message'] = 'Comanda ou cliente não encontrado';
        echo json_encode($response);
        exit;
    }

    $valor = $comanda['valor_total'];
    $telefone = $comanda['telefone'];

    // Configurações da API do Menuia
    $instancia = 'SUA_INSTANCIA_MENUIA'; // Substitua pela sua appkey do Menuia
    $token = 'SEU_AUTH_TOKEN_MENUIA'; // Substitua pelo seu authkey do Menuia

    // Gerar mensagem (exemplo com link fictício; ajuste para o link real do Mercado Pago)
    $mensagem = "Olá! Aqui está o link de pagamento para a comanda #$comanda_id no valor de R$ " . number_format($valor, 2, ',', '.') . ": https://seusite.com/pagamento/$comanda_id";
    $mensagem = str_replace("%0A", "\n", $mensagem);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://chatbot.menuia.com/api/create-message',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'appkey' => $instancia,
            'authkey' => $token,
            'to' => $telefone,
            'message' => $mensagem,
        ),
    ));

    $api_response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $api_response = json_decode($api_response, true);

    // Verificar se a mensagem foi enviada com sucesso
    if ($http_code == 200 && isset($api_response['status']) && $api_response['status'] == 'success') {
        $response = [
            'success' => true,
            'message' => 'Mensagem enviada com sucesso via WhatsApp'
        ];
        //save_log($pdo, 'f4QGNF6L4KhSNvEWP1VTHaDAI57bDTEj89Kemni1iZckHne3j9', 'GESTÃO', $api_response, 'texto', $telefone, $mensagem, $id_conta);
    } else {
        $response['message'] = 'Erro ao enviar mensagem: ' . ($api_response['message'] ?? 'Resposta inválida da API');
    }
} catch (Exception $e) {
    $response['message'] = 'Erro: ' . $e->getMessage();
}

echo json_encode($response);
?>