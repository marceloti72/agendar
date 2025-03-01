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
  <meta name="keywords" content="sistema de gestão escolar" />
  <meta name="description" content="sistema completo de gestão escolar" />
  <meta name="author" content="Marcelo Ferreira" />
  <link rel="shortcut icon" href="./img/logo_SS.png" type="image/x-icon">

  <title>Sistema de Gestão Escolar</title>

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
<script async src="https://www.googletagmanager.com/gtag/js?id=G-SGLLYY1R8P"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-SGLLYY1R8P');  
</script>

<body class="sub_page">
  <div class="hero_area">         
      <nav class="navbar navbar-expand-lg navbar-dark" id="cabecalho" style="background-color: #483D8B;">
        <a class="navbar-brand" href="#">EDUK - Gestão Escolar</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Alterna navegação">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav">
            <li class="nav-item active">
              <a class="nav-link" href="index.php">Home <span class="sr-only">(Página atual)</span></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="representante.php">Seja um Representante</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="section-11"  data-section="sessao-11" style="cursor: pointer;">Tutoriais</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Portais
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                <a class="dropdown-item" id="section-2" data-section="sessao-2">Portal do Aluno</a>
                <a class="dropdown-item"  id="section-3" data-section="sessao-3">Portal do Professor</a>
                <a class="dropdown-item" id="section-4" data-section="sessao-4">Portal do Responsável</a>
                <a class="dropdown-item" id="section-5" data-section="sessao-5">Portal da Secretaria</a>
                <a class="dropdown-item" id="section-6" data-section="sessao-6">Portal da Tesouraria</a>
                <a class="dropdown-item" id="section-7" data-section="sessao-7">Portal do Administrador</a>
                <a class="dropdown-item" id="section-8" data-section="sessao-9">Portal do Cuidador/Mediador</a>
                <a class="dropdown-item" id="section-9" data-section="sessao-8">Portal do Almoxarifado</a>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Integrações
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                <a class="dropdown-item" id="section-10" data-section="sessao-10">WhatsApp</a>
                <a class="dropdown-item"  id="section-12" data-section="sessao-11">Mercado Pago</a>                
                <a class="dropdown-item"  id="section-13" data-section="sessao-12">Diário de Classe</a>                
                <a class="dropdown-item"  id="section-14" data-section="sessao-13">Biblioteca</a>                
              </div>
            </li>
          </ul>
          <button class="botao2" id="section-12" data-section="sessao-12">Teste Grátis</button>
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
        #videos {
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
            
        }

        #videos:hover {
            transform: scale(1.2);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }

        .full-screen-image {
          width: 100vw;
          height: 100vh;
          object-fit: cover; /* ou object-fit: contain */
        }        
        #tela {
          width: 100vw;
          height: 100vh;
          object-fit: cover; /* ou object-fit: contain */
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
        #quem_somos{
          position: absolute;          
          top: 460px;
          right: 870px;
          background-color: white;
          border: none;           
          padding: 10px;  
          border-radius: 20px;        
          cursor: pointer;          
          transition: all 0.3s ease-in-out; /* Add transition for smooth effects */
          outline: none !important; 

        }  
        
        #quem_somos:hover {
            transform: scale(1.2);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
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

       
       
    </style>    
    <!-- end header section -->