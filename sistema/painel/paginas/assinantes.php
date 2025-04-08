<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");

$pagina = 'assinantes';
$id_conta_corrente = $id_conta; // Assume que $id_conta vem do verificar.php ou sessão
$data_atual = date('Y-m-d'); // Data de hoje para comparação
$data_atual_timestamp = strtotime($data_atual); // Timestamp de hoje para eficiência

?>
<style>
/* Estilo para linhas de assinantes inativos */
tr.assinante-inativo {
    opacity: 0.3; /* Define a transparência */
    /* Opcional: Pode adicionar outros estilos para inativos, como: */    
    font-style: italic;      /* Texto em itálico */
}

/* Seus estilos CSS (incluindo .assinante-inativo) */
tr.assinante-inativo {
    opacity: 0.3;
    font-style: italic;
}
/* Estilo opcional para o card de totais */
.card-totais {
    border-top: 3px solid #4682B4; /* Exemplo de destaque */
}

</style>

<?php 
// Busca os assinantes existentes com detalhes do plano E FREQUÊNCIA DA ÚLTIMA COBRANÇA
$assinantes = [];
$total_a_receber = 0; // Inicializa total a receber
$total_em_atraso = 0; // Inicializa total em atraso
try {
    $query = $pdo->prepare("
        SELECT
    -- Seleciona colunas específicas de 'assinantes'
    a.id, a.id_cliente, a.id_plano, a.data_cadastro, a.data_vencimento, a.ativo,
    -- Seleciona dados pessoais de 'clientes' (com alias 'c')
    c.nome, c.cpf, c.telefone, c.email,
    -- Seleciona dados do plano de 'planos' (com alias 'p')
    p.nome as nome_plano, p.preco_mensal, p.preco_anual,
    -- Subquery para buscar frequência da última cobrança (igual a antes)
    (SELECT r_freq.frequencia
     FROM receber r_freq
     WHERE r_freq.cliente = a.id AND r_freq.id_conta = a.id_conta AND r_freq.tipo = 'Assinatura'
     ORDER BY r_freq.data_venc DESC, r_freq.id DESC LIMIT 1
    ) as frequencia_atual,
    -- Subquery para buscar a ID da PRÓXIMA CONTA NÃO PAGA (igual a antes)
    (SELECT r_pag.id
     FROM receber r_pag
     WHERE r_pag.cliente = a.id AND r_pag.id_conta = a.id_conta AND r_pag.pago = 'Não' AND r_pag.tipo = 'Assinatura'
     ORDER BY r_pag.data_venc ASC, r_pag.id ASC LIMIT 1
    ) as id_receber_pendente,
    -- Subquery para buscar o VALOR da PRÓXIMA CONTA NÃO PAGA (corrigido)
    (SELECT r_val.valor
     FROM receber r_val
     WHERE r_val.cliente = a.id AND r_val.id_conta = a.id_conta AND r_val.pago = 'Não' AND r_val.tipo = 'Assinatura'
     ORDER BY r_val.data_venc ASC, r_val.id ASC LIMIT 1
    ) as valor_receber_pendente,
    -- Subquery para buscar o VENCIMENTO da PRÓXIMA CONTA NÃO PAGA (corrigido)
    (SELECT r_venc.data_venc -- Corrigido nome da coluna para vencimento
     FROM receber r_venc
     WHERE r_venc.cliente = a.id AND r_venc.id_conta = a.id_conta AND r_venc.pago = 'Não' AND r_venc.tipo = 'Assinatura'
     ORDER BY r_venc.data_venc ASC, r_venc.id ASC LIMIT 1
    ) as venc_receber_pendente
FROM
    assinantes a
-- JOIN para buscar os dados do cliente correspondente
INNER JOIN
    clientes c ON a.id_cliente = c.id AND a.id_conta = c.id_conta
-- LEFT JOIN para buscar os dados do plano (mais seguro se um plano for excluído)
LEFT JOIN
    planos p ON a.id_plano = p.id AND a.id_conta = p.id_conta
WHERE
    a.id_conta = :id_conta -- Filtra pela conta correta
ORDER BY
    c.nome ASC -- Ordena pelo NOME DO CLIENTE
    ");
    $query->execute([':id_conta' => $id_conta_corrente]);
    $assinantes = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro ao buscar assinantes: " . $e->getMessage();
}

// Busca os planos disponíveis para o select do modal
$planos_disponiveis = [];
try {
    $query_p = $pdo->prepare("SELECT id, nome FROM planos WHERE id_conta = :id_conta ORDER BY nome ASC");
    $query_p->execute([':id_conta' => $id_conta_corrente]);
    $planos_disponiveis = $query_p->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
     error_log("Erro ao buscar planos para modal: " . $e->getMessage());
}


?>

<?php // require_once("../cabecalho_painel.php"); // Exemplo ?>

<div class="container-fluid mt-4">
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h3>Gerenciamento de Assinantes</h3>
        <button type="button" class="btn btn-success" onclick="abrirModalAssinante()">
            <i class="fas fa-plus"></i> Novo Assinante
        </button>
    </div>
    <hr>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
     <?php if (isset($_GET['errmsg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['errmsg']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>


    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Nome</th>
                    <th>Plano</th>
                    <th>Valor Plano</th>
                    <th>Vencimento</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($assinantes) > 0): ?>
        <?php foreach ($assinantes as $assinante):        
            $data_venc_formatada = $assinante['data_vencimento'] ? date('d/m/Y', strtotime($assinante['data_vencimento'])) : '-';
            $nome_exibir = htmlspecialchars($assinante['nome_plano'] ?? 'Plano Desconhecido');
            $valor_exibir_num = $assinante['preco_mensal'] ?? 0; // Padrão mensal
             if (isset($assinante['frequencia_atual'])) {
                 if ($assinante['frequencia_atual'] == 365 && !empty($assinante['preco_anual'])) {
                     $nome_exibir .= " - Anual"; $valor_exibir_num = $assinante['preco_anual'];
                 } elseif ($assinante['frequencia_atual'] == 30) {
                      $nome_exibir .= " - Mensal";
                 } else { $nome_exibir .= " - (Freq. Desc.)"; }
             } else { $nome_exibir .= " - (Freq. Desc.)"; }
             $valor_exibir_fmt = number_format($valor_exibir_num, 2, ',', '.');
             $assinante_json = htmlspecialchars(json_encode($assinante), ENT_QUOTES, 'UTF-8');
             $classe_linha = ($assinante['ativo'] == 0) ? 'assinante-inativo' : '';
             $id_receber_pendente = $assinante['id_receber_pendente']; // Pega o ID da conta pendente
             $valor_pendente = $assinante['valor_receber_pendente'] ?? 0;
             $venc_pendente = $assinante['venc_receber_pendente'];
                         
             $data_atual = date('Y-m-d');
             $cor2 = "";

             if($assinante['venc_receber_pendente'] < $data_atual){
                $cor = 'red';
             }else{
                $cor = "";
             }

              // Soma aos totais se houver conta pendente
              if ($id_receber_pendente) {
                $valor_float = floatval($valor_pendente);
                $total_a_receber += $valor_float; // Soma ao total pendente

                // Verifica se está em atraso
                if ($venc_pendente && strtotime($venc_pendente) < $data_atual_timestamp) {
                    $total_em_atraso += $valor_float; // Soma ao total em atraso
                    $cor2 = 'red'; // Define a cor da linha
                }
            }
        ?>
            <tr class="<?= $classe_linha ?>" style = 'color: <?= $cor ?>' >
                <td><?= htmlspecialchars($assinante['nome'])?></td>
                <td><?= $nome_exibir ?></td> 
                <td>R$ <?= $valor_exibir_fmt ?></td>
                <td><?= $data_venc_formatada ?></td>
                <td class="text-center">
                    <?php if ($id_receber_pendente): ?>
                        <button type="button" class="btn btn-success btn-sm mr-1" title="Registrar Pagamento"
                                onclick="abrirModalPagar(<?= $id_receber_pendente ?>, '<?= htmlspecialchars(addslashes($assinante['nome']), ENT_QUOTES) ?>', <?= floatval($valor_pendente) ?>, '<?= htmlspecialchars($venc_pendente) ?>')">
                            <i class="fas fa-dollar-sign"></i> Pagar
                        </button>
                        <?php else: ?>
                            <span class="text-success" title="Nenhuma cobrança pendente"><i class="fas fa-check-circle"></i></span>
                        <?php endif; ?>
                        <?php if ($id_receber_pendente && $assinante['ativo'] == 1): ?>
             <button type="button" class="btn btn-primary btn-sm mr-1" title="Registrar Uso de Serviço"
                     onclick="abrirModalServico(<?= $assinante['id'] ?>, '<?= htmlspecialchars(addslashes($assinante['nome']), ENT_QUOTES) ?>', <?= $id_receber_pendente ?>)">
                 <i class="fas fa-concierge-bell"></i>
             </button>
         <?php endif; ?>
                    <button type="button" class="btn btn-secondary btn-sm mr-1" title="Visualizar Detalhes"
                            onclick="visualizarAssinante(<?= $assinante['id'] ?>)">
                        <i class='fas fa-eye'></i>
                    </button>
                    <button type="button" class="btn btn-warning btn-sm mr-1" title="Editar Assinante"
                                onclick='abrirModalAssinante(<?= $assinante_json ?>)'>
                        <i class="fas fa-edit"></i>
                    </button>

                    <?php // Botão Ativar/Inativar (muda com base no status atual)
                    if ($assinante['ativo'] == 1) { ?>
                        <button type="button" class="btn btn-outline-warning btn-sm mr-1" title="Inativar Assinante"
                                onclick="mudarStatusAssinante(<?= $assinante['id'] ?>, '<?= $assinante['nome_plano'] ?>', 0)"> 
                            <i class="fas fa-user-slash" style="color: red;"></i>
                        </button>
                    <?php } else { ?>
                        <button type="button" class="btn btn-outline-success btn-sm mr-1" title="Ativar Assinante"
                                onclick="mudarStatusAssinante(<?= $assinante['id'] ?>, '<?= $assinante['nome_plano'] ?>', 1)"> 
                            <i class="fas fa-user-check" style="color: green;"></i>
                        </button>
                    <?php } ?>

                    <button type="button" class="btn btn-danger btn-sm" title="Excluir Assinante Permanentemente"
                            onclick="abrirModalExcluirAssinante(<?= $assinante['id'] ?>, '<?= $assinante['nome_plano'] ?>')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nenhum assinante cadastrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="row mt-4 justify-content-end">
        <div class="col-md-6 col-lg-5 col-xl-4"> 
            <div class="card card-totais">
                 <div class="card-body">
                     <h5 class="card-title text-secondary">Resumo Financeiro</h5>
                     <hr class="my-2">
                     <p class="card-text d-flex justify-content-between mb-2">
                         <span>Total Pendente:</span>
                         <strong class="text-primary">R$ <?= number_format($total_a_receber, 2, ',', '.') ?></strong>
                     </p>
                     <p class="card-text d-flex justify-content-between mb-0">
                         <span>Total em Atraso:</span>
                          <strong class="text-danger">R$ <?= number_format($total_em_atraso, 2, ',', '.') ?></strong>
                     </p>
                 </div>
            </div>
         </div>
    </div>
</div>


<div class="modal fade" id="modalAssinante" tabindex="-1" role="dialog" aria-labelledby="modalAssinanteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
             <form id="form-assinante" method="post">
                <div class="modal-header text-white">
                    <h5 class="modal-title" id="modalAssinanteLabel">Adicionar Assinante</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_assinante" name="id_assinante"> 
                    <input type="hidden" id="id_cliente_encontrado" name="id_cliente_encontrado"> 
                    <input type="hidden" name="id_conta" value="<?= $id_conta_corrente ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ass_telefone">Telefone / WhatsApp <span class="text-danger">*</span></label>
                                <div class="input-group">
                                     <input type="text" class="form-control" id="ass_telefone" name="telefone" placeholder="(DDD) Número" required onblur="buscarClientePorTelefone(this.value)">
                                     <div class="input-group-append">
                                         <span class="input-group-text" id="telefone-loading" style="display: none;"><i class="fas fa-spinner fa-spin"></i></span>
                                         <span class="input-group-text" id="telefone-status"></span>
                                     </div>
                                 </div>
                                 <small id="mensagem-busca-cliente" class="form-text"></small>
                            </div>
                        </div>
                         <div class="col-md-6">
                             <div class="form-group">
                                 <label for="ass_nome">Nome Completo <span class="text-danger">*</span></label>
                                 <input type="text" class="form-control" id="ass_nome" name="nome" required>
                             </div>
                        </div>
                    </div>

                     <div class="row">
                         <div class="col-md-6">
                             <div class="form-group">
                                 <label for="ass_cpf">CPF</label>
                                 <input type="text" class="form-control" id="ass_cpf" name="cpf">
                             </div>
                        </div>
                         <div class="col-md-6">
                             <div class="form-group">
                                 <label for="ass_email">Email</label>
                                 <input type="email" class="form-control" id="ass_email" name="email">
                             </div>
                        </div>
                    </div>

                     <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ass_plano_freq">Plano e Frequência <span class="text-danger">*</span></label>
                                <select class="form-control" id="ass_plano_freq" name="plano_freq_selecionado" required>
                                     <option value="">-- Selecione o Plano e a Frequência --</option>
                                     <?php
                                     // Reutiliza $planos_disponiveis que já buscamos na página principal
                                     // Este loop agora está DENTRO do modal
                                     if(isset($planos_disponiveis) && count($planos_disponiveis) > 0){
                                        foreach ($planos_disponiveis as $plano_opt):
                                            $id_plano_opt = $plano_opt['id'];
                                            $nome_plano_opt = htmlspecialchars($plano_opt['nome']);

                                            // Busca os preços novamente para garantir que temos ambos aqui
                                            $query_precos = $pdo->prepare("SELECT preco_mensal, preco_anual FROM planos WHERE id = :id AND id_conta = :id_conta ORDER BY id ASC");
                                            $query_precos->execute([':id' => $id_plano_opt, ':id_conta' => $id_conta_corrente]);
                                            $precos = $query_precos->fetch(PDO::FETCH_ASSOC);

                                            if ($precos) {
                                                $preco_m_fmt = number_format($precos['preco_mensal'], 2, ',', '.');
                                                echo "<option value='{$id_plano_opt}-30'>{$nome_plano_opt} - Mensal (R$ {$preco_m_fmt})</option>";

                                                if (!empty($precos['preco_anual']) && $precos['preco_anual'] > 0) {
                                                    $preco_a_fmt = number_format($precos['preco_anual'], 2, ',', '.');
                                                    echo "<option value='{$id_plano_opt}-365'>{$nome_plano_opt} - Anual (R$ {$preco_a_fmt})</option>";
                                                }
                                            }
                                        endforeach;
                                    } else {
                                        echo '<option value="">Nenhum plano cadastrado</option>';
                                    }
                                     ?>
                                </select>
                            </div>
                        </div>
                         <div class="col-md-6">
                             <div class="form-group">
                                 <label for="ass_vencimento">Data de Vencimento <span class="text-danger">*</span></label>
                                 <input type="date" class="form-control" id="ass_vencimento" name="data_vencimento" required>
                             </div>
                        </div>
                    </div>

                    <small><div id="mensagem-assinante" class="mt-2"></div></small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>                    
                    <button type="button" class="btn btn-primary" id="btnSalvarAssinante">Salvar Assinante</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVisualizar" tabindex="-1" role="dialog" aria-labelledby="modalVisualizarLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-xl" role="document"> 
    <div class="modal-content">
      <div class="modal-header text-white" style="background-color: #4682B4;">
        <h5 class="modal-title" id="modalVisualizarLabel">Detalhes do Assinante</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
         <div id="loading-visualizar" class="text-center" style="display: none;">
             <i class="fas fa-spinner fa-spin fa-2x"></i> Carregando...
         </div>
         <div id="conteudo-visualizar">
             <div class="row">
                 <div class="col-md-6">
                    <p><strong>Nome:</strong> <span id="vis_nome"></span> <span id="vis_status_texto" class="ml-2"></span></p>
                    <p><strong>CPF:</strong> <span id="vis_cpf"></span></p>
                    <p><strong>Telefone:</strong> <span id="vis_telefone"></span></p>
                    <p><strong>Email:</strong> <span id="vis_email"></span></p>
                </div>
                 <div class="col-md-6">
                    <p><strong>Data Cadastro:</strong> <span id="vis_cadastro"></span></p>
                    <p><strong>Vencimento Assinatura:</strong> <span id="vis_vencimento"></span></p>
                    <p><strong>Plano Atual:</strong> <span id="vis_plano_nome" class="font-weight-bold"></span></p>
                    <p><strong>Valor Base:</strong> <span id="vis_plano_valor"></span></p>
                 </div>
             </div>
             <hr>
             <h6>Benefícios do Plano Atual (Uso / Limite):</h6>
             <div id="detalhes-plano-assinante" class="mb-3">
                 <p class="text-muted small">Carregando benefícios...</p>
             </div>
            
             <div class="text-center mb-3">                
                 <button type="button" class="btn btn-outline-info btn-sm" id="btnVerHistoricoUso" style="display: none;">
                     <i class="fas fa-history"></i> Ver Histórico de Uso Detalhado (Ciclo Atual)
                 </button>
             </div>              
             <div id="detalhes-historico-uso-detalhado" style="display: none; max-height: 250px; overflow-y: auto;">                 
             </div>
             
             <hr>


             <h6>Histórico de Cobranças / Pagamentos:</h6>
             <div id="detalhes-historico-pagamentos" style="max-height: 250px; overflow-y: auto;">                 
                 <p class="text-muted small">Carregando histórico...</p>
             </div>            

         </div>
         <small><div id="mensagem-visualizar" class="mt-2 text-center"></div></small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modalExcluirAssinante" tabindex="-1" role="dialog" aria-labelledby="modalExcluirAssinanteLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header text-white" style="background-color: #4682B4;">
        <h5 class="modal-title" id="modalExcluirAssinanteLabel">Confirmar Exclusão Permanente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Tem certeza que deseja excluir **permanentemente** o assinante "<span id="nome-assinante-excluir" class="font-weight-bold"></span>"?</p>
        <p class="text-danger"><strong>Atenção:</strong> Esta ação **não pode ser desfeita** e removerá também o histórico de cobranças associado a ele. É recomendado **inativar** o assinante em vez de excluir se quiser manter esses dados.</p>
        <input type="hidden" id="id_assinante_excluir">
        <small><div id="mensagem-excluir-assinante" class="mt-2 text-center"></div></small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btn-confirmar-exclusao-assinante">Confirmar Exclusão</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modalPagarConta" tabindex="-1" role="dialog" aria-labelledby="modalPagarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="form-pagar" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPagarLabel">Registrar Pagamento - <span id="pagar_nome_assinante" class="font-weight-bold"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pagar_id_receber" name="id_receber">
                    <input type="hidden" id="pagar_valor_original_hidden" name="valor_original">
                    <input type="hidden" name="id_conta" value="<?= $id_conta_corrente ?>">

                    <div class="mb-3">
                        <p class="mb-1"><strong>Descrição:</strong> <span id="pagar_descricao">Assinatura Plano...</span></p>
                        <p class="mb-1"><strong>Vencimento:</strong> <span id="pagar_vencimento">DD/MM/AAAA</span></p>
                        <p class="mb-1"><strong>Valor Original:</strong> R$ <span id="pagar_valor_original_display">0,00</span></p>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pagar_data_pagamento">Data Pagamento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" id="pagar_data_pagamento" name="data_pagamento" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                <div class="form-group">
                    <label for="pagar_forma_pgto">Forma Pagamento <span class="text-danger">*</span></label>
                    <select class="form-control form-control-sm" id="pagar_forma_pgto" name="forma_pgto" required> {/* Alterado name para 'forma_pgto' para clareza */}
                        <option value="">-- Selecione --</option>
                        <?php
                        try {
                            // Busca formas de pagamento ATIVAS (adicione WHERE ativo = 1 se tiver essa coluna)
                            // para a conta atual, ordenadas por nome.
                            $query_fp_modal = $pdo->prepare("SELECT id, nome FROM formas_pgto WHERE id_conta = :id_conta ORDER BY nome ASC");
                            // Certifique-se que $id_conta_corrente está definida e contém o ID da conta da sessão
                            $query_fp_modal->execute([':id_conta' => $id_conta_corrente]);
                            $formas_pgto_modal = $query_fp_modal->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($formas_pgto_modal as $forma):
                                // O 'value' da option envia o NOME da forma de pagamento.
                                // Isso corresponde ao script pagar_conta_receber.php que salva o nome.
                                // Se você modificar o backend para salvar o ID, mude o value para $forma['id'] aqui.
                                echo '<option value="' . htmlspecialchars($forma['nome']) . '">' . htmlspecialchars($forma['nome']) . '</option>';
                            endforeach;

                        } catch (PDOException $e) {
                            // Em caso de erro, loga e talvez mostre uma opção de erro
                            error_log("Erro ao buscar formas de pagamento para modal: " . $e->getMessage());
                            echo '<option value="">Erro ao carregar formas</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="pagar_multa">Multa (R$)</label>
                                <input type="text" class="form-control form-control-sm money" id="pagar_multa" name="multa" placeholder="0,00">
                            </div>
                        </div>
                         <div class="col-md-6">
                             <div class="form-group">
                                <label for="pagar_juros">Juros (R$)</label>
                                <input type="text" class="form-control form-control-sm money" id="pagar_juros" name="juros" placeholder="0,00">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pagar_valor_pago">Valor Recebido <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm money" id="pagar_valor_pago" name="valor_pago" required placeholder="0,00">
                    </div>

                    <div class="text-right font-weight-bold mt-2">
                       Total Calculado: R$ <span id="pagar_total_calculado">0,00</span>
                    </div>


                    <small><div id="mensagem-pagar" class="mt-2 text-center"></div></small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btn-confirmar-pagamento">Confirmar Pagamento</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="modalServico" tabindex="-1" role="dialog" aria-labelledby="modalServicoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="modalServicoLabel">Registrar Uso de Serviço - <span id="servico_nome_assinante" class="font-weight-bold"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_assinante_servico" name="id_assinante_servico">
                <input type="hidden" id="id_receber_atual" name="id_receber_atual">

                <h6>Registrar Novo Uso</h6>
                <form id="form-registrar-uso" class="mb-4">
                     <div class="row">
                        <div class="col-md-6 form-group">
                             <label for="select_servico_usar">Serviço Utilizado <span class="text-danger">*</span></label>
                             <select id="select_servico_usar" name="id_servico" class="form-control form-control-sm" required>
                                 <option value="">Carregando...</option>
                             </select>
                        </div>
                         <div class="col-md-3 form-group">
                             <label for="qtd_servico_usar">Quantidade <span class="text-danger">*</span></label>
                             <input type="number" class="form-control form-control-sm" id="qtd_servico_usar" name="quantidade_usada" value="1" min="1" required>
                         </div>
                         <div class="col-md-3 d-flex align-items-end form-group">
                             <button type="submit" class="btn btn-success btn-block btn-sm">Registrar Uso</button>
                         </div>
                     </div>
                     <div class="form-group">
                         <label for="obs_uso">Observação (Opcional)</label>
                         <textarea class="form-control form-control-sm" id="obs_uso" name="observacao" rows="2"></textarea>
                     </div>
                      <small><div id="mensagem-servico-add" class="mt-1 mb-2"></div></small>
                </form>
                <hr>

                <h6>Serviços Incluídos no Plano (Uso / Limite)</h6>
                <div id="lista-servicos-uso" style="max-height: 250px; overflow-y: auto;">
                    <p class="text-center text-muted">Carregando...</p>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>




<script>
    // Aplica máscaras
    $(document).ready(function(){
        $('#ass_cpf').mask('000.000.000-00', {reverse: true});
        $('#ass_telefone').mask('(00) 00000-0000');
    });

// Função para ABRIR modal (adaptada)
function abrirModalAssinante(assinante = null) {
    const $form = $('#form-assinante');
    const $modal = $('#modalAssinante');
    const $msgAssinante = $('#mensagem-assinante');
    const $msgBusca = $('#mensagem-busca-cliente');
    const $statusIcon = $('#telefone-status');

    $form[0].reset(); // Limpa form
    $('#id_assinante').val('');
    $('#id_cliente_encontrado').val(''); // Limpa ID cliente encontrado
    $msgAssinante.text('').removeClass('text-danger text-success');
    $msgBusca.text(''); // Limpa mensagem da busca
    $statusIcon.text(''); // Limpa ícone de status do telefone

    // Garante que campos sejam editáveis por padrão
    $('#ass_nome, #ass_cpf, #ass_email').prop('readonly', false);

    if (assinante && typeof assinante === 'object') {
        // Modo Edição
        $('#modalAssinanteLabel').text('Editar Assinante');
        $('#id_assinante').val(assinante.id);
        $('#id_cliente_encontrado').val(assinante.id_cliente); // Assume que id_cliente está nos dados do assinante
        $('#ass_nome').val(assinante.nome);
        $('#ass_cpf').val(assinante.cpf).trigger('input');
        $('#ass_telefone').val(assinante.telefone).trigger('input');
        $('#ass_email').val(assinante.email);

        // Pré-seleciona o Plano e Frequência corretos
        let frequenciaAtualParaSelect = assinante.frequencia_atual == 365 ? 365 : 30;
        const valorParaSelecionar = assinante.id_plano + '-' + frequenciaAtualParaSelect;
        $('#ass_plano_freq').val(valorParaSelecionar);

         // Formata data YYYY-MM-DD para input date
         let dataVenc = '';
         if(assinante.data_vencimento){
            try{ dataVenc = new Date(assinante.data_vencimento + 'T00:00:00').toISOString().split('T')[0]; } catch(e){}
         }
        $('#ass_vencimento').val(dataVenc);

        // Opcional: Desabilitar busca por telefone na edição?
        // $('#ass_telefone').prop('readonly', true);

    } else {
        // Modo Adição
        $('#modalAssinanteLabel').text('Adicionar Novo Assinante');
        $('#ass_vencimento').val('');
        $('#ass_plano_freq').val('');
        $('#ass_telefone').prop('readonly', false); // Garante que telefone seja editável
    }
    $modal.modal('show');
}

    // Função para Visualizar Assinante (AJUSTADA para mostrar histórico)
 function visualizarAssinante(idAssinante) {
     const $modal = $('#modalVisualizar');
     const $conteudoDiv = $('#conteudo-visualizar');
     const $loadingDiv = $('#loading-visualizar');
     const $mensagemDiv = $('#mensagem-visualizar');
     const $detalhesPlanoDiv = $('#detalhes-plano-assinante');
     const $statusAtivoSpan = $('#vis_status_texto');
     const $historicoDiv = $('#detalhes-historico-pagamentos'); // Div para o histórico
     const $historicoUsoDiv = $('#detalhes-historico-uso-detalhado'); // Div para histórico DETALHADO de USO
     const $btnVerHistoricoUso = $('#btnVerHistoricoUso'); // Botão para ver histórico de USO

     // Reset inicial
     $conteudoDiv.hide().css('opacity', '1');
     $loadingDiv.show();
     $mensagemDiv.text('');
     $detalhesPlanoDiv.html('<p class="text-muted small">Carregando benefícios...</p>');
     $historicoDiv.html('<p class="text-muted small">Carregando histórico...</p>'); // Limpa histórico
     $statusAtivoSpan.text('');
     $historicoUsoDiv.html('').hide(); // Esconde e limpa div do histórico de uso
     $btnVerHistoricoUso.hide().removeData('id-assinante').removeData('id-receber'); // Esconde e limpa dados anteriores do botão
     $modal.modal('show');

     $.ajax({
         url: 'paginas/<?= $pagina ?>/buscar_detalhes_assinante.php', // Verifique o caminho
         method: 'GET',
         data: { id_assinante: idAssinante, id_conta: '<?= $id_conta_corrente ?>' },
         dataType: 'json',
         success: function(response) {
            
             $loadingDiv.hide(); // Esconde loading independentemente do sucesso dos dados
             if (response.success && response.assinante && response.plano) {
                 const ass = response.assinante;
                 const plano = response.plano;
                 const servicos = response.servicos;
                 const historico = response.historico; // Pega o histórico da resposta                
                 const idReceberPendente = ass.id_receber_pendente; // Pega ID do ciclo atual
                 const idAssinante = ass.id; // Pega ID do assinante
                 

                 // Aplica opacidade se inativo
                 if (ass.ativo == '0' || ass.ativo == 0) {
                     $conteudoDiv.css('opacity', '0.5');
                     $statusAtivoSpan.text('(Inativo)').addClass('text-danger font-weight-bold ml-2');
                 } else {
                     $conteudoDiv.css('opacity', '1');
                     $statusAtivoSpan.text('').removeClass('text-danger font-weight-bold ml-2');
                 }

                 // Preenche dados do assinante
                 $('#vis_nome').text(ass.nome || '-');
                 $('#vis_cpf').text(ass.cpf || '-');
                 $('#vis_telefone').text(ass.telefone || '-');
                 $('#vis_email').text(ass.email || '-');
                 $('#vis_cadastro').text(ass.data_cadastro ? new Date(ass.data_cadastro).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '-');
                 $('#vis_vencimento').text(ass.data_vencimento ? new Date(ass.data_vencimento + 'T00:00:00').toLocaleDateString('pt-BR') : '-');

                 // Preenche dados do plano (com frequência)
                 let nomePlanoExibir = plano.nome || 'N/A';
                 let valorPlanoExibir = '-';
                 let precoNumerico = null;
                 if (ass.frequencia_atual == 365 && plano.preco_anual) { nomePlanoExibir += " - Anual"; precoNumerico = parseFloat(plano.preco_anual); }
                 else if (ass.frequencia_atual == 30 && plano.preco_mensal) { nomePlanoExibir += " - Mensal"; precoNumerico = parseFloat(plano.preco_mensal); }
                 else if (plano.preco_mensal) { nomePlanoExibir += " - (Mensal)"; precoNumerico = parseFloat(plano.preco_mensal); }
                 if(precoNumerico !== null){ valorPlanoExibir = 'R$ ' + precoNumerico.toLocaleString('pt-BR', { minimumFractionDigits: 2 }); }
                 $('#vis_plano_nome').text(nomePlanoExibir);
                 $('#vis_plano_valor').text(valorPlanoExibir);

                 // Monta lista de benefícios (com uso/limite)
                 let beneficiosHtml = '<ul class="list-unstyled plano-beneficios">';
                 if (servicos && servicos.length > 0) {
                     servicos.forEach(serv => { /* ... monta HTML como antes ... */
                        let limiteTexto = (serv.limite == 0) ? 'Ilimitado' : serv.limite;
                        let usoTexto = (serv.usados !== undefined ? serv.usados : '0') + ' / ' + limiteTexto; // Garante que 'usados' exista
                        if (serv.limite == 0) { usoTexto = `Ilimitado (Usados: ${serv.usados !== undefined ? serv.usados : '0'})`; }
                        let classeStatusUso = (serv.limite > 0 && serv.usados >= serv.limite) ? 'text-danger font-weight-bold' : 'text-muted';
                        beneficiosHtml += `<li><i class="fas fa-check text-success mr-2"></i>${serv.nome_servico || 'Serviço Desconhecido'} <small class="${classeStatusUso} ml-2">(${usoTexto})</small></li>`;
                    });
                 } else { beneficiosHtml += '<li><small class="text-muted">Nenhum serviço específico listado.</small></li>'; }
                 beneficiosHtml += '</ul>';
                 $detalhesPlanoDiv.html(beneficiosHtml);


                 

                 // --- MONTAGEM DO HISTÓRICO DE PAGAMENTOS ---
                 let historicoHtml = '<p class="text-muted small">Nenhum histórico de cobrança encontrado.</p>'; // Padrão
                 if (historico && historico.length > 0) {
                     historicoHtml = `
                         <table class="table table-sm table-striped table-hover small mt-2">
                             <thead>
                                 <tr>
                                     <th>Descrição</th>
                                     <th>Vencimento</th>
                                     <th>Pagamento</th>
                                     <th>Valor Pago</th>
                                     <th>Status</th>
                                 </tr>
                             </thead>
                             <tbody>
                     `;
                     historico.forEach(pag => {
                         let statusTexto = pag.pago === 'Sim' ? '<span class="badge badge-success">Pago</span>' : '<span class="badge badge-warning">Pendente</span>';
                         let dataPgtoTexto = pag.data_pgto ? new Date(pag.data_pgto + 'T00:00:00').toLocaleDateString('pt-BR') : '-';
                         let vencTexto = pag.data_venc ? new Date(pag.data_venc + 'T00:00:00').toLocaleDateString('pt-BR') : '-';
                         // Usa valor_pago se pago, senão valor original da cobrança
                         let valorTexto = pag.pago === 'Sim' && pag.valor_pago ? parseFloat(pag.valor_pago) : parseFloat(pag.valor);
                         let valorPagoFormatado = 'R$ ' + (isNaN(valorTexto) ? '0,00' : valorTexto.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));

                         let atrasoTexto = '';
                         if (pag.dias_atraso !== null && pag.dias_atraso > 0) {
                             atrasoTexto = `<small class="text-danger d-block">(${pag.dias_atraso} dia(s) atraso)</small>`;
                         } else if (pag.pago === 'Sim' && pag.dias_atraso === 0) {
                              atrasoTexto = `<small class="text-success d-block">(Em dia)</small>`;
                         } else if (pag.pago === 'Sim' && pag.dias_atraso === null){
                              // Pago, mas não foi possível calcular atraso (datas inválidas?)
                              atrasoTexto = `<small class="text-muted d-block">(Pago)</small>`;
                         }


                          historicoHtml += `
                             <tr>
                                 <td>${pag.descricao || '-'}</td>
                                 <td>${vencTexto}</td>
                                 <td>${dataPgtoTexto} ${atrasoTexto}</td>
                                 <td>${valorPagoFormatado}</td>
                                 <td>${statusTexto}</td>
                             </tr>
                         `;
                     });
                     historicoHtml += '</tbody></table>';
                 }
                 $historicoDiv.html(historicoHtml); // Popula a div de histórico
                 // --- FIM DO HISTÓRICO ---

                 // --- Prepara o botão para carregar histórico de USO ---
                if (idReceberPendente && idReceberPendente > 0) { // Só mostra botão se houver ciclo pendente válido
                    $btnVerHistoricoUso
                        .data('id-assinante', idAssinante) // Guarda o ID do assinante no botão
                        .data('id-receber', idReceberPendente) // Guarda o ID do ciclo no botão
                        .show(); // Mostra o botão
                    // Garante que a área de detalhes esteja escondida inicialmente
                    $historicoUsoDiv.html('').hide();
                } else {
                    $btnVerHistoricoUso.hide(); // Esconde se não há ciclo pendente
                    // Mostra um aviso na DIV de histórico de uso
                    $historicoUsoDiv.html('<p class="text-muted small text-center">Nenhuma cobrança pendente para exibir histórico de uso.</p>').show();
                }
                // --- Fim da preparação do botão ---

                 $conteudoDiv.show(); // Mostra todo o conteúdo


             } else { // Falha geral do AJAX ou assinante não encontrado
                  $mensagemDiv.text(response.message || 'Erro ao buscar detalhes.').addClass('text-danger');
                  $conteudoDiv.show().css('opacity', '1'); // Mostra div de conteúdo para exibir erro
                  $detalhesPlanoDiv.html('');
                  $historicoDiv.html(''); // Limpa histórico também
             }
         },
         error: function(xhr) { // Erro na comunicação AJAX
             $loadingDiv.hide();
              $mensagemDiv.text('Erro de comunicação. Verifique o console.').addClass('text-danger');
              $conteudoDiv.show().css('opacity', '1');
              $detalhesPlanoDiv.html('');
              $historicoDiv.html('');
              console.error("Erro ao buscar detalhes assinante:", xhr.responseText);
         }
     });
  }

 


     // Submit do Formulário Principal via Botão (AJAX)
$('#btnSalvarAssinante').on('click', function() {
    $('#form-assinante').submit(); // Dispara o evento submit do formulário
});

$('#form-assinante').on('submit', function(e) {
     e.preventDefault(); // Impede envio normal
     const form = this;
     const formData = new FormData(form); // Pega todos os dados, incluindo id_cliente_encontrado
     const $btnSubmit = $('#btnSalvarAssinante');
     const $msgDiv = $('#mensagem-assinante');

     $btnSubmit.prop('disabled', true).text('Salvando...');
     $msgDiv.text('').removeClass('text-danger text-success');

     $.ajax({
        url: 'paginas/<?= $pagina ?>/salvar_assinante.php', // <<<--- CRIE/AJUSTE ESTE ARQUIVO PHP
        type: 'POST',
        data: formData,
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        success: function(response) {
             if (response.success) {
                 $msgDiv.addClass('text-success').text(response.message);
                  setTimeout(function() {
                     $('#modalAssinante').modal('hide');
                     window.location.reload(); // Recarrega a lista
                  }, 1500);
             } else {
                 $msgDiv.addClass('text-danger').text(response.message);
                 $btnSubmit.prop('disabled', false).text('Salvar Assinante');
             }
         },
         error: function(xhr) {
             $msgDiv.addClass('text-danger').text('Erro de comunicação. Verifique o console.');
             console.error("Erro ao salvar assinante:", xhr.responseText);
             $btnSubmit.prop('disabled', false).text('Salvar Assinante');
         }
     });
});


     // Função para ATIVAR ou INATIVAR um assinante (via AJAX)
function mudarStatusAssinante(idAssinante, nomeAssinante, novoStatus) {
    const acao = novoStatus === 1 ? 'ativar' : 'inativar';
    const acaoVerb = novoStatus === 1 ? 'Ativando' : 'Inativando';
    const confirmTitle = 'Confirmar ' + (novoStatus === 1 ? 'Ativação' : 'Inativação');
    const confirmText = `Deseja realmente ${acao} o assinante "${nomeAssinante}"?`;

    Swal.fire({
        title: confirmTitle,
        text: confirmText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: novoStatus === 1 ? '#28a745' : '#ffc107', // Verde para ativar, Amarelo para inativar
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Sim, ${acao}!`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostra um indicador de carregamento
            Swal.fire({
                title: acaoVerb + '...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: 'paginas/<?= $pagina ?>/mudar_status_assinante.php', // <<<--- CRIE ESTE ARQUIVO PHP
                method: 'POST',
                data: {
                    id_assinante: idAssinante,
                    novo_status: novoStatus,
                    id_conta: '<?= $id_conta_corrente ?>' // Envia id_conta para segurança no backend
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            acao === 'ativar' ? 'Ativado!' : 'Inativado!',
                            response.message,
                            'success'
                        ).then(() => {
                            window.location.reload(); // Recarrega para ver a mudança
                        });
                    } else {
                        Swal.fire('Erro!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Erro!', 'Falha na comunicação com o servidor.', 'error');
                    console.error("Erro ao mudar status:", xhr.responseText);
                }
            });
        }
    });
}


// Função para ABRIR MODAL DE EXCLUSÃO
function abrirModalExcluirAssinante(id, nome) {
    $('#id_assinante_excluir').val(id);
    $('#nome-assinante-excluir').text(nome);
    $('#mensagem-excluir-assinante').text(''); // Limpa mensagens anteriores
    $('#modalExcluirAssinante').modal('show');
}

// Listener para o BOTÃO DE CONFIRMAR EXCLUSÃO dentro do modal
$('#btn-confirmar-exclusao-assinante').on('click', function() {
    const idParaExcluir = $('#id_assinante_excluir').val();
    const btn = $(this);
    const msgDiv = $('#mensagem-excluir-assinante');

    btn.prop('disabled', true).text('Excluindo...');
    msgDiv.text('').removeClass('text-danger text-success');

    $.ajax({
        url: 'paginas/<?= $pagina ?>/excluir_assinante.php', // <<<--- CRIE ESTE ARQUIVO PHP
        method: 'POST',
        data: {
            id_assinante: idParaExcluir,
            id_conta: '<?= $id_conta_corrente ?>' // Envia id_conta para segurança
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                msgDiv.addClass('text-success').text(response.message);
                // Fecha o modal e recarrega a página
                setTimeout(function() {
                    $('#modalExcluirAssinante').modal('hide');
                    window.location.reload();
                }, 1500);
            } else {
                msgDiv.addClass('text-danger').text(response.message);
                btn.prop('disabled', false).text('Confirmar Exclusão');
            }
        },
        error: function(xhr) {
            msgDiv.addClass('text-danger').text('Erro de comunicação.');
            console.error("Erro ao excluir assinante:", xhr.responseText);
            btn.prop('disabled', false).text('Confirmar Exclusão');
        }
    });
});



// Adicione máscaras para os novos campos de moeda
$(document).ready(function(){
    // ... suas outras máscaras e inicializações ...
    $('#pagar_multa, #pagar_juros, #pagar_valor_pago').mask('#.##0,00', {reverse: true});

    // Listener para calcular total no modal de pagamento
    $('#pagar_multa, #pagar_juros').on('input', function() {
        calcularTotalPagamento();
    });
     // Inicializa total quando modal abre (dentro de abrirModalPagar)
});

// Função para abrir o modal de pagamento
function abrirModalPagar(idReceber, nomeAssinante, valorOriginal, dataVenc) {
    const $modal = $('#modalPagarConta');
    // Limpa campos e mensagens
    $('#form-pagar')[0].reset();
    $('#mensagem-pagar').text('').removeClass('text-danger text-success');

    // Preenche dados
    $('#pagar_id_receber').val(idReceber);
    $('#pagar_nome_assinante').text(nomeAssinante);

    // Formata dados vindos do PHP (garante que sejam números para cálculo)
    const valorOrigFloat = parseFloat(valorOriginal) || 0;
    const dataVencObj = dataVenc ? new Date(dataVenc + 'T00:00:00') : null;
    const dataVencFormatada = dataVencObj ? dataVencObj.toLocaleDateString('pt-BR') : '-';
    const valorOrigFormatado = valorOrigFloat.toLocaleString('pt-BR', { minimumFractionDigits: 2 });

    $('#pagar_valor_original_hidden').val(valorOrigFloat); // Guarda valor numérico
    $('#pagar_descricao').text("Assinatura Pendente"); // Ou buscar descrição real via AJAX se necessário
    $('#pagar_vencimento').text(dataVencFormatada);
    $('#pagar_valor_original_display').text(valorOrigFormatado);

    // Define data de pagamento padrão como hoje
    $('#pagar_data_pagamento').val(new Date().toISOString().slice(0, 10));

    // Calcula e preenche o total inicial (sem multa/juros)
    calcularTotalPagamento(); // Isso também preenche #pagar_valor_pago inicialmente

    $modal.modal('show');
}

// Função para calcular o total e preencher o valor pago inicial
function calcularTotalPagamento() {
    const valorOriginal = parseFloat($('#pagar_valor_original_hidden').val()) || 0;
    // Função auxiliar para converter BRL string para float
    const brlStringToFloat = (str) => {
        if (!str) return 0;
        return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
    };

    const multa = brlStringToFloat($('#pagar_multa').val());
    const juros = brlStringToFloat($('#pagar_juros').val());
    const total = valorOriginal + multa + juros;
    const totalFormatado = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    $('#pagar_total_calculado').text(totalFormatado);

    // Preenche o campo 'Valor Recebido' com o total calculado por padrão
    // O usuário pode editar se o valor recebido for diferente
    $('#pagar_valor_pago').val(totalFormatado).mask('#.##0,00', {reverse: true}); // Reaplica máscara se necessário
}

// AJAX para confirmar pagamento
$('#form-pagar').on('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    const $btnSubmit = $(this).find('button[type="submit"]');
    const $msgDiv = $('#mensagem-pagar');

    $btnSubmit.prop('disabled', true).text('Processando...');
    $msgDiv.text('').removeClass('text-danger text-success');

    $.ajax({
        url: 'paginas/<?= $pagina ?>/pagar_conta_receber.php', // <<<--- CRIE ESTE ARQUIVO PHP
        method: 'POST',
        data: formData,
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        success: function(response) {
            if (response.success) {
                $msgDiv.addClass('text-success').text(response.message);
                Swal.fire({
                    title: 'Sucesso!',
                    text: response.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#modalPagarConta').modal('hide');
                    window.location.reload(); // Recarrega a lista de assinantes
                });
            } else {
                $msgDiv.addClass('text-danger').text(response.message);
                $btnSubmit.prop('disabled', false).text('Confirmar Pagamento');
            }
        },
        error: function(xhr) {
            $msgDiv.addClass('text-danger').text('Erro de comunicação.');
            console.error("Erro ao pagar conta:", xhr.responseText);
            $btnSubmit.prop('disabled', false).text('Confirmar Pagamento');
        }
    });
});

// Função para buscar cliente via AJAX quando sair do campo telefone
function buscarClientePorTelefone(telefone) {
    const telLimpo = telefone.replace(/\D/g, ''); // Remove não dígitos para validação básica
    const $inputNome = $('#ass_nome');
    const $inputCPF = $('#ass_cpf');
    const $inputEmail = $('#ass_email');
    const $hiddenClienteId = $('#id_cliente_encontrado');
    const $msgBusca = $('#mensagem-busca-cliente');
    const $loadingIcon = $('#telefone-loading');
    const $statusIcon = $('#telefone-status');

    $msgBusca.text(''); // Limpa mensagem anterior
    $statusIcon.text(''); // Limpa status anterior

    if (telLimpo.length < 10) { // Validação mínima (DDD + 8 dígitos)
        // Limpa campos se o telefone for apagado ou inválido
        $inputNome.val('').prop('readonly', false);
        $inputCPF.val('').prop('readonly', false);
        $inputEmail.val('').prop('readonly', false);
        $hiddenClienteId.val('');
        return;
    }

    $loadingIcon.show(); // Mostra loading

    $.ajax({
        url: 'paginas/<?= $pagina ?>/buscar_cliente_telefone.php', // <<< Verifique o caminho
        method: 'POST',
        data: { telefone: telefone, id_conta: '<?= $id_conta_corrente ?>' }, // Envia telefone com máscara (se salvo assim)
        dataType: 'json',
        success: function(response) {
            if (response.success && response.cliente) {
                // Cliente ENCONTRADO
                $inputNome.val(response.cliente.nome).prop('readonly', false); // Preenche e deixa editável (ou true se não quiser editar)
                $inputCPF.val(response.cliente.cpf || '').trigger('input').prop('readonly', false);
                $inputEmail.val(response.cliente.email || '').prop('readonly', false);
                $hiddenClienteId.val(response.cliente.id); // Guarda o ID do cliente existente
                $msgBusca.text('Cliente encontrado!').removeClass('text-danger').addClass('text-success');
                $statusIcon.html('<i class="fas fa-check text-success"></i>'); // Ícone de sucesso
            } else {
                // Cliente NÃO encontrado (Novo cliente)
                // Limpa os campos para garantir (exceto telefone)
                 $inputNome.val('').prop('readonly', false);
                 $inputCPF.val('').trigger('input').prop('readonly', false);
                 $inputEmail.val('').prop('readonly', false);
                 $hiddenClienteId.val(''); // Limpa ID do cliente
                 $msgBusca.text('Novo cliente. Preencha os dados.').removeClass('text-success text-danger');
                 $statusIcon.html('<i class="fas fa-user-plus text-info"></i>'); // Ícone de novo usuário
            }
        },
        error: function(xhr) {
            console.error("Erro ao buscar cliente:", xhr.responseText);
            $msgBusca.text('Erro ao buscar cliente.').removeClass('text-success').addClass('text-danger');
            $statusIcon.html('<i class="fas fa-times text-danger"></i>'); // Ícone de erro
            // Garante que campos sejam editáveis em caso de erro
            $inputNome.prop('readonly', false);
            $inputCPF.prop('readonly', false);
            $inputEmail.prop('readonly', false);
            $hiddenClienteId.val('');
        },
        complete: function() {
            $loadingIcon.hide(); // Esconde loading ao terminar
        }
    });
}


// Função para ABRIR MODAL DE USO DE SERVIÇO
function abrirModalServico(idAssinante, nomeAssinante, idReceberPendente) {
    const $modal = $('#modalServico');
    const $selectServico = $('#select_servico_usar');
    const $listaUsoDiv = $('#lista-servicos-uso');
    const $formRegistro = $('#form-registrar-uso');
    const $msgAddServico = $('#mensagem-servico-add');

    // Verifica se temos um ciclo de cobrança ativo/pendente
    if (!idReceberPendente || idReceberPendente <= 0) {
         Swal.fire('Atenção!', 'Não há uma cobrança de assinatura pendente para este assinante. Não é possível registrar uso de serviço agora.', 'warning');
         return;
     }


    // Preenche IDs e Título
    $('#servico_nome_assinante').text(nomeAssinante);
    $('#id_assinante_servico').val(idAssinante);
    $('#id_receber_atual').val(idReceberPendente);

    // Limpa e mostra loading
    $formRegistro[0].reset();
    $msgAddServico.text('').removeClass('text-danger text-success');
    $selectServico.html('<option value="">Carregando...</option>').prop('disabled', true);
    $listaUsoDiv.html('<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Carregando...</p>');

    $modal.modal('show');

    // Carrega dados via AJAX
    carregarServicosParaSelect(idAssinante);
    carregarUsoServicos(idAssinante, idReceberPendente);
}

// Função AJAX para popular o SELECT de serviços no form de registro
function carregarServicosParaSelect(idAssinante) {
     var $selectServico = $('#select_servico_usar');
     $.ajax({
        url: 'paginas/<?= $pagina ?>/listar_servicos_plano_select.php', // <<< Verifique caminho
        method: 'POST',
        data: { id_assinante: idAssinante, id_conta: '<?= $id_conta_corrente ?>' },
        dataType: 'html', // Espera options
        success: function(response) {
            $selectServico.html(response).prop('disabled', false);
        },
        error: function() {
            $selectServico.html('<option value="">Erro ao carregar</option>').prop('disabled', false);
        }
    });
}

// Função AJAX para listar o USO ATUAL dos serviços
function carregarUsoServicos(idAssinante, idReceber) {
    const listaDiv = $('#lista-servicos-uso');
    listaDiv.html('<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Carregando...</p>'); // Mostra loading

    $.ajax({
        url: 'paginas/<?= $pagina ?>/listar_servicos_com_uso.php', // <<< Verifique caminho
        method: 'POST',
        data: { id_assinante: idAssinante, id_receber: idReceber, id_conta: '<?= $id_conta_corrente ?>' },
        success: function(response) {
            listaDiv.html(response); // Preenche com a lista formatada
        },
        error: function() {
            listaDiv.html('<p class="text-center text-danger">Erro ao carregar o uso dos serviços.</p>');
        }
    });
}


// AJAX para REGISTRAR O USO do serviço
$('#form-registrar-uso').on('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    const $btnSubmit = $(this).find('button[type="submit"]');
    const $msgDiv = $('#mensagem-servico-add');
    const idAssinante = $('#id_assinante_servico').val(); // Pega ID do assinante do input hidden
    const idReceber = $('#id_receber_atual').val(); // Pega ID do ciclo do input hidden

     // Adiciona IDs que podem não estar no form diretamente ao FormData
     formData.append('id_assinante_servico', idAssinante);
     formData.append('id_receber_atual', idReceber);
     formData.append('id_conta', '<?= $id_conta_corrente ?>');


    $btnSubmit.prop('disabled', true).text('Registrando...');
    $msgDiv.text('').removeClass('text-danger text-success');

    $.ajax({
        url: 'paginas/<?= $pagina ?>/registrar_uso_servico.php', // <<< Verifique caminho
        type: 'POST',
        data: formData,
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        success: function(response) {
            if (response.success) {
                $msgDiv.addClass('text-success').text(response.message);
                form.reset(); // Limpa o formulário de registro
                carregarUsoServicos(idAssinante, idReceber); // ATUALIZA a lista de uso/limite
                 setTimeout(function() { $msgDiv.text('').removeClass('text-success'); }, 3000);
            } else {
                $msgDiv.addClass('text-danger').text(response.message);
            }
        },
        error: function(xhr) {
            $msgDiv.addClass('text-danger').text('Erro de comunicação.');
            console.error("Erro ao registrar uso:", xhr.responseText);
        },
        complete: function() {
             $btnSubmit.prop('disabled', false).text('Registrar Uso'); // Reabilita botão
        }
    });
});


// Listener para o botão "Ver/Esconder Histórico de Uso Detalhado" (COM TOGGLE)
$('#btnVerHistoricoUso').on('click', function() {
    const $button = $(this);
    const idAssinante = $button.data('id-assinante');
    const idReceber = $button.data('id-receber');
    const $historicoUsoDetalhadoDiv = $('#detalhes-historico-uso-detalhado');

    // 1. Verifica se a div de histórico JÁ ESTÁ visível
    if ($historicoUsoDetalhadoDiv.is(':visible')) {
        // Se está visível, apenas esconde com um efeito
        $historicoUsoDetalhadoDiv.slideUp('fast'); // Efeito "recolher"
        // Opcional: Muda o texto/ícone do botão de volta para "Ver"
        $button.html('<i class="fas fa-history"></i> Ver Histórico Detalhado');

    } else {
        // Se está escondida, executa a lógica para buscar e mostrar os dados

        // Verifica se os IDs necessários estão presentes no botão
        if (!idAssinante || !idReceber) {
            $historicoUsoDetalhadoDiv
                .html('<p class="text-danger small text-center">Erro: IDs necessários não encontrados no botão.</p>')
                .slideDown('fast'); // Mostra o erro
            return;
        }

        // Mostra loading e faz a chamada AJAX
        $historicoUsoDetalhadoDiv
            .html('<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Carregando histórico de uso...</p>')
            .slideDown('fast'); // Mostra o loading com efeito "expandir"
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Carregando...'); // Desabilita e mostra loading no botão

        $.ajax({
            url: 'paginas/<?= $pagina ?>/buscar_historico_uso_ajax.php', // Verifique o caminho
            method: 'POST', // Ou GET, ajuste o PHP
            data: {
                id_assinante: idAssinante,
                id_receber: idReceber,
                id_conta: '<?= $id_conta_corrente ?>'
            },
            // dataType: 'html', // Espera HTML diretamente
            success: function(responseHtml) {
                 // Coloca o HTML retornado (a tabela ou mensagem) na div
                $historicoUsoDetalhadoDiv.html(responseHtml);
                // Opcional: Muda o texto/ícone do botão para "Esconder"
                $button.html('<i class="fas fa-eye-slash"></i> Esconder Histórico');
            },
            error: function(xhr) {
                console.error("Erro ao buscar histórico de uso:", xhr.responseText);
                $historicoUsoDetalhadoDiv.html('<p class="text-center text-danger">Erro ao carregar histórico de uso.</p>');
                 // Opcional: Volta o texto do botão para "Ver" em caso de erro
                 $button.html('<i class="fas fa-history"></i> Ver Histórico Detalhado');
            },
            complete: function() {
                 $button.prop('disabled', false); // Reabilita o botão SEMPRE ao final
            }
        });
    }
});

</script>

</script>


</script>