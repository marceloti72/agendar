<?php require_once("sistema/conexao2.php") ?>
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
  <link rel="shortcut icon" href="<?php echo $url?>images/favicon<?php echo $id_conta?>.png" type="image/x-icon">

  <title><?php echo $nome_sistema ?></title>

  <!-- Inclua o Bootstrap CSS e JS -->

  
  <!-- Bootstrap CSS (escolha uma versão, recomendo 5.3 via CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
  <!-- Remova o local se optar pelo CDN: <link rel="stylesheet" type="text/css" href="css/bootstrap.css" /> -->

  <!-- Font Awesome (use apenas uma versão, recomendo CDN) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Remova o local se optar pelo CDN: <link href="css/font-awesome.min.css" rel="stylesheet" /> -->

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Slick CSS -->
  <!-- Slick CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- Custom Styles -->
  <link href="<?php echo $url?>css/style.css" rel="stylesheet" />
  <link href="<?php echo $url?>css/responsive.css" rel="stylesheet" />

  <!-- (Opcional) Owl Carousel, descomente se estiver usando -->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
</head>
  


<style>
    .header_section {
        background-color: #ffffff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 10px 0;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .custom_nav-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .navbar-brand {
        display: flex;
        align-items: center;
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .navbar-brand:hover {
        color: #007bff;
    }

    .navbar-brand img {
        width: 80px;
        filter: none;
        margin-right: 10px;
        border-radius: 50%; /* Logo redonda em todos os tamanhos */
        transition: transform 0.3s ease;
    }

    .navbar-brand img:hover {
        transform: scale(1.05);
    }

    .navbar-nav .nav-link {
        color: #333;
        font-size: 1rem;
        padding: 10px 15px;
        transition: color 0.3s ease, background-color 0.3s ease;
        border-radius: 5px;
    }

    .navbar-nav .nav-link:hover {
        color: #007bff;
        background-color: rgba(0, 123, 255, 0.1);
    }

    .navbar-nav .nav-item.active .nav-link {
        color: #007bff;
        font-weight: 500;
    }

    .navbar-toggler {
        border: none;
        color: #333;
    }

    .navbar-toggler:focus {
        box-shadow: none;
    }

    .social-icons .nav-link {
        font-size: 1.2rem;
        padding: 10px;
        color: #555;
    }

    .social-icons .nav-link:hover {
        color: #007bff;
    }

    .btn-box{
        border-radius: 15px;
            
        }

    @media (max-width: 991px) {
        .navbar-collapse {
            background-color: rgb(255, 255, 255);
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-nav .nav-link {
            padding: 10px;
        }
    }

    /* Media query para dispositivos móveis */
    @media (max-width: 768px) {
        .header_section .navbar {
            padding: 10px 0;
            display: flex;
            justify-content: space-between; /* Espaça a logo e o ícone */
            align-items: center;
        }

        .header_section .navbar-brand {
            display: flex;
            justify-content: center;
            width: auto; /* Ajusta para não ocupar toda a largura */
            margin: 0 auto; /* Centraliza a logo */
        }

        .header_section .navbar-brand img {
            width: 150px;
            margin-right: 0;
        }

        .header_section .navbar-brand span, /* Esconde o texto do nome do sistema */
        .header_section .navbar-brand b /* Esconde o texto em <b> */
        {
            display: none;
        }

        .navbar-collapse {
            background-color: rgb(255, 255, 255);
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-nav .nav-link {
            padding: 10px;
        }

        .header_section .navbar-toggler {
            display: block; /* Mostra o ícone de hambúrguer */
            position: absolute;
            right: 15px; /* Posiciona o ícone à direita */
        }

        #nome {
            font-size: 12px;
        }
        #titulo_servicos {
            font-size: 30px;
        }
        #titulo_produtos {
            font-size: 30px;
        }
        #contato {
            font-size: 30px;
        }
        #depoimentos {
            font-size: 20px;
        }

        #botao_servicos{            
            font-size: 12px;
        }

        .btn-box{
            font-size: 10px;
        }

            
    }
</style>
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


    <!-- header section starts -->
<header class="header_section">
    <nav class="navbar navbar-expand-lg custom_nav-container">
        <a class="navbar-brand" href="site.php?u=<?php echo $username?>">
            <img src= "sistema/img/logo<?php echo $id_conta?>.png" alt="Logo"><b id="nome">
            <?php echo $nome_sistema ?></b>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fas fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="site.php?u=<?php echo $username?>">Home <span class="sr-only">(current)</span></a>
                </li>
                <?php if ($agendamentos2 == 'Sim') { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="agendamentos.php?u=<?php echo $username?>">Agendamentos</a>
                    </li>
                <?php } ?>
                <?php if ($produtos2 == 'Sim') { ?>
                    <li class="nav-item active">
                        <a class="nav-link active" href="produtos.php?u=<?php echo $username?>">Produtos</a>
                    </li>
                <?php } ?>
                <?php if ($servicos2 == 'Sim') { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="servicos.php?u=<?php echo $username?>">Serviços</a>
                    </li>
                <?php } ?>
                <li class="nav-item social-icons active">
                    <a title="Ir para o Sistema" class="nav-link" href="login.php" target="_blank">
                        <i class="fas fa-user"></i>
                    </a>
                </li>
                <li class="nav-item social-icons active">
                    <a title="Ir para o Whatsapp" class="nav-link" href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whatsapp ?>" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </li>
                <li class="nav-item social-icons active">
                    <a title="Ver Instagram" class="nav-link" href="<?php echo $instagram_sistema ?>" target="_blank">
                        <i class="fab fa-instagram"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>
    <!-- end header section -->