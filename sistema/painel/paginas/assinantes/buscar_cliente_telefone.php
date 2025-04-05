<?php
require_once("../../../conexao.php");
@session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Erro: Telefone não fornecido.', 'cliente' => null];

if (!isset($_SESSION['id_conta'])) {
    $response['message'] = 'Sessão inválida.';
    echo json_encode($response); exit;
}
$id_conta_corrente = $_SESSION['id_conta'];

if (isset($_POST['telefone'])) {
    $telefone = trim($_POST['telefone']);
    // Opcional: Limpar máscara do telefone antes de buscar, dependendo de como está salvo
    // $telefone_limpo = preg_replace('/[^0-9]/', '', $telefone);

    if (!empty($telefone)) {
        try {
            // Busca pelo telefone EXATO (ou use LIKE se a máscara não for limpa)
            // Seleciona apenas os campos necessários
            $query = $pdo->prepare("SELECT id, nome, cpf, email FROM clientes WHERE telefone = :telefone AND id_conta = :id_conta LIMIT 1");
            $query->bindValue(':telefone', $telefone); // Usa o telefone com máscara se ele está salvo assim
            $query->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
            $query->execute();
            $cliente = $query->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                $response['success'] = true;
                $response['message'] = 'Cliente encontrado.';
                $response['cliente'] = $cliente; // Retorna os dados do cliente
            } else {
                $response['message'] = 'Novo cliente.'; // Indica que não encontrou
                 $response['success'] = false; // Mantém false para indicar que não achou existente
            }
        } catch (PDOException $e) {
            $response['message'] = 'Erro ao buscar cliente: ' . $e->getMessage();
            error_log("Erro SQL buscar_cliente_telefone: " . $e->getMessage());
        }
    } else {
         $response['message'] = 'Telefone vazio.';
    }
}

echo json_encode($response);
?>