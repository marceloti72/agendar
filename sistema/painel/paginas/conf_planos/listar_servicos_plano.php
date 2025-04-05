<?php
require_once("../../../conexao.php");
@session_start();
$id_conta_corrente = @$_SESSION['id_conta'];

$id_plano = isset($_POST['id_plano']) ? (int)$_POST['id_plano'] : 0;
$id_conta_form = isset($_POST['id_conta']) ? (int)$_POST['id_conta'] : 0;

// Validação
// if ($id_plano <= 0 || $id_conta_form !== $id_conta_corrente) {
//     echo '<p class="text-danger text-center">ID do plano ou conta inválido.</p>';
//     exit;
// }

$html_output = '<p class="text-center text-muted">Nenhum serviço associado a este plano.</p>'; // Mensagem padrão

try {
    $query = $pdo->prepare("
        SELECT ps.id as id_ligacao, ps.quantidade, s.nome as nome_servico
        FROM planos_servicos ps
        JOIN servicos s ON ps.id_servico = s.id
        WHERE ps.id_plano = :id_plano AND ps.id_conta = :id_conta
        ORDER BY s.nome ASC
    ");
    $query->execute([':id_plano' => $id_plano, ':id_conta' => $id_conta_corrente]);
    $servicos = $query->fetchAll(PDO::FETCH_ASSOC);

    if (count($servicos) > 0) {
        $html_output = '<ul class="list-group list-group-flush">'; // Usando lista para melhor semântica
        foreach ($servicos as $servico) {
            $id_ligacao = $servico['id_ligacao'];
            $nome_servico = htmlspecialchars($servico['nome_servico']);
            $quantidade = (int)$servico['quantidade'];
            $qtd_texto = ($quantidade === 0) ? 'Ilimitado' : $quantidade;

            $html_output .= '<li class="list-group-item d-flex justify-content-between align-items-center">';
            $html_output .= '<span>' . $nome_servico . '</span>';
            $html_output .= '<div>'; // Container para input e botão
            $html_output .= '   <input type="number" class="form-control form-control-sm d-inline-block input-qtd-servico" style="width: 70px; margin-right: 5px;" ';
            $html_output .= '          value="' . $quantidade . '" min="0" data-id="' . $id_ligacao . '">';
            $html_output .= '   <small class="text-muted mr-2"></small>';
            $html_output .= '   <button type="button" class="btn btn-danger btn-sm" title="Remover Serviço" ';
            $html_output .= '           onclick="removerServicoDoPlano(' . $id_ligacao . ', ' . $id_plano . ')">';
            $html_output .= '       <i class="fas fa-trash"></i>';
            $html_output .= '   </button>';
            $html_output .= '</div>';
            $html_output .= '</li><br>';
        }
        $html_output .= '</ul>';
    }

} catch (PDOException $e) {
    $html_output = '<p class="text-danger text-center">Erro ao buscar serviços: ' . htmlspecialchars($e->getMessage()) . '</p>';
    error_log("Erro SQL em listar_servicos_plano: " . $e->getMessage());
}

echo $html_output; // Envia o HTML gerado
?>