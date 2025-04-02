<!DOCTYPE html>
<html lang="pt-br">
<?php 
    // Validação do username
    if (isset($_GET['u'])) {
        $username = filter_var($_GET['u'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    } else {
    // die("Username não fornecido.");
    }
    $url = "https://" . $_SERVER['HTTP_HOST'] . "/";
        
?>

 <head>
	
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="AGENDAR - Gestão de Sistema"/>
    <meta name="keywords" content="AGENDAR - Gestão de Sistema"/>
    <meta name="author" content="Skysee Soluções de TI" />    
    <meta http-equiv="content-language" content="pt-br" />
    <meta name="robots" content="index, follow"/>
    
    <?php 
    // Gera o link para o manifesto DINAMICAMENTE se o username for conhecido
    if (!empty($username)) {
        echo '<link rel="manifest" href="manifest.php?u=' . urlencode($username) . '">';
        // Você também pode querer definir a theme-color dinamicamente aqui, se aplicável
        echo '<meta name="theme-color" content="#4682B4">';
    } else {
        // Opcional: O que fazer se não houver username?
        // Talvez não mostrar o link do manifesto? Ou linkar para um genérico?
        // echo '<link rel="manifest" href="/manifest_generico.json">';
    }?>
    
    <!--[if lt IE 9]>
        <script src="js/html5shiv.js"></script>
    <![endif]-->   
    
    <!-- Font Awesome (use apenas uma versão, recomendo CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" 1  crossorigin="anonymous" referrerpolicy="no-referrer" 2  />

	<link rel="stylesheet" type="text/css" href="css/reset.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/fonts-icones.css">
    <link rel="stylesheet" href="css/botao.css">
    <link rel="shortcut icon" href="https://www.loopnerd.com.br/artigos/css3/menu-sidebar-rotacao-3d/img/favicon.png" type="image/ico" />

    <!-- Custom Styles -->
  <link href="<?php echo $url?>css/style.css" rel="stylesheet" />
  <link href="<?php echo $url?>css/responsive.css" rel="stylesheet" />

  <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(function(OneSignal) {
    OneSignal.init({
      appId: "00d0cf8f-910f-4ff1-819d-1a429d468a4e",
      //safari_web_id: "YOUR_SAFARI_WEB_ID",
      notifyButton: {
        enable: true,
      },
    });
  });
</script>
   
    

	<title>AGENDAR - Sistema de Gestão de Serviço</title>

 </head>

 <style>
    .modal-simples {
  display: none; /* Escondido por padrão */
  position: fixed; /* Fica fixo na tela */
  z-index: 1000; /* Garante que fique acima de outros conteúdos */
  left: 0;
  top: 0;
  width: 100%; /* Largura total */
  height: 100%; /* Altura total */
  overflow: auto; /* Habilita scroll se o conteúdo for maior */
  background-color: rgba(0, 0, 0, 0.5); /* Fundo preto semi-transparente */
  /* Para centralizar o conteúdo com Flexbox */
  display: none; /* Sobrescrito por JS para flex */
  align-items: center;
  justify-content: center;
}

/* Conteúdo/Caixa do Modal */
.modal-conteudo {
  background-color: #fefefe;
  margin: auto; /* Centraliza verticalmente (alternativa ao flex) */
  padding: 25px;
  border: 1px solid #ccc;
  width: 90%; /* Largura em telas pequenas */
  max-width: 400px; /* Largura máxima para o modal pequeno */
  border-radius: 8px;
  position: relative; /* Para posicionar o botão de fechar */
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  animation-name: animatetop; /* Animação opcional */
  animation-duration: 0.4s
}

/* Animação opcional de entrada */
@keyframes animatetop {
  from {top:-300px; opacity:0}
  to {top:0; opacity:1}
}

/* Botão de Fechar (x) */
.modal-fechar {
  color: #aaa;
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 28px;
  font-weight: bold;
  background: none;
  border: none;
  cursor: pointer;
  line-height: 1; /* Alinha melhor o 'x' */
}

.modal-fechar:hover,
.modal-fechar:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

/* Estilos básicos para o formulário dentro do modal */
.modal-conteudo h2 {
    margin-top: 0;
    margin-bottom: 20px;
    text-align: center;
}

.modal-conteudo .form-grupo {
    margin-bottom: 15px;
}

.modal-conteudo label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.modal-conteudo input[type="text"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box; /* Importante para o padding não aumentar a largura */
}

.modal-conteudo .btn-cadastrar {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 15px;
    cursor: pointer;
    width: 100%; /* Botão ocupa largura total */
    font-size: 1em;
    margin-top: 10px;
}

.modal-conteudo .btn-cadastrar:hover {
    background-color: #0056b3;
}

.modal-conteudo #mensagem-rodape2 {
    margin-top: 15px;
    text-align: center;
    min-height: 1.2em; /* Garante espaço mesmo sem mensagem */
}
.modal-conteudo #mensagem-rodape2.text-success {
    color: green;
}
.modal-conteudo #mensagem-rodape2.text-danger {
    color: red;
}
 </style>

