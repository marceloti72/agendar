<?php
ob_start();

session_start();
require_once("../../../conexao.php");

date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['id_usuario']) || !isset($_POST['caixa_id']) || !isset($_POST['valor_fechamento'])) {
    echo json_encode(['success' => false, 'message' => 'Dados de fechamento incompletos.']);
    exit;
}

$caixa_id = filter_var($_POST['caixa_id'], FILTER_SANITIZE_NUMBER_INT);
$valor_fechamento = filter_var($_POST['valor_fechamento'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$obs = filter_var($_POST['obs'], FILTER_SANITIZE_STRING) ?? '';
$usuario_fechamento = $_SESSION['id_usuario'];
$data_fechamento = date('Y-m-d');

try {
    // Busca o valor de abertura para calcular a quebra
    $sql_abertura = "SELECT valor_abertura FROM caixa WHERE id = :caixa_id AND id_conta = :id_conta";
    $stmt_abertura = $pdo->prepare($sql_abertura);
    $stmt_abertura->bindParam(':caixa_id', $caixa_id, PDO::PARAM_INT);
    $stmt_abertura->bindParam(':id_conta', $_SESSION['id_conta'], PDO::PARAM_INT);
    $stmt_abertura->execute();
    $caixa_aberto_data = $stmt_abertura->fetch(PDO::FETCH_ASSOC);

    if (!$caixa_aberto_data) {
        echo json_encode(['success' => false, 'message' => 'Caixa não encontrado ou não pertence a esta conta.']);
        exit;
    }

    $valor_abertura = $caixa_aberto_data['valor_abertura'];
    
    // Calcula a quebra
    // Quebra = Valor de Fechamento - Valor de Abertura
    $quebra = $valor_fechamento - $valor_abertura;

    $sql_update = "UPDATE caixa 
                   SET data_fechamento = :data_fechamento, 
                       valor_fechamento = :valor_fechamento, 
                       quebra = :quebra, 
                       usuario_fechamento = :usuario_fechamento, 
                       obs = :obs
                   WHERE id = :caixa_id AND id_conta = :id_conta";

    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':data_fechamento', $data_fechamento);
    $stmt_update->bindParam(':valor_fechamento', $valor_fechamento);
    $stmt_update->bindParam(':quebra', $quebra);
    $stmt_update->bindParam(':usuario_fechamento', $usuario_fechamento);
    $stmt_update->bindParam(':obs', $obs);
    $stmt_update->bindParam(':caixa_id', $caixa_id, PDO::PARAM_INT);
    $stmt_update->bindParam(':id_conta', $_SESSION['id_conta'], PDO::PARAM_INT);
    
    $stmt_update->execute();
    
    echo json_encode(['success' => true, 'message' => 'Caixa fechado com sucesso!']);

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao fechar o caixa: ' . $e->getMessage()]);
}
?>
