<?php
@session_start();

// Configurações da URL
$url = "https://" . $_SERVER['HTTP_HOST'] . "/";
$url = explode("//", $url);

if ($url[1] === 'localhost/') {
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

	$url = "https://" . $_SERVER['HTTP_HOST'] . "/";
}

// Configuração do Fuso Horário
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o Banco de Dados
try {
	$pdo = new PDO("mysql:dbname=$db_nome;host=$db_servidor;charset=utf8", $db_usuario, $db_senha);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilita tratamento de erros
} catch (PDOException $e) {
	die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

// Configurações do Sistema
if (isset($_SESSION['id_conta'])) {
	$id_conta = $_SESSION['id_conta'];

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
			$instagram_sistema = $config['instagram'];
			$tipo_comissao = $config['tipo_comissao'];
			$texto_rodape = $config['texto_rodape'];
			$texto_sobre = $config['texto_sobre'];
			$mapa = $config['mapa'];
			$quantidade_cartoes = $config['quantidade_cartoes'];
			$texto_fidelidade = $config['texto_fidelidade'];
			$msg_agendamento = $config['msg_agendamento'];
			$cnpj_sistema = $config['cnpj'];
			$agendamento_dias = $config['agendamento_dias'];
			$minutos_aviso = $config['minutos_aviso'];
			$antAgendamento = $config['minutos_aviso'];
			$token = $config['token'];
			$instancia = $config['instancia'];
			$url_video = $config['url_video'];
			$taxa_sistema = $config['taxa_sistema'];
			$lanc_comissao = $config['lanc_comissao'];
			$ativo_sistema = $config['ativo'];
			$porc_servico = $config['porc_servico'];
			$pgto_api = $config['pgto_api'];
			$token_mp = $config['token_mp'];
			$key_mp = $config['key_mp'];
			$agendamentos2 = $config['agendamentos'];
			$produtos2 = $config['produtos'];
			$servicos2 = $config['servicos'];
			$depoimentos2 = $config['depoimentos'];
			$carrossel = $config['carrossel'];
			$username = $config['username'];
			$encaixe = $config['encaixe'];
			$satisfacao = $config['satisfacao'];

			
			// Novas variáveis Menuia
			$emailMenuia = $config['email_menuia'] ?? '';
			$planoMenuia = $config['plano_menuia'] ?? '';
			$validadeMenuia = $config['validade_menuia'] ?? '';
			$senhaMenuia = $config['senha_menuia'] ?? '';
			$api = $config['api'] ?? '';

			$horas_confirmacaoF = $minutos_aviso . ':00:00';
			$tel_whatsapp = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);

			// Verificação de Ativação do Sistema
			if ($ativo_sistema !== 'Sim' && $ativo_sistema !== '' && $ativo_sistema !== 'teste') {
				echo '<style type="text/css">
                        @media only screen and (max-width: 700px) {
                            .imgsistema_mobile {
                                width: 300px;
                            }
                        }
                    </style>
                    <div style="text-align: center; margin-top: 100px">
                        <img src="sistema/img/bloqueio.png" class="imgsistema_mobile">
                    </div>';
				exit();
			}
		} else {
			echo "Configurações não encontradas para a conta.";
		}
	} catch (PDOException $e) {
		echo "Erro ao buscar configurações: " . $e->getMessage();
	}
}
