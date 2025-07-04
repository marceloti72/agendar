<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");

$pag = 'servicos_agenda';


//verificar se ele tem a permissão de estar nessa página
if(@$_SESSION['nivel_usuario'] != 'administrador'){
	    echo "<script>window.location='agenda.php'</script>";
    }


$data_hoje = date('Y-m-d');
$data_ontem = date('Y-m-d', strtotime("-1 days",strtotime($data_hoje)));

$mes_atual = Date('m');
$ano_atual = Date('Y');
$data_inicio_mes = $ano_atual."-".$mes_atual."-01";

if($mes_atual == '4' || $mes_atual == '6' || $mes_atual == '9' || $mes_atual == '11'){
	$dia_final_mes = '30';
}else if($mes_atual == '2'){
	$dia_final_mes = '28';
}else{
	$dia_final_mes = '31';
}

$data_final_mes = $ano_atual."-".$mes_atual."-".$dia_final_mes;


?>
<style>
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

<div class="">      
	<a class="btn btn-primary novo" onclick="inserir()" style = 'border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)'><i class="fa fa-plus" aria-hidden="true"></i> Novo Serviço</a>
</div>

<div class="bs-example widget-shadow" style="padding:15px; background-color: #fff !important;">
    <div class="row" style="background-color: transparent !important;">
        <div class="col-md-5 col-12" style="margin-bottom:5px; background-color: transparent !important;">
            <div class="form-row align-items-center">
                <div class="col-auto">
                    <small><i title="Data de Vencimento Inicial" class="fa fa-calendar-o"></i></small>
                </div>
                <div class="col">
                    <input type="date" class="form-control" name="data-inicial" id="data-inicial-caixa" value="<?php echo $data_hoje ?>" required>
                </div>
                <div class="col-auto">
                    <small><i title="Data de Vencimento Final" class="fa fa-calendar-o"></i></small>
                </div>
                <div class="col">
                    <input type="date" class="form-control" name="data-final" id="data-final-caixa" value="<?php echo $data_hoje ?>" required>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-12" style="margin-top:5px; background-color: transparent !important;" align="center">    
            <div> 
                <small>
                    <a title="Conta de Ontem" class="text-muted" href="#" onclick="valorData('<?php echo $data_ontem ?>', '<?php echo $data_ontem ?>')"><span>Ontem</span></a> / 
                    <a title="Conta de Hoje" class="text-muted" href="#" onclick="valorData('<?php echo $data_hoje ?>', '<?php echo $data_hoje ?>')"><span>Hoje</span></a> / 
                    <a title="Conta do Mês" class="text-muted" href="#" onclick="valorData('<?php echo $data_inicio_mes ?>', '<?php echo $data_final_mes ?>')"><span>Mês</span></a>
                </small>
            </div>
        </div>

        <div class="col-md-3 col-12" style="margin-top:5px; background-color: transparent !important;" align="center">    
            <div> 
                <small>
                    <a title="Todas as Contas" class="text-muted" href="#" onclick="buscarContas('')"><span>Todas</span></a> / 
                    <a title="Contas Pendentes" class="text-muted" href="#" onclick="buscarContas('Não')"><span>Pendentes</span></a> / 
                    <a title="Contas Pagas" class="text-muted" href="#" onclick="buscarContas('Sim')"><span>Pagas</span></a>
                </small>
            </div>
        </div>

		<input type="hidden" id="buscar-contas">

	</div>
	<style>
    /* Estilos gerais */
    .btn-primary {
        transition: all 0.3s;
        background-color: #007bff !important; /* Garante azul padrão */
        color: #fff !important;
    }
    .form-control {
        width: 100%;
        background-color: #fff !important; /* Fundo branco nos inputs */
    }
    .widget-shadow {
        padding: 15px;
        background-color: #fff !important; /* Fundo branco no container */
    }
    .row, .col-md-5, .col-md-2, .col-md-3, .col-12 {
        background-color: transparent !important; /* Remove fundos indesejados */
    }

    /* Media Query para Mobile (max-width: 768px) */
    @media (max-width: 768px) {
        .btn-primary {
            
            margin-bottom: 10px;
            font-size: 14px;
        }
        .widget-shadow {
            padding: 10px;
        }
        .form-row .col, .form-row .col-auto {
            margin-bottom: 10px;
        }
        .form-control {
            font-size: 14px;
            padding: 5px;
        }
        small {
            font-size: 12px;
        }
        .col-12 {
            margin-top: 10px;
        }
        .text-muted span {
            padding: 2px 5px;
        }
    }
