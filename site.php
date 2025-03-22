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

/* Estilos do Slick Slider */
.slick-services {
    margin: 0 -15px;
    width: 100%;
    overflow: hidden; /* Evita overflow em telas pequenas */
}

.slick-services .slick-slide {
    padding: 15px;
    outline: none;
}

/* Estilos do Box */
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
$id_conta = '0';
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
                            <button id="botao_servicos" style="border-radius: 15px; background-color:rgb(141, 157, 248); color: white; padding: 2 px;"><?php echo $nome;?></button><?php              
                            
                        }
                    }

                    $query = $pdo->query("SELECT * FROM servicos WHERE ativo = 'Sim' AND id_conta = '$id_conta' ORDER BY id ASC");
                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                    $total_reg = @count($res);
                    if ($total_reg > 0) { 
                    ?>
                </p>
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
        <div class="heading_container heading_center">
          <h2 id="titulo_produtos">
            Nossos Produtos
          </h2>
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

  <!-- end client section -->

  <?php require_once("rodape2.php") ?>

  <!-- Adicionar o script Slick no final da página -->
  <script>
     $(document).ready(function(){
    //   $('.slick-slider-client').slick({
    //     slidesToShow: 1,
    //     slidesToScroll: 1,
    //     autoplay: true,
    //     autoplaySpeed: 2000,
    //     dots: true,
    //     arrows: true,
    //     infinite: true,
    //     responsive: [
    //       {
    //         breakpoint: 768,
    //         settings: {
    //           slidesToShow: 1
    //         }
    //       }
    //     ]
    //   });
           
    
    
    
    var slickSlider = $('.slick-services').slick({
        dots: true,
        infinite: true,
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 3000,
        arrows: true,
        centerMode: false,
        variableWidth: false,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 580,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    centerMode: false,
                    variableWidth: false
                }
            }
        ]
    });

   


$('.product-slider').slick({
          dots: true,           // Exibe pontos de navegação
        infinite: true,       // Loop infinito
        speed: 500,           // Velocidade da transição
        slidesToShow: 4,      // Mostra 4 itens por vez
        slidesToScroll: 1,    // Rola 1 item por vez
        autoplay: true,       // Ativa o autoplay
        autoplaySpeed: 3000,  // Tempo entre os slides (3 segundos)
        arrows: true,         // Exibe setas de navegação
        responsive: [         // Configuração responsiva
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
        });

        $('.slick-slider-client').slick({
          dots: true,           // Exibe pontos de navegação
        infinite: true,       // Loop infinito
        speed: 500,           // Velocidade da transição
        slidesToShow: 4,      // Mostra 4 itens por vez
        slidesToScroll: 1,    // Rola 1 item por vez
        autoplay: true,       // Ativa o autoplay
        autoplaySpeed: 3000,  // Tempo entre os slides (3 segundos)
        arrows: true,         // Exibe setas de navegação
        responsive: [         // Configuração responsiva
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
        });
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








<script type="text/javascript">
  
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