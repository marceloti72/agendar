<?php
function save_log($pdo, $token, $instancia, $response, $tipo, $telefone, $mensagem)
{
    $check = (empty($token) ? inserir_log($pdo, $tipo, '401', 'Authkey Invalida') : (empty($instancia) ? inserir_log($pdo, $tipo, '401', 'Appkey Invalida') : true));

    if($check !== true)
    {
        return false;
    }
    elseif(isset($response)) {
        $erro = $response['status'] ?? 501;
        $msg = $response['message'] ?? "Erro interno";
        inserir_log($pdo, $tipo, $erro, $msg, $telefone, $mensagem);
    } else {
        inserir_log($pdo, $tipo, 500, 'Ocorreu um erro interno!', $telefone, $mensagem);
    }
}

function inserir_log($pdo, $tipo, $codigo, $mensagem_status, $telefone, $mensagem)
{
    try {
        $query = $pdo->prepare("INSERT INTO logs SET tipo = :tipo, codigo_status = :codigo, mensagem_status = :mensagem_status, destinatario = :destinatario, mensagem = :mensagem");
        $query->bindValue(":mensagem", $mensagem);
        $query->bindValue(":destinatario", $telefone);
        $query->bindValue(":tipo", $tipo);
        $query->bindValue(":codigo", $codigo);
        $query->bindValue(":mensagem_status", $mensagem_status);
        $query->execute();
    } catch (PDOException $e) {
        $error_message = "[" . date("Y-m-d H:i:s") . "] Erro de execução da query: " . $e->getMessage() . "\n";
        file_put_contents('error.log', $error_message, FILE_APPEND);
    }
}
?>
