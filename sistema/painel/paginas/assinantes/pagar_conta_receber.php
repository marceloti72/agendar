<?php
require_once("../../../conexao.php"); // Ajuste o caminho se necessário
@session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Erro desconhecido.'];

// Validações iniciais
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido.';
    echo json_encode($response); exit;
}
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_conta'])) {
    $response['message'] = 'Erro: Sessão inválida ou não iniciada.';
    echo json_encode($response); exit;
}

$id_conta_corrente = $_SESSION['id_conta'];
$id_usuario_pgto = $_SESSION['id_usuario']; // Usuário que registrou o pagamento

// --- Recebe e Valida Dados do POST ---
$id_receber = isset($_POST['id_receber']) ? (int)$_POST['id_receber'] : 0;
$data_pagamento_str = isset($_POST['data_pagamento']) ? $_POST['data_pagamento'] : '';
// Assumindo que o NOME da forma é enviado (como no HTML anterior)
$forma_pgto = isset($_POST['forma_pgto']) ? trim($_POST['forma_pgto']) : '';
$multa_str = isset($_POST['multa']) ? $_POST['multa'] : '0';
$juros_str = isset($_POST['juros']) ? $_POST['juros'] : '0';
$valor_pago_str = isset($_POST['valor_pago']) ? $_POST['valor_pago'] : '';
//$id_conta_form = isset($_POST['id_conta']) ? (int)$_POST['id_conta'] : $id_conta_corrente; // Não precisa vir do form

// Validação básica dos dados recebidos
if ($id_receber <= 0) {
    $response['message'] = 'ID da conta a receber inválido.';
    echo json_encode($response); exit;
}
if (empty($data_pagamento_str) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_pagamento_str)) {
    $response['message'] = 'Data de Pagamento inválida (use AAAA-MM-DD).';
    echo json_encode($response); exit;
}
if (empty($forma_pgto)) {
    $response['message'] = 'Forma de Pagamento é obrigatória.';
    echo json_encode($response); exit;
}
if (empty($valor_pago_str)) {
     $response['message'] = 'Valor Recebido é obrigatório.';
     echo json_encode($response); exit;
 }

// Função para converter BRL para Decimal
function brlToDecimalPgto($valorBrl) {
    if ($valorBrl === null || $valorBrl === '') return 0.00;
    $valor = str_replace('.', '', $valorBrl); // Remove milhar
    $valor = str_replace(',', '.', $valor); // Troca vírgula
    return is_numeric($valor) ? (float)$valor : 0.00;
}

$data_pagamento = $data_pagamento_str;
$multa = brlToDecimalPgto($multa_str);
$juros = brlToDecimalPgto($juros_str);
$valor_pago = brlToDecimalPgto($valor_pago_str);

if ($valor_pago <= 0) {
     $response['message'] = 'Valor Recebido deve ser maior que zero.';
     echo json_encode($response); exit;
 }

// --- Inicia Transação ---
$pdo->beginTransaction();

