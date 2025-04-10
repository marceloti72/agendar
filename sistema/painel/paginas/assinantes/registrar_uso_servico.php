<?php
require_once("../../../conexao.php"); // Ajuste o caminho se necessário
@session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Erro desconhecido.'];

// Validações iniciais
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido.'; echo json_encode($response); exit;
}
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_conta'])) {
    $response['message'] = 'Sessão inválida.'; echo json_encode($response); exit;
}

$id_conta_corrente = $_SESSION['id_conta'];
$id_usuario_registro = $_SESSION['id_usuario'];

// --- Recebe e Valida Dados do POST ---
$id_assinante = isset($_POST['id_assinante_servico']) ? (int)$_POST['id_assinante_servico'] : 0;
$id_servico = isset($_POST['id_servico']) ? (int)$_POST['id_servico'] : 0;
$quantidade_usada = isset($_POST['quantidade_usada']) ? (int)$_POST['quantidade_usada'] : 0; // Quantidade que está sendo usada AGORA (normalmente 1)
$observacao = isset($_POST['observacao']) ? trim($_POST['observacao']) : null;
$id_receber_atual = isset($_POST['id_receber_atual']) ? (int)$_POST['id_receber_atual'] : 0; // ID do ciclo de cobrança atual

// Validações básicas
if ($id_assinante <= 0 || $id_servico <= 0 || $quantidade_usada <= 0 || $id_receber_atual <= 0) {
    $response['message'] = 'Dados inválidos fornecidos para registrar o uso.';
    echo json_encode($response); exit;
}

