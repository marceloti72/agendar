<?php require_once("sistema/conexao.php") ?>
<!DOCTYPE html>
<html>

<head>
  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Site Metas -->
  <meta name="keywords" content="Sistema de Agendamento" />
  <meta name="description" content="Agende aqui o serviço desejado" />
  <meta name="author" content="Marcelo Ferreira" />
  <link rel="shortcut icon" href="images/favicon<?php echo $id_conta?>.png" type="image/x-icon">

  <title><?php echo $nome_sistema ?></title>

  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />

  
  <!-- fonts style -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!--owl slider stylesheet -->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
  <!-- font awesome style -->
  <link href="css/font-awesome.min.css" rel="stylesheet" />

  <!-- Custom styles for this template -->
  <link href="css/style.css" rel="stylesheet" />
  <!-- responsive style -->
  <link href="css/responsive.css" rel="stylesheet" />
</head>
<?php 

// Validação do username
if (isset($_GET['u'])) {
  $username = filter_var($_GET['u'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
} else {
  // die("Username não fornecido.");
}

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

          $horas_confirmacaoF = $minutos_aviso . ':00:00';
          $tel_whatsapp = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);
      } else {
          echo "Configurações não encontradas para a conta.";
      }
  } catch (PDOException $e) {
      echo "Erro ao buscar configurações: " . $e->getMessage();
  }

?>

<body class="sub_page">
  <div class="hero_area">
    <div class="hero_bg_box">
      <img src="images/banner<?php echo $id_conta?>.jpg" alt="">
      
    </div>
    <!-- header section strats -->
    <header class="header_section">
      <div class="container">
        <nav class="navbar navbar-expand-lg custom_nav-container ">
          <img src="sistema/img/logo<?php echo $id_conta?>.png" width="80px" style="filter: invert(100%); margin-right: 3px">
          <a class="navbar-brand " href="index"> <?php echo $nome_sistema ?> </a>

          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class=""> </span>
          </button>

          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav  ">
              <li class="nav-item active">
                <a class="nav-link" href="site.php?u=<?php echo $username?>">Home <span class="sr-only">(current)</span></a>
              </li>
              <?php
              if($agendamentos2 == 'Sim'){?>
               <li class="nav-item">
                <a class="nav-link" href="agendamentos?u=<?php echo $username?>"> Agendamentos</a>
              </li> <?php 
              }    

              if($produtos2 == 'Sim'){?>                    
              <li class="nav-item">
                <a class="nav-link" href="produtos?u=<?php echo $username?>">Produtos</a>
              </li><?php 
              }
              if($servicos2 == 'Sim'){?> 
               <li class="nav-item">
                <a class="nav-link" href="servicos?u=<?php echo $username?>">Serviços</a>
              </li><?php 
              }?>
             
             
              <li class="nav-item">
                <a title="Ir para o Sistema" class="nav-link" href="sistema" target="_blank"> <i class="fa fa-user" aria-hidden="true"></i> </a>
              </li>

                <li  class="nav-item">
                <a title="Ir para o Whatsapp" class="nav-link" href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whatsapp ?>" target="_blank"> <i class="fa fa-whatsapp" aria-hidden="true"></i> </a>
              </li>

               <li class="nav-item">
                <a title="Ver Instagram" class="nav-link" href="<?php echo $instagram_sistema ?>" target="_blank"> <i class="fa fa-instagram" aria-hidden="true"></i> </a>
              </li>
             
            </ul>
          </div>
        </nav>
      </div>
    </header>
    <!-- end header section -->