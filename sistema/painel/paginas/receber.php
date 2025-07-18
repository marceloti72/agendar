<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");

$pag = 'receber';

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
	<a class="btn btn-primary novo" onclick="inserir()" style = 'border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)'><i class="fa fa-plus" aria-hidden="true"></i> Nova Conta</a>
</div>

<div class="bs-example widget-shadow" style="padding:15px; background-color: #fff !important;">
    <div class="row" style="background-color: transparent !important;">
        <div class="col-md-5 col-12" style="margin-bottom:5px; background-color: transparent !important;">
            <div class="form-row align-items-center">
                <div class="col-auto">
                    <small><i title="Data de Vencimento Inicial" class="fa fa-calendar-o"></i></small>
                </div>
                <div class="col">
                    <input type="date" class="form-control" name="data-inicial" id="data-inicial-caixa" value="<?php echo $data_inicio_mes ?>" required>
                </div>
                <div class="col-auto">
                    <small><i title="Data de Vencimento Final" class="fa fa-calendar-o"></i></small>
                </div>
                <div class="col">
                    <input type="date" class="form-control" name="data-final" id="data-final-caixa" value="<?php echo $data_final_mes ?>" required>
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
			<form id="form">
				<div class="modal-body">

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Descrição</label>
								<input type="text" class="form-control" id="descricao" name="descricao" placeholder="Descrição da Conta" >    
							</div> 	
						</div>

						<div class="col-md-6">
							
							<div class="form-group">
								<label for="exampleInputEmail1">Cliente</label>
								<select class="form-control sel2" id="pessoa" name="pessoa" style="width:100%;" > 

									<?php 
									$query = $pdo->query("SELECT * FROM clientes where id_conta = '$id_conta'ORDER BY nome asc");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									
									echo '<option value="0">Selecione um Cliente</option>';

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

						<div class="col-md-4">

							<div class="form-group">
								<label for="exampleInputEmail1">Valor</label>
								<input type="text" class="form-control" id="valor" name="valor" placeholder="Valor" required>    
							</div> 	
						</div>					
						

						<div class="col-md-4">

							<div class="form-group">
								<label for="exampleInputEmail1">Vencimento</label>
								<input type="date" class="form-control" id="data_venc" name="data_venc" value="<?php echo $data_hoje ?>" >    
							</div> 	
						</div>	


						<div class="col-md-4">

							<div class="form-group">
								<label for="exampleInputEmail1">Pago Em</label>
								<input type="date" class="form-control" id="data_pgto" name="data_pgto"  >    
							</div> 	
						</div>

						

					</div>

					

					<div class="row">
						<div class="col-md-8">						
							<div class="form-group"> 
								<label>Arquivo</label> 
								<input class="form-control" type="file" name="foto" onChange="carregarImg();" id="foto">
							</div>						
						</div>
						<div class="col-md-4">
							<div id="divImg">
								<img src="img/contas/sem-foto.jpg"  width="80px" id="target">									
							</div>
						</div>

					</div>


					
					<input type="hidden" name="id" id="id">

					<br>
					<small><div id="mensagem" align="center"></div></small>
				</div>

				<div class="modal-footer">      
					<button type="submit" class="btn btn-primary"><i class="fa-regular fa-floppy-disk"></i> Salvar</button>
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
					
					<div class="col-md-8">							
						<span><b>Cliente: </b></span>
						<span id="pessoa_dados"></span>							
					</div>


				</div>




				<div class="row">
					<div class="col-md-12" align="center">	
						<a id="link_mostrar" target="_blank" title="Clique para abrir o arquivo!">	
							<img width="250px" id="target_mostrar">
						</a>	
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