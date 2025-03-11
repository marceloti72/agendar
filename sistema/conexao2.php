<?php
@session_start();

// Configurações da URL
$url = "https://" . $_SERVER['HTTP_HOST'] . "/";

if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
  $url = "http://" . $_SERVER['HTTP_HOST'] . "/agendar/";

  // Configurações do Banco de Dados Local
  $db_servidor = 'localhost';
  $db_usuario = 'root';
  $db_senha = '';
  $db_nome = 'barbearia';
} else {
  // Configurações do Banco de Dados Hospedado
  $db_servidor = 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
  $db_usuario = 'skysee';
  $db_senha = '9vtYvJly8PK6zHahjPUg';
  $db_nome = 'barbearia';
}

// Configuração do Fuso Horário
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o Banco de Dados
try {
  $pdo = new PDO("mysql:dbname=$db_nome;host=$db_servidor;charset=utf8", $db_usuario, $db_senha);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

// Configurações do Sistema

// Validação do username
if (isset($_GET['u'])) {
	$username = filter_var($_GET['u'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

	try {
		$stmt = $pdo->prepare("SELECT * FROM config WHERE username = :username");
		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$stmt->execute();
		$config = $stmt->fetch(PDO::FETCH_ASSOC);
  
		if ($config) {
			$nome_sistema = htmlspecialchars($config['nome']);
			$email_sistema = htmlspecialchars($config['email']);
			$whatsapp_sistema = htmlspecialchars($config['telefone_whatsapp']);
			$telefone_fixo_sistema = htmlspecialchars($config['telefone_fixo']);
			$endereco_sistema = htmlspecialchars($config['endereco']);          
			$instagram_sistema = htmlspecialchars($config['instagram']);
			$texto_rodape = htmlspecialchars($config['texto_rodape']);          
			$texto_sobre = htmlspecialchars($config['texto_sobre']);          
			$mapa = $config['mapa'];
			$quantidade_cartoes = htmlspecialchars($config['quantidade_cartoes']);
			$texto_fidelidade = htmlspecialchars($config['texto_fidelidade']);
			$msg_agendamento = htmlspecialchars($config['msg_agendamento']);
			$cnpj_sistema = htmlspecialchars($config['cnpj']);          
			$agendamento_dias = htmlspecialchars($config['agendamento_dias']);
			$minutos_aviso = htmlspecialchars($config['minutos_aviso']);
			$antAgendamento = htmlspecialchars($config['minutos_aviso']);
			$token = htmlspecialchars($config['token']);
			$instancia = htmlspecialchars($config['instancia']);
			$url_video = htmlspecialchars($config['url_video']);          
			$taxa_sistema = htmlspecialchars($config['taxa_sistema']);
			$lanc_comissao = htmlspecialchars($config['lanc_comissao']);
			$ativo_sistema = htmlspecialchars($config['ativo']);
			$porc_servico = htmlspecialchars($config['porc_servico']);
			$pgto_api = htmlspecialchars($config['pgto_api']);
			$api = htmlspecialchars($config['api']);
			$id_conta = htmlspecialchars($config['id']);
			$agendamentos2 = $config['agendamentos'];
			$produtos2 = $config['produtos'];
			$servicos2 = $config['servicos'];
			$depoimentos2 = $config['depoimentos'];
			$carrossel = $config['carrossel'];
  
			$_SESSION['id_conta'] = $id_conta;
  
			$horas_confirmacaoF = $minutos_aviso . ':00:00';
			$tel_whatsapp = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);
		} else {
			echo "Configurações não encontradas para a conta.";
		}
	} catch (PDOException $e) {
		echo "Erro ao buscar configurações: " . $e->getMessage();
	}


  } else {
	  $id_conta = $_SESSION['id_conta'];

	  try {
		$stmt = $pdo->prepare("SELECT * FROM config WHERE id = :id");
		$stmt->bindParam(':id', $id_conta, PDO::PARAM_INT);
		$stmt->execute();
		$config = $stmt->fetch(PDO::FETCH_ASSOC);
  
		if ($config) {
			$nome_sistema = htmlspecialchars($config['nome']);
			$email_sistema = htmlspecialchars($config['email']);
			$whatsapp_sistema = htmlspecialchars($config['telefone_whatsapp']);
			$telefone_fixo_sistema = htmlspecialchars($config['telefone_fixo']);
			$endereco_sistema = htmlspecialchars($config['endereco']);          
			$instagram_sistema = htmlspecialchars($config['instagram']);
			$texto_rodape = htmlspecialchars($config['texto_rodape']);          
			$texto_sobre = htmlspecialchars($config['texto_sobre']);          
			$mapa = $config['mapa'];
			$quantidade_cartoes = htmlspecialchars($config['quantidade_cartoes']);
			$texto_fidelidade = htmlspecialchars($config['texto_fidelidade']);
			$msg_agendamento = htmlspecialchars($config['msg_agendamento']);
			$cnpj_sistema = htmlspecialchars($config['cnpj']);          
			$agendamento_dias = htmlspecialchars($config['agendamento_dias']);
			$minutos_aviso = htmlspecialchars($config['minutos_aviso']);
			$antAgendamento = htmlspecialchars($config['minutos_aviso']);
			$token = htmlspecialchars($config['token']);
			$instancia = htmlspecialchars($config['instancia']);
			$url_video = htmlspecialchars($config['url_video']);          
			$taxa_sistema = htmlspecialchars($config['taxa_sistema']);
			$lanc_comissao = htmlspecialchars($config['lanc_comissao']);
			$ativo_sistema = htmlspecialchars($config['ativo']);
			$porc_servico = htmlspecialchars($config['porc_servico']);
			$pgto_api = htmlspecialchars($config['pgto_api']);
			$api = htmlspecialchars($config['api']);
			$id_conta = htmlspecialchars($config['id']);
			$agendamentos2 = $config['agendamentos'];
			$produtos2 = $config['produtos'];
			$servicos2 = $config['servicos'];
			$depoimentos2 = $config['depoimentos'];
			$carrossel = $config['carrossel'];  
			$username = $config['username'];  
			
  
			$horas_confirmacaoF = $minutos_aviso . ':00:00';
			$tel_whatsapp = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);
		} else {
			echo "Configurações não encontradas para a conta.";
		}
	} catch (PDOException $e) {
		echo "Erro ao buscar configurações: " . $e->getMessage();
	}
  }

  

?>