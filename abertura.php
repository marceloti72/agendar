<div class="trinkstyle-v2 d-flex flex-column align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-xl-9 u-margin-top-large card-home">

                <div class="row u-margin-bottom-tiny">
<script src="/Areas/BackOffice/Views/Inicio/NotificacaoDiasDegustacao.cshtml.js?v=db3ef841e3" type="module"></script>

<div class="bar-notification-group__onboarding">
        <span class="bar-notication-group__onboarding__texto u-fw-600 u-color-neutral-darkest">
            Seu teste grátis termina em 5 dias
        </span>
</div>
                        <div class="card-identificacao w-100 d-flex flex-column align-items-center u-my-2xl">
                            <span class="card-identificacao__saudacao">Boa noite, Marcelo</span>
                            <span class="card-identificacao__data">Quinta-Feira, 20 de Fevereiro</span>
                        </div>

                </div>

                <div class="row">
                        <div class="col-12 col-lg-6 mb-5">
                            <script>var rastreios = {"Rastreio":[{"Id":578327,"IdEstabelecimento":213280,"TarefaOnboarding":{"Id":4,"Descricao":"Cadastre clientes e fidelize-os","DescricaoCurta":"Cadastrou o primeiro cliente","QtdNecessaria":1,"Ativo":true,"Ordem":1,"Trilha":null,"AcaoApp":1,"AcaoParam":"IrParaNovoCliente","AcaoNome":"Cadastrar cliente","WebUrl":"BackOffice/Transacao/AdicionarCliente","WebUrlTarget":1},"QtdRealizada":0,"NecessitaNotificacao":false},{"Id":578324,"IdEstabelecimento":213280,"TarefaOnboarding":{"Id":1,"Descricao":"Realize seu primeiro agendamento","DescricaoCurta":"Primeiro agendamento concluído","QtdNecessaria":1,"Ativo":true,"Ordem":2,"Trilha":null,"AcaoApp":1,"AcaoParam":"IrParaNovoAgendamento","AcaoNome":"Novo Agendamento","WebUrl":"Backoffice/Onboarding/TarefaAgendarCliente","WebUrlTarget":1},"QtdRealizada":0,"NecessitaNotificacao":false},{"Id":578325,"IdEstabelecimento":213280,"TarefaOnboarding":{"Id":2,"Descricao":"Cadastre serviços e venda mais","DescricaoCurta":"Serviços cadastrados","QtdNecessaria":1,"Ativo":true,"Ordem":3,"Trilha":null,"AcaoApp":2,"AcaoParam":"BackOffice/ServicosResponsivo/Editar","AcaoNome":"Cadastro de Serviços","WebUrl":"BackOffice/ServicosResponsivo/ServicosResponsivo","WebUrlTarget":1},"QtdRealizada":0,"NecessitaNotificacao":false},{"Id":578326,"IdEstabelecimento":213280,"TarefaOnboarding":{"Id":3,"Descricao":"Comunique horários e evite falhas","DescricaoCurta":"Horário de funcionamento configurado","QtdNecessaria":1,"Ativo":true,"Ordem":4,"Trilha":null,"AcaoApp":1,"AcaoParam":"IrParaConfigurarMeuSite","AcaoNome":"Configurações Básicas","WebUrl":"BackOffice/ConfiguracoesBasicas/#section-item-horario","WebUrlTarget":0},"QtdRealizada":0,"NecessitaNotificacao":false},{"Id":578328,"IdEstabelecimento":213280,"TarefaOnboarding":{"Id":5,"Descricao":"Destaque sua marca e fique à frente","DescricaoCurta":"Logo  adicionada no site","QtdNecessaria":1,"Ativo":true,"Ordem":5,"Trilha":null,"AcaoApp":1,"AcaoParam":"IrParaConfigurarMeuSite","AcaoNome":"Nova Logo","WebUrl":"BackOffice/ConfiguracoesBasicas/#configuracoes-basicas__fotos__logo","WebUrlTarget":0},"QtdRealizada":0,"NecessitaNotificacao":false},{"Id":578329,"IdEstabelecimento":213280,"TarefaOnboarding":{"Id":6,"Descricao":"Mostre seu espaço e ganhe clientes","DescricaoCurta":"Fotos adicionadas no site","QtdNecessaria":1,"Ativo":true,"Ordem":6,"Trilha":null,"AcaoApp":1,"AcaoParam":"IrParaConfigurarMeuSite","AcaoNome":"Configurações Básicas","WebUrl":"BackOffice/ConfiguracoesBasicas/#configuracoes-basicas__fotos","WebUrlTarget":0},"QtdRealizada":0,"NecessitaNotificacao":false}],"TodasCompletas":false,"Total":6,"QtdCompletas":0}</script>


