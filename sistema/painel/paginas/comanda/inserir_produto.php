<?php
// Arquivo: paginas/SUA_PAGINA/inserir_produto.php (Exemplo de Caminho)

require_once("../../../conexao.php"); // Ajuste o caminho se necessário
@session_start(); // Inicia ou continua a sessão

header('Content-Type: application/json'); // Sempre retornar JSON
$response = [
    'success' => false,
    'message' => 'Erro desconhecido.',
    'nova_comanda_id' => null // Guarda o ID da comanda se uma nova for criada
];

// --- Validações Iniciais ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido.'; echo json_encode($response); exit;
}
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_conta'])) {
    $response['message'] = 'Sessão inválida ou expirada.'; echo json_encode($response); exit;
}

$id_conta_corrente = $_SESSION['id_conta'];
$usuario_logado = $_SESSION['id_usuario']; // Usuário que está registrando

// --- Recebe Dados do POST e Valida ---
$cliente_id = isset($_POST['cliente']) ? filter_var($_POST['cliente'], FILTER_VALIDATE_INT) : 0;
$comanda_id_recebido = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : 0; // ID da COMANDA vindo do JS (pode ser 0)
$funcionario_id = isset($_POST['funcionario2']) ? filter_var($_POST['funcionario2'], FILTER_VALIDATE_INT) : 0; // ID do USUARIO que vendeu (vem do funcionario2 no seu HTML)
$produto_id = isset($_POST['produto']) ? filter_var($_POST['produto'], FILTER_VALIDATE_INT) : 0; // ID do PRODUTO
$quantidade = isset($_POST['quantidade']) ? filter_var($_POST['quantidade'], FILTER_VALIDATE_INT) : 0; // Quantidade vendida

// Validação dos dados recebidos
if (!$cliente_id || !$produto_id || $quantidade <= 0 || $comanda_id_recebido < 0) {
    $response['message'] = 'Dados inválidos: Cliente, Produto, Quantidade ou Comanda não informados corretamente.';
    echo json_encode($response); exit;
}
// Funcionário pode ser opcional para venda de produto? Se sim, ok. Senão, valide: if(!$funcionario_id){...}

// --- Inicia Transação ---
$pdo->beginTransaction();

