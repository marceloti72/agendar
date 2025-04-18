<?php
// Arquivo: ajax/listar_servicos_para_agenda.php (Exemplo de Caminho)

require_once("../sistema/conexao.php"); // Ajuste o caminho
@session_start();

// Validações
if (!isset($_SESSION['id_conta'])) { exit; }
$id_conta = $_SESSION['id_conta'];
$cliente_id = isset($_POST['cliente_id']) ? filter_var($_POST['cliente_id'], FILTER_VALIDATE_INT) : 0;
$is_assinante = isset($_POST['is_assinante']) && $_POST['is_assinante'] === 'true'; // Converte string 'true' para boolean

$html_options = '<option value="">Selecione um Serviço</option>'; // Padrão

try {
    $id_assinante = null;
    $id_plano = null;
    $id_receber_ciclo = null;
    $frequencia_ciclo = 0;

    // Se for assinante, busca dados da assinatura e ciclo atual
    if ($is_assinante && $cliente_id > 0) {
        $query_ass = $pdo->prepare("SELECT id, id_plano FROM assinantes WHERE id_cliente = :id_cli AND id_conta = :id_conta AND ativo = 1 AND data_vencimento >= CURDATE()");
        $query_ass->execute([':id_cli' => $cliente_id, ':id_conta' => $id_conta]);
        $assinante_info = $query_ass->fetch();
        if ($assinante_info) {
            $id_assinante = $assinante_info['id'];
            $id_plano = $assinante_info['id_plano'];

            // Busca ciclo atual da assinatura
            // *** VERIFIQUE A FK: 'cliente' ou 'pessoa'? Assumindo 'cliente' = assinantes.id ***
            $query_rec = $pdo->prepare("SELECT id, frequencia FROM receber WHERE cliente = :id_ass AND id_conta = :id_conta AND pago = 'Não' AND tipo = 'Assinatura' ORDER BY data_venc ASC, id ASC LIMIT 1");
            $query_rec->execute([':id_ass' => $id_assinante, ':id_conta' => $id_conta]);
            $rec_atual = $query_rec->fetch();
            if($rec_atual){
                 $id_receber_ciclo = $rec_atual['id'];
                 $frequencia_ciclo = (int)$rec_atual['frequencia'];
            }
        }
    }

    // Busca todos os serviços ativos da conta
    $query_s = $pdo->prepare("SELECT id, nome, valor FROM servicos where ativo = 'Sim' and id_conta = :id_conta ORDER BY nome asc");
    $query_s->execute([':id_conta' => $id_conta]);
    $res_s = $query_s->fetchAll(PDO::FETCH_ASSOC);

    // Prepara query para contar uso (se for assinante e tiver ciclo)
    $query_uso = null;
    if ($id_assinante && $id_receber_ciclo) {
         $query_uso = $pdo->prepare("SELECT SUM(quantidade_usada) as total_usado FROM assinantes_servicos_usados WHERE id_assinante = :id_ass AND id_servico = :id_serv AND id_receber_associado = :id_rec AND id_conta = :id_conta");
    }


    foreach($res_s as $serv){
        $id_servico_atual = $serv['id'];
        $nome_servico_atual = htmlspecialchars($serv['nome']);
        $valor_original_servico = $serv['valor'];
        $valor_original_formatado = number_format($valor_original_servico, 2, ',', '.');

        $valor_exibir = $valor_original_formatado; // Valor padrão
        $texto_extra = ''; // Texto extra (assinatura, uso)
        $classe_extra = ''; // Classe CSS extra

        // Verifica cobertura pela assinatura SE for assinante E o serviço estiver no plano
        if ($is_assinante && $id_plano && $id_receber_ciclo) {
             // Verifica se este serviço está no plano e qual o limite base
             $query_limite = $pdo->prepare("SELECT quantidade FROM planos_servicos WHERE id_plano = :id_plano AND id_servico = :id_servico AND id_conta = :id_conta");
             $query_limite->execute([':id_plano' => $id_plano, ':id_servico' => $id_servico_atual, ':id_conta' => $id_conta]);
             $limite_info = $query_limite->fetch();

             if ($limite_info) { // Serviço está no plano
                 $limite_base = (int)$limite_info['quantidade'];
                 $limite_ciclo = $limite_base;
                 if ($frequencia_ciclo == 365 && $limite_base > 0) { $limite_ciclo = $limite_base * 12; }
                 elseif ($limite_base == 0) { $limite_ciclo = 0; } // Ilimitado

                 $usados_atualmente = 0;
                 if ($limite_ciclo !== 0 && $query_uso) { // Conta uso se não for ilimitado e a query estiver preparada
                      $query_uso->execute([':id_ass' => $id_assinante, ':id_serv' => $id_servico_atual, ':id_rec' => $id_receber_ciclo, ':id_conta' => $id_conta]);
                      $uso_info = $query_uso->fetch();
                      $usados_atualmente = $uso_info ? (int)$uso_info['total_usado'] : 0;
                 }

                 // Monta texto e define valor 0 se coberto
                 if ($limite_ciclo === 0 || ($usados_atualmente + 1) <= $limite_ciclo) { // +1 porque estamos vendo se PODE usar mais uma vez
                     $valor_exibir = '0,00'; // Valor zerado                     
                     $novo_uso = $usados_atualmente + 1;
                     $limite_texto = ($limite_ciclo === 0) ? 'Ilimitado' : $limite_ciclo;
                     $texto_extra = " (Assinatura - Uso: {$usados_atualmente} / {$limite_texto})"; // Mostra status atual
                 } else {
                      $texto_extra = " (Assinatura - Limite Atingido: {$usados_atualmente} / {$limite_ciclo})";                      
                 }
             }
             // Se serviço não está no plano, $texto_extra continua vazio e $valor_exibir mantém o original
        }

        // Monta a option
        $html_options .= '<option value="'.$id_servico_atual.'">'
                       . $nome_servico_atual . ' - R$ ' . $valor_exibir . $texto_extra
                       . '</option>';
    }

} catch (PDOException $e) {
     error_log("Erro ao listar serviços para agenda: " . $e->getMessage());
     $html_options = "<option value=''>Erro ao carregar serviços</option>";
}

echo $html_options; // Retorna as options formatadas
?>