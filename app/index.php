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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    
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
          $id_conta = $config['id'];
          $agendamentos2 = $config['agendamentos'];
          $produtos2 = $config['produtos'];
          $servicos2 = $config['servicos'];
          $assinaturas2 = $config['assinaturas'];
          $depoimentos2 = $config['depoimentos'];
          $carrossel = $config['carrossel'];
          $encaixe = $config['encaixe'];
		  $satisfacao = $config['satisfacao'];
          $access_token = $config['token_mp'];
	      $public_key = $config['key_mp'];

          $horas_confirmacaoF = $minutos_aviso . ':00:00';
          $tel_whatsapp = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);

          $_SESSION['id_conta'] = $id_conta;
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
                    <?php 
                    if($agendamentos2 == 'Sim'){
                    ?>

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
                    <?php }?>

                    <?php 
                    if($servicos2 == 'Sim'){
                    ?>
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
                    <?php }?>

                    <?php 
                    if($produtos2 == 'Sim'){
                    ?>
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
                    <?php }?>

                    <?php 
                    if($agendamentos2 == 'Sim'){
                    ?>
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
                    <?php }?>

                    <?php 
                    if($assinaturas2 == 'Sim'){
                    ?>
                    <div id="cubo">
                        
                        <article class="box-cubo">
                            
                            <div>
                                <p class="cubo-text"><a href="#" data-toggle="modal" data-target="#modalAssinaturas" title="Blog">Clube do Assinante</a></p>
                            </div>
                            
                            <!-- <div>
                                <p class="cubo-text-transformed">Planos de Assinatura</p>
                            </div> -->

                        </article>
                        
                    </div>
                    <?php }?>

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
                    <?php 
                    if($depoimentos2 == 'Sim'){
                    ?>

                    <div id="cubo">
                        
                        <article class="box-cubo">
                            
                            <div>
                                <p class="cubo-text"><a href="#" data-toggle="modal" data-target="#modalVerDepoimentos" title="Portfólio">Depoimentos</a></p>
                            </div>
                            
                            <div>
                                <p class="cubo-text-transformed">Depoimentos</p>
                                
                            </div>

                        </article>
                        
                    </div>
                    <?php }?>

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
                            
                            <!-- <div>
                                <p class="cubo-text-transformed">Acesso Restrito</p>
                                
                            </div> -->

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

<div class="modal fade" id="modalVerDepoimentos" tabindex="-1" role="dialog" aria-labelledby="modalDepoimentosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
        <?php
        // Busca os depoimentos
        $query_modal = $pdo->query("SELECT * FROM comentarios where ativo = 'Sim' and id_conta = '$id_conta' ORDER BY id desc"); // Ordenar por ID desc para mais recentes primeiro?
        $res_modal = $query_modal->fetchAll(PDO::FETCH_ASSOC);
        $total_reg_modal = count($res_modal);
        ?>

            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h5 class="modal-title" id="modalDepoimentosLabel">Depoimentos dos Clientes</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true" style="padding: 5px;">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="text-center btn-inserir-depoimento mb-4">
                     <a style="border-radius: 15px;" href="#" class="btn btn-success" data-toggle="modal" data-target="#modalComentario2" data-dismiss="modal">
                         <i class="fa fa-plus"></i> Deixar Meu Depoimento
                     </a>
                 </div>
                 <hr> 

                <?php if ($total_reg_modal > 0): ?>
                    <?php foreach ($res_modal as $item_modal):
                        // Sanitiza os dados para exibição segura
                        $nome_modal = htmlspecialchars($item_modal['nome']);
                        $texto_modal = nl2br(htmlspecialchars($item_modal['texto'])); // nl2br e htmlspecialchars
                        $foto_modal = htmlspecialchars($item_modal['foto']);
                        // Monta o caminho completo da imagem - AJUSTE ESTE CAMINHO
                        $caminho_foto_modal = '../sistema/painel/img/comentarios/' . ($foto_modal ?: 'sem-foto.jpg');
                    ?>
                        <div class="depoimento-item">
                            <div class="depoimento-img">
                                <img src="<?php echo $caminho_foto_modal; ?>"
                                     alt="Foto de <?php echo $nome_modal; ?>"
                                     onerror="this.onerror=null; this.src='sistema/painel/img/comentarios/sem-foto.jpg';">
                            </div>
                            <div class="depoimento-texto">
                                <h5><?php echo $nome_modal; ?></h5>
                                <p><?php echo $texto_modal; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">Seja o primeiro a deixar um depoimento!</p>
                <?php endif; ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalComentario2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header text-white" style="background-color: #4682B4;">
          <h5 class="modal-title" id="exampleModalLabel">Inserir Depoimento
                   </h5>
          <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar">
          </button>
        </div>
        
        <form id="form2">
      <div class="modal-body">

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="exampleInputEmail1">Nome</label>
                <input type="text" class="form-control" id="nome_cliente2" name="nome" placeholder="Nome" required>    
              </div>  
            </div>
            <div class="col-md-12">

              <div class="form-group">
                <label for="exampleInputEmail1">Texto <small>(Até 500 Caracteres)</small></label>
                <textarea maxlength="500" class="form-control" id="texto_cliente2" name="texto" placeholder="Texto Comentário" required> </textarea>   
              </div>  
            </div>
          </div>               

            <div class="row">
              <div class="col-md-8">            
                <div class="form-group"> 
                  <label>Foto</label> 
                  <input class="form-control" type="file" name="foto" onChange="carregarImg();" id="foto2">
                </div>            
              </div>
              <div class="col-md-4">
                <div id="divImg">
                  <img src="../sistema/painel/img/comentarios/sem-foto.jpg"  width="80px" id="target2">                  
                </div>
              </div>
            </div>          
            <input type="hidden" name="id" id="id">
             <input type="hidden" name="cliente" value="1">

          <br>
          <small><div id="mensagem-comentario2" align="center"></div></small>
        </div>

        <div class="modal-footer text-white" style="background-color: #4682B4;">      
          <button type="submit" class="btn btn-warning">Inserir</button>
        </div>
      </form>

      </div>
    </div>
  </div>
