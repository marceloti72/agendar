<?php require_once("sistema/conexao.php") ?>
<!-- footer section -->
  <footer class="footer_section" style="background-color:rgb(56, 87, 112);">
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

  <!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!-- Popper.js e Bootstrap JS (5.3 bundle inclui Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<!-- jQuery Mask -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

<!-- Slick JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js" integrity="sha512-XtmMtDEcNz2j7ekrtHvOVR4iwwaD6o/FUJe6+Zq+HgcCsk3kj4uSQQR8weQ2QVj1o0Pk6PwYLohm206ZzNfubg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Canvas Confetti -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<!-- Máscaras JS -->
<script type="text/javascript" src="sistema/painel/js/mascaras.js"></script>

<!-- Custom JS -->
<script src="js/custom.js"></script>





</body>

</html>




<script type="text/javascript">


$(document).ready(function(){
  $('#telefone_rodape').mask('(00) 00000-0000');
  $('#telefone_compra').mask('(00) 00000-0000');
    // Inicializa o Slick Slider
    // $('.slick-services').slick({
    //     dots: true,           // Exibe pontos de navegação
    //     infinite: true,       // Loop infinito
    //     speed: 500,           // Velocidade da transição
    //     slidesToShow: 4,      // Mostra 4 itens por vez
    //     slidesToScroll: 1,    // Rola 1 item por vez
    //     autoplay: true,       // Ativa o autoplay
    //     autoplaySpeed: 3000,  // Tempo entre os slides (3 segundos)
    //     arrows: true,         // Exibe setas de navegação
    //     responsive: [         // Configuração responsiva
    //         {
    //             breakpoint: 1024,
    //             settings: {
    //                 slidesToShow: 3
    //             }
    //         },
    //         {
    //             breakpoint: 768,
    //             settings: {
    //                 slidesToShow: 2
    //             }
    //         },
    //         {
    //             breakpoint: 480,
    //             settings: {
    //                 slidesToShow: 1
    //             }
    //         }
    //     ]
    // });


   
        


        
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