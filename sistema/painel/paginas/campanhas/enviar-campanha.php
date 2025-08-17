<?php
session_start();
require_once("../../../conexao.php"); // Ajuste o caminho conforme necessário

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Erro desconhecido', 'details' => []];

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
        $query .= " AND DATEDIFF(CURDATE(), data_retorno) BETWEEN 30 AND 90";
    } elseif ($segmento === '90-180') {
        $query .= " AND DATEDIFF(CURDATE(), data_retorno) BETWEEN 91 AND 180";
    } elseif ($segmento === '180-365') {
        $query .= " AND DATEDIFF(CURDATE(), data_retorno) BETWEEN 181 AND 365";
    } elseif ($segmento === '365+') {
        $query .= " AND DATEDIFF(CURDATE(), data_retorno) > 365";
    } elseif ($segmento === 'sem-retorno') {
        $query .= " AND data_retorno IS NULL";
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
            SELECT codigo, valor, tipo_desconto, data_validade
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
    $failed_count = 0;
    $details = [];

    // Calcular data inicial para agendamento (1 minuto a partir de agora)
    $data_mensagem = new DateTime();
    $data_mensagem->modify('+1 minute');

    foreach ($clientes as $cliente) {
        // Validar e formatar telefone
        $telefone = preg_replace('/[ ()-]+/', '', $cliente['telefone']);
        if (!preg_match('/^\d{10,11}$/', $telefone)) {
            $details[] = "Telefone inválido para {$cliente['nome']}: {$cliente['telefone']}";
            $failed_count++;
            continue;
        }
        $telefone = '55' . $telefone;

        // Montar mensagem
        $mensagem = "Olá, {$cliente['nome']}! 😊\nSentimos sua falta, na *{$nome_sistema}*!\n";
        if ($cupom_codigo) {
            $mensagem .= "Volte e use o cupom *{$cupom_codigo}* para um desconto especial!\n\n ";
        } else {
            $mensagem .= "Que tal voltar para um serviço especial?\n\n ";
        }
        $mensagem .= "Agende agora:\n https://markai.skysee.com.br/agendamentos.php?u={$id_conta}";
        $mensagem = str_replace("%0A", "\n", $mensagem);

        // Formatar data de agendamento (Y-m-d H:i:s)
        $data_mensagem_str = $data_mensagem->format('Y-m-d H:i:s');
        
        // Enviar mensagem via API do Menuia com agendamento
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
        'agendamento' => $data_mensagem_str
        ),
      ));

        $api_response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            $details[] = "Erro cURL para {$cliente['nome']} ({$telefone}): {$curl_error}";
            $failed_count++;
        } else {
            $api_response = json_decode($api_response, true);
            if ($http_code == 200 && isset($api_response['status']) && $api_response['status'] == 200) {
                $success_count++;
                
                // Opcional: salvar log da mensagem
                // save_log($pdo, 'f4QGNF6L4KhSNvEWP1VTHaDAI57bDTEj89Kemni1iZckHne3j9', 'CAMPANHA', $api_response, 'texto', $telefone, $mensagem, $id_conta);
            } else {
                $details[] = "Erro API para {$cliente['nome']} ({$telefone}): HTTP {$http_code}, Resposta: " . json_encode($api_response);
                $failed_count++;
            }
        }

        // Incrementar data de agendamento em 60 segundos
        $data_mensagem->modify('+60 seconds');
    }

    $response = [
        'success' => $success_count > 0,
        'message' => "Agendamentos iniciados para $success_count de " . count($clientes) . " clientes. Mensagens serão disparadas a cada 60 segundos. Falhas: $failed_count.",
        'clientes' => $clientes,
        'details' => $details
    ];
    if ($success_count < count($clientes)) {
        http_response_code(207); // Sucesso parcial
    }
} catch (Exception $e) {
    $response['message'] = 'Erro: ' . $e->getMessage();
    $response['details'] = ['Exception' => $e->getMessage()];
    error_log('Erro em enviar-campanha.php: ' . $e->getMessage());
}

echo json_encode($response);
?>