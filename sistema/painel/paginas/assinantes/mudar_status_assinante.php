<?php
require_once("../../../conexao.php");
@session_start();

header('Content-Type: application/json'); // Sempre definir o tipo de resposta
$response = ['success' => false, 'message' => 'Erro desconhecido.'];

// Validações Iniciais Essenciais
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido.';
    echo json_encode($response); exit;
}
// Verifica se os dados essenciais da sessão existem
// if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_conta'])) {
//     $response['message'] = 'Erro: Sessão inválida ou não iniciada.';
//     echo json_encode($response); exit;
// }

$id_conta_corrente = $_SESSION['id_conta'];
$id_usuario_logado = $_SESSION['id_usuario']; // Usuário que está fazendo a ação
$id_assinante = isset($_POST['id_assinante']) ? (int)$_POST['id_assinante'] : 0;
$novo_status = isset($_POST['novo_status']) ? (int)$_POST['novo_status'] : -1; // 0 para inativar, 1 para ativar
// $id_conta_form = isset($_POST['id_conta']) ? (int)$_POST['id_conta'] : $id_conta_corrente; // Pega id_conta do post ou sessão

// --- Reativando Validação ---
if ($id_assinante <= 0 || ($novo_status !== 0 && $novo_status !== 1) ) {
     $response['message'] = 'Dados inválidos para alterar status (ID ou Status).';
     echo json_encode($response); exit;
 }
 // Opcional: Verificar id_conta se vier do POST
 // if ($id_conta_form !== $id_conta_corrente) { ... }
// --- Fim da Validação ---


// Inicia Transação para garantir atomicidade
$pdo->beginTransaction();

