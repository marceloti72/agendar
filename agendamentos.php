<?php
//require_once("./sistema/conexao.php"); // Ajuste o caminho se necessário
require_once("./cabecalho2.php"); // Inclui seu cabeçalho (presume que inicia a sessão, define $pdo, $id_conta, $username, $url, etc.)
$data_atual = date('Y-m-d');
$data_atual_iso = $data_atual; // Renomeado para clareza, usado no input e valor inicial

// --- LÓGICA PHP PARA GERAR DATAS (EXECUTAR ANTES DO HTML) ---
$numeroDeDiasParaMostrar = 365; // Quantos dias mostrar no total
$hoje = new DateTime(); // Pega a data/hora atual
$hoje->setTime(0, 0, 0); // Zera a hora para comparações de data

$datasParaExibir = [];
$dataCorrente = new DateTime(); // Começa com hoje
$dataCorrente->setTime(0, 0, 0);

// Define locale para Português do Brasil ANTES de usar os formatters
// Certifique-se de que a extensão intl está habilitada no seu PHP
try {
    // Ajuste o timezone se necessário
    $formatterDiaSemana = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Sao_Paulo', IntlDateFormatter::GREGORIAN, 'EEE');
    $formatterMes = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Sao_Paulo', IntlDateFormatter::GREGORIAN, 'MMM');
    // Define pattern para remover o ponto final, se houver (varia conforme a versão do ICU)
    $formatterDiaSemana->setPattern('EEE');
    $formatterMes->setPattern('MMM');
} catch (Exception $e) {
    // Fallback simples se Intl não estiver disponível
    error_log("Extensão Intl não disponível ou erro: " . $e->getMessage());
    function formatarDiaSemanaPt($dateObj) {
        $dias = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sab'];
        return $dias[$dateObj->format('w')];
    }
    function formatarMesPt($dateObj) {
        $meses = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
        return $meses[$dateObj->format('n') - 1];
    }
}

for ($i = 0; $i < $numeroDeDiasParaMostrar; $i++) {
    $dataYmd = $dataCorrente->format('Y-m-d');
    $datasParaExibir[] = [
        'dataCompleta' => $dataYmd,
        // Usa IntlDateFormatter se disponível, senão usa fallback
        'diaDaSemana' => (isset($formatterDiaSemana)) ? mb_strtoupper($formatterDiaSemana->format($dataCorrente)) : mb_strtoupper(formatarDiaSemanaPt($dataCorrente)),
        'dia' => $dataCorrente->format('d'),
        'mes' => (isset($formatterMes)) ? mb_strtoupper(str_replace('.', '', $formatterMes->format($dataCorrente))) : mb_strtoupper(formatarMesPt($dataCorrente)), // Remove ponto final se houver
        'objetoData' => clone $dataCorrente
    ];
    $dataCorrente->modify('+1 day');
}

// Lógica de Desconto (Exemplo - Substitua pela sua)
function obterDescontoParaData($dataYmd) {
    $dataObj = new DateTime($dataYmd);
    $diaSemanaNum = $dataObj->format('N'); // 1 (Segunda) a 7 (Domingo)
    if ($diaSemanaNum == 2 || $diaSemanaNum == 3) { return 10; }
    return 0;
}

$dataSelecionadaInicial = $data_atual_iso; // Data de hoje no formato correto

