<?php
@session_start();
$id_conta = $_SESSION['id_conta'];
include('../../conexao.php');
include('data_formatada.php');

$dataInicial = $_POST['dataInicial'];
$dataFinal = $_POST['dataFinal'];


//CARREGAR DOMPDF
require_once '../../dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function gerarPDF6($html)
{
	$options = new Options();
	$options->set('isRemoteEnabled', true);
	$pdf = new DOMPDF($options);

	$pdf->set_paper('A4', 'portrait');
	$pdf->load_html($html);
	$pdf->render();

	$pdf->stream(
		'aniversariantes.pdf',
		array("Attachment" => false)
	);
}

// Verifica se o botão PDF foi clicado
if (isset($_GET['gerar_pdf'])) {
	$dataInicial = $_GET['dataInicial'];
    $dataFinal = $_GET['dataFinal'];
	//ob_start(); // Inicia o buffer de saída
	$html = file_get_contents($url . "sistema/painel/rel/rel_aniv.php?dataInicial=$dataInicial&dataFinal=$dataFinal&id_conta=$id_conta");
	//$html = ob_get_clean(); // Obtém o conteúdo do buffer e limpa-o
	gerarPDF6($html);
	exit();
}

$partesInicial = explode('-', $dataInicial);
$dataDiaInicial = $partesInicial[2];
$dataMesInicial = $partesInicial[1];

$partesFinal = explode('-', $dataFinal);
$dataDiaFinal = $partesFinal[2];
$dataMesFinal = $partesFinal[1];

$dataInicialF = implode('/', array_reverse(explode('-', $dataInicial)));
$dataFinalF = implode('/', array_reverse(explode('-', $dataFinal)));

if ($dataInicial == $dataFinal) {
	$texto_apuracao = 'ANIVERSÁRIANTES DO DIA ' . $dataInicialF;
} else {
	$texto_apuracao = 'ANIVERSÁRIANTES DE ' . $dataInicialF . ' ATÉ ' . $dataFinalF;
}



?>

<!DOCTYPE html>
<html>

<head>
	<title>Relatório de Aniversáriantes</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">


	<style>
		@page {
			margin: 0px;

		}

		body {
			margin-top: 5px;
			font-family: Times, "Times New Roman", Georgia, serif;
		}

		.footer {
			margin-top: 20px;
			width: 100%;
			background-color: #ebebeb;
			padding: 5px;
			position: absolute;
			bottom: 0;
		}



		.cabecalho {
			padding: 10px;
			margin-bottom: 30px;
			width: 100%;
			font-family: Times, "Times New Roman", Georgia, serif;
		}

		.titulo_cab {
			color: #0340a3;
			font-size: 20px;
		}



		.titulo {
			margin: 0;
			font-size: 28px;
			font-family: Arial, Helvetica, sans-serif;
			color: #6e6d6d;

		}

		.subtitulo {
			margin: 0;
			font-size: 12px;
			font-family: Arial, Helvetica, sans-serif;
			color: #6e6d6d;
		}



		hr {
			margin: 8px;
			padding: 0px;
		}



		.area-cab {

			display: block;
			width: 100%;
			height: 10px;

		}


		.coluna {
			margin: 0px;
			float: left;
			height: 30px;
		}

		.area-tab {

			display: block;
			width: 100%;
			height: 30px;

		}


		.imagem {
			width: 150px;
			position: absolute;
			right: 20px;
			top: 10px;
		}

		.titulo_img {
			position: absolute;
			margin-top: 10px;
			margin-left: 10px;

		}

		.data_img {
			position: absolute;
			margin-top: 40px;
			margin-left: 10px;
			border-bottom: 1px solid #000;
			font-size: 10px;
		}

		.endereco {
			position: absolute;
			margin-top: 50px;
			margin-left: 10px;
			border-bottom: 1px solid #000;
			font-size: 10px;
		}

		.verde {
			color: green;
		}



		table.borda {
			border-collapse: collapse;
			/* CSS2 */
			background: #FFF;
			font-size: 12px;
			vertical-align: middle;
		}

		table.borda td {
			border: 1px solid #dbdbdb;
		}

		table.borda th {
			border: 1px solid #dbdbdb;
			background: #ededed;
			font-size: 13px;
		}
	</style>


