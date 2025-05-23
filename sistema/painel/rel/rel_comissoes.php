<?php
@session_start();
$id_conta = $_SESSION['id_conta'];
include('../../conexao.php');
include('data_formatada.php');

$dataInicial = $_GET['dataInicial'];
$dataFinal = $_GET['dataFinal'];
$pago = $_GET['pago'];
$funcionario = $_GET['funcionario'];
$id_conta = $_GET['id_conta'];

$dataInicialF = implode('/', array_reverse(explode('-', $dataInicial)));
$dataFinalF = implode('/', array_reverse(explode('-', $dataFinal)));

if ($dataInicial == $dataFinal) {
	$texto_apuracao = 'APURADO EM ' . $dataInicialF;
} else if ($dataInicial == '1980-01-01') {
	$texto_apuracao = 'APURADO EM TODO O PERÍODO';
} else {
	$texto_apuracao = 'APURAÇÃO DE ' . $dataInicialF . ' ATÉ ' . $dataFinalF;
}

try {
	$stmt = $pdo->prepare("SELECT * FROM config WHERE id = :id_conta");
	$stmt->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
	$stmt->execute();
	$config = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($config) {
		// Variáveis de Configuração do Sistema
		$nome_sistema = $config['nome'];
		$email_sistema = $config['email'];
		$whatsapp_sistema = $config['telefone_whatsapp'];
		$tipo_rel = $config['tipo_rel'];
		$telefone_fixo_sistema = $config['telefone_fixo'];
		$endereco_sistema = $config['endereco'];
		
	} else {
		echo "Configurações não encontradas para a conta.";
	}
} catch (PDOException $e) {
	echo "Erro ao buscar configurações: " . $e->getMessage();
}



if ($pago == '') {
	$acao_rel = '';
	$pago_pdo = '';
} else {
	if ($pago == 'Sim') {
		$acao_rel = ' Pagas ';
		$pago_pdo = 'and pago = "Sim"';
	} else {
		$acao_rel = ' Pendentes ';
		$pago_pdo = 'and pago = "Não"';
	}
}

// $pago = '%' . $pago . '%';


if ($funcionario == '') {
	$nome_func = '';
} else {
	$query = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta'");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$nome_func = ' - Funcionário: ' . $res[0]['nome'];
	$nome_func2 = $res[0]['nome'];
	$tel_func = $res[0]['telefone'];
	$pix_func = ' <b>Chave:</b> ' . $res[0]['tipo_chave'] . ' <b>Pix:</b> ' . $res[0]['chave_pix'];
}

$funcionario = '%' . $funcionario . '%';

?>

<!DOCTYPE html>
<html>