?>
<style type="text/css">
    /* Estilo geral da página */
    .sub_page .hero_area {
        min-height: auto;
    }

    /* Estilo para inputs transparentes (se aplicável em outro lugar) */
    .inputs_agenda{
        background: transparent !important;
        border:none !important;
        border-bottom: 1px solid #FFF !important;
        font-size: 14px !important;
        color:#FFF !important;
        padding:0 !important;
        margin:0px !important;
        margin-bottom:5px !important;
    }

    /* --- ESTILOS DO CALENDÁRIO CARROSSEL --- */
    .calendario-container {
        max-width: 100%;
        margin: 15px 0; /* Ajuste da margem */
        padding: 15px;
        background-color: #f8f9fa; /* Fundo claro para contraste no rodapé escuro */
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .calendario-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 10px;
        margin-bottom: 10px; /* Adiciona espaço antes do hr */
        border-bottom: 1px solid #dee2e6; /* Linha divisória */
    }

    .calendario-header .escolha-horario {
        font-size: 1rem; /* Tamanho ajustado */
        font-weight: bold;
        color: #343a40; /* Cor escura */
        text-align: center;
        flex-grow: 1;
    }

    .calendario-header .btn-nav {
        color: #007bff;
        text-decoration: none;
        display: flex;
        align-items: center;
        padding: 5px 8px; /* Padding ajustado */
        background: none;
        border: none;
        cursor: pointer;
        line-height: 1; /* Melhora alinhamento do ícone */
    }
    .calendario-header .btn-nav:hover {
        color: #0056b3;
    }
    .calendario-header .btn-nav i {
        font-size: 1.2em; /* Tamanho ajustado */
    }
     .calendario-header .btn-nav span {
        font-size: 0.9em; /* Tamanho ajustado */
    }


    /* Itens de data */
    .datas-carousel {
        margin: 0 -5px; /* Compensa o padding dos itens */
    }

    .data-item {
        display: flex !important;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center; /* Garante centralização do texto */
        padding: 8px 5px; /* Padding reduzido */
        margin: 0 5px;
        border: 1px solid #dee2e6; /* Borda mais sutil */
        border-radius: 6px; /* Borda um pouco menos redonda */
        cursor: pointer;
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        background-color: #fff; /* Fundo branco */
        min-height: 85px; /* Altura mínima ajustada */
        color: #495057; /* Cor do texto padrão */
    }

    .data-item:hover {
        background-color: #e9ecef;
        border-color: #ced4da;
    }

    .data-item.selecionado {
        background-color: #007bff;
        border-color: #0056b3;
        color: white;
    }
    .data-item.selecionado .desconto { /* Ajusta cor do desconto quando selecionado */
         color: #ffc107;
    }


    .data-item .dia-semana {
        font-size: 0.75em;
        font-weight: bold;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .data-item .dia-mes {
        font-size: 1em; /* Tamanho ajustado */
        font-weight: bold;
        margin-bottom: 4px;
    }
    .data-item .dia-mes .mes {
        font-size: 0.75em;
        display: block; /* Coloca o mês abaixo do dia */
    }

    .data-item .desconto {
        font-size: 0.7em;
        font-weight: bold;
        color: #dc3545;
        height: 1.1em;
        line-height: 1.1em;
    }

    /* Ajustes Slick */
    .slick-track {
        display: flex !important;
        align-items: stretch !important; /* Tenta alinhar altura */
    }
    .slick-slide {
        height: inherit !important;
        float: none !important; /* Remove float que pode atrapalhar flex */
        display: flex; /* Adiciona flex ao slide */
        align-items: stretch; /* Adiciona alinhamento */
    }
    .slick-slide > div {
         height: 100%;
         display: flex; /* Adiciona flex ao container interno */
    }
     .slick-slide .data-item {
         width: 100%; /* Faz o item ocupar todo o espaço do slide */
         height: 100%;
     }


    /* Remove setas padrão do Slick se usar botões customizados */
    .slick-prev, .slick-next {
        /* display: none !important; */
    }

    /* Estilos para a lista de horários */
     #listar-horarios {
        max-height: 250px;
        overflow-y: auto;
        background: rgba(255,255,255,0.1);
        border-radius: 5px;
        padding: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
     #listar-horarios .hora-item { /* Estilo para cada botão de horário */
        display: inline-block;
        background-color: #f8f9fa;
        color: #343a40;
        border: 1px solid #dee2e6;
        padding: 5px 10px;
        margin: 3px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s ease;
		width: 100px;
     }
      #listar-horarios .hora-item:hover {
        background-color: #e2e6ea;
     }
     #listar-horarios .hora-item.hora-selecionada {
        background-color: #28a745; /* Verde para selecionado */
        color: white;
        border-color: #1c7430;
     }
     #listar-horarios .text-danger {
        color: #ffcdd2 !important; /* Vermelho claro para contraste */
     }

     /* Ajustes gerais no rodapé para espaçamento */
     .footer_section {
         padding-top: 40px; /* Adiciona mais espaço no topo */
     }
      .footer_content {
         padding-bottom: 20px; /* Espaço antes do rodapé final */
     }
      label.text-white { /* Melhora visibilidade dos labels */
         margin-bottom: .3rem;
         font-size: 0.9em;
         font-weight: 500;
     }

     /* Ajuste Select2 no rodapé escuro */
     .select2-container--default .select2-selection--single {
         background-color: #fff; /* Fundo branco */
         border: 1px solid #ced4da;
     }

      .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #495057 !important; /* Cor do texto padrão */
        line-height: calc(1.5em + .5rem + 2px) !important; /* Alinha com form-control-sm */
         padding-left: .75rem; /* Padding interno */
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #888 transparent transparent transparent;
    }
     .select2-dropdown { /* Para o dropdown do select2 */
        color: #495057; /* Cor de texto no dropdown */
     }
 

	 @media (max-width: 768px) {
		.data-item {
        display: flex !important;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center; /* Garante centralização do texto */
        padding: 8px 5px; /* Padding reduzido */
        margin: 0 3px;
        border: 1px solid #dee2e6; /* Borda mais sutil */
        border-radius: 6px; /* Borda um pouco menos redonda */
        cursor: pointer;
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        background-color: #fff; /* Fundo branco */
        min-height: 95px; /* Altura mínima ajustada */
        color: #495057; /* Cor do texto padrão */
    }
		.data-item .dia-semana {
        font-size: 0.65em;
        font-weight: bold;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .data-item .dia-mes {
        font-size: 1.0em; /* Tamanho ajustado */
        font-weight: bold;
        margin-bottom: 4px;
    }
    .data-item .dia-mes .mes {
        font-size: 0.6em;
        display: block; /* Coloca o mês abaixo do dia */
    }

    .data-item .desconto {
        font-size: 0.7em;
        font-weight: bold;
        color: #dc3545;
        height: 1.1em;
        line-height: 1.1em;
    }

	#listar-horarios {
        max-height: 280px;
        overflow-y: auto;
        background: rgba(255,255,255,0.1);
        border-radius: 5px;
        padding: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
     #listar-horarios .hora-item { /* Estilo para cada botão de horário */
        display: inline-block;
        background-color: #f8f9fa;
        color: #343a40;
        border: 1px solid #dee2e6;
        padding: 5px 10px;
        margin: 3px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s ease;
     }
      #listar-horarios .hora-item:hover {
        background-color: #e2e6ea;
     }
     #listar-horarios .hora-item.hora-selecionada {
        background-color: #28a745; /* Verde para selecionado */
        color: white;
        border-color: #1c7430;
     }
     #listar-horarios .text-danger {
        color: #ffcdd2 !important; /* Vermelho claro para contraste */
     }
	
	 }

