<?php
require_once("cabecalho.php");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar - Funcionalidades</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            background-color: #f8f9fa;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 2rem;
        }
        .section-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .section-card:hover {
            transform: translateY(-5px);
        }
        .section-icon {
            font-size: 2.5rem;
            color: #007bff;
            margin-right: 1rem;
        }
        footer {
            background: #343a40;
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }
        .footer a {
            color: #adb5bd;
            text-decoration: none;
        }
        .footer a:hover {
            color: white;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <header class="header">
        <div class="container">
            <h1 class="display-4 fw-bold">Agendar</h1>
            <p class="lead">Soluções completas para sua barbearia</p>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <h2 class="text-center mb-5">Funcionalidades do Agendar</h2>

        <div class="row">
            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-bell section-icon"></i>
                    <h3>Lembrete de Horários</h3>
                    <p>Diminua o risco de esquecimentos/ausências dos seus clientes através dos Lembretes de Horários do AppBarber. Seu cliente, ao agendar horários, receberá automaticamente uma notificação (via WhatsApp) e um e-mail, no horário configurado para lembrá-lo.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-calendar-alt section-icon"></i>
                    <h3>Flexibilização da Agenda dos Profissionais</h3>
                    <p><strong>Jornada por dia da semana e Jornada por Período</strong></p>
                    <p>Cada profissional pode ter sua própria jornada de trabalho por dia da semana. Sendo assim, o sistema fará o controle automático para não deixar ocorrer agendamentos fora da jornada de trabalho.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-bullhorn section-icon"></i>
                    <h3>Envio de Notícias e Promoções</h3>
                    <p>Precisa enviar para todos os seus clientes que sua Barbearia irá promover um evento em alguma data, ou lançar alguma promoção? Com o Agendar, você consegue enviar para todos ou um grupo de clientes, para eles receberem via WhatsApp e email.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-chart-line section-icon"></i>
                    <h3>Gestão Financeira</h3>
                    <p>Ter o controle do seu estabelecimento é primordial para o sucesso do seu negócio. Com o Agendar, é possível ter o controle de contas a pagar e a receber, controlar o processo de fluxo de caixa, taxas de cartões, etc. E assim, você tem os resultados financeiros sempre atualizados. Controle total de seus números!</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-clock section-icon"></i>
                    <h3>Controle automatizado da Agenda</h3>
                    <p>Ter a agenda on-line e disponível para seus clientes e/ou profissionais agendarem tem grandes vantagens. A sua agenda vai se moldando conforme os usuários vão cadastrando os agendamentos de acordo com os horários disponíveis.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-file-alt section-icon"></i>
                    <h3>Relatórios Financeiros</h3>
                    <p>Transformar dados e informações em CONHECIMENTO é um grande diferencial para tomadas de decisões. No Agendar, você tem:</p>
                    <ul>
                        <li>Relatório de Produtos</li>
                        <li>Relatório de Entradas</li>
                        <li>Relatório de Saídas</li>
                        <li>Relatório de Comissões</li>
                        <li>Relatório de Serviços <small>(por dia, mês e ano. Filtro por forma de pagamento e serviço)</small></li>
                        <li>Relatório de Aniversariantes</li>
                        <li>Relatório de Demonstrativo de Lucro</li>                       
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fab fa-whatsapp section-icon"></i>
                    <h3>WhatsApp Marketing</h3>
                    <p>Construa seus pacotes de serviços e produtos, com imagens e áudios e dispare e fidelize clientes e antecipar receitas. E para os clientes uma forma de ganhar aquele desconto.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-boxes section-icon"></i>
                    <h3>Gestão de Estoque</h3>
                    <p>No Agendar vc faz o controle de estoque que é primordial para evitar prejuízos com perda ou vencimento de produtos. Com o Agendar você consegue:</p>
                    <ul>
                        <li>Saber o saldo de cada produto no estoque;</li>
                        <li>Histórico de movimentações de Entrada e Saída;</li>
                        <li>Saber o custo por produto;</li>
                        <li>Lucro por produto;</li>
                        <li>Receber avisos/alertas de produtos com quantidades baixas no estoque;</li>
                        <li>Controlar a validade dos produtos;</li>
                        <li>Controlar o valor em estoque (inventário).</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-ticket-alt section-icon"></i>
                    <h3>Comandas e Controle de Consumo</h3>
                    <p>Controlar o que seu cliente consome é muito importante para não causar erros que possam prejudicar o próprio cliente ou o estabelecimento. Com o Agendar, todo agendamento já gera uma Comanda, onde pode ser adicionado outros serviços e/ou produtos. Caso o cliente venha a consumir apenas produtos, pode ser aberta uma comanda e adicionado(s) o(s) produto(s) nessa comanda. E o estabelecimento tem total flexibilidade e liberdade para aplicar descontos, parcelar, escolher a forma de pagamento, entre outras funcionalidades.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-cloud section-icon"></i>
                    <h3>On-line 24 horas por dia, 7 dias por Semana</h3>
                    <p>Ter a sua Barbearia on-line traz muitas vantagens. Imagine que em um domingo de manhã, o cliente queira marcar, da comodidade da casa dele e na palma da mão, um horário durante a semana. Ele não conseguiria se a sua Barbearia não estivesse on-line, pois provavelmente ela estaria fechada, correto? Ou então, você tira uma semana de férias e quer acompanhar como está o andamento do seu negócio, seja lá aonde você estiver. Com o conceito “nuvem” sua Barbearia está ao alcance dos seus clientes e dos seus profissionais, com a segurança e disponibilidade.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-list-ul section-icon"></i>
                    <h3>Lista de Espera</h3>
                    <p>Sua agenda está lotada em determinado dia e você não tem tempo para ficar retornando aos clientes se vagar horários? Deixa que o Agendar faz isso por você. Com a Lista de Espera, o cliente pode se adicionar na lista do dia e ser avisado, automaticamente, quando vagar algum horário. Assim você não perde horários, seu cliente consegue agendar pelo aplicativo e você muitas das vezes nem fica sabendo que ocorreu esse processo pois estará realizando seus serviços sem ser interrompido.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-comment-dots section-icon"></i>
                    <h3>Mensagens de Retorno Automáticas</h3>
                    <p>Aumentar a frequência/fidelização do seu cliente é um fator primordial para o andamento do seu negócio. Estudos mostram que manter o cliente pode ser até 5 vezes mais barato do que conquistar um novo. Com o Agendar, você pode configurar mensagens automáticas de retorno para que seu cliente receba no dia configurado de retorno de determinado serviço.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-star section-icon"></i>
                    <h3>Pesquisa de Satisfação</h3>
                    <p>Medir a satisfação dos atendimentos realizados é uma forma de manter a qualidade dos serviços, a satisfação do cliente e a sua fidelização. E para isso, você pode habilitar a pesquisa de satisfação do AppBarber. Sendo assim, sempre que um Serviço for realizado, o cliente poderá responder como foi o atendimento realizado pelo profissional e você acompanhar as respostas e analisar, caso a caso, para uma tomada de decisão.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-id-card section-icon"></i>
                    <h3>Cartão Fidelidade</h3>
                    <p>Com o cartão fidelidade vc pode estipular quantos serviços o cliente completar ele terá direito a algum desconto ou serviço gratuito. Sendo assim, o desconto/brinde é aplicado automaticamente no agendamento desse cliente e sua Barbearia ganha mais clientes fiéis.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-globe section-icon"></i>
                    <h3>Site do Estabelecimento</h3>
                    <p>Ter um site é um fator muito importante para divulgação do seu negócio. Com o Agendar, sua Barbearia já ganha um site onde você pode personalizar com informações de horário de funcionamento, formas de pagamento, imagens da Barbearia, localização, serviços e profissionais entre outras informações. Seus clientes (ou novos) podem encontrar seu negócio por sites de busca e inclusive podem agendar horários por lá.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fab fa-facebook section-icon"></i>
                    <h3>Agendar pelo Site ou Facebook</h3>
                    <p>Já tem um site e/ou uma Página do Facebook do seu negócio? Você pode colocar o link de Agendamento On-line do Agendar em ambas as plataformas. Assim você tem mais essa opção do Agendar.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-birthday-cake section-icon"></i>
                    <h3>Aniversariantes</h3>
                    <p>Quem aqui não gosta de ser lembrado em uma data tão especial quanto o seu aniversário? O Agendar avisa os clientes aniversariantes para que você envie uma mensagem para ele, faça uma promoção especial de aniversário, entre outras opções. Vc tem a opção de colocar essas mensagens automáticas por WhatsApp, assim vc não se preocuparia, o Agendar faz tudo para vc!</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-credit-card section-icon"></i>
                    <h3>Pagamento On-line</h3>
                    <p>Estudos dizem que o consumo on-line aumenta cerca de 90% todo ano. O pagamento de serviços e compra de produtos on-line tiveram um grande aumento nos últimos anos e o comportamento do consumidor está mudando rapidamente. Dar a opção de pagamento on-line para seu cliente, via Aplicativo, garante o recebimento do valor (em caso de cancelamento ou ausência), ajuda a diminuir o número de ausência (afinal, o cliente que já pagou vai fazer uma forcinha pra não se esquecer do agendamento), dá maior comodidade ao seu cliente e claro, seu negócio estará usando uma tecnologia segura e uma tendência mundial.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="section-card">
                    <i class="fas fa-money-check-alt section-icon"></i>
                    <h3>Comissões</h3>
                    <p>Com o Agendar é possível configurar comissões por Serviço realizado e/ou produto vendido, e também comandas de consumo dos Profissionais. Sendo assim, você consegue extrair poderosos relatórios de comissões para ter as informações necessárias para pagamento do seus profissionais de uma forma clara e transparente.</p>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <section class="text-center my-5">
            <h2 class="fw-bold">Cadastre-se Já</h2>
            <p class="lead">Experimente todas as funcionalidades grátis por 30 dias, sem compromisso.</p>
            <a href="#" class="btn btn-primary btn-lg">Começar Agora</a>
        </section>

        <!-- Ajuda e Conteúdo -->
        <div class="row text-center">
            <div class="col-md-6">
                <h2>Ajuda</h2>
                <p><a href="#">Central de Ajuda</a> | <a href="#">Contato</a> | <a href="#">Interesse</a></p>
                <p><a href="#">Módulo Fiscal</a> | <a href="#">Aplicativo Próprio</a> | <a href="#">Revenda</a></p>
            </div>
            <div class="col-md-6">
                <h2>Conteúdo</h2>
                <p><a href="#">WebSerie</a> | <a href="#">Blog</a></p>
            </div>
        </div>
    </main>

    <!-- Rodapé -->
    <footer class="footer">
        <div class="container text-center">
            <div class="contact-info mb-3">
                <p>Rua Borges de Medeiros, 897-E, Sala 1601, Presidente Médici. Chapecó/SC | Brasil</p>
                <p><a href="mailto:contato@skysee.com.br">contato@skysee.com.br</a></p>
                <p>(49) 3025-7680</p>
            </div>
            <p>© 2021. TODOS OS DIREITOS RESERVADOS. STARAPP SISTEMAS</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>