try {
    // 1. Busca dados da conta a receber que está sendo paga (e valida)
    //    Pegamos mais dados para usar na criação da próxima conta
    $check = $pdo->prepare("SELECT id, valor, frequencia, descricao, pessoa, cliente, data_venc
                           FROM receber WHERE id = :id AND id_conta = :id_conta AND pago = 'Não'");
    $check->execute([':id' => $id_receber, ':id_conta' => $id_conta_corrente]);
    $conta_receber = $check->fetch(PDO::FETCH_ASSOC);

    if (!$conta_receber) {
        // Se não encontrou, pode já ter sido paga ou não existe/pertence à conta
        $pdo->rollBack(); // Desfaz a transação iniciada
        $response['message'] = "Conta a receber não encontrada, já paga ou não pertence a esta conta.";
        echo json_encode($response);
        exit;
    }

    // Guarda os dados necessários para a próxima cobrança ANTES de atualizar
    $valor_original_proxima = $conta_receber['valor'];
    $frequencia_proxima = (int)$conta_receber['frequencia']; // Garante que é inteiro
    $descricao_original = $conta_receber['descricao'];
    $pessoa_proxima = $conta_receber['pessoa'];
    $cliente_proximo = $conta_receber['cliente']; // Pode ser o mesmo que pessoa
    $vencimento_anterior = $conta_receber['data_venc']; // Vencimento desta conta que está sendo paga

    // 2. Atualiza a conta atual como PAGA
    // Atenção aos nomes das colunas: 'pgto' ou 'forma_pgto'? 'usuario_baixa' ou 'usuario_pgto'?
    // Usei os nomes do seu exemplo de UPDATE anterior. AJUSTE SE NECESSÁRIO.
    $query_update = $pdo->prepare("UPDATE receber SET
                                pago = 'Sim',
                                data_pgto = :data_pgto,
                                pgto = :forma_pgto,    
                                multa = :multa,
                                juros = :juros,
                                subtotal = :valor_pago,  
                                usuario_baixa = :usuario_pgto                     
                            WHERE id = :id_receber AND id_conta = :id_conta");

    $query_update->bindValue(':data_pgto', $data_pagamento);
    $query_update->bindValue(':forma_pgto', $forma_pgto); // Nome da forma
    $query_update->bindValue(':multa', $multa);
    $query_update->bindValue(':juros', $juros);
    $query_update->bindValue(':valor_pago', $valor_pago); // Valor efetivamente pago
    $query_update->bindValue(':usuario_pgto', $id_usuario_pgto, PDO::PARAM_INT);
    $query_update->bindValue(':id_receber', $id_receber, PDO::PARAM_INT);
    $query_update->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_update->execute();

    // Verifica se a atualização deu certo
    if ($query_update->rowCount() > 0) {

        // 3. Cria a PRÓXIMA conta a receber (se a frequência for válida: 30 ou 365)
        if ($frequencia_proxima == 30 || $frequencia_proxima == 365) {

            // Calcula o próximo vencimento baseado no VENCIMENTO ANTERIOR
            $proximo_vencimento = date('Y-m-d', strtotime("+$frequencia_proxima days", strtotime($vencimento_anterior)));

            // Ajusta a descrição para a próxima cobrança (remove extras)
            $descricao_proxima = preg_replace('/\(Reativação\)| \(Renovação\/Alteração\)/i', '', $descricao_original);
            // Garante que tenha Mensal/Anual corretamente (se não tiver já)
             if (strpos(strtolower($descricao_proxima), 'mensal') === false && strpos(strtolower($descricao_proxima), 'anual') === false) {
                 $descricao_proxima .= ($frequencia_proxima == 365) ? ' - Anual' : ' - Mensal';
             }

            // Insere a próxima cobrança na tabela 'receber'
            // VERIFIQUE OS NOMES DAS COLUNAS!
            $query_insert_next = $pdo->prepare("INSERT INTO receber
                (descricao, tipo, valor, subtotal, data_lanc, data_venc, usuario_lanc, pessoa, pago, id_conta, frequencia, cliente)
                VALUES
                (:desc, :tipo, :valor, :subtotal, CURDATE(), :venc, :user_lanc, :pessoa, 'Não', :id_conta, :freq, :cliente_id)");

            $query_insert_next->bindValue(':desc', $descricao_proxima);
            $query_insert_next->bindValue(':tipo', 'Assinatura'); // Mantém o tipo
            $query_insert_next->bindValue(':valor', $valor_original_proxima); // Valor BASE do plano
            $query_insert_next->bindValue(':subtotal', $valor_original_proxima); // Subtotal igual ao valor base
            $query_insert_next->bindValue(':venc', $proximo_vencimento); // Próximo vencimento
            $query_insert_next->bindValue(':user_lanc', $id_conta_corrente, PDO::PARAM_INT); // Quem gerou
            $query_insert_next->bindValue(':pessoa', $pessoa_proxima, PDO::PARAM_INT); // Mesmo assinante
            $query_insert_next->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
            $query_insert_next->bindValue(':freq', $frequencia_proxima, PDO::PARAM_INT); // Mesma frequência
            $query_insert_next->bindValue(':cliente_id', $cliente_proximo, PDO::PARAM_INT); // Mesmo cliente

            $query_insert_next->execute();

            if($query_insert_next->rowCount() <= 0){
                 // Se falhou ao criar a próxima, desfaz tudo
                 throw new Exception("Falha ao gerar a próxima cobrança para o assinante.");
            }

             $response['success'] = true;
             $response['message'] = 'Pagamento registrado e próxima cobrança gerada!';

        } else {
             // Frequência inválida ou não definida na conta paga, apenas registra o pagamento
             $response['success'] = true;
             $response['message'] = 'Pagamento registrado com sucesso! (Próxima cobrança não gerada - frequência inválida/não aplicável)';
             error_log("Aviso: Frequência inválida/não encontrada ({$frequencia_proxima}) ao tentar gerar próxima cobrança para conta ID {$id_receber}.");
        }

        $query_update2 = $pdo->prepare("UPDATE assinantes SET
                                data_vencimento = :data_vencimento                
                            WHERE id = :id_cliente AND id_conta = :id_conta");

        $query_update2->bindValue(':data_vencimento', $proximo_vencimento);    
        $query_update2->bindValue(':id_cliente', $cliente_proximo, PDO::PARAM_INT);
        $query_update2->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_update2->execute();

        

    } else {
        // Falha ao atualizar a conta atual (UPDATE não afetou linhas)
        throw new Exception("Não foi possível marcar a conta como paga. Verifique se ela já foi paga ou se os dados estão corretos.");
    }

    // --- Confirma Transação (SOMENTE SE TUDO DEU CERTO) ---
    $pdo->commit();

} catch (PDOException $e) { // Captura erros específicos do PDO
    $pdo->rollBack(); // Desfaz tudo em caso de erro no TRY
    $response['message'] = 'Erro de Banco de Dados: ' . $e->getMessage();
    error_log("Erro PDO em pagar_conta_receber: " . $e->getMessage());
} catch (Exception $e) { // Captura outras exceções gerais (incluindo as que lançamos)
    $pdo->rollBack(); // Desfaz tudo em caso de erro no TRY
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral em pagar_conta_receber: " . $e->getMessage());
}

// Retorna a resposta JSON
echo json_encode($response);
