<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'agendamentos';
$data_atual = date('Y-m-d');

//verificar se ele tem a permissão de estar nessa página
// if(@$agendamentos == 'ocultar'){
// 	echo "<script>window.location='../index.php'</script>";
// 	exit();
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

</style>

<style>
    /* Estilos Gerais do Modal */
    .modal-content {
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        border: none;
        overflow: hidden; /* Garante que os cantos arredondados sejam respeitados */
    }

    /* Cabeçalho do Modal */
    .modal-header-custom {
        background: linear-gradient(135deg, #4a90e2 0%, #007bff 100%); /* Gradiente Azul */
        color: #fff;
        border-bottom: none;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-title {
        color: #fff;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 1.2rem;
    }

    .modal-icon {
        color: #fff; /* Ícone branco para contraste no fundo azul */
        font-size: 2.2rem;
    }

    .modal-header-custom .close {
        color: #fff;
        opacity: 0.9;
        font-size: 2rem;
        transition: opacity 0.2s ease;
    }

    .modal-header-custom .close:hover {
        opacity: 1;
    }
    
    /* Corpo Principal do Modal */
    .modal-body {
        padding: 0;
        display: flex;
        height: 80%;
    }

    .modal-xl {
    max-width: 80% !important;
    width: 80% !important;
}
    
    /* Painel Esquerdo (Serviços, Produtos, Descontos) */
    .modal-left-panel {
        background-color: #f8f9fa; /* Fundo mais claro */
        padding-right: 0;
        border-right: 1px solid #e0e0e0; /* Separador sutil */
    }

    .modal-body-scroll {
        max-height: 75vh; /* Aumenta a altura para melhor visualização */
        overflow-y: auto;
        padding-bottom: 2rem !important; /* Espaço para o final */
    }

    .client-name {
        font-weight: 700;
        color: #343a40; /* Cor mais escura para destaque */
        font-size: 1.2rem;
        text-align: center;
        margin-bottom: 1.5rem;
        padding-bottom: 10px;
        border-bottom: 2px solid #e0e0e0;
    }

    .divider {
        margin: 2rem 0;
        border-top: 1px dashed #cccccc; /* Linha tracejada mais clara */
    }

    /* Estilo para as Seções (Serviços, Produtos, Descontos) */
    .section-card {
        background-color: #ffffff; /* Fundo branco para as seções */
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); /* Sombra mais visível */
        transition: all 0.3s ease;
    }

    .section-card:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .section-header {
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 1rem;
    }

    .section-title {
        font-weight: 700;
        color: #343a40; /* Cor escura para o título */
        font-size: 1.4rem;
        margin-bottom: 0; /* Remover margem inferior padrão */
    }

    .section-icon {
        color: #4a90e2; /* Ícone azul para destaque */
        font-size: 1.6rem;
    }

    .item-list-container {
        margin-top: 1.5rem; /* Mais espaço */
        margin-bottom: 1rem;
    }

    /* Campos de Formulário */
    .form-group label {
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.5rem;
        display: block; /* Garante que o label ocupe sua própria linha */
    }
    
    .form-control, .sel2 {
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }

    .sel2 {
        padding: 0.375rem 0.75rem !important; /* Ajuste para Select2 */
    }

    /* Botões de Adicionar Item */
    .btn-add-item {
        border-radius: 8px;
        height: 45px; /* Altura maior para melhor usabilidade */
        width: 100%;
        font-size: 1.1rem;
        font-weight: 600;
        background-color: #28a745; /* Verde padrão */
        border-color: #28a745;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-add-item:hover {
        background-color: #218838;
        border-color: #1e7e34;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-add-item i {
        font-size: 1rem;
    }

    /* Input de Valores e Totais */
    .value-input, .summary-value {
        font-size: 1.1em;
        font-weight: bold;
        background-color: #e9ecef; /* Fundo levemente cinza */
        border: 1px solid #dee2e6;
        color: #343a40;
    }
    
    /* Painel Direito (Pagamento) */
    .modal-right-panel {
        background-color: #f0f2f5; /* Fundo ligeiramente mais escuro */
        border-left: 1px solid #e1e4e8;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .pagamento-container {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding-bottom: 2rem !important;
    }

    .pagamento-header {
        display: flex;
        align-items: center;
        gap: 15px;
        color: #007bff;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 1rem;
        justify-content: center;
    }
    
    .pagamento-header h4 {
        font-weight: 700;
        color: #343a40;
        font-size: 1.6rem;
    }

    .pagamento-icon {
        height: 45px; /* Tamanho maior para o ícone */
        filter: invert(30%) sepia(80%) saturate(1500%) hue-rotate(200deg) brightness(90%); /* Azul */
    }

    .divider-light {
        margin: 1.5rem 0;
        border-top: 1px solid #e0e0e0;
    }

    .summary-label {
        font-weight: 600;
        color: #555;
    }

    .summary-value {
        background-color: #fff;
        border-color: #dee2e6;
    }
    
    .total-final-section {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 2px solid #d4edda;
        background-color: #e2f0e6;
        border-radius: 8px;
        padding: 1.5rem;
    }

    .summary-label-total {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1a5e20;
    }

    .total-final-value {
        font-size: 1.8em; /* Tamanho maior para o total final */
        font-weight: bold;
        color: #155724;
        background-color: #d4edda;
        border: 2px solid #28a745;
        padding: 1rem;
        border-radius: 10px;
    }

    /* Botões de Ação Final */
    .btn-action {
        border-radius: 10px;
        padding: 15px 20px;
        font-size: 1.2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
    }

    .btn-action:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }
    
    .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
    }
    
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: #fff;
    }

    /* Customizações para Select2 (necessário para compatibilidade visual) */
    .select2-container--default .select2-selection--single {
        height: 45px; /* Altura consistente com outros inputs */
        border-radius: 8px;
        border: 1px solid #ced4da;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 45px;
        padding-left: 1rem;
        color: #495057;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 45px;
        top: 1px;
        right: 1px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #888 transparent transparent transparent;
        border-width: 5px 5px 0 5px;
    }
    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent #888 transparent;
        border-width: 0 5px 5px 5px;
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .modal-left-panel, .modal-right-panel {
            flex: 0 0 100%;
            max-width: 100%;
            border-right: none;
            border-bottom: 1px solid #e0e0e0;
        }
        .modal-right-panel {
            border-top: 1px solid #e0e0e0;
        }
        .modal-body-scroll {
            max-height: 60vh; /* Ajuste para telas menores */
        }
        .modal-dialog.modal-xl {
            max-width: 95%; /* Reduz o tamanho do modal em telas menores */
            margin: 1rem auto;
        }
    }


    .modal-agendamento {
    max-width: 800px; /* Largura fixa para este modal */
}