</style>

</div> <div class="footer_section" style="background:#585757;">
    <div class="container">
        <div class="footer_content">

            <form id="form-agenda" method="post">
                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-3">
                       <label for="telefone" class="text-white">WhatsApp:</label>
                       <input onkeyup="buscarNome()" class="form-control form-control-sm" type="text" name="telefone" id="telefone" placeholder="(DDD) Número" required />
                    </div>
                     <div class="col-md-6 col-lg-3 mb-3">
                        <label for="nome_cliente" class="text-white">Nome:</label>
                        <input class="form-control form-control-sm" type="text" name="nome" id="nome_cliente" placeholder="Seu Nome" required />
                    </div>
                     <div class="col-md-6 col-lg-3 mb-3">
                        <label for="servico" class="text-white">Serviço:</label>
                         <select onchange="mudarServico()" class="form-control form-control-sm sel2" id="servico" name="servico" required>
                             <option value="">Selecione um Serviço</option>
                            <?php
                                $query_s = $pdo->query("SELECT * FROM servicos where ativo = 'Sim' and id_conta = '$id_conta' ORDER BY nome asc");
                                $res_s = $query_s->fetchAll(PDO::FETCH_ASSOC);
                                foreach($res_s as $serv){
                                    $valorF = number_format($serv['valor'], 2, ',', '.');
                                    echo '<option value="'.$serv['id'].'">'.htmlspecialchars($serv['nome']).' - R$ '.$valorF.'</option>'; // Adicionado htmlspecialchars
                                }
                            ?>
                        </select>
                    </div>
                     <div class="col-md-6 col-lg-3 mb-3">
                        <label for="funcionario" class="text-white">Profissional:</label>
                        <select class="form-control form-control-sm sel2" id="funcionario" name="funcionario" onchange="mudarFuncionario()" required>
                            <option value="">Selecione o Serviço</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-2 mb-3">
                    <div class="col-12">
                        <div class="calendario-container bg-light">
                            <div class="calendario-header">
                                <button type="button" class="btn btn-link btn-nav" id="btn-anterior">
                                    <i class="material-icons">arrow_back</i>
                                    <span class="ml-1 d-none d-sm-inline-block">Anterior</span>
                                </button>
                                <span class="escolha-horario">Escolha o Dia</span>
                                <button type="button" class="btn btn-link btn-nav" id="btn-proximo">
                                    <span class="mr-1 d-none d-sm-inline-block">Próximo</span>
                                    <i class="material-icons">arrow_forward</i>
                                </button>
                            </div>
                            
                            <div class="datas-carousel mt-2">
                                <?php foreach ($datasParaExibir as $dataItem):
                                    $desconto = obterDescontoParaData($dataItem['dataCompleta']);
                                    $classeSelecionado = ($dataItem['dataCompleta'] == $dataSelecionadaInicial) ? 'selecionado' : '';
                                ?>
                                    <div class="data-item <?= $classeSelecionado ?>" data-date="<?= $dataItem['dataCompleta'] ?>">
                                        <span class="dia-semana"><?= $dataItem['diaDaSemana'] ?></span>
                                        <span class="dia-mes">
                                            <span><?= $dataItem['dia'] ?></span>
                                            <span>/</span>
                                            <span class="mes"><?= $dataItem['mes'] ?></span>
                                        </span>
                                        <span class="desconto">
                                            <?= ($desconto > 0) ? $desconto . '% OFF' : '&nbsp;' ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                 <div class="row align-items-stretch">
                    <div class="col-md-9 mb-3">
                        <label class="text-white">Horários Disponíveis:</label>
                        <div id="listar-horarios">
                            <small class="text-white-50">Selecione Data e Profissional</small>
                        </div>
                    </div>
                     <div class="col-md-3 mb-3 d-flex flex-column">
                         <label for="obs" class="text-white">Observações:</label>
                         <input maxlength="100" type="text" class="form-control form-control-sm mb-2" name="obs" id="obs" placeholder="Opcional"> 

                         <button class="btn btn-success btn-block mt-auto" type="submit" id="btn_agendar" style="background-color: #108554;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)">
                            <span id='botao_salvar'><i class="fas fa-calendar-check"></i> Agendar</span>
                        </button>
                         <a href="meus-agendamentos.php?u=<?php echo $username?>" class="btn btn-info btn-block mt-2" id='botao_editar' style="display: none; background-color: #7c5c16;border: 1px solid #7c5c16; box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4) ">
                            <i class="fas fa-search"></i> Meus Agendamentos
                        </a>
                    </div>
                </div>

                <small><div id="mensagem" class="mt-2 text-white text-center"></div></small>

                <input type="hidden" id="dataSelecionadaInput" name="data_selecionada" value="<?= $dataSelecionadaInicial ?>">
                 <input type="hidden" id="id" name="id">
                <input type="hidden" id="hora_rec" name="hora_rec">
                 <input type="hidden" id="nome_func" name="nome_func">
                <input type="hidden" id="data_rec" name="data_rec">
                 <input type="hidden" id="nome_serv" name="nome_serv">

            </form>

            <div id="listar-cartoes" class="mt-4">
                </div>

        </div></div></div><?php require_once("rodape2.php") ?>

