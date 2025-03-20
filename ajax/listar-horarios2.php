<?php 
require_once("../sistema/conexao2.php");
@session_start();
$usuario = @$_SESSION['id_usuario'];

$funcionario = @$_POST['funcionario'];
$data = @$_POST['data'];
$hora_rec = @$_POST['hora'];
$nome = @$_POST['nome'];
$whatsapp = @$_POST['telefone'];

$hoje = date('Y-m-d');
$hora_atual = date('H:i:s');

if(strtotime($data) < strtotime($hoje)){
	echo '000';
	exit();
}

if($funcionario == ""){
	
	exit();
}

//verificar se possui essa data nos dias bloqueio geral
$query = $pdo->query("SELECT * FROM dias_bloqueio where funcionario = '0' and data = '$data' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
	echo 'Não estaremos funcionando nesta Data!';
	exit();
}

//verificar se possui essa data nos dias bloqueio func
$query = $pdo->query("SELECT * FROM dias_bloqueio where funcionario = '$funcionario'  and data = '$data' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
		echo 'Este Profissional não irá trabalhar nesta Data, selecione outra data ou escolhar outro Profissional!';
		exit();
}


$diasemana = array("Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sabado");
$diasemana_numero = date('w', strtotime($data));
$dia_procurado = $diasemana[$diasemana_numero];

//percorrer os dias da semana que ele trabalha
$query = $pdo->query("SELECT * FROM dias where funcionario = '$funcionario' and dia = '$dia_procurado' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) == 0){
		echo 'Este Funcionário não trabalha neste Dia!';
	exit();
}else{
	$inicio = $res[0]['inicio'];
	$final = $res[0]['final'];
	$inicio_almoco = $res[0]['inicio_almoco'];
	$final_almoco = $res[0]['final_almoco'];
}

$query = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$intervalo = $res[0]['intervalo'];