try {
    // 1. Verifica se o assinante pertence à conta (Importante!)
    $check = $pdo->prepare("SELECT id, id_plano, id_cliente FROM assinantes WHERE id = :id_assinante AND id_conta = :id_conta");
    $check->execute([':id_assinante' => $id_assinante, ':id_conta' => $id_conta_corrente]);
    $assinante_info = $check->fetch(PDO::FETCH_ASSOC);

    if (!$assinante_info) {
        throw new Exception("Assinante não encontrado ou não pertence a esta conta.");
    }
    $id_plano_atual_assinante = $assinante_info['id_plano']; // Guarda ID do plano atual
    $id_cliente = $assinante_info['id_cliente']; 

    // 2. Atualiza o status 'ativo' na tabela 'assinantes'
    $query_update_status = $pdo->prepare("UPDATE assinantes SET ativo = :novo_status WHERE id = :id_assinante AND id_conta = :id_conta");
    $query_update_status->bindValue(':novo_status', $novo_status, PDO::PARAM_INT);
    $query_update_status->bindValue(':id_assinante', $id_assinante, PDO::PARAM_INT);
    $query_update_status->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_update_status->execute();


    $query_update4 = $pdo->prepare("UPDATE clientes SET
                                assinante = :assinante                
                            WHERE id = :id_cliente AND id_conta = :id_conta");
    $query_update4->bindValue(':assinante', 'Não');    
    $query_update4->bindValue(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $query_update4->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_update4->execute();

    // 3. Ações na tabela 'receber' baseadas no novo status
    if ($novo_status == 0) {
        // --- INATIVANDO ---
        // Deleta TODAS as cobranças NÃO PAGAS do tipo 'Assinatura' para este assinante
        $query_del_rec = $pdo->prepare("DELETE FROM receber WHERE cliente = :id_assinante AND id_conta = :id_conta AND pago = 'Não' AND tipo = 'Assinatura'");
        $query_del_rec->bindValue(':id_assinante', $id_assinante, PDO::PARAM_INT);
        $query_del_rec->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_del_rec->execute();
        $num_deleted = $query_del_rec->rowCount();

        $response['success'] = true; // Considera sucesso mesmo que não delete nada
        $response['message'] = "Assinante inativado. {$num_deleted} cobrança(s) pendente(s) removida(s).";

    } else { // ($novo_status == 1)
        // --- ATIVANDO ---
        // Cria uma NOVA cobrança baseada no plano ATUAL do assinante, com vencimento HOJE

        // Busca detalhes do plano ATUAL do assinante
        $query_plano = $pdo->prepare("SELECT nome, preco_mensal FROM planos WHERE id = :id_plano AND id_conta = :id_conta");
        $query_plano->execute([':id_plano' => $id_plano_atual_assinante, ':id_conta' => $id_conta_corrente]);
        $plano_info = $query_plano->fetch(PDO::FETCH_ASSOC);

        if (!$plano_info) {
             error_log("Aviso: Plano ID {$id_plano_atual_assinante} não encontrado para o assinante ID {$id_assinante} ao reativar.");
             $valor_cobrar = 0; // Ou valor padrão
             $descricao_cobranca = "Assinatura - Plano Indefinido (Reativação)";
             $frequencia_plano = 30; // Padrão mensal
        } else {
            // Define como cobrança MENSAL por padrão na reativação
             $valor_cobrar = $plano_info['preco_mensal'];
             $descricao_cobranca = "Assinatura Plano " . $plano_info['nome'] . " - Mensal (Reativação)";
             $frequencia_plano = 30; // Assume mensal na reativação
        }

        $data_vencimento_nova = date('Y-m-d'); // Vencimento HOJE

        // Insere a nova cobrança na tabela 'receber' - AJUSTE COLUNAS se necessário
        // Verifique os nomes das colunas: pessoa, cliente, etc.
        $query_rec = $pdo->prepare("INSERT INTO receber (descricao, tipo, valor, data_lanc, data_venc, usuario_lanc, pessoa, pago, id_conta, frequencia, cliente)
                                    VALUES (:desc, :tipo, :valor, CURDATE(), :venc, :user_lanc, :pessoa, 'Não', :id_conta, :freq, :cliente_id)");

        $query_rec->bindValue(':desc', $descricao_cobranca);
        $query_rec->bindValue(':tipo', 'Assinatura');
        $query_rec->bindValue(':valor', $valor_cobrar);        
        $query_rec->bindValue(':venc', $data_vencimento_nova); // Vencimento HOJE
        $query_rec->bindValue(':user_lanc', $id_usuario_logado, PDO::PARAM_INT);
        $query_rec->bindValue(':pessoa', $id_cliente, PDO::PARAM_INT);       // ID do assinante
        $query_rec->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_rec->bindValue(':freq', $frequencia_plano, PDO::PARAM_INT);      // Mensal por padrão
        $query_rec->bindValue(':cliente_id', $id_assinante, PDO::PARAM_INT);   // Ou null se 'pessoa' for o correto

        $query_rec->execute();

        if($query_rec->rowCount() > 0){
            $query_update2 = $pdo->prepare("UPDATE assinantes SET
                                data_vencimento = :data_vencimento                
                            WHERE id = :id_cliente AND id_conta = :id_conta");

            $query_update2->bindValue(':data_vencimento', $data_vencimento_nova);    
            $query_update2->bindValue(':id_cliente', $id_assinante, PDO::PARAM_INT);
            $query_update2->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
            $query_update2->execute();

            $query_update3 = $pdo->prepare("UPDATE clientes SET
                                assinante = :assinante                
                            WHERE id = :id_cliente AND id_conta = :id_conta");

            $query_update3->bindValue(':assinante', 'Sim');    
            $query_update3->bindValue(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $query_update3->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
            $query_update3->execute();

             $response['success'] = true;
             $response['message'] = 'Assinante ativado e nova cobrança mensal criada com vencimento hoje! Altere de desejar.';
        } else {
             throw new Exception("Falha ao criar nova cobrança para reativação."); // Força rollback
        }
    }

    // --- Confirma Transação ---
    $pdo->commit();

} catch (PDOException $e) { // Captura erros específicos do PDO
    $pdo->rollBack(); // Desfaz tudo em caso de erro no TRY
    $response['message'] = 'Erro de Banco de Dados: ' . $e->getMessage();
    error_log("Erro PDO em mudar_status_assinante: " . $e->getMessage());
} catch (Exception $e) { // Captura outras exceções gerais
    $pdo->rollBack(); // Desfaz tudo em caso de erro no TRY
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral em mudar_status_assinante: " . $e->getMessage());
}

// Retorna a resposta JSON
echo json_encode($response);
