<?php
require_once("../../../conexao.php");
@session_start();
$id_conta_corrente = @$_SESSION['id_conta'];

$id_assinante = isset($_POST['id_assinante']) ? (int)$_POST['id_assinante'] : 0;
$id_receber = isset($_POST['id_receber']) ? (int)$_POST['id_receber'] : 0; // ID do ciclo atual

$output = '<p class="text-danger text-center">Erro: Dados inválidos.</p>';

if ($id_assinante > 0 && $id_receber > 0 && isset($id_conta_corrente)) {
     $output = '<p class="text-muted text-center">Nenhum serviço encontrado para este plano.</p>'; // Padrão
    try {
        // Descobre o plano do assinante
        $query_plano = $pdo->prepare("SELECT id_plano FROM assinantes WHERE id = :id_ass AND id_conta = :id_conta");
        $query_plano->execute([':id_ass' => $id_assinante, ':id_conta' => $id_conta_corrente]);
        $plano_ass = $query_plano->fetch();

        if ($plano_ass) {
            $id_plano = $plano_ass['id_plano'];

            // Busca serviços do plano
            $query_serv_plano = $pdo->prepare("
                SELECT ps.id as id_plano_servico, ps.id_servico, ps.quantidade as limite, s.nome as nome_servico
                FROM planos_servicos ps
                JOIN servicos s ON ps.id_servico = s.id
                WHERE ps.id_plano = :id_plano AND ps.id_conta = :id_conta
                ORDER BY s.nome ASC
            ");
            $query_serv_plano->execute([':id_plano' => $id_plano, ':id_conta' => $id_conta_corrente]);
            $servicos_do_plano = $query_serv_plano->fetchAll(PDO::FETCH_ASSOC);

            if (count($servicos_do_plano) > 0) {
                 $html_output = '<ul class="list-group list-group-flush">';

                 // Prepara query para contar uso (será executada no loop)
                 $query_uso = $pdo->prepare("
                    SELECT SUM(quantidade_usada) as total_usado
                    FROM assinantes_servicos_usados
                    WHERE id_assinante = :id_ass
                      AND id_servico = :id_serv
                      AND id_receber_associado = :id_rec
                      AND id_conta = :id_conta
                 ");

                 foreach ($servicos_do_plano as $serv) {
                     $id_servico_atual = $serv['id_servico'];
                     $limite = (int)$serv['limite'];
                     $nome_servico = htmlspecialchars($serv['nome_servico']);

                     // Conta o uso para este serviço NESTE ciclo
                     $query_uso->execute([
                         ':id_ass' => $id_assinante,
                         ':id_serv' => $id_servico_atual,
                         ':id_rec' => $id_receber,
                         ':id_conta' => $id_conta_corrente
                     ]);
                     $res_uso = $query_uso->fetch();
                     $usados = $res_uso ? (int)$res_uso['total_usado'] : 0;

                     // Monta a exibição
                     $texto_limite = ($limite === 0) ? 'Ilimitado' : $limite;
                     $uso_texto = $usados . ' de ' . $texto_limite . ' usados';
                     if ($limite === 0) {
                         $uso_texto = 'Uso Ilimitado (' . $usados . ' registrados)';
                     }

                     // Adiciona classe se o limite foi atingido (e não for ilimitado)
                     $classe_limite = ($limite > 0 && $usados >= $limite) ? 'text-danger font-weight-bold' : 'text-muted';

                     $html_output .= '<li class="list-group-item d-flex justify-content-between align-items-center">';
                     $html_output .= '<span>✅ <b>' . $nome_servico . ':</b></span>';
                     $html_output .= '<small class="' . $classe_limite . '"> ' . $uso_texto . '</small>';
                     $html_output .= '</li>';
                 }

                $html_output .= '</ul>';
                $output = $html_output; // Define a saída final
            }
        } else {
             $output = '<p class="text-danger text-center">Assinante não encontrado.</p>';
        }

    } catch (PDOException $e) {
        error_log("Erro listar_servicos_com_uso: " . $e->getMessage());
        $output = '<p class="text-danger text-center">Erro ao carregar dados.</p>';
    }
}
echo $output;
?>