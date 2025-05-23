<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");

$pag = 'minhas_comissoes';



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

$id_func = $_SESSION['id_usuario'];
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
        </div><br>
		<div class="col-md-2"  align="center">	
			<div > 
				<form action="rel/rel_comissoes_class.php" target="_blank" method="POST">

					<input type="hidden" name="dataInicial" id="dataInicial">
					<input type="hidden" name="dataFinal" id="dataFinal">
					<input type="hidden" name="pago" id="pago_rel">
					<input type="hidden" name="funcionario" value="<?php echo $id_func ?>">

				<button type="submit" class="btn btn-primary novo link-botao"><i class="fa fa-file-pdf-o"></i> <span >Gerar Relatório</span></button>

				</form>
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

	

	<hr>
	<div id="listar">

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
						<span><b>Fornecedor: </b></span>
						<span id="pessoa_dados"></span>							
					</div>

					<div class="col-md-6">							
						<span><b>Funcionário: </b></span>
						<span id="nome_func_dados"></span>							
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


	$('#dataInicial').val(dataInicial);
	$('#dataFinal').val(dataFinal);
	$('#pago_rel').val(status);


	
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