</div>


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



<div class="modal fade" id="modalAssinaturas">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content text-white" style="background-color: #4682B4;">
            <div class="modal-header">Planos de Assinaturas</div>
            <div class="modal-body">

                <div class="text-center btn-inserir-depoimento mb-4">

            <button type="button" id="btnIniciarBuscaAssinatura" class="btn btn-info btn-sm">
                Ver Assinatura
            </button>
                    <hr>
                </div>

                <div class="row justify-content-center">
                    <?php
                    try {
                        // Busca os planos ativos para a conta atual, ordenados
                        $query_planos = $pdo->prepare("SELECT * FROM planos WHERE id_conta = :id_conta ORDER BY ordem ASC, id ASC");
                        $query_planos->execute([':id_conta' => $id_conta]);
                        $planos = $query_planos->fetchAll(PDO::FETCH_ASSOC);

                        if (count($planos) > 0) {
                            foreach ($planos as $plano) {
                                $id_plano_atual = $plano['id'];
                                $nome_plano = htmlspecialchars($plano['nome']);
                                $preco_mensal_plano = number_format($plano['preco_mensal'], 2, ',', '.');
                                $imagem_plano = htmlspecialchars($plano['imagem'] ?: 'default-plano.jpg'); // Imagem padrão
                                $caminho_imagem_plano = '../images/' . $imagem_plano; // AJUSTE O CAMINHO

                                // Busca os serviços associados a este plano
                                $query_servicos_plano = $pdo->prepare("
                                    SELECT ps.quantidade, s.nome
                                    FROM planos_servicos ps
                                    JOIN servicos s ON ps.id_servico = s.id
                                    WHERE ps.id_plano = :id_plano AND ps.id_conta = :id_conta
                                    ORDER BY s.nome ASC
                                ");
                                $query_servicos_plano->execute([':id_plano' => $id_plano_atual, ':id_conta' => $id_conta]);
                                $servicos_incluidos = $query_servicos_plano->fetchAll(PDO::FETCH_ASSOC);

                                // Determina a classe do botão (exemplo)
                                $btn_class = 'btn-primary';
                                if (strtolower($plano['nome']) == 'ouro') $btn_class = 'btn-warning';
                                if (strtolower($plano['nome']) == 'diamante') $btn_class = 'btn-dark';
                                if (strtolower($plano['nome']) == 'bronze') $btn_class = 'btn-outline-primary';


                    ?>
                                <div class="col-md-6 col-lg-5 mb-4">
                            <div class="plano-item card h-100 shadow-sm text-center"> 
                                <img src="<?php echo $caminho_imagem_plano; ?>"
                                     class="card-img-top plano-img mt-3 mx-auto d-block" 
                                     alt="Plano <?php echo $nome_plano; ?>"
                                     onerror="this.onerror=null; this.src='images/planos/default-plano.jpg';">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title plano-titulo"><?php echo $nome_plano; ?></h5>
                                    
                                    <p class="plano-preco">
                                        <strong>R$ <?php echo $preco_mensal_plano; ?></strong> / mês
                                    </p>

                                    <?php // Exibe Preço Anual SE existir e for maior que zero
                                    if (!empty($plano['preco_anual']) && $plano['preco_anual'] > 0):
                                        $preco_anual_plano = number_format($plano['preco_anual'], 2, ',', '.');
                                    ?>
                                        <p class="plano-preco-anual small text-muted mb-3"> 
                                            ou R$ <?php echo $preco_anual_plano; ?> / ano
                                            <?php
                                                // Opcional: Calcular e mostrar economia anual
                                                $economia = ($plano['preco_mensal'] * 12) - $plano['preco_anual'];
                                                if ($economia > 0) {
                                                     echo '<br><span class="text-success font-weight-bold">(Economize R$ ' . number_format($economia, 2, ',', '.') . '!)</span>';
                                                }
                                            ?>
                                        </p>
                                    <?php else: ?>
                                        
                                         <div style="height: 3.5em;" class="mb-3"></div>
                                    <?php endif; ?>
                                    
                                    <ul class="list-unstyled mt-3 mb-4 plano-beneficios text-left"> 
                                        <?php if (count($servicos_incluidos) > 0): ?>
                                            <?php foreach ($servicos_incluidos as $servico):
                                                $qtd_texto = '';
                                                if ($servico['quantidade'] == 0) { $qtd_texto = 'Ilimitado - '; }
                                                elseif ($servico['quantidade'] > 1) { $qtd_texto = $servico['quantidade'] . 'x '; }
                                            ?>
                                                <li><i class="fas fa-check text-success mr-2"></i><?php echo $qtd_texto . htmlspecialchars($servico['nome']); ?></li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li><small>Consulte os benefícios incluídos.</small></li>
                                        <?php endif; ?>                                     
                                    </ul>
                                    
                                    <button type="button" class="btn btn-lg btn-block <?php echo $btn_class; ?> btn-assinar mt-auto" data-toggle="modal" data-target="#modalAssinante2" data-plano="<?php echo $id_plano_atual; ?>">Assinar <?php echo $nome_plano; ?></button>
                                </div>
                            </div>
                        </div>
                    <?php
                            } // Fim foreach $planos
                        } else {
                            echo '<div class="col-12"><p class="text-center text-muted">Nenhum plano de assinatura disponível no momento.</p></div>';
                        }
                    } catch (PDOException $e) {
                         error_log("Erro ao buscar planos/serviços: " . $e->getMessage());
                         echo '<div class="col-12"><p class="text-center text-danger">Erro ao carregar os planos. Tente novamente mais tarde.</p></div>';
                    }
                    ?>
                </div> </div> 
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssinante2" tabindex="-1" role="dialog" aria-labelledby="modalAssinante2Label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="form-assinante2" method="post">
                <div class="modal-header text-white" style="background-color: #295f41;">
                    <h5 class="modal-title" id="modalAssinante2Label">Adicionar Assinatura</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar">
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_assinante" name="id_assinante"> 
                    <input type="hidden" id="id_cliente_encontrado" name="id_cliente_encontrado"> 
                    <input type="hidden" name="id_conta" value="<?= $id_conta ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ass_telefone">Telefone / WhatsApp <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="ass_telefone" name="telefone" placeholder="(DDD) Número" required onblur="buscarClientePorTelefone(this.value)">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="telefone-loading" style="display: none;"><i class="fas fa-spinner fa-spin"></i></span>
                                        <span class="input-group-text" id="telefone-status"></span>
                                    </div>
                                </div>
                                <small id="mensagem-busca-cliente" class="form-text"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ass_nome">Nome Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ass_nome" name="nome" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ass_cpf">CPF</label>
                                <input type="text" class="form-control" id="ass_cpf" name="cpf">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ass_email">Email</label>
                                <input type="email" class="form-control" id="ass_email" name="email">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ass_senha">Senha</label>
                                <input type="password" class="form-control" id="ass_senha" name="senha" placeholder="Digite a senha">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ass_confirma_senha">Confirmar Senha</label>
                                <input type="password" class="form-control" id="ass_confirma_senha" name="confirma_senha" placeholder="Confirme a senha">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ass_plano_freq">Plano e Frequência <span class="text-danger">*</span></label>
                                <select class="form-control" id="ass_plano_freq" name="plano_freq_selecionado" required>
                                    <option value="">-- Selecione o Plano e a Frequência --</option>
                                    <?php
                                    $planos_disponiveis = [];
                                    try {
                                        $query_p = $pdo->prepare("SELECT id, nome FROM planos WHERE id_conta = :id_conta ORDER BY nome ASC");
                                        $query_p->execute([':id_conta' => $id_conta]);
                                        $planos_disponiveis = $query_p->fetchAll(PDO::FETCH_ASSOC);
                                    } catch (PDOException $e) {
                                        error_log("Erro ao buscar planos para modal: " . $e->getMessage());
                                    }
                                    if(isset($planos_disponiveis) && count($planos_disponiveis) > 0){
                                        foreach ($planos_disponiveis as $plano_opt):
                                            $id_plano_opt = $plano_opt['id'];
                                            $nome_plano_opt = htmlspecialchars($plano_opt['nome']);
                                            $query_precos = $pdo->prepare("SELECT preco_mensal, preco_anual FROM planos WHERE id = :id AND id_conta = :id_conta ORDER BY id ASC");
                                            $query_precos->execute([':id' => $id_plano_opt, ':id_conta' => $id_conta]);
                                            $precos = $query_precos->fetch(PDO::FETCH_ASSOC);
                                            if ($precos) {
                                                $preco_m_fmt = number_format($precos['preco_mensal'], 2, ',', '.');
                                                echo "<option value='{$id_plano_opt}-30'>{$nome_plano_opt} - Mensal (R$ {$preco_m_fmt})</option>";
                                                if (!empty($precos['preco_anual']) && $precos['preco_anual'] > 0) {
                                                    $preco_a_fmt = number_format($precos['preco_anual'], 2, ',', '.');
                                                    echo "<option value='{$id_plano_opt}-365'>{$nome_plano_opt} - Anual (R$ {$preco_a_fmt})</option>";
                                                }
                                            }
                                        endforeach;
                                    } else {
                                        echo '<option value="">Nenhum plano cadastrado</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <small><div id="mensagem-assinante2" class="mt-2"></div></small>
                </div>
                <div class="modal-footer text-white" style="background-color: #295f41;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>                    
                    <button type="button" class="btn btn-warning" id="btnSalvarAssinante2" onclick="validarSenhas()">Assinar</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="modalPedirTelefone" tabindex="-1" aria-labelledby="modalPedirTelefoneLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm"> 
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #295f41;">
                <h5 class="modal-title" id="modalPedirTelefoneLabel">Buscar Assinatura</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <label for="inputTelefoneBusca" class="form-label">Digite o Telefone:</label>
                <input type="tel" class="form-control" id="inputTelefoneBusca" placeholder="(XX) XXXXX-XXXX" required>                 
                 <div class="invalid-feedback">Por favor, informe um telefone válido.</div>
                 <label for="senha2" class="form-label">Senhe:</label>
                 <input type="text" class="form-control" id="senha2" required>
            </div>
            <div class="modal-footer text-white" style="background-color: #295f41;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnBuscarPorTelefone">Buscar Detalhes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssinaturaDetalhes" tabindex="-1" aria-labelledby="modalAssinaturaDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> 
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #295f41;">
                <h5 class="modal-title" id="modalAssinaturaDetalhesLabel">Detalhes da Assinatura</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">               
                <div id="modalAssinaturaLoading" style="display: none;" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p>Buscando detalhes...</p>
                </div>
                
                <div id="modalAssinaturaErro" class="alert alert-danger" style="display: none;">
                    Não foi possível carregar os detalhes da assinatura.
                </div>
                
                <div id="modalAssinaturaConteudo">
                    <p><strong>Cliente:</strong> <span id="modalAssinaturaClienteNome" class="text-primary"></span></p>
                    <p><strong>Plano Atual:</strong> <span id="modalAssinaturaPlanoNome" class="fw-bold"></span></p>
                    <p><strong>Próximo Vencimento:</strong> <span id="modalAssinaturaProximoVenc" class="text-danger"></span></p>

                    <hr>
                    <h6>Serviços Incluídos e Uso no Ciclo Atual:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Serviço</th>
                                    <th class="text-center">Uso Atual</th>
                                    <th class="text-center">Limite no Ciclo</th>
                                </tr>
                            </thead>
                            <tbody id="modalAssinaturaServicosBody">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-white" style="background-color: #295f41;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>                
                
            </div>
        </div>
    </div>
</div>










<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Canvas Confetti -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script src="js/app.js"></script>

<script>

$('#telefone_rodape').mask('(00) 00000-0000');
$('#ass_telefone').mask('(00) 00000-0000');
$('#inputTelefoneBusca').mask('(00) 00000-0000');
$('#ass_cpf').mask('000.000.000-00', {reverse: true});
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


  $("#form2").submit(function () {
    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: '../sistema/painel/paginas/comentarios/salvar.php',
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem-comentario2').text('');
            $('#mensagem-comentario2').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {
            
            $('#mensagem-comentario2').addClass('text-success')
                Swal.fire({
                    position: "top-center",
                    icon: "success",
                    title: "Comentário Enviado para Aprovação!",
                    showConfirmButton: false,
                    timer: 1500,
                                    
                    willClose: () => {
                    setTimeout(() => {
                        $('#nome_cliente2').val('');
                        $('#texto_cliente2').val('');
                        window.location.reload();
                        }, 300);
                    }  
                });                 
                

            } else {
                $('#mensagem-comentario2').addClass('text-danger')
                $('#mensagem-comentario2').text(mensagem)
            }
        },
        cache: false,
        contentType: false,
        processData: false,
    });

  });

  function carregarImg() {
    var target = document.getElementById('target2');
    var file = document.querySelector("#foto2").files[0];
    
        var reader = new FileReader();

        reader.onloadend = function () {
            target.src = reader.result;
        };

        if (file) {
            reader.readAsDataURL(file);

        } else {
            target.src = "";
        }
    }



    $(document).ready(function() {
    // Listener para os botões "Assinar" dentro do modal
    $('#modalAssinaturas').on('click', '.btn-assinar', function() {
        const planoSelecionado = $(this).data('plano'); // Pega o valor de data-plano (bronze, prata, etc.)

        console.log("Plano selecionado:", planoSelecionado);
        // Adicione aqui a sua lógica para lidar com a seleção do plano:
        // - Redirecionar para uma página de pagamento/checkout com o plano selecionado
        //   Ex: window.location.href = '/checkout.php?plano=' + planoSelecionado;
        // - Fazer uma chamada AJAX para iniciar o processo de assinatura
        // - Etc.

        // Exemplo de feedback
        alert("Você selecionou o Plano " + planoSelecionado.charAt(0).toUpperCase() + planoSelecionado.slice(1) + "!");

        // Fecha o modal após a ação (opcional)
        $('#modalAssinaturas').modal('hide');
    });
});


