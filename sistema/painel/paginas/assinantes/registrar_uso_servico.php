<?php
require_once("../../../conexao.php");
@session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Erro desconhecido.'];

// Validações
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { /* ... */ }
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_conta'])) { /* ... */ }

$id_conta_corrente = $_SESSION['id_conta'];
$id_usuario_registro = $_SESSION['id_usuario'];

$id_assinante = isset($_POST['id_assinante_servico']) ? (int)$_POST['id_assinante_servico'] : 0;
$id_servico = isset($_POST['id_servico']) ? (int)$_POST['id_servico'] : 0;
$quantidade_usada = isset($_POST['quantidade_usada']) ? (int)$_POST['quantidade_usada'] : 0;
$observacao = isset($_POST['observacao']) ? trim($_POST['observacao']) : null;
$id_receber_atual = isset($_POST['id_receber_atual']) ? (int)$_POST['id_receber_atual'] : 0; // ID do ciclo

if ($id_assinante <= 0 || $id_servico <= 0 || $quantidade_usada <= 0 || $id_receber_atual <= 0) {
     $response['message'] = 'Dados inválidos para registrar uso.';
     echo json_encode($response); exit;
}

$pdo->beginTransaction();
try {
    // 1. Pega o plano atual do assinante
    $query_plano = $pdo->prepare("SELECT id_plano FROM assinantes WHERE id = :id_ass AND id_conta = :id_conta");
    $query_plano->execute([':id_ass' => $id_assinante, ':id_conta' => $id_conta_corrente]);
    $plano_ass = $query_plano->fetch();
    if (!$plano_ass) { throw new Exception("Assinante não encontrado."); }
    $id_plano = $plano_ass['id_plano'];

    // 2. Busca o limite deste serviço neste plano
    $query_limite = $pdo->prepare("SELECT id, quantidade FROM planos_servicos WHERE id_plano = :id_plano AND id_servico = :id_servico AND id_conta = :id_conta");
    $query_limite->execute([':id_plano' => $id_plano, ':id_servico' => $id_servico, ':id_conta' => $id_conta_corrente]);
    $limite_info = $query_limite->fetch();

    if (!$limite_info) { throw new Exception("Este serviço não está incluído no plano do assinante."); }
    $limite_permitido = (int)$limite_info['quantidade'];
    $id_plano_servico = $limite_info['id']; // Pega o ID da ligação plano <-> serviço

    // 3. Se o limite NÃO for ilimitado (0), verifica o uso atual
    if ($limite_permitido > 0) {
        $query_uso_atual = $pdo->prepare("
            SELECT SUM(quantidade_usada) as total_usado
            FROM assinantes_servicos_usados
            WHERE id_assinante = :id_ass
              AND id_servico = :id_serv
              AND id_receber_associado = :id_rec
              AND id_conta = :id_conta
        ");
        $query_uso_atual->execute([
             ':id_ass' => $id_assinante,
             ':id_serv' => $id_servico,
             ':id_rec' => $id_receber_atual,
             ':id_conta' => $id_conta_corrente
        ]);
        $res_uso = $query_uso_atual->fetch();
        $usados_atualmente = $res_uso ? (int)$res_uso['total_usado'] : 0;

        // Verifica se o novo uso excede o limite
        if (($usados_atualmente + $quantidade_usada) > $limite_permitido) {
            throw new Exception("Limite excedido para este serviço neste ciclo! Limite: {$limite_permitido}, Já usados: {$usados_atualmente}, Tentando usar: {$quantidade_usada}");
        }
    }

    // 4. Insere o registro de uso
    $query_insert = $pdo->prepare("INSERT INTO assinantes_servicos_usados
        (id_assinante, id_servico, id_plano_servico, id_receber_associado, quantidade_usada, data_uso, id_usuario_registro, id_conta, observacao)
        VALUES
        (:id_ass, :id_serv, :id_ps, :id_rec, :qtd, NOW(), :id_user, :id_conta, :obs)");

    $query_insert->bindValue(':id_ass', $id_assinante, PDO::PARAM_INT);
    $query_insert->bindValue(':id_serv', $id_servico, PDO::PARAM_INT);
    $query_insert->bindValue(':id_ps', $id_plano_servico, PDO::PARAM_INT); // ID da ligação planos_servicos
    $query_insert->bindValue(':id_rec', $id_receber_atual, PDO::PARAM_INT); // ID do ciclo 'receber'
    $query_insert->bindValue(':qtd', $quantidade_usada, PDO::PARAM_INT);
    $query_insert->bindValue(':id_user', $id_usuario_registro, PDO::PARAM_INT);
    $query_insert->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_insert->bindValue(':obs', $observacao, PDO::PARAM_STR);

    $query_insert->execute();

    if ($query_insert->rowCount() > 0) {
         $pdo->commit();
         $response['success'] = true;
         $response['message'] = 'Uso do serviço registrado com sucesso!';
    } else {
         throw new Exception("Falha ao registrar o uso do serviço no banco de dados.");
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro de Banco de Dados: ' . $e->getMessage();
    error_log("Erro PDO em registrar_uso_servico: " . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral em registrar_uso_servico: " . $e->getMessage());
}

echo json_encode($response);
?>