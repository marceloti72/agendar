<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'agendamentos';
$data_atual = date('Y-m-d');

//verificar se ele tem a permissão de estar nessa página
// if(@$agendamentos == 'ocultar'){
//  echo "<script>window.location='../index.php'</script>";
//  exit();
// }
if(@$_SESSION['nivel_usuario'] != 'administrador'){
    echo "<script>window.location='agenda.php'</script>";
}
?>

<style>
    .tooltip-inner {
        background-color: #48D1CC; /* Amarelo */
        color: #000; /* Cor do texto */
    }    

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
        color: #ffffffff;
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
        padding: 0;
    }
    
    .modal-left-panel {
        background-color: #fff;
        padding-right: 0;
    }

    .modal-body-scroll {
        max-height: 70vh;
        overflow-y: auto;
    }

    .modal-right-panel {
        background-color: #e9ecef;
        border-left: 1px solid #e1e4e8;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .pagamento-container {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .pagamento-header {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #007bff;
        margin-bottom: 10px;
    }
    
    .pagamento-icon {
        height: 40px;
        filter: grayscale(100%) brightness(50%) sepia(100%) hue-rotate(180deg) saturate(200%);
    }

    .divider {
        margin: 20px 0;
        border-top: 1px dashed #929292ff;
    }
    
    .section-header {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title {
        font-weight: 600;
        color: #555;
        border-left: 6px solid #007bff;
        padding-left: 10px;
    }

    #nome_do_cliente_aqui {
        font-weight: 600;
        color: #555;
    }
    
    .item-list-container {
        margin-top: 10px;
        margin-bottom: 20px;
    }

    .form-group label {
        font-weight: 500;
        color: #495057;
    }
    
    .form-control {
        border-radius: 8px;
    }
    
    .sel2 {
        border-radius: 8px !important;
    }

    .btn-add {
        border-radius: 8px;
        height: 38px;
        width: 100%;
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .btn-add:hover {
        transform: translateY(-2px);
    }
    
    .valor-display, .total-display {
        font-size: 1.1em;
        font-weight: bold;
        background-color: #fff;
        border-color: #ced4da;
    }
    
    .total-display {
        color: #155724;
        background-color: #d4edda;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
        transition: all 0.2s ease;
    }
    
    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
        transform: translateY(-2px);
    }
    
    .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: #fff;
        transform: translateY(-2px);
    }

    .btn-lg {
        padding: 12px 20px;
    }

    /* Container principal do calendário */
    .monthly-calendar {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Título e navegação */
    .monthly-header {
        background-color: #f0f2f5;
        color: #333;
        padding: 15px;
        border-radius: 8px 8px 0 0;
    }

    /* Cor do texto do mês e ano */
    .monthly-header-title {
        color: #444;
    }

    /* Usando !important para forçar a cor */
    .monthly-prev,
    .monthly-next {
        color: #292929;
    }

    /* Dias da semana (cabeçalho) */
    .monthly-day-header {
        background-color: #fafafa;
        color: #555;
        font-weight: bold;
    }

    /* Células dos dias */
    .monthly-day-cell {
        background-color: #ffffff;
        border-right: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
    }

    /* Células de dias inativos (outros meses) */
    .monthly-day-cell.monthly-day-unhighlighted {
        background-color: #f8f8f8;
        color: #b0b0b0;
    }

    /* Dia atual */
    .monthly-day-cell.monthly-today {
        background-color: #e6f7ff;
        border: 2px solid #a8e3ff;
        border-radius: 5px;
    }

    /* Eventos */
    .monthly-event-list {
        background-color: #f1f8ff;
        border-left: 3px solid #007bff;
        color: #333;
        padding: 5px 10px;
        margin: 2px 0;
        border-radius: 4px;
    }

    /* Estilos do Modal de Pagamento */
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
        <button style="margin-bottom:10px; border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)" data-toggle="modal" data-target="#modalForm" type="button" class="btn novo"><i class="fa fa-plus" aria-hidden="true"></i> Novo Agendamento</button>
    </div>

    <div class="col-md-3">
        <div class="form-group">            
            <select class="form-control sel2" id="funcionario" name="funcionario" style="width:100%;" onchange="mudarFuncionario()"> 
                <option value="">Todos</option>
                <?php 
                $query = $pdo->query("SELECT * FROM usuarios where atendimento = 'Sim' and id_conta = '$id_conta' ORDER BY id desc");
                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                $total_reg = @count($res);
                if($total_reg > 0){
                    for($i=0; $i < $total_reg; $i++){
                        foreach ($res[$i] as $key => $value){}
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

<div class="modal fade" id="modalForm2" tabindex="-1" role="dialog" aria-labelledby="modalForm2Label" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h4 class="modal-title" id="titulo_comanda">
                    <i class="fas fa-cash-register modal-icon"></i>
                    Nova Comanda
                </h4>
                <button type="button" id="btn-fechar" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form_salvar">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 modal-left-panel">
                            <div class="modal-body-scroll p-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <h3 id="nome_do_cliente_aqui"></h3>
                                        </div>
                                    </div>
                                </div>

                                <hr class="divider">

                                <div class="section-header">
                                    <h5 class="section-title">Serviços</h5>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <select class="form-control sel2" id="servico" name="servico" style="width:100%;">
                                                <?php
                                                $query = $pdo->query("SELECT * FROM servicos where id_conta = '$id_conta' ORDER BY nome asc");
                                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($res as $item) {
                                                    echo '<option value="' . $item['id'] . '">' . htmlspecialchars($item['nome']) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <select class="form-control sel2" id="funcionario2" name="funcionario" style="width:100%;">
                                                <?php
                                                $query = $pdo->query("SELECT * FROM usuarios where atendimento = 'Sim' and id_conta = '$id_conta' ORDER BY nome asc");
                                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($res as $item) {
                                                    echo '<option value="' . $item['id'] . '">' . htmlspecialchars($item['nome']) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-success btn-add" onclick="inserirServico()"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="item-list-container" id="listar_servicos"></div>

                                <hr class="divider">                               

                                <div class="section-header">
                                    <h5 class="section-title">Produtos</h5>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <select class="form-control sel2" id="produto" name="produto" style="width:100%;" onchange="listarServicos2()">
                                                <?php
                                                $query = $pdo->query("SELECT * FROM produtos where estoque > 0 and id_conta = '$id_conta' ORDER BY nome asc");
                                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                foreach($res as $item){
                                                    echo '<option value="'.$item['id'].'">'.htmlspecialchars($item['nome']).'</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <input type="number" class="form-control" name="quantidade" id="quantidade" value="1" min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">                                
                                        <button type="button" class="btn btn-success btn-add" onclick="inserirProduto()"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="item-list-container" id="listar_produtos"></div>

                                <hr class="divider">

                                <div class="section-header">
                                    <h5 class="section-title">Descontos</h5>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Sinal (Valor Pago)</label>
                                            <input type="text" class="form-control text-right valor-display" id="valor_sinal" style="color: red" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Desconto Cupom</label>
                                            <input type="text" class="form-control text-right valor-display" id="valor_cupom" style="color: red" readonly>
                                        </div>
                                    </div>
                                </div>

                                <hr class="divider">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Observações</label>
                                        <input type="text" class="form-control" value="" name="obs" id="obs2" maxlength="1000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 modal-right-panel">
                            <div class="pagamento-container p-3">
                                <div class="pagamento-header">
                                    <img src="../../images/registradora.png" alt="Ícone Pagamento" class="pagamento-icon">
                                    <h4>PAGAMENTO</h4>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label><small>Total Serviços</small></label>
                                    <input type="text" class="form-control text-right valor-display" id="valor_servicos" readonly>
                                </div>
                                <div class="form-group">
                                    <label><small>Total Produtos</small></label>
                                    <input type="text" class="form-control text-right valor-display" id="valor_produtos" readonly>
                                </div>
                                <div class="form-group">
                                    <label><small>Total Descontos</small></label>
                                    <input type="text" class="form-control text-right valor-display" id="valor_descontos" style="color: red" readonly>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label><small>Total a Pagar</small></label>
                                    <input type="text" class="form-control text-right total-display" name="valor_total" id="valor_serv" readonly>
                                </div>

                                <div class="row mt-3">
                                    <!-- Campos comentados para pagamento e troco podem ser reativados se necessário -->
                                </div>

                                <div class="d-flex flex-column gap-2 mt-4">
                                    <a href="#" id="btn_fechar_comanda" class="btn btn-success btn-lg btn-block" onclick="abrirModalPagamento()">
                                        <i class="fas fa-check-circle"></i> Fechar Comanda
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary btn-block" data-dismiss="modal">
                                        <i class="fas fa-times-circle"></i> Sair
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="valor_servicos" id="valor_servicos_hidden">
                    <input type="hidden" name="valor_produtos" id="valor_produtos_hidden">
                    <input type="hidden" name="valor_descontos" id="valor_descontos_hidden">
                    <small><div id="mensagem" align="center" class="mt-2"></div></small>
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
                    <div class="col-md-8"></div>
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
                                            foreach ($res[$i] as $key => $value){}
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
                                            foreach ($res[$i] as $key => $value){}
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
                                <select class="form-control sel3" id="servico2" name="servico" style="width:100%;" required>                                     
                                </select>    
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

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>CUPOM</label>
                            <select class="form-control" name="cupom" id="cupom">
                                <option value="">Nenhum</option>
                                <?php
                                $data_atual = date('Y-m-d');
                                $query = $pdo->prepare("SELECT * FROM cupons WHERE id_conta = :id_conta AND data_validade >= :data_atual AND usos_atuais < max_usos ORDER BY codigo ASC");
                                $query->bindValue(':id_conta', $id_conta);
                                $query->bindValue(':data_atual', $data_atual);
                                $query->execute();
                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($res as $item) {
                                    $sufixo = ($item['tipo_desconto'] === 'porcentagem') ? '%' : '$';
                                    $exibicao = htmlspecialchars($item['codigo']) . ' (' . htmlspecialchars($item['valor']) . $sufixo . ')';
                                    echo '<option value="' . htmlspecialchars($item['codigo']) . '">' . $exibicao . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

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
                                            foreach ($res[$i] as $key => $value){}
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
                                            foreach ($res[$i] as $key => $value){}
                                                echo '<option value="'.$res[$i]['nome'].'">'.$res[$i]['nome'].'</option>';
                                        }
                                    }
                                    ?>
                                </select>    
                            </div>                        
                        </div>    
                    </div>

                    <div class="row">
                        <div class="col-md-4" id="">                        
                            <div class="form-group"> 
                                <label>Valor Restante <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Caso o cliente efetue o pagamento com duas formas diferentes. Ex: Pix e Cartão." style="color: blue;"></i></label> 
                                <input type="text" class="form-control" name="valor_serv_agd_restante" id="valor_serv_agd_restante" placeholder="Mais de uma forma PGTO"> 
                            </div>                        
                        </div>

                        <div class="col-md-4" id="">                        
                            <div class="form-group"> 
                                <label>Data PGTO Restante</label> 
                                <input type="date" class="form-control" name="data_pgto_restante" id="data_pgto_restante" value=""> 
                            </div>                        
                        </div>

                        <div class="col-md-4">                        
                            <div class="form-group"> 
                                <label>Forma PGTO Restante</label> 
                                <select class="form-control" id="pgto_restante" name="pgto_restante" style="width:100%;" > 
                                    <option value="">Selecionar Pgto</option>
                                    <?php 
                                    $query = $pdo->query("SELECT * FROM formas_pgto where id_conta = '$id_conta'");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    $total_reg = @count($res);
                                    if($total_reg > 0){
                                        for($i=0; $i < $total_reg; $i++){
                                            foreach ($res[$i] as $key => $value){}
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
                        <img src="../../../images/mercado-pago-logo.png" alt="Mercado Pago"> Criar QR Code
                    </button>
                    <button class="payment-option-btn" id="btn-enviar-link">
                        <img src="../../../images/mercado-pago-logo.png" alt="Mercado Pago"> Enviar Link de Pagamento
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

<script type="text/javascript">var pag = "<?=$pag?>";</script>
<script src="js/ajax.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script type="text/javascript" src="js/monthly.js"></script>
<script type="text/javascript">
    // Função global para abrir o modal de pagamento
    window.abrirModalPagamento = function() {
        console.log('Função abrirModalPagamento chamada');
        $('#modalForm2').modal('hide');
        var total = parseFloat($('#valor_serv').val()) || 0;
        $('#total-pagar').text(total.toFixed(2));
        $('#qr-code-container').hide();
        $('#payment-options').show();
        $('#mensagem-pagamento').text('');
        $('#modalPagamento').modal('show');
    };

    $(window).load(function() {
        $('#mycalendar').monthly({
            mode: 'event',
        });

        $('#mycalendar2').monthly({
            mode: 'picker',
            target: '#mytarget',
            setWidth: '150px',
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
        
        // Criar QR Code
        $('#btn-criar-qr').click(function() {
            var id = $('#id').val();
            $.ajax({
                url: 'paginas/' + pag + '/gerar-qr.php',
                method: 'POST',
                data: { id: id },
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
                        fecharComanda(id);
                    } else {
                        $('#mensagem-pagamento').addClass('error-text').text(response.message);
                    }
                },
                error: function() {
                    $('#mensagem-pagamento').addClass('error-text').text('Erro ao conectar com o servidor');
                }
            });
        });

        // Enviar Link de Pagamento
        $('#btn-enviar-link').click(function() {
            var id = $('#id').val();
            $.ajax({
                url: 'paginas/' + pag + '/enviar-link.php',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Sucesso', 'Link de pagamento enviado com sucesso!', 'success');
                        fecharComanda(id);
                    } else {
                        $('#mensagem-pagamento').addClass('error-text').text(response.message);
                    }
                },
                error: function() {
                    $('#mensagem-pagamento').addClass('error-text').text('Erro ao conectar com o servidor');
                }
            });
        });

        // Confirmar Pagamento
        $('#btn-confirmar-pagamento').click(function() {
            var id = $('#id').val();
            Swal.fire({
                title: 'Confirmar Pagamento',
                text: 'Deseja confirmar manualmente o pagamento desta comanda? Esta ação marcará a comanda como paga.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fecharComanda(id);
                }
            });
        });

        // Enviar Código via WhatsApp
        $('#btn-whatsapp').click(function() {
            var id = $('#id').val();
            $.ajax({
                url: 'paginas/' + pag + '/enviar-whatsapp.php',
                method: 'POST',
                data: { id: id },
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

        // Função para fechar comanda
        function fecharComanda(id) {
            var cliente = $("#cliente").val();
            var valor = $("#valor_serv").val();
            var valor_restante = $("#valor_serv_agd_restante").val();
            var data_pgto = $("#data_pgto").val();
            var data_pgto_restante = $("#data_pgto_restante").val();
            var pgto_restante = $("#pgto_restante").val();
            var pgto = $("#pgto").val();

            if (valor_restante > 0 && (!data_pgto_restante || !pgto_restante)) {
                Swal.fire('Atenção', 'Preencha a Data de Pagamento Restante e o tipo de Pagamento Restante', 'warning');
                return;
            }

            Swal.fire({
                title: 'Fechando...',
                text: 'Aguarde...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'paginas/comanda/fechar_comanda.php',
                method: 'POST',
                data: { id, valor, valor_restante, data_pgto, data_pgto_restante, pgto_restante, pgto, cliente },
                dataType: 'text',
                success: function(result) {
                    Swal.close();
                    if (result.trim() === 'Fechado com Sucesso') {
                        Swal.fire('Fechado!', 'Comanda fechada com sucesso.', 'success');
                        $('#modalPagamento').modal('hide');
                        $('#btn-fechar').click();
                        $('#data_pgto').val('<?php echo $data_atual; ?>');
                        $('#valor_serv_agd_restante').val('');
                        $('#data_pgto_restante').val('');
                        $('#valor_serv').val('');
                        $('#pgto_restante').val('').trigger('change');
                        if (typeof listar === 'function') listar();
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#mensagem-pagamento').addClass('error-text').text(result);
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    $('#mensagem-pagamento').addClass('error-text').text('Erro ao conectar com o servidor');
                    console.error("Erro AJAX:", status, error, xhr.responseText);
                }
            });
        }
    });

    $("#form-text").submit(function(event) {
        event.preventDefault();
        const form = this;
        const $mensagemDiv = $('#mensagem');
        const $submitButton = $(form).find('button[type="submit"]');
        $mensagemDiv.text('Carregando...').removeClass('text-danger text-success');
        if($submitButton.length) $submitButton.prop('disabled', true).append(' <i class="fas fa-spinner fa-spin"></i>');

        var formData = new FormData(form);

        $.ajax({
            url: 'paginas/' + pag + "/inserir.php",
            type: 'POST',
            data: formData,
            dataType: "json",
            cache: false,
            contentType: false,
            processData: false,
            success: function(response) {
                $mensagemDiv.text('');
                if (response.status === 'success') {
                    $mensagemDiv.addClass('text-success').text(response.message || "Salvo com Sucesso!");
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: response.message || "Salvo com Sucesso!",
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if ($('#btn-fechar').length) {
                            $('#btn-fechar').click();
                        } else if ($(form).closest('.modal').length) {
                            $(form).closest('.modal').modal('hide');
                        }
                        if (typeof listar === 'function') listar();
                        if (typeof listarHorarios === 'function') listarHorarios();
                    });
                } else {
                    $mensagemDiv.addClass('text-danger').text(response.message || 'Erro ao salvar.');
                    Swal.fire('Erro!', response.message || 'Erro ao salvar.', 'error');
                }
            },
            error: function(xhr, status, error) {
                $mensagemDiv.text('Erro de comunicação. Verifique o console.').addClass('text-danger');
                Swal.fire('Erro Crítico!', 'Falha na comunicação com o servidor. Verifique o console (F12).', 'error');
                console.error("Erro AJAX form-text:", status, error, xhr.responseText);
            },
            complete: function() {
                if($submitButton.length) $submitButton.prop('disabled', false).find('i.fa-spinner').remove();
            }
        });
    });

    function listar() {
        var funcionario = $('#funcionario').val();
        var data = $("#data_agenda").val();    
        $("#data-modal").val(data);
        
        $.ajax({
            url: 'paginas/agendamentos/listar.php',
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
        listar();
    }

    function mudarFuncionarioModal() {    
        var func = $('#funcionario_modal').val();    
        listarHorarios();
        listarServicos(func);
    }

    function mudarData() {
        var data = $('#data-modal').val();            
        $('#data_agenda').val(data).change();
        listar();
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

    $("#form-servico").submit(function(event) {
        event.preventDefault();
        var formData = new FormData(this);

        fetch('paginas/agendamentos/inserir-servico.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'Sucesso!',
                    text: data.message + (data.detail ? '\n\nDetalhe: ' + data.detail : ''),
                    icon: 'success',
                    confirmButtonText: 'Ok'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Erro!',
                    text: data.message + (data.detail ? '\n\nErro Técnico: ' + data.detail : ''),
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            Swal.fire({
                title: 'Erro de Conexão!',
                text: 'Não foi possível concluir a operação. Verifique sua conexão ou contate o suporte.',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        });
    });

    function listarServicos(func) {    
        var serv = $("#servico2").val();        
                
        $.ajax({
            url: 'paginas/' + pag + "/listar-servicos.php",
            method: 'POST',
            data: {func},
            dataType: "text",
            success: function(result) {
                $("#servico2").html(result);
            }
        });
    }

    function calcular() {
        setTimeout(function() {
            var produtos = parseFloat($('#valor_produtos').val() || 0);
            var servicos = parseFloat($('#valor_servicos').val() || 0);
            var descontos = parseFloat($('#valor_descontos').val() || 0);
            var total = (produtos + servicos) - descontos;        
            $('#valor_serv').val(total.toFixed(2));
        }, 500);
    }

    function inserirServico() {
        $("#mensagem").text('');
        var servico = $("#servico").val();
        var funcionario = $("#funcionario2").val();
        var cliente = $("#cliente").val();
        var id_atual_comanda = $("#id").val();

        console.log("Iniciando inserirServico. ID Comanda Atual:", id_atual_comanda, "Cliente:", cliente, "Serviço:", servico, "Funcionário:", funcionario);

        if (!cliente) {
            Swal.fire('Atenção!', 'Selecione um Cliente', 'warning');
            return;
        }
        if (!servico) {
            Swal.fire('Atenção!', 'Selecione um Serviço', 'warning');
            return;
        }
        if (!funcionario) {
            Swal.fire('Atenção!', 'Selecione um Profissional', 'warning');
            return;
        }

        var $botaoOriginal = $('#botao-inserir-servico');
        if($botaoOriginal.length) { $botaoOriginal.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Inserindo...'); }

        $.ajax({
            url: 'paginas/comanda/inserir_servico.php',
            method: 'POST',
            data: {
                servico: servico,
                funcionario: funcionario,
                cliente: cliente,
                id: id_atual_comanda
            },
            dataType: "json",
            success: function(response) {
                console.log("Resposta recebida de inserir_servico.php:", response);

                if (response && response.success) {
                    let comanda_id_para_listar = id_atual_comanda;
                    if (response.nova_comanda_id && response.nova_comanda_id > 0) {
                        $('#id').val(response.nova_comanda_id);
                        comanda_id_para_listar = response.nova_comanda_id;
                        console.log("Input #id ATUALIZADO com Nova Comanda ID:", comanda_id_para_listar);
                    } else if (comanda_id_para_listar <= 0 && $('#id').val() > 0) {
                        comanda_id_para_listar = $('#id').val();
                        console.log("Usando ID da comanda do input #id:", comanda_id_para_listar);
                    }

                    let swalTitle = 'Sucesso!';
                    let swalText = response.message || 'Operação realizada.';
                    if(response.tipo_registro === 'Assinante') {
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

                    console.log("Chamando listarServicos com ID:", comanda_id_para_listar);
                    if(typeof listarServicos === 'function') {
                        listarServicos2(comanda_id_para_listar);
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

                    $('#servico').val('').trigger('change.select2');
                } else {
                    Swal.fire('Erro!', response.message || 'Ocorreu um erro ao processar a solicitação.', 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Erro Crítico!', 'Falha na comunicação com o servidor ou erro interno. Verifique o console (F12).', 'error');
                console.error("Erro AJAX inserirServico:", status, error, xhr.responseText);
            },
            complete: function() {
                if($botaoOriginal.length) { $botaoOriginal.prop('disabled', false).html('<i class="fa fa-plus"></i> Inserir'); }
            }
        });
    }

    function listarServicos2() {
        var id_atual_comanda = $("#id").val();
        var cliente = $("#cliente").val();

        if (!cliente) {
            Swal.fire('Atenção!', 'Selecione um Cliente', 'warning');
            return;
        }

        $.ajax({
            url: 'paginas/comanda/listar_servicos.php',
            method: 'POST',
            data: { id: id_atual_comanda },
            dataType: "html",
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
        var id_atual_comanda = $("#id").val();

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

        var $botaoOriginal = $('button[onclick="inserirProduto()"]');
        if($botaoOriginal.length) { $botaoOriginal.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Inserindo...'); }

        console.log("Enviando para inserir_produto.php:", { produto, funcionario, cliente, quantidade, id: id_atual_comanda });

        $.ajax({
            url: 'paginas/comanda/inserir_produto.php',
            method: 'POST',
            data: {
                produto: produto,
                funcionario: funcionario,
                cliente: cliente,
                quantidade: quantidade,
                id: id_atual_comanda
            },
            dataType: "json",
            success: function(response) {
                console.log("Resposta recebida de inserir_produto.php:", response);

                if (response && response.success) {
                    let comanda_id_para_listar = id_atual_comanda;
                    if (response.nova_comanda_id && response.nova_comanda_id > 0) {
                        $('#id').val(response.nova_comanda_id);
                        comanda_id_para_listar = response.nova_comanda_id;
                        console.log("Input #id ATUALIZADO com Nova Comanda ID:", comanda_id_para_listar);
                    } else if (comanda_id_para_listar <= 0 && $('#id').val() > 0) {
                        comanda_id_para_listar = $('#id').val();
                    }

                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Sucesso!',
                        text: response.message || 'Produto adicionado com sucesso!',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    console.log("Chamando listarProdutos com ID:", comanda_id_para_listar);
                    if(typeof listarProdutos === 'function') {
                        listarProdutos(comanda_id_para_listar);
                    } else {
                        console.warn("Função listarProdutos não está definida!");
                    }

                    console.log("Chamando calcular()...");
                    if(typeof calcular === 'function') {
                        calcular();
                    } else {
                        console.warn("Função calcular() não definida!");
                    }

                    $("#quantidade").val('1');
                    $('#produto').val('').trigger('change.select2');
                    $('#funcionario2').val('0').trigger('change.select2');
                } else {
                    Swal.fire('Erro!', response.message || 'Não foi possível adicionar o produto.', 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Erro Crítico!', 'Falha na comunicação ao adicionar produto. Verifique o console (F12).', 'error');
                console.error("Erro AJAX inserirProduto:", status, error, xhr.responseText);
            },
            complete: function() {
                if($botaoOriginal.length) { $botaoOriginal.prop('disabled', false).html('<i class="fa fa-plus"></i> Inserir'); }
            }
        });
    }

    function listarProdutos(id) {
        $.ajax({
            url: 'paginas/comanda/listar_produtos.php',
            method: 'POST',
            data: { id },
            dataType: "html",
            success: function(result) {
                $("#listar_produtos").html(result);
            }
        });
    }

    function listarProdutosDados(id) {
        $.ajax({
            url: 'paginas/comanda/listar_produtos_dados.php',
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
            url: 'paginas/comanda/listar_servicos_dados.php',
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
            url: 'paginas/comanda/salvar.php',
            type: 'POST',
            data: formData,
            success: function(mensagem) {
                var msg = mensagem.split("*");
                $('#mensagem').text('');
                $('#mensagem').removeClass();

                if (mensagem.trim() == "Salvo com Sucesso") {
                    $('#btn-fechar').click();
                    listar();
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
        var produtos = parseFloat($('#valor_produtos').val() || 0);
        var servicos = parseFloat($('#valor_servicos').val() || 0);
        var descontos = parseFloat($('#valor_descontos').val() || 0);
        var total_valores = (produtos + servicos) - descontos;
        var valor = parseFloat($("#valor_serv").val() || 0);
        var valor_rest = parseFloat($("#valor_serv_agd_restante").val() || 0);
        var total = total_valores - valor_rest;
        $('#valor_serv').val(total.toFixed(2));
    }

    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>