<style>
    div.progressao-do-trinks-v2 .card-como-comecar:hover {
        background-color: #E2F1F8 !important;
        border-color: #B6DEEF !important;
    }

    .apresentarEtapa {
        display: block !important;
    }
</style>
<div id="progressao-do-trinks-v2" class="c-card-inicio">
    <div class="c-card-inicio__header d-flex flex-column">
        <div class="d-flex justify-content-between" style="margin-bottom: 8px;">
            <span class="c-card-inicio__titulo" style="white-space: nowrap">Comece por aqui</span>
            <div class="d-flex justify-content-between">
                <div class="c-etapas-bolinhas">
                    <div data-bind="click: clickEtapas" id="etapa-1" class="c-etapas-bolinhas__etapa u-bg-secundary-dark"></div>
                    <div data-bind="click: clickEtapas" id="etapa-2" class="c-etapas-bolinhas__etapa"></div>
                </div>
            </div>
        </div>

                <div data-bind="visible: primeiraEtapa" class="alinhamento-do-numero-com-o-texto">
                    <div style="padding: 0 !important; white-space: nowrap; display: flex; align-items: flex-start; justify-content: center; min-width: unset;" class="card-acao card-como-comecar" data-bind=" click: () => { rastreio().Rastreio()[0].NecessitaNotificacao === false && realizarTarefaDaTrilha(rastreio().Rastreio()[0].TarefaOnboarding.WebUrl, +(rastreio()?.Rastreio()[0].TarefaOnboarding.WebUrlTarget !== 0), rastreio()?.Rastreio()[0].TarefaOnboarding.AcaoNome, rastreio()?.Rastreio()[0]) }">
                        <div class="d-flex align-items-center">
                                <div class="card-acao__icon card__number  progressao-icone-troca font-weight-bold"  data-bind="text: textoDaContagem(0,  rastreio()?.Rastreio()[0])"></div>
                            <div class="card-como-comecar-text u-fs-sm font-weight-normal">Cadastre clientes e fidelize-os</div>
                        </div>
                    </div>
                </div>
                <div data-bind="visible: primeiraEtapa" class="alinhamento-do-numero-com-o-texto">
                    <div style="padding: 0 !important; white-space: nowrap; display: flex; align-items: flex-start; justify-content: center; min-width: unset;" class="card-acao card-como-comecar" data-bind=" click: () => { rastreio().Rastreio()[1].NecessitaNotificacao === false && realizarTarefaDaTrilha(rastreio().Rastreio()[1].TarefaOnboarding.WebUrl, +(rastreio()?.Rastreio()[1].TarefaOnboarding.WebUrlTarget !== 0), rastreio()?.Rastreio()[1].TarefaOnboarding.AcaoNome, rastreio()?.Rastreio()[1]) }">
                        <div class="d-flex align-items-center">
                                <div class="card-acao__icon card__number  progressao-icone-troca font-weight-bold"  data-bind="text: textoDaContagem(1,  rastreio()?.Rastreio()[1])"></div>
                            <div class="card-como-comecar-text u-fs-sm font-weight-normal">Realize seu primeiro agendamento</div>
                        </div>
                    </div>
                </div>
                <div data-bind="visible: primeiraEtapa" class="alinhamento-do-numero-com-o-texto">
                    <div style="padding: 0 !important; white-space: nowrap; display: flex; align-items: flex-start; justify-content: center; min-width: unset;" class="card-acao card-como-comecar" data-bind=" click: () => { rastreio().Rastreio()[2].NecessitaNotificacao === false && realizarTarefaDaTrilha(rastreio().Rastreio()[2].TarefaOnboarding.WebUrl, +(rastreio()?.Rastreio()[2].TarefaOnboarding.WebUrlTarget !== 0), rastreio()?.Rastreio()[2].TarefaOnboarding.AcaoNome, rastreio()?.Rastreio()[2]) }">
                        <div class="d-flex align-items-center">
                                <div class="card-acao__icon card__number  progressao-icone-troca font-weight-bold"  data-bind="text: textoDaContagem(2,  rastreio()?.Rastreio()[2])"></div>
                            <div class="card-como-comecar-text u-fs-sm font-weight-normal">Cadastre servi&#231;os e venda mais</div>
                        </div>
                    </div>
                </div>
                <div data-bind="visible: segundaEtapa, css: { 'apresentarEtapa': segundaEtapa }" style="display: none;" class="alinhamento-do-numero-com-o-texto">
                    <div style="padding: 0 !important; white-space: nowrap; display: flex; align-items: flex-start; justify-content: center; min-width: unset;" class="card-acao card-como-comecar" data-bind=" click: () => { rastreio().Rastreio()[3].NecessitaNotificacao === false && realizarTarefaDaTrilha(rastreio().Rastreio()[3].TarefaOnboarding.WebUrl, +(rastreio()?.Rastreio()[3].TarefaOnboarding.WebUrlTarget !== 0), rastreio()?.Rastreio()[3].TarefaOnboarding.AcaoNome, rastreio()?.Rastreio()[3]) }">
                        <div class="d-flex align-items-center">
                                <div class="card-acao__icon card__number progressao-icone-troca font-weight-bold"  data-bind="text: textoDaContagem(3,  rastreio()?.Rastreio()[3])"></div>
                            <div class="card-como-comecar-text u-fs-sm font-weight-normal">Comunique hor&#225;rios e evite falhas</div>
                        </div>
                    </div>
                </div>
                <div data-bind="visible: segundaEtapa, css: { 'apresentarEtapa': segundaEtapa }" style="display: none;" class="alinhamento-do-numero-com-o-texto">
                    <div style="padding: 0 !important; white-space: nowrap; display: flex; align-items: flex-start; justify-content: center; min-width: unset;" class="card-acao card-como-comecar" data-bind=" click: () => { rastreio().Rastreio()[4].NecessitaNotificacao === false && realizarTarefaDaTrilha(rastreio().Rastreio()[4].TarefaOnboarding.WebUrl, +(rastreio()?.Rastreio()[4].TarefaOnboarding.WebUrlTarget !== 0), rastreio()?.Rastreio()[4].TarefaOnboarding.AcaoNome, rastreio()?.Rastreio()[4]) }">
                        <div class="d-flex align-items-center">
                                <div class="card-acao__icon card__number progressao-icone-troca font-weight-bold"  data-bind="text: textoDaContagem(4,  rastreio()?.Rastreio()[4])"></div>
                            <div class="card-como-comecar-text u-fs-sm font-weight-normal">Destaque sua marca e fique &#224; frente</div>
                        </div>
                    </div>
                </div>
                <div data-bind="visible: segundaEtapa, css: { 'apresentarEtapa': segundaEtapa }" style="display: none;" class="alinhamento-do-numero-com-o-texto">
                    <div style="padding: 0 !important; white-space: nowrap; display: flex; align-items: flex-start; justify-content: center; min-width: unset;" class="card-acao card-como-comecar" data-bind=" click: () => { rastreio().Rastreio()[5].NecessitaNotificacao === false && realizarTarefaDaTrilha(rastreio().Rastreio()[5].TarefaOnboarding.WebUrl, +(rastreio()?.Rastreio()[5].TarefaOnboarding.WebUrlTarget !== 0), rastreio()?.Rastreio()[5].TarefaOnboarding.AcaoNome, rastreio()?.Rastreio()[5]) }">
                        <div class="d-flex align-items-center">
                                <div class="card-acao__icon card__number progressao-icone-troca font-weight-bold"  data-bind="text: textoDaContagem(5,  rastreio()?.Rastreio()[5])"></div>
                            <div class="card-como-comecar-text u-fs-sm font-weight-normal">Mostre seu espa&#231;o e ganhe clientes</div>
                        </div>
                    </div>
                </div>
    </div>
