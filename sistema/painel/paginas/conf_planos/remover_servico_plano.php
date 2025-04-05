<?php
require_once("../../../conexao.php");
@session_start();
$id_conta_corrente = @$_SESSION['id_conta'];

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Erro desconhecido.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido.';
    echo json_encode($response);
    exit;
}

$id_plano_servico = isset($_POST['id_plano_servico']) ? (int)$_POST['id_plano_servico'] : 0;
$id_conta_form = isset($_POST['id_conta']) ? (int)$_POST['id_conta'] : 0;

// Validação
// if ($id_plano_servico <= 0 || $id_conta_form !== $id_conta_corrente) {
//     $response['message'] = 'ID da ligação ou conta inválido.';
//     echo json_encode($response);
//     exit;
// }

try {
    // Deleta a ligação específica, garantindo que pertence à conta correta
    $query = $pdo->prepare("DELETE FROM planos_servicos WHERE id = :id_ps AND id_conta = :id_conta");
    $query->bindValue(':id_ps', $id_plano_servico, PDO::PARAM_INT);
    $query->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query->execute();

    if ($query->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Serviço removido do plano.';
    } else {
        $response['message'] = 'Não foi possível remover o serviço (não encontrado ou pertence a outra conta).';
    }

} catch (PDOException $e) {
    $response['message'] = 'Erro ao remover serviço: ' . $e->getMessage();
    error_log("Erro SQL em remover_servico_plano: " . $e->getMessage());
}

echo json_encode($response);
?>