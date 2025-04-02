<style>
/* Estilos Gerais do Rodapé (Base) */
.footer_section {
  color: #fdfdfd; /* Cor do texto padrão no rodapé */
  padding: 50px 0; /* Espaçamento interno geral */
}

.footer_section h4 {
  font-size: 1.4rem;
  color: #ffffff;
  margin-bottom: 20px;
  font-weight: 600;
}

.footer_section a {
  color: #fdfdfd; /* Cor dos links */
  text-decoration: none; /* Remove sublinhado padrão */
}

.footer_section a:hover {
  color: #dddddd; /* Cor do link no hover */
  text-decoration: underline;
}

.footer_detail img {
  vertical-align: middle; /* Alinha a logo com o texto */
  margin-right: 10px;
}

.footer_detail p {
  margin-top: 15px;
  font-size: 1rem;
  line-height: 1.6;
}

.contact_nav span,
.contact_nav a {
  display: block; /* Faz cada item de contato ficar em uma linha */
  margin-bottom: 10px; /* Espaço entre itens de contato */
  font-size: 1rem;
}

.contact_nav i {
  margin-right: 10px; /* Espaço entre ícone e texto */
  width: 20px; /* Garante alinhamento dos ícones */
  text-align: center;
}

.cad {
  width: 100%;
  border: none;
  height: 45px;
  margin-bottom: 15px; /* Espaço entre inputs */
  padding-left: 15px;
  background-color: #fff;
  color: #1a1a1a; /* Cor do texto dentro do input */
  outline: none;
  border-radius: 15px !important; /* Força borda arredondada */
}

.footer_form button {
  display: inline-block;
  padding: 10px 45px;
  background-color: #ffffff;
  color: #007cff; /* Cor do texto do botão */
  text-align: center;
  border: 1px solid #ffffff;
  transition: all 0.3s;
  border-radius: 15px !important; /* Força borda arredondada */
  font-weight: bold;
  width: 100%; /* Botão ocupa largura total no rodapé */
}

.footer_form button:hover {
  background-color: transparent;
  color: #ffffff;
}

.footer_section .m-b-footer { /* Para o "Desenvolvido por" */
    display: block;
    text-align: center;
    margin-top: 40px; /* Espaço acima */
    padding-top: 20px; /* Espaço acima da linha */
    border-top: 1px solid rgba(255, 255, 255, 0.2); /* Linha divisória sutil */
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
}
.footer_section .m-b-footer a{
    color: rgba(255, 255, 255, 0.9);
    font-weight: bold;
}

/* --- MEDIA QUERY PARA MOBILE --- */
@media (max-width: 767px) { /* Aplica em telas menores que 768px */

    .footer_section {
        padding: 0px 0; /* Diminui o padding geral */
        text-align: center; /* Centraliza todo o texto no mobile */
    }

     .footer_col {
        margin-bottom: 10px; /* Adiciona espaço entre as colunas empilhadas */
     }
     .footer_col:last-child{
        margin-bottom: 0; /* Remove margem da última coluna */
     }

    .footer_section h4 {
        font-size: 1.0rem; /* Diminui o tamanho dos títulos */
        margin-bottom: 15px;
    }

    /* Detalhes da Empresa */
    .footer_detail img {
        width: 60px; /* Diminui a logo */
    }
    .footer_detail p {
        font-size: 0.85rem; /* Diminui o texto */
    }

    /* Contatos */
    .contact_nav span,
    .contact_nav a {
        font-size: 0.7rem; /* Diminui o texto dos contatos */
        margin-bottom: 8px;
        text-align: left; /* Alinha contatos à esquerda dentro da coluna centralizada */
        display: inline-block; /* Permite alinhar à esquerda */
        width: auto; /* Ajusta largura */
        padding-left: 20px; /* Adiciona espaço para centralizar bloco */
        padding-right: 20px;
    }
    .contact_nav {
         text-align: center; /* Centraliza o container dos contatos */
     }

    /* Formulário de Cadastro */
    .cad {
        height: 40px; /* Diminui altura do input */
        font-size: 0.9rem; /* Diminui texto do input */
        margin-bottom: 10px;
        padding-left: 10px;
    }

    .footer_form button {
        padding: 8px 30px; /* Diminui padding do botão */
        font-size: 0.9rem;
    }

    .footer_section .m-b-footer {
        margin-top: 0px;
        padding-top: 0px;
        font-size: 0.8rem;
    }
    .fa-map-marker{
      font-size: 20px;
    }
}
</style>
<?php require_once("sistema/conexao.php") ?>
<!-- footer section -->
  <footer class="footer_section" style="background-color:rgb(0, 0, 0);">
    <div class="container">
      <div class="footer_content ">
        <div class="row ">
          <div class="col-md-5 col-lg-5 footer-col">
            <div class="footer_detail">
              <a href="<?php echo $username?>">
                <h4>
                <img src="<?php echo $url?>sistema/img/logo<?php echo $id_conta?>.png" alt="Logo" width="50px">
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
                <button type="submit" style="border-radius: 15px; background-color: #108554">
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
<script type="text/javascript" src="<?php echo $url?>sistema/painel/js/mascaras.js"></script>

<!-- Custom JS -->
<script src="<?php echo $url?>js/custom.js"></script>





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