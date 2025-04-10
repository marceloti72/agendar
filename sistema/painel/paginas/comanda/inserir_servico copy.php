<?php
// --- Assume this file is called via AJAX, e.g., registrar_servico_comanda.php ---

require_once("../../../conexao.php"); // Ajuste o caminho se necessário
@session_start(); // Inicia ou continua a sessão

header('Content-Type: application/json'); // Sempre retornar JSON
// Adiciona mais campos à resposta padrão
$response = [
    'success' => false,
    'message' => 'Erro desconhecido.',
    'valor_cobrado' => null,        // Valor que foi efetivamente lançado em receber (ou 0)
    'tipo_registro' => 'Erro',      // 'Assinante' ou 'Servico' (para o JS saber o que aconteceu)
    'detalhe_assinatura' => null    // String como "Uso: 1 / 4"
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
$cliente_id = isset($_POST['cliente']) ? (int)$_POST['cliente'] : 0; // ID da tabela CLIENTES
$comanda_id = isset($_POST['id']) ? (int)$_POST['id'] : 0; // ID da COMANDA sendo processada (opcional?)
$funcionario_id = isset($_POST['funcionario']) ? (int)$_POST['funcionario'] : 0; // ID do USUARIO que executou
$servico_id = isset($_POST['servico']) ? (int)$_POST['servico'] : 0; // ID do SERVICO executado
$quantidade_a_usar = 1; // Assumindo que cada clique registra 1 uso. Mude se necessário.

if ($cliente_id <= 0 || $funcionario_id <= 0 || $servico_id <= 0) {
    $response['message'] = 'Dados inválidos: Cliente, Funcionário ou Serviço não informado corretamente.';
    echo json_encode($response); exit;
}

// --- Inicia Transação ---
$pdo->beginTransaction();

try {
    // --- 1. Busca detalhes do Serviço ---
    $query_s = $pdo->prepare("SELECT nome, valor, comissao FROM servicos WHERE id = :id_servico AND id_conta = :id_conta");
    $query_s->execute([':id_servico' => $servico_id, ':id_conta' => $id_conta_corrente]);
    $servico_info = $query_s->fetch(PDO::FETCH_ASSOC);
    if (!$servico_info) { throw new Exception("Serviço não encontrado (ID: {$servico_id})."); }

    $valor_servico = $servico_info['valor'];
    $comissao_servico = $servico_info['comissao'];
    $nome_servico = $servico_info['nome'];
    $descricao_receber = $nome_servico; // Descrição para conta a receber
    $descricao_pagar = 'Comissão - ' . $nome_servico; // Descrição para conta a pagar

    // --- 2. Busca comissão específica do Funcionário e Calcula Valor ---
    $query_f = $pdo->prepare("SELECT comissao FROM usuarios WHERE id = :id_funcionario AND id_conta = :id_conta");
    $query_f->execute([':id_funcionario' => $funcionario_id, ':id_conta' => $id_conta_corrente]);
    $func_info = $query_f->fetch(PDO::FETCH_ASSOC);
    $comissao_taxa = $comissao_servico;
    if ($func_info && isset($func_info['comissao']) && $func_info['comissao'] > 0) {
        $comissao_taxa = $func_info['comissao'];
    }
    // Busca tipo de comissão global ou define padrão
    if (!isset($tipo_comissao)) $tipo_comissao = $tipo_comissao_global ?? 'Porcentagem'; // Use $tipo_comissao_global se existir
    $valor_comissao = ($tipo_comissao == 'Porcentagem') ? (($comissao_taxa * $valor_servico) / 100) : $comissao_taxa;

    // --- 3. VERIFICAÇÃO DE ASSINATURA ---
    $coberto_pela_assinatura = false;
    $mensagem_assinatura = '';
    $id_assinante_encontrado = null;
    $id_receber_ciclo_atual = null;
    $id_plano_servico_encontrado = null;

    // a. Cliente tem assinatura ativa? (ativo = 1 e não vencida)
    $query_find_ass = $pdo->prepare("SELECT id, id_plano FROM assinantes WHERE id_cliente = :id_cliente AND id_conta = :id_conta AND ativo = 1 AND data_vencimento >= CURDATE()");
    $query_find_ass->execute([':id_cliente' => $cliente_id, ':id_conta' => $id_conta_corrente]);
    $assinante_info = $query_find_ass->fetch(PDO::FETCH_ASSOC);

    if ($assinante_info) {
        $id_assinante_encontrado = $assinante_info['id'];
        $id_plano_assinante = $assinante_info['id_plano'];

        // b. Serviço está incluído no plano? Qual o limite base?
        $query_limite = $pdo->prepare("SELECT id, quantidade FROM planos_servicos WHERE id_plano = :id_plano AND id_servico = :id_servico AND id_conta = :id_conta");
        $query_limite->execute([':id_plano' => $id_plano_assinante, ':id_servico' => $servico_id, ':id_conta' => $id_conta_corrente]);
        $limite_info = $query_limite->fetch();

        if ($limite_info) { // Serviço incluído
            $limite_base = (int)$limite_info['quantidade'];
            $id_plano_servico_encontrado = $limite_info['id']; // ID da ligação planos_servicos

            // c. Encontra o ciclo de cobrança atual (conta pendente mais próxima)
            // *** VERIFIQUE SE A LIGAÇÃO É POR 'cliente' OU 'pessoa' ***
            $query_rec = $pdo->prepare("SELECT id, frequencia FROM receber WHERE cliente = :id_ass AND id_conta = :id_conta AND pago = 'Não' AND tipo = 'Assinatura' ORDER BY data_venc ASC, id ASC LIMIT 1");
            $query_rec->execute([':id_ass' => $id_assinante_encontrado, ':id_conta' => $id_conta_corrente]);
            $rec_atual = $query_rec->fetch();

            if ($rec_atual) { // Ciclo encontrado
                $id_receber_ciclo_atual = $rec_atual['id'];
                $frequencia_ciclo = (int)$rec_atual['frequencia'];

                // d. Calcula o limite real para o ciclo (anual * 12)
                $limite_ciclo = $limite_base;
                if ($frequencia_ciclo == 365 && $limite_base > 0) { $limite_ciclo = $limite_base * 12; }
                elseif ($limite_base == 0) { $limite_ciclo = 0; }

                // e. Conta o uso atual DENTRO deste ciclo
                $usados_atualmente = 0;
                if ($limite_ciclo > 0) { // Só precisa contar se não for ilimitado
                    $query_uso = $pdo->prepare("SELECT SUM(quantidade_usada) as total_usado FROM assinantes_servicos_usados WHERE id_assinante = :id_ass AND id_servico = :id_serv AND id_receber_associado = :id_rec AND id_conta = :id_conta");
                    $query_uso->execute([':id_ass' => $id_assinante_encontrado, ':id_serv' => $servico_id, ':id_rec' => $id_receber_ciclo_atual, ':id_conta' => $id_conta_corrente]);
                    $uso_info = $query_uso->fetch();
                    $usados_atualmente = $uso_info ? (int)$uso_info['total_usado'] : 0;
                }

                // f. Verifica se há saldo
                if ($limite_ciclo === 0 || $usados_atualmente < $limite_ciclo) {
                    $coberto_pela_assinatura = true;
                    $novo_uso_num = $usados_atualmente + $quantidade_a_usar;
                    $limite_texto = ($limite_ciclo === 0) ? "Ilimitado" : $limite_ciclo;
                    // Mensagem para o frontend
                    $mensagem_assinatura = "(Uso: {$novo_uso_num} / {$limite_texto})";
                } else {
                    $mensagem_assinatura = "(Limite Atingido: {$usados_atualmente} / {$limite_ciclo})";
                }
            } else {
                 $mensagem_assinatura = "(Ciclo Assinatura não localizado)";
                 error_log("Aviso: Assinante Ativo ID {$id_assinante_encontrado} sem cobrança pendente em 'receber'.");
            }
        } // else: serviço não está no plano
    } // else: não é assinante ativo

    // --- 4. Ação Condicional ---
    if ($coberto_pela_assinatura) {
        // --- SERVIÇO COBERTO PELA ASSINATURA ---

        // a. INSERT usage record
        $query_insert_uso = $pdo->prepare("INSERT INTO assinantes_servicos_usados
            (id_assinante, id_servico, id_plano_servico, id_receber_associado, quantidade_usada, data_uso, id_usuario_registro, id_conta, observacao)
            VALUES (:id_ass, :id_serv, :id_ps, :id_rec, :qtd, NOW(), :id_user, :id_conta, :obs)");
        $query_insert_uso->bindValue(':id_ass', $id_assinante_encontrado, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_serv', $servico_id, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_ps', $id_plano_servico_encontrado, PDO::PARAM_INT); // FK para planos_servicos
        $query_insert_uso->bindValue(':id_rec', $id_receber_ciclo_atual, PDO::PARAM_INT); // FK para receber (ciclo)
        $query_insert_uso->bindValue(':qtd', $quantidade_a_usar, PDO::PARAM_INT); // Quantidade usada agora (1)
        $query_insert_uso->bindValue(':id_user', $usuario_logado, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':obs', 'Coberto pela Assinatura', PDO::PARAM_STR); // Observação
        $query_insert_uso->execute();
        if ($query_insert_uso->rowCount() <= 0) { throw new Exception("Falha ao registrar uso do serviço."); }

        // b. INSERT commission (id_ref = null)
        $query_pagar = $pdo->prepare("INSERT INTO pagar SET descricao = :desc, tipo = 'Comissão', valor = :val, data_lanc = CURDATE(), data_venc = CURDATE(), usuario_lanc = :user_lanc, foto = 'sem-foto.jpg', pago = 'Não', funcionario = :func, servico = :serv, cliente = :cli, id_ref = NULL, id_conta = :id_conta");
        $query_pagar->bindValue(':desc', $descricao_pagar);
        $query_pagar->bindValue(':val', $valor_comissao);
        $query_pagar->bindValue(':user_lanc', $usuario_logado);
        $query_pagar->bindValue(':func', $funcionario_id);
        $query_pagar->bindValue(':serv', $servico_id);
        $query_pagar->bindValue(':cli', $cliente_id); // FK para clientes.id
        $query_pagar->bindValue(':id_conta', $id_conta_corrente);
        $query_pagar->execute();
        if ($query_pagar->rowCount() <= 0) { throw new Exception("Falha ao lançar comissão (serviço coberto)."); }

        // c. Define a resposta JSON
        $response['success'] = true;
        $response['message'] = 'Serviço Coberto pela Assinatura';
        $response['valor_cobrado'] = 0;
        $response['tipo_registro'] = 'Assinante';
        $response['detalhe_assinatura'] = $mensagem_assinatura; // Envia ex: "(Uso: 1 / 4)"

    } else {
        // --- SERVIÇO NÃO COBERTO (Cobrança Normal) ---

        // a. INSERT into 'receber' - Use PREPARED STATEMENT
        // *** Verifique os nomes das colunas da sua tabela 'receber' ***
        $query_receber = $pdo->prepare("INSERT INTO receber SET descricao = :desc, tipo = 'Serviço', valor = :val, data_lanc = CURDATE(), data_venc = CURDATE(), usuario_lanc = :user_lanc, foto = 'sem-foto.jpg', cliente = :cli, pago = 'Não', servico = :serv, funcionario = :func, func_comanda = :user_comanda, comanda = :comanda_id, id_conta = :id_conta");
        // Removi pessoa, valor2, frequencia - ADICIONE se precisar
        $query_receber->bindValue(':desc', $descricao_receber);
        $query_receber->bindValue(':val', $valor_servico);
        $query_receber->bindValue(':user_lanc', $usuario_logado);
        $query_receber->bindValue(':cli', $cliente_id); // FK para clientes.id
        $query_receber->bindValue(':serv', $servico_id);
        $query_receber->bindValue(':func', $funcionario_id);
        $query_receber->bindValue(':user_comanda', $usuario_logado); // Usuário da comanda
        $query_receber->bindValue(':comanda_id', $comanda_id); // ID da comanda (se aplicável)
        $query_receber->bindValue(':id_conta', $id_conta_corrente);
        $query_receber->execute();

        $ult_id_receber = $pdo->lastInsertId();
        if (!$ult_id_receber) { throw new Exception("Falha ao criar cobrança do serviço."); }

        // b. INSERT commission linked to the charge - Use PREPARED STATEMENT
        $query_pagar = $pdo->prepare("INSERT INTO pagar SET descricao = :desc, tipo = 'Comissão', valor = :val, data_lanc = CURDATE(), data_venc = CURDATE(), usuario_lanc = :user_lanc, foto = 'sem-foto.jpg', pago = 'Não', funcionario = :func, servico = :serv, cliente = :cli, id_ref = :id_ref, id_conta = :id_conta");
        $query_pagar->bindValue(':desc', $descricao_pagar);
        $query_pagar->bindValue(':val', $valor_comissao);
        $query_pagar->bindValue(':user_lanc', $usuario_logado);
        $query_pagar->bindValue(':func', $funcionario_id);
        $query_pagar->bindValue(':serv', $servico_id);
        $query_pagar->bindValue(':cli', $cliente_id); // FK para clientes.id
        $query_pagar->bindValue(':id_ref', $ult_id_receber); // Link com a conta criada
        $query_pagar->bindValue(':id_conta', $id_conta_corrente);
        $query_pagar->execute();
        if ($query_pagar->rowCount() <= 0) { throw new Exception("Falha ao lançar comissão (serviço cobrado)."); }

        // c. Define a resposta JSON
        $response['success'] = true;
        $response['message'] = 'Salvo com Sucesso'; // Mensagem padrão
        $response['valor_cobrado'] = $valor_servico;
        $response['tipo_registro'] = 'Servico';
        $response['detalhe_assinatura'] = trim($mensagem_assinatura); // Informa se limite foi atingido
    }

    // --- Commit Transaction ---
    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro de Banco de Dados: ' . $e->getMessage();
    error_log("Erro PDO no registro de serviço/assinatura: " . $e->getMessage() . " | Dados: " . print_r($_POST, true));
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral no registro de serviço/assinatura: " . $e->getMessage() . " | Dados: " . print_r($_POST, true));
}

// --- Final JSON Output ---
echo json_encode($response);
?>