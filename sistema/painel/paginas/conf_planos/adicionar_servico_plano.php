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

$id_plano = isset($_POST['id_plano']) ? (int)$_POST['id_plano'] : 0;
$id_servico = isset($_POST['id_servico']) ? (int)$_POST['id_servico'] : 0;
$quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1; // Padrão 1
$id_conta_form = isset($_POST['id_conta']) ? (int)$_POST['id_conta'] : 0;

// Validação
// if ($id_plano <= 0 || $id_servico <= 0 || $quantidade < 0 || $id_conta_form !== $id_conta_corrente) {
//     $response['message'] = 'Dados inválidos fornecidos.';
//     echo json_encode($response);
//     exit;
// }

try {
    // Verifica se já existe para evitar erro de UNIQUE (opcional, mas bom)
    $check = $pdo->prepare("SELECT id FROM planos_servicos WHERE id_plano = :id_plano AND id_servico = :id_servico AND id_conta = :id_conta");
    $check->execute([':id_plano' => $id_plano, ':id_servico' => $id_servico, ':id_conta' => $id_conta_corrente]);
    if ($check->rowCount() > 0) {
        $response['message'] = 'Este serviço já está incluído neste plano.';
        echo json_encode($response);
        exit;
    }

    // Insere a associação
    $query = $pdo->prepare("INSERT INTO planos_servicos (id_plano, id_servico, quantidade, id_conta) VALUES (:id_plano, :id_servico, :quantidade, :id_conta)");
    $query->bindValue(':id_plano', $id_plano, PDO::PARAM_INT);
    $query->bindValue(':id_servico', $id_servico, PDO::PARAM_INT);
    $query->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
    $query->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query->execute();

    if ($query->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Serviço adicionado ao plano!';
    } else {
        $response['message'] = 'Não foi possível adicionar o serviço.';
    }

} catch (PDOException $e) {
    $response['message'] = 'Erro ao adicionar serviço: ' . $e->getMessage();
    // Verifica erro de chave duplicada (código 23000 / 1062)
    if($e->getCode() == '23000' || $e->getCode() == 1062) {
        $response['message'] = 'Este serviço já está incluído neste plano.';
    }
    error_log("Erro SQL em adicionar_servico_plano: " . $e->getMessage());
}

echo json_encode($response);
?>