function validarSenhas() {    
    const senha = document.getElementById('ass_senha').value;
    const confirmaSenha = document.getElementById('ass_confirma_senha').value;
    const mensagem = document.getElementById('mensagem-assinante2');

    if (senha && confirmaSenha && senha !== confirmaSenha) {
        mensagem.innerHTML = '<span class="text-danger">As senhas não coincidem.</span>';
        return;
    }

    // If passwords match or are empty, proceed with form submission    
    $('#form-assinante2').submit();
}


$('#form-assinante2').on('submit', function(e) {    
     e.preventDefault(); // Impede envio normal
     const form = this;
     const formData = new FormData(form); // Pega todos os dados, incluindo id_cliente_encontrado
     const $btnSubmit = $('#btnSalvarAssinante');
     const $msgDiv = $('#mensagem-assinante2');

     $btnSubmit.prop('disabled', true).text('Salvando...');
     $msgDiv.text('').removeClass('text-danger text-success');

     $.ajax({
        url: 'salvar_assinante.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        success: function(response) {
             if (response.success) {                
                 $msgDiv.addClass('text-success').text(response.message);
                  setTimeout(function() {                    
                     //$('#modalAssinante2').modal('hide');
                     window.location="../pagar_ass/"+response.id_receber;
                  }, 1500);
             } else {
                 $msgDiv.addClass('text-danger').text(response.message);
                 $btnSubmit.prop('disabled', false).text('Salvar Assinante');
             }
         },
         error: function(xhr) {
             $msgDiv.addClass('text-danger').text('Erro de comunicação. Verifique o console.');
             console.error("Erro ao salvar assinante:", xhr.responseText);
             $btnSubmit.prop('disabled', false).text('Salvar Assinante');
         }
     });
});


