<?php
@session_start();
$id_conta = $_SESSION['id_conta'];
include('../../conexao.php');
include('data_formatada.php');

$hoje = date('Y-m-d');
$mes_atual = Date('m');
$ano_atual = Date('Y');
$dataInicioMes = $ano_atual . "-" . $mes_atual . "-01";

$id_conta = $_GET['id_conta'];

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

?>

<!DOCTYPE html>
<html>

<head>
	<title>Relatório de Produtos</title>
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

	<div class="titulo_cab titulo_img"><u>Relatório de Produtos </u></div>
	<div class="data_img"><?php echo mb_strtoupper($data_hoje) ?></div>

	<img class="imagem" src="<?php echo $url ?>sistema/img/logo_rel<?php echo $id_conta ?>.jpg">


	<br><br><br>
	<div class="cabecalho" style="border-bottom: solid 1px #0340a3">
	</div>

	<div class="mx-2">

		<?php
		$estoque_baixo = 0;
		$query = $pdo->query("SELECT * FROM produtos where id_conta = '$id_conta' ORDER BY nome asc");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
		if ($total_reg > 0) {
		?>

			<table class="table table-striped borda" cellpadding="6">
				<thead>
					<tr align="center">
						<th scope="col">Nome</th>
						<th scope="col">Categoria</th>
						<th scope="col">Valor Compra</th>
						<th scope="col">Valor Venda</th>
						<th scope="col">Estoque</th>
					</tr>
				</thead>
				<tbody>

					<?php
					for ($i = 0; $i < $total_reg; $i++) {
						foreach ($res[$i] as $key => $value) {
						}
						$id = $res[$i]['id'];
						$nome = $res[$i]['nome'];
						$descricao = $res[$i]['descricao'];
						$categoria = $res[$i]['categoria'];
						$valor_compra = $res[$i]['valor_compra'];
						$valor_venda = $res[$i]['valor_venda'];
						$foto = $res[$i]['foto'];
						$estoque = $res[$i]['estoque'];
						$nivel_estoque = $res[$i]['nivel_estoque'];

						$valor_vendaF = number_format($valor_venda, 2, ',', '.');
						$valor_compraF = number_format($valor_compra, 2, ',', '.');


						//extensão do arquivo
						$ext = pathinfo($foto, PATHINFO_EXTENSION);
						if ($ext == 'jpg' || $ext == 'JPG' || $ext == 'png' || $ext == 'PNG' || $ext == 'jpeg' || $ext == 'JPEG') {
							$foto2 = $foto;
						} else {
							$foto2 = 'sem-foto.jpg';
						}



						$query2 = $pdo->query("SELECT * FROM cat_produtos where id = '$categoria' and id_conta = '$id_conta'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						$total_reg2 = @count($res2);
						if ($total_reg2 > 0) {
							$nome_cat = $res2[0]['nome'];
						} else {
							$nome_cat = 'Sem Referência!';
						}


						if ($nivel_estoque >= $estoque) {
							$alerta_estoque = 'text-danger';
							$estoque_baixo += 1;
						} else {
							$alerta_estoque = '';
						}

					?>

						<tr align="center" class="<?php echo $alerta_estoque ?>">
							<td align="left">
								<img src="<?php echo $url ?>/sistema/painel/img/produtos/<?php echo $foto2 ?>" width="27px" height="27px">
								<?php echo $nome ?>
							</td>
							<td class="esc"><?php echo $nome_cat ?></td>
							<td class="esc">R$ <?php echo $valor_compraF ?></td>
							<td class="esc">R$ <?php echo $valor_vendaF ?></td>
							<td class="esc"><?php echo $estoque ?></td>
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

			<span class="text-danger"> <small><small><small><small>PRODUTOS ESTOQUE BAIXO</small> : <?php echo @$estoque_baixo ?></small></small></small> </span>

			<span class=""> <small><small><small><small>TOTAL DE PRODUTOS</small> : <?php echo @$total_reg ?></small></small></small> </span>



		</div>
	</div>
	<div class="cabecalho" style="border-bottom: solid 1px #0340a3">
	</div>




	<div class="footer" align="center">
		<span style="font-size:10px"><?php echo $nome_sistema ?> Whatsapp: <?php echo $whatsapp_sistema ?></span>
	</div>

</body>

</html>