<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'agenda';
$data_atual = date('Y-m-d');

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
	<!-- <div class="col-md-3">
		<button style="margin-bottom:10px; border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)" onclick="inserir()" type="button" class="btn btn-primary novo " ><i class="fa fa-plus" aria-hidden="true"></i> Novo Agendamento</button>
	</div> -->

	
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
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
						<div class="col-md-5">						
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


						<div class="col-md-4">						
						<div class="form-group"> 
							<label>Serviço</label> 
							<select class="form-control sel3" id="servico" name="servico" style="width:100%;" required> 

									<?php 
									$query = $pdo->query("SELECT * FROM servicos_func where funcionario = '$id_usuario' and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
	for($i=0; $i < @count($res); $i++){
		$serv = $res[$i]['servico'];

		$query2 = $pdo->query("SELECT * FROM servicos where id = '$serv' and ativo = 'Sim' and id_conta = '$id_conta' ");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);	
		$nome_func = $res2[0]['nome'];

		echo '<option value="'.$serv.'">'.$nome_func.'</option>';
	}		
}else{
	echo '<option value="">Nenhum Serviço</option>';
}
									?>


								</select>    
						</div>						
					</div>

						<div class="col-md-3" id="nasc">						
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
					<small><div id="mensagem-ag" align="center" class="mt-3"></div></small>					

				</div>


				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="fa-regular fa-floppy-disk"></i> Salvar</button>
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
								<label>Valor Restante <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Caso o cliente efetue o pagamento com duas formas diferentes de pagamento. Ex: Pix e Cartão." style="color: blue;"></i> </label> 
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
<!-- //calendar -->

<script type="text/javascript">
	$(document).ready(function() {

		listarHorarios();
		
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

	$("#form-text").submit(function () {
		$('#mensagem-ag').text('Carregando...');
		event.preventDefault();
		
		var formData = new FormData(this);

		$.ajax({
			url: 'paginas/' + pag +  "/inserir.php",
			type: 'POST',
			data: formData,

			success: function (mensagem) {				
				$('#mensagem-ag').text('');
				$('#mensagem-ag').removeClass()
				if (mensagem.trim() == "Salvo com Sucesso") {                   
					$('#btn-fechar').click();
					listar();
					listarHorarios();
				} else {
					$('#mensagem-ag').addClass('text-danger')
					$('#mensagem-ag').text(mensagem)
				}

			},

			cache: false,
			contentType: false,
			processData: false,

		});

	});

</script>




<script type="text/javascript">
	function listar(){

		var funcionario = $('#funcionario').val();

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
		listar();	
		listarHorarios();				
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

		var funcionario = $('#funcionario').val();	
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

		$.ajax({
			url: 'paginas/' + pag +  "/inserir-servico.php",
			type: 'POST',
			data: formData,

			success: function (mensagem) {
				$('#mensagem-servico').text('');
				$('#mensagem-servico').removeClass()
				if (mensagem.trim() == "Salvo com Sucesso") {                    
					$('#btn-fechar-servico').click();
					listar();
				} else {
					$('#mensagem-servico').addClass('text-danger')
					$('#mensagem-servico').text(mensagem)
				}

			},

			cache: false,
			contentType: false,
			processData: false,

		});

	});

</script>

<script>
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
</script>