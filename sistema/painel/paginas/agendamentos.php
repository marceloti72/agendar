<?php
@session_start();
if (!isset($_SESSION['id_usuario'])) {
    // Redireciona se não estiver logado, idealmente para a página de login
    echo "<script>window.location='../'</script>";
    exit();
}

$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");

$pag = 'agendamentos';
$data_atual = date('Y-m-d');

// Garante que apenas administradores acessem esta página
if (@$_SESSION['nivel_usuario'] != 'administrador') {
    echo "<script>window.location='agenda'</script>";
    exit();
}
?>

<!-- Estilos específicos para a biblioteca do calendário -->
<link rel="stylesheet" type="text/css" href="css/monthly.css">
<style>
    /* FIX: Estilos para fazer os modais funcionarem sem Bootstrap CSS */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 1050;
        overflow-y: auto;
        /* Alinha o .modal-dialog no centro */
        display: none; /* Começa escondido */
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal.fade {
        transition: opacity 0.3s ease;
    }

    /* Adaptações visuais para a biblioteca do calendário se integrar ao tema */
    .monthly-header {
        background-color: #1e40af; /* Azul escuro para combinar com o tema */
        color: white;
    }
    .monthly-day-header {
        background-color: #f1f5f9; /* Cinza claro */
    }
    .monthly-day-cell.monthly-today .monthly-day-number {
        background-color: #2563eb;
        color: white;
        border-radius: 50%;
    }
    .monthly-event-list {
        background-color: #dbeafe;
        border-left-color: #2563eb;
    }
</style>

<!-- Topo da Página: Ações e Filtros -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Gerenciar Agendamentos</h1>
    <div class="flex flex-wrap items-center gap-4">
        <div class="w-full sm:w-56">
            <select class="form-control sel2" id="funcionario" name="funcionario" style="width:100%;" onchange="mudarFuncionario()"> 
                <option value="">Filtrar por Profissional</option>
                <?php 
                $query = $pdo->query("SELECT * FROM usuarios where atendimento = 'Sim' and id_conta = '$id_conta' ORDER BY nome asc");
                foreach($query->fetchAll(PDO::FETCH_ASSOC) as $res) {
                    echo '<option value="'.$res['id'].'">'.htmlspecialchars($res['nome']).'</option>';
                }
                ?>
            </select>
        </div>
        <!-- BOTÃO CORRIGIDO: Usa onclick padrão para chamar a função global e parar a propagação do evento -->
        <button onclick="event.stopPropagation(); showModal('#modalForm');" type="button" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-200 flex items-center justify-center">
            <i class="fa fa-plus mr-2"></i> Novo Agendamento
        </button>
    </div>
</div>

<input type="hidden" name="data_agenda" id="data_agenda" value="<?= $data_atual ?>"> 

<!-- Layout Principal: Calendário e Lista -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Coluna do Calendário -->
    <div class="lg:col-span-1 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-md">
        <div class="monthly" id="mycalendar"></div>
    </div>

    <!-- Coluna da Lista de Agendamentos -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-md min-w-0">
        <div id="listar">
            <!-- O conteúdo dos agendamentos será carregado aqui via AJAX -->
        </div>
    </div>
</div>

