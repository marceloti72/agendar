<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
$username = $_SESSION['username'];
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
if(@$_SESSION['nivel_usuario'] != 'administrador'){
	    echo "<script>window.location='agenda.php'</script>";
    }
?>

<div class="row">

<form method="post" id="form-config2">
				<div class="modal-body">

					<div class="row">
						<div class="col-md-6">
							<button type="button" id="btnCopiarUrl" style="background-color: dimgrey;color: white;border: 0;padding: 6px; box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)">
								<i class="fa-regular fa-copy"></i> Copiar Link do Site
							</button>
							<span id="mensagemCopia" style="margin-left: 10px; font-style: italic; color: green;"></span>
						</div>
					</div>					
					<div class="row">					
						
						<!-- <div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Instagram</label>
								<input type="text" class="form-control" id="instagram_sistema" name="instagram_sistema" placeholder="Link do Perfil no Instagram" value="<?php echo $instagram_sistema ?>">   
							</div> 	
						</div> -->					

						
					</div>

					<div class="row">
						<div class="col-md-12">
						<div class="form-group">
								<label for="exampleInputEmail1">Texto Rodapé<small>(255) Caracteres</small> <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Texto com tamanho para até 255 caracteres e será apresentado no rodapé de todo o site!." style="color: blue;"></i></label>
								<input maxlength="255" type="text" class="form-control" id="texto_rodape" name="texto_rodape" placeholder="Texto para o Rodapé do site" value="<?php echo $texto_rodape ?>">   
							</div> 
						</div>
					</div>	
						
					</div>

					<div class="row">						

							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Logo do site(*PNG)</label> 
									<input class="form-control" type="file" name="foto-logo" onChange="carregarImgLogo();" id="foto-logo">
								</div>						
							</div>
							<div class="col-md-2">
								<?php
								// Define o caminho base da imagem e o ID da conta
								$caminho_base = "../img/";								

								// Constrói o nome do arquivo completo para a logo do cliente
								$nome_arquivo_logo = "logo" . $id_conta . ".png";
								$caminho_completo_logo = $caminho_base . $nome_arquivo_logo;

								// Define o caminho da imagem alternativa
								$caminho_logo_alternativa = $caminho_base . "sem_logo.png";

								// Checa se o arquivo de logo do cliente existe
								if (file_exists($caminho_completo_logo)) {
									// Se a logo existe, use o caminho dela
									$src_logo = $caminho_completo_logo;
								} else {
									// Se a logo não existe, use o caminho da imagem alternativa
									$src_logo = $caminho_logo_alternativa;
								}
								?>

								<div id="divImg">
									<img src="<?php echo $src_logo; ?>" width="80px" id="target-logo">
								</div>
								
							</div>							

							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Logo dos Relatórios (*Jpg)</label> 
									<input class="form-control" type="file" name="foto-logo-rel" onChange="carregarImgLogoRel();" id="foto-logo-rel">
								</div>						
							</div>
							<div class="col-md-2">
								<?php
								// Define o caminho base da imagem e o ID da conta
								$caminho_base = "../img/";								

								// Constrói o nome do arquivo completo para a logo do cliente
								$nome_arquivo_rel = "logo_rel" . $id_conta . ".jpg";
								$caminho_completo_rel = $caminho_base . $nome_arquivo_rel;

								// Define o caminho da imagem alternativa
								$caminho_rel_alternativa = $caminho_base . "sem_logo.png";

								// Checa se o arquivo de logo do cliente existe
								if (file_exists($caminho_completo_rel)) {
									// Se a logo existe, use o caminho dela
									$src_rel = $caminho_completo_rel;
								} else {
									// Se a logo não existe, use o caminho da imagem alternativa
									$src_rel = $caminho_rel_alternativa;
								}
								?>

								<div id="divImg">
									<img src="<?php echo $src_rel; ?>" width="80px" id="target-logo-rel">
								</div>

								<!-- <div id="divImg">
									<img src="../img/logo_rel<?php echo $id_conta?>.jpg"  width="80px" id="target-logo-rel">									
								</div> -->
							</div>

						</div>

						<div class="row">

							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Ícone do Site (*png)</label> 
									<input class="form-control" type="file" name="foto-icone-site" onChange="carregarImgIconeSite();" id="foto-icone-site">
								</div>						
							</div>
							<div class="col-md-2">
								<?php 
								$caminho_base2 = "../../images/";
								// Constrói o nome do arquivo completo para a logo do cliente
								$nome_arquivo_icone = "favicon" . $id_conta . ".png";
								$caminho_completo_icone = $caminho_base2 . $nome_arquivo_icone;

								// Define o caminho da imagem alternativa
								$caminho_icone_alternativa = $caminho_base2 . "sem_logo.png";

								// Checa se o arquivo de logo do cliente existe
								if (file_exists($caminho_completo_icone)) {
									// Se a logo existe, use o caminho dela
									$src_icone = $caminho_completo_icone;
								} else {
									// Se a logo não existe, use o caminho da imagem alternativa
									$src_icone = $caminho_icone_alternativa;
								}
								?>

								<div id="divImg">
									<img src="<?php echo $src_icone; ?>" width="80px" id="target-icone-site">
								</div>
								<!-- <div id="divImg">
									<img src="../../images/favicon<?php echo $id_conta?>.png"  width="50px" id="target-icone-site">									
								</div> -->
							</div>

							<!-- <div class="col-md-4">						
								<div class="form-group"> 
									<label>Imagem principal do site<small>(1500x1000) (*jpg)</small></label> 
									<input class="form-control" type="file" name="foto-banner-index" onChange="carregarImgBannerIndex();" id="foto-banner-index">
								</div>						
							</div>
							<div class="col-md-2">
								<div id="divImg">
									<img src="../../images/banner<?php echo $id_conta?>.jpg"  width="80px" id="target-banner-index">									
								</div>
							</div> -->

						</div>								

						<div class="col-md-6">
						<div class="form-group">
								<label for="exampleInputEmail1">Mapa no Site <small>(Url incorporada)</small> <i class="bi bi-map"></i> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Vá no Google Maps digite seu endereço e coloque aqui o codigo de incorporação, assim o mapa do seu endereço aparecerá no seu site." style="color: blue;"></i></label>
								<input type="text" class="form-control" id="mapa" name="mapa" placeholder="" value='<?php echo $mapa ?>'>  
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
							<input class="form-check-input" type="checkbox" role="switch" id="assinaturas2" name="assinaturas2" value="Sim" <?php if($assinaturas2 == 'Sim'){ echo 'checked'; } ?>>
							<label class="form-check-label" for="servicos2">Clube do Assinante</label>
							</div>
							<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch" id="depoimentos2" name="depoimentos2" value="Sim" <?php if($depoimentos2 == 'Sim'){ echo 'checked'; } ?>>
							<label class="form-check-label" for="depoimentos2">Depoimentos</label>
						</div>
											
						

					<br>
					<small><div id="mensagem-config2" align="center"></div></small>
				</div>
				<div class="modal-footer">      
					<button type="submit" class="btn btn-primary"><i class="fa-regular fa-floppy-disk"></i> Salvar</button>
				</div>
			</form>	