<head>
	<title>Relatório de Comissões</title>
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
			font-size: 17px;
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

	<div class="titulo_cab titulo_img"><u>Relatório de Comissões <?php echo $acao_rel ?> <?php echo $nome_func ?></u></div>
	<div class="data_img"><?php echo mb_strtoupper($data_hoje) ?></div>

	<img class="imagem" src="<?php echo $url ?>/sistema/img/logo_rel.jpg" width="150px">


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
		$total_a_pagar = 0;
		$total_pendente = 0;

		$query = $pdo->query("SELECT * FROM pagar where data_lanc >= '$dataInicial' and data_lanc <= '$dataFinal' $pago_pdo and funcionario like '$funcionario' and tipo = 'Comissão' and id_conta = '$id_conta' ORDER BY pago asc, data_venc asc");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
		if ($total_reg > 0) {
		?>

			<table class="table table-striped borda" cellpadding="6">
				<thead>
					<tr align="center">
						<th scope="col">Serviço</th>
						<th scope="col">Valor</th>
						<th scope="col">Funcionário</th>
						<th scope="col">Data Serviço</th>
						<th scope="col">Pagamento</th>
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
						$funcionario = $res[$i]['funcionario'];
						$cliente = $res[$i]['cliente'];

						$pago = $res[$i]['pago'];
						$servico = $res[$i]['servico'];

						$valorF = number_format($valor, 2, ',', '.');
						$data_lancF = implode('/', array_reverse(explode('-', $data_lanc)));
						$data_pgtoF = implode('/', array_reverse(explode('-', $data_pgto)));
						$data_vencF = implode('/', array_reverse(explode('-', $data_venc)));


						$query2 = $pdo->query("SELECT * FROM clientes where id = '$pessoa' and id_conta = '$id_conta'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						$total_reg2 = @count($res2);
						if ($total_reg2 > 0) {
							$nome_pessoa = $res2[0]['nome'];
							$telefone_pessoa = $res2[0]['telefone'];
						} else {
							$nome_pessoa = 'Nenhum!';
							$telefone_pessoa = 'Nenhum';
						}


						$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_baixa' and id_conta = '$id_conta'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						$total_reg2 = @count($res2);
						if ($total_reg2 > 0) {
							$nome_usuario_pgto = $res2[0]['nome'];
						} else {
							$nome_usuario_pgto = 'Nenhum!';
						}



						$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente' and id_conta = '$id_conta'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						$total_reg2 = @count($res2);
						if ($total_reg2 > 0) {
							$nome_cliente = $res2[0]['nome'];
						} else {
							$nome_cliente = 'Nenhum!';
						}



						$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_lanc' and id_conta = '$id_conta'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						$total_reg2 = @count($res2);
						if ($total_reg2 > 0) {
							$nome_usuario_lanc = $res2[0]['nome'];
						} else {
							$nome_usuario_lanc = 'Sem Referência!';
						}



						$query2 = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						$total_reg2 = @count($res2);
						if ($total_reg2 > 0) {
							$nome_func = $res2[0]['nome'];
							$chave_pix_func = $res2[0]['chave_pix'];
							$tipo_chave_func = $res2[0]['tipo_chave'];
						} else {
							$nome_func = 'Sem Referência!';
							$chave_pix_func = '';
							$tipo_chave_func = '';
						}


						$query2 = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						$total_reg2 = @count($res2);
						if ($total_reg2 > 0) {
							$nome_serv = $res2[0]['nome'];
						} else {
							$nome_serv = 'Sem Referência!';
						}


						if ($data_pgto == '0000-00-00' || $data_pgto == null) {
							$classe_alerta = 'text-danger';
							$data_pgtoF = 'Pendente';
							$visivel = '';
							$total_a_pagar += $valor;
							$total_pendente += 1;
							$imagem = 'vermelho.jpg';
						} else {
							$classe_alerta = 'verde';
							$visivel = 'ocultar';
							$total_pago += $valor;
							$imagem = 'verde.jpg';
						}




						if ($data_venc < $data_hoje and $pago != 'Sim') {
							$classe_debito = 'vermelho-escuro';
						} else {
							$classe_debito = '';
						}


						$total_pagoF = number_format($total_pago, 2, ',', '.');
						$total_a_pagarF = number_format($total_a_pagar, 2, ',', '.');


					?>

						<tr align="center" class="<?php echo $classe_debito ?>">
							<td align="left">
								<img src="<?php echo $url ?>/sistema/img/<?php echo $imagem ?>" width="11px" height="11px" style="margin-top:3px">
								<?php echo $nome_serv ?>
							</td>
							<td class="esc">R$ <?php echo $valorF ?></td>
							<td class="esc"><?php echo $nome_func ?></td>
							<td class="esc"><?php echo $data_lancF ?></td>
							<td class="esc"><?php echo $data_vencF ?></td>
							<td class="esc"><?php echo $nome_cliente ?></td>
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

			<span class=""> <small><small><small><small>TOTAL DE COMISSÕES</small> : <?php echo @$total_reg ?></small></small></small> </span>

			<span class="text-success"> <small><small><small><small>TOTAL PAGO R$</small> : <?php echo @$total_pagoF ?></small></small></small> </span>

			<span class="text-danger"> <small><small><small><small>TOTAL À PAGAR R$</small> : <?php echo @$total_a_pagarF ?></small></small></small> </span>



		</div>
	</div>
	<div class="cabecalho" style="border-bottom: solid 1px #0340a3">
	</div>





	<?php if ($funcionario != "") { ?>

		<div class="col-md-12 p-2" align="center">
			<div class="">
				<small><small>
						<span class=""> <b>Funcionário</b> : <?php echo @$nome_func2 ?> </span>

						<span class=""> <b>Telefone</b> : <?php echo @$tel_func ?> </span>

						<span class=""> <?php echo @$pix_func ?> </span>

						<span class="text-success"> <b>Total à Receber</b> : <?php echo @$total_a_pagarF ?> </span>
					</small></small>


			</div>
		</div>
		<div class="cabecalho" style="border-bottom: solid 1px #0340a3">
		</div>

	<?php } ?>



	<div class="footer" align="center">
		<span style="font-size:10px"><?php echo $nome_sistema ?> Whatsapp: <?php echo $whatsapp_sistema ?></span>
	</div>

</body>

</html>