.modal-agendamento .modal-content {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.modal-agendamento-header {
    background-color: #f7f9fc;
    border-bottom: 1px solid #e1e4e8;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-agendamento-header .modal-title {
    color: #495057;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-agendamento-header .modal-icon {
    color: #007bff;
    font-size: 1.5rem;
}

.modal-agendamento-header .close {
    color: #6c757d;
    opacity: 0.8;
}

.modal-agendamento-body {
    padding: 2rem;
}

.modal-agendamento-body .form-group {
    margin-bottom: 1.5rem;
}

.modal-agendamento-body label {
    font-weight: 600;
    color: #495057;
}

.modal-agendamento-body .form-control {
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 0.75rem 1rem;
}

/* Estilo para a linha divisória */
.divider-agendamento {
    margin: 2rem 0;
    border-top: 1px solid #dee2e6;
}

/* Estilo para a seção de horários */
.horarios-container {
    background-color: #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    border: 1px solid #dee2e6;
}

.horarios-container .horarios-label {
    font-size: 1.2rem;
    font-weight: 700;
    color: #007bff;
    margin-bottom: 1rem;
    display: block;
}

.horarios-container .horarios-list {
    min-height: 100px; /* Espaço para carregar os horários */
    background-color: #fff;
    border-radius: 8px;
    padding: 1rem;
    border: 1px dashed #ced4da;
}

.horarios-container .horarios-list small {
    display: block;
    text-align: center;
    color: #6c757d;
    padding-top: 2rem;
}

/* Estilo para o rodapé */
.modal-agendamento-footer {
    background-color: #f7f9fc;
    border-top: 1px solid #e1e4e8;
    padding: 1.5rem;
    border-bottom-left-radius: 15px;
    border-bottom-right-radius: 15px;
    text-align: right;
}

.modal-agendamento-footer .btn {
    padding: 10px 20px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 8px;
}

/* Select2 custom styles to match form controls */
.sel2, .sel3 {
    padding: 0.75rem 1rem;
    height: auto;
}
.select2-container--default .select2-selection--single {
    border-radius: 8px;
    border: 1px solid #ced4da;
    height: 45px;
    display: flex;
    align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 45px;
    padding-left: 1rem;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 45px;
}
</style>



<div class="row">
	<div class="col-md-3">
		<button style="margin-bottom:10px; border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)" data-toggle="modal" data-target="#modalForm" type="button" class="btn novo" ><i class="fa fa-plus" aria-hidden="true"></i> Novo Agendamento</button>
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

			<!-- grids -->
			<div class="agile-calendar-grid">
				<div class="page">

					<div class="w3l-calendar-left">
						<div class="calendar-heading">

						</div>
						<div class="monthly" id="mycalendar"></div>
					</div>

					<div class="clearfix"> </div>
				</div>
			</div>
		</div>
	</div>


	<div class="col-xs-12 col-md-8 bs-example widget-shadow" style="padding:10px 5px; margin-top: 0px;" id="listar">

	</div>
</div>



<div class="modal fade" id="modalForm2" tabindex="-1" role="dialog" aria-labelledby="modalForm2Label" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
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
                        <div class="col-md-7 modal-left-panel">
                            <div class="modal-body-scroll p-4">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <h3 id="nome_do_cliente_aqui" class="client-name">Nome do Cliente</h3>
                                    </div>
                                </div>                             

                                <div class="section-card">
                                    <div class="section-header">
                                        <h5 class="section-title"><i class="fas fa-scissors section-icon"></i> Serviços</h5>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
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
                                        <div class="col-md-4">
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
                                            <button type="button" class="btn btn-primary btn-add-item" onclick="inserirServico()"><i class="fa fa-plus"></i> Add</button>
                                        </div>
                                    </div>
                                    <div class="item-list-container" id="listar_servicos"></div>
                                </div> 

                                <div class="section-card">
                                    <div class="section-header">
                                        <h5 class="section-title"><i class="fas fa-box-open section-icon"></i> Produtos</h5>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <select class="form-control sel2" id="produto" name="produto" style="width:100%;" onchange="listarServicos2()">
                                                    <?php
                                                    $query = $pdo->query("SELECT * FROM produtos where estoque > 0 and id_conta = '$id_conta' ORDER BY nome asc");
                                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                    foreach ($res as $item) {
                                                        echo '<option value="' . $item['id'] . '">' . htmlspecialchars($item['nome']) . '</option>';
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
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary btn-add-item" onclick="inserirProduto()"><i class="fa fa-plus"></i> Add</button>
                                        </div>
                                    </div>
                                    <div class="item-list-container" id="listar_produtos"></div>
                                </div> 

                                <div class="section-card">
                                    <div class="section-header">
                                        <h5 class="section-title"><i class="fas fa-tags section-icon"></i> Descontos</h5>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><small>Sinal (Valor Pago)</small></label>
                                                <input type="text" class="form-control text-right value-input" id="valor_sinal" style="color: red" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><small>Desconto Cupom</small></label>
                                                <input type="text" class="form-control text-right value-input" id="valor_cupom" style="color: red" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div> <hr class="divider">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Observações</label>
                                        <input type="text" class="form-control" value="" name="obs" id="obs2" maxlength="1000" placeholder="Adicione observações para a comanda...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5 modal-right-panel">
                            <div class="pagamento-container p-4">
                                <div class="pagamento-header">
                                    <img src="../../images/registradora.png" alt="Ícone Pagamento" class="pagamento-icon">
                                    <h4>FINALIZAÇÃO</h4>
                                </div>                               
                                <div class="form-group">
                                    <label class="summary-label"><small>Total Serviços</small></label>
                                    <input type="text" class="form-control text-right summary-value" id="valor_servicos" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="summary-label"><small>Total Produtos</small></label>
                                    <input type="text" class="form-control text-right summary-value" id="valor_produtos" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="summary-label"><small>Total Descontos</small></label>
                                    <input type="text" class="form-control text-right summary-value" id="valor_descontos" style="color: red" readonly>
                                </div>                                
                                <div class="form-group total-final-section">
                                    <label class="summary-label-total"><small>Total a Pagar</small></label>
                                    <input type="text" class="form-control text-right total-final-value" name="valor_total" id="valor_serv" readonly>
                                </div>

                                <div class="d-flex flex-column gap-3 mt-5">
                                    <a href="#" id="btn_fechar_comanda" class="btn btn-success btn-lg btn-block btn-action" onclick="fecharComanda()">
                                        <i class="fas fa-check-circle"></i> Fechar Comanda
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary btn-block btn-action" data-dismiss="modal">
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

<!-- Modal -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-agendamento" role="document">
        <div class="modal-content">
            <div class="modal-header modal-agendamento-header">
                <h4 class="modal-title" id="titulo_comanda">
                    <i class="fas fa-calendar-alt modal-icon"></i> Novo Agendamento
                </h4>
                <button type="button" id="btn-fechar" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="form-text">
                <div class="modal-body modal-agendamento-body">

                    <div class="row">
                        <div class="col-md-12">
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
                    </div>

                    <div class="row">
                        <div class="col-md-12">
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
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Serviço</label>
                                <select class="form-control sel3" id="servico2" name="servico" style="width:100%;" required>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12" id="nasc">
                            <div class="form-group">
                                <label>Data</label>
                                <input type="date" class="form-control" name="data" id="data-modal" onchange="mudarData()">
                            </div>
                        </div>
                    </div>

                    <hr class="divider-agendamento">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group horarios-container">
                                <label class="horarios-label">Horários Disponíveis</label>
                                <div id="listar-horarios" class="horarios-list">
                                    <small class="text-muted">Selecione um Funcionário e uma Data</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="divider-agendamento">
                    <div class="row">
                        <div class="col-md-12">
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
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>OBS <small>(Máx 100 Caracteres)</small></label>
                                <input maxlength="100" type="text" class="form-control" name="obs" id="obs" placeholder="Adicione uma observação...">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="id_funcionario" id="id_funcionario">
                    <small><div id="mensagem" align="center" class="mt-3"></div></small>

                </div>
                <div class="modal-footer modal-agendamento-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Agendamento</button>
                </div>
            </form>
        </div>
    </div>
</div>







<!-- Modal -->
<div class="modal fade" id="modalServico" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header text-white" style="background-color: #4682B4;">
				<h4 class="modal-title">Serviço: <span id="titulo_servico"></span>  </h4>
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
								<label>Observações </label> 
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





<script type="text/javascript">var pag = "<?=$pag?>";</script>
<script src="js/ajax.js"></script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>



<script type="text/javascript" src="js/monthly.js"></script>
<script type="text/javascript">
	$(window).load( function() {

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
		// running on a server, should be good.
		break;
		case 'file:':
		alert('Just a heads-up, events will not work when run locally.');
	}

});
</script>

<script type="text/javascript">
	$(document).ready(function() {
		
		$('.sel3').select2({
			dropdownParent: $('#modalForm')
		});
	});
</script>


<script type="text/javascript">
	$(document).ready(function() {
		$('.sel2').select2({
			
		});
	});
</script>


<script type="text/javascript">
	$(document).ready(function() {
		
		$('.sel4').select2({
			dropdownParent: $('#modalServico')
		});
	});
</script>



<script>

$("#form-text").submit(function (event) {
    // 1. Previne o envio padrão do formulário HTML
    event.preventDefault();

    // 2. Referências e Feedback Inicial
    const form = this;
    const $mensagemDiv = $('#mensagem'); // Onde as mensagens são exibidas
    const $submitButton = $(form).find('button[type="submit"]'); // Botão de submit do form

    $mensagemDiv.text('Carregando...').removeClass('text-danger text-success'); // Mostra carregando
    if($submitButton.length) $submitButton.prop('disabled', true).append(' <i class="fas fa-spinner fa-spin"></i>'); // Desabilita botão (opcional)

    // 3. Pega os dados do formulário
    var formData = new FormData(form);

    // 4. Executa a chamada AJAX esperando JSON
    $.ajax({
        url: 'paginas/' + pag +  "/inserir.php", // VERIFIQUE SE ESTE É O SCRIPT PHP CORRETO
        type: 'POST',
        data: formData,
        dataType: "json", // <<<--- DEFINIDO COMO JSON ---<<<
        cache: false,
        contentType: false,
        processData: false,

        success: function (response) { // 'response' agora é um objeto JSON
            $mensagemDiv.text(''); // Limpa 'Carregando...'

            // 5. Verifica a resposta do PHP
            
                // SUCESSO
                $mensagemDiv.addClass('text-success').text(response.message || "Salvo com Sucesso!");

                // Usa SweetAlert para um feedback melhor (opcional, mas recomendado)
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: response.message || "Salvo com Sucesso!",
                    timer: 1500, // Fecha automaticamente após 1.5 segundos
                    showConfirmButton: false
                }).then(() => { // Executa DEPOIS que o Swal fechar
                    // Fecha o modal associado (se o botão existir)
                    //  if ($('#btn-fechar').length) {
                    //       $('#btn-fechar').click();
                    //  } else if ($(form).closest('.modal').length) {
                    //      // Tenta fechar o modal pai do formulário (alternativa)
                    //      $(form).closest('.modal').modal('hide');
                    //  }
                    $('#modalForm').modal('hide');

                    // Chama funções para atualizar listas/dados na página principal
                    if (typeof listar === 'function') {
                        listar(); // Sua função global de listagem principal?
                    } else { console.warn("Função listar() não definida."); }

                    if (typeof listarHorarios === 'function') {
                        listarHorarios(); // Sua função que lista horários?
                    } else { console.warn("Função listarHorarios() não definida."); }

                    // Adicione outras funções de atualização se necessário
                });

           
        },

        error: function (xhr, status, error) {
            // ERRO DE COMUNICAÇÃO ou PHP não retornou JSON válido
            $mensagemDiv.text('Erro de comunicação. Verifique o console.').addClass('text-danger');
            Swal.fire('Erro Crítico!', 'Falha na comunicação com o servidor. Verifique o console (F12).', 'error');
            console.error("Erro AJAX form-text:", status, error, xhr.responseText);
        },

        complete: function() {
            // Reabilita botão de submit SEMPRE ao final
            if($submitButton.length) $submitButton.prop('disabled', false).find('i.fa-spinner').remove();
        }
    }); // Fim $.ajax
}); 