<div class="modal fade" id="modalExcluir" tabindex="-1" role="dialog" aria-labelledby="modalExcluirLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document"> 
        <div class="modal-content">
            <div class="modal-header text-white bg-danger">
                <h5 class="modal-title" id="modalExcluirLabel">Excluir Agendamento</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" id="btn-fechar-excluir">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-excluir">
                <div class="modal-body">
                    <p id="msg-excluir">Deseja realmente excluir este agendamento?</p> 
                    <input type="hidden" name="id" id="id_excluir">
                    <small><div id="mensagem-excluir" class="text-center mt-2"></div></small>
                </div>
                <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                   <button type="submit" class="btn btn-danger">Excluir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<script type="text/javascript" src="<?php echo $url?>sistema/painel/js/mascaras.js"></script>
<script src="<?php echo $url?>js/custom.js"></script>


<style type="text/css">
    /* Estilos específicos para Select2 no contexto do rodapé */
    .footer_section .select2-selection__rendered {
        line-height: calc(1.5em + .5rem + 2px) !important; /* Alinha com form-control-sm */
        font-size: 0.875rem !important;
        color: #495057 !important; /* Cor de texto padrão */
        padding-left: .5rem !important; /* Padding igual ao form-control-sm */
        padding-right: 1.75rem !important; /* Espaço para a seta */
    }

    .footer_section .select2-selection {
        height: calc(1.5em + .5rem + 2px) !important; /* Altura do form-control-sm */
        font-size:0.875rem !important;
        color:#495057 !important;
        border-radius: .2rem !important;
        border: 1px solid #ced4da !important; /* Borda padrão Bootstrap */
        background-color: #fff !important; /* Fundo branco */
    }
     .footer_section .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + .5rem + 2px) !important;
        position: absolute;
        top: 1px;
        right: 1px;
        width: 20px;
    }
     .footer_section .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #888 transparent transparent transparent !important;
        border-width: 5px 4px 0 4px !important;
        margin-left: -4px;
        margin-top: -2px;
    }
     .footer_section .select2-dropdown {
        color: #495057;
        border-color: #ced4da;
        font-size: 0.875rem;
     }
</style>

<script type="text/javascript">
// <![CDATA[  <-- Adicione esta linha
$(document).ready(function() {
    $('#telefone').mask('(00) 00000-0000');

    if(document.getElementById("botao_editar")) { // Verifica se o botão existe
        document.getElementById("botao_editar").style.display = "none";
    }

    $('.sel2').select2({
         width: '100%',
         theme: "default" // Usa o tema padrão que funciona melhor com esses overrides
    });

    // Inicializa o Slick Carousel (se o elemento existir)
    if ($('.datas-carousel').length > 0) {
        $('.datas-carousel').slick({
            dots: false,
            infinite: false,
            speed: 300,
            slidesToShow: 10, // Começa com mais para telas maiores
            slidesToScroll: 10,
            arrows: false,
            variableWidth: false,
            responsive: [
                { breakpoint: 1200, settings: { slidesToShow: 10, slidesToScroll: 10 }}, // Ajuste para LG
                { breakpoint: 992, settings: { slidesToShow: 7, slidesToScroll: 7 }},
                { breakpoint: 768, settings: { slidesToShow: 7, slidesToScroll: 7 }},
                { breakpoint: 576, settings: { slidesToShow: 5, slidesToScroll: 5 }}
            ]
        });

        // Controles de Navegação Personalizados
        $('#btn-anterior').on('click', function() { $('.datas-carousel').slick('slickPrev'); });
        $('#btn-proximo').on('click', function() { $('.datas-carousel').slick('slickNext'); });

        // Seleção de Data Inicial e Ação
        var dataInicialPHP = "<?php echo $dataSelecionadaInicial; ?>";
        selecionarDataItemPelaData(dataInicialPHP);

        // Listener para seleção de data no Carrossel
        $('.datas-carousel').on('click', '.data-item', function() {
            $('.datas-carousel .data-item').removeClass('selecionado');
            $(this).addClass('selecionado');
            var dataSelecionada = $(this).data('date');
            console.log("Data Selecionada (Carrossel):", dataSelecionada);
             $('#dataSelecionadaInput').val(dataSelecionada); // Atualiza o input hidden
             mudarFuncionario(); // Atualiza horários para nova data
        });

         // Carrega funcionários e horários iniciais APÓS selecionar data inicial
         mudarServico(); // Carrega funcionários baseado no serviço inicial (se houver)
         // mudarFuncionario(); // A chamada em mudarServico->listarFuncionarios já limpará os horários


    } else {
        console.warn("Elemento .datas-carousel não encontrado para inicializar o Slick.");
    }


});


