<?php require_once("sistema/conexao.php") ?>
<!-- footer section -->
<footer class="footer_section">
    <div class="container rodape">
        <div class="footer_content">
            <div class="row">
                <div class="col-md-8 col-lg-6 footer-col">
                    <div class="footer_detail">
                        <a href="index.php">
                            <h4>MARKAI - Sistema de Gestão de Serviços</h4>
                        </a>
                        <small>Desenvolvido por</small><br>
                        <img src="images/logo-ss-branco-lg.png" alt="Logo Skysee">
                    </div>
                </div>
                <div class="col-md-7 col-lg-3 footer-col">
                    <h4>Contatos</h4>
                    <div class="contact_nav">
                        <a href="http://api.whatsapp.com/send?1=pt_BR&phone=5522998838694" target="_blank">
                            <i class="fa fa-phone" aria-hidden="true"></i>
                            <span>Whatsapp:<br>(22) 99883-8694</span>
                        </a>
                        <a>
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                            <span>Email:<br>contato@skysee.com.br</span>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 footer-col">
                    <div class="footer_form">
                        <h5>Dúvidas?</h5>
                        <small>Entraremos em contato!</small>
                        <form id="form_cadastro">
                            <input type="text" name="telefone" id="telefone_rodape" placeholder="DDD + número" title="Digite um telefone válido com DDD (10 ou 11 dígitos)" />
                            <input type="text" name="nome" placeholder="Seu Nome" required />
                            <button type="submit" class="form-control" id="botao_duvidas">Enviar</button>
                        </form>
                        <small><div id="mensagem-rodape"></div></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- footer section -->

<!-- CSS for Responsiveness -->
<style>
    /* Base Footer Styles */
    .rodape {
        min-height: 200px; /* Base height for desktop, adjustable */
        display: flex;
        align-items: center; /* Vertically center content */
    }

    .footer_section {
        background-color: #483D8B;
        color: white;
        padding: 20px 0;
    }

    .footer_content {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

    .footer-col {
        padding: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .footer_detail h4 {
        margin-bottom: 8px;
        font-size: 1.2rem;
    }

    .footer_detail small {
        color: #d3d3d3;
        font-size: 0.75rem;
    }

    .footer_detail img {
        max-width: 45%;
        height: auto;
    }

    .contact_nav {
        display: flex;
        flex-direction: row;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .contact_nav a {
        display: flex;
        align-items: center;
        gap: 6px;
        color: white;
        text-decoration: none;
        margin-bottom: 0;
    }

    .contact_nav i {
        font-size: 0.9rem;
    }

    .contact_nav span {
        font-size: 0.85rem;
    }

    .footer_form h5 {
        margin-bottom: 6px;
        font-size: 1rem;
    }

    .footer_form small {
        display: block;
        margin-bottom: 6px;
        color: #d3d3d3;
        font-size: 0.7rem;
    }

    .footer_form form {
        display: flex;
        flex-direction: column;
        gap: 6px;
        width: 100%;
    }

    .footer_form input[type="text"] {
        padding: 6px;
        border: none;
        border-radius: 5px;
        width: 100%;
        font-size: 0.85rem;
        box-sizing: border-box;
    }

    .footer_form button {
        padding: 6px;
        border: none;
        border-radius: 5px;
        background-color: #fff;
        color: #483D8B;
        font-weight: bold;
        cursor: pointer;
        font-size: 0.85rem;
        transition: background-color 0.3s ease;
    }

    .footer_form button:hover {
        background-color: #f0f0f0;
    }

    #mensagem-rodape {
        color: white;
        font-size: 0.7rem;
        margin-top: 4px;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .rodape {
            min-height: 300px; /* Taller for mobile to accommodate stacked content */
            align-items: flex-start; /* Align to top when stacked */
        }

        .footer_section {
            padding: 12px 0;
        }

        .footer_content .row {
            flex-direction: column;
            align-items: center;
        }

        .footer-col {
            width: 100%;
            padding: 6px;
        }

        .footer_detail h4 {
            font-size: 1rem;
        }

        .footer_detail img {
            max-width: 35%;
        }

        .contact_nav {
            gap: 15px;
        }

        .contact_nav a {
            font-size: 0.8rem;
        }

        .footer_form h5 {
            font-size: 0.9rem;
        }

        .footer_form input[type="text"],
        .footer_form button {
            font-size: 0.8rem;
            padding: 5px;
        }
    }

    @media (max-width: 480px) {
        .rodape {
            min-height: 280px; /* Slightly shorter than 768px, adjusted for compactness */
        }

        .footer_section {
            padding: 8px 0;
        }

        .footer_detail h4 {
            font-size: 0.9rem;
        }

        .footer_detail img {
            max-width: 25%;
        }

        .contact_nav {
            gap: 10px;
            flex-direction: column; /* Stack on very small screens */
        }

        .contact_nav a {
            font-size: 0.75rem;
        }

        .contact_nav i {
            font-size: 0.8rem;
        }

        .footer_form h5 {
            font-size: 0.85rem;
        }

        .footer_form input[type="text"],
        .footer_form button {
            font-size: 0.75rem;
            padding: 4px;
        }

        .footer_form form {
            gap: 5px;
        }
    }
</style>

<!-- Scripts -->
<script src="js/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="./js/bootstrap.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="js/custom.js"></script>
<script type="text/javascript" src="js/mascaras.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/cd6d9e5a-205c-424e-a7b7-9cf86de4d85c-loader.js"></script>

<script type="text/javascript">
    $('#telefone_rodape').mask('(00) 00000-0000');
    AOS.init();

    $("#form_cadastro").submit(function (event) {
        event.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: 'ajax/cadastrar2.php',
            type: 'POST',
            data: formData,
            success: function (mensagem) {
                $('#mensagem-rodape').text('');
                $('#mensagem-rodape').removeClass();
                if (mensagem.trim() == "Enviado com Sucesso") {
                    Swal.fire({
                        title: "Pedido enviado!",
                        text: "Retornaremos contato em breve!",
                        icon: "success"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = "index.php";
                        }
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

    document.getElementById('form_cadastro').addEventListener('invalid', function(event) {
        event.preventDefault();
        if (event.target.id === 'telefone_rodape') {
            event.target.setCustomValidity('Por favor, digite um telefone válido com DDD.');
        } else {
            event.target.setCustomValidity('Por favor, preencha este campo.');
        }
    });
</script>
</body>
</html>