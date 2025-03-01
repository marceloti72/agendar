<?php require_once("sistema/conexao.php") ?>
<!-- footer section -->
  <footer class="footer_section" style="background-color: #483D8B;">
    <div class="container">
      <div class="footer_content ">
        <div class="row ">
          <div class="col-md-8 col-lg-6 footer-col">
            <div class="footer_detail">
              <a href="index.php">
                <h4>
                  EDUK - Sistema Online de Gestão Escolar
                </h4>
              </a>
              <small>
                Desevolvido por
              </small><br>
              <img src="img/logo/logo-ss-branco-lg.png" style="width: 60%;">
            </div>
          </div>
          <div class="col-md-7 col-lg-3 ">
            <h4>
              Contatos
            </h4>
            <div class="contact_nav footer-col">              
              <a href="http://api.whatsapp.com/send?1=pt_BR&phone=5522998838694" target="_blank">
                <i class="fa fa-phone" aria-hidden="true"></i>
                <span>
                  Whatsapp :<br> (22) 99883-8694
                </span>
              </a>
              <a >
                <i class="fa fa-envelope" aria-hidden="true"></i>
                <span>
                  Email :<br> contato@skysee.com.br
                </span>
              </a>
            </div>
          </div>
          <div class="col-lg-3">
            <div class="footer_form footer-col">
              <h5>
                Dúvidas ?
              </h5>
              <small>Entraremos em contato!</small>
              <form id="form_cadastro">
                <input type="text" name="telefone" id="telefone_rodape" placeholder="Seu Telefone DDD + número" required pattern="[0-9]{10,11}" title="Digite um telefone válido com DDD (10 ou 11 dígitos)" />
                <input type="text" name="nome" placeholder="Seu Nome" required />
                <button type="submit" class="form-control" id='botao_duvidas'>Enviar</button>
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
  <script src="./js/bootstrap.js"></script>
  <!-- owl slider -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
  <!-- custom js -->
  <script src="js/custom.js"></script>
  <!-- Google Map -->
  <!-- <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCh39n5U-4IoWpsVGUHWdqB6puEkhRLdmI&callback=myMap"></script> -->
  <!-- End Google Map -->

    <!-- Mascaras JS -->
<script type="text/javascript" src="js/mascaras.js"></script>

<!-- Ajax para funcionar Mascaras JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script> 

</div>



</body>
<script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/cd6d9e5a-205c-424e-a7b7-9cf86de4d85c-loader.js" ></script>



</html>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>



<script type="text/javascript">

AOS.init();

// const scrollButtons = document.querySelectorAll('.scroll-down');
// const sections = document.querySelectorAll('section');
// let currentSectionIndex = 0;

// scrollButtons.forEach(button => {
//   button.addEventListener('click', () => {
//     currentSectionIndex++;
//     if (currentSectionIndex >= sections.length) {
//       currentSectionIndex = 0; // Voltar para a primeira seção se chegar ao final
//     }
//     sections[currentSectionIndex].scrollIntoView({ behavior: 'smooth' });
//   });
// });


// const scrollButtons2 = document.querySelectorAll('.scroll-down2');

// scrollButtons2.forEach(button => {
//   button.addEventListener('click', () => {
//     currentSectionIndex-1;
//     if (currentSectionIndex >= sections.length) {
//       currentSectionIndex = 0; // Voltar para a primeira seção se chegar ao final
//     }
//     sections[currentSectionIndex].scrollIntoView({ behavior: 'smooth' });
//   });
// });


const scrollButtonsUp = document.querySelectorAll('.scroll-up'); 
const scrollButtonsDown = document.querySelectorAll('.scroll-down');
const sections = document.querySelectorAll('section');
let currentSectionIndex = 0;

scrollButtonsDown.forEach(button => {
  button.addEventListener('click', () => {
    currentSectionIndex++;
    if (currentSectionIndex >= sections.length) {
      currentSectionIndex = 0; 
    }
    sections[currentSectionIndex].scrollIntoView({ behavior: 'smooth' });
  });
});

scrollButtonsUp.forEach(button => {
  button.addEventListener('click', () => {
    currentSectionIndex--;
    if (currentSectionIndex < 0) {
      currentSectionIndex = sections.length - 1; 
    }
    sections[currentSectionIndex].scrollIntoView({ behavior: 'smooth' });
  });
});

document.getElementById('quem_somos').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});

document.getElementById('section-2').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});
document.getElementById('section-3').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});
document.getElementById('section-4').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});
document.getElementById('section-5').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});
document.getElementById('section-6').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});
document.getElementById('section-7').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});
document.getElementById('section-8').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});
document.getElementById('section-9').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});
document.getElementById('section-10').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});

document.getElementById('section-11').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});

document.getElementById('section-12').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});
document.getElementById('section-13').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});

document.getElementById('section-14').addEventListener('click', function() {
  const sectionId = this.dataset.section;
  const section = document.getElementById(sectionId);

  // Rolar suavemente até a seção
  section.scrollIntoView({ behavior: 'smooth' });
});




  
$("#form_cadastro").submit(function () {

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'ajax/cadastrar.php',
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem-rodape').text('');
            $('#mensagem-rodape').removeClass()
            if (mensagem.trim() == "Enviado com Sucesso") {
               //$('#mensagem-rodape').addClass('text-success')
                //$('#mensagem-rodape').text(mensagem)

                Swal.fire({
                  title: "Pedido enviado!",
                  text: "Retornarem contato em breve!",
                  icon: "success"
                  }).then((result) => {
                      if(result.isConfirmed){
                      window.location = "index.php";        
                }});
               

            } else {

                //$('#mensagem-rodape').addClass('text-danger')
                $('#mensagem-rodape').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});


</script>

<script>
  document.getElementById('form_cadastro').addEventListener('invalid', function(event) {
    event.preventDefault();
    if (event.target.id === 'telefone_rodape') {
      event.target.setCustomValidity('Por favor, digite um telefone válido com DDD.');
    } else {
      event.target.setCustomValidity('Por favor, preencha este campo.');
    }
  });
</script>