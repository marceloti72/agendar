<?php
// Arquivo: paginas/SUA_PAGINA/excluir_produto.php (Completo e Seguro)

require_once("../../../conexao.php"); // Ajuste o caminho se necessário
@session_start(); // Inicia ou continua a sessão

header('Content-Type: application/json'); // Define tipo de resposta como JSON
// Resposta padrão inicializada como erro
$response = ['success' => false, 'message' => 'Erro desconhecido ao excluir produto.'];

// --- Validações Iniciais (Sessão, Método POST) ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido.';
    echo json_encode($response); exit;
}
if (!isset($_SESSION['id_conta'])) { // Verifica se a sessão da conta existe
    $response['message'] = 'Sessão inválida ou expirada.';
    echo json_encode($response); exit;
}
$id_conta_corrente = $_SESSION['id_conta'];

// --- Recebe Dados do POST e Valida ---
$id_receber = isset($_POST['id_receber']) ? (int)$_POST['id_receber'] : 0;
$id_comanda = isset($_POST['id_comanda']) ? (int)$_POST['id_comanda'] : 0;

// --- Inicia Transação ---
$pdo->beginTransaction();

try {
    // 1. Busca o valor do item e outros campos necessários para verificar assinatura
    $query_check = $pdo->prepare("SELECT valor, cliente, valor2, tipo, servico FROM receber WHERE id = :id_rec AND id_conta = :id_conta AND comanda = :id_comanda AND tipo = 'Serviço'");
    $query_check->execute([
        ':id_rec' => $id_receber,
        ':id_conta' => $id_conta_corrente,
        ':id_comanda' => $id_comanda
    ]);
    $item_rec = $query_check->fetch(PDO::FETCH_ASSOC);

    if (!$item_rec) {
        throw new Exception("Lançamento de venda (ID: {$id_receber}) não encontrado para esta comanda ou conta.");
    }
    $valor_a_subtrair = $item_rec['valor']; // Valor a ser subtraído da comanda
    $id_assinante = $item_rec['cliente'];
    $valor2 = $item_rec['valor2'];
    $tipo = $item_rec['tipo'];
    $id_servico = $item_rec['servico'];

    // 2. Verifica e deleta o último registro de uso de serviço para assinantes
    if ($id_assinante !== null && $id_assinante > 0 && $valor2 == 0 && $tipo === 'Serviço') {
        $query_del_uso = $pdo->prepare("DELETE FROM assinantes_servicos_usados WHERE id_assinante = :id_assinante AND id_servico = :id_servico AND id_conta = :id_conta ORDER BY id DESC LIMIT 1");
        $query_del_uso->bindValue(':id_assinante', $id_assinante, PDO::PARAM_INT);
        $query_del_uso->bindValue(':id_servico', $id_servico, PDO::PARAM_INT);
        $query_del_uso->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_del_uso->execute();
        
    }

    // 3. Excluir a comissão associada da tabela 'pagar' PRIMEIRO (se houver)
    $query_del_pag = $pdo->prepare("DELETE FROM pagar WHERE id_ref = :id_receber AND tipo = 'Comissão' AND id_conta = :id_conta");
    $query_del_pag->bindValue(':id_receber', $id_receber, PDO::PARAM_INT);
    $query_del_pag->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_del_pag->execute();
    if ($query_del_pag->rowCount() <= 0) {
        error_log("Aviso: Nenhuma comissão encontrada em 'pagar' para id_ref {$id_receber} ao excluir produto.");
    }

    // 4. Excluir o registro da tabela 'receber'
    $query_del_rec = $pdo->prepare("DELETE FROM receber WHERE id = :id_receber AND id_conta = :id_conta");
    $query_del_rec->bindValue(':id_receber', $id_receber, PDO::PARAM_INT);
    $query_del_rec->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_del_rec->execute();

    if ($query_del_rec->rowCount() <= 0) {
        throw new Exception("Falha ao excluir o lançamento da venda da tabela 'receber' (Item não encontrado ou erro de permissão?).");
    }

    // 5. Atualizar (subtrair) o valor total na tabela 'comandas'
    if ($valor_a_subtrair >= 0) {
        $query_upd_com = $pdo->prepare("UPDATE comandas SET valor = valor - :valor_sub WHERE id = :id_comanda AND id_conta = :id_conta");
        $query_upd_com->bindValue(':valor_sub', $valor_a_subtrair);
        $query_upd_com->bindValue(':id_comanda', $id_comanda, PDO::PARAM_INT);
        $query_upd_com->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_upd_com->execute();
        if ($query_upd_com->rowCount() <= 0) {
            error_log("Aviso: Valor total da comanda ID {$id_comanda} não foi atualizado após excluir item receber ID {$id_receber}. Verificar se comanda existe.");
        }
    }

    // 6. Se tudo deu certo, confirma a transação
    $pdo->commit();
    $response['success'] = true;
    $response['message'] = 'Produto removido da comanda e estoque revertido!';

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro de Banco de Dados ao excluir: ' . $e->getMessage();
    error_log("Erro PDO em excluir_produto.php: " . $e->getMessage() . " | ID Receber: " . $id_receber);
    $response['success'] = false;
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral em excluir_produto.php: " . $e->getMessage() . " | ID Receber: " . $id_receber);
    $response['success'] = false;
}

// --- Envia Resposta JSON Final ---
echo json_encode($response);
?>