<body>

<?php 



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
          $facebook_sistema = htmlspecialchars($config['facebook']);
          $tiktok_sistema = htmlspecialchars($config['tiktok']);
          $x_sistema = htmlspecialchars($config['x']);
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
    
<header class="main_header container">        
    <div class="content">
    
        <div class="main_header_logo">
            <!-- <a href="https://www.agendar.skysee.com.br" target="_blank"><img src="img/logo.png" alt="logo.png" title="Loop Nerd"/></a> -->

            <img src= "../sistema/img/logo<?php echo $id_conta?>.png" alt="Logo"><b id="nome">
            
        </div>
    
    </div>
</header>

<main class="main_content container">
        
    <section class="section-seu-codigo container">
        
        <div class="content">           
            
            
            <div class="box-artigo">
                
                <div class="sidebar">

                    <div id="cubo">
                        
                        <article class="box-cubo">
                            
                            <div>
                                <p class="cubo-text"><a href="../agendamentos.php?u=<?php echo $username?>" title="Home">Agendar</a></p>
                            </div>
                            
                            <div>
                                <p class="cubo-text-transformed">Agendar</p>
                            </div>

                        </article>

                    </div>

                    <div id="cubo">
                        
                        <article class="box-cubo">
                            
                            <div>
                                <p class="cubo-text"><a href="../servicos?u=<?php echo $username?>" title="Sobre">Serviços</a></p>
                            </div>
                            
                            <div>
                                <p class="cubo-text-transformed">Serviços</p>
                            </div>

                        </article>

                    </div>

                    <div id="cubo">
                        
                        <article class="box-cubo">
                            
                            <div>
                                <p class="cubo-text"><a href="../produtos?u=<?php echo $username?>" title="Sobre">Produtos</a></p>
                            </div>
                            
                            <div>
                                <p class="cubo-text-transformed">Produtos</p>
                            </div>

                        </article>

                    </div>

                    <div id="cubo">
                        
                        <article class="box-cubo">
                            
                            <div>
                                <p class="cubo-text"><a href="../meus-agendamentos.php?u=<?php echo $username?>" title="Blog">Meus agendamentos</a></p>
                            </div>
                            
                            <div>
                                <p class="cubo-text-transformed">Meus agendamentos</p>
                            </div>

                        </article>
                        
                    </div>

                    <div id="cubo">
                        
                        <article class="box-cubo">
                            
                            <div>
                                <p class="cubo-text"><a href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whatsapp ?>" target="_blank"
                                title="Contato">Contato</a></p>
                            </div>
                            
                            <div>
                                <p class="cubo-text-transformed">Contato</p>
                            </div>

                        </article>
                        
                    </div>

                    <div id="cubo">
                        
                        <article class="box-cubo">
                            
                            <div>
                                <p class="cubo-text"><a href="#" id="abrirModalCadastro" title="Portfólio">Cadastre-se</a></p>
                            </div>
                            
                            <div>
                                <p class="cubo-text-transformed">Cadastre-se</p>
                                
                            </div>

                        </article>
                        
                    </div>

                    <div id="cubo"><?php 
                        // --- DEFINA O ENDEREÇO COMPLETO DA BARBEARIA AQUI ---
                        // Certifique-se de que seja o mais preciso possível (rua, número, bairro, cidade, estado)
                        $enderecoBarbearia = $endereco_sistema; // << SUBSTITUA PELO ENDEREÇO REAL

                        // 1. Codificar o endereço para ser seguro em um URL
                        //    Isso substitui espaços por '+' ou '%20' e codifica caracteres especiais.
                        $enderecoCodificado = urlencode($enderecoBarbearia);

                        // 2. Montar o URL do Google Maps Directions
                        //    - api=1: Usa a interface de direções.
                        //    - destination=: Define o destino com o endereço codificado.
                        //    - IMPORTANTE: NÃO definimos o parâmetro 'origin'. Isso faz com que o
                        //      Google Maps peça/use a localização atual do usuário como origem.
                        $urlGoogleMaps = "https://www.google.com/maps/dir/?api=1&destination=" . $enderecoCodificado;

                        // 3. Gerar o link HTML clicável
                        ?>
                        <article class="box-cubo">
                            
                            <div>
                                <p class="cubo-text"><a href="<?php echo htmlspecialchars($urlGoogleMaps); ?>" target="_blank" title="Portfólio">Localização</a></p>
                            </div>
                            
                            <div>
                                <p class="cubo-text-transformed">Localização</p>
                            </div>

                        </article>
                        
                    </div>

                    <div id="cubo">
                        
                        <article class="box-cubo">
                            
                            <div>
                                <p class="cubo-text"><a href="../login.php" title="Portfólio">Acesso Restrito</a></p>
                            </div>
                            
                            <div>
                                <p class="cubo-text-transformed">Acesso Restrito</p>
                                
                            </div>

                        </article>
                        
                    </div>
                    
                    <button id="installButton" class="install-btn">
                        <!-- <span class="icon">⏰</span> Instalar o App -->
                        <img src="../images/icone_app.jpg" alt="Ícone do App" class="icon">Instalar o App
                    </button>

                </div><!--Sidebar-->    


            </div><!--Box Artigo-->


        <div class="clear"></div>
        </div>
    </section><!--FECHA BOX HTML-->