// Função para selecionar um item no carrossel pela data YYYY-MM-DD
function selecionarDataItemPelaData(dataYmd) {
    if (!$('.datas-carousel').length) return; // Sai se o carrossel não existe

    $('.datas-carousel .data-item').removeClass('selecionado');
    const itemSelecionar = $('.datas-carousel .data-item[data-date="' + dataYmd + '"]');

    if(itemSelecionar.length > 0) {
        itemSelecionar.addClass('selecionado');
        $('#dataSelecionadaInput').val(dataYmd);

        // Centraliza o slide apenas se o Slick foi inicializado
        if ($('.datas-carousel').hasClass('slick-initialized')) {
            const slideIndex = itemSelecionar.closest('.slick-slide').data('slick-index');
            if (slideIndex !== undefined) {
                $('.datas-carousel').slick('slickGoTo', parseInt(slideIndex), true); // true para animar
            }
        }
    } else {
        // Fallback: Seleciona o primeiro item se a data não for encontrada
        const primeiroItem = $('.datas-carousel .data-item:first-child');
        if(primeiroItem.length > 0){
             primeiroItem.addClass('selecionado');
             $('#dataSelecionadaInput').val(primeiroItem.data('date'));
             if ($('.datas-carousel').hasClass('slick-initialized')) {
                 $('.datas-carousel').slick('slickGoTo', 0); // Vai para o primeiro
             }
        } else {
            // Nenhuma data no carrossel, define um valor padrão ou limpa
            $('#dataSelecionadaInput').val("<?php echo $data_atual_iso; ?>"); // Usa data atual como fallback
        }
        console.warn("Item para data " + dataYmd + " não encontrado no carrossel. Selecionado o primeiro/padrão.");
    }
}


// Função chamada quando o input date é alterado (SE você reativar o input date)
/* function mudarDataCalendario(){
    const novaData = $('#data').val();
    console.log("Data alterada no Input Date:", novaData);
    selecionarDataItemPelaData(novaData);
    mudarFuncionario();
} */

// Função para buscar nome e outras ações
function buscarNome(){
    var tel = $('#telefone').val();
    listarCartoes(tel);

    $.ajax({
        url: "ajax/listar-nome.php", // Verifique o caminho
        method: 'POST',
        data: {tel: tel},
        dataType: "text",
        success:function(result){
            var split = result.split("*");
             if(split[0] && split[0] !== ""){
                 $("#nome_cliente").val(split[0]);
             } else {
                 // Opcional: Limpar o nome se não encontrar?
                 // $("#nome_cliente").val('');
             }

            // Lógica para pré-selecionar Serviço e mostrar botão Editar
            if(split[5] && split[5] !== ""){
                 $("#servico").val(parseInt(split[5])).trigger('change'); // Usa trigger('change')
                 if(document.getElementById("botao_editar")) {
                     document.getElementById("botao_editar").style.display = "block";
                 }
                 if(document.getElementById("botao_salvar")){
                     $("#botao_salvar").html('<i class="fas fa-calendar-check"></i> Novo Agendamento');
                 }
             } else {
                 if(document.getElementById("botao_editar")) {
                    document.getElementById("botao_editar").style.display = "none";
                 }
                  if(document.getElementById("botao_salvar")){
                     // Resetar botão apenas se não houver serviço pré-definido?
                     // $("#botao_salvar").html('<i class="fas fa-calendar-check"></i> Confirmar Agendamento');
                  }
             }

            // Pré-selecionar Funcionário (split[2]) - Requer lógica mais cuidadosa
            // É melhor deixar o usuário selecionar após carregar a lista

             // Atualizar mensagem de exclusão (se o modal for usado a partir daqui)
             if(split[7] && split[4]){
                $("#msg-excluir").text('Deseja Realmente excluir esse agendamento feito para o dia ' + split[7] + ' às ' + split[4]);
             }

             // NÃO chame mudarFuncionario() aqui diretamente, pois listarFuncionarios será chamado
             // pelo trigger('change') do #servico, e então mudarFuncionario será chamado
             // quando o usuário selecionar um funcionário.

        },
         error: function(xhr, status, error){
             console.error("Erro ao buscar nome:", error);
         }
    });
}

