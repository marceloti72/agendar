<?php require_once("sistema/conexao.php") ?>
<!-- footer section -->
  <footer class="footer_section" style="background-color: #836FFF ;">
    <div class="container">
      <div class="footer_content ">
        <div class="row ">
          <div class="col-md-5 col-lg-5 footer-col">
            <div class="footer_detail">
              <a href="site.php/u=<?php echo $username?>">
                <h4>
                <img src="sistema/img/logo<?php echo $id_conta?>.png" alt="Logo" width="50px">
                  <?php echo $nome_sistema ?>
                </h4>
              </a>
              <p>
                <?php echo $texto_rodape ?>
              </p>
            </div>
          </div>
          <div class="col-md-7 col-lg-4 ">
            <h4>
              Contatos
            </h4>
            <div class="contact_nav footer-col">
              
                
                <span>
                <i class="fa fa-map-marker" aria-hidden="true"></i>
                  <?php echo $endereco_sistema ?>
                </span>
              
              <a href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whatsapp ?>">
                <i class="fa fa-phone" aria-hidden="true"></i>
                <span>
                  Whatsapp : <?php echo $whatsapp_sistema ?>
                </span>
              </a>
              <a href="mailto:<?php echo $email_sistema ?>?subject=Contato&body=Olá, gostaria de mais informações.">
                <i class="fa fa-envelope" aria-hidden="true"></i>
                <span>
                  Email : <?php echo $email_sistema ?>
                </span>
              </a>
            </div>
          </div>
          <div class="col-lg-3">
            <div class="footer_form footer-col">
              <h4>
                CADASTRE-SE
              </h4>
              <form id="form_cadastro">
                <input type="text" style="border-radius: 15px;" name="telefone" id="telefone_rodape" placeholder="Seu Telefone DDD + número" />
                <input type="text" style="border-radius: 15px;" name="nome" placeholder="Seu Nome" />
                <button type="submit" style="border-radius: 15px;">
                  Cadastrar
                </button>
              </form>
              <br><small><div id="mensagem-rodape"></div></small>
            </div>
          </div>
        </div>
      </div>
    </div>
    
  </footer>
  <!-- footer section -->

  <!-- jQery -->
  <script src="js/jquery-3.4.1.min.js"></script>
  <!-- popper js -->
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
  <!-- bootstrap js -->
  <script src="js/bootstrap.js"></script>  
 
  <!-- custom js -->
  <script src="js/custom.js"></script>  

    <!-- Mascaras JS -->
<script type="text/javascript" src="sistema/painel/js/mascaras.js"></script>

<!-- Ajax para funcionar Mascaras JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script> 

<!-- jQuery (necessário para o Slick) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Slick JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>





</body>

</html>




<script type="text/javascript">


$(document).ready(function(){
  $('#telefone_rodape').mask('(00) 00000-0000');
  $('#telefone_compra').mask('(00) 00000-0000');
    // Inicializa o Slick Slider
    $('.slick-services').slick({
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

  
  
 $("#form_cadastro").submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'ajax/cadastrar.php',
        type: 'POST',
        data: formData,
        success: function (mensagem) {
            $('#mensagem-rodape').text('');
            $('#mensagem-rodape').removeClass();
            if (mensagem.trim() == "Salvo com Sucesso") {
                $('#mensagem-rodape').text(mensagem);
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
                    // if (result.isConfirmed) {
                    //     window.location = "index.php";
                    // }
                });
            } else {
                $('#mensagem-rodape').text(mensagem);
            }
        },
        cache: false,
        contentType: false,
        processData: false,
    });
});


</script>