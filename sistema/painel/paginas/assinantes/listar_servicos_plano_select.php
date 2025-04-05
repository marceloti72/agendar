<?php
require_once("../../../conexao.php");
@session_start();
$id_conta_corrente = @$_SESSION['id_conta'];

$id_assinante = isset($_POST['id_assinante']) ? (int)$_POST['id_assinante'] : 0;
$output = '<option value="">Erro: Assinante inválido</option>'; // Padrão

if ($id_assinante > 0 && isset($id_conta_corrente)) {
    try {
        // Descobre o plano do assinante
        $query_plano = $pdo->prepare("SELECT id_plano FROM assinantes WHERE id = :id_ass AND id_conta = :id_conta");
        $query_plano->execute([':id_ass' => $id_assinante, ':id_conta' => $id_conta_corrente]);
        $plano_ass = $query_plano->fetch();

        if ($plano_ass) {
            $id_plano = $plano_ass['id_plano'];
            // Busca serviços DAQUELE plano
            $query_serv = $pdo->prepare("
                SELECT s.id, s.nome
                FROM planos_servicos ps
                JOIN servicos s ON ps.id_servico = s.id
                WHERE ps.id_plano = :id_plano AND ps.id_conta = :id_conta
                ORDER BY s.nome ASC
            ");
            $query_serv->execute([':id_plano' => $id_plano, ':id_conta' => $id_conta_corrente]);
            $servicos = $query_serv->fetchAll(PDO::FETCH_ASSOC);

            if (count($servicos) > 0) {
                 $output = '<option value="">-- Selecione o Serviço Usado --</option>';
                 foreach ($servicos as $serv) {
                     $output .= '<option value="' . $serv['id'] . '">' . htmlspecialchars($serv['nome']) . '</option>';
                 }
            } else {
                 $output = '<option value="">Nenhum serviço neste plano</option>';
            }
        } else {
             $output = '<option value="">Assinante não encontrado</option>';
        }

    } catch (PDOException $e) {
         error_log("Erro listar_servicos_plano_select: " . $e->getMessage());
         $output = '<option value="">Erro ao carregar serviços</option>';
    }
}
echo $output;
?>