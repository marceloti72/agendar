<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");

$pag = 'servicos';

?>
<style>
	.tooltip-inner {
		background-color: #48D1CC;
		/* Amarelo */
		color: #000;
		/* Cor do texto */
	}
</style>
<?php

if($tipo_comissao == 'Porcentagem'){
		$item_comissao = '(%)';
	}else{
		$item_comissao = '(R$)';
	}


	//verificar se ele tem a permissão de estar nessa página
if(@$_SESSION['nivel_usuario'] != 'Administrador'){
	    echo "<script>window.location='agenda.php'</script>";
    }
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

<div class="bs-example widget-shadow" style="padding:15px" id="listar">
	
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
								<label for="exampleInputEmail1">Nome</label>
								<input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" required>    
							</div> 	
						</div>
						<div class="col-md-6">

							<div class="form-group">
								<label for="exampleInputEmail1">Valor</label>
								<input type="text" class="form-control" id="valor" name="valor" placeholder="Valor" >    
							</div> 	
						</div>
					</div>


					<div class="row">					
						

						<!-- <div class="col-md-6">
							
							<div class="form-group">
								<label for="exampleInputEmail1">Categoria</label>
								<select class="form-control sel2" id="categoria" name="categoria" style="width:100%;" > 

									<?php 
									$query = $pdo->query("SELECT * FROM cat_servicos where id_conta = '$id_conta' ORDER BY id asc");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									if($total_reg > 0){
										for($i=0; $i < $total_reg; $i++){
										foreach ($res[$i] as $key => $value){}
										echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
										}
									}else{
											echo '<option value="0">Cadastre uma Categoria</option>';
										}
									 ?>
									

								</select>   
							</div> 	
						</div> -->

						<div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Dias Retorno <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Informe aqui os dias de retorno para esse serviço. O cliente receberá um WhatsApp com uma pesquisa de satisfação junto com uma mensagem incentivando a agendar um novo serviço, será enviado o link de agendamento. 🚀" ></i></label>
								<input type="number" class="form-control" id="dias_retorno" name="dias_retorno" placeholder="Dias Retorno" >    
							</div> 	
						</div>					

					
							<!-- <div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Comissão <?php echo $item_comissao ?></label>
								<input type="text" class="form-control" id="comissao" name="comissao" placeholder="Valor Comissão" >    
							</div> 	
						</div> -->

						<div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Tempo Extimado <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Infome aqui o tempo que levar a conclusão do serviço. Isso impacta diretamente sua agenda 📆. " ></i></label>
								<input type="number" class="form-control" id="tempo" name="tempo" placeholder="Minutos" >    
							</div> 	
						</div>
					</div>

					

						<!-- <div class="row">
							<div class="col-md-8">						
								<div class="form-group"> 
									<label>Foto</label> 
									<input class="form-control" type="file" name="foto" onChange="carregarImg();" id="foto">
								</div>						
							</div>
							<div class="col-md-4">
								<div id="divImg">
									<img src="img/servicos/sem-foto.jpg"  width="80px" id="target">									
								</div>
							</div>

						</div> -->


					
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
					<div class="col-md-8">							
						<span><b>Categoria: </b></span>
						<span id="categoria_dados"></span>							
					</div>
					<div class="col-md-4">							
						<span><b>Valor: </b></span>
						<span id="valor_dados"></span>
					</div>					

				</div>


			

				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-6">							
						<span><b>Dias Retorno: </b></span>
						<span id="dias_retorno_dados"></span>							
					</div>

					<div class="col-md-6">							
						<span><b>Ativo: </b></span>
						<span id="ativo_dados"></span>							
					</div>
						

				</div>


				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-6">							
						<span><b>Comissão: </b></span>
						<span id="comissao_dados"></span>							
					</div>

				
						

				</div>

			


				<div class="row">
					<div class="col-md-12" align="center">		
						<img width="250px" id="target_mostrar">	
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

<script>
	$(function() {
		$('[data-toggle="tooltip"]').tooltip()
	})
</script>


