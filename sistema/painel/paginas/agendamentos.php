<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'agendamentos';
$data_atual = date('Y-m-d');

//verificar se ele tem a permissão de estar nessa página
if(@$agendamentos == 'ocultar'){
    echo "<script>window.location='../index.php'</script>";
    exit();
}
?>

<style>
    .tooltip-inner {
        background-color: #48D1CC;
        color: #000;
    }
    .novo {
        background-color: #e99f35;
    }
    @media (max-width: 768px) {
        .novo {
            display: flex;
            width: 100%;
            height: 30px;
            margin-bottom: 10px;
            font-size: 14px;
            align-items: center;
            justify-content: center;
        }
    }
    /* Payment Modal Styles */
    .payment-option-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        padding: 10px;
        background-color: #4682B4;
        color: white;
        border-radius: 5px;
        width: 100%;
        text-align: center;
        font-size: 14px;
    }
    .payment-option-btn img {
        width: 24px;
        height: 24px;
        margin-right: 10px;
    }
    .payment-option-btn:hover {
        background-color: #357ABD;
        color: white;
    }
    .close-btn {
        background-color: #dc3545;
        color: white;
        border-radius: 5px;
        padding: 10px;
        width: 100%;
        text-align: center;
    }
    .close-btn:hover {
        background-color: #c82333;
        color: white;
    }
    .total-text {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 20px;
        text-align: center;
    }
    .error-text {
        color: #dc3545;
        text-align: center;
        margin-top: 10px;
    }
    .qr-code-container {
        text-align: center;
        margin-bottom: 20px;
    }
</style>

<div class="row">
    <div class="col-md-3">
        <button style="margin-bottom:10px; border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)" onclick="inserir()" type="button" class="novo"><i class="fa fa-plus" aria-hidden="true"></i> Novo Agendamento</button>
    </div>

    <div class="col-md-3">
        <div class="form-group">            
            <select class="form-control sel2" id="funcionario" name="funcionario" style="width:100%;" onchange="mudarFuncionario()"> 
                <option value="">Selecione um Profissional</option>
                <?php 
                $query = $pdo->query("SELECT * FROM usuarios where atendimento = 'Sim' and id_conta = '$id_conta' ORDER BY id desc");
                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                $total_reg = @count($res);
                if($total_reg > 0){
                    for($i=0; $i < $total_reg; $i++){
                        echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
                    }
                }
                ?>
            </select>   
        </div>    
    </div>
</div>
<input type="hidden" name="data_agenda" id="data_agenda" value="<?php echo date('Y-m-d') ?>"> 

<div class="row" style="margin-top: 15px">
    <div class="col-md-4 agile-calendar">
        <div class="calendar-widget">
            <div class="agile-calendar-grid">
                <div class="page">
                    <div class="w3l-calendar-left">
                        <div class="calendar-heading"></div>
                        <div class="monthly" id="mycalendar"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xs-12 col-md-8 bs-example widget-shadow" style="padding:10px 5px; margin-top: 0px;" id="listar"></div>
</div>

