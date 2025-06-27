<?php
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");

$pagina = 'conf_planos';
$id_conta_corrente = $id_conta; // Assume que $id_conta vem do verificar.php ou sessão

if(@$_SESSION['nivel_usuario'] != 'Administrador'){
	    echo "<script>window.location='agenda.php'</script>";
    }

// Busca os planos existentes (presumindo que eles já existem no BD)
try {
    // Ordena pelo nome para garantir Bronze, Prata, etc., se os nomes forem esses
    $query_planos = $pdo->prepare("SELECT * FROM planos WHERE id_conta = :id_conta ORDER BY FIELD(nome, 'Bronze', 'Prata', 'Ouro', 'Diamante'), ordem ASC, nome ASC");
    $query_planos->execute([':id_conta' => $id_conta_corrente]);
    $planos = $query_planos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao buscar planos: " . $e->getMessage();
    $planos = []; // Define como array vazio em caso de erro
}

// Busca todos os serviços disponíveis para adicionar aos planos
$servicos_disponiveis = [];
try {
    $query_servicos_todos = $pdo->prepare("SELECT id, nome FROM servicos WHERE id_conta = :id_conta ORDER BY nome ASC");
    $query_servicos_todos->execute([':id_conta' => $id_conta_corrente]);
    $servicos_disponiveis = $query_servicos_todos->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
     error_log("Erro ao buscar serviços: " . $e->getMessage());
}


?>

<?php // require_once("../cabecalho_painel.php"); // Exemplo ?>