</div>
                        </div>

                        <div class="col-12 col-lg-6 mb-5">
                            <script>
    var eh5OuMais = 'False' === 'True'
</script>

<div class="parte-banner">
    <div class="c-card-banner__container u-d-flex u-justify-content-space-between">
        <div class="c-card-banner__content u-d-flex u-flex-column u-justify-content-space-between">
            <div class="u-d-flex u-flex-column">
                <span class="c-card-banner__titulo">
                    Vamos conversar
                </span>
                <span class="c-card-banner__subTitulo">
                    D&#234; o pr&#243;ximo passo e gerencie todo o seu estabelecimento por meio da Trinks!
                </span>
            </div>
            <div class="u-d-flex u-justify-content-sm-space-between u-align-items-center">
                <div class="u-flex-2xl-column">
                    <img src="https://djnn6j6gf59xn.cloudfront.net/Content/img/elemento-card-home.png?v=20241202045752" width="80" height="55" />
                </div>
                <div class="u-flex-2xl-column u-margin-right-small">
                    <button class="o-button-raised o-button-raised--accent " data-bind="click: btnBanner">
                        Assinar Agora
                    </button>
                </div>
            </div>
        </div>
        
    </div>
</div>

                        </div>

                    <div class="col-12 col-lg-6 mb-5">
                        <div class="parte-de-olho-no-estabelecimento h-100">
    <div class="c-card-inicio">
        <div class="c-card-inicio__header">
            <span class="c-card-inicio__titulo u-w-100">Resumo financeiro do negócio</span>
            <button class="o-button-icon o-button-icon__secondary" data-bind="visible: !nenhumLancamento(), click: () => redirecionarRespeitandoPermissoes()">
                <i class="icomoon ds-icon-right-filled"></i>
            </button>
        </div>
        <div class="c-card-inicio__body">
            <div class="c-card-inicio__body" data-bind="visible: !nenhumLancamento()">
                <div class="c-card-inicio__body__periodo" data-bind="text: periodo"></div>
                <div class="d-flex justify-content-between">

                    <div class="o-label-valor o-label-valor__positivo">
                        <p class="o-label-valor__descricao">Receita no mês</p>
                        <div class="d-flex">
                            <span class="o-label-valor__cifrao">R$</span>
                            <span class="o-label-valor__valor" data-bind="text: receitaDoMes"></span>
                        </div>
                    </div>

                    <div class="o-label-valor o-label-valor__negativo">
                        <p class="o-label-valor__descricao">Despesas no mês</p>
                        <div class="d-flex">
                            <span class="o-label-valor__cifrao">R$</span>
                            <span class="o-label-valor__valor" data-bind="text: despesaDoMes"></span>
                        </div>
                    </div>

                </div>
            </div>
            <div class="c-card-inicio__body" data-bind="visible: nenhumLancamento()">
                <div class="u-d-flex u-flex-column">
                    <div class="u-d-flex u-justify-content-center">
                        <div class="ds-icon-coin-regular u-fs-4xl "></div>
                    </div>
                    <div class="u-d-flex u-fs-md u-fw-600 u-color-neutral-darkest u-justify-content-center">
                        Nenhum lançamento financeiro
                    </div>
                    <div class="u-d-flex u-fs-sm u-fw-400 u-color-neutral-dark u-justify-content-center">
                        Feche a contas dos clientes ou lance suas despesas
                    </div>
                </div>
            </div>        
        </div>
    </div>