<!-- Modal Agendamento -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h4 class="modal-title" id="titulo_inserir"></h4>
                <button id="btn-fechar" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="form-text">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">                        
                            <div class="form-group"> 
                                <label>Cliente</label> 
                                <select class="form-control sel3" id="cliente" name="cliente" style="width:100%;" required> 
                                    <?php 
                                    $query = $pdo->query("SELECT * FROM clientes where id_conta = '$id_conta' ORDER BY nome asc");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    $total_reg = @count($res);
                                    if($total_reg > 0){
                                        for($i=0; $i < $total_reg; $i++){
                                            echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
                                        }
                                    }
                                    ?>
                                </select>    
                            </div>                        
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Funcionário</label>            
                                <select class="form-control sel2" id="funcionario_modal" name="funcionario" style="width:100%;" onchange="mudarFuncionarioModal()"> 
                                    <option value="">Selecione um Funcionário</option>
                                    <?php 
                                    $query = $pdo->query("SELECT * FROM usuarios where atendimento = 'Sim' and id_conta = '$id_conta' ORDER BY id desc");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    $total_reg = @count($res);
                                    if($total_reg > 0){
                                        for($i=0; $i < $total_reg; $i++){
                                            echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
                                        }
                                    }
                                    ?>
                                </select>   
                            </div>    
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">                        
                            <div class="form-group"> 
                                <label>Serviço</label> 
                                <select class="form-control sel3" id="servico" name="servico" style="width:100%;" required></select>    
                            </div>                        
                        </div>

                        <div class="col-md-4" id="nasc">                        
                            <div class="form-group"> 
                                <label>Data</label> 
                                <input type="date" class="form-control" name="data" id="data-modal" onchange="mudarData()"> 
                            </div>                        
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-12" id="nasc">                        
                            <div class="form-group">                                
                                <div id="listar-horarios">
                                    <small>Selecionar Funcionário</small>
                                </div>
                            </div>                        
                        </div>                    
                    </div>
                    <hr>

                    <div class="col-md-12">                        
                        <div class="form-group"> 
                            <label>OBS <small>(Máx 100 Caracteres)</small></label> 
                            <input maxlength="100" type="text" class="form-control" name="obs" id="obs">
                        </div>                        
                    </div>

                    <br>
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="id_funcionario" id="id_funcionario"> 
                    <small><div id="mensagem" align="center" class="mt-3"></div></small>                    
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Serviço -->
<div class="modal fade" id="modalServico" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h4 class="modal-title">Serviço: <span id="titulo_servico"></span></h4>
                <button id="btn-fechar-servico" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="form-servico">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">                        
                            <div class="form-group"> 
                                <label>Funcionário</label> 
                                <select class="form-control sel4" id="funcionario_agd" name="funcionario_agd" style="width:100%;" required> 
                                    <?php 
                                    $query = $pdo->query("SELECT * FROM usuarios where atendimento = 'Sim' and id_conta = '$id_conta' ORDER BY nome asc");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    $total_reg = @count($res);
                                    if($total_reg > 0){
                                        for($i=0; $i < $total_reg; $i++){
                                            echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
                                        }
                                    }
                                    ?>
                                </select>    
                            </div>                        
                        </div>                    
                    </div>

                    <div class="row">
                        <div class="col-md-4" id="nasc">                        
                            <div class="form-group"> 
                                <label>Valor (Falta Pagar)</label> 
                                <input type="text" class="form-control" name="valor_serv_agd" id="valor_serv_agd"> 
                            </div>                        
                        </div>

                        <div class="col-md-4" id="nasc">                        
                            <div class="form-group"> 
                                <label>Data PGTO</label> 
                                <input type="date" class="form-control" name="data_pgto" id="data_pgto" value="<?php echo $data_atual ?>"> 
                            </div>                        
                        </div>

                        <div class="col-md-4">                        
                            <div class="form-group"> 
                                <label>Forma PGTO</label> 
                                <select class="form-control" id="pgto" name="pgto" style="width:100%;" required> 
                                    <?php 
                                    $query = $pdo->query("SELECT * FROM formas_pgto where id_conta = '$id_conta'");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    $total_reg = @count($res);
                                    if($total_reg > 0){
                                        for($i=0; $i < $total_reg; $i++){
                                            echo '<option value="'.$res[$i]['nome'].'">'.$res[$i]['nome'].'</option>';
                                        }
                                    }
                                    ?>
                                </select>    
                            </div>                        
                        </div>    
                    </div>

                    <div class="row">
                        <div class="col-md-4">                        
                            <div class="form-group"> 
                                <label>Valor Restante <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Caso o cliente efetue o pagamento com duas formas diferentes. Ex: Pix e Cartão."></i></label> 
                                <input type="text" class="form-control" name="valor_serv_agd_restante" id="valor_serv_agd_restante" placeholder="Mais de uma forma PGTO"> 
                            </div>                        
                        </div>

                        <div class="col-md-4">                        
                            <div class="form-group"> 
                                <label>Data PGTO Restante</label> 
                                <input type="date" class="form-control" name="data_pgto_restante" id="data_pgto_restante" value=""> 
                            </div>                        
                        </div>

                        <div class="col-md-4">                        
                            <div class="form-group"> 
                                <label>Forma PGTO Restante</label> 
                                <select class="form-control" id="pgto_restante" name="pgto_restante" style="width:100;"> 
                                    <option value="">Selecionar Pgto</option>
                                    <?php 
                                    $query = $pdo->query("SELECT * FROM formas_pgto where id_conta = '$id_conta'");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    $total_reg = @count($res);
                                    if($total_reg > 0){
                                        for($i=0; $i < $total_reg; $i++){
                                            echo '<option value="'.$res[$i]['nome'].'">'.$res[$i]['nome'].'</option>';
                                        }
                                    }
                                    ?>
                                </select>    
                            </div>                        
                        </div>    
                    </div>

                    <div class="row">
                        <div class="col-md-12">                        
                            <div class="form-group"> 
                                <label>Observações</label> 
                                <input maxlength="1000" type="text" class="form-control" name="obs" id="obs2"> 
                            </div>                        
                        </div>
                    </div>

                    <br>
                    <input type="hidden" name="id_agd" id="id_agd"> 
                    <input type="hidden" name="cliente_agd" id="cliente_agd"> 
                    <input type="hidden" name="servico_agd" id="servico_agd">
                    <input type="hidden" name="descricao_serv_agd" id="descricao_serv_agd">
                    <small><div id="mensagem-servico" align="center" class="mt-3"></div></small>                    
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Concluir</button>
                    <button type="button" class="btn btn-success" id="btn-fechar-comanda">Fechar Comanda</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pagamento -->
