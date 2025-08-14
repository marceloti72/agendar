<?php
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'agendamentos';
$data_atual = date('Y-m-d');

// Redireciona se o usuário não for administrador
if (@$_SESSION['nivel_usuario'] != 'administrador') {
    echo "<script>window.location='agenda.php'</script>";
    exit();
}
?>

<style>
    /* Estilos Gerais */
    .btn-primary.novo {
        background-color: #007bff;
        border-color: #007bff;
        transition: all 0.3s ease;
    }

    .btn-primary.novo:hover {
        background-color: #0056b3;
        border-color: #004085;
    }

    /* Cards de Agendamento */
    .agendamento-card {
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
        background-color: #ffffff;
        border-left: 5px solid;
        transition: transform 0.2s;
    }

    .agendamento-card:hover {
        transform: translateY(-5px);
    }
    
    .agendamento-card .card-body {
        padding: 15px;
    }

    .agendamento-card h5 {
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    .agendamento-card p {
        margin: 0;
        font-size: 14px;
        color: #666;
    }

    .agendamento-card .actions {
        margin-top: 10px;
        display: flex;
        gap: 10px;
    }
    
    .agendamento-card .actions .btn {
        font-size: 12px;
        padding: 5px 10px;
    }

    /* Cores dos Status */
    .status-agendado {
        border-left-color: #007bff; /* Azul para Agendado */
    }

    .status-concluido {
        border-left-color: #28a745; /* Verde para Concluído */
    }

    .status-cancelado {
        border-left-color: #dc3545; /* Vermelho para Cancelado */
    }

    /* Tooltip */
    .tooltip-inner {
        background-color: #48D1CC; /* Amarelo (como no original) */
        color: #000;
        font-weight: bold;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .novo {
            width: 100%;
            margin-bottom: 15px;
        }
        .agendamento-card {
            margin-bottom: 10px;
        }
    }
</style>

<div class="row">
    <div class="col-md-3">
        <button style="margin-bottom:10px; border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)" onclick="inserir()" type="button" class="btn btn-primary novo"><i class="fa fa-plus" aria-hidden="true"></i> Novo Agendamento</button>
    </div>
    
    <div class="col-md-3">
        <div class="form-group">
            <select class="form-control sel2" id="funcionario" name="funcionario" style="width:100%;" onchange="mudarFuncionario()">
                <option value="">Selecione um Profissional</option>
                <?php
                $query = $pdo->query("SELECT * FROM usuarios WHERE atendimento = 'Sim' AND id_conta = '$id_conta' ORDER BY nome ASC");
                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                foreach ($res as $item) {
                    echo '<option value="' . $item['id'] . '">' . htmlspecialchars($item['nome']) . '</option>';
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

    <div class="col-xs-12 col-md-8" style="padding:10px 5px; margin-top: 0px;">
        <div class="row" id="listar">
            </div>
    </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h4 class="modal-title" id="titulo_inserir"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
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
                                    $query = $pdo->query("SELECT * FROM clientes WHERE id_conta = '$id_conta' ORDER BY nome ASC");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($res as $item) {
                                        echo '<option value="' . $item['id'] . '">' . htmlspecialchars($item['nome']) . '</option>';
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
                                    $query = $pdo->query("SELECT * FROM usuarios WHERE atendimento = 'Sim' AND id_conta = '$id_conta' ORDER BY nome ASC");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($res as $item) {
                                        echo '<option value="' . $item['id'] . '">' . htmlspecialchars($item['nome']) . '</option>';
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Data</label>
                                <input type="date" class="form-control" name="data" id="data-modal" onchange="mudarData()">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Horários Disponíveis</label>
                                <div id="listar-horarios" class="d-flex flex-wrap gap-2">
                                    <small>Selecione um Funcionário e uma Data</small>
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

<div class="modal fade" id="modalServico" tabindex="-1" role="dialog" aria-labelledby="modalServicoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h4 class="modal-title">Concluir Serviço: <span id="titulo_servico"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
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
                                    $query = $pdo->query("SELECT * FROM usuarios WHERE atendimento = 'Sim' AND id_conta = '$id_conta' ORDER BY nome ASC");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($res as $item) {
                                        echo '<option value="' . $item['id'] . '">' . htmlspecialchars($item['nome']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Valor (Falta Pagar)</label>
                                <input type="text" class="form-control" name="valor_serv_agd" id="valor_serv_agd">
                            </div>
                        </div>
                        <div class="col-md-4">
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
                                    $query = $pdo->query("SELECT * FROM formas_pgto WHERE id_conta = '$id_conta'");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($res as $item) {
                                        echo '<option value="' . $item['nome'] . '">' . htmlspecialchars($item['nome']) . '</option>';
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
                                <input type="date" class="form-control" name="data_pgto_restante" id="data_pgto_restante">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Forma PGTO Restante</label>
                                <select class="form-control" id="pgto_restante" name="pgto_restante" style="width:100%;">
                                    <option value="">Selecionar Pgto</option>
                                    <?php
                                    $query = $pdo->query("SELECT * FROM formas_pgto WHERE id_conta = '$id_conta'");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($res as $item) {
                                        echo '<option value="' . $item['nome'] . '">' . htmlspecialchars($item['nome']) . '</option>';
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
                    <button type="submit" class="btn btn-success">Concluir Agendamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">var pag = "<?=$pag?>";</script>
<script src="js/ajax.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>

<script type="text/javascript" src="js/monthly.js"></script>
<script type="text/javascript">
    $(window).on('load', function() {
        $('#mycalendar').monthly({ mode: 'event' });
        listar(); // Adicionei esta chamada para carregar os agendamentos ao carregar a página
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.sel3').select2({ dropdownParent: $('#modalForm') });
        $('.sel2').select2({});
        $('.sel4').select2({ dropdownParent: $('#modalServico') });
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<script>
$("#form-text").submit(function (event) {
    event.preventDefault();
    const form = this;
    const formData = new FormData(form);
    
    // Feedback visual de carregamento
    Swal.fire({
        title: 'Salvando...',
        text: 'Por favor, aguarde.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: 'paginas/' + pag + "/inserir.php",
        type: 'POST',
        data: formData,
        dataType: "json",
        cache: false,
        contentType: false,
        processData: false,
        success: function (response) {
            Swal.close();
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: response.message || "Salvo com Sucesso!",
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#modalForm').modal('hide');
                    if (typeof listar === 'function') {
                        listar();
                    }
                    if (typeof listarHorarios === 'function') {
                        listarHorarios();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: response.message || "Ocorreu um erro ao salvar.",
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function (xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro Crítico!',
                text: 'Falha na comunicação com o servidor. Verifique o console (F12) para detalhes.',
                confirmButtonText: 'OK'
            });
            console.error("Erro AJAX form-text:", status, error, xhr.responseText);
        }
    });
});
</script>

<script type="text/javascript">
    function listar(){
        var funcionario = $('#funcionario_modal').val();
        var data = $("#data_agenda").val();
        $("#data-modal").val(data);

        $.ajax({
            url: 'paginas/' + pag + "/listar.php",
            method: 'POST',
            data: {data, funcionario},
            dataType: "text",
            success:function(result){
                $("#listar").html(result);
            }
        });
    }

    function limparCampos(){
        $('#id').val('');
        $('#obs').val('');
        $('#hora').val('');
        $('#data-modal').val('<?=$data_atual?>');
        $('#titulo_inserir').text('Novo Agendamento');
        $('#btn-fechar').show();
        $('#id_agd').val('');
        $('#cliente_agd').val('');
        $('#servico_agd').val('');
        $('#descricao_serv_agd').val('');
    }

    function mudarFuncionario(){
        var funcionario = $('#funcionario').val();
        $('#id_funcionario').val(funcionario);
        $('#funcionario_modal').val(funcionario).trigger('change');
        listar();
        listarHorarios();
        listarServicos(funcionario);
    }

    function mudarFuncionarioModal(){
        var func = $('#funcionario_modal').val();
        listar();
        listarHorarios();
        listarServicos(func);
    }

    function mudarData(){
        var data = $('#data-modal').val();
        $('#data_agenda').val(data);
        listar();
        listarHorarios();
    }

    function listarHorarios(){
        var funcionario = $('#funcionario_modal').val();
        var data = $('#data_agenda').val();
        
        if (!funcionario) {
            $("#listar-horarios").html('<small>Selecione um Funcionário para ver os horários</small>');
            return;
        }

        $.ajax({
            url: 'paginas/' + pag + "/listar-horarios.php",
            method: 'POST',
            data: {funcionario, data},
            dataType: "text",
            success:function(result){
                $("#listar-horarios").html(result);
            }
        });
    }
    
    function listarServicos(func){
        $.ajax({
            url: 'paginas/' + pag + "/listar-servicos.php",
            method: 'POST',
            data: {func},
            dataType: "text",
            success:function(result){
                $("#servico").html(result);
            }
        });
    }
</script>