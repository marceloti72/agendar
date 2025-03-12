<?php
include('../../conexao.php');
include('data_formatada.php');

$dataInicial = $_POST['dataInicial'];
$dataFinal = $_POST['dataFinal'];


//CARREGAR DOMPDF
require_once '../../dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function gerarPDF7($html)
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
	$html = file_get_contents($url . "sistema/painel/rel/rel_lucro.php?dataInicial=$dataInicial&dataFinal=$dataFinal&id_conta=$id_conta");
	//$html = ob_get_clean(); // Obtém o conteúdo do buffer e limpa-o
	gerarPDF7($html);
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


?>

<!DOCTYPE html>
<html>

<head>
	<title>Demonstrativo de Lucro</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">


	<style>
		@page {
			margin: 0px;

		}

		body {
			margin-top: 5px;
			font-family: TimesNewRoman, Geneva, sans-serif;
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
			font-family: TimesNewRoman, Geneva, sans-serif;
		}

		.titulo_cab {
			color: #0340a3;
			font-size: 20px;
		}



		.titulo {
			margin: 0;
			font-size: 28px;
			font-family: TimesNewRoman, Geneva, sans-serif;
			color: #6e6d6d;

		}

		.subtitulo {
			margin: 0;
			font-size: 12px;
			font-family: TimesNewRoman, Geneva, sans-serif;
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

	<div class="titulo_cab titulo_img"><u>Demonstrativo de Lucro </u></div>
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
		$total_servicos = 0;
		$total_vendas = 0;
		$total_receber = 0;
		$total_pagar = 0;
		$total_compras = 0;
		$total_comissoes = 0;
		$total_comanda = 0;

		$total_entradas = 0;
		$total_saidas = 0;

		$saldo_total = 0;

		?>

		<table class="table table-striped borda" cellpadding="6">
			<thead>
				<tr align="center">
					<th scope="col">Comandas</th>
					<th scope="col">Serviços</th>
					<th scope="col">Vendas</th>
					<th scope="col">Recebimentos</th>
					<th scope="col">Despesas</th>
					<th scope="col">Compras</th>
					<th scope="col">Comissões</th>
				</tr>
			</thead>
			<tbody>

				<?php
				//totalizar os serviços 
				$query = $pdo->query("SELECT * FROM receber where data_pgto >= '$dataInicial' and data_pgto <= '$dataFinal' and tipo = 'Serviço' and pago = 'Sim' and id_conta = '$id_conta' ORDER BY data_pgto asc");
				$res = $query->fetchAll(PDO::FETCH_ASSOC);
				$total_reg = @count($res);
				for ($i = 0; $i < $total_reg; $i++) {
					foreach ($res[$i] as $key => $value) {
					}

					$valor = $res[$i]['valor'];


					$total_servicos += $valor;
				}



				//totalizar os vendas 
				$query = $pdo->query("SELECT * FROM receber where data_pgto >= '$dataInicial' and data_pgto <= '$dataFinal' and tipo = 'Venda' and pago = 'Sim' and id_conta = '$id_conta' ORDER BY data_pgto asc");
				$res = $query->fetchAll(PDO::FETCH_ASSOC);
				$total_reg = @count($res);
				for ($i = 0; $i < $total_reg; $i++) {
					foreach ($res[$i] as $key => $value) {
					}

					$valor = $res[$i]['valor'];


					$total_vendas += $valor;
				}



				//totalizar contas recebidas
				$query = $pdo->query("SELECT * FROM receber where data_pgto >= '$dataInicial' and data_pgto <= '$dataFinal' and tipo = 'Conta' and pago = 'Sim' and id_conta = '$id_conta' ORDER BY data_pgto asc");
				$res = $query->fetchAll(PDO::FETCH_ASSOC);
				$total_reg = @count($res);
				for ($i = 0; $i < $total_reg; $i++) {
					foreach ($res[$i] as $key => $value) {
					}

					$valor = $res[$i]['valor'];

					$total_receber += $valor;
				}



				//totalizar comandas
				$query = $pdo->query("SELECT * FROM receber where data_pgto >= '$dataInicial' and data_pgto <= '$dataFinal' and tipo = 'Comanda' and pago = 'Sim' and id_conta = '$id_conta' ORDER BY data_pgto asc");
				$res = $query->fetchAll(PDO::FETCH_ASSOC);
				$total_reg = @count($res);
				for ($i = 0; $i < $total_reg; $i++) {
					foreach ($res[$i] as $key => $value) {
					}

					$valor = $res[$i]['valor'];

					$total_comanda += $valor;
				}





				//totalizar contas despesas
				$query = $pdo->query("SELECT * FROM pagar where data_pgto >= '$dataInicial' and data_pgto <= '$dataFinal' and tipo = 'Conta' and pago = 'Sim' and id_conta = '$id_conta' ORDER BY data_pgto asc");
				$res = $query->fetchAll(PDO::FETCH_ASSOC);
				$total_reg = @count($res);
				for ($i = 0; $i < $total_reg; $i++) {
					foreach ($res[$i] as $key => $value) {
					}

					$total_pagar += $res[$i]['valor'];
				}




				//totalizar contas compras
				$query = $pdo->query("SELECT * FROM pagar where data_pgto >= '$dataInicial' and data_pgto <= '$dataFinal' and tipo = 'Compra' and pago = 'Sim' and id_conta = '$id_conta' ORDER BY data_pgto asc");
				$res = $query->fetchAll(PDO::FETCH_ASSOC);
				$total_reg = @count($res);
				for ($i = 0; $i < $total_reg; $i++) {
					foreach ($res[$i] as $key => $value) {
					}

					$total_compras += $res[$i]['valor'];
				}





				//totalizar contas despesas
				$query = $pdo->query("SELECT * FROM pagar where data_pgto >= '$dataInicial' and data_pgto <= '$dataFinal' and tipo = 'Comissão' and pago = 'Sim' and id_conta = '$id_conta' ORDER BY data_pgto asc");
				$res = $query->fetchAll(PDO::FETCH_ASSOC);
				$total_reg = @count($res);
				for ($i = 0; $i < $total_reg; $i++) {
					foreach ($res[$i] as $key => $value) {
					}

					$total_comissoes += $res[$i]['valor'];
				}


				$total_servicosF = number_format($total_servicos, 2, ',', '.');
				$total_vendasF = number_format($total_vendas, 2, ',', '.');
				$total_receberF = number_format($total_receber, 2, ',', '.');
				$total_pagarF = number_format($total_pagar, 2, ',', '.');
				$total_comprasF = number_format($total_compras, 2, ',', '.');
				$total_comissoesF = number_format($total_comissoes, 2, ',', '.');
				$total_comandasF = number_format($total_comanda, 2, ',', '.');

				$total_entradas = $total_servicos + $total_vendas + $total_receber + $total_comanda;
				$total_saidas = $total_pagar + $total_compras + $total_comissoes;

				$total_entradasF = number_format($total_entradas, 2, ',', '.');
				$total_saidasF = number_format($total_saidas, 2, ',', '.');

				$saldo_total = $total_entradas - $total_saidas;

				$saldo_totalF = number_format($saldo_total, 2, ',', '.');

				if ($saldo_total < 0) {
					$classe_saldo = 'text-danger';
					$classe_img = 'negativo.jpg';
				} else {
					$classe_saldo = 'text-success';
					$classe_img = 'positivo.jpg';
				}

				?>

				<tr align="center" class="">
					<td class="text-success">R$ <?php echo $total_comandasF ?></td>
					<td class="text-success">R$ <?php echo $total_servicosF ?></td>
					<td class="text-success">R$ <?php echo $total_vendasF ?></td>
					<td class="text-success">R$ <?php echo $total_receberF ?></td>
					<td class="text-danger">R$ <?php echo $total_pagarF ?></td>
					<td class="text-danger">R$ <?php echo $total_comprasF ?></td>
					<td class="text-danger">R$ <?php echo $total_comissoesF ?></td>

				</tr>


				<tr align="center" class="">
					<td style="background: #e6ffe8" colspan="4" scope="col">Total de Entradas / Ganhos</td>
					<td style="background: #ffe7e6" colspan="3" scope="col">Total de Saídas / Despesas</td>
				</tr>

				<tr align="center" class="">
					<td colspan="4" class="text-success"> R$ <?php echo $total_entradasF ?></td>
					<td colspan="3" class="text-danger"> R$ <?php echo $total_saidasF ?></td>
				</tr>

			</tbody>
		</table>
	</div>



	<div class="col-md-12 p-2">
		<div class="" align="center" style="margin-right: 20px">

			<img src="<?php echo $url ?>/sistema/img/<?php echo $classe_img ?>" width="100px">
			<span class="<?php echo $classe_saldo ?>">R$ <?php echo $saldo_totalF ?></span>


		</div>
	</div>



	<div class="footer" align="center">
		<span style="font-size:10px"><?php echo $nome_sistema ?> Whatsapp: <?php echo $whatsapp_sistema ?></span>
	</div>

	<div style="float: right;margin-right: 20px;">
        <a href="?dataInicial=<?php echo $dataInicial?>&dataFinal=<?php echo $dataFinal?>&id_conta=<?php echo $id_conta?>&gerar_pdf=1" target="_blank">
            <button class="btn btn-primary">Gerar PDF</button>
        </a>
    </div>

</body>

</html>