<div class="modal fade" id="modalPagamento" tabindex="-1" role="dialog" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h4 class="modal-title" id="modalPagamentoLabel">Fechar Comanda</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="total-text">Total a Pagar: R$ <span id="total-pagar">0.00</span></div>
                <div id="qr-code-container" class="qr-code-container" style="display: none;">
                    <p>Escaneie este QR Code com seu app de pagamento:</p>
                    <div id="qr-code"></div>
                    <button class="payment-option-btn" id="btn-whatsapp">Enviar Código ao Cliente</button>
                </div>
                <div id="payment-options">
                    <button class="payment-option-btn" id="btn-criar-qr">
                        <img src="https://logospng.org/download/mercado-pago/logo-mercado-pago-icon-256.png" alt="Mercado Pago"> Criar QR Code
                    </button>
                    <button class="payment-option-btn" id="btn-enviar-link">
                        <img src="https://logospng.org/download/mercado-pago/logo-mercado-pago-icon-256.png" alt="Mercado Pago"> Enviar Link de Pagamento
                    </button>
                    <button class="payment-option-btn" id="btn-confirmar-pagamento">Confirmar Pagamento</button>
                </div>
                <small><div id="mensagem-pagamento" class="error-text" align="center"></div></small>
            </div>
            <div class="modal-footer">
                <button type="button" class="close-btn" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">var pag = "<?=$pag?>"</script>
<script src="js/ajax.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script type="text/javascript" src="js/monthly.js"></script>

