<?php
// Inclua seu arquivo de cabeçalho aqui, se necessário.
// require_once("cabecalho.php");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MARKAI - Sistema de Gestão para Serviços</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="./images/favicon_markai.png">
    <style>
        .icon-card {
            color: #5d53c8;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .section-card {
            background-color: #ffffff;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition-property: all;
            transition-duration: 300ms;
            transform: scale(1);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .section-card:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .custom-button {
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            transition-property: all;
            transition-duration: 300ms;
            transform: scale(1);
        }

        .custom-button:hover {
            transform: scale(1.05);
        }

        .primary-button {
            background-color: #5c5ff6ff;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .primary-button:hover {
            background-color: #5c5ff6ff;
        }

        .secondary-button {
            background-color: #e5e7eb;
            color: #374151;
        }

        .secondary-button:hover {
            background-color: #d1d5db;
        }

        .whatsapp-button {
            position: fixed;
            bottom: 75px;
            right: 20px;
            z-index: 1000;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #25D366;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }

        .whatsapp-button:hover {
            transform: scale(1.1);
        }

        .whatsapp-button i {
            color: white;
            font-size: 2rem;
        }
            
        /* NOVO CSS PARA O BOTÃO "ACESSAR" */
        /* Remova o estilo antigo do botão .acessar-button */
        .acessar-top-button {
            /* Posição no canto superior direito */
            position: fixed;
            top: 20px; /* Distância do topo */
            right: 20px; /* Distância da direita */
            z-index: 1000;
            
            /* Formato e estilo */
            padding: 0.75rem 1.5rem; /* Espaçamento interno (retangular) */
            font-weight: bold;
            font-size: 1rem;
            color: white;
            border-radius: 0.5rem; /* Bordas levemente arredondadas */
            background-color: #5c5ff6ff; /* Cor roxa */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }

        .acessar-top-button:hover {
            transform: scale(1.05);
            background-color: #5c5ff6ff;
        }
    </style>

    <style>
    .custom-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 2rem;
        font-size: 1rem;
        font-weight: bold;
        border-radius: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .primary-button {
        background: linear-gradient(to right, #4A90E2, #50C9C3);
        color: white;
    }
    .primary-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 7px 20px rgba(74, 144, 226, 0.4);
    }
</style>


    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-R39PP1R46W"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-R39PP1R46W');
</script>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-953415060"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-953415060');
</script>

   
</head>

<body class="bg-gray-50 font-sans">

<section class="relative overflow-hidden bg-gray-50 text-gray-800" id="sessao-0">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-teal-50 -z-10"></div>
    <div class="absolute top-0 left-0 w-72 h-72 bg-blue-100 rounded-full opacity-50 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-teal-100 rounded-full opacity-50 translate-x-1/2 translate-y-1/2"></div>

    <div class="container mx-auto px-6 py-20 md:py-32">
        <div class="flex flex-col md:flex-row items-center justify-between max-w-6xl mx-auto">
            
            <div class="md:w-6/12 text-center md:text-left mb-12 md:mb-0">
    <span class="inline-block bg-blue-100 text-blue-600 text-sm font-semibold px-4 py-1 rounded-full mb-4">
        ✨ SISTEMA DE GESTÃO COMPLETO
    </span>

    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-4">
        A Gestão do Seu Negócio, <span class="text-blue-600">Reinventada.</span>
    </h1>

    <p class="text-lg md:text-xl text-gray-600 mb-8">
        Com o MarkAi, você automatiza tarefas, organiza agendamentos e encanta seus clientes. Tudo em um só lugar.
    </p>

    <ul class="space-y-3 text-left mb-8 mx-auto md:mx-0 max-w-md">
        <li class="flex items-center">
            <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>Agenda Inteligente e Online</span>
        </li>
        <li class="flex items-center">
            <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>Controle Financeiro Simplificado</span>
        </li>
        <li class="flex items-center">
            <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>Marketing e Lembretes Automáticos</span>
        </li>
    </ul>

    <div class="flex flex-col items-center md:items-start">
        <a href="precos.php" class="custom-button primary-button">
            QUERO MEU TESTE GRÁTIS DE 7 DIAS
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
        </a>
        <span class="text-xs text-gray-500 mt-3">Sem cartão de crédito. Cancele quando quiser.</span>
    </div>

    <div class="mt-8 flex items-center justify-center md:justify-start">
        <div class="flex text-yellow-400">
            ★★★★★
        </div>
        <p class="ml-3 text-sm text-gray-600">4.9/5 de <span class="font-semibold">200+ avaliações</span></p>
    </div>

    <div class="mt-8 text-center md:text-left">
        <p class="text-sm font-semibold text-gray-600 mb-3">Leve sua gestão para onde for:</p>
        <div class="flex items-center justify-center md:justify-start space-x-3">
            <div class="flex items-center space-x-2 rounded-lg bg-gray-200 px-3 py-2">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span class="text-sm font-medium text-gray-800">Computador</span>
            </div>
            <div class="flex items-center space-x-2 rounded-lg bg-gray-200 px-3 py-2">
                 <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span class="text-sm font-medium text-gray-800">App Profissional</span>
            </div>
        </div>
    </div>
    </div>
            
            <div class="md:w-5/12 flex justify-center">
                <div class="bg-white p-3 rounded-xl shadow-2xl transform transition hover:scale-105 duration-300">
                    <div class="w-full h-4 flex items-center space-x-1.5 mb-2">
                        <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                    </div>
                    <img src="./images/menu_principal.jpg" alt="Interface do sistema MarkAi" class="w-full rounded-md">
                </div>
            </div>
        </div>
    </div>
</section>


<!-- <section class="py-16 bg-white" id="sessao-1">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-12">
            Veja o MarkAi em Ação
        </h2>
        <div class="max-w-4xl mx-auto flex flex-col items-center bg-gray-100 rounded-lg p-4 shadow-xl">
            <iframe 
                class="w-full rounded-lg" 
                height="315"
                src="https://www.youtube.com/embed/bc4pbzZFuzE"
                title="YouTube video player"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
            ></iframe>
        </div>
    </div>
</section> -->

<section class="py-16 bg-gray-100" id="sessao-2">
    <div class="container mx-auto px-6">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 text-center mb-12">
            Funcionalidades que Transformam Seu Negócio
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="section-card">
                <i class="fas fa-clock icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Agendamentos Online 24H</h3>
                <p class="text-gray-600 flex-grow">
                    Seus clientes podem marcar horários a qualquer momento, com praticidade e autonomia.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-sync-alt icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Campanhas de Retorno de Clientes</h3>
                <p class="text-gray-600 flex-grow">
                    Reconquiste seus clientes com disparos de WhatsApp incentivando retorno e com a opção de oferecer cupom de desconto.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-bell icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Notificações e Lembretes Automatizados</h3>
                <p class="text-gray-600 flex-grow">
                    Mantenha seus clientes engajados com comunicações pontuais e profissionais.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-mobile-alt icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">App ou Link Personalizado</h3>
                <p class="text-gray-600 flex-grow">
                    Ofereça uma experiência exclusiva com um aplicativo sob medida para seus clientes.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-list-ol icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Encaixe com fila de espera</h3>
                <p class="text-gray-600 flex-grow">
                    Não havendo horarios disponíveis o cliente pode se cadastrar na fila de espera, encaixe, e será notificado caso haja cancelamentos. Sua agenda sempre cheia. Não perca clientes! 
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-ticket-alt icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Cupons de Descontos</h3>
                <p class="text-gray-600 flex-grow">
                    Crie e ofeceça cupons a seus clientes, em capanhas de retorno, aniversários e muito mais.
                </p>
            </div>
            <div class="section-card">
                <i class="fab fa-whatsapp icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">WhatsApp Integrado</h3>
                <p class="text-gray-600 flex-grow">
                    Automatize notificações, lembretes, confirmações de agendamento e mensagens de marketing diretamente pelo WhatsApp.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-boxes icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Cadastro e Venda Produtos</h3>
                <p class="text-gray-600 flex-grow">
                    Com o MarkAi vc cadastrar produtos e fornecedores, e pode vender em seu App ou Link com o Mercado Pago, diversas formas de pagamento. 
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-hand-holding-usd icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Comissões Automáticas</h3>
                <p class="text-gray-600 flex-grow">
                    No MarkAi ao fechar a comanda todas as comissões são automaticamente distribuidas aos profissionais. Gestão completo com métricas e gráficos a sua disposição. 
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-tachometer-alt icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Página Principal</h3>
                <p class="text-gray-600 flex-grow">
                    Página com metrícas e graficos importantes para seu dia a dia, alertas e informações que facilitam sua gestão.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-gem icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Clube do Assinante</h3>
                <p class="text-gray-600 flex-grow">
                    Crie suas assinaturas e ofeceça vantagens exclusivas a seus clientes. Painel de controle robusto para sua gerência.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-credit-card icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Mercado Pago integrado</h3>
                <p class="text-gray-600 flex-grow">
                    Com ele vc poderá cobrar adiantamento nos agendamentos e oferecer diversas formas de pagamento a seus clientes, com baixa automática.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-star icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Pesquisa de Satisfação</h3>
                <p class="text-gray-600 flex-grow">
                    Receba feedback sobre seus serviços e profissionais para manter a excelência e a satisfação do cliente.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-id-card icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Cartão Fidelidade</h3>
                <p class="text-gray-600 flex-grow">
                    Recompense seus clientes mais fiéis com um sistema de fidelidade automático, aumentando a recorrência.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-users icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Cadastros</h3>
                <p class="text-gray-600 flex-grow">
                    Cadastros de clientes, profissionais e fornecedores com historico e métricas individualizadas.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-desktop icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">App ou Web</h3>
                <p class="text-gray-600 flex-grow">
                    Vc poderá usar o sistema pelo nosso App ou se preferir pela Web através do site.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-birthday-cake icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Alertas de Aniversário</h3>
                <p class="text-gray-600 flex-grow">
                    Surpreenda seus clientes com mensagens de parabéns e promoções no dia do aniversário, fortalecendo a relação.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-user-friends icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Multi Usuários</h3>
                <p class="text-gray-600 flex-grow">
                    Seus profissionais terão acesso ao App ou Site através de login e senha para gerenciar sua agenda. Exclusivo para o plano Empresa.
                </p>
            </div>
            <div class="section-card">
                <i class="fas fa-cloud icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Qualquer Dispositivo e Lugar</h3>
                <p class="text-gray-600 flex-grow">
                    O sistema poderá ser usado 24 horas por dia de qualquer dispoditivo e lugar. Sistema e banco de dados na Amazon Web Services (AWS), plataforma de nuvem mais segura e mais abrangente do mundo.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-16 md:py-24 bg-gradient-to-l from-[#4A90E2] to-[#50C9C3] text-white">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-5xl font-extrabold mb-4">
            Pronto para Revolucionar Seu Negócio?
        </h2>
        <p class="text-xl md:text-2xl mb-8">
            Comece agora seu teste grátis de 7 dias e descubra como o MarkAi pode transformar sua gestão.
        </p>
        <div class="space-x-4">
            <a href="precos.php" class="custom-button primary-button">TESTAR GRÁTIS AGORA</a>
            <!-- <a href="https://wa.me/5522998838694" target="_blank" class="custom-button secondary-button">FALAR COM VENDAS</a> -->
        </div>
    </div>
</section>

<footer class="bg-gray-800 text-gray-300">
    <div class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <h3 class="font-bold text-lg text-white mb-2">MARKAI</h3>
                <p class="text-sm">Transformando a gestão de negócios de serviços.</p>
                <p class="text-xs mt-4">&copy; <?php echo date('Y'); ?> MarkAi. Todos os direitos reservados.</p>
                <p class="text-xs">CNPJ: 59.774.964/0001-30</p>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-3">Navegação</h4>
                <ul class="space-y-2">
                    <li><a href="#sessao-2" class="hover:text-white">Funcionalidades</a></li>
                    <li><a href="precos.php" class="hover:text-white">Preços</a></li>
                    <li><a href="#faq" class="hover:text-white">FAQ</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-3">Legal</h4>
                <ul class="space-y-2">
                    <li><a href="terms-of-use.html" class="hover:text-white">Termos de Uso</a></li>
                    <li><a href="privacy-policy.html" class="hover:text-white">Política de Privacidade</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-3">Siga-nos</h4>
                <div class="flex space-x-4">
                    <a href="https://www.instagram.com/skysee.software/" target="_blank" class="hover:text-white"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="https://www.facebook.com/profile.php?id=61574991107173" target="_blank" class="hover:text-white"><i class="fab fa-facebook fa-lg"></i></a>
                </div>
                <p class="text-sm mt-4">contato@skysee.com.br</p>
            </div>
        </div>
    </div>
</footer>

<div id="cookie-banner" class="hidden fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 flex items-center justify-between z-50">
    <p class="text-sm">Nós usamos cookies para melhorar sua experiência. Ao continuar a navegar, você concorda com a nossa <a href="privacidade.php" class="underline hover:text-blue-400">Política de Privacidade</a>.</p>
    <button id="accept-cookies" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
        Entendi
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const banner = document.getElementById('cookie-banner');
        const acceptButton = document.getElementById('accept-cookies');

        if (!localStorage.getItem('cookies_accepted')) {
            banner.classList.remove('hidden');
        }

        acceptButton.addEventListener('click', function() {
            localStorage.setItem('cookies_accepted', 'true');
            banner.style.display = 'none';
        });
    });
</script>

<a href="https://wa.me/5522998838694" target="_blank" class="whatsapp-button">
    <i class="fab fa-whatsapp"></i>
</a>

<a href="login.php" class="acessar-top-button">Acessar</a>

<?php
// Inclua seu arquivo de rodapé aqui, se necessário.
// require_once("rodape.php");
?>
</body>
</html>