</main>

<div id="modalCadastroSimples" class="modal-simples">
  <div class="modal-conteudo">
    <span class="modal-fechar">&times;</span>
    <h2>Cadastro Rápido</h2>
    <form id="form_cadastro2" method="POST">
        <div class="form-grupo">
           <label for="telefone_rodape">Telefone:</label>
           <input type="text" name="telefone" id="telefone_rodape" placeholder="Seu Telefone DDD + número" required />
        </div>
        <div class="form-grupo">
           <label for="nome_rodape">Nome:</label>
           <input type="text" name="nome" id="nome_rodape" placeholder="Seu Nome" required />
        </div>
        <button type="submit" class="btn-cadastrar">Cadastrar</button>
        <small><div id="mensagem-rodape2"></div></small>
    </form>
  </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Canvas Confetti -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

<script src="js/app.js"></script>

<script>

$('#telefone_rodape').mask('(00) 00000-0000');
//$('#telefone_compra').mask('(00) 00000-0000');
    // Pega os elementos
var modal = document.getElementById("modalCadastroSimples");
var btnAbrir = document.getElementById("abrirModalCadastro");
var btnFechar = document.querySelector("#modalCadastroSimples .modal-fechar"); // Pega o botão dentro do modal

// Quando o usuário clicar no botão, abre o modal
btnAbrir.onclick = function() {
  modal.style.display = "flex"; // Usa flex para aproveitar o alinhamento do CSS
}

// Quando o usuário clicar no <span> (x), fecha o modal
btnFechar.onclick = function() {
  modal.style.display = "none";
}

// Quando o usuário clicar fora do modal, fecha-o também
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}