<!-- Modal Concluir Serviço/Comanda -->
<div class="modal fade" id="modalForm2" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 90%;">
        <div class="modal-content">
            <!-- Header do Modal -->
            <div class="modal-header bg-blue-600 text-white flex justify-between items-center">
                <h4 class="modal-title text-xl font-bold" id="titulo_comanda"><i class="fas fa-cash-register mr-2"></i> Nova Comanda</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>

            <form id="form_salvar" class="w-full h-full flex flex-col md:flex-row">
                <!-- Left Panel -->
                <div class="md:w-3/4 modal-left-panel p-6 overflow-y-auto">
                    <div class="mb-4">
                        <h3 id="nome_do_cliente_aqui" class="text-2xl font-bold text-gray-800">Cliente Exemplo</h3>
                    </div>

                    <!-- Services Card -->
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200 max-w-xl">
                        <div class="section-header">
                            <i class="fas fa-cut section-icon"></i>
                            <h5 class="text-xl font-semibold text-gray-800">Serviços</h5>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                            <div class="col-span-8">
                                <label for="servico" class="block text-sm font-medium text-gray-700 mb-1">Serviço</label>
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
                            <div class="col-span-4">
                                <label for="funcionario2" class="block text-sm font-medium text-gray-700 mb-1">Funcionário</label>
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
                            <div class="md:col-span-12">
                                <button type="button" class="w-full btn-success bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md shadow transition duration-200" onclick="inserirServico()">
                                    <i class="fa fa-plus mr-2"></i> Adicionar Serviço
                                </button>
                            </div>
                        </div>
                        <div class="item-list-container space-y-2" id="listar_servicos"></div>
                    </div>

                    <!-- Products Card -->
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200 max-w-xl" >
                        <div class="section-header">
                            <i class="fas fa-box section-icon"></i>
                            <h5 class="text-xl font-semibold text-gray-800">Produtos</h5>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                            <div class="col-span-8">
                                <label for="produto" class="block text-sm font-medium text-gray-700 mb-1">Produto</label>
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
                            <div class="col-span-4">
                                <label for="quantidade" class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                                <input type="number" class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="quantidade" id="quantidade" value="1" min="1">
                            </div>
                            <div class="md:col-span-12">
                                <button type="button" class="w-full btn-success bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md shadow transition duration-200" onclick="inserirProduto()">
                                    <i class="fa fa-plus mr-2"></i> Adicionar Produto
                                </button>
                            </div>
                        </div>
                        <div class="item-list-container space-y-2" id="listar_produtos"></div>
                    </div>

                    <!-- Discounts & Observations Card -->
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200 max-w-xl">
                        <div class="section-header">
                            <i class="fas fa-percentage section-icon"></i>
                            <h5 class="section-title text-xl font-semibold text-gray-800">Descontos</h5>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sinal (Valor Pago)</label>
                                <input type="text" class="form-input block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-right text-red-500 font-semibold" id="valor_sinal" value="R$ 10,00" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Desconto Cupom</label>
                                <input type="text" class="form-input block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-right text-red-500 font-semibold" id="valor_cupom" value="R$ 5,00" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200 max-w-xl">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                            <textarea class="form-textarea block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="obs" id="obs2" maxlength="1000" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Panel -->
                <div class="md:w-1/4 bg-gray-50 p-6 modal-right-panel overflow-y-auto border-t md:border-t-0 md:border-l border-gray-200">
                    <div class="pagamento-container">
                        <div class="pagamento-header">
                            <img src="https://placehold.co/80x80/2563eb/ffffff?text=CASH" alt="Ícone Pagamento" class="pagamento-icon rounded-full p-2 bg-blue-500 mb-2">
                            <h4 class="text-2xl font-bold text-gray-800">PAGAMENTO</h4>
                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Serviços</label>
                                <input type="text" class="form-input block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-right valor-display" id="valor_servicos" value="R$ 0,00" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Produtos</label>
                                <input type="text" class="form-input block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-right valor-display" id="valor_produtos" value="R$ 0,00" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Descontos</label>
                                <input type="text" class="form-input block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-right valor-display text-red-500 font-semibold" id="valor_descontos" value="R$ 15,00" readonly>
                            </div>
                            <hr class="border-gray-300">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total a Pagar</label>
                                <input type="text" class="form-input block w-full rounded-md border-2 border-green-500 shadow-sm bg-green-100 text-right total-display" name="valor_total" id="valor_serv" value="R$ 0,00" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Forma de Pagamento</label>
                                <select class="form-select block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="forma_pgto" name="forma_pgto"> 
                                    <option value="">Selecione</option>
                                    <option value="Mercado Pago">Mercado Pago</option>
                                    <option value="Credito">Cartão de Crédito</option>
                                    <option value="Debito">Cartão de Débito</option>
                                    <option value="Pix">Pix</option>
                                    <option value="Dinheiro">Dinheiro</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3">
                            <a href="#" id="btn_fechar_comanda" class="btn btn-success w-full bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold py-3 px-6 rounded-lg shadow-md transition duration-200 text-center" onclick="fecharComanda(event)">
                                <i class="fas fa-check-circle mr-2"></i> Fechar Comanda
                            </a>
                            <button type="button" class="btn btn-outline-secondary w-full border border-gray-400 text-gray-600 hover:bg-gray-200 font-bold py-3 px-6 rounded-lg shadow-sm transition duration-200" onclick="hideModal()">
                                <i class="fas fa-times-circle mr-2"></i> Sair
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

