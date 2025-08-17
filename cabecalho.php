<?php require_once("sistema/conexao.php");

?>
<!DOCTYPE html>
<html>

<head>
  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Site Metas -->
  <meta name="keywords" content="sistema de gestão de serviços" />
  <meta name="description" content="sistema completo de gestão de serviços" />
  <meta name="author" content="Marcelo Ferreira" />
  <link rel="shortcut icon" href="./img/logo_SS.png" type="image/x-icon">

  <title>Sistema de Gestão de Serviços</title>

  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />

  <!-- fonts style -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!--owl slider stylesheet -->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
  <!-- font awesome style -->
  <!-- <link href="css/font-awesome.min.css" rel="stylesheet" /> -->
  <link href="https://use.fontawesome.com/releases/v5.0.1/css/all.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

   <!-- Custom styles for this template -->
  <link href="css/style2.css" rel="stylesheet" />
  <!-- responsive style -->
  <link href="css/responsive.css" rel="stylesheet" />

  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

 
  
</head>


<!-- VERIFICANDO SE TEM REPRESENTANTE -->
<?php 
// Capturando o ID do vendedor da URL
if(isset($_GET['id'])){
    $id_repr = $_GET['id'];

    if($url2[1] == 'localhost/'){				
      //VARIAVEIS DO BANCO DE DADOS LOCAL
      $servidor_bd = 'localhost';
      $banco_bd = 'gestao_sistemas';
      $usuario_bd = 'root';
      $senha_bd = '';	
    }else{				
      //VARIAVEIS DO BANCO DE DADOS HOSPEDADO
      $servidor_bd = 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
      $usuario_bd = 'skysee';
      $senha_bd = '9vtYvJly8PK6zHahjPUg';
      $banco_bd = 'gestao_sistemas';	
    }

    $pdo2 = new PDO("mysql:dbname=$banco_bd;host=$servidor_bd;charset=utf8", "$usuario_bd", "$senha_bd");
    $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Lança exceções em caso de erro

    $query2 = $pdo2->query("SELECT * FROM usuarios where id = '$id_repr'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);

    if(count($res2) > 0){      
        // Criando uma sessão para o vendedor        
        $_SESSION['id_repr'] = $res2[0]['id'];
        
    }       
}

?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-R39PP1R46W"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-R39PP1R46W');
</script>

<body class="sub_page" style="background-color:rgb(224, 224, 224);">
  <div class="hero_area">         
      <nav class="navbar navbar-expand-lg navbar-dark" id="cabecalho" style="background-color: #483D8B;">
        <a class="navbar-brand" href="#">MARKAI - Gestão de Serviços</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Alterna navegação">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav">
            <li class="nav-item active">
              <a class="nav-link" href="index.php">Home <span class="sr-only">(Página atual)</span></a>
            </li>            
            <!-- <li class="nav-item">
              <a class="nav-link" id="section-11"  data-section="sessao-11" style="cursor: pointer;">Tutoriais</a>
            </li> -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Funcionalidades
              </a>
              
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Soluções
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                <a class="dropdown-item" href="sistema-para-barbearia.php" >Sistema para Barbearia</a>
                <a class="dropdown-item" href="sistema-para-salao-de-beleza.php" >Sistema para Salão de Beleza</a>                
                <a class="dropdown-item"  href="sistema-para-clinica-de-estetica.php">Sistema para Clinica de Estética</a>                
                <a class="dropdown-item"  href="sistema-para-esmalteria.php">Sistema para Esmalteria</a>                
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="precos.php" id="navbarDropdownMenuLink" role="button" aria-haspopup="true" aria-expanded="false">
                Preços
              </a>
              
            </li>
          </ul>
          <a href="plan-selection.html"><button class="botao2">Teste Grátis</button></a>
          <a href="login.php" class="botao">Acesse</a>
        </div>
      </nav>
     
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6ZYgfuA2A0+LcUGcQFMqOYPq5W5/K0/kOzUF4JLT94KtGz8uz2L0AqQXFkQgx" crossorigin="anonymous"></script>

<script>
    document.getElementById('videos').addEventListener('click', () => {    
    $('#modalVideos').modal('show');
});
</script>

    


    <style>
        .botao {
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            background-color: #DCDCDC;
            color: black;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            margin-left: 30px;
            outline: none !important;
        }

        .botao:hover {
            transform: scale(1.2);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }
        .botao2 {
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            margin-left: 30px;
            outline: none !important;
            
        }

        .botao2:hover {
            transform: scale(1.2);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }
        #botao_gratis {
          width: 200px;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;           
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            margin-left: 0px;
            outline: none !important;
            
        }

        #botao_gratis:hover {
            transform: scale(1.2);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }
        #botao_duvidas {
          width: 200px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 20px;           
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            margin-left: 0px;
            outline: none !important;
            
        }

        #botao_duvidas:hover {
            transform: scale(1.2);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }
                  
           

        .scroll-down {
          position: fixed;
          bottom: 20px;
          right: 700px;
          background-color: transparent;
          border: none;          
          padding: 0px;          
          cursor: pointer;         
          transition: all 0.3s ease-in-out; /* Add transition for smooth effects */
          outline: none; /* Remove o contorno padrão do navegador */
          border: none; /* Remove qualquer borda definida */
          outline: none !important; 

        }
       
        .scroll-up{
          position: fixed;
          top: 20px;
          right: 700px;
          background-color: transparent;
          border: none;           
          padding: 0px;          
          cursor: pointer;          
          transition: all 0.3s ease-in-out; /* Add transition for smooth effects */
          outline: none !important; 

        }       
               
        
        #cabecalho{
          z-index: 2;
        }

        .btn-light {
          border-radius: 30px;
          margin-bottom: 25px;
          width: 250px;
          transition: all 0.3s ease-in-out;
        }

        .btn-light:hover {
          transform: scale(1.2);
          box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }


        /* Centraliza o conteúdo da seção */
        #sessao-1 {
            text-align: center;
        }

        #sessao-1 h2 {
            margin-bottom: 20px;
            font-size: 1.8rem;
            font-weight: bold;
        }

        /* Estiliza o contêiner de cada vídeo */
        .img-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Estiliza o título acima do vídeo */
        .video-title {
            margin: 0 0 10px 0;
            font-size: 1rem;
            font-weight: 600;
            color: #333;
        }

        /* Estiliza o vídeo */
        .box_img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Opcional: sombra suave */
        }

        /* Remove margens padrão da row */
        .row {
            margin: 0;
        }

        /* Ajuste para telas menores */
        @media (max-width: 768px) {
            .img-box {
                margin-bottom: 20px; /* Espaçamento vertical entre vídeos empilhados */
            }

            .col-6 {
                display: flex;
                justify-content: center; /* Centraliza as colunas em celulares */
            }

            .video-title {
                font-size: 0.9rem; /* Ajusta o tamanho do texto em celulares */
            }
        }

              

        
        @media (max-width: 767px) {
          /* Estilos para telas com largura máxima de 767px (comum em celulares) */
          .btn-light {
          border-radius: 30px;
          margin-bottom: 25px;
          width: 90px;
          transition: all 0.3s ease-in-out;
          font-size: 9px;
          margin-left: 15px;
        }

        .btn-light:hover {
          transform: scale(1.2);
          box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }

        #videos2 {
          position: absolute;
          bottom: 500px;
          background-color: transparent;  

        }

        #gratis2 {
          width: 60%;
        }
        }

        .content {
            display: flex;
            justify-content: center; /* Centraliza a imagem */
            align-items: center;
            padding: 50px;
        }

        .image {
            position: relative; /* Necessário para posicionar o overlay */
            width: 80%; /* Aumenta a largura da imagem */
            max-width: 1000px; /* Define um tamanho máximo */
            border-radius: 10px; /* Adiciona bordas arredondadas */
            overflow: hidden; /* Garante que o conteúdo não vaze das bordas arredondadas */
        }

        .image img {
            width: 100%;
            height: auto;
            display: block; /* Remove espaço extra abaixo da imagem */
        }

        .image-overlay {
            position: absolute; /* Posiciona o overlay sobre a imagem */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Adiciona um fundo semi-transparente */
            color: white; /* Define a cor do texto como branco */
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center; /* Centraliza o texto horizontalmente */
            padding: 20px;
            box-sizing: border-box; /* Garante que o padding não aumente o tamanho do overlay */
        }

        .text {
            width: 80%; /* Largura do texto dentro do overlay */
        }

        .text h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .text p {
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .buttons a {
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 5px;
            margin-right: 20px;
            font-weight: bold;
        }

        .buttons .trial-large {
            background-color: #3498db;
            color: white;
        }

        .buttons .sales {
            border: 1px solid #27ae60;
            color: #27ae60;
        }

        .section-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .section-card:hover {
            transform: translateY(-5px);
        }
        .section-icon {
            font-size: 2.5rem;
            color: #007bff;
            margin-right: 1rem;
        }


        /* Media Query para telas pequenas (celulares) */
@media (max-width: 768px) {
    .content {
        padding: 20px; /* Reduz o padding */
        flex-direction: column; /* Empilha os elementos verticalmente */
    }

    .image {
        width: 100%; /* Ocupa toda a largura */
        margin-bottom: 20px;
        margin-top: 20px;
    }

    .text {
        width: 100%; /* Ocupa toda a largura */
        padding: 0 10px; /* Pequeno padding interno */
    }

    .text h1 {
        font-size: 1.8em; /* Reduz o tamanho do título */
    }

    .text p {
        font-size: 1em; /* Reduz o tamanho do parágrafo */
    }

    .buttons {
        flex-direction: column; /* Garante que os botões fiquem em coluna */
        align-items: center; /* Centraliza os botões */
        gap: 10px; /* Espaçamento vertical entre botões */
    }

    .buttons a {
        padding: 10px 20px; /* Reduz o padding dos botões */
        min-width: 150px; /* Ajusta a largura mínima */
        width: 100%; /* Garante que o botão ocupe a largura total do container */
        box-sizing: border-box; /* Inclui padding na largura total */
        margin: 0; /* Remove margens laterais para evitar sobreposição */
        font-size: 12px;
    }

    .scroll-up, .scroll-down {
        font-size: 1.2em; /* Reduz o tamanho dos ícones */
    }

    .scroll-up {
        bottom: 70px; /* Ajusta posição para evitar sobreposição */
    }

    .scroll-down {
        bottom: 20px;
    }
    
}

/* Ajustes para telas muito pequenas (ex.: smartphones em modo retrato) */
@media (max-width: 480px) {
    .text h1 {
        font-size: 1.5em; /* Título ainda menor */
    }

    .text p {
        font-size: 0.9em; /* Parágrafo ainda menor */
    }

    .buttons a {
        width: 120px; /* Largura mínima ainda menor */
        padding: 8px 15px; /* Padding reduzido */
        font-size: 10px;
        margin-bottom: 30px;
        
    }

   
}
       
       
    </style>    
    <!-- end header section -->