</script>




<script type="text/javascript">
	function listar(){

		var funcionario = $('#funcionario').val();

		var data = $("#data_agenda").val();	
		$("#data-modal").val(data);
        
		$.ajax({
			url: 'paginas/agendamentos/listar.php',
			method: 'POST',
			data: {data, funcionario},
			dataType: "text",

			success:function(result){
				$("#listar").html(result);
			}
		});
	}
</script>




<script type="text/javascript">
	
	function limparCampos(){
		$('#id').val('');		
		$('#obs').val('');
		$('#hora').val('');				
		$('#data').val('<?=$data_atual?>');	

	}
</script>


<script type="text/javascript">
	
	function mudarFuncionario(){
		var funcionario = $('#funcionario').val();
		//$('#id_funcionario').val(funcionario);	
		//$('#funcionario_modal').val(funcionario).change();

		listar();	
		//listarHorarios();
		//listarServicos(funcionario);

	}
</script>



<script type="text/javascript">
	
	function mudarFuncionarioModal(){	
		var func = $('#funcionario_modal').val();	
		//listar();	
		listarHorarios();
		listarServicos(func);
	}
</script>



<script type="text/javascript">
	
	function mudarData(){
		var data = $('#data-modal').val();			
		$('#data_agenda').val(data).change();

		listar();	
		//listarHorarios();

	}