</div>
                    </div>
                    <div class="col-12 col-lg-6 mb-5">
                        <div class="parte-grafico-despesas-por-categoria u-h-100" data-bind="css: { 'd-none': !exibirCard  }">

    <div class="c-card-inicio">
        <div class="c-card-inicio__header">
            <span class="c-card-inicio__titulo">Despesas por categoria</span>
            <button class="o-button-icon o-button-icon__secondary" data-bind="visible: !nenhumaDespesa(), click: irParaControleDeEntradaESaida">
                <i class="icomoon ds-icon-right-filled"></i>
            </button>
        </div>
        <div class="c-card-inicio__body">
            <div data-bind="visible: !nenhumaDespesa()">
                <canvas height="100px" width="335px" id="myChart" style="margin-inline: auto;"></canvas>
            </div>
            <div data-bind="visible: nenhumaDespesa()">
                <div class="u-d-flex u-flex-column">
                    <div class="u-d-flex u-justify-content-center">
                        <div class="ds-icon-expense-regular u-fs-4xl "></div>
                    </div>
                    <div class="u-d-flex u-fs-md u-fw-600 u-color-neutral-darkest u-justify-content-center">
                        Nenhuma despesa
                    </div>
                    <div class="u-d-flex u-fs-sm u-fw-400 u-color-neutral-dark u-justify-content-center">
                        Cadastre e lance suas despesas
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js" integrity="sha512-sW/w8s4RWTdFFSduOTGtk4isV1+190E/GghVffMA9XczdJ2MDzSzLEubKAs5h0wzgSJOQTRYyaz73L3d6RtJSg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="/Areas/BackOffice/Views/Inicio/ParteGraficoDespesasPorCategoria.cshtml.vmodel.js?v=db3ef841e3" type="module"></script>
                    </div>
                    <div class="col-12 col-lg-6 mb-5">
                        <div class="row parte-proximos-compromissos u-h-100">
    <div class="col-12">
        <div class="c-card-inicio">
            <div class="c-card-inicio__header">
                <span class="c-card-inicio__titulo">Próximos compromissos</span>
                <button class="o-button-icon o-button-icon__secondary" data-bind="visible: !nenhumCompromisso(), click: () => irParaAAgenda()">
                    <i class="icomoon ds-icon-right-filled"></i>
                </button>
            </div>
            <div class="c-card-inicio__body" data-bind="visible: nenhumCompromisso()">
                <div class="u-d-flex u-flex-column">
                    <div class="u-d-flex u-justify-content-center">
                        <div class="ds-icon-clock-regular u-fs-3xl "></div>
                    </div>
                    <div class="u-d-flex u-fs-md u-fw-600 u-color-neutral-darkest u-justify-content-center">
                        Nenhum compromisso
                    </div>
                    <div class="u-d-flex u-fs-sm u-fw-400 u-color-neutral-dark u-justify-content-center">
                        Configure seus horários de atendimento e faça agendamentos
                    </div>
                </div>
            </div>
            <div class="c-card-inicio__body">
                <div class="agendamentos" data-bind="visible: !nenhumCompromisso()">
                    <!--ko foreach: agendamentos-->
                    <div class="d-flex justify-content-start p-2 rounded mb-1" data-bind="style:{'background-color' : cor }">
                        <div class="d-flex flex-column mr-2">
                            <span class="c-card-inicio__body__texto-descricao" data-bind="text: dataInicio"></span>
                            <span class="c-card-inicio__body__texto-descricao" data-bind="text: dataFim"></span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="c-card-inicio__body__texto-descricao" data-bind="text: cliente"></span>
                            <span class="c-card-inicio__body__texto-descricao" data-bind="text: servico"></span>
                        </div>
                    </div>
                    <!--/ko-->
                </div>
            </div>
        </div>
    </div>