?>
<div class="row">

	<?php 
	$i = 0;
	while (strtotime($inicio) <= strtotime($final)){
	
	if(strtotime($inicio) >= strtotime($inicio_almoco) and strtotime($inicio) < strtotime($final_almoco)){
		$hora_minutos = strtotime("+$intervalo minutes", strtotime($inicio));			
		$inicio = date('H:i:s', $hora_minutos);
	}else{
			
				$hora = $inicio;
				$horaF = date("H:i", strtotime($hora));
				$dataH = '';

				//validar horario
$query2 = $pdo->query("SELECT * FROM agendamentos where data = '$data' and hora = '$hora' and funcionario = '$funcionario' and id_conta = '$id_conta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);

$total_reg2 = @count($res2);
if($total_reg2 == 0 || strtotime($hora_rec) == strtotime($hora)){
	$hora_agendada = '';
	$texto_hora = '';

	if(strtotime($hora_rec) == strtotime($hora)){
		$checado = 'checked';
	}else{
		$checado = '';
	}

	if(strtotime($hora) < strtotime($hora_atual) and strtotime($data) == strtotime($hoje)){
		$esconder = 'none';
	}else{
		$esconder = '';
				
		//VERIFICAR NA TABELA HORARIOS AGD SE TEM O HORARIO NESSA DATA
		$query_agd = $pdo->query("SELECT * FROM horarios_agd where data = '$data' and funcionario = '$funcionario' and horario = '$hora' and id_conta = '$id_conta'");
		$res_agd = $query_agd->fetchAll(PDO::FETCH_ASSOC);
		if(@count($res_agd) > 0){
			$esconder = 'none';			
		}else{
			$esconder = '';
			$i += 1;
		}

	}



	if(strtotime($dataH) != strtotime($data) and $dataH != "" and $dataH != "null"){
		continue;
	}	
				?>
				<style>
					.form-check-input:checked {
						background-color: blue !important;
						border-color: blue !important;
					}
					.form-check-input {
						background-color: white !important;
						border-color: #ccc !important;
					}
				</style>
				

				<div class="col-3" style='display: <?php echo $esconder ?>'>
					<div class="form-check form-switch">
					  <input class="form-check-input" type="radio" role="switch" id="flexSwitchCheckDefault" name="hora" value="<?php echo $hora ?>" <?php echo $hora_agendada ?> style="width:17px; height: 17px; " required <?php echo $checado ?>>
					  <label class="form-check-label <?php echo $texto_hora ?>" for="flexSwitchCheckDefault">
					    <?php echo $horaF ?>
					  </label>
					</div>
				</div> 

				<?php 
				
		}

		$hora_minutos = strtotime("+$intervalo minutes", strtotime($inicio));			
$inicio = date('H:i:s', $hora_minutos);

	}
}
	
if ($i == 0) {
    echo <<<HTML
    <script>
    Swal.fire({
        title: 'Horários Indisponíveis',
        text: 'Não temos mais horários disponíveis com este funcionário para essa data! Deseja se registar no encaixe? Avisaremos por WhatsApp se houver alguma desistência.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não',
        reverseButtons: true, // Inverte a ordem dos botões (opcional)
        allowOutsideClick: false, // Impede fechar clicando fora (importante)
    }).then((result) => {
        if (result.isConfirmed) {
            // Abrir o modal de encaixe
            $('#modalEncaixe').modal('show');
        }
    });
    </script>
HTML;
}
	?>


</div>

<div class="modal fade" id="modalEncaixe" tabindex="-1" role="dialog" aria-labelledby="modalEncaixeLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header text-white" style="background-color: #4682B4;">
				<h4 class="modal-title">Registrar Encaixe<span id="titulo_servico"></span>  </h4>
				<a id="btn-fechar-servico" type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true">&times;</span>
				</a>
			</div>
            <div class="modal-body">
                <form id="formEncaixe">
                    <div class="form-group">
                        <label for="nomeEncaixe" style="color: black;">Nome</label>
                        <input type="text" class="form-control" id="nomeEncaixe" name="nomeEncaixe" value="<?php echo $nome?>" placeholder="Nome" required>
                    </div>
                    <div class="form-group">
                        <label for="whatsappEncaixe"  style="color: black;">WhatsApp</label>
                        <input type="tel" class="form-control" id="whatsappEncaixe" name="whatsappEncaixe" value="<?php echo $whatsapp?>" required placeholder="(xx) xxxxx-xxxx"> </div>
                    <input type="hidden" name="dataEncaixe" id="dataEncaixe" value="<?php echo isset($_POST['data']) ? htmlspecialchars($_POST['data']) : ''; ?>">
                    <input type="hidden" name="profissionalEncaixe" id="profissionalEncaixe" value="<?php echo isset($_POST['funcionario']) ? htmlspecialchars($_POST['funcionario']) : ''; ?>">
                     <input type="hidden" name="id_conta" value="<?php echo $id_conta; ?>">
                </form>
            </div>
            <div class="modal-footer">
                <a type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</a>
                <a type="button" class="btn btn-primary" onclick="cadastrarEncaixe()">Registrar</a>
            </div>
        </div>
    </div>
</div>

<script>
function cadastrarEncaixe() {
    // Validação básica dos campos (pode ser aprimorada)
    var nome = $("#nomeEncaixe").val();
    var whatsapp = $("#whatsappEncaixe").val();

     if (!nome || !whatsapp) {
        Swal.fire('Erro', 'Preencha todos os campos!', 'error');
        return;
    }
    //Verificação se é um numero de WhatsApp Válido (Melhorado)
      var whatsappRegex = /^\(\d{2}\)\s?\d{4,5}-\d{4}$/;  //Formato (xx) xxxx-xxxx ou (xx) xxxxx-xxxx
     if (!whatsappRegex.test(whatsapp)) {
        Swal.fire('Erro', 'Número de WhatsApp inválido!', 'error');
        return;
    }

    // Coleta os dados do formulário e inputs hidden
    var data = $("#dataEncaixe").val();
    var profissional = $("#profissionalEncaixe").val();
     var id_conta = $("input[name='id_conta']").val();
	 

    $.ajax({
        url: 'ajax/arquivo_cadastro_encaixe.php', // MUDE PARA O CAMINHO CORRETO!
        method: 'POST',
        data: {
            nome: nome,
            whatsapp: whatsapp,
            data: data,
            profissional: profissional,
            id_conta: id_conta
        },
        dataType: 'json', // Espera uma resposta JSON
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Sucesso!',
                    text: 'Registro de encaixe realizado com sucesso! Entraremos em contato caso haja disponibilidade.',
                    icon: 'success',
                    allowOutsideClick: false,
                }).then(() => {
                  $('#modalEncaixe').modal('hide'); // Fecha o modal após o sucesso
                  // Limpar Campos
                  $("#nomeEncaixe").val('');
                  $("#whatsappEncaixe").val('');
                });

            } else {
                Swal.fire('Erro', response.message || 'Erro ao cadastrar encaixe.', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText); // Log do erro completo no console
            Swal.fire('Erro', 'Erro ao cadastrar encaixe. Tente novamente mais tarde.', 'error');
        }
    });
}

//Adiciona uma máscara para o campo WhatsApp
$(document).ready(function(){
    $('#whatsappEncaixe').mask('(00) 00000-0000');
});

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>