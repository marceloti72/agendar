<?php
session_start();
require_once("../conexao.php"); // Ajuste o caminho conforme necessário
$id_conta = $_SESSION['id_conta'];

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Erro desconhecido', 'details' => []];

try {
    // Verificar autenticação
    if (!isset($_SESSION['id_conta']) || $_SESSION['id_conta'] <= 0) {
        $response['message'] = 'Usuário não autenticado';
        echo json_encode($response);
        exit;
    }

    // Obter dados do POST    
    $clientes = isset($_POST['clientes']) ? $_POST['clientes'] : null;
    $oferecer_presente = isset($_POST['oferecer_presente']) ? $_POST['oferecer_presente'] : 'Não';
    $id_cupom = isset($_POST['id_cupom']) ? (int)$_POST['id_cupom'] : null;
    
    if (!is_array($clientes) || empty($clientes)) {
        $response['message'] = 'Lista de clientes é obrigatória e não pode estar vazia';
        echo json_encode($response);
        exit;
    }

    if ($oferecer_presente === 'Sim' && (!$id_cupom || $id_cupom <= 0)) {
        $response['message'] = 'ID do cupom é obrigatório quando oferecer presente é Sim';
        echo json_encode($response);
        exit;
    }

    // Buscar cupom, se aplicável
    $cupom = null;
    if ($oferecer_presente === 'Sim') {
        $query = $pdo->prepare("
            SELECT codigo, valor, tipo_desconto, data_validade
            FROM cupons
            WHERE id = :id_cupom AND id_conta = :id_conta
            AND data_validade >= CURDATE()
            AND (usos_atuais < max_usos OR usos_atuais IS NULL)
        ");
        $query->bindValue(':id_cupom', $id_cupom, PDO::PARAM_INT);
        $query->bindValue(':id_conta', $id_conta, PDO::PARAM_INT);
        $query->execute();
        $cupom = $query->fetch(PDO::FETCH_ASSOC);

        if (!$cupom) {
            $response['message'] = 'Cupom inválido ou expirado';
            echo json_encode($response);
            exit;
        }
    }

    // Processar clientes
    $success_count = 0;
    $failed_count = 0;
    $details = [];

    foreach ($clientes as $cliente) {
        $nome = isset($cliente['nome']) ? trim($cliente['nome']) : '';
        $telefone = isset($cliente['telefone']) ? trim($cliente['telefone']) : '';

        // Validar dados do cliente
        if (empty($nome) || empty($telefone)) {
            $details[] = "Nome ou telefone ausente para cliente: " . json_encode($cliente);
            $failed_count++;
            continue;
        }

        // Normalizar telefone
        $telefone = preg_replace('/[ ()-]+/', '', $telefone);
        if (!preg_match('/^\d{10,11}$/', $telefone)) {
            $details[] = "Telefone inválido para {$nome}: {$telefone}";
            $failed_count++;
            continue;
        }
        $telefone = '55' . $telefone;
        if (strlen($telefone) > 15) {
            $telefone = substr($telefone, 0, 15);
        }

        // Montar mensagem
        $linkAgendamento = "https://markai.skysee.com.br/agendamentos.php?u={$username}";
        $mensagem = "Parabéns pelo seu aniversário, {$nome}! 🎉\nA *{$nome_sistema}* deseja um dia especial cheio de alegria!\n\n";
        if ($oferecer_presente === 'Sim' && $cupom) {
            $descontoFormatado = $cupom['tipo_desconto'] === 'porcentagem'
                ? "{$cupom['valor']}%"
                : "R$" . number_format($cupom['valor'], 2, ',', '.');
            $validade = $cupom['data_validade']
                ? (new DateTime($cupom['data_validade']))->format('d/m/Y')
                : (new DateTime())->modify('+30 days')->format('d/m/Y');
            $mensagem .= "Como presente, oferecemos o cupom *{$cupom['codigo']}* com *{$descontoFormatado}* de desconto, válido até {$validade}.\n\nAcesse nosso link e insira seu cupom. Aproveite! {$linkAgendamento}";
        } else {
            $mensagem .= "Que tal celebrar conosco? Agende sua visita em: {$linkAgendamento}";
        }
        $mensagem = str_replace("%0A", "\n", $mensagem);

        // Enviar mensagem via API do Menuia imediatamente
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://chatbot.menuia.com/api/create-message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'appkey' => $instancia,
                'authkey' => $token,
                'to' => $telefone,
                'message' => $mensagem
            ]
        ]);

        $api_response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            $details[] = "Erro cURL para {$nome} ({$telefone}): {$curl_error}";
            $failed_count++;
        } else {
            $api_response = json_decode($api_response, true);
            if ($http_code == 200 && isset($api_response['status']) && $api_response['status'] == 200) {
                $success_count++;
                // Opcional: salvar log da mensagem (descomentar se necessário)
                // save_log($pdo, 'f4QGNF6L4KhSNvEWP1VTHaDAI57bDTEj89Kemni1iZckHne3j9', 'ANIVERSARIO', $api_response, 'texto', $telefone, $mensagem, $id_conta);
            } else {
                $details[] = "Erro API para {$nome} ({$telefone}): HTTP {$http_code}, Resposta: " . json_encode($api_response);
                $failed_count++;
            }
        }
    }

    // Montar resposta
    $response = [
        'success' => $success_count > 0,
        'message' => "Mensagens enviadas para {$success_count} de " . count($clientes) . " clientes. Falhas: {$failed_count}.",
        'details' => $details
    ];

    if ($success_count < count($clientes)) {
        http_response_code(207); // Sucesso parcial
    }

} catch (Exception $e) {
    $response['message'] = 'Erro: ' . $e->getMessage();
    $response['details'] = ['Exception' => $e->getMessage()];
    error_log('Erro em send-birthday-message.php: ' . $e->getMessage());
}

echo json_encode($response);
?>