</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




    
    <script src="https://djnn6j6gf59xn.cloudfront.net/Content/libs/bootstrap/4.0.0/js/bootstrap.min.js?v=db3ef841e3"></script>
    <script src="/Areas/BackOffice/Views/Inicio/Index.js?v=db3ef841e3"></script>
    <script src="/Areas/BackOffice/Views/Inicio/ParteProximosCompromissos.vmodel.js?v=db3ef841e3" type="module"></script>
    <script src="/Areas/BackOffice/Views/Inicio/ParteDeOlhoNoEstabelecimento.vmodel.js?v=db3ef841e3" type="module"></script>

    <script>var idTrilha = 1</script>

            <script src="/Areas/BackOffice/Views/Inicio/ParteProgressaoDeUsoDoTrinks.vmodel.js?v=db3ef841e3" type="text/javascript"></script>

    <script src="https://djnn6j6gf59xn.cloudfront.net/Content/js/guia-passo-a-passo/guia-passo-a-passo.js?v=db3ef841e3"></script>
    <link rel="stylesheet" href="https://djnn6j6gf59xn.cloudfront.net/Content/js/guia-passo-a-passo/guia-passo-a-passo.css?v=db3ef841e3">

        <script src="/Areas/BackOffice/Views/Inicio/ParteBanner.vmodel.js?v=db3ef841e3" type="module"></script>

    </div>