// Lógica AJAX para o formulário (adaptada do seu exemplo anterior, usando jQuery ainda)
// Se você NÃO estiver usando jQuery, precisará reescrever esta parte com XMLHttpRequest ou fetch API.
$(document).ready(function() { // Se você já tiver jQuery na página
    $("#form_cadastro2").submit(function (event) {        
        event.preventDefault(); // Impede o envio padrão do formulário
        var formData = new FormData(this);
        var $mensagemDiv = $('#mensagem-rodape2');

        $mensagemDiv.text('Enviando...').removeClass('text-success text-danger');
        


        $.ajax({
        url: '../ajax/cadastrar.php',
        type: 'POST',
        data: formData,
        success: function (mensagem) {
            $mensagemDiv.removeClass('text-danger');            
            if (mensagem.trim() == "Salvo com Sucesso") {                
                $mensagemDiv.addClass('text-success').text(mensagem);
                Swal.fire({
                    title: "Cadastro Efetuado! 😃",
                    text: "Você estará sempre atualizado com nossos serviços, produtos e promoções.",
                    icon: "success",
                    didOpen: () => {
                        // Dispara o confete
                        confetti({
                          particleCount: 150,
                          spread: 90,
                          origin: { y: 0.5 },
                          colors: ['#ff0000', '#00ff00', '#0000ff'], // Cores personalizadas (vermelho, verde, azul)
                          angle: 90,                          // Direção do lançamento
                          decay: 0.9,                         // Velocidade de desaceleração
                          startVelocity: 45                   // Velocidade inicial
                        });
                        // Ajusta o z-index do canvas do confete
                        setTimeout(() => {
                            const confettiCanvas = document.querySelector('canvas');
                            if (confettiCanvas) {
                                confettiCanvas.style.zIndex = '9999'; // Valor alto para ficar acima do Swal
                            }
                        }, 0);
                    }
                }).then((result) => {
                    document.getElementById("modalCadastroSimples").style.display = "none";
                    // if (result.isConfirmed) {
                    //     window.location = "index.php";
                    // }
                });
            } else {
                $mensagemDiv.addClass('text-danger').text(mensagem);
            }
            },
            cache: false,
            contentType: false,
            processData: false,
        });
    });        
});
</script>

<footer class="main_footer container">
    <div class="main_footer_copy">
        <h1>
            <a href="<?php echo $facebook_sistema?>" target="_blank" title="Siga-nos no Facebook" style="text-decoration: none; margin-right: 10px;">
                <i class="fab fa-facebook"></i>
            </a>
            <a href="<?php echo $instagram_sistema?>" target="_blank" title="Siga-nos no Instagram" style="text-decoration: none; margin-right: 10px;">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="<?php echo $tiktok_sistema?>" target="_blank" title="Siga-nos no TikTok" style="text-decoration: none; margin-right: 10px;">
                <i class="fab fa-tiktok"></i>
            </a>
            <a href="<?php echo $x_sistema?>" target="_blank" title="Siga-nos no X" style="text-decoration: none;">
                <i class="fa-brands fa-x-twitter"></i>
            </a>
        </h1><br>

        <small class="m-b-footer">
            Desenvolvido por: <a href="https://www.skysee.com.br" title="Skysee Soluções de TI" target="_blank">skysee.com.br</a>
        </small>
    </div>

    <style>
    /* Opcional: Estilo para os ícones dentro do H1 (melhor que inline) */
    .main_footer_copy h1 a {
        color: inherit; /* Herda a cor do H1 */
        text-decoration: none; /* Remove sublinhado */
        margin-right: 10px; /* Espaçamento entre ícones */
    }

    .main_footer_copy h1 a:last-child {
        margin-right: 0; /* Remove margem do último ícone */
    }

    .main_footer_copy h1 a:hover i {
        opacity: 0.8; /* Efeito leve ao passar o mouse */
    }
    </style>
</footer>



<script src="js/jquery.js"></script>
<script src="js/script.js"></script>

<script>
  // Verifica se o navegador suporta Service Workers
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => { // Registra após a página carregar
      navigator.serviceWorker.register('/app/sw.js') // Caminho para o seu Service Worker
        .then(registration => {
          console.log('Service Worker registrado com sucesso! Escopo:', registration.scope);
        })
        .catch(error => {
          console.error('Falha ao registrar o Service Worker:', error);
        });
    });
  }
</script>
</body>
</html>