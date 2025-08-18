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
        background-color: #8b5cf6;
        color: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .primary-button:hover {
        background-color: #7c3aed;
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
            bottom: 20px;
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
        .acessar-button {
            position: fixed;
            bottom: 20px;
            right: 100px; /* Ajusta a posição para não colidir com o botão do WhatsApp */
            z-index: 1000;
            padding: 1rem;
            font-weight: bold;
            font-size: 1rem;
            color: white;
            border-radius: 50%; /* Torna o botão redondo */
            background-color: #7c3aed; /* Cor roxa para combinar com o tema */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            text-align: center;
        }

        .acessar-button:hover {
            transform: scale(1.1);
            background-color: #6d28d9;
        }

    </style>
</head>
<body class="bg-gray-50 font-sans">

<section class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white py-20 md:py-32" id="sessao-0">
    <div class="container mx-auto px-6">
        <div class="flex flex-col md:flex-row items-center justify-between max-w-6xl mx-auto">
            <div class="md:w-1/2 text-center md:text-left mb-10 md:mb-0">
                <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-4">
                    Teste Grátis do MarkAi: Transforme a Gestão do Seu Negócio em Apenas 7 Dias!
                </h1>
                <p class="text-xl md:text-2xl mb-8">
                    Descubra o MarkAi, o sistema completo para serviços que revoluciona a forma como você gerencia seu negócio!
                </p>
                <div class="space-x-4">
                    <a href="plan-selection.html" class="custom-button primary-button">TESTAR GRÁTIS POR 7 DIAS</a>
                    <a href="https://wa.me/5522998838694" target="_blank" class="custom-button secondary-button">FALAR COM VENDAS</a>
                </div>
            </div>
            <div class="md:w-1/2 flex justify-center">
                <img src="./images/menu_principal.jpg" alt="Descrição da Imagem" class="w-full max-w-sm rounded-lg shadow-lg">
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-white" id="sessao-1">
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
</section>

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

<section class="py-16 md:py-24 bg-gradient-to-l from-purple-500 to-indigo-600 text-white">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-5xl font-extrabold mb-4">
            Pronto para Revolucionar Seu Negócio?
        </h2>
        <p class="text-xl md:text-2xl mb-8">
            Comece agora seu teste grátis de 7 dias e descubra como o MarkAi pode transformar sua gestão.
        </p>
        <div class="space-x-4">
            <a href="plan-selection.html" class="custom-button primary-button">TESTAR GRÁTIS AGORA</a>
            <a href="https://wa.me/5522998838694" target="_blank" class="custom-button secondary-button">FALAR COM VENDAS</a>
        </div>
    </div>
</section>

<a href="https://wa.me/5522998838694" target="_blank" class="whatsapp-button">
    <i class="fab fa-whatsapp"></i>
</a>

<a href="login.php" class="acessar-button">Acessar</a>

<?php
// Inclua seu arquivo de rodapé aqui, se necessário.
// require_once("rodape.php");
?>
</body>
</html>