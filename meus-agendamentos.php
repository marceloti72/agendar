<?php 
session_start();
if(!empty($_SESSION['telefone_user'])){
	$telefone = $_SESSION['telefone_user'];
}else{
	$telefone = @$_POST['telefone_user'];
	$_SESSION['telefone_user'] = $telefone;
}

$id_conta = @$_SESSION['id_conta'];
require_once("cabecalho2.php");
$data_atual = date('Y-m-d');


if ($telefone == '') {
    // Exibe um formulário para o usuário inserir o telefone
	$id_cliente = '';
    echo '
    <form method="POST" action="">
	<div style="margin-left: 20px;margin-bottom: 10px;">
        <label for="telefone">Por favor, insira seu telefone:</label>
        <input type="text" id="telefone" name="telefone_user" required>
        <button type="submit">Enviar</button>
		</div>
    </form>';
}else {
	$query = $pdo->prepare("SELECT * FROM clientes WHERE telefone = :telefone AND id_conta = :id_conta");
	$query->execute(['telefone' => $telefone, 'id_conta' => $id_conta]);
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	
	if (count($res) > 0) {
		$id_cliente = $res[0]['id'];
		// Continue com sua lógica
	} else {
		echo "<script>window.alert('Nenhum cliente encontrado com este telefone.')</script>";
		// echo "<script>window.location='agendamentos.php'</script>";
	}
}



?>
<style type="text/css">
	.sub_page .hero_area {
		min-height: auto;
	}
</style>

</div>

<div class="footer_section" style="background: #585757; ">
	<div class="container">
		<div class="footer_content " >
			
<?php
$query = $pdo->query("SELECT * FROM agendamentos where cliente = '$id_cliente' and status = 'Agendado' and id_conta = '$id_conta' ORDER BY data asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
$id = $res[$i]['id'];
$funcionario = $res[$i]['funcionario'];
$cliente = $res[$i]['cliente'];
$hora = $res[$i]['hora'];
$data = $res[$i]['data'];
$usuario = $res[$i]['usuario'];
$data_lanc = $res[$i]['data_lanc'];
$obs = $res[$i]['obs'];
$status = $res[$i]['status'];
$servico = $res[$i]['servico'];
$ref_pix = $res[$i]['ref_pix'];


$dataF = implode('/', array_reverse(explode('-', $data)));
$horaF = date("H:i", strtotime($hora));


if($status == 'Concluído'){		
	$classe_linha = '';
}else{		
	$classe_linha = 'text-muted';
}



if($status == 'Agendado'){
	$imagem = 'icone-relogio.png';
	$classe_status = '';	
}else{
	$imagem = 'icone-relogio-verde.png';
	$classe_status = 'ocultar';
}

$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario' and id_conta = '$id_conta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_usu = $res2[0]['nome'];
}else{
	$nome_usu = 'Sem Usuário';
}

$query2 = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_func = $res2[0]['nome'];
}else{
	$nome_func = 'Sem Usuário';
}


$query2 = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_serv = $res2[0]['nome'];
	$valor_serv = $res2[0]['valor'];
}else{
	$nome_serv = 'Não Lançado';
	$valor_serv = '';
}


$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente' and id_conta = '$id_conta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_cliente = $res2[0]['nome'];
	$total_cartoes = $res2[0]['cartoes'];
}else{
	$nome_cliente = 'Sem Cliente';
	$total_cartoes = 0;
}

if($total_cartoes >= $quantidade_cartoes and $status == 'Agendado'){
	$ocultar_cartoes = '';
}else{
	$ocultar_cartoes = 'ocultar';
}

//retirar aspas do texto do obs
$obs = str_replace('"', "**", $obs);

?>
			
<div class="list-group" >

  <div class="list-group-item list-group-item-action flex-column align-items-start " style="margin-bottom: 10px; box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4);">
    <div class="d-flex w-100 justify-content-between">
      <h6 class="mb-1"><i class="fa fa-calendar" aria-hidden="true"></i> Data: <?php echo $dataF ?>  <i class="fa fa-clock-o text-success" aria-hidden="true" style="margin-left: 10px"></i> Hora: <?php echo $horaF ?></h6> 

      <small><a href="#" onclick="excluir('<?php echo $id ?>', '<?php echo $nome_cliente ?>', '<?php echo $dataF ?>', '<?php echo $horaF ?>', '<?php echo $nome_serv ?>', '<?php echo $nome_func ?>')"><i class="fa fa-trash text-danger" aria-hidden="true"></i> </a></small>     
    </div>
	<div class="d-flex w-100 justify-content-between">
        <p class="mb-1"><small><b>Profissional:</b> <?php echo $nome_func ?></small></p>
	</div>
	<div class="d-flex w-100 justify-content-between">
        <small><b>Serviço:</b> <?php echo $nome_serv ?> <b>Valor:</b> R$ <?php echo $valor_serv ?></small>
	</div>
  </div>
 
