<?php
@session_start();
require_once("verificar.php");
require_once("../conexao.php");

$pag = 'comanda';

//Verificar permissão
if (@$comanda == 'ocultar') {
    echo "<script>window.location='../index.php'</script>";
    exit();
}

$data_hoje = date('Y-m-d');
$data_ontem = date('Y-m-d', strtotime("-1 days", strtotime($data_hoje)));
$mes_atual = date('m');
$ano_atual = date('Y');
$data_inicio_mes = $ano_atual . "-" . $mes_atual . "-01";
$dia_final_mes = ($mes_atual == '4' || $mes_atual == '6' || $mes_atual == '9' || $mes_atual == '11') ? '30' : (($mes_atual == '2') ? '28' : '31');
$data_final_mes = $ano_atual . "-" . $mes_atual . "-" . $dia_final_mes;
?>
<style>
    /* Estilos gerais (mantenha os que você já tem) */
    .tooltip-inner { background-color: #48D1CC; color: #000; }
    .btn-custom { border-radius: 10px; box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4); }
    .modal-header-custom { background-color: #4682B4; color: white; }
    /* .modal-body-scroll JÁ TEM overflow: auto;  Removi altura máxima */
    .modal-body-scroll { overflow: auto; background-color: #FFFACD; }
    .servico-item, .produto-item { border: 7px solid #5c5c5c; margin-bottom: 5px; }
    .pagamento-header { background-color: #FFA500; display: flex; align-items: center; justify-content: center; }
    .input-background { background-color: #e9e6e6; }

     /* Estilos para responsividade */
    @media (max-width: 768px) { /* Ajuste este valor se necessário */
        .modal-dialog {
            width: 95%; /* Ocupa quase toda a largura em telas pequenas */
            margin: 10px auto; /* Centraliza e dá um pouco de margem */
        }
        .modal-body-scroll {
             max-height: 60vh; /* Usa vh (viewport height) para altura máxima */
        }

        /* Reduz espaçamento entre elementos */
        .form-group {
            margin-bottom: 0.5rem; /* Reduz a margem inferior */
        }
        .row > div {
            padding-left: 5px;  /* Reduz o padding lateral das colunas */
            padding-right: 5px;
        }
       .btn-primary.novo {
           /* Reduz um pouco o tamanho do botão e texto em telas pequenas*/
           font-size: 0.9em;
            padding: 0.25rem 0.5rem;
            height: auto; /* Altura automática */
       }
        .modal-body .row {
           margin-left: -5px; /* Compensa o padding reduzido */
           margin-right: -5px;
        }

        #btn_fechar_comanda,
        .btn-danger, .btn-primary {
            width: 100% !important; /* Força botões a terem largura total */
            margin-bottom: 5px;     /*Espaço entre botões*/

        }
    }
    .novo {
        display: flex;
        width: 100%;
        height: auto; /* Altura automática*/
        margin-bottom: 10px;
        font-size: 14px;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="mb-3">
    <a class="btn btn-primary btn-custom" onclick="inserir()"><i class="fa fa-plus"></i> Nova Comanda</a>
</div>

<div class="bs-example" style="padding:15px">
    <div class="row" style="margin-top: -20px">
        <div class="col-md-5 mb-2">
            <div class="d-inline-block me-2"><span><small><i title="Data Inicial" class="fa fa-calendar-o"></i></small></span></div>
            <div class="d-inline-block me-4"><input type="date" class="form-control" id="data-inicial-caixa" value="<?php echo $data_hoje ?>" required></div>
            <div class="d-inline-block me-2"><span><small><i title="Data Final" class="fa fa-calendar-o"></i></small></span></div>
            <div class="d-inline-block"><input type="date" class="form-control" id="data-final-caixa" value="<?php echo $data_hoje ?>" required></div>
        </div>

        <div class="col-md-3 text-center mt-2">
            <small>
                <a class="text-muted me-1" href="#" onclick="valorData('<?php echo $data_ontem ?>', '<?php echo $data_ontem ?>')">Ontem</a>/
                <a class="text-muted me-1" href="#" onclick="valorData('<?php echo $data_hoje ?>', '<?php echo $data_hoje ?>')">Hoje</a>/
                <a class="text-muted" href="#" onclick="valorData('<?php echo $data_inicio_mes ?>', '<?php echo $data_final_mes ?>')">Mês</a>
            </small>
        </div>

        <div class="col-md-4 text-center mt-2">
            <small>
                <a class="text-muted me-1" href="#" onclick="buscarContas('')">Todas</a>/
                <a class="text-muted me-1" href="#" onclick="buscarContas('Aberta')">Abertas</a>/
                <a class="text-muted" href="#" onclick="buscarContas('Fechada')">Fechadas</a>
            </small>
            <input type="hidden" id="buscar-contas" value="Aberta">
        </div>
    </div>

    <div id="listar"></div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document" style="width:80%;">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h4 class="modal-title"><span id="titulo_comanda">Nova Comanda</span></h4>
                <button type="button" id="btn-fechar" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="form_salvar">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="modal-body-scroll">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Cliente</label>
                                            <select class="form-control sel2" id="cliente" name="cliente" value="" style="width:100%;" required>
                                                <option value="">Selecionar Cliente</option>
                                                <?php
                                                $query = $pdo->query("SELECT * FROM clientes where id_conta = '$id_conta' ORDER BY nome asc");
                                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($res as $item) {
                                                    echo '<option value="' . $item['id'] . '">' . $item['nome'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Observações</label>
                                            <input type="text" class="form-control" value="" name="obs" id="obs2" maxlength="1000">
                                        </div>
                                    </div>
                                </div>

                                <hr style="border-top: 1px solid #cecece;">

                                 <div class="row mb-2">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Serviço</label>
                                            <select class="form-control sel2" id="servico" name="servico" value="" style="width:100%;" >
                                                <?php
                                                $query = $pdo->query("SELECT * FROM servicos where id_conta = '$id_conta' ORDER BY nome asc");
                                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($res as $item) {
                                                    echo '<option value="' . $item['id'] . '">' . $item['nome'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Profissional</label>
                                            <select class="form-control sel2" id="funcionario" name="funcionario" value="" style="width:100%;">
                                                <?php
                                                $query = $pdo->query("SELECT * FROM usuarios where atendimento = 'Sim' and id_conta = '$id_conta' ORDER BY nome asc");
                                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($res as $item) {
                                                    echo '<option value="' . $item['id'] . '">' . $item['nome'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mt-4">
                                        <button type="button" class="btn btn-primary novo" onclick="inserirServico()"><i class="fa fa-plus"></i>Inserir</button>
                                    </div>
                                </div>
                                <div class="servico-item" id="listar_servicos"></div>

                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                          <label>Produtos</label>
                                            <select class="form-control sel2" id="produto" name="produto" value="" style="width:100%;" required onchange="listarServicos()">
                                              <?php
                                                $query = $pdo->query("SELECT * FROM produtos where estoque > 0 and id_conta = '$id_conta' ORDER BY nome asc");
                                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                foreach($res as $item){
                                                  echo '<option value="'.$item['id'].'">'.$item['nome'].'</option>';
                                                }
                                                ?>
                                            </select>
                                      </div>
                                    </div>
                                      <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Quantidade</label>
                                            <input type="number" class="form-control" name="quantidade" id="quantidade" value="1">
                                        </div>
                                      </div>
                                      <div class="col-md-4">
                                        <div class="form-group">
                                         <label>Profissional</label>
                                            <select class="form-control sel2" id="funcionario2" name="funcionario2" value="" style="width:100%;" required onchange="listarServicos()">
                                                <option value="0">Nenhum</option>
                                                <?php
                                                  $query = $pdo->query("SELECT * FROM usuarios where nivel != 'administrador' and id_conta = '$id_conta' ORDER BY nome asc");
                                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                  foreach($res as $item){
                                                    echo '<option value="'.$item['id'].'">'.$item['nome'].'</option>';
                                                  }
                                                ?>
                                            </select>
                                        </div>
                                      </div>
                                       <div class="col-md-1 mt-4">
                                         <button type="button" class="btn btn-primary novo" onclick="inserirProduto()"><i class="fa fa-plus"></i>Inserir</button>
                                      </div>
                                </div>
                                <div class="produto-item" id="listar_produtos"></div>
                             </div> </div> <div class="col-md-4">
                           <div class="modal-header pagamento-header">
                                <img src="../../images/registradora.png" alt="Ícone Pagamento" style="height: 50px; margin-right: 10px;">
                                <h4 style="margin: 0;">PAGAMENTO</h4>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-5">
                                    <div class="form-group">
                                     <label><small>Valor</small></label>
                                        <input type="text" class="form-control input-background" name="valor_serv" id="valor_serv">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label><small>Data PGTO</small></label>
                                        <input type="date" class="form-control input-background" name="data_pgto" id="data_pgto" value="<?php echo date('Y-m-d') ?>">
                                    </div>
                                </div>
                           </div>

                            <div class="form-group">
                                <label><small>Forma PGTO</small></label>
                                <select class="form-control input-background" id="pgto" name="pgto" style="width:100%;" required>
                                    <?php
                                    $query = $pdo->query("SELECT * FROM formas_pgto where id_conta = '$id_conta'");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($res as $item) {
                                        echo '<option value="' . $item['nome'] . '">' . $item['nome'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                          <div class="row">
                                <div class="col-md-5">
                                  <div class="form-group">
                                    <label for="valor_serv_agd_restante">
                                      <small>Valor Restante</small>
                                      <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Caso o cliente efetue o pagamento com duas formas diferentes. Ex: Pix e Cartão."></i>
                                    </label>
                                    <input type="text" class="form-control input-background" name="valor_serv_agd_restante" id="valor_serv_agd_restante" onkeyup="abaterValor()">
                                  </div>
                                </div>
                                  <div class="col-md-7">
                                        <div class="form-group">
                                          <label><small>Data PGTO Restante</small></label>
                                            <input type="date" class="form-control input-background" name="data_pgto_restante" id="data_pgto_restante">
                                        </div>
                                </div>
                           </div>

                           <div class="form-group">
                                <label><small>Forma PGTO Restante</small></label>
                                <select class="form-control input-background" id="pgto_restante" name="pgto_restante" style="width:100%;">
                                    <option value="">Selecionar Pgto</option>
                                    <?php
                                    $query = $pdo->query("SELECT * FROM formas_pgto where id_conta = '$id_conta'");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($res as $item) {
                                        echo '<option value="' . $item['nome'] . '">' . $item['nome'] . '</option>';
                                    }
                                    ?>
                                </select>
                          </div>

                            <div class="text-center">
                                <a href="#" id="btn_fechar_comanda"  onclick="fecharComanda()" class="btn btn-success">Fechar Comanda</a>
                                <button type="button" class="btn btn-danger"  onclick="excluirComanda('0')" >Cancelar</button>
                                <button type="submit"
								<button type="submit" class="btn btn-primary" >Salvar</button>
                            </div>

                            <input type="hidden" name="id" id="id">
                            <input type="hidden" name="valor_servicos" id="valor_servicos">
                            <input type="hidden" name="valor_produtos" id="valor_produtos">
                            <small><div id="mensagem" align="center" class="mt-2"></div></small>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h4 class="modal-title">Informações da Comanda</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row" style="border-bottom: 1px solid #cac7c7;">
                    <div class="col-md-8">
                    <span><b>Cliente:</b></span>
                    <span id="cliente_dados"></span>
                    </div>
                    <div class="col-md-4">
                        <span><b>Valor: </b></span>
                        <span id="valor_dados"></span>
                    </div>
                </div>

                <div class="row" style="border-bottom: 1px solid #cac7c7;">
                    <div class="col-md-8">
                        <span><b>Aberta Por: </b></span>
                        <span id="func_dados"></span>
                    </div>
                    <div class="col-md-4">
                        <span><b>Data: </b></span>
                        <span id="data_dados"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 servico-item" id="listar_servicos_dados"></div>
                </div>

                <div class="row">
                    <div class="col-md-12 produto-item" id="listar_produtos_dados"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">var pag = "<?=$pag?>";</script>
<script src="js/ajax.js"></script>

<script>
    $(document).ready(function() {
        listar(); //Chama a função listar ao carregar a página
        $('.sel2').select2({
            dropdownParent: $('#modalForm')
        });

         // Inicializa tooltips
         $('[data-toggle="tooltip"]').tooltip();
    });

    function inserir() {
    $('#titulo_comanda').text('Nova Comanda');
    $('#form_salvar').trigger("reset"); // RESET COMPLETO DO FORMULÁRIO

    // As linhas abaixo NÃO são mais necessárias, pois o reset já faz isso
    //$('#id').val('');
    //$('#cliente').val('').trigger('change');
    // ... (todas as outras linhas que setam valores para '' ou valores padrão)

    // Mantém as linhas para limpar as listagens e a mensagem, e exibir o modal
    $('#listar_servicos').empty();
    $('#listar_produtos').empty();
    $('#mensagem').text('');

     //Re-inicializar os select2 após o reset, para que o dropdownParent funcione corretamente
    $('.sel2').select2({
          dropdownParent: $('#modalForm')
      });
    $('#modalForm').modal('show');
}



    function valorData(dataInicio, dataFinal) {
        $('#data-inicial-caixa').val(dataInicio);
        $('#data-final-caixa').val(dataFinal);
        listar();
    }

    $('#data-inicial-caixa, #data-final-caixa').change(function() {
        listar();
    });

    function buscarContas(status) {
        $('#buscar-contas').val(status);
        listar();
    }
    function listar() {
        var dataInicial = $('#data-inicial-caixa').val();
        var dataFinal = $('#data-final-caixa').val();
        var status = $('#buscar-contas').val();

        $.ajax({
            url: 'paginas/' + pag + "/listar.php",
            method: 'POST',
            data: { dataInicial, dataFinal, status },
            dataType: "html",
            success: function(result) {
                $("#listar").html(result);
            }
        });
    }


    function calcular() {
      setTimeout(function() {
        var produtos = parseFloat($('#valor_produtos').val() || 0); // Garante que é número
        var servicos = parseFloat($('#valor_servicos').val() || 0);

        var total = produtos + servicos;
        $('#valor_serv').val(total.toFixed(2));

        abaterValor(); //Chama depois de calcular o total
    }, 500);
    }

    function inserirServico() {
        $("#mensagem").text('');
        var servico = $("#servico").val();
        var funcionario = $("#funcionario").val();
        var cliente = $("#cliente").val();
        var id = $("#id").val();

        if (!cliente) { //Verificação mais robusta (falsy)
            alert("Selecione um Cliente");
            return;
        }
        if (!servico) {
            alert("Selecione um Serviço");
            return;
        }

        $.ajax({
            url: 'paginas/' + pag + "/inserir_servico.php",
            method: 'POST',
            data: { servico, funcionario, cliente, id },
            dataType: "text",
            success: function(result) {
                if (result.trim() === 'Salvo com Sucesso') {
                    listarServicos(id);
                    calcular(); //Recalcula após inserir
                } else {
                    $("#mensagem").text(result);
                }
            }
        });
    }

    function listarServicos(id) {
        $.ajax({
            url: 'paginas/' + pag + "/listar_servicos.php",
            method: 'POST',
            data: { id },
            dataType: "html", // Importante para conteúdo HTML
            success: function(result) {
                $("#listar_servicos").html(result);
            }
        });
    }
    function inserirProduto() {
        $("#mensagem").text('');
        var produto = $("#produto").val();
        var funcionario = $("#funcionario2").val();
        var cliente = $("#cliente").val();
        var quantidade = $("#quantidade").val();
        var id = $("#id").val();


        if (!produto) {
            alert("Selecione um Produto");
            return;
        }

          if (!cliente) { //Verificação mais robusta (falsy)
            alert("Selecione um Cliente");
            return;
        }

        $.ajax({
            url: 'paginas/' + pag + "/inserir_produto.php",
            method: 'POST',
            data: { produto, funcionario, cliente, quantidade, id },
            dataType: "text",
            success: function(result) {
                if (result.trim() === 'Salvo com Sucesso') {
                    listarProdutos(id);
                    calcular(); //Recalcula
                    $("#quantidade").val('1');
                } else {
                    $("#mensagem").text(result);
                }
            }
        });
    }

    function listarProdutos(id) {
        $.ajax({
            url: 'paginas/' + pag + "/listar_produtos.php",
            method: 'POST',
            data: { id },
            dataType: "html",
            success: function(result) {
                $("#listar_produtos").html(result);
            }
        });
    }

    function fecharComanda() {
        var cliente = $("#cliente").val();
        var valor = $("#valor_serv").val();
        var valor_restante = $("#valor_serv_agd_restante").val();
        var data_pgto = $("#data_pgto").val();
        var data_pgto_restante = $("#data_pgto_restante").val();
        var pgto_restante = $("#pgto_restante").val();
        var pgto = $("#pgto").val();
        var id = $("#id").val();

        if (valor_restante > 0) {
            if (!data_pgto_restante || !pgto_restante) { //Verificação mais segura
                alert('Preencha a Data de Pagamento Restante e o tipo de Pagamento Restante');
                return;
            }
        }

        $.ajax({
            url: 'paginas/' + pag + "/fechar_comanda.php",
            method: 'POST',
            data: { id, valor, valor_restante, data_pgto, data_pgto_restante, pgto_restante, pgto, cliente },
            dataType: "text",
            success: function(result) {
                if (result.trim() === 'Salvo com Sucesso') {
                    $('#btn-fechar').click();
                    listar();
                    $('#data_pgto').val('<?php echo $data_hoje; ?>');
                    $('#valor_serv_agd_restante').val('');
                    $('#data_pgto_restante').val('');
                    $('#pgto_restante').val('').trigger('change'); //Limpa select2
                } else {
                    $("#mensagem").text(result);
                }
            }
        });
    }

      function listarProdutosDados(id) {
        $.ajax({
            url: 'paginas/' + pag + "/listar_produtos_dados.php",
            method: 'POST',
            data: { id },
            dataType: "html",
            success: function(result) {
                $("#listar_produtos_dados").html(result);
            }
        });
    }

    function listarServicosDados(id) {
        $.ajax({
            url: 'paginas/' + pag + "/listar_servicos_dados.php",
            method: 'POST',
            data: { id },
            dataType: "html",
            success: function(result) {
                $("#listar_servicos_dados").html(result);
            }
        });
    }


    $("#form_salvar").submit(function(event) {
        event.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: 'paginas/' + pag + "/salvar.php",
            type: 'POST',
            data: formData,
            success: function(mensagem) {
                var msg = mensagem.split("*");
                $('#mensagem').text('');
                $('#mensagem').removeClass();

                if (msg[0].trim() == "Salvo com Sucesso") {
                    // var salvar = $('#salvar_comanda').val();  // REMOVIDO

                    // if (salvar == 'Sim') {                // REMOVIDO
                    //     $("#id").val(msg[1]);              // REMOVIDO
                    //     fecharComanda();                   // REMOVIDO
                    // }                                      // REMOVIDO
                    $('#btn-fechar').click();
                    listar(); // Atualiza a lista após salvar
                } else {
                    $('#mensagem').addClass('text-danger');
                    $('#mensagem').text(msg[0]);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
        });
    });

    function abaterValor() {
     var produtos = parseFloat($('#valor_produtos').val() || 0);  // Valor padrão 0
        var servicos = parseFloat($('#valor_servicos').val() || 0);

        var total_valores = produtos + servicos;

        var valor = parseFloat($("#valor_serv").val() || 0);
        var valor_rest = parseFloat($("#valor_serv_agd_restante").val() || 0);

        var total = total_valores - valor_rest;
        $('#valor_serv').val(total.toFixed(2));
    }
</script>