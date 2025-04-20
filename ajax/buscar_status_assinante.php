<?php
// Arquivo: ajax/buscar_status_assinante.php (Exemplo de Caminho)

require_once("../sistema/conexao.php"); // Ajuste o caminho
@session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Erro.', 'is_assinante' => false, 'cliente_id' => null, 'nome_cliente' => null];

if (!isset($_SESSION['id_conta'])) {
    $response['message'] = 'Sessão inválida.'; echo json_encode($response); exit;
}
$id_conta = $_SESSION['id_conta'];
$telefone_busca = isset($_POST['telefone']) ? $_POST['telefone'] : '';

if (empty($telefone_busca)) {
     $response['message'] = 'Telefone não fornecido.'; echo json_encode($response); exit;
}else{
    $_SESSION['telefone_user'] = $telefone_busca;
}

try {
    // 1. Encontra o cliente pelo telefone
    $query_cli = $pdo->prepare("SELECT id, nome FROM clientes WHERE telefone = :telefone AND id_conta = :id_conta LIMIT 1");
    $query_cli->execute([':telefone' => $telefone_busca, ':id_conta' => $id_conta]);
    $cliente = $query_cli->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $response['cliente_id'] = $cliente['id'];
        $response['nome_cliente'] = $cliente['nome'];
        $response['success'] = true; // Encontrou o cliente

        // 2. Verifica se ele é assinante ativo
        $query_ass = $pdo->prepare("SELECT id FROM assinantes WHERE id_cliente = :id_cliente AND id_conta = :id_conta AND ativo = 1 AND data_vencimento >= CURDATE()");
        $query_ass->execute([':id_cliente' => $cliente['id'], ':id_conta' => $id_conta]);
        if ($query_ass->rowCount() > 0) {
            $response['is_assinante'] = true; // É assinante ativo
            $response['message'] = 'Cliente encontrado (Assinante Ativo).';
        } else {
             $response['is_assinante'] = false; // Não é assinante ativo
             $response['message'] = 'Cliente encontrado (Não Assinante).';
        }
    } else {
        $response['message'] = 'Cliente não encontrado.'; // Não achou cliente com esse telefone
         $response['success'] = false; // Cliente não encontrado
    }

} catch (PDOException $e) {
     $response['message'] = 'Erro ao buscar status do assinante.';
     error_log("Erro buscar_status_assinante: " . $e->getMessage());
}

echo json_encode($response);
?>