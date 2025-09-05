<?php
@session_start();
$id_conta = $_SESSION['id_conta'];

require_once("verificar.php");
require_once("../conexao.php");

$pag_inicial = 'home';

$id_usuario = $_SESSION['id_usuario'];

//VERIFICA O STATUS DO WHATSAPP	
require __DIR__ . '../../../ajax/status.php';

// Fetch user data
$query = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND id_conta = ?");
$query->execute([$id_usuario, $id_conta]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);

$nome_usuario = $email_usuario = $cpf_usuario = $senha_usuario = $nivel_usuario = $telefone_usuario = $endereco_usuario = $foto_usuario = $atendimento = $intervalo_horarios = '';
if (!empty($res)) {
    $user_data = $res[0];
    $nome_usuario = $user_data['nome'];
    $email_usuario = $user_data['email'];
    $cpf_usuario = $user_data['cpf'];
    $senha_usuario = $user_data['senha'];
    $nivel_usuario = $user_data['nivel'];
    $telefone_usuario = $user_data['telefone'];
    $endereco_usuario = $user_data['endereco'];
    $foto_usuario = $user_data['foto'];
    $atendimento = $user_data['atendimento'];
    $intervalo_horarios = $user_data['intervalo'];
}

$pag = (isset($_GET['pag']) && $_GET['pag'] != "") ? $_GET['pag'] : $pag_inicial;
if (@$_SESSION['nivel_usuario'] != 'administrador') {
    $pag = 'agenda';
}

$data_atual = date('Y-m-d');
$mes_atual = date('m');
$ano_atual = date('Y');
$data_mes = $ano_atual . "-" . $mes_atual . "-01";
$data_ano = $ano_atual . "-01-01";

$partesInicial = explode('-', $data_atual);
$dataDiaInicial = $partesInicial[2];
$dataMesInicial = $partesInicial[1];

// Fetch plano data
$query = $pdo->prepare("SELECT plano FROM config WHERE id = ?");
$query->execute([$id_conta]);
$res3 = $query->fetch(PDO::FETCH_ASSOC);
$plano = $res3['plano'];

// Check if caixa is open
$query_caixa = $pdo->prepare("SELECT * FROM caixa WHERE id_conta = ? ORDER BY id DESC LIMIT 1");
$query_caixa->execute([$id_conta]);
$res_caixa = $query_caixa->fetchAll(PDO::FETCH_ASSOC);
$caixa_aberto = !empty($res_caixa) && $res_caixa[0]['data_fechamento'] === NULL;

// Fetch notification data
$total_agendamentos_hoje_usuario_pendentes = 0;
$total_encaixes_hoje = 0;
$total_aniversariantes_hoje = 0;
$total_comentarios = 0;

if (@$_SESSION['nivel_usuario'] == 'administrador') {
    $query = $pdo->prepare("SELECT * FROM agendamentos WHERE data = CURDATE() AND status = 'Agendado' AND id_conta = ?");
    $query->execute([$id_conta]);
    $total_agendamentos_hoje_usuario_pendentes = count($query->fetchAll());
    $query = $pdo->prepare("SELECT * FROM encaixe WHERE data = CURDATE() AND id_conta = ?");
    $query->execute([$id_conta]);
    $total_encaixes_hoje = count($query->fetchAll());
    $query = $pdo->prepare("SELECT * FROM clientes WHERE MONTH(data_nasc) = ? AND DAY(data_nasc) = ? AND id_conta = ?");
    $query->execute([$dataMesInicial, $dataDiaInicial, $id_conta]);
    $total_aniversariantes_hoje = count($query->fetchAll());
    $query = $pdo->prepare("SELECT * FROM comentarios WHERE ativo != 'Sim' AND id_conta = ?");
    $query->execute([$id_conta]);
    $total_comentarios = count($query->fetchAll());
} else {
    $query = $pdo->prepare("SELECT * FROM agendamentos WHERE data = CURDATE() AND funcionario = ? AND status = 'Agendado' AND id_conta = ?");
    $query->execute([$id_usuario, $id_conta]);
    $total_agendamentos_hoje_usuario_pendentes = count($query->fetchAll());
    $query = $pdo->prepare("SELECT * FROM encaixe WHERE data = CURDATE() AND profissional = ? AND id_conta = ?");
    $query->execute([$id_usuario, $id_conta]);
    $total_encaixes_hoje = count($query->fetchAll());
}

