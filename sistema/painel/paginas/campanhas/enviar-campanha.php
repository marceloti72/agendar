<?php
session_start();
require_once("../../conexao.php"); // Ajuste o caminho conforme necessário

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Erro desconhecido'];

try {
    $id_conta = isset($_POST['id_conta']) ? (int)$_POST['id_conta'] : 0;
    $segmento = isset($_POST['segmento']) ? $_POST['segmento'] : null;
    $oferecer_cupom = isset($_POST['oferecer_cupom']) ? $_POST['oferecer_cupom'] : 'Não';
    $id_cupom = isset($_POST['id_cupom']) ? (int)$_POST['id_cupom'] : null;

    if ($id_conta <= 0 || !$segmento) {
        $response['message'] = 'Parâmetros inválidos';
        echo json_encode($response);
        exit;
    }

    // Validar segmento
    $segmentos_validos = ['30-90', '90-180', '180-365', '365+', 'sem-retorno'];
    if (!in_array($segmento, $segmentos_validos)) {
        $response['message'] = 'Segmento inválido';
        echo json_encode($response);
        exit;
    }

    // Buscar clientes do segmento
    $query = "SELECT id, nome, telefone FROM clientes WHERE id_conta = :id_conta";
    if ($segmento === '30-90') {
        $query .= " AND DATEDIFF(CURDATE(), ultima_visita) BETWEEN 30 AND 90";
    } elseif ($segmento === '90-180') {
        $query .= " AND DATEDIFF(CURDATE(), ultima_visita) BETWEEN 91 AND 180";
    } elseif ($segmento === '180-365') {
        $query .= " AND DATEDIFF(CURDATE(), ultima_visita) BETWEEN 181 AND 365";
    } elseif ($segmento === '365+') {
        $query .= " AND DATEDIFF(CURDATE(), ultima_visita) > 365";
    } elseif ($segmento === 'sem-retorno') {
        $query .= " AND ultima_visita IS NULL";
    }

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':id_conta', $id_conta);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($clientes)) {
        $response['message'] = 'Nenhum cliente encontrado para este segmento';
        echo json_encode($response);
        exit;
    }

    // Buscar informações do cupom, se aplicável
    $cupom_codigo = null;
    if ($oferecer_cupom === 'Sim' && $id_cupom) {
        $query = $pdo->prepare("
            SELECT codigo, valor, tipo_desconto
            FROM cupons
            WHERE id = :id_cupom AND id_conta = :id_conta
            AND data_validade >= CURDATE()
            AND (usos_atuais < max_usos OR usos_atuais IS NULL)
        ");
        $query->bindValue(':id_cupom', $id_cupom);
        $query->bindValue(':id_conta', $id_conta);
        $query->execute();
        $cupom = $query->fetch(PDO::FETCH_ASSOC);
        if ($cupom) {
            $cupom_codigo = $cupom['codigo'] . ' (' . $cupom['valor'] . ($cupom['tipo_desconto'] === 'porcentagem' ? '%' : 'R$') . ')';
        } else {
            $response['message'] = 'Cupom inválido ou expirado';
            echo json_encode($response);
            exit;
        }
    }

    // Configurações da API do Menuia    
    $success_count = 0;

    foreach ($clientes as $cliente) {
        $mensagem = "Olá, {$cliente['nome']}!\nSentimos sua falta! ";
        if ($cupom_codigo) {
            $mensagem .= "Volte e use o cupom {$cupom_codigo} para um desconto especial! ";
        }
        $mensagem .= "Agende agora: https://seusite.com/agendamento";
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
                'to' => $cliente['telefone'],
                'message' => $mensagem,
            ),
        ));

        $api_response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $api_response = json_decode($api_response, true);

        if ($http_code == 200 && isset($api_response['status']) && $api_response['status'] == 'success') {
            $success_count++;
            // Opcional: salvar log da mensagem
            // save_log($pdo, 'f4QGNF6L4KhSNvEWP1VTHaDAI57bDTEj89Kemni1iZckHne3j9', 'CAMPANHA', $api_response, 'texto', $cliente['telefone'], $mensagem, $id_conta);
        }
    }

    // Atualizar usos do cupom, se aplicável
    if ($cupom_codigo && $success_count > 0) {
        $query = $pdo->prepare("
            UPDATE cupons
            SET usos_atuais = usos_atuais + :success_count
            WHERE id = :id_cupom AND id_conta = :id_conta
        ");
        $query->bindValue(':success_count', $success_count);
        $query->bindValue(':id_cupom', $id_cupom);
        $query->bindValue(':id_conta', $id_conta);
        $query->execute();
    }

    $response = [
        'success' => true,
        'message' => "Mensagens enviadas para $success_count de " . count($clientes) . " clientes.",
        'clientes' => $clientes
    ];
    if ($success_count < count($clientes)) {
        http_response_code(207); // Sucesso parcial
    }
} catch (Exception $e) {
    $response['message'] = 'Erro: ' . $e->getMessage();
}

echo json_encode($response);
?>