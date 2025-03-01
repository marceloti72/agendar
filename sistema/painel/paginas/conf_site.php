<?php 
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'configuracoes';
$data_atual = date('Y-m-d');
?>
<style>
	.tooltip-inner {
		background-color: #48D1CC; /* Amarelo */
		color: #000; /* Cor do texto */
	}
</style>
<?php 

//verificar se ele tem a permissão de estar nessa página
if(@$configuracoes == 'ocultar'){
	echo "<script>window.location='../index.php'</script>";
	exit();
}

?>

<div class="row">

<form method="post" id="form-config2">
				<div class="modal-body">

					<div class="row">					
						
						<div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Instagram</label>
								<input type="text" class="form-control" id="instagram_sistema" name="instagram_sistema" placeholder="Link do Perfil no Instagram" value="<?php echo $instagram_sistema ?>">   
							</div> 	
						</div>

					<div class="col-md-6">
						<div class="form-group">
								<label for="exampleInputEmail1">Mapa no Site <small>(Url incorporada)</small> <i class="bi bi-map"></i> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Vá no Google Maps digite seu endereço e coloque aqui o codigo de incorporação, assim o mapa do seu endereço aparecerá no seu site." style="color: blue;"></i></label>
								<input type="text" class="form-control" id="mapa" name="mapa" placeholder="" value='<?php echo $mapa ?>'>  
							</div> 	
						</div>		


						
					</div>

					<div class="row">
						<div class="col-md-12">
						<div class="form-group">
								<label for="exampleInputEmail1">Texto Rodapé<small>(255) Caracteres</small> <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Texto com tamanho para até 255 caracteres e será apresentado no rodapé de todo o site!." style="color: blue;"></i></label>
								<input maxlength="255" type="text" class="form-control" id="texto_rodape" name="texto_rodape" placeholder="Texto para o Rodapé do site" value="<?php echo $texto_rodape ?>">   
							</div> 
						</div>
					</div>


					<div class="row">
						<div class="col-md-12">
						<div class="form-group">
								<label for="exampleInputEmail1">Texto Sobre <small>(600) Caracteres</small> <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Aqui voce vai colocar um texto escrevendo sobre sua empresa." style="color: blue;"></i></label>
								<input maxlength="255" type="text" class="form-control" id="texto_sobre" name="texto_sobre" placeholder="Texto para a área Sobre a empresa no site" value="<?php echo $texto_sobre ?>">   
							</div> 
						</div>
					</div>
						
					</div>

					<div class="row">

						

							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Logo (*PNG)</label> 
									<input class="form-control" type="file" name="foto-logo" onChange="carregarImgLogo();" id="foto-logo">
								</div>						
							</div>
							<div class="col-md-2">
								<div id="divImg">
									<img src="../img/logo<?php echo $id_conta?>.png"  width="80px" id="target-logo">									
								</div>
							</div>


							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Ícone (*Png)</label> 
									<input class="form-control" type="file" name="foto-icone" onChange="carregarImgIcone();" id="foto-icone">
								</div>						
							</div>
							<div class="col-md-2">
								<div id="divImg">
									<img src="../img/icon<?php echo $id_conta?>.png"  width="50px" id="target-icone">									
								</div>
							</div>

						</div>



						<div class="row">

							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Logo Relatório (*Jpg)</label> 
									<input class="form-control" type="file" name="foto-logo-rel" onChange="carregarImgLogoRel();" id="foto-logo-rel">
								</div>						
							</div>
							<div class="col-md-2">
								<div id="divImg">
									<img src="../img/logo_rel<?php echo $id_conta?>.jpg"  width="80px" id="target-logo-rel">									
								</div>
							</div>



							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Ícone Site (*png)</label> 
									<input class="form-control" type="file" name="foto-icone-site" onChange="carregarImgIconeSite();" id="foto-icone-site">
								</div>						
							</div>
							<div class="col-md-2">
								<div id="divImg">
									<img src="../../images/favicon<?php echo $id_conta?>.png"  width="50px" id="target-icone-site">									
								</div>
							</div>



						</div>



						<div class="row">

							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Imagem Área Sobre (Site) (*png)</label> 
									<input class="form-control" type="file" name="foto-sobre" onChange="carregarImgSobre();" id="foto-sobre">
								</div>						
							</div>
							<div class="col-md-2">
								<div id="divImg">
									<img src="../../images/foto-sobre<?php echo $id_conta?>.png"  width="80px" id="target-sobre">									
								</div>
							</div>



							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Imagem Banner Index <small>(1500x1000) (*jpg)</small></label> 
									<input class="form-control" type="file" name="foto-banner-index" onChange="carregarImgBannerIndex();" id="foto-banner-index">
								</div>						
							</div>
							<div class="col-md-2">
								<div id="divImg">
									<img src="../../images/banner<?php echo $id_conta?>.jpg"  width="80px" id="target-banner-index">									
								</div>
							</div>



						</div>


						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
								<label for="exampleInputEmail1">Url do Vídeo Index</label>
								 	<input type="text" class="form-control" id="url_video" name="url_video" value="<?php echo $url_video ?>" placeholder="Url do Youtube Incorporada">    
							</div> 
							</div>	

							
						</div>

						<div class="row">
							<label>Habilitar no Site:</label>
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch" id="agendamentos2" name="agendamentos2" value="Sim"<?php if($agendamentos2 == 'Sim'){ echo 'checked'; } ?>>
							<label class="form-check-label" for="agendamento2">Agendamentos</label>
							</div>
							<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch" id="produtos2" name="produtos2" value="Sim" <?php if($produtos2 == 'Sim'){ echo 'checked'; } ?>>
							<label class="form-check-label" for="produtos2">Produtos</label>
							</div>
							<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch" id="servicos2" name="servicos2" value="Sim" <?php if($servicos2 == 'Sim'){ echo 'checked'; } ?>>
							<label class="form-check-label" for="servicos2">Serviços</label>
							</div>
							<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch" id="depoimentos2" name="depoimentos2" value="Sim" <?php if($depoimentos2 == 'Sim'){ echo 'checked'; } ?>>
							<label class="form-check-label" for="depoimentos2">Depoimentos</label>
						</div>
							<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch" id="carrossel" name="carrossel" value="Sim" <?php if($carrossel == 'Sim'){ echo 'checked'; } ?>>
							<label class="form-check-label" for="depoimentos">Textos Carrossel</label>
						</div>

					
						

					<br>
					<small><div id="mensagem-config2" align="center"></div></small>
				</div>
				<div class="modal-footer">      
					<button type="submit" class="btn btn-primary">Salvar Dados</button>
				</div>
			</form>	