<div class="container-fluid mt-4">
    <h3>Configuração de Planos de Assinatura</h3>
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
                    <th>Plano</th>
                    <th>Preço Mensal</th>
                    <th>Preço Anual</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($planos) > 0): ?>
                    <?php foreach ($planos as $plano):
                        // Prepara dados para passar ao JS de forma segura
                        $id_plano_js = $plano['id'];
                        $nome_plano_js = htmlspecialchars(addslashes($plano['nome']), ENT_QUOTES);
                        $preco_m_js = $plano['preco_mensal']; // Passa o número cru
                        $preco_a_js = $plano['preco_anual'] ?: 'null'; // Passa null se for nulo/vazio
                    ?>
                        <tr>
                            <td>
                                <?php if($plano['imagem'] && $plano['imagem'] != 'sem-foto.jpg'): ?>
                                    <img src="../../images/<?= htmlspecialchars($plano['imagem']) ?>" width="30" height="30" class="mr-2 rounded-circle" style="object-fit: cover;">
                                <?php endif; ?>
                                <strong><?= htmlspecialchars($plano['nome']) ?></strong>
                            </td>
                            <td>R$ <?= number_format($plano['preco_mensal'], 2, ',', '.') ?></td>
                            <td><?= $plano['preco_anual'] ? 'R$ ' . number_format($plano['preco_anual'], 2, ',', '.') : '-' ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm mr-1" title="Editar Preços"
                                        onclick="abrirModalPrecos(<?= $id_plano_js ?>, '<?= $nome_plano_js ?>', <?= $preco_m_js ?>, <?= $preco_a_js ?>)">
                                    <i class="fas fa-dollar-sign"></i> Preços
                                </button>
                                <button type="button" class="btn btn-info btn-sm" title="Gerenciar Serviços Incluídos"
                                        onclick="abrirModalServicos(<?= $id_plano_js ?>, '<?= $nome_plano_js ?>')">
                                    <i class="fas fa-list-check"></i> Serviços
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">Nenhum plano encontrado. Cadastre os planos Bronze, Prata, Ouro e Diamante primeiro.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalEditarPrecos" tabindex="-1" role="dialog" aria-labelledby="modalPrecosLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="form-editar-precos" method="post"> 
                <div class="modal-header text-white" style="background-color: #4682B4;">
                    <h5 class="modal-title" id="modalPrecosLabel">Editar Preços - Plano <span id="nome-plano-precos"></span></h5>
                    <button type="button" class="close" style="margin-top: -20px;" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="plano_id_precos" name="plano_id">
                    <input type="hidden" name="id_conta" value="<?= $id_conta_corrente ?>">

                     <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plano_preco_mensal_edit">Preço Mensal <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="plano_preco_mensal_edit" name="plano_preco_mensal" placeholder="0,00" required>
                            </div>
                        </div>
                         <div class="col-md-6">
                             <div class="form-group">
                                 <label for="plano_preco_anual_edit">Preço Anual (Opcional)</label>
                                 <input type="text" class="form-control" id="plano_preco_anual_edit" name="plano_preco_anual" placeholder="0,00">
                             </div>
                        </div>
                    </div>
                    <small><div id="mensagem-precos" class="mt-2"></div></small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Preços</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modalServicos" tabindex="-1" role="dialog" aria-labelledby="modalServicosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                 <h5 class="modal-title" id="modalServicosLabel">Gerenciar Serviços - Plano <span id="nome-plano-modal-servicos"></span></h5>
                <button type="button" class="close" style="margin-top: -20px;" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                 <input type="hidden" id="id_plano_servicos" name="id_plano_servicos">
                 <input type="hidden" name="id_conta" value="<?= $id_conta_corrente ?>">


                 <h5>Adicionar Serviço ao Plano</h5>
                 <form id="form-add-servico" class="form-inline mb-3">
                    <input type="hidden" id="id_plano_add_servico" name="id_plano">
                    <input type="hidden" name="id_conta" value="<?= $id_conta_corrente ?>">
                    <div class="form-group mr-2 flex-grow-1">
                         <label for="select-servico-add" class="sr-only">Serviço</label>
                         <select class="form-control form-control-sm w-100" id="select-servico-add" name="id_servico" required>
                            <option value="">-- Selecione um Serviço --</option>
                            <?php foreach ($servicos_disponiveis as $serv): ?>
                                <option value="<?= $serv['id'] ?>"><?= htmlspecialchars($serv['nome']) ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     <div class="form-group mr-2" style="width: 90px;">
                         <label for="qtd-servico-add" class="sr-only">Qtd</label>
                         <input type="number" class="form-control form-control-sm w-100" id="qtd-servico-add" name="quantidade" value="1" min="0" required placeholder="Qtd" style="width: 80px;">
                         <small class="text-muted d-block text-center"></small>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm align-self-end">Adicionar</button>                 </form>
                 <small><div id="mensagem-add-servico" class="mb-2"></div></small>
                 <hr>

                 <h5>Serviços Incluídos Neste Plano</h5>
                 <div id="listar-servicos-plano" style="max-height: 300px; overflow-y: auto;">
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
    // Função para formatar número como moeda BRL para os inputs
    function formatarMoeda(valor) {
        if (valor === null || valor === undefined || valor === '') return '';
        // Converte para número, formata e substitui ponto por vírgula
        return parseFloat(valor).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Função para ABRIR MODAL DE PREÇOS e preencher
    function abrirModalPrecos(id, nome, precoM, precoA) {
        $('#form-editar-precos')[0].reset(); // Limpa form
        $('#plano_id_precos').val(id);
        $('#nome-plano-precos').text(nome);
        $('#plano_preco_mensal_edit').val(formatarMoeda(precoM));
        $('#plano_preco_anual_edit').val(formatarMoeda(precoA));
        $('#mensagem-precos').text('').removeClass('text-danger text-success');

        // Aplica máscara aos campos de preço (requer jquery.mask.min.js)
         $('#plano_preco_mensal_edit, #plano_preco_anual_edit').mask('#.##0,00', {reverse: true});


        $('#modalEditarPrecos').modal('show');
    }

    // AJAX para SALVAR PREÇOS
    $('#form-editar-precos').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const msgDiv = $('#mensagem-precos');
        var formData = new FormData(this);

        btn.prop('disabled', true).text('Salvando...');
        msgDiv.text('').removeClass('text-danger text-success');

        $.ajax({
            url: 'paginas/<?= $pagina ?>/salvar_precos_plano.php', // <<<--- CRIE ESTE ARQUIVO PHP
            method: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false, // Necessário para FormData
            cache: false,       // Necessário para FormData
            processData: false, // Necessário para FormData
            success: function(response) {
                if (response.success) {
                    msgDiv.addClass('text-success').text(response.message);
                    // Recarrega a página para ver a tabela atualizada
                    setTimeout(function() {
                        // $('#modalEditarPrecos').modal('hide'); // Fecha o modal
                        window.location.reload(); // Recarrega a página inteira
                    }, 1500);
                } else {
                    msgDiv.addClass('text-danger').text(response.message);
                    btn.prop('disabled', false).text('Salvar Preços');
                }
            },
            error: function(xhr) {
                 msgDiv.addClass('text-danger').text('Erro de comunicação. Verifique o console.');
                 console.error("Erro ao salvar preços:", xhr.responseText);
                 btn.prop('disabled', false).text('Salvar Preços');
            }
        });
    });


    // --- Funções para Gerenciar Serviços ---

    function abrirModalServicos(idPlano, nomePlano) {
        $('#id_plano_servicos').val(idPlano); // Input hidden no modal de serviços
        $('#id_plano_add_servico').val(idPlano); // Input hidden no form de adicionar
        $('#nome-plano-modal-servicos').text(nomePlano);
        $('#modalServicos').modal('show');
        carregarServicosDoPlano(idPlano);
        // Limpa o form de adicionar
        $('#select-servico-add').val('');
        $('#qtd-servico-add').val('1');
        $('#mensagem-add-servico').text('').removeClass('text-danger text-success');
    }

    function carregarServicosDoPlano(idPlano) {
        const listaDiv = $('#listar-servicos-plano');
        listaDiv.html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando...</p>');

        $.ajax({
            url: 'paginas/<?= $pagina ?>/listar_servicos_plano.php', // <<<--- CRIE ESTE ARQUIVO PHP
            method: 'POST',
            data: { id_plano: idPlano, id_conta: '<?= $id_conta_corrente ?>' },
            success: function(response) {
                listaDiv.html(response);
                // Aplica máscara aos inputs de quantidade que podem ter sido carregados
                listaDiv.find('.input-qtd-servico').mask('00'); // Máscara simples para quantidade
            },
            error: function() {
                listaDiv.html('<p class="text-center text-danger">Erro ao carregar serviços.</p>');
            }
        });
    }

    // Submit do form para adicionar serviço ao plano (AJAX)
    $('#form-add-servico').on('submit', function(e) {
        e.preventDefault();
        const idPlano = $('#id_plano_add_servico').val();
        const idServico = $('#select-servico-add').val();
        const quantidade = $('#qtd-servico-add').val();
        const msgDiv = $('#mensagem-add-servico');
        const btn = $(this).find('button[type="submit"]');


        if (!idServico) {
            msgDiv.text('Selecione um serviço.').addClass('text-danger').removeClass('text-success');
            return;
        }

        msgDiv.text('Adicionando...').removeClass('text-danger text-success');
        btn.prop('disabled', true);

        $.ajax({
             url: 'paginas/<?= $pagina ?>/adicionar_servico_plano.php', // <<<--- CRIE ESTE ARQUIVO PHP
             method: 'POST',
             data: {
                 id_plano: idPlano,
                 id_servico: idServico,
                 quantidade: quantidade,
                 id_conta: '<?= $id_conta_corrente ?>'
             },
             dataType: 'json',
             success: function(response) {
                if (response.success) {
                     msgDiv.addClass('text-success').text(response.message);
                     carregarServicosDoPlano(idPlano); // Recarrega a lista
                     $('#select-servico-add').val('');
                     $('#qtd-servico-add').val('1');
                      setTimeout(function() { msgDiv.text('').removeClass('text-success'); }, 2500); // Limpa msg
                 } else {
                     msgDiv.addClass('text-danger').text(response.message);
                 }
             },
             error: function() {
                  msgDiv.addClass('text-danger').text('Erro de comunicação.');
             },
             complete: function() {
                 btn.prop('disabled', false); // Reabilita o botão
             }
        });
    });

     // Função para REMOVER serviço do plano (chamada por um botão/link na lista)
     // O botão/link deve ser gerado por listar_servicos_plano.php com o ID correto
     function removerServicoDoPlano(idPlanoServico, idPlano) {
         Swal.fire({
             title: 'Tem certeza?',
             text: "Deseja realmente remover este serviço do plano?",
             icon: 'warning',
             showCancelButton: true,
             confirmButtonColor: '#d33',
             cancelButtonColor: '#3085d6',
             confirmButtonText: 'Sim, remover!',
             cancelButtonText: 'Cancelar'
         }).then((result) => {
             if (result.isConfirmed) {
                 $.ajax({
                     url: 'paginas/<?= $pagina ?>/remover_servico_plano.php', // <<<--- CRIE ESTE ARQUIVO PHP
                     method: 'POST',
                     data: { id_plano_servico: idPlanoServico, id_conta: '<?= $id_conta_corrente ?>' },
                     dataType: 'json',
                     success: function(response) {
                         if (response.success) {
                            carregarServicosDoPlano(idPlano); // Recarrega a lista
                            Swal.fire('Removido!', 'Serviço removido do plano.', 'success');
                         } else {
                            Swal.fire('Erro!', response.message, 'error');
                         }
                     },
                     error: function() {
                          Swal.fire('Erro!', 'Erro de comunicação ao remover.', 'error');
                     }
                 });
             }
         })
     }

    // Função para ATUALIZAR quantidade (chamada ao mudar valor no input da lista)
     function atualizarQuantidadeServico(idPlanoServico, novaQuantidade, idPlano) {
          // Adiciona um pequeno delay para evitar chamadas múltiplas rápidas
         clearTimeout(window.delayAtualizarQtd); // Limpa timeout anterior se houver
         window.delayAtualizarQtd = setTimeout(() => {
             $.ajax({
                 url: 'paginas/<?= $pagina ?>/atualizar_qtd_servico.php', // <<<--- CRIE ESTE ARQUIVO PHP
                 method: 'POST',
                 data: {
                     id_plano_servico: idPlanoServico,
                     quantidade: novaQuantidade,
                     id_conta: '<?= $id_conta_corrente ?>'
                  },
                 dataType: 'json',
                 success: function(response) {
                     if (!response.success) {
                          Swal.fire('Erro!', response.message, 'error');
                         // Talvez recarregar a lista para reverter?
                         // carregarServicosDoPlano(idPlano);
                     } else {
                         console.log('Quantidade atualizada para ' + idPlanoServico);
                         // Feedback visual sutil opcional (ex: borda verde rápida)
                     }
                 },
                 error: function() {
                     Swal.fire('Erro!', 'Erro de comunicação ao atualizar quantidade.', 'error');
                     // carregarServicosDoPlano(idPlano); // Reverter
                 }
             });
         }, 500); // Atraso de 500ms antes de enviar a atualização
     }

    // Listener para mudança nos inputs de quantidade DENTRO da lista de serviços
    // A lista é carregada dinamicamente, então usamos delegação de eventos
     $('#listar-servicos-plano').on('change input', '.input-qtd-servico', function() {
         const idPlanoServico = $(this).data('id'); // Pega o ID da ligação
         const novaQtd = $(this).val();
         const idPlano = $('#id_plano_servicos').val(); // Pega o ID do plano atual
         if (idPlanoServico && idPlano) {
             atualizarQuantidadeServico(idPlanoServico, novaQtd, idPlano);
         }
     });

</script>