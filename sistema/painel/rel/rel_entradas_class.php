<?php
@session_start();
$id_conta = $_SESSION['id_conta'];
include('../../conexao.php');
include('data_formatada.php');


$dataInicial = $_POST['dataInicial'];
$dataFinal = $_POST['dataFinal'];
$filtro = $_POST['filtro'];
$cliente = $_POST['cliente'];

//CARREGAR DOMPDF
require_once '../../dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function gerarPDF2($html)
{
	$options = new Options();
	$options->set('isRemoteEnabled', true);
	$pdf = new DOMPDF($options);

	$pdf->set_paper('A4', 'portrait');
	$pdf->load_html($html);
	$pdf->render();

	$pdf->stream(
		'entradas.pdf',
		array("Attachment" => false)
	);
}

// Verifica se o botão PDF foi clicado
if (isset($_GET['gerar_pdf'])) {
	$dataInicial = $_GET['dataInicial'];
	$dataFinal = $_GET['dataFinal'];
	$filtro = urlencode($_GET['filtro']);
	$cliente = urlencode($_GET['cliente']);
	//ob_start(); // Inicia o buffer de saída
	$html = file_get_contents($url . "sistema/painel/rel/rel_entradas.php?dataInicial=$dataInicial&dataFinal=$dataFinal&filtro=$filtro&cliente=$cliente&id_conta=$id_conta");
	//$html = ob_get_clean(); // Obtém o conteúdo do buffer e limpa-o
	gerarPDF2($html);
	exit();
}


if ($cliente != "") {
	$sql_cli = "and pessoa = '$cliente'";
} else {
	$sql_cli = '';
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


if ($filtro == '') {
	$acao_rel = 'Entradas / Ganhos';
	$tipo_pdo = "";
} elseif ($filtro == 'Produto') {
	$acao_rel = ' Vendas ';
	$tipo_pdo = ' and tipo = "Produto" ';
} elseif ($filtro == 'Serviço') {
	$acao_rel = ' Serviços ';
	$tipo_pdo = ' and tipo = "Serviço" ';
} else {
	$acao_rel = 'Recebimentos';
	$tipo_pdo = ' and tipo = "conta" ';
}


?>

<!DOCTYPE html>
<html>

<head>
	<title>Relatório de Ganhos</title>
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

	<div class="titulo_cab titulo_img"><u>Relatório de <?php echo $acao_rel ?> </u></div>
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
		$total_contas = 0;
		$query = $pdo->query("SELECT * FROM receber where data_pgto >= '$dataInicial' and data_pgto <= '$dataFinal' $tipo_pdo and pago = 'Sim' $sql_cli and id_conta = '$id_conta'  ORDER BY data_pgto asc");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
		if ($total_reg > 0) {
		?>

			<table class="table table-striped borda" cellpadding="6">
				<thead>
					<tr align="center">
						<th scope="col">Descrição</th>
						<th scope="col">Tipo</th>
						<th scope="col">Valor</th>
						<th scope="col">Data PGTO</th>
						<th scope="col">Recebido Por</th>
						<th scope="col">Cliente</th>
					</tr>
				</thead>
				<tbody>

					<?php
					for ($i = 0; $i < $total_reg; $i++) {
						foreach ($res[$i] as $key => $value) {
						}
						$id = $res[$i]['id'];
						$descricao = $res[$i]['descricao'];
						$tipo = $res[$i]['tipo'];
						$valor = $res[$i]['valor'];
						$data_lanc = $res[$i]['data_lanc'];
						$data_pgto = $res[$i]['data_pgto'];
						$data_venc = $res[$i]['data_venc'];
						$usuario_lanc = $res[$i]['usuario_lanc'];
						$usuario_baixa = $res[$i]['usuario_baixa'];
						$foto = $res[$i]['foto'];
						$pessoa = $res[$i]['pessoa'];
						$pago = $res[$i]['pago'];
						$valor2 = $res[$i]['valor2'];

						if ($tipo == 'Comanda') {
							continue;
						}

						// if ($filtro == '') {
						// 	if ($valor <= 0 || $valor == "") {
						// 		continue;
						// 	}
						// }

						if ($valor <= 0 || $valor == "") {
							$valor = $valor2;
						}
						
						$total_contas ++; 		
						$total_entradas += $valor;

						$valorF = number_format($valor, 2, ',', '.');
						$total_entradasF = number_format($total_entradas, 2, ',', '.');
						$data_lancF = implode('/', array_reverse(explode('-', $data_lanc)));
						$data_pgtoF = implode('/', array_reverse(explode('-', $data_pgto)));
						$data_vencF = implode('/', array_reverse(explode('-', $data_venc)));


						$query2 = $pdo->query("SELECT * FROM clientes where id = '$pessoa'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						$total_reg2 = @count($res2);
						if ($total_reg2 > 0) {
							$nome_pessoa = $res2[0]['nome'];
							$telefone_pessoa = $res2[0]['telefone'];
							$classe_whats = '';
						} else {
							$nome_pessoa = 'Nenhum!';
							$telefone_pessoa = '';
							$classe_whats = 'ocultar';
						}


						$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_baixa'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						$total_reg2 = @count($res2);
						if ($total_reg2 > 0) {
							$nome_usuario_pgto = $res2[0]['nome'];
						} else {
							$nome_usuario_pgto = 'Nenhum!';
						}



						$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_lanc'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						$total_reg2 = @count($res2);
						if ($total_reg2 > 0) {
							$nome_usuario_lanc = $res2[0]['nome'];
						} else {
							$nome_usuario_lanc = 'Sem Referência!';
						}





					?>

						<tr align="center" class="">
							<td align="left">
								<?php echo $descricao ?>
							</td>
							<td class="esc"><?php echo $tipo ?></td>
							<td class="esc">R$ <?php echo $valorF ?></td>
							<td class="esc"><?php echo $data_pgtoF ?></td>
							<td class="esc"><?php echo $nome_usuario_pgto ?></td>
							<td class="esc"><?php echo $nome_pessoa ?></td>
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

			<span class=""> <small><small><small><small>TOTAL DE RECEBIMENTOS</small> : <?php echo @$total_contas ?></small></small></small> </span>

			<span class="text-success"> <small><small><small><small>TOTAL R$</small> : <?php echo @$total_entradasF ?></small></small></small> </span>



		</div>
	</div>
	<div class="cabecalho" style="border-bottom: solid 1px #0340a3">
	</div>



	<!-- <div class="footer" align="center">
		<span style="font-size:10px"><?php echo $nome_sistema ?> Whatsapp: <?php echo $whatsapp_sistema ?></span>
	</div> -->

	<div style="float: right;margin-right: 20px;">
        <a href="?dataInicial=<?php echo $dataInicial?>&dataFinal=<?php echo $dataFinal?>&filtro=<?php echo $filtro?>&cliente=<?php echo $cliente?>&id_conta=<?php echo $id_conta?>&gerar_pdf=1" target="_blank">
            <button class="btn btn-primary">Gerar PDF</button>
        </a>
    </div>

</body>

</html>