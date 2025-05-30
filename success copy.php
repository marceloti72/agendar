<?php
// Configurações iniciais
header('Content-Type: text/html; charset=utf-8');
session_start();

// Obtém o session_id da URL
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;

$email = 'carregando...';
$trialDays = 15;
$trialEndDate = date('d/m/Y', strtotime("+$trialDays days"));
$defaultPassword = '123';

if ($session_id) {
    // Faz a chamada à API para buscar os dados da sessão
    $apiUrl = "https://agendar.skysee.com.br:3001/api/get-session-data?session_id=" . urlencode($session_id);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['email']) && $data['email']) {
            $email = htmlspecialchars($data['email']);
        } else {
            $email = 'email_nao_disponivel@example.com';
        }
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
            background-color: #f8f9fa;
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
            color: #4a148c;
        }
        .note {
            font-size: 14px;
            color: #dc3545;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .login-button {
            background-color: #4a148c;
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
            *Observação: Por favor, troque sua senha assim que acessar o app, no seu perfil.
        </div>
        <a href="https://agendar.skysee.com.br" class="login-button">Ir para a Página de Login</a>
        <div class="bottom-spacer"></div>
    </div>
</body>
</html>