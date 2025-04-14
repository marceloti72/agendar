<?php 
require_once("cabecalho2.php") ?>

<style>
 /* Estilos gerais da seção */
.product_section {
    padding: 60px 0;
    background-color: #f9f9f9;
}

.heading_container h2 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 20px;
}

.heading_container p {
    font-size: 1.1rem;
    color: #666;
}

.box {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.box:hover {
    transform: translateY(-10px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.img-box img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}

.detail-box {
    padding: 20px;
    text-align: center;
}

.detail-box h4 {
    font-size: 1.2rem;
    color: #222;
    margin-bottom: 10px;
}

.detail-box .price .new_price {
    font-size: 1.3rem;
    color: #e67e22;
    font-weight: bold;
}

.detail-box a {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #007bff;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.detail-box a:hover {
    background-color: #0056b3;
}

/* Estilos dos controles do Slick */
.slick-dots li button:before {
    font-size: 12px;
    color: #007bff;
}

.slick-dots li.slick-active button:before {
    color: #0056b3;
}

/* Ajustes responsivos para telas pequenas */
@media (max-width: 580px) {
    .slick-services {
        margin: 0;
    }

    .slick-services .slick-slide {
        padding: 5px;
        opacity: 0; /* Esconde todos os slides por padrão */
        transition: opacity 0.3s ease;
    }

    .slick-services .slick-slide.slick-active {
        opacity: 1; /* Mostra apenas o slide ativo */
    }

    .slick-services .box {
        width: 100%;
        max-width: 100%;
    }

    .slick-services .img-box img {
        height: 150px;
    }

    .slick-services .detail-box h4 {
        font-size: 1rem;
    }

    .slick-services .detail-box .new_price {
        font-size: 1.1rem;
    }

    .slick-services .detail-box a {
        padding: 8px 16px;
        font-size: 0.9rem;
    }
}



</style>

<body class="sub_page">

    

  <div class="hero_area">
    <div class="hero_bg_box">
      <img src="images/banner<?php echo $id_conta?>.jpg" alt="">
      
    </div>


<?php 

if($carrossel == 'Sim'){
  $query = $pdo->query("SELECT * FROM textos_index where id_conta = '$id_conta' ORDER BY id asc");
  $res = $query->fetchAll(PDO::FETCH_ASSOC);
  $total_reg = @count($res);
  if($total_reg > 0){
    ?>

    <section class="slider_section ">
      <div id="customCarousel1" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">

            <?php 
            for($i=0; $i < $total_reg; $i++){
              foreach ($res[$i] as $key => $value){}
              $id = $res[$i]['id'];
              $titulo = $res[$i]['titulo'];
              $descricao = $res[$i]['descricao'];

              $descricaoF = mb_strimwidth($descricao, 0, 50, "...");

              if($i == 0){
                $ativo = 'active';
              }else{
                $ativo = '';
              }
            ?>

              <div class="carousel-item <?php echo $ativo ?>">
                <div class="container ">
                  <div class="row">
                    <div class="col-md-6 ">
                      <div class="detail-box">
                        <h1>
                        <?php echo $titulo ?>
                        </h1>
                        <p>
                        <?php echo $descricao ?>
                        </p>
                        <div class="btn-box">
                          <a href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whatsapp ?>" target="_blank" class="btn1">
                            Contate-nos <i class="fa fa-whatsapp"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php 
            } ?>

            
          </div>
          <div class="container">
            <div class="carousel_btn-box">
              <a class="carousel-control-prev" href="#customCarousel1" role="button" data-slide="prev">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span class="sr-only">Previous</span>
              </a>
              <a class="carousel-control-next" href="#customCarousel1" role="button" data-slide="next">
                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                <span class="sr-only">Next</span>
              </a>
            </div>
          </div>
        </div>
      </section>
    </div>
     

  <?php }
  }else{?>
     </div>
      <?php      
  }

  if ($servicos2 == 'Sim') { ?>
    <section class="product_section layout_padding">
        <div class="container-fluid">
            <div class="heading_container heading_center">
                <h2 id="titulo_servicos">Nossos Serviços</h2>
                <p class="col-lg-8 px-0">
                    <?php 
                    $query = $pdo->query("SELECT * FROM cat_servicos WHERE id_conta = '$id_conta' ORDER BY id ASC");
                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                    $total_reg = @count($res);
                    if ($total_reg > 0) { 
                        for ($i = 0; $i < $total_reg; $i++) {
                            $nome = $res[$i]['nome'];?>
                            <button id="botao_servicos" style="border-radius: 10px; background-color:rgb(141, 157, 248); color: white; padding: 2 px;border: 0"><?php echo $nome;?></button><?php              
                            
                        }
                    }

                    $query = $pdo->query("SELECT * FROM servicos WHERE ativo = 'Sim' AND id_conta = '$id_conta' ORDER BY id ASC");
                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                    $total_reg = @count($res);
                    if ($total_reg > 0) { 
                    ?>
                </p>
                <?php 
              if($pgto_api == 'Sim'){
              ?>
              <img src="images/mp2.png" alt="Banner Mercado Pago" class="img-fluid mb-4 produto-banner" style="max-width: 200px; height: auto;margin: 0;">
              <?php 
              }
              ?>
            </div>
            <div class="product_container">
                <div class="slick-services">
                    <?php 
                    for ($i = 0; $i < $total_reg; $i++) {
                        $id = $res[$i]['id'];
                        $nome = $res[$i]['nome']; 
                        $valor = $res[$i]['valor'];
                        $foto = $res[$i]['foto'];
                        $valorF = number_format($valor, 2, ',', '.');
                        $nomeF = mb_strimwidth($nome, 0, 20, "...");
                    ?>
                    <div class="slick-slide">
                        <div class="box">
                            <div class="img-box">
                                <img src="sistema/painel/img/servicos/<?php echo $foto ?>" alt="<?php echo $nome ?>">
                            </div>
                            <div class="detail-box">
                                <h4><?php echo $nomeF ?></h4>
                                <h6 class="price">
                                    <span class="new_price">R$ <?php echo $valorF ?></span>
                                </h6>
                                <a href="agendamentos?u=<?php echo $username ?>">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <div class="btn-box">
          <a href="servicos?u=<?php echo $username?>">
            Ver mais Serviços
          </a>
        </div>
            </div>            
        </div>
    </section>
<?php } ?>
<?php } ?>

  
  <section class="about_section ">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6 px-0">
          <div class="img-box ">
            <?php if($url_video != "" ){
              echo '<iframe width="100%" height="350" src="'.$url_video.'" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
            }else{?>
              <img src="images/foto-sobre<?php echo $id_conta?>.jpg" class="box_img" alt="about img">
            <?php } ?>
          </div>
        </div>
        <div class="col-md-5">
          <div class="detail-box ">
            <div class="heading_container">
              <h2 style="color: white;">
                Sobre Nós
              </h2>
            </div>
            <p class="detail_p_mt">
              <?php echo $texto_sobre ?>
            </p>
            <a style="border-radius: 15px;" href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whatsapp ?>" class="">
              Mais Informações <i class="fa fa-whatsapp"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>


  <?php 

if($produtos2 == 'Sim'){
  $query = $pdo->query("SELECT * FROM produtos where estoque > 0 and valor_venda > 0 and id_conta = '$id_conta' ORDER BY id desc limit 8");
  $res = $query->fetchAll(PDO::FETCH_ASSOC);
  $total_reg = @count($res);
  if($total_reg > 0){   ?>   

    <section class="product_section layout_padding">
      <div class="container-fluid">
      <div class="heading_container heading_center d-flex align-items-center">
        <h2 id="titulo_produtos">
          Nossos Produtos
        </h2>
        <?php 
        if($pgto_api == 'Sim'){
        ?>
        <img src="images/mp2.png" alt="Banner Mercado Pago" class="img-fluid mb-4 produto-banner" style="max-width: 200px; height: auto;margin: 0;">
        <?php 
        }
        ?>
      </div>
        <div class="row">
          <div class="product-slider">
            <?php 
            for($i=0; $i < $total_reg; $i++){
              foreach ($res[$i] as $key => $value){}
              
              $id = $res[$i]['id'];
              $nome = $res[$i]['nome'];   
              $valor = $res[$i]['valor_venda'];
              $foto = $res[$i]['foto'];
              $descricao = $res[$i]['descricao'];
              $valorF = number_format($valor, 2, ',', '.');
              $nomeF = mb_strimwidth($nome, 0, 23, "...");
            ?>
              <div class="product-slide">
                <div class="box">
                  <div class="img-box">
                    <img src="sistema/painel/img/produtos/<?php echo $foto ?>" title="<?php echo $descricao ?>">
                  </div>
                  <div class="detail-box">
                    <h5>
                      <?php echo $nomeF ?>
                    </h5>
                    <h6 class="price">
                      <span class="new_price">
                        R$ <?php echo $valorF ?>
                      </span>
                    </h6>
                    <?php 
                    if($pgto_api != 'Sim'){?>
                      <a target="_blank" href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whatsapp ?>&text=Ola, gostaria de saber mais informações sobre o produto <?php echo $nome ?>">
                        Comprar Agora
                      </a><?php 
                    }else{?>
                      <a href="pagamento2/<?php echo $id ?>/<?php echo $id_conta?>">
                        Comprar Agora
                      </a><?php 
                    }
                    ?>
                  </div>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>
        <div class="btn-box">
          <a style="border-radius: 15px;" href="produtos?u=<?php echo $username?>">
            Ver mais Produtos
          </a>
        </div>
      </div>
    </section>
   

  <?php }
} ?>
  
  <section class="contact_section layout_padding-bottom">
    <div class="container">
      <div class="heading_container">
        <h2 id="contato">
          Contate-nos
        </h2>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form_container">
            <form id="form-email">
              <div>
                <input type="text" name="nome" placeholder="Seu Nome" required/>
              </div>
              <div>
                <input type="text" name="telefone" id="telefone" placeholder="Seu Telefone" required />
              </div>
              <div>
                <input type="email" name="email" placeholder="Seu Email" required />
              </div>
              <div>
                <input type="text" name="mensagem" class="message-box" placeholder="Mensagem" required />
              </div>
              <div class="btn_box">
                <button style="border-radius: 15px;">
                  Enviar
                </button>
              </div>
            </form>

            <br><div id="mensagem"></div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="map_container ">
           <?php echo $mapa ?>
          </div>
        </div>
      </div>
    </div>
  </section>
  


  <?php
if($depoimentos2 == 'Sim'){ 
  $query = $pdo->query("SELECT * FROM comentarios where ativo = 'Sim' and id_conta = '$id_conta' ORDER BY id asc");
  $res = $query->fetchAll(PDO::FETCH_ASSOC);
  $total_reg = @count($res);
  if($total_reg > 0){ 
  ?>
    <section class="client_section layout_padding-bottom">
      <div class="container">
        <div class="heading_container">
          <h2 id="depoimentos">
            Depoimento dos nossos Clientes
          </h2>
        </div>
        <div class="client_container">
          <div class="slick-slider-client">
            <?php 
            for($i=0; $i < $total_reg; $i++){
              foreach ($res[$i] as $key => $value){}
  
              $id = $res[$i]['id'];
              $nome = $res[$i]['nome'];   
              $texto = $res[$i]['texto'];
              $foto = $res[$i]['foto'];   
            ?>
              <div class="item">
                <div class="box">
                  <div >
                  <img src="sistema/painel/img/comentarios/<?php echo $foto ?>" alt="" class="img-1" style="aspect-ratio: 1 / 1; object-fit: cover; border-radius: 50%;border: 5px solid #be2623; width: 50%;">
                  </div>
                  <div class="detail-box">
                    <h5>
                      <?php echo $nome ?>
                    </h5>
                    <p>
                      <?php echo $texto ?>
                    </p>
                  </div>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>
        <div class="btn-box2">
        <a style="border-radius: 15px;" href="" data-toggle="modal" data-target="#modalComentario">
          Inserir Depoimento
        </a>
      </div>
      </div>

      
    </section>

    

  <?php }
} ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM carregado. Verificando largura:", window.innerWidth); // Debug
    if (window.innerWidth <= 999) {
        var redirectUrl = "<?php echo $url?>app/index.php?u=<?php echo $username?>";
        console.log("Redirecionando para:", redirectUrl); // Debug
        window.location.href = redirectUrl;
    } else {
        console.log("Largura maior que 768."); // Debug
    }
});
</script>

  <!-- end client section -->

  <?php require_once("rodape2.php") ?>

  <!-- Adicionar o script Slick no final da página -->
  <script>
     $(document).ready(function(){
   
      // Inicializa o Slick com configurações básicas
    var slickSlider = $('.slick-services').slick({
        dots: true,
        infinite: true,
        speed: 500,
        slidesToShow: 4, // Padrão inicial
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 3000,
        arrows: true,
        centerMode: false,
        variableWidth: false
    });

    // Função para ajustar o número de slides com base na largura da tela
    function adjustSlickSlides() {
        var windowWidth = $(window).width();        

        if (windowWidth <= 580) {
            // Celulares
            slickSlider.slick('slickSetOption', 'slidesToShow', 1, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
            slickSlider.slick('slickSetOption', 'centerMode', false, true);
            slickSlider.slick('slickSetOption', 'variableWidth', false, true);
            
        } else if (windowWidth <= 768) {
            // Tablets e celulares maiores
            slickSlider.slick('slickSetOption', 'slidesToShow', 2, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
            
        } else if (windowWidth <= 1024) {
            // Tablets e laptops menores
            slickSlider.slick('slickSetOption', 'slidesToShow', 3, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
            
        } else {
            // Desktop
            slickSlider.slick('slickSetOption', 'slidesToShow', 4, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
           
        }      
    }

    // Aplica ao carregar a página e ao redimensionar a janela
    $(window).on('resize', adjustSlickSlides).trigger('resize');

    


    var slickSlider = $('.product-slider').slick({
        dots: true,
        infinite: true,
        speed: 500,
        slidesToShow: 4, // Padrão inicial
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 3000,
        arrows: true,
        centerMode: false,
        variableWidth: false
    });

    // Função para ajustar o número de slides com base na largura da tela
    function adjustSlickSlides() {
        var windowWidth = $(window).width();        

        if (windowWidth <= 580) {
            // Celulares
            slickSlider.slick('slickSetOption', 'slidesToShow', 1, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
            slickSlider.slick('slickSetOption', 'centerMode', false, true);
            slickSlider.slick('slickSetOption', 'variableWidth', false, true);
            
        } else if (windowWidth <= 768) {
            // Tablets e celulares maiores
            slickSlider.slick('slickSetOption', 'slidesToShow', 2, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
            
        } else if (windowWidth <= 1024) {
            // Tablets e laptops menores
            slickSlider.slick('slickSetOption', 'slidesToShow', 3, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
            
        } else {
            // Desktop
            slickSlider.slick('slickSetOption', 'slidesToShow', 4, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
           
        }      
    }

    // Aplica ao carregar a página e ao redimensionar a janela
    $(window).on('resize', adjustSlickSlides).trigger('resize');

    


    var slickSlider = $('.slick-slider-client').slick({
        dots: true,
        infinite: true,
        speed: 500,
        slidesToShow: 4, // Padrão inicial
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 3000,
        arrows: true,
        centerMode: false,
        variableWidth: false
    });

    // Função para ajustar o número de slides com base na largura da tela
    function adjustSlickSlides() {
        var windowWidth = $(window).width();        

        if (windowWidth <= 580) {
            // Celulares
            slickSlider.slick('slickSetOption', 'slidesToShow', 1, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
            slickSlider.slick('slickSetOption', 'centerMode', false, true);
            slickSlider.slick('slickSetOption', 'variableWidth', false, true);
            
        } else if (windowWidth <= 768) {
            // Tablets e celulares maiores
            slickSlider.slick('slickSetOption', 'slidesToShow', 2, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
            
        } else if (windowWidth <= 1024) {
            // Tablets e laptops menores
            slickSlider.slick('slickSetOption', 'slidesToShow', 3, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
            
        } else {
            // Desktop
            slickSlider.slick('slickSetOption', 'slidesToShow', 4, true);
            slickSlider.slick('slickSetOption', 'slidesToScroll', 1, true);
           
        }      
    }

    // Aplica ao carregar a página e ao redimensionar a janela
    $(window).on('resize', adjustSlickSlides).trigger('resize');


      });
    </script>




  <!-- Modal Depoimentos -->
  <div class="modal fade" id="modalComentario" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Inserir Depoimento
                   </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <form id="form">
      <div class="modal-body">

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="exampleInputEmail1">Nome</label>
                <input type="text" class="form-control" id="nome_cliente" name="nome" placeholder="Nome" required>    
              </div>  
            </div>
            <div class="col-md-12">

              <div class="form-group">
                <label for="exampleInputEmail1">Texto <small>(Até 500 Caracteres)</small></label>
                <textarea maxlength="500" class="form-control" id="texto_cliente" name="texto" placeholder="Texto Comentário" required> </textarea>   
              </div>  
            </div>
          </div>               

            <div class="row">
              <div class="col-md-8">            
                <div class="form-group"> 
                  <label>Foto</label> 
                  <input class="form-control" type="file" name="foto" onChange="carregarImg();" id="foto">
                </div>            
              </div>
              <div class="col-md-4">
                <div id="divImg">
                  <img src="sistema/painel/img/comentarios/sem-foto.jpg"  width="80px" id="target">                  
                </div>
              </div>

            </div>


          
            <input type="hidden" name="id" id="id">
             <input type="hidden" name="cliente" value="1">

          <br>
          <small><div id="mensagem-comentario" align="center"></div></small>
        </div>

        <div class="modal-footer">      
          <button type="submit" class="btn btn-primary">Inserir</button>
        </div>
      </form>

      </div>
    </div>
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
                                    
                                    <button type="button" class="btn btn-lg btn-block <?php echo $btn_class; ?> btn-assinar mt-auto" data-toggle="modal" data-target="#modalAssinante3" data-plano="<?php echo $id_plano_atual; ?>">Assinar <?php echo $nome_plano; ?></button>
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

<div class="modal fade" id="modalAssinante3" tabindex="-1" role="dialog" aria-labelledby="modalAssinante3Label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
             <form id="form-assinante3" method="post">
                <div class="modal-header text-white" style="background-color: #4682B4;">
                    <h5 class="modal-title" id="modalAssinante3Label">Adicionar Assinatura</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;">
                        <span aria-hidden="true">&times;</span>
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
                                <label for="ass_plano_freq">Plano e Frequência <span class="text-danger">*</span></label>
                                <select class="form-control" id="ass_plano_freq" name="plano_freq_selecionado" required>
                                     <option value="">-- Selecione o Plano e a Frequência --</option>
                                     <?php
                                     // Busca os planos disponíveis para o select do modal
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

                                            // Busca os preços novamente para garantir que temos ambos aqui
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
                         <!-- <div class="col-md-6">
                             <div class="form-group">
                                 <label for="ass_vencimento">Data de Vencimento <span class="text-danger">*</span></label>
                                 <input type="date" class="form-control" id="ass_vencimento" name="data_vencimento" required>
                             </div>
                        </div> -->
                    </div>

                    <small><div id="mensagem-assinante3" class="mt-2"></div></small>
                </div>
                <div class="modal-footer text-white" style="background-color: #4682B4;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>                    
                    <button type="button" class="btn btn-warning" id="btnSalvarAssinante3">Assinar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPedirTelefone" tabindex="-1" aria-labelledby="modalPedirTelefoneLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm"> 
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h5 class="modal-title" id="modalPedirTelefoneLabel">Buscar Assinatura</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <label for="inputTelefoneBusca" class="form-label">Digite o Telefone:</label>
                <input type="tel" class="form-control" id="inputTelefoneBusca" placeholder="(XX) XXXXX-XXXX" required>                 
                 <div class="invalid-feedback">Por favor, informe um telefone válido.</div>
            </div>
            <div class="modal-footer text-white" style="background-color: #4682B4;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnBuscarPorTelefone">Buscar Detalhes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssinaturaDetalhes" tabindex="-1" aria-labelledby="modalAssinaturaDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> 
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
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
            <div class="modal-footer text-white" style="background-color: #4682B4;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>                
                
            </div>
        </div>
    </div>
</div>








<script type="text/javascript">

$('#btnSalvarAssinante3').on('click', function() {
    $('#form-assinante3').submit(); // Dispara o evento submit do formulário
});
  
$("#form-email").submit(function () {

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'ajax/enviar-email.php',
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem').text('');
            $('#mensagem').removeClass()
            if (mensagem.trim() == "Enviado com Sucesso") {
               $('#mensagem').addClass('text-success')
                $('#mensagem').text(mensagem)
                Swal.fire({
                  position: "top-center",
                  icon: "success",
                  title: "Obrigado! Entraremos em contato!",
                  showConfirmButton: false,
                  timer: 2000
                });

            } else {

                $('#mensagem').addClass('text-danger')
                $('#mensagem').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});


</script>



<script type="text/javascript">
  function carregarImg() {
    var target = document.getElementById('target');
    var file = document.querySelector("#foto").files[0];
    
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
</script>



<script type="text/javascript">
  
$("#form").submit(function () {

    event.preventDefault();
    var formData = new FormData(this);


    $.ajax({
        url: 'sistema/painel/paginas/comentarios/salvar.php',
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem-comentario').text('');
            $('#mensagem-comentario').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {
            
            $('#mensagem-comentario').addClass('text-success')
                $('#mensagem-comentario').text('Comentário Enviado para Aprovação!')
                 $('#nome_cliente').val('');
                  $('#texto_cliente').val('');

            } else {

                $('#mensagem-comentario').addClass('text-danger')
                $('#mensagem-comentario').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});


</script>