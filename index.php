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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <style>
        .icon-card {
            color: #5d53c8;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .section-card {
            @apply bg-white p-6 rounded-xl shadow-lg transition-transform transform hover:scale-105 hover:shadow-2xl flex flex-col h-full;
        }

        .custom-button {
            @apply font-bold py-3 px-6 rounded-full transition-all duration-300 transform hover:scale-105;
        }

        .primary-button {
            @apply bg-purple-600 text-white shadow-lg hover:bg-purple-700;
        }

        .secondary-button {
            @apply bg-gray-200 text-gray-800 hover:bg-gray-300;
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

    </style>
</head>
<body class="bg-gray-50 font-sans">

<section class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white py-20 md:py-32" id="sessao-0">
    <div class="container mx-auto px-6 text-center">
        <div class="max-w-4xl mx-auto">
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
    </div>
</section>

<section class="py-16 bg-white" id="sessao-1">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-12">
            Veja o MarkAi em Ação
        </h2>
        
        <div class="swiper mySwiper max-w-4xl mx-auto">
            <div class="swiper-wrapper">
                <div class="swiper-slide flex flex-col items-center bg-gray-100 rounded-lg p-4 shadow-md">
                    <p class="text-lg font-semibold text-gray-700 mb-4">Agendamentos</p>
                    <div class="aspect-w-16 aspect-h-9 w-full">
                        <iframe 
                            class="w-full h-full rounded-lg" 
                            src="https://www.youtube.com/embed/bc4pbzZFuzE"
                            title="YouTube video player"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>

                <div class="swiper-slide flex flex-col items-center bg-gray-100 rounded-lg p-4 shadow-md">
                    <p class="text-lg font-semibold text-gray-700 mb-4">Encaixe Rápido</p>
                    <div class="aspect-w-16 aspect-h-9 w-full">
                        <iframe 
                            class="w-full h-full rounded-lg" 
                            src="https://www.youtube.com/embed/KTrHJuTFFXs"
                            title="YouTube video player"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
                
                <div class="swiper-slide flex flex-col items-center bg-gray-100 rounded-lg p-4 shadow-md">
                    <p class="text-lg font-semibold text-gray-700 mb-4">Gestão de Produtos</p>
                    <div class="aspect-w-16 aspect-h-9 w-full">
                        <iframe 
                            class="w-full h-full rounded-lg" 
                            src="https://www.youtube.com/embed/eTaFOxIaP9s"
                            title="YouTube video player"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>

            </div>
            
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination mt-4"></div>
        </div>
        
    </div>
</section>

<script>
    var swiper = new Swiper(".mySwiper", {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });
</script>

<section class="py-16 bg-gray-100" id="sessao-2">
    <div class="container mx-auto px-6">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 text-center mb-12">
            Funcionalidades que Transformam Seu Negócio
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="section-card">
                <i class="fas fa-bell icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Lembrete de Horários</h3>
                <p class="text-gray-600 flex-grow">
                    Diminua as ausências dos seus clientes com notificações automáticas via WhatsApp e e-mail.
                </p>
            </div>

            <div class="section-card">
                <i class="fas fa-calendar-alt icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Flexibilização da Agenda</h3>
                <p class="text-gray-600 flex-grow">
                    Personalize a jornada de trabalho de cada profissional para um controle preciso de horários.
                </p>
            </div>

            <div class="section-card">
                <i class="fas fa-bullhorn icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Envio de Promoções</h3>
                <p class="text-gray-600 flex-grow">
                    Envie campanhas de marketing pelo WhatsApp para todos os seus clientes ou grupos específicos.
                </p>
            </div>

            <div class="section-card">
                <i class="fas fa-chart-line icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Gestão Financeira</h3>
                <p class="text-gray-600 flex-grow">
                    Controle suas finanças com clareza: gerencie contas a pagar e a receber, comissões e tenha seus resultados sempre atualizados.
                </p>
            </div>

            <div class="section-card">
                <i class="fas fa-clock icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Agenda Automatizada</h3>
                <p class="text-gray-600 flex-grow">
                    Sua agenda se adapta em tempo real. Ofereça pagamento online e garanta que os agendamentos se convertam em lucro.
                </p>
            </div>
            
            <div class="section-card">
                <i class="fas fa-file-alt icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Relatórios Detalhados</h3>
                <p class="text-gray-600 flex-grow">
                    Tome decisões inteligentes com relatórios de produtos, entradas, saídas, comissões, serviços e muito mais.
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
                <h3 class="text-xl font-bold text-gray-800 mb-2">Gestão de Estoque</h3>
                <p class="text-gray-600 flex-grow">
                    Monitore o saldo e o custo dos produtos, receba alertas de estoque baixo e controle a validade dos itens.
                </p>
            </div>
            
            <div class="section-card">
                <i class="fas fa-ticket-alt icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Comandas Flexíveis</h3>
                <p class="text-gray-600 flex-grow">
                    Gere comandas para serviços e produtos, aplique descontos e gerencie os pagamentos com total controle.
                </p>
            </div>

            <div class="section-card">
                <i class="fas fa-cloud icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Online 24h, 7 dias por semana</h3>
                <p class="text-gray-600 flex-grow">
                    Sua agenda está sempre disponível, permitindo que clientes marquem horários a qualquer momento, de qualquer lugar.
                </p>
            </div>

            <div class="section-card">
                <i class="fas fa-list-ul icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Lista de Espera Inteligente</h3>
                <p class="text-gray-600 flex-grow">
                    Não perca clientes! O sistema avisa automaticamente quando um horário livre aparece na agenda.
                </p>
            </div>

            <div class="section-card">
                <i class="fas fa-comment-dots icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Mensagens de Retorno</h3>
                <p class="text-gray-600 flex-grow">
                    Fidelize clientes com mensagens automáticas enviadas no momento certo para incentivá-los a retornar.
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
                <h3 class="text-xl font-bold text-gray-800 mb-2">Cartão Fidelidade Digital</h3>
                <p class="text-gray-600 flex-grow">
                    Recompense seus clientes mais fiéis com um sistema de fidelidade automático, aumentando a recorrência.
                </p>
            </div>

            <div class="section-card">
                <i class="fas fa-globe icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Site Personalizado</h3>
                <p class="text-gray-600 flex-grow">
                    Tenha um site próprio para seu negócio, otimizado para que seus clientes te encontrem e agendem facilmente.
                </p>
            </div>

            <div class="section-card">
                <i class="fab fa-facebook icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Agendamento Integrado</h3>
                <p class="text-gray-600 flex-grow">
                    Integre o link de agendamento em seu site, Facebook ou Instagram para captar mais clientes.
                </p>
            </div>

            <div class="section-card">
                <i class="fas fa-birthday-cake icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Mensagens de Aniversário</h3>
                <p class="text-gray-600 flex-grow">
                    Surpreenda seus clientes com mensagens de parabéns e promoções no dia do aniversário, fortalecendo a relação.
                </p>
            </div>

            <div class="section-card">
                <i class="fas fa-credit-card icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Pagamento Online</h3>
                <p class="text-gray-600 flex-grow">
                    Reduza faltas e ofereça comodidade aos seus clientes. Proteja seu lucro com pagamentos antecipados.
                </p>
            </div>
            
            <div class="section-card">
                <i class="fas fa-money-check-alt icon-card"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Controle de Comissões</h3>
                <p class="text-gray-600 flex-grow">
                    Gerencie as comissões da sua equipe de forma clara e automatizada, evitando erros e garantindo a transparência.
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

<?php
// Inclua seu arquivo de rodapé aqui, se necessário.
// require_once("rodape.php");
?>
</body>
</html>