</div>


<script>
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
	
</script>

<script>
	document.addEventListener('DOMContentLoaded', (event) => { // Garante que o DOM esteja pronto

const btnCopiar = document.getElementById('btnCopiarUrl');
const mensagemCopia = document.getElementById('mensagemCopia');
const urlParaCopiar = 'https://markai.skysee.com.br/site.php?u=<?php echo $username?>';

if (btnCopiar) { // Verifica se o botão existe
	btnCopiar.addEventListener('click', () => {
		mensagemCopia.textContent = ''; // Limpa mensagem anterior

		// --- Método Moderno: Clipboard API (Requer HTTPS ou localhost) ---
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(urlParaCopiar).then(() => {
				/* Sucesso */
				mensagemCopia.textContent = 'Link Copiado!';
				mensagemCopia.style.color = 'green';
				console.log('URL copiada com sucesso via Clipboard API');

				// Limpa a mensagem após alguns segundos
				setTimeout(() => {
					mensagemCopia.textContent = '';
				}, 2500);

			}).catch(err => {
				/* Erro na API moderna - tenta o método legado */
				console.error('Falha ao copiar com Clipboard API:', err);
				copiarComExecCommand(urlParaCopiar, mensagemCopia);
			});
		} else {
			// --- Método Legado (Fallback para HTTP ou navegadores antigos) ---
			console.warn('Clipboard API não disponível/segura. Usando método legado execCommand.');
			copiarComExecCommand(urlParaCopiar, mensagemCopia);
		}
	});
}

});

// Função separada para o método legado (execCommand)
function copiarComExecCommand(texto, elementoMensagem) {
const textArea = document.createElement('textarea');
textArea.value = texto;

// Estilos para esconder o textarea sem causar scroll
textArea.style.position = 'fixed';
textArea.style.top = '-9999px';
textArea.style.left = '-9999px';

document.body.appendChild(textArea);
textArea.focus();
textArea.select(); // Seleciona o conteúdo

try {
	const successful = document.execCommand('copy'); // Tenta copiar
	if (successful) {
		elementoMensagem.textContent = 'Link Copiado!';
		elementoMensagem.style.color = 'green';
		console.log('URL copiada com sucesso via execCommand');
	} else {
		elementoMensagem.textContent = 'Falha ao copiar.';
		elementoMensagem.style.color = 'red';
		console.error('Falha ao copiar com execCommand (retornou false)');
	}
} catch (err) {
	elementoMensagem.textContent = 'Erro ao copiar.';
	elementoMensagem.style.color = 'red';
	console.error('Erro ao usar execCommand:', err);
}

document.body.removeChild(textArea); // Remove o textarea temporário

// Limpa a mensagem após alguns segundos
 setTimeout(() => {
	elementoMensagem.textContent = '';
}, 2500);
}

</script>