// Função para carregar horários E nome do funcionário selecionado
function mudarFuncionario(){
    var funcionario = $('#funcionario').val();
    var data = $('#dataSelecionadaInput').val();
    var hora = $('#hora_rec').val();
    var nome = $('#nome_cliente').val();
    var telefone = $('#telefone').val();

     if (!data) { // Garante que temos uma data
         data = "<?php echo $data_atual_iso; ?>"; // Usa data atual se o input hidden estiver vazio
         $('#dataSelecionadaInput').val(data);
         console.warn("Input hidden de data estava vazio, usando data atual.");
     }

    listarHorarios(funcionario, data, hora, nome, telefone); // Atualiza a lista de horários
    listarFuncionario(); // Atualiza o nome no input hidden #nome_func
}

// Função para listar horários
function listarHorarios(funcionario, data, hora, nome, telefone){	
     $('#listar-horarios').html('<small class="text-white-50">Carregando horários...</small>');
     if (!funcionario || !data) {
         $('#listar-horarios').html('<small class="text-white-50">Selecione Data e Profissional</small>');
         return;
     }
	 
    $.ajax({
        url: "ajax/listar-horarios2.php", // Verifique o caminho
        method: 'POST',
        data: {funcionario, data, hora, nome, telefone},
        dataType: "html",
        success:function(result){
            if(result.trim() === '000'){
                 Swal.fire('Data Inválida!', 'Selecione uma data igual ou maior que hoje!', 'warning');
                $('#dataSelecionadaInput').val('<?php echo $data_atual_iso; ?>'); // Volta para data atual no hidden
                 selecionarDataItemPelaData('<?php echo $data_atual_iso; ?>'); // Seleciona hoje no carrossel
                // Não precisa chamar mudarFuncionario aqui, pois selecionarDataItemPelaData já chama
                return;
            } else if (result.trim() === '') {
                $('#listar-horarios').html('<div class="alert alert-warning text-dark small p-1 text-center" role="alert">Nenhum horário disponível!</div>');
            } else {
                $("#listar-horarios").html(result);
            }
        },
        error: function() {
            $('#listar-horarios').html('<div class="alert alert-danger text-dark small p-1 text-center" role="alert">Erro ao carregar horários.</div>');
        }
    });
}

// Função para obter o nome do funcionário selecionado e colocar no input hidden
function listarFuncionario(){
    var func_id = $("#funcionario").val();
    if (!func_id) {
        $("#nome_func").val(''); // Limpa se nenhum selecionado
        return;
    }
     // Pega o texto da opção selecionada (que inclui o nome)
     var nomeFuncionario = $("#funcionario option:selected").text();
     // Remove informações extras se houver (ex: " - Disponível") - ajuste se necessário
     nomeFuncionario = nomeFuncionario.split(" - ")[0];
    $("#nome_func").val(nomeFuncionario.trim()); // Atualiza input hidden com o nome
}

// Função chamada ao mudar o serviço
function mudarServico(){
    listarFuncionarios(); // Carrega os funcionários
    var serv_id = $("#servico").val();
     if (!serv_id) {
         $("#nome_serv").val('');
        return;
    }
     // Pega o texto da opção selecionada (que inclui o nome)
     var nomeServico = $("#servico option:selected").text();
     // Remove informações extras (valor)
     nomeServico = nomeServico.split(" - R$")[0];
    $("#nome_serv").val(nomeServico.trim()); // Atualiza input hidden
}


function listarFuncionarios(){
    var serv = $("#servico").val();
    var $selectFunc = $('#funcionario');

    $selectFunc.html('<option value="">Carregando...</option>').prop('disabled', true);

    if (!serv) {
        $selectFunc.html('<option value="">Selecione um Serviço</option>').prop('disabled', false);
        // (Re)Inicializa Select2 mesmo sem serviço
        $selectFunc.select2({
            width: '100%', theme: "default", templateResult: formatarOpcaoComImagem,
            templateSelection: formatarSelecaoComImagem, escapeMarkup: function (m) { return m; }
        });
        $('#listar-horarios').html('<small class="text-white-50">Selecione Serviço e Profissional</small>');
        return;
    }

    $.ajax({
        url: "ajax/listar-funcionarios.php", // Este PHP DEVE gerar o data-image agora
        method: 'POST',
        data: {serv: serv},
        dataType: "html", // Espera as tags <option>
        success:function(result){
            $selectFunc.html('<option value="">Selecione um Profissional</option>' + result).prop('disabled', false);
            // REINICIALIZA Select2 DEPOIS de carregar as novas opções
            $selectFunc.select2({
                width: '100%', theme: "default", templateResult: formatarOpcaoComImagem,
                templateSelection: formatarSelecaoComImagem, escapeMarkup: function (m) { return m; }
            });
            $('#listar-horarios').html('<small class="text-white-50">Selecione um Profissional</small>');
            $("#nome_func").val('');
        },
        error: function() {
            $selectFunc.html('<option value="">Erro ao carregar</option>').prop('disabled', false);
            // (Re)Inicializa Select2 no erro também
            $selectFunc.select2({ /* ... opções ... */ });
        }
    });
}