</div>


<!-- Modal Inserir-->
<div class="modal" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header text-white" style="background-color: #4682B4;">
				<h4 class="modal-title">Textos para o Carrossel</h4>
				<button id="btn-fechar" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<form id="form">
			<div class="modal-body">

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="exampleInputEmail1">Título <small>(Até 25 Caracteres)</small></label>
								<input maxlength="25" type="text" class="form-control" id="titulo" name="titulo" placeholder="Título do Texto" >    
							</div> 	
						</div>
						
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="exampleInputEmail1">Descrição <small>(Até 255 Caracteres)</small></label>
								<input maxlength="255" type="text" class="form-control" id="descricao" name="descricao" placeholder="Descrição do Texto" >    
							</div> 	
						</div>
						
					</div><br><br>

					<div id="listar"></div>

					<input type="hidden" name="id" id="id">	

					<br>
					<small><div id="mensagem" align="center"></div></small>
				</div>


				<div class="modal-footer">    
			    	<button type="button" data-bs-dismiss="modal" class="btn btn-danger">Sair</button>  

					<button type="submit" class="btn btn-primary">Salvar</button> 
					
				</div>
			</form>
			<script type="text/javascript">var pag = "textos_index";</script>
            <script src="js/ajax.js"></script>

							
		</div>
	</div>
</div>





<script>
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
	
</script>

<script>
    document.getElementById('carrossel').addEventListener('change', function() {
        if (this.checked) {
            $('#modalForm').modal('show'); // Abre o modal
        }
    });
</script>