// Função para verificar se o telefone existe no banco de clientes e se está associado a um assinante ativo
function buscarClientePorTelefone(telefone) {
    // Se o telefone estiver vazio, limpa os campos e habilita o botão
    if (!telefone) {
        document.getElementById('mensagem-busca-cliente').innerHTML = '';
        document.getElementById('id_cliente_encontrado').value = '';
        document.getElementById('ass_nome').value = '';
        document.getElementById('ass_cpf').value = '';
        document.getElementById('ass_email').value = '';
        document.getElementById('telefone-loading').style.display = 'none';
        document.getElementById('telefone-status').innerHTML = '';
        document.getElementById('btnSalvarAssinante2').disabled = false;
        return;
    }

    // Exibe o ícone de carregamento
    document.getElementById('telefone-loading').style.display = 'inline-block';
    document.getElementById('mensagem-busca-cliente').innerHTML = '';
    document.getElementById('telefone-status').innerHTML = '';

    // Faz a requisição AJAX para verificar o cliente e o status de assinante
    fetch('verificar_telefone.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'telefone=' + encodeURIComponent(telefone) + '&id_conta=<?= $id_conta ?>'
    })
    .then(response => response.json())
    .then(data => {
        // Oculta o ícone de carregamento
        document.getElementById('telefone-loading').style.display = 'none';

        // Se houver erro na requisição
        if (data.error) {
            document.getElementById('mensagem-busca-cliente').innerHTML = '<span class="text-danger">' + data.error + '</span>';
            document.getElementById('id_cliente_encontrado').value = '';
            document.getElementById('ass_nome').value = '';
            document.getElementById('ass_cpf').value = '';
            document.getElementById('ass_email').value = '';
            document.getElementById('telefone-status').innerHTML = '<i class="fas fa-times text-danger"></i>';
            document.getElementById('btnSalvarAssinante2').disabled = false;
            document.getElementById('ass_telefone').focus();
            return;
        }

        // Se o cliente for encontrado
        if (data.cliente) {
            // Se o cliente já for um assinante ativo, bloqueia o formulário
            if (data.is_assinante) {
                document.getElementById('mensagem-busca-cliente').innerHTML = '<span class="text-danger">Erro: Este telefone já está associado a um assinante ativo. Insira outro número.</span>';
                document.getElementById('id_cliente_encontrado').value = '';
                document.getElementById('ass_nome').value = '';
                document.getElementById('ass_cpf').value = '';
                document.getElementById('ass_email').value = '';
                document.getElementById('telefone-status').innerHTML = '<i class="fas fa-times text-danger"></i>';
                document.getElementById('btnSalvarAssinante2').disabled = true;
                document.getElementById('ass_telefone').focus();
            } else {
                // Cliente encontrado, mas não é assinante: permite prosseguir
                document.getElementById('id_cliente_encontrado').value = data.cliente.id;
                document.getElementById('ass_nome').value = data.cliente.nome || '';
                document.getElementById('ass_cpf').value = data.cliente.cpf || '';
                document.getElementById('ass_email').value = data.cliente.email || '';
                document.getElementById('mensagem-busca-cliente').innerHTML = '<span class="text-success">Cliente encontrado.</span>';
                document.getElementById('telefone-status').innerHTML = '<i class="fas fa-check text-success"></i>';
                document.getElementById('btnSalvarAssinante2').disabled = false;
            }
        } else {
            // Nenhum cliente encontrado: permite prosseguir
            document.getElementById('id_cliente_encontrado').value = '';
            document.getElementById('ass_nome').value = '';
            document.getElementById('ass_cpf').value = '';
            document.getElementById('ass_email').value = '';
            document.getElementById('mensagem-busca-cliente').innerHTML = '<span class="text-info">Nenhum cliente encontrado com este telefone.</span>';
            document.getElementById('telefone-status').innerHTML = '<i class="fas fa-check text-success"></i>';
            document.getElementById('btnSalvarAssinante2').disabled = false;
        }
    })
    .catch(error => {
        // Em caso de erro na requisição
        document.getElementById('telefone-loading').style.display = 'none';
        document.getElementById('mensagem-busca-cliente').innerHTML = '<span class="text-danger">Erro ao verificar telefone.</span>';
        document.getElementById('id_cliente_encontrado').value = '';
        document.getElementById('ass_nome').value = '';
        document.getElementById('ass_cpf').value = '';
        document.getElementById('ass_email').value = '';
        document.getElementById('telefone-status').innerHTML = '<i class="fas fa-times text-danger"></i>';
        document.getElementById('btnSalvarAssinante2').disabled = false;
        document.getElementById('ass_telefone').focus();
        console.error('Erro:', error);
    });
}
</script>
</body>
</html>