<script type="text/javascript">
    $(window).load(function() {
        $('#mycalendar').monthly({
            mode: 'event',
        });

        $('#mycalendar2').monthly({
            mode: 'picker',
            target: '#mytarget',
            setWidth: '250px',
            startHidden: true,
            showTrigger: '#mytarget',
            stylePast: true,
            disablePast: true
        });

        switch(window.location.protocol) {
            case 'http:':
            case 'https:':
                break;
            case 'file:':
                alert('Just a heads-up, events will not work when run locally.');
        }
    });

    $(document).ready(function() {
        $('.sel3').select2({
            dropdownParent: $('#modalForm')
        });
        $('.sel2').select2();
        $('.sel4').select2({
            dropdownParent: $('#modalServico')
        });
        $('[data-toggle="tooltip"]').tooltip();

        // Open payment modal
        $('#btn-fechar-comanda').click(function() {
            $('#modalServico').modal('hide');
            var valor = parseFloat($('#valor_serv_agd').val()) || 0;
            var valorRestante = parseFloat($('#valor_serv_agd_restante').val()) || 0;
            var total = valor + valorRestante;
            $('#total-pagar').text(total.toFixed(2));
            $('#qr-code-container').hide();
            $('#payment-options').show();
            $('#mensagem-pagamento').text('');
            $('#modalPagamento').modal('show');
        });

        // Handle QR Code generation
        $('#btn-criar-qr').click(function() {
            var id_agd = $('#id_agd').val();
            $.ajax({
                url: 'paginas/' + pag + '/gerar-qr.php',
                method: 'POST',
                data: { id_agd: id_agd },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#payment-options').hide();
                        $('#qr-code-container').show();
                        $('#qr-code').html('');
                        QRCode.toCanvas(document.getElementById('qr-code'), response.qr_code, { width: 200 }, function(error) {
                            if (error) {
                                $('#mensagem-pagamento').addClass('error-text').text('Erro ao gerar QR Code');
                            }
                        });
                        fecharComanda(id_agd);
                    } else {
                        $('#mensagem-pagamento').addClass('error-text').text(response.message);
                    }
                },
                error: function() {
                    $('#mensagem-pagamento').addClass('error-text').text('Erro ao conectar com o servidor');
                }
            });
        });

        // Handle Send Payment Link
        $('#btn-enviar-link').click(function() {
            var id_agd = $('#id_agd').val();
            $.ajax({
                url: 'paginas/' + pag + '/enviar-link.php',
                method: 'POST',
                data: { id_agd: id_agd },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Sucesso', 'Link de pagamento enviado com sucesso!', 'success');
                        fecharComanda(id_agd);
                    } else {
                        $('#mensagem-pagamento').addClass('error-text').text(response.message);
                    }
                },
                error: function() {
                    $('#mensagem-pagamento').addClass('error-text').text('Erro ao conectar com o servidor');
                }
            });
        });

        // Handle Confirm Payment
        $('#btn-confirmar-pagamento').click(function() {
            var id_agd = $('#id_agd').val();
            Swal.fire({
                title: 'Confirmar Pagamento',
                text: 'Deseja confirmar manualmente o pagamento desta comanda? Esta ação marcará a comanda como paga.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fecharComanda(id_agd);
                }
            });
        });

        // Handle WhatsApp Send
        $('#btn-whatsapp').click(function() {
            var id_agd = $('#id_agd').val();
            $.ajax({
                url: 'paginas/' + pag + '/enviar-whatsapp.php',
                method: 'POST',
                data: { id_agd: id_agd },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Sucesso', 'Código enviado ao cliente via WhatsApp!', 'success');
                    } else {
                        $('#mensagem-pagamento').addClass('error-text').text(response.message);
                    }
                },
                error: function() {
                    $('#mensagem-pagamento').addClass('error-text').text('Erro ao conectar com o servidor');
                }
            });
        });

        // Function to close comanda
        function fecharComanda(id_agd) {
            $.ajax({
                url: 'paginas/comanda/fechar_comanda.php',
                method: 'POST',
                data: { id_agd: id_agd },
                dataType: 'text',
                success: function(mensagem) {
                    if (mensagem.trim() === 'Salvo com Sucesso') {
                        Swal.fire('Sucesso', 'Comanda fechada com sucesso!', 'success');
                        $('#modalPagamento').modal('hide');
                        listar();
                    } else {
                        $('#mensagem-pagamento').addClass('error-text').text(mensagem);
                    }
                },
                error: function() {
                    $('#mensagem-pagamento').addClass('error-text').text('Erro ao conectar com o servidor');
                }
            });
        }
    });

    $("#form-text").submit(function() {
        $('#mensagem').text('Carregando...');
        event.preventDefault();
        
        var formData = new FormData(this);

        $.ajax({
            url: 'paginas/' + pag + "/inserir.php",
            type: 'POST',
            data: formData,
            success: function(mensagem) {
                $('#mensagem').text('');
                $('#mensagem').removeClass();
                if (mensagem.trim() == "Salvo com Sucesso") {                    
                    $('#btn-fechar').click();
                    listar();
                    listarHorarios();
                } else {
                    $('#mensagem').addClass('text-danger').text(mensagem);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
        });
    });

    function listar() {
        var funcionario = $('#funcionario_modal').val();
        var data = $("#data_agenda").val();    
        $("#data-modal").val(data);

        $.ajax({
            url: 'paginas/' + pag + "/listar.php",
            method: 'POST',
            data: {data, funcionario},
            dataType: "text",
            success: function(result) {
                $("#listar").html(result);
            }
        });
    }

    function limparCampos() {
        $('#id').val('');        
        $('#obs').val('');
        $('#hora').val('');                
        $('#data').val('<?=$data_atual?>');    
    }

    function mudarFuncionario() {
        var funcionario = $('#funcionario').val();
        $('#id_funcionario').val(funcionario);    
        $('#funcionario_modal').val(funcionario).change();
        listar();    
        listarHorarios();
        listarServicos(funcionario);
    }

    function mudarFuncionarioModal() {    
        var func = $('#funcionario_modal').val();    
        listar();    
        listarHorarios();
        listarServicos(func);
    }

    function mudarData() {
        var data = $('#data-modal').val();            
        $('#data_agenda').val(data).change();
        listar();    
        listarHorarios();
    }

    function listarHorarios() {
        var funcionario = $('#funcionario_modal').val();    
        var data = $('#data_agenda').val();    

        $.ajax({
            url: 'paginas/' + pag + "/listar-horarios.php",
            method: 'POST',
            data: {funcionario, data},
            dataType: "text",
            success: function(result) {    
                $("#listar-horarios").html(result);
            }
        });
    }

    $("#form-servico").submit(function() {
        event.preventDefault();
        
        var formData = new FormData(this);

        $.ajax({
            url: 'paginas/' + pag + "/inserir-servico.php",
            type: 'POST',
            data: formData,
            success: function(mensagem) {
                $('#mensagem-servico').text('');
                $('#mensagem-servico').removeClass();
                if (mensagem.trim() == "Salvo com Sucesso") {                    
                    $('#btn-fechar-servico').click();
                    listar();
                } else {
                    $('#mensagem-servico').addClass('text-danger').text(mensagem);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
        });
    });

    function listarServicos(func) {    
        var serv = $("#servico").val();
        
        $.ajax({
            url: 'paginas/' + pag + "/listar-servicos.php",
            method: 'POST',
            data: {func},
            dataType: "text",
            success: function(result) {
                $("#servico").html(result);
            }
        });
    }
</script>