<?php

require_once("conexao.php");

date_default_timezone_set('America/Sao_Paulo');

$fusoHorarioAtual = date('Y-m-d H:i:s');

$url = "https://" . $_SERVER['HTTP_HOST'] . "/";
$url = explode("//", $url);

$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

$query = $pdo->prepare("SELECT * FROM usuarios WHERE email = :usuario OR username = :usuario");
$query->bindValue(":usuario", $usuario);
$query->execute();
$res = $query->fetch(PDO::FETCH_ASSOC);

if ($res) {

	if ($res['nivel'] != 'Administrador') {
		if ($res['ativo'] != 'Sim') {
			echo "<script language='javascript'> window.alert('Seu acesso esta bloqueado! Favor entar em contato com a instituição.') </script>";

			echo "<script language='javascript'> window.location='index.php' </script>";
		}
	}

	$hash_armazenado = $res['senha'];

	if (password_verify($senha, $hash_armazenado)) {
		$ativo = @$res['ativo'];
		$id_conta = @$res['id_conta'];
		$id_usuario = @$res['id'];

		$_SESSION['id_conta'] = @$res['id_conta'];
		$_SESSION['id_usuario'] = @$res['id'];		
		$_SESSION['username'] = @$res['username'];		

		if ($ativo == 'Não') {

			if ($url[1] == 'localhost/') {

				//VARIAVEIS DO BANCO DE DADOS LOCAL
				$servidor_bd = 'localhost';
				$banco_bd = 'gestao_sistemas';
				$usuario_bd = 'root';
				$senha_bd = '';
			} else {
				//VARIAVEIS DO BANCO DE DADOS HOSPEDADO
				$servidor_bd = 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
				$usuario_bd = 'skysee';
				$senha_bd = '9vtYvJly8PK6zHahjPUg';
				$banco_bd = 'gestao_sistemas';
			}


			$pdo2 = new PDO("mysql:dbname=$banco_bd;host=$servidor_bd;charset=utf8", "$usuario_bd", "$senha_bd");
			$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Lança exceções em caso de erro

			$query2 = $pdo2->query("SELECT * FROM clientes where id_conta = '$id_conta'");
			$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
			$id = @$res2[0]['id'];

			$query3 = $pdo2->query("SELECT * FROM receber where cliente = '$id' and vencimento < curDate() and pago = 'Não' order by vencimento asc ");
			$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
			if (count($res3) > 0) {
				$id_receber = @$res3[0]['id'];

?>
				<div class="container">
					<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

					<script>
						Swal.fire({
							title: "Seu sistema foi desativado! 😥",
							text: "Existe mensalidade em aberto, efetue o pagamento da assinatura para reestabelecer o sistema.",
							icon: "error",
							confirmButtonText: 'Pagar agora'
						}).then((result) => {
							if (result.isConfirmed) {
								window.location = "https://www.gestao.skysee.com.br/pagar/<?php echo $id_receber ?>";
							}
						});
					</script>


				</div>

			<?php
				exit();
			} else {
				echo "<script language='javascript'> window.alert('Seu acesso esta bloqueado! Favor entar em contato com a SKYSEE.') </script>";
				echo "<script language='javascript'> window.location='../login.php' </script>";
			}
		}


		if ($ativo == 'Nãogt') {

			if ($url[1] == 'localhost/') {
				//VARIAVEIS DO BANCO DE DADOS LOCAL
				$servidor_bd = 'localhost';
				$banco_bd = 'gestao_sistemas';
				$usuario_bd = 'root';
				$senha_bd = '';
			} else {
				//VARIAVEIS DO BANCO DE DADOS HOSPEDADO
				$servidor_bd = 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
				$usuario_bd = 'skysee';
				$senha_bd = '9vtYvJly8PK6zHahjPUg';
				$banco_bd = 'gestao_sistemas';
			}


			$pdo2 = new PDO("mysql:dbname=$banco_bd;host=$servidor_bd;charset=utf8", "$usuario_bd", "$senha_bd");

			$query2 = $pdo2->query("SELECT * FROM clientes where id_conta = '$id_conta'");
			$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
			$id = @$res2[0]['id'];

			$query3 = $pdo2->query("SELECT * FROM receber where cliente = '$id' and vencimento < curDate() and pago = 'Não' order by vencimento asc ");
			$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
			$id_receber = @$res3[0]['id'];

			?>
			<div class="container">
				<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

				<script>
					Swal.fire({
						title: "Período de teste encerrado! 😥",
						text: "Infelizmente seu período de teste grátis acabou, efetue o pagamento da assinatura para reestabelecer o sistema.",
						icon: "error",
						confirmButtonText: 'Pagar agora'
					}).then((result) => {
						if (result.isConfirmed) {
							window.location = "https://www.gestao.skysee.com.br/pagar/<?php echo $id_receber ?>";
						}
					});
				</script>




			</div>

<?php
			exit();
		}

		if ($ativo == 'teste') {
			if ($url[1] == 'localhost/') {
				//VARIAVEIS DO BANCO DE DADOS LOCAL
				$servidor_bd = 'localhost';
				$banco_bd = 'gestao_sistemas';
				$usuario_bd = 'root';
				$senha_bd = '';
			} else {
				//VARIAVEIS DO BANCO DE DADOS HOSPEDADO
				$servidor_bd = 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
				$usuario_bd = 'skysee';
				$senha_bd = '9vtYvJly8PK6zHahjPUg';
				$banco_bd = 'gestao_sistemas';
			}


			$pdo2 = new PDO("mysql:dbname=$banco_bd;host=$servidor_bd;charset=utf8", "$usuario_bd", "$senha_bd");

			$userId = $id_usuario;

			// Inserir no banco de dados
			$stmt = $pdo2->prepare("INSERT INTO user_accesses (user_id, access_time) VALUES (:user_id, :access_time)");
			$stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
			$stmt->bindValue(":access_time", $fusoHorarioAtual, PDO::PARAM_STR);
			$stmt->execute();
		}


		$_SESSION['id_usuario'] = $res['id'];
		$_SESSION['nome_usuario'] = $res['nome'];
		$_SESSION['cpf_usuario'] = $res['cpf'];
		$_SESSION['nivel_usuario'] = $res['nivel'];

		// APAGAR ENCAIXES ANTERIORES
		$data_atual = date('Y-m-d'); // Data atual no formato YYYY-MM-DD
		$query = $pdo->prepare("DELETE FROM encaixe WHERE data < :data_atual and id_conta = :id_conta");
		$query->bindValue(":data_atual", $data_atual);
		$query->bindValue(":id_conta", $id_conta);
		$query->execute();


		//APAGAR AGENDAMENTOS ANTERIORES a 30 dias		
		$data_anterior = date('Y-m-d', strtotime("-30 days",strtotime($data_atual)));

		$query = $pdo->query("SELECT * FROM agendamentos WHERE data < '$data_anterior' and id_conta = '$id_conta'");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
			if($total_reg > 0){
			for($i=0; $i < $total_reg; $i++){
				foreach ($res[$i] as $key => $value){}
					$id = $res[$i]['id'];
					$pdo->query("DELETE FROM agendamentos WHERE id = '$id'");
			}
		}

		echo "<script language='javascript'> window.location='painel/index.php' </script>";
		

	} else {
		echo "<script language='javascript'> window.alert('Senha incorreta!') </script>";
		echo "<script language='javascript'> window.location='../login.php' </script>";
	}
} else {
	echo "<script language='javascript'> window.alert('Usuário não encontrado!') </script>";
	echo "<script language='javascript'> window.location='../login.php' </script>";
}

?>