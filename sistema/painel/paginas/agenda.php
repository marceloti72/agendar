<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'agenda';
$data_atual = date('Y-m-d');
?>

<style>
    /* Tooltip Styling */
    .tooltip-inner {
        background-color: #48D1CC; /* Turquoise */
        color: #000; /* Black text */
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border: none;
    }

    .modal-header-custom {
        background-color: #f7f9fc;
        border-bottom: 1px solid #e1e4e8;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        padding: 1.5rem;
    }

    .modal-title {
        color: #333;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .modal-icon {
        color: #007bff;
        font-size: 1.8rem;
    }

    .modal-body {
        padding: 2rem;
    }

    .form-group label {
        font-weight: 500;
        color: #495057;
    }

    .form-control, .sel3, .sel4 {
        border-radius: 8px !important;
    }

    .btn-primary {
        background-color: #28a745;
        border-color: #28a745;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        background-color: #218838;
        border-color: #1e7e34;
        transform: translateY(-2px);
    }

    .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: #fff;
        transform: translateY(-2px);
    }

    /* Calendar Styling */
    .monthly-calendar {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .monthly-header {
        background-color: #f0f2f5;
        color: #333;
        padding: 15px;
        border-radius: 8px 8px 0 0;
    }

    .monthly-header-title {
        color: #444;
    }

    .monthly-prev,
    .monthly-next {
        color: #292929;
    }

    .monthly-day-header {
        background-color: #fafafa;
        color: #555;
        font-weight: bold;
    }

    .monthly-day-cell {
        background-color: #ffffff;
        border-right: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
    }

    .monthly-day-cell.monthly-day-unhighlighted {
        background-color: #f8f8f8;
        color: #b0b0b0;
    }

    .monthly-day-cell.monthly-today {
        background-color: #e6f7ff;
        border: 2px solid #a8e3ff;
        border-radius: 5px;
    }

    .monthly-event-list {
        background-color: #f1f8ff;
        border-left: 3px solid #007bff;
        color: #333;
        padding: 5px 10px;
        margin: 2px 0;
        border-radius: 4px;
    }

    /* Button Styling */
    .btn-novo {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        background-color: #28a745;
        border-color: #28a745;
        color: #fff;
        transition: all 0.2s ease;
        padding: 10px 20px;
        font-size: 1rem;
    }

    .btn-novo:hover {
        background-color: #218838;
        transform: translateY(-2px);
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .btn-novo {
            width: 100%;
            font-size: 14px;
            padding: 8px;
        }
    }
</style>

<div class="row">
    <div class="col-md-3">
        <button style="margin-bottom:10px;" data-toggle="modal" data-target="#modalForm" type="button" class="btn btn-novo">
            <i class="fa fa-plus" aria-hidden="true"></i> Novo Agendamento
        </button>
    </div>
</div>
<input type="hidden" name="data_agenda" id="data_agenda" value="<?php echo date('Y-m-d') ?>">

<div class="row" style="margin-top: 15px">
    <div class="col-md-4 agile-calendar">
        <div class="calendar-widget monthly-calendar">
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

    <div class="col-xs-12 col-md-8 bs-example widget-shadow" style="padding:10px 5px; margin-top: 0px;" id="listar">
    </div>
</div>

<!-- Modal Agendamento -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h4 class="modal-title" id="titulo_inserir">
                    <i class="fas fa-calendar-alt modal-icon"></i> Novo Agendamento
                </h4>
                <button id="btn-fechar" type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="form-text">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Cliente</label>
                                <select class="form-control sel3" id="cliente" name="cliente" style="width:100%;" required>
                                    <?php 
                                    $query = $pdo->query("SELECT * FROM clientes WHERE id_conta = '$id_conta' ORDER BY nome ASC");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    $total_reg = @count($res);
                                    if ($total_reg > 0) {
                                        foreach ($res as $row) {
                                            echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nome']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Serviço</label>
                                <select class="form-control sel3" id="servico" name="servico" style="width:100%;" required>
                                    <?php 
                                    $query = $pdo->query("SELECT * FROM servicos_func WHERE funcionario = '$id_usuario' AND id_conta = '$id_conta'");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    if (@count($res) > 0) {
                                        foreach ($res as $row) {
                                            $serv = $row['servico'];
                                            $query2 = $pdo->query("SELECT * FROM servicos WHERE id = '$serv' AND ativo = 'Sim' AND id_conta = '$id_conta'");
                                            $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
                                            if (@count($res2) > 0) {
                                                echo '<option value="' . $serv . '">' . htmlspecialchars($res2[0]['nome']) . '</option>';
                                            }
                                        }
                                    } else {
                                        echo '<option value="">Nenhum Serviço</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
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
                                <div id="listar-horarios">
                                    <small>Selecionar Horário</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Observações <small>(Máx 100 Caracteres)</small></label>
                                <input maxlength="100" type="text" class="form-control" name="obs" id="obs">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="id_funcionario" id="id_funcionario">
                    <small><div id="mensagem-ag" align="center" class="mt-3"></div></small>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa-regular fa-floppy-disk"></i> Salvar</button>
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i class="fas fa-times-circle"></i> Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Serviço -->
<div class="modal fade" id="modalServico" tabindex="-1" role="dialog" aria-labelledby="modalServicoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h4 class="modal-title">Serviço: <span id="titulo_servico"></span></h4>
                <button id="btn-fechar-servico" type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                                    $total_reg = @count($res);
                                    if ($total_reg > 0) {
                                        foreach ($res as $row) {
                                            echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nome']) . '</option>';
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
                                    $total_reg = @count($res);
                                    if ($total_reg > 0) {
                                        foreach ($res as $row) {
                                            echo '<option value="' . $row['nome'] . '">' . htmlspecialchars($row['nome']) . '</option>';
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
                                <label>Valor Restante <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Caso o cliente efetue o pagamento com duas formas diferentes de pagamento. Ex: Pix e Cartão."></i></label>
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
                                    $total_reg = @count($res);
                                    if ($total_reg > 0) {
                                        foreach ($res as $row) {
                                            echo '<option value="' . $row['nome'] . '">' . htmlspecialchars($row['nome']) . '</option>';
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
                                <input maxlength="1000" type="text" class="form="form-control" name="obs" id="obs2">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="id_agd" id="id_agd">
                    <input type="hidden" name="cliente_agd" id="cliente_agd">
                    <input type="hidden" name="servico_agd" id="servico_agd">
                    <input type="hidden" name="descricao_serv_agd" id="descricao_serv_agd">
                    <small><div id="mensagem-servico" align="center" class="mt-3"></div></small>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Concluir</button>
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i class="fas fa-times-circle"></i> Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">var pag = "<?=$pag?>";</script>
<script src="js/ajax.js"></script>

<!-- Calendar -->
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

        switch (window.location.protocol) {
            case 'http:':
            case 'https:':
                break;
            case 'file:':
                alert('Just a heads-up, events will not work when run locally.');
        }
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        listarHorarios();
        $('.sel3').select2({
            dropdownParent: $('#modalForm')
        });
        $('.sel4').select2({
            dropdownParent: $('#modalServico')
        });
    });
</script>

<script>
    $("#form-text").submit(function(event) {
        $('#mensagem-ag').text('Carregando...');
        event.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: 'paginas/' + pag + "/inserir.php",
            type: 'POST',
            data: formData,
            success: function(mensagem) {
                $('#mensagem-ag').text('');
                $('#mensagem-ag').removeClass();
                if (mensagem.trim() == "Salvo com Sucesso") {
                    $('#btn-fechar').click();
                    listar();
                    listarHorarios();
                } else {
                    $('#mensagem-ag').addClass('text-danger');
                    $('#mensagem-ag').text(mensagem);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
        });
    });
</script>

<script type="text/javascript">
    function listar() {
        var data = $("#data_agenda").val();
        $("#data-modal").val(data);
        $.ajax({
            url: 'paginas/' + pag + "/listar.php",
            method: 'POST',
            data: {data},
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

    function mudarData() {
        var data = $('#data-modal').val();
        $('#data_agenda').val(data).change();
        listar();
        listarHorarios();
    }

    function listarHorarios() {
        var data = $('#data_agenda').val();
        $.ajax({
            url: 'paginas/' + pag + "/listar-horarios.php",
            method: 'POST',
            data: {data},
            dataType: "text",
            success: function(result) {
                $("#listar-horarios").html(result);
            }
        });
    }

    $("#form-servico").submit(function(event) {
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
                    $('#mensagem-servico').addClass('text-danger');
                    $('#mensagem-servico').text(mensagem);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
        });
    });
</script>

<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>