<!-- Modal Novo Agendamento -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue-600 text-white">
                <h4 class="modal-title">Novo Agendamento</h4>
                <button type="button" data-dismiss="modal" class="close" aria-label="Close">
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
							<label>Funcionário </label> 			
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
								<label>Data </label> 
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
								// Obtém a data atual no formato 'YYYY-MM-DD'
								$data_atual = date('Y-m-d');
								
								// Query para buscar cupons válidos
								$query = $pdo->prepare("SELECT * FROM cupons WHERE id_conta = :id_conta AND data_validade >= :data_atual AND usos_atuais < max_usos ORDER BY codigo ASC");
								$query->bindValue(':id_conta', $id_conta);
								$query->bindValue(':data_atual', $data_atual);
								$query->execute();
								$res = $query->fetchAll(PDO::FETCH_ASSOC);

								foreach ($res as $item) {
									// Determina o sufixo com base no tipo de desconto
									$sufixo = ($item['tipo_desconto'] === 'porcentagem') ? '%' : '$';
									
									// Monta a string de exibição no formato "codigo + valor + tipo"
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
    // ===================================================================
    // FUNÇÕES GLOBAIS DE CONTROLE DOS MODAIS (CORRIGIDAS)
    // ===================================================================
    function showModal(selector) {
        alert('jlkjlkj')
        if (typeof selector !== 'string' || selector.trim() === '') return;
        // CORREÇÃO DEFINITIVA: Garante que o seletor seja sempre válido.
        const cleanSelector = '#' + selector.replace(/^#+/, '');
        $(cleanSelector).css('display', 'flex').hide().fadeIn(200);
    }

    function hideModal(selector) {
        // Se o seletor for um objeto (como um elemento DOM), usa-o diretamente.
        if (typeof selector !== 'string') {
             $(selector).fadeOut(200, function() {
                $(this).css('display', 'none');
            });
            return;
        }
        if (selector.trim() === '') return;
        const cleanSelector = '#' + selector.replace(/^#+/, '');
        $(cleanSelector).fadeOut(200, function() {
            $(this).css('display', 'none');
        });
    }
	$(window).load( function() {

		// $('#mycalendar').monthly({
		// 	mode: 'event',

		// });

		// $('#mycalendar2').monthly({
		// 	mode: 'picker',
		// 	target: '#mytarget',
		// 	setWidth: '150px',
		// 	startHidden: true,
		// 	showTrigger: '#mytarget',
		// 	stylePast: true,
		// 	disablePast: true
		// });
        // ===================================================================
        // FIX: LÓGICA PARA CONTROLAR MODAIS SEM BOOTSTRAP.JS (CORRIGIDO)
        // ===================================================================        
        
        // Listener para botões que AINDA usam data-toggle (para compatibilidade com código antigo/AJAX)
        $('body').on('click', '[data-toggle="modal"]', function(e) {
            alert('target')
            if (e.isDefaultPrevented()) return; // Se o Alpine já tratou, não faz nada
            e.preventDefault();
            var target = $(this).data('target');
             alert(target)
            showModal(target);
        });

        // Listener para botões de fechar
        $(document).on('click', '[data-dismiss="modal"]', function(e) {
            e.preventDefault();
            var modal = $(this).closest('.modal');
            hideModal(modal);
        });

        // Listener para fechar clicando fora
        $(document).on('click', '.modal', function(e) {
            if ($(e.target).is('.modal') && $(this).data('backdrop') !== 'static') {
                hideModal(this);
            }
        });
        // ===================================================================
        // FIM DO FIX
        // ===================================================================

        // Inicializa o calendário
        $('#mycalendar').monthly({ mode: 'event' });

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
		listarHorarios();

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
        var pgto = $("#forma_pgto").val();
        var id = $("#id").val();

        // Verifica se o valor selecionado é vazio
        if (pgto === "") {
            // Se for vazio, exibe o SweetAlert de erro
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Por favor, selecione uma forma de pagamento!',
                confirmButtonText: 'OK'
            });
            // É importante adicionar um "return" aqui para interromper a função
            return;
        } 

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
                    $('#forma_pgto').val('').trigger('change'); //Limpa select2
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