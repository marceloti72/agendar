<?php
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");

$pag = 'comanda';

//Verificar permissão
if(@$_SESSION['nivel_usuario'] != 'Administrador'){
	    echo "<script>window.location='agenda.php'</script>";
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
    .tooltip-inner { background-color: #48D1CC; color: #000; }
    .btn-custom { border-radius: 10px; box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4); }
    .modal-header-custom { background-color: #4682B4; color: white; }
    .modal-body-scroll { overflow: scroll; max-height: 550px; scrollbar-width: thin; background-color: #FFFACD; }
    .servico-item, .produto-item { border: 3px solid #5c5c5c; margin-bottom: 5px; }
    .pagamento-header { background-color:rgb(207, 239, 245); display: flex; align-items: center; justify-content: center; padding: 10px; }
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
    

	.novo {
		display: flex;
		width: 100%;
		height: 30px;
		margin-bottom: 10px;
		font-size: 14px;
		align-items: center;
		justify-content: center;
			
        }
        .comanda{
            width: 100%;
            height: 80px;
        }
    }
</style>

<!-- <div class="mb-3">
    <a class="btn btn-primary btn-custom" onclick="inserir()"><i class="fa fa-plus"></i> Nova Comanda</a>
</div> -->

<div class="bs-example" style="padding:15px">
    <div class="row" style="margin-top: -20px">
        <div class="col-md-5 col-sm-12 mb-3">
            <div class="row">
                <div class="col-sm-6 mb-2">
                     <div class="d-inline-block">
                        <span><small><i title="Data Inicial" class="fa fa-calendar-o"></i></small></span> Data inicial
                    </div>
                    <input type="date" class="form-control form-control-sm" id="data-inicial-caixa" value="<?php echo $data_hoje; ?>" required>
                </div>
                 <div class="col-sm-6 mb-2">
                    <div class="d-inline-block">
                        <span><small><i title="Data Final" class="fa fa-calendar-o"></i></small></span> Data Final
                    </div>
                    <input type="date" class="form-control form-control-sm" id="data-final-caixa" value="<?php echo $data_hoje; ?>" required>
                </div>
            </div>
        </div>

        <div class="col-md-7 col-sm-12">
            <div class="row">
                <div class="col-sm-6 text-center text-sm-start mt-2 mt-sm-0">
                    <small>
                        <a class="text-muted me-1" href="#" onclick="valorData('<?php echo $data_ontem; ?>', '<?php echo $data_ontem; ?>')">Ontem</a> /
                        <a class="text-muted me-1" href="#" onclick="valorData('<?php echo $data_hoje; ?>', '<?php echo $data_hoje; ?>')">Hoje</a> /
                        <a class="text-muted" href="#" onclick="valorData('<?php echo $data_inicio_mes; ?>', '<?php echo $data_final_mes; ?>')">Mês</a>
                    </small>
                </div>

                <div class="col-sm-6 text-center text-sm-end mt-2 mt-sm-0">
                    <small>
                        <a class="text-muted me-1" href="#" onclick="buscarContas('')">Todas</a> /
                        <a class="text-muted me-1" href="#" onclick="buscarContas('Aberta')">Abertas</a> /
                        <a class="text-muted" href="#" onclick="buscarContas('Fechada')">Fechadas</a>
                    </small>
                    <input type="hidden" id="buscar-contas" value="Aberta">
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 20px;">
        <div class="col-12 text-center">
            <div id="listar"></div>
        </div>
    </div>
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
                                    </div><br>
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
                                      <div class="col-md-3">
                                        <div class="form-group">
                                         <label>Profissional</label>
                                            <select class="form-control sel2" id="funcionario2" name="funcionario2" value="" style="width:100%;" required onchange="listarServicos()">
                                                <option value="0">Nenhum</option>
                                                <?php
                                                  $query = $pdo->query("SELECT * FROM usuarios where nivel != 'Administrador' and id_conta = '$id_conta' ORDER BY nome asc");
                                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                  foreach($res as $item){
                                                    echo '<option value="'.$item['id'].'">'.$item['nome'].'</option>';
                                                  }
                                                ?>
                                            </select>
                                        </div>
                                      </div><br>
                                       <div class="col-md-2 mt-4">
                                         <button type="button" class="btn btn-primary novo" onclick="inserirProduto()"><i class="fa fa-plus"></i>Inserir</button>
                                      </div>
                                </div>
                                <div class="produto-item" id="listar_produtos"></div>
                             </div> </div> <div class="col-md-4">
                           <div class="modal-header2 pagamento-header">
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

                          <!-- <div class="row">
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
                          </div>  -->
                                                     
							<a href="#" id="btn_fechar_comanda" style="width: 100%;margin-bottom: 20px;" onclick="fecharComanda()" class="btn btn-success">Fechar Comanda</a>

							<button type="button" data-dismiss="modal" class="btn btn-dark" style="width: 100%;margin-bottom: 20px;">Sair</a>

                            <!-- <div class="text-center">                           
							   <button type="button" class="btn btn-danger" style="width: 150px;" onclick="excluirComanda('0')" >Cancelar</button>
                               <button type="submit" style="width: 150px;" class="btn btn-primary" >Salvar</button>            
								
                            </div> -->

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

    $('#modalForm').on('hidden.bs.modal', function (e) {          
         location.reload();
     });

    function inserir() {
    $('#titulo_comanda').text('Nova Comanda');
    $('#form_salvar').trigger("reset"); // RESET COMPLETO DO FORMULÁRIO

    // As linhas abaixo NÃO são mais necessárias, pois o reset já faz isso
    $('#id').val('');
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
    $("#mensagem").text(''); // Limpa mensagens anteriores na página principal, se houver
    var servico = $("#servico").val();
    var funcionario = $("#funcionario").val();
    var cliente = $("#cliente").val(); // Este é o ID do CLIENTE
    var id_atual_comanda = $("#id").val(); // Pega o ID atual ANTES do AJAX

    console.log("Iniciando inserirServico. ID Comanda Atual:", id_atual_comanda, "Cliente:", cliente, "Serviço:", servico, "Funcionário:", funcionario);

    // Validações
    if (!cliente) {
        Swal.fire('Atenção!', 'Selecione um Cliente', 'warning');
        return;
    }
    if (!servico) {
        Swal.fire('Atenção!', 'Selecione um Serviço', 'warning');
        return;
    }
     // Validação Funcionário (se obrigatório)
     if (!funcionario) {
        Swal.fire('Atenção!', 'Selecione um Profissional', 'warning');
        return;
     }


    // Adicionar um indicador de loading
    var $botaoOriginal = $('#botao-inserir-servico'); // Certifique-se que seu botão tem id="botao-inserir-servico"
    if($botaoOriginal.length){ $botaoOriginal.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Inserindo...'); }


    $.ajax({
        url: 'paginas/' + pag + "/inserir_servico.php", // Verifique o caminho e nome do script PHP
        method: 'POST',
        data: {
            servico: servico,
            funcionario: funcionario,
            cliente: cliente,
            id: id_atual_comanda // Envia o ID atual (pode ser 0)
        },
        dataType: "json", // Espera JSON

        success: function(response) {
            console.log("Resposta recebida de inserir_servico.php:", response); // Log da resposta completa

            if (response && response.success) { // Verifica se a resposta é válida e se teve sucesso

                // ***** INÍCIO: VERIFICA E ATUALIZA ID DA COMANDA *****
                let comanda_id_para_listar = id_atual_comanda; // Usa o ID antigo por padrão
                if (response.nova_comanda_id && response.nova_comanda_id > 0) {
                    // Se o PHP criou uma nova comanda e retornou o ID
                    $('#id').val(response.nova_comanda_id); // ATUALIZA o input hidden
                    comanda_id_para_listar = response.nova_comanda_id; // Usa o NOVO ID para listar
                    console.log("Input #id ATUALIZADO com Nova Comanda ID:", comanda_id_para_listar);
                    // Opcional: Atualizar título do modal, etc.
                    // $('#titulo_comanda').text('Comanda #' + comanda_id_para_listar);
                } else {
                     // Garante que estamos usando um ID válido para listar, mesmo que não seja novo
                     if (comanda_id_para_listar <= 0 && $('#id').val() > 0) {
                         comanda_id_para_listar = $('#id').val();
                         console.log("Usando ID da comanda do input #id:", comanda_id_para_listar);
                     }
                }
                // ***** FIM: VERIFICA E ATUALIZA ID DA COMANDA *****

                // Mostra feedback Swal
                let swalTitle = 'Sucesso!';
                let swalText = response.message || 'Operação realizada.';
                 if(response.tipo_registro === 'Assinante'){
                     swalTitle = 'Serviço Registrado!';
                     swalText = response.message + (response.detalhe_assinatura ? ' ' + response.detalhe_assinatura : '');
                 } else if (response.tipo_registro === 'Servico' && response.detalhe_assinatura) {
                     swalText += response.detalhe_assinatura;
                 }
                  Swal.fire({
                     position: 'center',
                     icon: 'success',
                     title: swalTitle,
                     text: swalText,
                     showConfirmButton: false,
                     timer: 2000
                 });

                // Atualiza a interface usando o ID CORRETO
                console.log("Chamando listarServicos com ID:", comanda_id_para_listar);
                if(typeof listarServicos === 'function') {
                    listarServicos(comanda_id_para_listar); // <<-- USA O ID CORRETO
                } else {
                     console.error("FUNÇÃO LISTARSERVICOS NÃO DEFINIDA!");
                     alert("Erro Crítico: Função listarServicos não encontrada.");
                }

                 console.log("Chamando calcular()...");
                 if(typeof calcular === 'function') {
                    calcular();
                } else {
                     console.warn("Função calcular() não definida!");
                }

                // Limpar selects após sucesso
                // Considerar não limpar funcionário se for adicionar outro serviço com o mesmo
                 $('#servico').val('').trigger('change.select2'); // Limpa Select2 serviço
                 // $('#funcionario').val('').trigger('change.select2'); // Limpa Select2 funcionário?

            } else {
                // Se response.success for false
                Swal.fire('Erro!', response.message || 'Ocorreu um erro ao processar a solicitação.', 'error');
            }
        },
        error: function(xhr, status, error) {
            // Erro de comunicação ou erro fatal no PHP que não retornou JSON válido
            Swal.fire('Erro Crítico!', 'Falha na comunicação com o servidor ou erro interno. Verifique o console (F12).', 'error');
            console.error("Erro AJAX inserirServico:", status, error, xhr.responseText); // Loga o erro real
        },
        complete: function() {
             // Reabilita o botão de inserir serviço
             if($botaoOriginal.length){ $botaoOriginal.prop('disabled', false).html('<i class="fa fa-plus"></i> Inserir'); }
        }
    });
}

    function listarServicos() {
        var id_atual_comanda = $("#id").val(); // Pega o ID atual da comanda (pode ser 0)
        var cliente = $("#cliente").val();

        if (!cliente) {
        Swal.fire('Atenção!', 'Selecione um Cliente', 'warning');
        return;
        }

        $.ajax({
            url: 'paginas/' + pag + "/listar_servicos.php",
            method: 'POST',
            data: { id: id_atual_comanda },
            dataType: "html", // Importante para conteúdo HTML
            success: function(result) {
                $("#listar_servicos").html(result);
            }
        });
    }

    function inserirProduto() {
    $("#mensagem").text(''); // Limpa mensagens gerais, se houver
    var produto = $("#produto").val();
    var funcionario = $("#funcionario2").val(); // Pega do select 'funcionario2'
    var cliente = $("#cliente").val();
    var quantidade = $("#quantidade").val();
    var id_atual_comanda = $("#id").val(); // Pega o ID atual da comanda (pode ser 0)

    // Validações
    if (!cliente) {
        Swal.fire('Atenção!', 'Selecione um Cliente', 'warning');
        return;
    }
    if (!produto) {
        Swal.fire('Atenção!', 'Selecione um Produto', 'warning');
        return;
    }
     if (!quantidade || parseInt(quantidade) <= 0) {
        Swal.fire('Atenção!', 'Informe uma quantidade válida (maior que zero).', 'warning');
        return;
    }
    // Validação de funcionário é opcional para produto? Se não, adicione:
    // if (!funcionario || funcionario == "0") { Swal.fire('Atenção!', 'Selecione um funcionário.', 'warning'); return; }


    // Indicador de loading no botão (ajuste o seletor do botão se necessário)
    var $botaoOriginal = $('button[onclick="inserirProduto()"]'); // Tenta pegar pelo onclick
    if($botaoOriginal.length){ $botaoOriginal.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Inserindo...'); }

    // Log para debug
    console.log("Enviando para inserir_produto.php:", { produto, funcionario, cliente, quantidade, id: id_atual_comanda });

    $.ajax({
        url: 'paginas/' + pag + "/inserir_produto.php", // <<< VERIFIQUE O CAMINHO E NOME DO ARQUIVO PHP
        method: 'POST',
        data: {
            produto: produto,
            funcionario: funcionario, // ID do funcionário (pode ser 0 se 'Nenhum' for selecionado)
            cliente: cliente,
            quantidade: quantidade,
            id: id_atual_comanda // Envia o ID atual da comanda (pode ser 0)
        },
        dataType: "json", // <<<----- CORRIGIDO PARA JSON -----<<<

        success: function(response) {
            console.log("Resposta recebida de inserir_produto.php:", response); // Log da resposta

            if (response && response.success) { // Verifica resposta válida e sucesso

                // ***** INÍCIO: VERIFICA E ATUALIZA ID DA COMANDA *****
                let comanda_id_para_listar = id_atual_comanda; // Usa o ID antigo por padrão
                if (response.nova_comanda_id && response.nova_comanda_id > 0) {
                    // Se o PHP criou uma nova comanda e retornou o ID
                    $('#id').val(response.nova_comanda_id); // ATUALIZA o input hidden
                    comanda_id_para_listar = response.nova_comanda_id; // Usa o NOVO ID para listar
                    console.log("Input #id ATUALIZADO com Nova Comanda ID:", comanda_id_para_listar);
                } else if (comanda_id_para_listar <= 0 && $('#id').val() > 0){
                     // Garante que usa o ID do input se ele já existia mas não era novo
                     comanda_id_para_listar = $('#id').val();
                }
                // ***** FIM: ATUALIZA ID DA COMANDA *****

                // Mostra feedback Swal
                 Swal.fire({
                     position: 'center',
                     icon: 'success',
                     title: 'Sucesso!',
                     text: response.message || 'Produto adicionado com sucesso!', // Mensagem do PHP
                     showConfirmButton: false,
                     timer: 1500 // Timer mais curto
                 });

                // --- ATUALIZA A LISTA DE PRODUTOS ---
                // Você precisa ter uma função separada para listar produtos
                console.log("Chamando listarProdutos com ID:", comanda_id_para_listar);
                if(typeof listarProdutos === 'function') {
                    listarProdutos(comanda_id_para_listar); // <<-- CHAMA A FUNÇÃO DE LISTAR PRODUTOS
                } else {
                     console.warn("Função listarProdutos não está definida! A lista de produtos não será atualizada.");
                     // Se a mesma função lista serviços E produtos, chame listarServicos
                     // if(typeof listarServicos === 'function') { listarServicos(comanda_id_para_listar); }
                }

                // --- ATUALIZA TOTAIS ---
                 console.log("Chamando calcular()...");
                 if(typeof calcular === 'function') {
                    calcular();
                } else {
                     console.warn("Função calcular() não definida!");
                }

                // Limpa campos do formulário de produto
                $("#quantidade").val('1');
                $('#produto').val('').trigger('change.select2');
                $('#funcionario2').val('0').trigger('change.select2'); // Reseta funcionário do produto

            } else {
                // Se response.success for false
                Swal.fire('Erro!', response.message || 'Não foi possível adicionar o produto.', 'error');
            }
        },
        error: function(xhr, status, error) {
            // Erro de comunicação ou erro fatal no PHP
            Swal.fire('Erro Crítico!', 'Falha na comunicação ao adicionar produto. Verifique o console (F12).', 'error');
            console.error("Erro AJAX inserirProduto:", status, error, xhr.responseText);
        },
        complete: function() {
             // Reabilita o botão
             if($botaoOriginal.length){ $botaoOriginal.prop('disabled', false).html('<i class="fa fa-plus"></i> Inserir'); }
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

        Swal.fire({
                    title: 'Tem Certeza?',
                    text: "Deseja realmente fechar essa comanda?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, fechar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Fechando...',
                            text: 'Aguarde...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

        $.ajax({
            url: 'paginas/' + pag + "/fechar_comanda.php",
            method: 'POST',
            data: { id, valor, valor_restante, data_pgto, data_pgto_restante, pgto_restante, pgto, cliente },
            dataType: "text",
            success: function(result) {
                Swal.close();
                if (result.trim() === 'Fechado com Sucesso') {
                    Swal.fire('Fechado!', result || 'Comanda fechada.', 'success');
                    $('#btn-fechar').click();
                                    
                    $('#data_pgto').val('<?php echo $data_hoje; ?>');
                    $('#valor_serv_agd_restante').val('');
                    $('#data_pgto_restante').val('');
                    $('#valor_serv').val('');
                    $('#pgto_restante').val('').trigger('change'); //Limpa select2
                    if (typeof listar === 'function') listar();
                    setTimeout(function() {                        
                        location.reload();
                    }, 1500);
                } else {
                    $("#mensagem").text(result);
                }
            },
            error: function(xhr, status, error) {
                        Swal.close();
                        Swal.fire('Erro Crítico!', 'Falha na comunicação com o servidor.', 'error');
                        console.error("Erro AJAX:", status, error, xhr.responseText);
                    }
                });
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

                if (mensagem.trim() == "Salvo com Sucesso") {
                    // var salvar = $('#salvar_comanda').val();

                    // if (salvar == 'Sim') {
                    //     $("#id").val(msg[1]);
                    //     fecharComanda();
                    // }
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