// --- Inicia Transação ---
$pdo->beginTransaction();
try {
    // 1. Pega o plano atual do assinante E A FREQUÊNCIA DO CICLO ATUAL
    //    (Verifica também se o assinante e a conta a receber pertencem à conta logada)
    $query_plano_freq = $pdo->prepare("
        SELECT a.id_plano, r.frequencia
        FROM assinantes a
        JOIN receber r ON r.cliente = a.id -- Ou r.pessoa = a.id (VERIFIQUE SUA FK)
        WHERE a.id = :id_ass
          AND r.id = :id_rec
          AND a.id_conta = :id_conta
          AND r.id_conta = :id_conta -- Dupla checagem de conta
          -- AND r.pago = 'Não' -- Opcional: Permite registrar uso apenas em ciclo não pago? Pode ser restritivo demais.
          LIMIT 1
    ");
    $query_plano_freq->execute([
        ':id_ass' => $id_assinante,
        ':id_rec' => $id_receber_atual,
        ':id_conta' => $id_conta_corrente
    ]);
    $info_ciclo = $query_plano_freq->fetch(PDO::FETCH_ASSOC);

    if (!$info_ciclo || empty($info_ciclo['id_plano']) || empty($info_ciclo['frequencia'])) {
         throw new Exception("Não foi possível encontrar o plano ou a frequência do ciclo de cobrança atual (ID Receber: {$id_receber_atual}). Verifique se o ciclo pertence ao assinante.");
    }
    $id_plano = $info_ciclo['id_plano'];
    $frequencia_ciclo = (int)$info_ciclo['frequencia']; // Frequência (30 ou 365)

    // 2. Busca o limite BASE (mensal) deste serviço neste plano
    $query_limite = $pdo->prepare("SELECT id, quantidade FROM planos_servicos WHERE id_plano = :id_plano AND id_servico = :id_servico AND id_conta = :id_conta");
    $query_limite->execute([':id_plano' => $id_plano, ':id_servico' => $id_servico, ':id_conta' => $id_conta_corrente]);
    $limite_info = $query_limite->fetch();

    if (!$limite_info) { throw new Exception("Este serviço (ID: {$id_servico}) não está incluído no plano atual (ID: {$id_plano})."); }

    $limite_base = (int)$limite_info['quantidade']; // Limite salvo no BD (assumido mensal)
    $id_plano_servico = $limite_info['id']; // ID da ligação planos_servicos

    // 3. Calcula o limite REAL para o ciclo atual (mensal ou anual)
    $limite_ciclo = $limite_base; // Padrão
    if ($frequencia_ciclo == 365 && $limite_base > 0) { // Se for ciclo ANUAL e não ilimitado
        $limite_ciclo = $limite_base * 12;
        // error_log("INFO registrar_uso_servico: Ciclo Anual detectado. Limite calculado: {$limite_base} * 12 = {$limite_ciclo}"); // Debug
    } elseif ($limite_base == 0) {
        $limite_ciclo = 0; // Ilimitado
    }
    // else: ciclo é mensal (30 dias) ou ilimitado (0), usa limite_base que já é igual a limite_ciclo

    // 4. Se o limite NÃO for ilimitado (0), verifica o uso atual DENTRO DO CICLO
    if ($limite_ciclo > 0) {
        // Soma o uso JÁ REGISTRADO para este serviço neste ciclo
        $query_uso_atual = $pdo->prepare("
            SELECT SUM(quantidade_usada) as total_usado FROM assinantes_servicos_usados
            WHERE id_assinante = :id_ass AND id_servico = :id_serv
              AND id_receber_associado = :id_rec AND id_conta = :id_conta
        ");
        $query_uso_atual->execute([
            ':id_ass' => $id_assinante,
            ':id_serv' => $id_servico,
            ':id_rec' => $id_receber_atual, // Conta uso apenas neste ciclo
            ':id_conta' => $id_conta_corrente
        ]);
        $res_uso = $query_uso_atual->fetch();
        $usados_atualmente = $res_uso ? (int)$res_uso['total_usado'] : 0;

        // Verifica se o novo uso ($quantidade_usada vindo do form) excede o limite CALCULADO para o ciclo
        if (($usados_atualmente + $quantidade_usada) > $limite_ciclo) {
            // Monta mensagem de erro detalhada
            $msgErroLimite = "Limite excedido para este serviço neste ciclo! ";
            $msgErroLimite .= "Limite do Ciclo: {$limite_ciclo}, ";
            $msgErroLimite .= "Já Usados: {$usados_atualmente}, ";
            $msgErroLimite .= "Tentando Usar: {$quantidade_usada}";
            throw new Exception($msgErroLimite);
        }
        // error_log("INFO registrar_uso_servico: Verificação de limite OK. Ciclo Limite: {$limite_ciclo}, Usados Antes: {$usados_atualmente}, Usando Agora: {$quantidade_usada}"); // Debug
    }

    // 5. Insere o registro de uso (quantidade real usada, ex: 1)
    $query_insert = $pdo->prepare("INSERT INTO assinantes_servicos_usados
        (id_assinante, id_servico, id_plano_servico, id_receber_associado, quantidade_usada, data_uso, id_usuario_registro, id_conta, observacao)
        VALUES
        (:id_ass, :id_serv, :id_ps, :id_rec, :qtd, NOW(), :id_user, :id_conta, :obs)");

    $query_insert->bindValue(':id_ass', $id_assinante, PDO::PARAM_INT);
    $query_insert->bindValue(':id_serv', $id_servico, PDO::PARAM_INT);
    $query_insert->bindValue(':id_ps', $id_plano_servico, PDO::PARAM_INT); // ID da ligação planos_servicos
    $query_insert->bindValue(':id_rec', $id_receber_atual, PDO::PARAM_INT); // ID do ciclo 'receber'
    $query_insert->bindValue(':qtd', $quantidade_usada, PDO::PARAM_INT);   // Quantidade REAL usada (normalmente 1)
    $query_insert->bindValue(':id_user', $id_usuario_registro, PDO::PARAM_INT);
    $query_insert->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_insert->bindValue(':obs', $observacao, PDO::PARAM_STR);

    $query_insert->execute();

    if ($query_insert->rowCount() > 0) {
         $pdo->commit(); // Confirma a transação SE TUDO deu certo
         $response['success'] = true;
         $response['message'] = 'Uso do serviço registrado com sucesso!';
    } else {
         // Se o INSERT falhou por algum motivo não pego pelo try/catch do PDO
         throw new Exception("Falha ao registrar o uso do serviço no banco de dados.");
    }

} catch (PDOException $e) { // Erro no Banco de Dados
    $pdo->rollBack(); // Desfaz a transação
    $response['message'] = 'Erro de Banco de Dados: ' . $e->getMessage();
    error_log("Erro PDO em registrar_uso_servico: " . $e->getMessage());
} catch (Exception $e) { // Outros Erros (como limite excedido)
    $pdo->rollBack(); // Desfaz a transação
    $response['message'] = $e->getMessage(); // Usa a mensagem da exceção (ex: "Limite excedido...")
    error_log("Erro Geral em registrar_uso_servico: " . $e->getMessage());
}

echo json_encode($response);
?>