// Função para listar funcionários baseados no serviço
// function listarFuncionarios(){
//     var serv = $("#servico").val();
//     var $selectFunc = $('#funcionario'); // Cache do seletor

//     $selectFunc.html('<option value="">Carregando...</option>').prop('disabled', true).trigger('change.select2');

//      if (!serv) {
//         $selectFunc.html('<option value="">Selecione um Serviço</option>').prop('disabled', false).trigger('change.select2');
//         $('#listar-horarios').html('<small class="text-white-50">Selecione Serviço e Profissional</small>');
//         return;
//     }

//     $.ajax({
//         url: "ajax/listar-funcionarios.php", // Verifique o caminho
//         method: 'POST',
//         data: {serv: serv},
//         dataType: "html",
//         success:function(result){
//             $selectFunc.html('<option value="">Selecione um Profissional</option>' + result).prop('disabled', false);
//             $selectFunc.val('').trigger('change.select2'); // Garante que o placeholder apareça e limpa seleção
//             $('#listar-horarios').html('<small class="text-white-50">Selecione um Profissional</small>'); // Limpa horários
//              $("#nome_func").val(''); // Limpa nome do func no hidden input
//         },
//         error: function() {
//             $selectFunc.html('<option value="">Erro ao carregar</option>').prop('disabled', false).trigger('change.select2');
//         }
//     });
// }

// Função para listar cartões (mantida)
function listarCartoes(tel){
    if (!tel) { // Não busca se telefone vazio
        $("#listar-cartoes").html('');
        return;
    }
    $.ajax({
        url: "ajax/listar-cartoes.php", // Verifique o caminho
        method: 'POST',
        data: {tel: tel},
        dataType: "text",
        success:function(result){
            $("#listar-cartoes").html(result);
        }
        // Adicionar error handler é bom
    });
}

// Função Salvar (apenas limpa ID)
function salvar(){
    $('#id').val('');
}

// Selecionar Horário (clique nos botões de horário)
$('#listar-horarios').on('click', '.hora-item', function(){
	
    $('.hora-item').removeClass('hora-selecionada'); // Remove seleção de outros
    $(this).addClass('hora-selecionada'); // Marca o clicado
    $('#hora_rec').val($(this).data('hora')); // Pega a hora do data-hora e põe no input hidden
    console.log("Hora selecionada:", $(this).data('hora'));
});


// Submit do Formulário Principal
$("#form-agenda").submit(function (event) {
    event.preventDefault();
    var $btnAgendar = $('#btn_agendar');
    var $mensagemDiv = $('#mensagem');

    // Verifica se um horário foi selecionado
    if (!$('#hora_rec').val()) {
        Swal.fire('Ops!', 'Por favor, selecione um horário disponível.', 'warning');
        return; // Impede o envio
    }

    $btnAgendar.prop('disabled', true).find('span').text('Processando...');
    $mensagemDiv.text('Carregando...').removeClass('text-danger text-success');

    var formData = new FormData(this);
  	formData.append('data_selecionada', $('#dataSelecionadaInput').val());
    // Adiciona a hora selecionada explicitamente (do input hidden #hora_rec)
     formData.append('hora', $('#hora_rec').val()); // Garante que a hora selecionada seja enviada

    $.ajax({
        url: "ajax/agendar_temp.php", // Verifique o caminho
        type: 'POST',
        data: formData,
        dataType: 'text', // Espera texto (com split '*')
        success: function (mensagem) {
             var msg = mensagem.split('*');
             var id_agd = msg.length > 1 ? msg[1] : null; // Pega ID se existir

            $mensagemDiv.text('');
            if (msg[0].trim() == "Pré Agendado" && id_agd) {
                $mensagemDiv.addClass('text-success').text(msg[0]);
                 // buscarNome(); // Provavelmente não necessário aqui

                  Swal.fire({
                      title: 'Pré-Agendado!',
                      text: 'Seu horário foi reservado. Redirecionando para concluir...',
                      icon: 'info',
                      timer: 2500,
                      showConfirmButton: false,
                      allowOutsideClick: false
                  }).then(() => {
                       window.location="pagamento/"+id_agd+"/100"; // Ajuste o '100' se necessário
                  });

            } else {
                // Exibe outras mensagens de erro/aviso do backend
                $mensagemDiv.addClass('text-danger').text(msg[0] || "Ocorreu um erro.");
                $btnAgendar.prop('disabled', false).find('span').html('<i class="fas fa-calendar-check"></i> Confirmar Agendamento');
            }
        },
        error: function(xhr, status, error){
             $mensagemDiv.addClass('text-danger').text('Erro na comunicação com o servidor.');
             $btnAgendar.prop('disabled', false).find('span').html('<i class="fas fa-calendar-check"></i> Confirmar Agendamento');
             console.error("Erro AJAX agendar_temp:", xhr.responseText, status, error);
        },
        cache: false,
        contentType: false,
        processData: false,
    });
});