</div>



<?php
}

}else{
	echo 'Nenhum horário para essa Data!';
}

?>


<br>

<div id="listar-cartoes" align="center">

</div>

		</div>


	</div>





</div>




<?php require_once("rodape3.php") ?>


  <!-- Modal Excluir -->
  <div class="modal fade" id="modalExcluir" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header text-white" style="background-color: #4682B4;">
          <h5 class="modal-title" id="exampleModalLabel"><small>Cancelar Agendamento</small></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" id="btn-fechar-excluir">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <form id="form-excluir">
      <div class="modal-body">

      	Deseja cancelar o Agendamento?

      	<span id="msg-excluir"></span>
                   
            <input type="hidden" name="id" id="nome_excluir">
             <input type="hidden" name="id" id="data_excluir">
              <input type="hidden" name="id" id="hora_excluir">
               <input type="hidden" name="id" id="servico_excluir">
                <input type="hidden" name="id" id="func_excluir">
                <input type="hidden" name="id" id="id_excluir">

          <br>
          <small><div id="mensagem-excluir" align="center"></div></small>	  

			<div class="progress mt-4" id="progresso" style="display: none;">
			    <div class="progress-bar" id="barra_progresso" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>         
				
			</div>

           <div align="right"><button type="submit" class="btn btn-danger">Confirmar</button></div>
        </div>

      
      </form>

      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
  



<script type="text/javascript">
	$(document).ready(function() {
		$('#telefone').mask('(00) 00000-0000');

		var tel = "<?=$telefone?>";
		listarCartoes(tel)
	});
</script>


<script type="text/javascript">
	function excluir(id, nome, data, hora, servico, func){

		
		$('#id_excluir').val(id);	

		$('#nome_excluir').val(nome);	
		$('#data_excluir').val(data);	
		$('#hora_excluir').val(hora);	
		$('#servico_excluir').val(servico);	
		$('#func_excluir').val(func);		

		$('#modalExcluir').modal('show');
		
	}
</script>



<script>

	$("#form-excluir").submit(function () {
		event.preventDefault();
		var formData = new FormData(this);

		$('#progresso').show(); //Exibe a barra
		$('#barra_progresso').css('width', '0%').text('0%'); // Reset da barra

		$.ajax({
			url: "ajax/excluir.php",
			type: 'POST',
			data: formData,
			contentType: false,
			cache: false,
			processData:false,					
			xhr: function() {  // Função personalizada para o XHR
				var myXhr = $.ajaxSettings.xhr();
				if (myXhr.upload) { // Verifica se a propriedade de upload existe
					// Evento de progresso do upload
					myXhr.upload.addEventListener('progress', function(e) {
						if (e.lengthComputable) {
							var percentComplete = (e.loaded / e.total) * 100;
							$('#barra_progresso').css('width', percentComplete + '%').text(Math.round(percentComplete) + '%');
						}
					}, false);
				}
				return myXhr;
			},

			success: function (mensagem) {			
				$('#mensagem-excluir').text('');
				$('#mensagem-excluir').removeClass()
				if (mensagem.trim() == "Cancelado com Sucesso") {   
					$('#progresso').hide(); 
				    $('#btn-fechar-excluir').click();     	          
					$('#mensagem').text(mensagem)
					

					Swal.fire({
						title: 'Cancelado!',
						text: 'Cancelamento realizado com sucesso.',
						icon: 'success',
						timer: 3000,
						width: '600px', // Janela maior
						showConfirmButton: false,
						
					}).then(() => {
						window.location.href = 'meus-agendamentos.php';
					});							
								
				} else {
					//$('#mensagem').addClass('text-danger')
					$('#progresso').hide();
					$('#mensagem-excluir').text(mensagem)
				}

			},

			cache: false,
			contentType: false,
			processData: false,

		});

	});

</script>


<script type="text/javascript">
	
	function listarCartoes(tel){

			$.ajax({
			url: "ajax/listar-cartoes.php",
			method: 'POST',
			data: {tel},
			dataType: "text",

			success:function(result){
				$("#listar-cartoes").html(result);
			}
		});
		
			}
</script>



