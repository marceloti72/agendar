<?php
@session_start();
$id_conta = $_SESSION['id_conta'];
include('../../conexao.php');
include('data_formatada.php');

$dataInicial = $_POST['dataInicial'];
$dataFinal = $_POST['dataFinal'];
$pago = $_POST['pago'];
$tabela = $_POST['tabela'];
$busca = $_POST['busca'];

if($pago == ''){
	$sem_pago = 1;
}else{
	$sem_pago = 0;
}

//CARREGAR DOMPDF
require_once '../../dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function gerarPDF4($html)
{
	$options = new Options();
	$options->set('isRemoteEnabled', true);
	$pdf = new DOMPDF($options);

	$pdf->set_paper('A4', 'portrait');
	$pdf->load_html($html);
	$pdf->render();

	$pdf->stream(
		'contas.pdf',
		array("Attachment" => false)
	);
}

// Verifica se o botão PDF foi clicado
if (isset($_GET['gerar_pdf'])) {
	$dataInicial = $_GET['dataInicial'];
	$dataFinal = $_GET['dataFinal'];
	$pago = $_GET['pago'];
	$tabela = $_GET['tabela'];
	$busca = $_GET['busca'];
	
	//ob_start(); // Inicia o buffer de saída
	$html = file_get_contents($url . "sistema/painel/rel/rel_contas.php?pago=$pago&dataInicial=$dataInicial&dataFinal=$dataFinal&tabela=$tabela&busca=$busca&id_conta=$id_conta");
	//$html = ob_get_clean(); // Obtém o conteúdo do buffer e limpa-o
	gerarPDF4($html);
	exit();
}


$dataInicialF = implode('/', array_reverse(explode('-', $dataInicial)));
$dataFinalF = implode('/', array_reverse(explode('-', $dataFinal)));

if ($dataInicial == $dataFinal) {
	$texto_apuracao = 'APURADO EM ' . $dataInicialF;
} else if ($dataInicial == '1980-01-01') {
	$texto_apuracao = 'APURADO EM TODO O PERÍODO';
} else {
	$texto_apuracao = 'APURAÇÃO DE ' . $dataInicialF . ' ATÉ ' . $dataFinalF;
}


if ($pago == '') {
	$acao_rel = '';
	$pago_pdo = '';
} else {
	if ($pago == 'Sim') {
		$acao_rel = ' Pagas ';
		$pago_pdo = ' and pago = "Sim" ';
	} else {
		$acao_rel = ' Pendentes ';
		$pago_pdo = ' and pago = "Não" ';
	}
}

if ($tabela == 'receber') {
	$texto_tabela = ' à Receber';
	$cor_tabela = 'text-success';
	$tabela_pago = 'RECEBIDAS';
} else {
	$texto_tabela = ' à Pagar';
	$cor_tabela = 'text-danger';
	$tabela_pago = 'PAGAS';
}

?>

<!DOCTYPE html>
<html>

<head>
	<title>Relatório de Contas</title>
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


	<div class="titulo_cab titulo_img"><u>Relatório de Contas <?php echo $texto_tabela ?> <?php echo $acao_rel ?> </u></div>
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
		$total_pago = 0;
		$total_pagoF = 0;
		$total_a_pagar = 0;
		$total_a_pagarF = 0;
		$query = $pdo->query("SELECT * from $tabela where ($busca >= '$dataInicial' and $busca <= '$dataFinal') $pago_pdo and valor > 0 and id_conta = '$id_conta' order by id desc ");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = count($res);
		if ($total_reg > 0) {
		?>

			<table class="table table-striped borda" cellpadding="6">
				<thead>
					<tr align="center">
						<th scope="col">Descrição</th>
						<th scope="col">Valor</th>
						<th scope="col">Vencimento</th>
						<th scope="col">Data PGTO</th>
						<th scope="col">Pago</th>
					</tr>
				</thead>
				<tbody>

					<?php
					for ($i = 0; $i < $total_reg; $i++) {
						foreach ($res[$i] as $key => $value) {
						}
						$id = $res[$i]['id'];
						$descricao = $res[$i]['descricao'];
						$valor = $res[$i]['valor'];
						$data = $res[$i]['data_lanc'];
						$vencimento = $res[$i]['data_venc'];
						$pago = $res[$i]['pago'];
						$data_pgto = $res[$i]['data_pgto'];

						if ($pago == 'Sim') {
							$total_pago += $valor;
							$classe_square = 'verde';
							$ocultar_baixa = 'ocultar';
							$imagem = 'verde.jpg';
						} else {
							$total_a_pagar += $valor;
							$classe_square = 'text-danger';
							$ocultar_baixa = '';
							$imagem = 'vermelho.jpg';
						}


						$valorF = number_format($valor, 2, ',', '.');
						$total_pagoF = number_format($total_pago, 2, ',', '.');
						$total_a_pagarF = number_format($total_a_pagar, 2, ',', '.');

						$dataF = implode('/', array_reverse(explode('-', $data)));
						$vencimentoF = implode('/', array_reverse(explode('-', $vencimento)));
						$data_pgtoF = implode('/', array_reverse(explode('-', $data_pgto)));

						if ($data_pgtoF == '00/00/0000' || $data_pgto == null) {
							$data_pgtoF = 'Pendente';
						}



					?>

						<tr align="center" class="">
							<td align="left">
								<img src="<?php echo $url ?>/sistema/img/<?php echo $imagem ?>" width="11px" height="11px" style="margin-top:3px">
								<?php echo $descricao ?>
							</td>
							<td class="esc">R$ <?php echo $valorF ?></td>
							<td class="esc"><?php echo $vencimentoF ?></td>
							<td class="esc"><?php echo $data_pgtoF ?></td>
							<td class="esc"><?php echo $pago ?></td>
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

			<span class="text-danger"> <small><small><small><small>TOTAL À <?php echo mb_strtoupper($tabela) ?></small> : R$ <?php echo $total_a_pagarF ?></small></small></small> </span>
			<span class="text-success"> <small><small><small><small>TOTAL <?php echo $tabela_pago ?></small> : R$ <?php echo $total_pagoF ?></small></small></small> </span>


		</div>
	</div>
	<div class="cabecalho" style="border-bottom: solid 1px #0340a3">
	</div>



	<!-- <div class="footer" align="center">
		<span style="font-size:10px"><?php echo $nome_sistema ?> Whatsapp: <?php echo $whatsapp_sistema ?></span>
	</div> -->
	<?php 
	if($sem_pago == 1){
		$pago = '';
	}
	?>

	<div style="float: right;margin-right: 20px;">
        <a href="?dataInicial=<?php echo $dataInicial?>&dataFinal=<?php echo $dataFinal?>&pago=<?php echo $pago?>&tabela=<?php echo $tabela?>&busca=<?php echo $busca?>&id_conta=<?php echo $id_conta?>&gerar_pdf=1" target="_blank">
            <button class="btn btn-primary">Gerar PDF</button>
        </a>
    </div>

</body>

</html>