</script>



<script type="text/javascript">
	function listarHorarios(){

		var funcionario = $('#funcionario_modal').val();	
		var data = $('#data_agenda').val();	

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
</script>






<script>

	$("#form-servico").submit(function () {
		event.preventDefault();
		
		var formData = new FormData(this);		

			// Exemplo de como processar a resposta no seu JS (dentro do success do AJAX/Fetch)
		fetch('paginas/agendamentos/inserir-servico.php', {
			method: 'POST',
			body: formData // Seus dados do formulário
		})
		.then(response => response.json())
		.then(data => {
			if (data.status === 'success') {
				Swal.fire({
					title: 'Sucesso!',
					text: data.message + (data.detail ? '\n\nDetalhe: ' + data.detail : ''), // Mostra detalhe se houver
					icon: 'success',
					confirmButtonText: 'Ok'
				}).then(() => {
					// Ação pós-sucesso, ex: recarregar tabela, fechar modal
					window.location.reload(); // Exemplo simples
				});
			} else {
				Swal.fire({
					title: 'Erro!',
					text: data.message + (data.detail ? '\n\nErro Técnico: ' + data.detail : ''), // Mostra detalhe técnico se houver
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

			

</script>



<script type="text/javascript">
	function listarServicos(func){	
		var serv = $("#servico2").val();        
				
		$.ajax({
			url: 'paginas/' + pag +  "/listar-servicos.php",
			method: 'POST',
			data: {func},
			dataType: "text",

			success:function(result){
				$("#servico2").html(result);
			}
		});
	}
</script>

<script>
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })




function calcular() {
      setTimeout(function() {
        var produtos = parseFloat($('#valor_produtos').val() || 0); // Garante que é número
        var servicos = parseFloat($('#valor_servicos').val() || 0);
        var descontos = parseFloat($('#valor_descontos').val() || 0);

        var total = (produtos + servicos)-descontos;        
        $('#valor_serv').val(total.toFixed(2));

        //abaterValor(); //Chama depois de calcular o total
    }, 500);
    }

    function inserirServico() {
    $("#mensagem").text(''); // Limpa mensagens anteriores na página principal, se houver
    var servico = $("#servico").val();
    var funcionario = $("#funcionario2").val();
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
        url: 'paginas/comanda/inserir_servico.php', // Verifique o caminho e nome do script PHP
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
                    listarServicos2(comanda_id_para_listar); // <<-- USA O ID CORRETO
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

    function listarServicos2() {
        var id_atual_comanda = $("#id").val(); // Pega o ID atual da comanda (pode ser 0)
        var cliente = $("#cliente").val();

        if (!cliente) {
        Swal.fire('Atenção!', 'Selecione um Cliente', 'warning');
        return;
        }

        $.ajax({
            url: 'paginas/comanda/listar_servicos.php',
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
        url: 'paginas/comanda/inserir_produto.php', // <<< VERIFIQUE O CAMINHO E NOME DO ARQUIVO PHP
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
            url: 'paginas/comanda/listar_produtos.php',
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
            url: 'paginas/comanda/fechar_comanda.php',
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
	    var descontos = parseFloat($('#valor_descontos').val() || 0);

        var total_valores = (produtos + servicos)-descontos;

        var valor = parseFloat($("#valor_serv").val() || 0);
        var valor_rest = parseFloat($("#valor_serv_agd_restante").val() || 0);

        var total = total_valores - valor_rest;
        $('#valor_serv').val(total.toFixed(2));
    } 

	</script>