$link_ag = (@$_SESSION['nivel_usuario'] == 'administrador') ? 'agendamentos' : 'agenda';
$username = strtolower(str_replace(' ', '', $nome_usuario));

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title><?php echo $nome_sistema ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../../images/favicon<?php echo $id_conta?>.png">

    <script src="https://cdn.tailwindcss.com"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background-color: transparent;
        }
        .modal-header-gradient {
            background-image: linear-gradient(to right, #6a85b6, #bac8e0);
        }
        .modal-background {
            background-color: rgba(0, 0, 0, 0.5);
        }
        @keyframes pulse-once {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-pulse-once { animation: pulse-once 1.5s ease-in-out; }

        /* Estilos do Select2 para integração com o Tailwind */
        .select2-container .select2-selection--single {
            height: 42px !important;
            border-radius: 0.5rem !important;
            border: 1px solid #d1d5db !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 1rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 0.5rem !important;
        }
    </style>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

</head>
<body class="bg-gray-100 flex min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">

    <div x-show="sidebarOpen" @click.away="sidebarOpen = false"
         class="fixed inset-y-0 left-0 w-64 bg-slate-800 text-white flex flex-col z-50 transform lg:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="flex items-center justify-center p-6 bg-slate-900">
            <a href="index.php" class="flex items-center space-x-2">
                <img src="../../images/icone_512.png" alt="Logo" class="w-10 h-10">
                <div class="flex flex-col">
                    <span class="text-xl font-bold">Painel</span>
                    <span class="text-xs text-slate-400 font-medium tracking-wide"><?php echo $nome_sistema ?></span>
                </div>
            </a>
        </div>
        
        <nav class="flex-1 overflow-y-auto custom-scrollbar p-4">
            <ul class="space-y-2">
                <?php if (@$_SESSION['nivel_usuario'] == 'administrador'): ?>
                <li>
                    <a href="index.php" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out">
                        <i class="fa fa-dashboard w-5 mr-3"></i> Dashboards
                    </a>
                </li>
                <li>
                    <a href="caixa" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out">
                        <i class="fas fa-cash-register w-5 mr-3"></i> Abrir Caixa
                    </a>
                </li>
                <li>
                    <a href="agendamentos" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out">
                        <i class="fe fe-clock w-5 mr-3"></i> Agendamentos
                    </a>
                </li>
                <li x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out w-full text-left">
                        <i class="fa fa-pencil w-5 mr-3"></i> Cadastros
                        <i class="fa fa-angle-left ml-auto transition-transform" :class="{ 'rotate-90': open }"></i>
                    </button>
                    <ul x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="pl-6 pt-2 space-y-1">
                        <li><a href="clientes" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Clientes</a></li>
                        <?php if($plano == '2'): ?>
                        <li><a href="funcionarios" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Profissionais</a></li>
                        <?php endif; ?>
                        <li><a href="fornecedores" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Fornecedores</a></li>
                        <li><a href="servicos" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Serviços</a></li>
                        <li><a href="cupons" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Cupons de Desconto</a></li>
                    </ul>
                </li>
                <li x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out w-full text-left">
                        <i class="fa fa-tags w-5 mr-3"></i> Produtos
                        <i class="fa fa-angle-left ml-auto transition-transform" :class="{ 'rotate-90': open }"></i>
                    </button>
                    <ul x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="pl-6 pt-2 space-y-1">
                        <li><a href="produtos" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Produtos</a></li>
                        <li><a href="vendas" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Vendas de Produtos</a></li>
                        <li><a href="compras" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Compras de Produtos</a></li>
                        <li><a href="estoque" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Estoque Baixo</a></li>
                        <li><a href="saidas" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Saídas</a></li>
                        <li><a href="entradas" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Entradas</a></li>
                    </ul>
                </li>
                <?php if ($assinaturas2 == 'Sim'): ?>
                <li x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out w-full text-left">
                        <i class="fas fa-crown w-5 mr-3"></i> Clube do Assinante
                        <i class="fa fa-angle-left ml-auto transition-transform" :class="{ 'rotate-90': open }"></i>
                    </button>
                    <ul x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="pl-6 pt-2 space-y-1">
                        <li><a href="assinantes" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Assinantes</a></li>
                        <li><a href="conf_planos" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Configuração</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <li x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out w-full text-left">
                        <i class="fa fa-usd w-5 mr-3"></i> Financeiro
                        <i class="fa fa-angle-left ml-auto transition-transform" :class="{ 'rotate-90': open }"></i>
                    </button>
                    <ul x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="pl-6 pt-2 space-y-1">
                        <li><a href="pagar" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Contas à Pagar</a></li>
                        <li><a href="receber" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Contas à Receber</a></li>
                        <li><a href="comissoes" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Comissões</a></li>
                    </ul>
                </li>
                <li x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out w-full text-left">
                        <i class="fa fa-file-pdf-o w-5 mr-3"></i> Relatórios
                        <i class="fa fa-angle-left ml-auto transition-transform" :class="{ 'rotate-90': open }"></i>
                    </button>
                    <ul x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="pl-6 pt-2 space-y-1">
                        <li><a href="rel/rel_produtos_class.php" target="_blank" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Relatório de Produtos</a></li>
                        <li><a href="#" onclick="showModal('RelEntradas')" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Entradas / Ganhos</a></li>
                        <li><a href="#" onclick="showModal('RelSaidas')" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Saídas / Despesas</a></li>
                        <li><a href="#" onclick="showModal('RelComissoes')" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Relatório de Comissões</a></li>
                        <li><a href="#" onclick="showModal('RelCon')" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Relatório de Contas</a></li>
                        <li><a href="#" onclick="showModal('RelServicos')" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Relatório de Serviços</a></li>
                        <li><a href="#" onclick="showModal('RelAniv')" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Relatório de Aniversariantes</a></li>
                        <li><a href="#" onclick="showModal('RelLucro')" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Demonstrativo de Lucro</a></li>
                    </ul>
                </li>
                <li>
                    <a href="campanhas" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out">
                        <i class="fa fa-paper-plane w-5 mr-3"></i> Campanha de retorno
                    </a>
                </li>
                <li>
                    <a href="#" onclick="showModal('modalSeuLink')" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out">
                        <i class="fa fa-link w-5 mr-3"></i> Seu Link
                    </a>
                </li>
                <li>
                    <a href="comentarios" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out">
                        <i class="fa fa-comments w-5 mr-3"></i> Comentários
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($atendimento == 'Sim'): ?>
                <li class="border-t border-slate-700 pt-2 mt-2">
                    <div class="p-2 text-slate-400 text-sm font-semibold uppercase">Menu do Profissional</div>
                </li>
                <li>
                    <a href="agenda" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out">
                        <i class="fa fa-calendar-o w-5 mr-3"></i> Minha Agenda
                    </a>
                </li>
                <li>
                    <a href="servicos_func" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out">
                        <i class="fa fa-server w-5 mr-3"></i> Meus Serviços
                    </a>
                </li>
                <li>
                    <a href="minhas_comissoes" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out">
                        <i class="fa fa-dollar-sign w-5 mr-3"></i> Minhas Comissões
                    </a>
                </li>
                <li x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition duration-200 ease-in-out w-full text-left">
                        <i class="fa fa-clock w-5 mr-3"></i> Meus Horários / Dias
                        <i class="fa fa-angle-left ml-auto transition-transform" :class="{ 'rotate-90': open }"></i>
                    </button>
                    <ul x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="pl-6 pt-2 space-y-1">
                        <li><a href="dias" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Horários / Dias</a></li>
                        <li><a href="dias_bloqueio_func" class="block p-2 text-sm rounded-lg hover:bg-slate-700 transition">Bloqueio de Dias</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <div class="flex-1 flex flex-col transition-all duration-300 ease-in-out lg:ml-64">
        <header class="bg-white shadow-sm sticky top-0 z-40 p-4 flex items-center justify-between">
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 text-gray-600 hover:bg-gray-200 rounded-lg lg:hidden">
                <i class="fa fa-bars text-xl"></i>
            </button>
            <div class="hidden lg:block"></div> <div class="flex items-center space-x-4">
                <?php if (@$_SESSION['nivel_usuario'] == 'administrador'): ?>
                <a href="<?= $link_ag ?>" class="relative" title="Agendamentos hoje">
                    <i class="fas fa-calendar-check text-xl text-gray-600"></i>
                    <?php if ($total_agendamentos_hoje_usuario_pendentes > 0): ?>
                    <span class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-red-500 text-white text-xs font-bold rounded-full px-2 py-1"><?= $total_agendamentos_hoje_usuario_pendentes ?></span>
                    <?php endif; ?>
                </a>
                <a href="#" class="relative" title="Encaixes hoje">
                    <i class="fa fa-bell text-xl text-gray-600"></i>
                    <?php if ($total_encaixes_hoje > 0): ?>
                    <span class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-red-500 text-white text-xs font-bold rounded-full px-2 py-1"><?= $total_encaixes_hoje ?></span>
                    <?php endif; ?>
                </a>
                <a href="#" onclick="showModal('RelAniv')" class="relative" title="Aniversariantes de hoje">
                    <i class="fa fa-birthday-cake text-xl text-gray-600"></i>
                    <?php if ($total_aniversariantes_hoje > 0): ?>
                    <span class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-green-600 text-white text-xs font-bold rounded-full px-2 py-1"><?= $total_aniversariantes_hoje ?></span>
                    <?php endif; ?>
                </a>
                <a href="comentarios" class="relative" title="Depoimentos pendentes">
                    <i class="fa fa-comment text-xl text-gray-600"></i>
                    <?php if ($total_comentarios > 0): ?>
                    <span class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-blue-700 text-white text-xs font-bold rounded-full px-2 py-1"><?= $total_comentarios ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
                
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                        <img src="img/perfil/<?= $foto_usuario ?: 'sem-foto.jpg' ?>" alt="Foto de Perfil" class="w-10 h-10 rounded-full object-cover">
                        <div class="hidden md:flex flex-col items-start">
                            <span class="text-sm font-semibold text-gray-800"><?= $nome_usuario ?></span>
                            <span class="text-xs text-gray-500"><?= $nivel_usuario ?></span>
                        </div>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2">
                        <a href="#" onclick="showModal('modalPerfil')" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 transition">
                            <i class="fa fa-suitcase mr-2"></i> Editar Perfil
                        </a>
                        <?php if(@$_SESSION['nivel_usuario'] == 'administrador'): ?>
                        <a href="configuracoes" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 transition">
                            <i class="fa fa-cog mr-2"></i> Config. Sistema
                        </a>
                        <?php endif; ?>
                        <a href="conf_site" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 transition">
                            <i class="fa fa-link mr-2"></i> Config.Seu Site
                        </a>
                        <?php if (@$_SESSION['nivel_usuario'] == 'administrador' && $cliente_stripe == null): ?>
                        <a href="#" onclick="showModal('assinaturaModal')" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 transition">
                            <i class="fa fa-dollar mr-2"></i> Minha Assinatura
                        </a>
                        <?php else: ?>
                        <a href="../../portal.php" target="_blank" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 transition">
                            <i class="fa fa-dollar mr-2"></i> Sua Assinatura
                        </a>
                        <?php endif; ?>
                        <a href="logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 transition">
                            <i class="fa fa-sign-out mr-2"></i> Sair
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 overflow-y-auto">
            <?php require_once("paginas/" . $pag . '.php'); ?>
        </main>
        
        <?php if ($caixa_aberto): ?>
        <a href="caixa" class="fixed bottom-6 right-6 bg-green-500 text-white p-4 rounded-full shadow-lg hover:bg-green-600 transition-all duration-300">
            <i class="fas fa-cash-register mr-2"></i> Caixa Aberto
        </a>
        <?php endif; ?>
    </div>

    <div id="modalPerfil" class="fixed inset-0 hidden modal-background z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden max-w-lg w-full m-4">
            <div class="modal-header-gradient text-white p-6 rounded-t-xl flex justify-between items-center">
                <h4 class="text-xl font-bold">Editar Perfil</h4>
                <button type="button" onclick="hideModal('modalPerfil')" class="text-white text-2xl">&times;</button>
            </div>
            <form id="form-perfil" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nome-perfil" class="block text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" id="nome-perfil" name="nome" value="<?= $nome_usuario ?>" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="email-perfil" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email-perfil" name="email" value="<?= $email_usuario ?>" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="telefone-perfil" class="block text-sm font-medium text-gray-700">Telefone</label>
                        <input type="text" id="telefone-perfil" name="telefone" value="<?= $telefone_usuario ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="cpf-perfil" class="block text-sm font-medium text-gray-700">CPF</label>
                        <input type="text" id="cpf-perfil" name="cpf" value="<?= $cpf_usuario ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="senha-perfil" class="block text-sm font-medium text-gray-700">Senha</label>
                        <input type="password" id="senha-perfil" name="senha" autocomplete="new-password" oninput="validarConfirmacaoSenha()"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="conf-senha-perfil" class="block text-sm font-medium text-gray-700">Confirmar Senha</label>
                        <input type="password" id="conf-senha-perfil" name="conf_senha" oninput="validarConfirmacaoSenha()"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="atendimento-perfil" class="block text-sm font-medium text-gray-700">Atendimento</label>
                        <select name="atendimento" id="atendimento-perfil"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="Sim" <?= ($atendimento == 'Sim') ? 'selected' : '' ?>>Sim</option>
                            <option value="Não" <?= ($atendimento == 'Não') ? 'selected' : '' ?>>Não</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label for="endereco-perfil" class="block text-sm font-medium text-gray-700">Endereço</label>
                    <input type="text" id="endereco-perfil" name="endereco" value="<?= $endereco_usuario ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <div>
                        <label for="foto-usu" class="block text-sm font-medium text-gray-700">Foto</label>
                        <input type="file" name="foto" id="foto-usu" onchange="carregarImgPerfil()" class="mt-1">
                    </div>
                    <div id="divImg" class="flex-shrink-0">
                        <img src="img/perfil/<?= $foto_usuario ?: 'sem-foto.jpg' ?>" width="80" id="target-usu" class="rounded-full">
                    </div>
                </div>
                <input type="hidden" name="id" value="<?= $id_usuario ?>">
                <div class="mt-4 text-center text-red-500" id="mensagem-perfil"></div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fa-regular fa-floppy-disk mr-2"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="RelEntradas" class="fixed inset-0 hidden modal-background z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden max-w-xl w-full m-4">
            <div class="modal-header-gradient text-white p-6 rounded-t-xl flex justify-between items-center">
                <h4 class="text-xl font-bold">Relatório de Ganhos</h4>
                <button type="button" onclick="hideModal('RelEntradas')" class="text-white text-2xl">&times;</button>
            </div>
            <form method="post" action="rel/rel_entradas_class.php" target="_blank" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data Inicial</label>
                        <input type="date" name="dataInicial" id="dataInicialRel-Ent" value="<?= date('Y-m-d') ?>" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data Final</label>
                        <input type="date" name="dataFinal" id="dataFinalRel-Ent" value="<?= date('Y-m-d') ?>" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Entradas / Ganhos</label>
                        <select name="filtro" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Todas</option>
                            <option value="Produto">Produtos</option>
                            <option value="Serviço">Serviços</option>
                            <option value="Conta">Demais Ganhos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Selecionar Cliente</label>
                        <select name="cliente" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm selcli">
                            <option value="">Todos</option>
                            <?php
                            $query = $pdo->prepare("SELECT * FROM clientes WHERE id_conta = ?");
                            $query->execute([$id_conta]);
                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['id'] . '">' . $row['nome'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fa-solid fa-file-lines mr-2"></i> Gerar Relatório
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    </body>
</html>

<script>
    // Função para mostrar modal
    function showModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    // Função para esconder modal
    function hideModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // Ações do perfil e formulários
    $(document).ready(function() {
        $('#form-perfil').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "editar-perfil.php",
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.trim() === "Editado com Sucesso") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Alterado com sucesso!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        $('#mensagem-perfil').text(response);
                    }
                },
                cache: false,
                contentType: false,
                processData: false,
            });
        });

        // Toggle do menu lateral para mobile
        $('#showLeftPush').on('click', function() {
            $('body').toggleClass('sidebar-open');
        });
    });

    function carregarImgPerfil() {
        var target = document.getElementById('target-usu');
        var file = document.querySelector("#foto-usu").files[0];
        if (file) {
            var reader = new FileReader();
            reader.onloadend = function() {
                target.src = reader.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>