</style>

	<hr>
	<div id="listar">

	</div>
	
</div>






<!-- Modal Inserir-->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header text-white" style="background-color: #4682B4;">
				<h4 class="modal-title"><span id="titulo_inserir"></span></h4>
				<button id="btn-fechar" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			<form id="form-servico2">
				<div class="modal-body">

					<div class="row">
						<div class="col-md-12">						
							<div class="form-group"> 
								<label>Profissional</label> 
								<select class="form-control sel2" id="funcionario" name="funcionario" style="width:100%;" required onchange="listarServicos()"> 

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
							<div class="col-md-6">						
							<div class="form-group"> 
								<label>Clientes</label> 
								<select class="form-control sel2" id="cliente" name="cliente" style="width:100%;" required> 

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
								<label>Serviço</label> 
								<select class="form-control sel2" id="servico" name="servico" style="width:100%;" required> 

									<?php 
									$query = $pdo->query("SELECT * FROM servicos where id_conta = '$id_conta' ORDER BY nome asc");
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

							

						<div class="col-md-3" id="nasc">						
							<div class="form-group"> 
								<label>Valor </label> 
								<input type="text" class="form-control" name="valor_serv" id="valor_serv" required> 
							</div>						
						</div>


							<div class="col-md-4" id="nasc">						
							<div class="form-group"> 
								<label>Data PGTO</label> 
								<input type="date" class="form-control" name="data_pgto" id="data_pgto" value="<?php echo date('Y-m-d') ?>"> 
							</div>						
						</div>	

						<div class="col-md-5">						
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
						<div class="col-md-3" id="">						
							<div class="form-group"> 
								<label>Valor Restante </label> 
								<input type="text" class="form-control" name="valor_serv_agd_restante" id="valor_serv_agd_restante"> 
							</div>						
						</div>


						<div class="col-md-4" id="">						
							<div class="form-group"> 
								<label>Data PGTO Restante</label> 
								<input type="date" class="form-control" name="data_pgto_restante" id="data_pgto_restante" value=""> 
							</div>						
						</div>

						<div class="col-md-5">						
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



					<div class="col-md-12">						
							<div class="form-group"> 
								<label>Observações </label> 
								<input maxlength="1000" type="text" class="form-control" name="obs" id="obs2"> 
							</div>						
						</div>



					
					<input type="hidden" name="id" id="id">

					<br>
					<small><div id="mensagem" align="center"></div></small>
				</div>

				<div class="modal-footer">      
					<button type="submit" class="btn btn-primary">Salvar</button>
				</div>
			</form>

			
		</div>
	</div>
</div>





<!-- Modal Dados-->
<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header text-white" style="background-color: #4682B4;">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_dados"></span></h4>
				<button id="btn-fechar-perfil" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">

				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-6">							
						<span><b>Valor : </b></span>
						<span id="valor_dados"></span>
					</div>	

					<div class="col-md-6">							
						<span><b>Data Lançamento: </b></span>
						<span id="data_lanc_dados"></span>							
					</div>


				</div>




				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-6">							
						<span><b>Data Vencimento: </b></span>
						<span id="data_venc_dados"></span>							
					</div>

					<div class="col-md-6">							
						<span><b>Data PGTO: </b></span>
						<span id="data_pgto_dados"></span>							
					</div>


				</div>



				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-6">							
						<span><b>Usuário Lanc: </b></span>
						<span id="usuario_lanc_dados"></span>							
					</div>

					<div class="col-md-6">							
						<span><b>Usuário Baixa: </b></span>
						<span id="usuario_baixa_dados"></span>							
					</div>


				</div>

				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					
					<div class="col-md-6">							
						<span><b>Cliente: </b></span>
						<span id="pessoa_dados"></span>							
					</div>

						<div class="col-md-6">							
						<span><b>Telefone: </b></span>
						<span id="telefone_dados"></span>							
					</div>

				</div>




				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					
					<div class="col-md-6">							
						<span><b>OBS: </b></span>
						<span id="obs_dados"></span>							
					</div>

				</div>


			</div>

			
		</div>
	</div>
