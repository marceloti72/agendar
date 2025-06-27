<?php	
@session_start();
$id_conta = $_SESSION['id_conta'];

$query = $pdo->query("SELECT * FROM agendamentos where data >= curDate() and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);

//verificar se ele tem a permissão de estar nessa página
// if(@$calendario == 'ocultar'){
// 	echo "<script>window.location='../index.php'</script>";
// 	exit();
// }

if(@$_SESSION['nivel_usuario'] != 'Administrador'){
	    echo "<script>window.location='agenda.php'</script>";
    }
?>
<style>
	@media (max-width: 768px) {
		.col-lg-12{
		height: 350px;
	    }
    }
</style>

<!-- FullCalendar -->
<link href='paginas/calendario/css/fullcalendar.css' rel='stylesheet' />
<link href='paginas/calendario/css/fullcalendar.print.min.css' rel='stylesheet' media='print' />
<!-- Custom CSS Calendario -->
<link href='paginas/calendario/css/calendar.css' rel='stylesheet' />
		
   
		<!-- Page Content -->
		<div style="margin:0 !important; background: #FFF">
			<div class="row">
				<div class="col-lg-12 text-center">
					<p class="lead"></p>
					<div id="calendar" class="col-centered">
					</div>
				</div>
			</div>
			<!-- /.row -->

			<!-- Valida data dos Modals -->
			<script type="text/javascript">
				function validaForm(erro) {
					if(erro.inicio.value>erro.termino.value){
						alert('Data de Inicio deve ser menor ou igual a de termino.');
						return false;
					}else if(erro.inicio.value==erro.termino.value){
						alert('Defina um horario de inicio e termino.(24h)');
						return false;
					}
				}
			</script>


			<!-- Modal Adicionar Evento -->
			<?php include ('paginas/calendario/evento/modal/modalAdd.php'); ?>
			
			
			<!-- Modal Editar/Mostrar/Deletar Evento -->
			<?php include ('paginas/calendario/evento/modal/modalEdit.php'); ?>

		</div>

		
				
		<!-- FullCalendar -->
		<script src='paginas/calendario/js/moment.min.js'></script>
		<script src='paginas/calendario/js/fullcalendar.min.js'></script>
		<script src='paginas/calendario/locale/pt-br.js'></script>
		<?php include ('paginas/calendario/calendario.php'); ?>
		
