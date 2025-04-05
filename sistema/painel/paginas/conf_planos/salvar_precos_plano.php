<?php
require_once("../../../conexao.php");
@session_start();
$id_conta_corrente = @$_SESSION['id_conta']; // Ou de onde vier

header('Content-Type: application/json'); // Define o tipo de resposta como JSON

$response = ['success' => false, 'message' => 'Erro desconhecido.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido.';
    echo json_encode($response);
    exit;
}

$id_plano = isset($_POST['plano_id']) ? (int)$_POST['plano_id'] : 0;
$id_conta_form = isset($_POST['id_conta']) ? (int)$_POST['id_conta'] : 0;
$preco_mensal_str = isset($_POST['plano_preco_mensal']) ? $_POST['plano_preco_mensal'] : '';
$preco_anual_str = isset($_POST['plano_preco_anual']) ? $_POST['plano_preco_anual'] : '';

// Validação básica
// if ($id_conta_form !== $id_conta_corrente) {
//     $response['message'] = 'ID do plano ou conta inválido.';
//     echo json_encode($response);
//     exit;
// }

if (empty($preco_mensal_str)) {
     $response['message'] = 'O Preço Mensal é obrigatório.';
     echo json_encode($response);
     exit;
}


// Função para converter BRL para Decimal (ex: "1.234,56" -> 1234.56)
function brlToDecimal($valorBrl) {
    if (empty($valorBrl)) return null;
    $valor = str_replace('.', '', $valorBrl); // Remove separador de milhar
    $valor = str_replace(',', '.', $valor); // Troca vírgula por ponto
    return is_numeric($valor) ? (float)$valor : null;
}

$preco_mensal = brlToDecimal($preco_mensal_str);
$preco_anual = brlToDecimal($preco_anual_str); // Pode ser null se vazio

if ($preco_mensal === null) {
    $response['message'] = 'Formato inválido para Preço Mensal.';
    echo json_encode($response);
    exit;
}
if (!empty($preco_anual_str) && $preco_anual === null) {
     $response['message'] = 'Formato inválido para Preço Anual.';
     echo json_encode($response);
     exit;
}


try {
    // Atualiza o plano no banco
    $query = $pdo->prepare("UPDATE planos SET
                                preco_mensal = :preco_m,
                                preco_anual = :preco_a
                            WHERE id = :id_plano AND id_conta = :id_conta");

    $query->bindValue(":preco_m", $preco_mensal);
    $query->bindValue(":preco_a", $preco_anual, $preco_anual === null ? PDO::PARAM_NULL : PDO::PARAM_STR); // Usa NULL se for nulo
    $query->bindValue(":id_plano", $id_plano, PDO::PARAM_INT);
    $query->bindValue(":id_conta", $id_conta_corrente, PDO::PARAM_INT);
    $query->execute();

    if ($query->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Preços atualizados com sucesso!';
    } else {
        // Pode não ter encontrado o plano ou os valores eram os mesmos
        $response['message'] = 'Nenhuma alteração realizada (plano não encontrado ou valores iguais).';
        // Considere success=true se não for um erro real
        $response['success'] = true;
    }

} catch (PDOException $e) {
    $response['message'] = 'Erro ao atualizar preços: ' . $e->getMessage();
     error_log("Erro SQL em salvar_precos_plano: " . $e->getMessage()); // Log do erro
}

echo json_encode($response);
?>