</div>





<script type="text/javascript">var pag = "<?=$pag?>"</script>
<script src="js/ajax.js"></script>


<script type="text/javascript">
	$(document).ready(function() {
		calcular()
		listarServicos()

		$('.sel2').select2({
			dropdownParent: $('#modalForm')
		});
	});
</script>


<script type="text/javascript">
	function carregarImg() {
		var target = document.getElementById('target');
		var file = document.querySelector("#foto").files[0];


		var arquivo = file['name'];
		resultado = arquivo.split(".", 2);

		if(resultado[1] === 'pdf'){
			$('#target').attr('src', "img/pdf.png");
			return;
		}

		if(resultado[1] === 'rar' || resultado[1] === 'zip'){
			$('#target').attr('src', "img/rar.png");
			return;
		}



		var reader = new FileReader();

		reader.onloadend = function () {
			target.src = reader.result;
		};

		if (file) {
			reader.readAsDataURL(file);

		} else {
			target.src = "";
		}
	}
</script>



<script type="text/javascript">
	function valorData(dataInicio, dataFinal){
	 $('#data-inicial-caixa').val(dataInicio);
	 $('#data-final-caixa').val(dataFinal);	
	listar();
	
}
</script>



<script type="text/javascript">
	$('#data-inicial-caixa').change(function(){
			//$('#tipo-busca').val('');
			listar();
		});

		$('#data-final-caixa').change(function(){						
			//$('#tipo-busca').val('');
			listar();
		});	
</script>





<script type="text/javascript">
	function listar(){

	var dataInicial = $('#data-inicial-caixa').val();
	var dataFinal = $('#data-final-caixa').val();	
	var status = $('#buscar-contas').val();	
	
    $.ajax({
        url: 'paginas/' + pag + "/listar.php",
        method: 'POST',
        data: {dataInicial, dataFinal, status},
        dataType: "html",

        success:function(result){
            $("#listar").html(result);
            $('#mensagem-excluir').text('');
        }
    });
}
</script>



<script type="text/javascript">
	function buscarContas(status){
	 $('#buscar-contas').val(status);
	 listar();
	}
</script>




<script type="text/javascript">
	function baixar(id){
    $.ajax({
        url: 'paginas/' + pag + "/baixar.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Baixado com Sucesso") {                
                listar();                
            } else {
                    $('#mensagem-excluir').addClass('text-danger')
                    $('#mensagem-excluir').text(mensagem)
                }

        },      

    });
}

</script>


<script type="text/javascript">
	function calcular(){

		var quant = $('#quantidade').val();
		var produto = $('#produto').val();



    $.ajax({
        url: 'paginas/' + pag + "/calcular.php",
        method: 'POST',
        data: {produto, quant},
        dataType: "text",

        success: function (mensagem) {  

           $('#valor').val(mensagem)
        },      

    });
}
</script>



<script type="text/javascript">
	function listarServicos(){	
		var func = $("#funcionario").val();
		
		$.ajax({
			url: "paginas/agendamentos/listar-servicos.php",
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
$("#form-servico2").submit(function () {
		event.preventDefault();
		
		
		var formData = new FormData(this);	

fetch('paginas/servicos_agenda/salvar.php', {
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
	})
</script>