// Exclusão (Modal)
function excluir(id, nome, data, hora, servico, func) { // Passa os dados para o modal se precisar mostrar
    $('#id_excluir').val(id); // Define o ID no input hidden do modal de exclusão
     // Atualiza a mensagem do modal (opcional)
     $('#msg-excluir').text('Deseja realmente excluir o agendamento de ' + nome + ' para ' + data + ' às ' + hora + '?');
    $('#modalExcluir').modal('show'); // Abre o modal de exclusão
}

$("#form-excluir").submit(function (event) {
    event.preventDefault();
     var idParaExcluir = $('#id_excluir').val();
     var $mensagemExcluirDiv = $('#mensagem-excluir');
     var $submitButton = $(this).find('button[type="submit"]'); // Cache do botão

     $mensagemExcluirDiv.text('Excluindo...').removeClass('text-danger text-success');
     $submitButton.prop('disabled', true); // Desabilita botão

     $.ajax({
        url: "ajax/excluir.php", // Verifique o caminho
        type: 'POST',
        data: { id: idParaExcluir },
        dataType: 'text',
        success: function (mensagem) {
            $mensagemExcluirDiv.text('');
             var msgTrimmed = mensagem.trim();
            if (msgTrimmed == "Cancelado com Sucesso" || msgTrimmed == "Excluído com Sucesso") {
                 // Fecha o modal
                 $('#modalExcluir').modal('hide');
                // Mostra um SweetAlert de sucesso
                 Swal.fire('Excluído!', 'Agendamento cancelado com sucesso.', 'success');
                 // Atualiza a interface (recarrega a lista de agendamentos)
                 // Se esta página lista os agendamentos, você pode recarregar:
                 // setTimeout(() => { window.location.reload(); }, 1500); // Recarrega após 1.5s
                 // Ou chamar uma função específica se existir:
                 // listarMeusAgendamentos();
            } else {
                 $mensagemExcluirDiv.addClass('text-danger').text(msgTrimmed || "Erro ao excluir.");
                 $submitButton.prop('disabled', false); // Reabilita botão no erro
            }

        },
         error: function(xhr, status, error){
             $mensagemExcluirDiv.addClass('text-danger').text('Erro na comunicação.');
             console.error("Erro AJAX excluir:", xhr.responseText, status, error);
             $submitButton.prop('disabled', false); // Reabilita botão no erro
        }
    });
});


// DENTRO DO SEU $(document).ready(function() { ... });

$('.sel2').select2({
    width: '100%',
    theme: "default", // Ou seu tema preferido
    templateResult: formatarOpcaoComImagem,  // Função para formatar opções na lista
    templateSelection: formatarSelecaoComImagem, // Função para formatar o item selecionado
    escapeMarkup: function (markup) { return markup; } // MUITO IMPORTANTE: Permite HTML nos templates
});




// --- FUNÇÕES DE TEMPLATE ---

// Formata como cada opção aparece na lista dropdown
function formatarOpcaoComImagem (opcao) {
    if (!opcao.id) { // Ignora o placeholder "Selecione..."
        return opcao.text;
    }

    var $opcao = $(opcao.element); // Pega o elemento <option> original
    var imgUrl = $opcao.data('image'); // Pega o URL da imagem do atributo data-image
    var nome = opcao.text;

    // Se não houver URL de imagem ou for a imagem padrão "sem foto"
    if (!imgUrl || imgUrl.includes('sem-foto.jpg')) {
        // Retorna o texto com um ícone genérico (opcional)
         // Certifique-se de ter Font Awesome incluído se usar o ícone
        return $('<span><i class="fas fa-user-circle" style="margin-right: 8px; color: #ccc;"></i> ' + nome + '</span>');
    }

    // Monta o HTML com a imagem e o texto
    var $resultado = $(
        '<span style="display: flex; align-items: center;">' + // Usa flexbox para alinhar
        '<img src="' + imgUrl + '" style="height: 24px; width: 24px; object-fit: cover; border-radius: 50%; margin-right: 8px;" /> ' + // Estilos da imagem
        nome + // O texto da opção
        '</span>'
    );
    return $resultado;
};

// Formata como o item SELECIONADO aparece na caixa do select
function formatarSelecaoComImagem (opcao) {
    // Pode ser igual à função acima ou mais simples
     if (!opcao.id) {
        return opcao.text;
    }
    var $opcao = $(opcao.element);
    var imgUrl = $opcao.data('image');
    var nome = opcao.text;

    if (!imgUrl || imgUrl.includes('sem-foto.jpg')) {
        return $('<span><i class="fas fa-user-circle" style="margin-right: 5px; color: #555;"></i> ' + nome + '</span>');
    }

    // Imagem um pouco menor no item selecionado (opcional)
    var $resultado = $(
        '<span style="display: flex; align-items: center;">' +
        '<img src="' + imgUrl + '" style="height: 20px; width: 20px; object-fit: cover; border-radius: 50%; margin-right: 5px;" /> ' +
        nome +
        '</span>'
    );
    return $resultado;
};

// ]]> <-- Adicione esta linha
</script>

</body>
</html>