try {
    // Define o ID da comanda a ser usado (existente ou novo)
    $comanda_id_final = $comanda_id_recebido;

    // --- 1. Busca detalhes do Produto e Verifica Estoque ---
    $query_p = $pdo->prepare("SELECT nome, estoque, valor_venda FROM produtos WHERE id = :id_produto AND id_conta = :id_conta");
    $query_p->execute([':id_produto' => $produto_id, ':id_conta' => $id_conta_corrente]);
    $produto_info = $query_p->fetch(PDO::FETCH_ASSOC);

    if (!$produto_info) { throw new Exception("Produto não encontrado (ID: {$produto_id})."); }

    $estoque_atual = $produto_info['estoque'];
    $valor_unitario = $produto_info['valor_venda'];
    $nome_produto = $produto_info['nome'];
    $valor_total_produto = $valor_unitario * $quantidade; // Calcula o valor total para a quantidade
    $descricao_receber = 'Venda - (' . $quantidade . 'x) ' . $nome_produto; // Descrição para conta a receber

    // Verifica estoque
    if ($quantidade > $estoque_atual) {
        throw new Exception('Estoque insuficiente! Você possui apenas ' . $estoque_atual . ' unidade(s) de "' . $nome_produto . '".');
    }

    // ***** INÍCIO: LÓGICA PARA CRIAR COMANDA SE NECESSÁRIO *****
    if ($comanda_id_final <= 0) {
        // Se ID é 0, CRIA uma nova comanda
        error_log("Info: Criando nova comanda para cliente ID {$cliente_id} pela inserção do produto ID {$produto_id}");

        // AJUSTE colunas e tabela 'comandas' conforme sua estrutura
        $query_nova_comanda = $pdo->prepare("INSERT INTO comandas
            (cliente, status, data, hora, funcionario, id_conta, valor)
            VALUES
            (:cliente, :status, CURDATE(), CURTIME(), :usuario, :id_conta, 0)"); // Inicia com valor 0

        $query_nova_comanda->execute([
            ':cliente' => $cliente_id,
            ':status' => 'Aberta',
            ':usuario' => $usuario_logado, // Quem abriu
            ':id_conta' => $id_conta_corrente
        ]);

        $comanda_id_final = $pdo->lastInsertId();
        if (!$comanda_id_final || $comanda_id_final <= 0) { throw new Exception("Falha crítica ao criar a nova comanda."); }
        $response['nova_comanda_id'] = $comanda_id_final; // Informa ao JS
        error_log("Info: Nova comanda criada com ID: " . $comanda_id_final);
    } else {
         error_log("Info: Usando comanda existente ID: " . $comanda_id_final);
    }
    // ***** FIM: LÓGICA PARA CRIAR COMANDA SE NECESSÁRIO *****


    // --- 2. Atualiza Estoque do Produto ---
    $query_upd_est = $pdo->prepare("UPDATE produtos SET estoque = estoque - :qtd WHERE id = :id_produto AND id_conta = :id_conta");
    $query_upd_est->bindValue(':qtd', $quantidade, PDO::PARAM_INT);
    $query_upd_est->bindValue(':id_produto', $produto_id, PDO::PARAM_INT);
    $query_upd_est->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_upd_est->execute();
    if ($query_upd_est->rowCount() <= 0) {
        // Isso pode indicar um problema se o produto sumiu entre o select e o update
        error_log("Aviso: Nenhuma linha afetada ao atualizar estoque do produto ID {$produto_id}.");
    }

    // --- 3. Insere o item na tabela 'receber' ---
    // *** Verifique os nomes das colunas da sua tabela 'receber' ***
    // Adicionando quantidade e produto, removendo servico, func_comanda? Adapte!
    $query_receber = $pdo->prepare("INSERT INTO receber SET
        descricao = :desc,
        tipo = 'Produto',            -- Tipo é Venda
        valor = :val,              -- Valor TOTAL (qtd * valor_unitario)
        data_lanc = CURDATE(),
        data_venc = CURDATE(),
        usuario_lanc = :user_lanc,
        foto = 'sem-foto.jpg',     -- Foto do produto? Ou deixar null?
        cliente = :cli,            -- FK para CLIENTES
        pago = 'Não',
        produto = :prod,           -- FK para PRODUTOS
        quantidade = :qtd,         -- Quantidade vendida
        funcionario = :func,       -- Quem VENDEU (pode ser null se não aplicável)
        comanda = :comanda_id,     -- FK para COMANDAS
        id_conta = :id_conta,
        valor2 = :val            -- Subtotal geralmente igual ao valor para venda direta
        -- Removi: servico, func_comanda, pessoa, valor2, frequencia (adicione se necessário)
    ");
    $query_receber->bindValue(':desc', $descricao_receber);
    $query_receber->bindValue(':val', $valor_total_produto);
    $query_receber->bindValue(':user_lanc', $usuario_logado);
    $query_receber->bindValue(':cli', $cliente_id);
    $query_receber->bindValue(':prod', $produto_id);
    $query_receber->bindValue(':qtd', $quantidade);
    $query_receber->bindValue(':func', $funcionario_id ?: null, PDO::PARAM_INT); // Permite funcionário nulo se 0 for enviado
    $query_receber->bindValue(':comanda_id', $comanda_id_final, PDO::PARAM_INT);
    $query_receber->bindValue(':id_conta', $id_conta_corrente);
    $query_receber->bindValue(':sub', $valor_total_produto);
    $query_receber->execute();
    $ult_id_receber = $pdo->lastInsertId();
    if (!$ult_id_receber) { throw new Exception("Falha ao registrar venda na cobrança."); }


    // --- 4. Atualiza Valor Total da Comanda ---
    // Soma o VALOR TOTAL do(s) produto(s) adicionado(s)
    $query_update_comanda = $pdo->prepare("UPDATE comandas SET valor = valor + :valor_add WHERE id = :comanda_id AND id_conta = :id_conta");
    $query_update_comanda->bindValue(':valor_add', $valor_total_produto); // <<< SOMA O VALOR DOS PRODUTOS
    $query_update_comanda->bindValue(':comanda_id', $comanda_id_final, PDO::PARAM_INT);
    $query_update_comanda->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_update_comanda->execute();
    // if ($query_update_comanda->rowCount() <= 0) { ... log aviso ... }


    // --- 5. Lançar Comissão (Opcional para Produto?) ---
    // Verifique sua regra de negócio: produto gera comissão? Para qual funcionário?
    // Se sim, adicione um INSERT INTO pagar similar ao do serviço, usando $valor_comissao_produto
    /*
    if ($gera_comissao_produto && $valor_comissao_produto > 0) {
         $query_pagar = $pdo->prepare("INSERT INTO pagar SET descricao = :desc, tipo = 'Comissão Venda', valor = :val, ..., id_ref = :id_ref, ...");
         $query_pagar->bindValue(':desc', 'Comissão - Venda ' . $nome_produto);
         $query_pagar->bindValue(':val', $valor_comissao_produto); // Precisa calcular essa comissão
         $query_pagar->bindValue(':func', $funcionario_id); // Funcionário que vendeu
         $query_pagar->bindValue(':cli', $cliente_id);
         $query_pagar->bindValue(':id_ref', $ult_id_receber); // Liga com a linha em receber
         // ... outros binds ...
         $query_pagar->execute();
         if ($query_pagar->rowCount() <= 0) { throw new Exception("Falha ao lançar comissão da venda."); }
    }
    */

    // --- 6. Define a resposta JSON final ---
    $response['success'] = true;
    $response['message'] = $quantidade . 'x ' . $nome_produto . ' adicionado(s)!'; // Mensagem específica
    $response['tipo_registro'] = 'Produto'; // Indica que foi um produto
    // $response['valor_cobrado'] = $valor_total_produto; // Opcional: retornar o valor adicionado


    // --- Commit Transaction ---
    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro DB: ' . $e->getMessage();
    error_log("Erro PDO no registro de produto/comanda: " . $e->getMessage() . " | Dados: " . print_r($_POST, true));
    $response['success'] = false;
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral no registro de produto/comanda: " . $e->getMessage() . " | Dados: " . print_r($_POST, true));
    $response['success'] = false;
}

// --- Final JSON Output ---
echo json_encode($response);
?>