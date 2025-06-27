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
if(@$_SESSION['nivel_usuario'] != 'Administrador'){
	    echo "<script>window.location='agenda.php'</script>";
    }

?>

<style>
	.tooltip-inner {
		background-color: #48D1CC; /* Amarelo */
		color: #000; /* Cor do texto */
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

</style>

<div class="row">
	<div class="col-md-3">
		<button style="margin-bottom:10px; border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)" onclick="inserir()" type="button" class="btn btn-primary novo" ><i class="fa fa-plus" aria-hidden="true"></i> Novo Agendamento</button>
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






<!-- Modal -->
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
								<select class="form-control sel3" id="servico" name="servico" style="width:100%;" required> 									

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






<script type="text/javascript">var pag = "<?=$pag?>"</script>
<script src="js/ajax.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>


<!-- calendar -->
<script type="text/javascript" src="js/monthly.js"></script>
<script type="text/javascript">
	$(window).load( function() {

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
                     if ($('#btn-fechar').length) {
                          $('#btn-fechar').click();
                     } else if ($(form).closest('.modal').length) {
                         // Tenta fechar o modal pai do formulário (alternativa)
                         $(form).closest('.modal').modal('hide');
                     }

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
		$('#id_funcionario').val(funcionario);	
		$('#funcionario_modal').val(funcionario).change();

		listar();	
		listarHorarios();
		listarServicos(funcionario);

	}
</script>



<script type="text/javascript">
	
	function mudarFuncionarioModal(){	
		var func = $('#funcionario_modal').val();	
		listar();	
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
		var serv = $("#servico").val();
		
		$.ajax({
			url: 'paginas/' + pag +  "/listar-servicos.php",
			method: 'POST',
			data: {func},
			dataType: "text",

			success:function(result){
				$("#servico").html(result);
			}
		});
	}
</script>

<script>
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
</script>