</head>

<body>

	<div class="titulo_cab titulo_img"><u>Relatório de Aniversáriantes </u></div>
	<div class="data_img"><?php echo mb_strtoupper($data_hoje) ?></div>

	<img class="imagem" src="<?php echo $url ?>/sistema/img/logo_rel<?php echo $id_conta ?>.jpg" width="150px">


	<br><br><br>
	<div class="cabecalho" style="border-bottom: solid 1px #0340a3">
	</div>

	<div class="mx-2">

		<section class="area-cab">

			<div>
				<small><small><small><u><?php echo $texto_apuracao ?></u></small></small></small>
			</div>


		</section>

		<br>

		<?php
		$total_entradas = 0;
		$query = $pdo->query("SELECT * FROM clientes where month(data_nasc) >= '$dataMesInicial' and day(data_nasc) >= '$dataDiaInicial' and month(data_nasc) <= '$dataMesFinal' and day(data_nasc) <= '$dataDiaFinal' and id_conta = '$id_conta' order by data_nasc asc, id asc");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
		if ($total_reg > 0) {
		?>

			<table class="table table-striped borda" cellpadding="6">
				<thead>
					<tr align="center">
						<th scope="col">Nome</th>
						<th scope="col">Telefone</th>
						<th scope="col">Cadastro</th>
						<th scope="col">Nascimento</th>
						<th scope="col">Cartões</th>
					</tr>
				</thead>
				<tbody>

					<?php
					for ($i = 0; $i < $total_reg; $i++) {
						foreach ($res[$i] as $key => $value) {
						}
						$id = $res[$i]['id'];
						$nome = $res[$i]['nome'];
						$data_nasc = $res[$i]['data_nasc'];
						$data_cad = $res[$i]['data_cad'];
						$telefone = $res[$i]['telefone'];
						$endereco = $res[$i]['endereco'];
						$cartoes = $res[$i]['cartoes'];
						$data_retorno = $res[$i]['data_retorno'];
						$ultimo_servico = $res[$i]['ultimo_servico'];

						$data_cadF = implode('/', array_reverse(explode('-', $data_cad)));
						$data_nascF = implode('/', array_reverse(explode('-', $data_nasc)));
						$data_retornoF = implode('/', array_reverse(explode('-', $data_retorno)));

						if ($data_nascF == '00/00/0000' || $data_nascF == null) {
							$data_nascF = 'Sem Lançamento';
						}



					?>

						<tr align="center" class="">
							<td align="">
								<?php echo $nome ?>
							</td>
							<td class="esc"><?php echo $telefone ?></td>
							<td class="esc"> <?php echo $data_cadF ?></td>
							<td class="esc"><?php echo $data_nascF ?></td>
							<td class="esc"><?php echo $cartoes ?></td>

						</tr>

					<?php } ?>

				</tbody>
			</table>

		<?php } else {
			echo 'Não possuem registros para serem exibidos!';
			exit();
		} ?>

	</div>



	<div class="col-md-12 p-2">
		<div class="" align="right" style="margin-right: 20px">

			<span class=""> <small><small><small><small>TOTAL DE ANIVERSÁRIANTES</small> : <?php echo @$total_reg ?></small></small></small> </span>


		</div>
	</div>
	<div class="cabecalho" style="border-bottom: solid 1px #0340a3">
	</div>



	<!-- <div class="footer" align="center">
		<span style="font-size:10px"><?php echo $nome_sistema ?> Whatsapp: <?php echo $whatsapp_sistema ?></span>
	</div> -->

	<div style="float: right;margin-right: 20px;">
        <a href="?dataInicial=<?php echo $dataInicial?>&dataFinal=<?php echo $dataFinal?>&id_conta=<?php echo $id_conta?>&gerar_pdf=1" target="_blank">
            <button class="btn btn-primary">Gerar PDF</button>
        </a>
    </div>

</body>

</html>
