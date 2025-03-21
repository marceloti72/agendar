<?php 
require_once("cabecalho2.php");
?>

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
}

.slick-item {
    padding: 15px;
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

.product-slider {
    width: 100%;
    margin: 0 auto;
}

.product-slide {
    padding: 0 15px;
}

.slick-prev, .slick-next {
    z-index: 1;
}

/* Correção para evitar slides espremidos em telas pequenas */
.slick-slide {
    width: 100% !important; /* Força cada slide a ocupar a largura total */
    min-width: 0; /* Evita largura mínima fixa */
    box-sizing: border-box;
}

@media (max-width: 480px) {
    .slick-services .slick-slide {
        width: 100% !important; /* Garante 1 slide por vez em 480px */
    }
    .slick-item {
        padding: 10px; /* Reduz padding em telas pequenas */
    }
    .img-box img {
        height: 150px; /* Reduz altura da imagem em mobile */
    }
    .detail-box {
        padding: 15px; /* Reduz padding interno */
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
    <!-- Código do carrossel mantido igual -->
    <section class="slider_section ">
      <div id="customCarousel1" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
          <?php 
          for($i=0; $i < $total_reg; $i++){
            $id = $res[$i]['id'];
            $titulo = $res[$i]['titulo'];
            $descricao = $res[$i]['descricao'];
            $descricaoF = mb_strimwidth($descricao, 0, 50, "...");
            $ativo = ($i == 0) ? 'active' : '';
            ?>
            <div class="carousel-item <?php echo $ativo ?>">
              <div class="container ">
                <div class="row">
                  <div class="col-md-6 ">
                    <div class="detail-box">
                      <h1><?php echo $titulo ?></h1>
                      <p><?php echo $descricao ?></p>
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
          <?php } ?>
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
  } else { ?>
    </div>
  <?php } ?>

  <?php if ($servicos2 == 'Sim') { ?>
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
                $nome = $res[$i]['nome']; ?>
                <button id="botao_servicos" style="border-radius: 15px; background-color:rgb(141, 157, 248); color: white; padding: 2px;"><?php echo $nome;?></button>
              <?php } 
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
            <div class="slick-item">
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
            <a href="servicos?u=<?php echo $username?>">Ver mais Serviços</a>
          </div>
        </div>            
      </div>
    </section>
  <?php } ?>
<?php } ?>

<!-- Restante do código mantido igual -->
<!-- ... (seções Sobre Nós, Produtos, Contato, Depoimentos) ... -->

<?php require_once("rodape2.php") ?>

<script type="text/javascript">
$(document).ready(function(){
    // Inicialização do Slick Slider para serviços
    $('.slick-services').slick({
        dots: true,
        infinite: true,
        speed: 500,
        slidesToShow: 4,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 3000,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: true,
                    adaptiveHeight: true // Ajusta altura automaticamente
                }
            }
        ]
    }).on('breakpoint', function(event, slick, breakpoint) {
        console.log('Breakpoint ativado: ' + breakpoint); // Debug
    });
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