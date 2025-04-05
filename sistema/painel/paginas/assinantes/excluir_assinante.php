<?php
require_once("../../../conexao.php"); // Ajuste o caminho
@session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Erro desconhecido.'];

// Validações
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido.';
    echo json_encode($response); exit;
}
if (!isset($_SESSION['id_conta'])) {
    $response['message'] = 'Sessão inválida.';
    echo json_encode($response); exit;
}

$id_conta_corrente = $_SESSION['id_conta'];
$id_assinante = isset($_POST['id_assinante']) ? (int)$_POST['id_assinante'] : 0;
$id_conta_form = isset($_POST['id_conta']) ? (int)$_POST['id_conta'] : 0;

// if ($id_assinante <= 0 || $id_conta_form !== $id_conta_corrente) {
//     $response['message'] = 'ID do assinante ou conta inválido.';
//     echo json_encode($response); exit;
// }

// Inicia Transação
$pdo->beginTransaction();

try {
    // 1. Opcional, mas recomendado: Verificar se o assinante existe e pertence à conta
    $check = $pdo->prepare("SELECT id FROM assinantes WHERE id = :id AND id_conta = :id_conta");
    $check->execute([':id' => $id_assinante, ':id_conta' => $id_conta_corrente]);
    if ($check->rowCount() == 0) {
        throw new Exception("Assinante não encontrado para exclusão.");
    }

    // 2. Excluir registros relacionados em 'receber' (IMPORTANTE!)
    // Ajuste 'pessoa' se a coluna de ligação for outra
    $query_del_rec = $pdo->prepare("DELETE FROM receber WHERE cliente = :id_assinante AND id_conta = :id_conta AND tipo = 'Assinatura'"); // Pode ser mais específico no tipo
    $query_del_rec->bindValue(':id_assinante', $id_assinante, PDO::PARAM_INT);
    $query_del_rec->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_del_rec->execute();
    // Não verificamos rowCount aqui, pois pode não haver cobranças

    // 3. Excluir o assinante da tabela 'assinantes'
    $query_del_ass = $pdo->prepare("DELETE FROM assinantes WHERE id = :id_assinante AND id_conta = :id_conta");
    $query_del_ass->bindValue(':id_assinante', $id_assinante, PDO::PARAM_INT);
    $query_del_ass->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_del_ass->execute();

    if ($query_del_ass->rowCount() > 0) {
        $pdo->commit(); // Confirma se a exclusão do assinante funcionou
        $response['success'] = true;
        $response['message'] = 'Assinante excluído permanentemente com sucesso!';
    } else {
        // Isso não deveria acontecer se o check inicial passou, mas por segurança
        throw new Exception("Falha ao excluir o assinante após deletar cobranças.");
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro de Banco de Dados ao excluir: ' . $e->getMessage();
    error_log("Erro SQL em excluir_assinante: " . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral em excluir_assinante: " . $e->getMessage());
}

echo json_encode($response);
?>