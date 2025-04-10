<?php
// Arquivo: paginas/SUA_PAGINA/excluir_produto.php (Exemplo de Caminho)

require_once("../../../conexao.php"); // Ajuste o caminho
@session_start();

header('Content-Type: application/json'); // Define o tipo de resposta como JSON
$response = ['success' => false, 'message' => 'Erro desconhecido ao excluir produto.'];

// --- Validações Iniciais ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido.'; echo json_encode($response); exit;
}
if (!isset($_SESSION['id_conta'])) { // Verifica se a sessão da conta existe
    $response['message'] = 'Sessão inválida ou expirada.'; echo json_encode($response); exit;
}
$id_conta_corrente = $_SESSION['id_conta'];

// --- Recebe Dados do POST e Valida ---
// Recebe o ID do registro em 'receber' que será excluído
$id_receber = isset($_POST['id_receber']) ? (int)$_POST['id_receber'] : 0;

$id_comanda = isset($_POST['id_comanda']) ? (int)$_POST['id_comanda'] : 0; // Recebe ID da comanda

// --- Inicia Transação ---
$pdo->beginTransaction();

try {
    // 1. Busca o valor e confirma que o item pertence à conta e comanda antes de deletar
    //    Isso também recupera o valor para subtrair do total da comanda
    $query_check = $pdo->prepare("SELECT * FROM receber WHERE id = :id_rec AND id_conta = :id_conta AND comanda = :id_comanda AND tipo = 'Venda'");
    $query_check->execute([
        ':id_rec' => $id_receber,
        ':id_conta' => $id_conta_corrente,
        ':id_comanda' => $id_comanda
        ]);
    $item_rec = $query_check->fetchAll();

    if (!$item_rec) {
        throw new Exception("Lançamento de venda (ID: {$id_receber}) não encontrado para esta comanda ou conta.");
    }
    $valor_a_subtrair = $item_rec[0]['valor'];
    $produto = $item_rec[0]['produto'];
    $quantidade = $item_rec[0]['quantidade'];


    $query_check2 = $pdo->prepare("SELECT * FROM produtos WHERE id = :id_prod AND id_conta = :id_conta");
    $query_check2->execute([
        ':id_prod' => $produto,
        ':id_conta' => $id_conta_corrente        
        ]);
    $item_prod = $query_check2->fetchAll();
    $estoque = $item_prod[0]['estoque'];

    //atualizar estoque do produto
    $total_estoque = $estoque + $quantidade;


    // 2. Excluir o registro da tabela 'receber' usando Prepared Statements
    $query_del_rec = $pdo->prepare("DELETE FROM receber WHERE id = :id_receber AND id_conta = :id_conta");
    $query_del_rec->bindValue(':id_receber', $id_receber, PDO::PARAM_INT);
    $query_del_rec->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_del_rec->execute();

    // Verifica se realmente deletou (importante antes de alterar estoque/comanda)
    if ($query_del_rec->rowCount() <= 0) {
        throw new Exception("Falha ao excluir o lançamento da venda (possivelmente já excluído).");
    }

    // 3. Reverter (adicionar de volta) o estoque na tabela 'produtos' usando Prepared Statements
    $query_est = $pdo->prepare("UPDATE produtos SET estoque = :qtd WHERE id = :id_produto AND id_conta = :id_conta");
    $query_est->bindValue(':qtd', $total_estoque, PDO::PARAM_INT);
    $query_est->bindValue(':id_produto', $produto, PDO::PARAM_INT);
    $query_est->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_est->execute();
    if ($query_est->rowCount() <= 0) {
         // Loga um aviso, mas talvez não precise parar a transação se o produto foi excluído
        error_log("Aviso: Estoque para produto ID {$id_produto} não foi revertido ao excluir item. Produto existe?");
        // Se for crítico, lance uma exceção: throw new Exception("Falha ao reverter estoque.");
    }

    // 4. Atualizar (subtrair) o valor total na tabela 'comandas'
    if ($valor_a_subtrair > 0) { // Só atualiza se o item tinha valor
        $query_upd_com = $pdo->prepare("UPDATE comandas SET valor = valor - :valor_sub WHERE id = :id_comanda AND id_conta = :id_conta");
        $query_upd_com->bindValue(':valor_sub', $valor_a_subtrair);
        $query_upd_com->bindValue(':id_comanda', $id_comanda, PDO::PARAM_INT);
        $query_upd_com->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_upd_com->execute();
         if ($query_upd_com->rowCount() <= 0) {
             error_log("Aviso: Valor total da comanda ID {$id_comanda} não foi atualizado após excluir item.");
         }
    }

        // Se tudo deu certo até aqui, confirma a transação
    $pdo->commit();
    $response['success'] = true;
    $response['message'] = 'Produto removido da comanda com sucesso!';

} catch (PDOException $e) { // Erro de banco de dados
    $pdo->rollBack(); // Desfaz qualquer alteração
    $response['message'] = 'Erro DB: ' . $e->getMessage(); // Cuidado com msg em produção
    error_log("Erro PDO em excluir_produto.php: " . $e->getMessage() . " | ID Receber: " . $id_receber);
} catch (Exception $e) { // Outros erros (Ex: Falha ao excluir, estoque, etc.)
    $pdo->rollBack(); // Desfaz qualquer alteração
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral em excluir_produto.php: " . $e->getMessage() . " | ID Receber: " . $id_receber);
}

echo json_encode($response); // Envia a resposta JSON
?>