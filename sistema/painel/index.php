<?php
@session_start();
// ini_set('display_errors', 0); // Descomente em produção
// error_reporting(0);

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../');
    exit();
}

$id_conta = $_SESSION['id_conta'];
$id_usuario = $_SESSION['id_usuario'];

require_once("verificar.php");
require_once("../conexao.php");

// Lógica de Paginação
$pag_inicial = 'home';
$pag = $_GET['pag'] ?? $pag_inicial;
if (@$_SESSION['nivel_usuario'] != 'administrador' && $pag == 'home') {
    $pag = 'agenda';
}

// ========================================================================
// INÍCIO DO CÓDIGO PHP INTEGRADO DO CABEÇALHO ANTIGO
// ========================================================================

// Dados do Usuário Logado
$query_user = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id_usuario AND id_conta = :id_conta");
$query_user->execute([':id_usuario' => $id_usuario, ':id_conta' => $id_conta]);
$user_data = $query_user->fetch(PDO::FETCH_ASSOC);

$nome_usuario = $user_data['nome'] ?? '';
$nivel_usuario = $user_data['nivel'] ?? '';
$foto_usuario = $user_data['foto'] ?? 'sem-foto.jpg';
$cliente_stripe = $user_data['cliente_stripe'] ?? null; // Adicionado para lógica do botão de assinatura

// Datas
$data_atual = date('Y-m-d');
$dataMesInicial = date('m');
$dataDiaInicial = date('d');

// Notificações - Agendamentos e Encaixes
if (@$_SESSION['nivel_usuario'] == 'administrador') {
    $query_agendamentos = $pdo->prepare("SELECT a.*, c.nome as cliente_nome, s.nome as servico_nome FROM agendamentos a LEFT JOIN clientes c ON a.cliente = c.id LEFT JOIN servicos s ON a.servico = s.id WHERE a.data = CURDATE() AND a.status = 'Agendado' AND a.id_conta = :id_conta");
    $query_agendamentos->execute([':id_conta' => $id_conta]);
    $res_agendamentos = $query_agendamentos->fetchAll(PDO::FETCH_ASSOC);
    $total_agendamentos_hoje_usuario_pendentes = count($res_agendamentos);
    $link_ag = 'agendamentos';

    $query_encaixes = $pdo->prepare("SELECT * FROM encaixe WHERE data = CURDATE() AND id_conta = :id_conta");
    $query_encaixes->execute([':id_conta' => $id_conta]);
    $total_encaixes_hoje = $query_encaixes->rowCount();
} else {
    $query_agendamentos = $pdo->prepare("SELECT a.*, c.nome as cliente_nome, s.nome as servico_nome FROM agendamentos a LEFT JOIN clientes c ON a.cliente = c.id LEFT JOIN servicos s ON a.servico = s.id WHERE a.data = CURDATE() AND a.funcionario = :id_usuario AND a.status = 'Agendado' AND a.id_conta = :id_conta");
    $query_agendamentos->execute([':id_usuario' => $id_usuario, ':id_conta' => $id_conta]);
    $res_agendamentos = $query_agendamentos->fetchAll(PDO::FETCH_ASSOC);
    $total_agendamentos_hoje_usuario_pendentes = count($res_agendamentos);
    $link_ag = 'agenda';

    $query_encaixes = $pdo->prepare("SELECT * FROM encaixe WHERE data = CURDATE() AND profissional = :id_usuario AND id_conta = :id_conta");
    $query_encaixes->execute([':id_usuario' => $id_usuario, ':id_conta' => $id_conta]);
    $total_encaixes_hoje = $query_encaixes->rowCount();
}

// Notificações - Aniversariantes e Comentários (Apenas Admin)
$total_aniversariantes_hoje = 0;
$res_aniversariantes = [];
$total_comentarios = 0;
$res_comentarios = [];

if (@$_SESSION['nivel_usuario'] == 'administrador') {
    $query_aniv = $pdo->prepare("SELECT id, nome, telefone FROM clientes WHERE MONTH(data_nasc) = :mes AND DAY(data_nasc) = :dia AND id_conta = :id_conta ORDER BY nome ASC");
    $query_aniv->execute([':mes' => $dataMesInicial, ':dia' => $dataDiaInicial, ':id_conta' => $id_conta]);
    $res_aniversariantes = $query_aniv->fetchAll(PDO::FETCH_ASSOC);
    $total_aniversariantes_hoje = count($res_aniversariantes);

    $query_coment = $pdo->prepare("SELECT nome FROM comentarios WHERE ativo != 'Sim' AND id_conta = :id_conta");
    $query_coment->execute([':id_conta' => $id_conta]);
    $res_comentarios = $query_coment->fetchAll(PDO::FETCH_ASSOC);
    $total_comentarios = count($res_comentarios);
}

// Status do WhatsApp
$whatsapp_status = '';
$whatsapp_color = 'text-gray-400';
require __DIR__ . '../../../ajax/status.php'; // Este script deve definir as variáveis $status e $cor
if (isset($status) && isset($cor)) {
    $whatsapp_status = $status;
    $whatsapp_color = ($cor == 'green') ? 'text-green-500' : 'text-red-500';
}
// ========================================================================
// FIM DO CÓDIGO PHP INTEGRADO
// ========================================================================
?>
<!DOCTYPE html>
<html lang="pt-br" class="dark">
<head>
    <title><?= $nome_sistema ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../../images/favicon<?= $id_conta ?>.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(156, 163, 175, 0.5); border-radius: 4px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(107, 114, 128, 0.5); }
        .custom-scrollbar::-webkit-scrollbar-track { background-color: transparent; }
        .modal-background { background-color: rgba(0, 0, 0, 0.6); }
        [x-cloak] { display: none !important; }

        /* Estilos do Select2 para integração com o Tailwind */
        .select2-container .select2-selection--single { height: 42px !important; border-radius: 0.375rem !important; border: 1px solid #d1d5db !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px !important; padding-left: 0.75rem !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px !important; right: 0.5rem !important; }
        .select2-dropdown { border-radius: 0.375rem !important; border: 1px solid #d1d5db !important; }
    </style>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body class="bg-gray-100 dark:bg-slate-900 flex min-h-screen" 
      x-data="{ sidebarOpen: window.innerWidth >= 1024 }" 
      x-init="$watch('sidebarOpen', value => { 
          setTimeout(() => { 
              window.dispatchEvent(new Event('resize')); 
          }, 310); 
      })">

    <!-- Sidebar -->
    <div x-show="sidebarOpen" @click.away="sidebarOpen = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 w-64 bg-slate-800 text-white flex flex-col z-50 transform lg:translate-x-0">
        <!-- Sidebar content... -->
        <div class="flex items-center justify-center p-5 bg-slate-900">
            <a href="index.php" class="flex items-center space-x-3">
                <img src="../../images/icone_512.png" alt="Logo" class="w-10 h-10">
                <div class="flex flex-col">
                    <span class="text-xl font-bold text-white">Painel</span>
                    <span class="text-xs text-slate-400 font-medium tracking-wide"><?= $nome_sistema ?></span>
                </div>
            </a>
        </div>
        <nav class="flex-1 overflow-y-auto custom-scrollbar p-4">
            <ul class="space-y-2">
                <?php if (@$_SESSION['nivel_usuario'] == 'administrador'): ?>
                <li x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full p-2 rounded-lg hover:bg-slate-700 transition">
                        <span><i class="fa fa-pencil w-6 mr-2"></i> Dashboards</span>
                        <i class="fa fa-chevron-down transition-transform" :class="{'rotate-180': open}"></i>
                    </button>
					<ul x-show="open" x-transition class="pl-8 mt-1 space-y-1">
						<li class="block p-2 text-sm rounded-lg hover:bg-slate-600"><a href="index.php"></i>Financeiro</a></li>
						<li class="block p-2 text-sm rounded-lg hover:bg-slate-600"><a href="grafico_dias"></i>Agendamentos Mês</a></li>
						<li class="block p-2 text-sm rounded-lg hover:bg-slate-600"><a href="grafico_ano"></i>Agendamentos Ano</a></li>
		

					</ul>
				</li>

                <li><a href="caixa" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition"><i class="fas fa-cash-register w-6 mr-2"></i> Abrir Caixa</a></li>
                <li><a href="agendamentos" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition"><i class="fa fa-clock w-6 mr-2"></i> Agendamentos</a></li>
                
                <li x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full p-2 rounded-lg hover:bg-slate-700 transition">
                        <span><i class="fa fa-pencil w-6 mr-2"></i> Cadastros</span>
                        <i class="fa fa-chevron-down transition-transform" :class="{'rotate-180': open}"></i>
                    </button>
                    <ul x-show="open" x-transition class="pl-8 mt-1 space-y-1">
                        <li><a href="clientes" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Clientes</a></li>
                        <?php if($plano == '2'): ?><li><a href="funcionarios" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Profissionais</a></li><?php endif; ?>
                        <li><a href="fornecedores" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Fornecedores</a></li>
                        <li><a href="servicos" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Serviços</a></li>
                        <li><a href="cupons" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Cupons</a></li>
                    </ul>
                </li>
                <li x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full p-2 rounded-lg hover:bg-slate-700 transition">
                        <span><i class="fa fa-pencil w-6 mr-2"></i> Produtos</span>
                        <i class="fa fa-chevron-down transition-transform" :class="{'rotate-180': open}"></i>
                    </button>
                    <ul x-show="open" x-transition class="pl-8 mt-1 space-y-1">
                        <li><a href="vendas" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Vendas de Produtos</a></li>
                        <li><a href="compras" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Compras de Produtos</a></li>
                        <li><a href="estoque" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Estoque Baixo</a></li>
                        <li><a href="saidas" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Saídas</a></li>
                        <li><a href="entradas" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Entradas</a></li>
                    </ul>
                </li>
                <li x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full p-2 rounded-lg hover:bg-slate-700 transition">
                        <span><i class="fa fa-pencil w-6 mr-2"></i> Clube do Assinante</span>
                        <i class="fa fa-chevron-down transition-transform" :class="{'rotate-180': open}"></i>
                    </button>
                    <ul x-show="open" x-transition class="pl-8 mt-1 space-y-1">
                        <li><a href="assinantes" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Assinantes</a></li>
                        <li><a href="conf_planos" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Configuração</a></li>                        
                    </ul>
                </li>
                <li x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full p-2 rounded-lg hover:bg-slate-700 transition">
                        <span><i class="fa fa-pencil w-6 mr-2"></i> Financeiro</span>
                        <i class="fa fa-chevron-down transition-transform" :class="{'rotate-180': open}"></i>
                    </button>
                    <ul x-show="open" x-transition class="pl-8 mt-1 space-y-1">
                        <li><a href="pagar" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Contas à Pagar</a></li>
                        <li><a href="receber" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Contas à Receber</a></li>
                        <li><a href="comissoes" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Comissões</a></li>                        
                    </ul>
                </li>        

                <li x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full p-2 rounded-lg hover:bg-slate-700 transition">
                        <span><i class="fa fa-pencil w-6 mr-2"></i> Relatórios</span>
                        <i class="fa fa-chevron-down transition-transform" :class="{'rotate-180': open}"></i>
                    </button>
                    <ul x-show="open" x-transition class="pl-8 mt-1 space-y-1">
                        <li><a href="rel/rel_produtos_class.php" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Relatório de Produtos</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#RelEntradas" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Entradas / Ganhos</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#RelSaidas" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Saídas / Despesas</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#RelComissoes" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Relatório de Comissões</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#RelCon" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Relatório de Contas</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#RelServicos" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Relatório de Serviços</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#RelAniv" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Relatório de Aniversáriantes</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#RelLucro" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Demonstrativo de Lucro</a></li>
                    </ul>
                </li>
                <li><a href="whatsapp" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition"><i class="fas fa--whatsapp w-6 mr-2"></i> WhatsApp</a></li>
                <li><a href="campanhas" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition"><i class="fa fa-paper-plane w-6 mr-2"></i> Campanhas</a></li>
                <li><a href="#" onclick="showModal('modalSeuLink')" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition"><i class="fa fa-link w-6 mr-2"></i> Seu Link</a></li>
                <li><a href="comentarios" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition"><i class="fa fa-comments w-6 mr-2"></i> Comentários</a></li>
                <li x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full p-2 rounded-lg hover:bg-slate-700 transition">
                        <span><i class="fa fa-pencil w-6 mr-2"></i> Meus Horário / Dias</span>
                        <i class="fa fa-chevron-down transition-transform" :class="{'rotate-180': open}"></i>
                    </button>
                    <ul x-show="open" x-transition class="pl-8 mt-1 space-y-1">
                        <li><a href="diasr" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Horários / Dias</a></li>
                        <li><a href="dias_bloqueio_func" class="block p-2 text-sm rounded-lg hover:bg-slate-600">Bloqueio de Dias</a></li>                                             
                    </ul>
                </li>

                <?php endif; ?>

                <?php if ($atendimento == 'Sim'): ?>
                <li class="pt-4 mt-2 border-t border-slate-700">
                    <span class="px-2 text-xs font-bold text-slate-400 uppercase">Menu Profissional</span>
                </li>
                <li><a href="agenda" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition"><i class="fa fa-calendar-o w-6 mr-2"></i> Minha Agenda</a></li>
                <li><a href="servicos_func" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition"><i class="fa fa-server w-6 mr-2"></i> Meus Serviços</a></li>                
                <li><a href="minhas_comissoes" class="flex items-center p-2 rounded-lg hover:bg-slate-700 transition"><i class="fa fa-dollar-sign w-6 mr-2"></i> Minhas Comissões</a></li>
                <?php endif; ?>
            </ul>       
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col transition-all duration-300" :class="{'lg:ml-64': sidebarOpen}">
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-40 p-4 flex items-center justify-between">
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-700 rounded-lg lg:hidden">
                <i class="fa fa-bars text-xl"></i>
            </button>
            <div class="hidden lg:block"></div> <!-- Spacer -->

            <div class="flex items-center space-x-5">
                
                <!-- Dropdown Agendamentos -->
                <div class="relative" x-data="{ open: false }">
                    <button @click.stop="open = !open" class="relative text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400" title="Agendamentos hoje">
                        <i class="fas fa-calendar-check text-xl"></i>
                        <?php if ($total_agendamentos_hoje_usuario_pendentes > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?= $total_agendamentos_hoje_usuario_pendentes ?></span>
                        <?php endif; ?>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-700 rounded-lg shadow-lg z-20">
                        <div class="p-3 border-b border-gray-200 dark:border-slate-600">
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200"><?= $total_agendamentos_hoje_usuario_pendentes ?> Agendamentos Pendentes</h3>
                        </div>
                        <div class="max-h-64 overflow-y-auto custom-scrollbar">
                            <?php if(empty($res_agendamentos)): ?>
                                <p class="text-center text-gray-500 dark:text-gray-400 p-4">Nenhum agendamento pendente.</p>
                            <?php else: ?>
                                <?php foreach($res_agendamentos as $agendamento): ?>
                                    <div class="p-3 border-b border-gray-100 dark:border-slate-600">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            <span class="font-bold"><?= date("H:i", strtotime($agendamento['hora'])) ?></span> - <?= htmlspecialchars($agendamento['cliente_nome'] ?? 'Sem Cliente') ?>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($agendamento['servico_nome'] ?? 'Não Lançado') ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <a href="<?= $link_ag ?>" class="block text-center p-2 bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-600 text-sm font-medium text-blue-600 dark:text-blue-400 rounded-b-lg">Ver Todos</a>
                    </div>
                </div>

                <!-- Dropdown Encaixes -->
                <div class="relative" x-data="{ open: false }">
                    <button @click.stop="open = !open" class="relative text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400" title="Encaixes hoje">
                        <i class="fa fa-bell text-xl"></i>
                        <?php if ($total_encaixes_hoje > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?= $total_encaixes_hoje ?></span>
                        <?php endif; ?>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white dark:bg-slate-700 rounded-lg shadow-lg z-20">
                         <div class="p-3 border-b border-gray-200 dark:border-slate-600">
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200"><?= $total_encaixes_hoje ?> Encaixes Aguardando</h3>
                        </div>
                        <a href="index.php?pag=home#encaixes-hoje" @click="open = false" class="block text-center p-2 bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-600 text-sm font-medium text-blue-600 dark:text-blue-400 rounded-b-lg">Ver na Dashboard</a>
                    </div>
                </div>

                <?php if (@$_SESSION['nivel_usuario'] == 'administrador'): ?>
                <!-- Dropdown Aniversariantes -->
                <div class="relative" x-data="{ open: false }">
                    <button @click.stop="open = !open" class="relative text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400" title="Aniversariantes de hoje">
                        <i class="fa fa-birthday-cake text-xl"></i>
                         <?php if ($total_aniversariantes_hoje > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-green-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?= $total_aniversariantes_hoje ?></span>
                        <?php endif; ?>
                    </button>
                     <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-700 rounded-lg shadow-lg z-20">
                        <div class="p-3 border-b border-gray-200 dark:border-slate-600">
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200"><?= $total_aniversariantes_hoje ?> Aniversariando Hoje</h3>
                        </div>
                        <div class="max-h-64 overflow-y-auto custom-scrollbar">
                            <?php if(empty($res_aniversariantes)): ?>
                                <p class="text-center text-gray-500 dark:text-gray-400 p-4">Nenhum aniversariante hoje.</p>
                            <?php else: ?>
                                <?php foreach($res_aniversariantes as $aniv): ?>
                                    <div class="p-3 border-b border-gray-100 dark:border-slate-600">
                                        <p class="text-sm text-gray-700 dark:text-gray-300 font-semibold"><?= htmlspecialchars($aniv['nome']) ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($aniv['telefone']) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="p-2 bg-gray-50 dark:bg-slate-800 rounded-b-lg">
                           <?php if ($total_aniversariantes_hoje > 0): ?>
                            <button onclick="showModal('birthdayModal')" @click="open = false" class="w-full text-center p-2 bg-blue-500 text-white hover:bg-blue-600 text-sm font-medium rounded-md">Enviar Parabéns</button>
                           <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Dropdown Comentários -->
                <div class="relative" x-data="{ open: false }">
                    <button @click.stop="open = !open" class="relative text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400" title="Depoimentos pendentes">
                        <i class="fa fa-comment text-xl"></i>
                        <?php if ($total_comentarios > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-blue-700 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?= $total_comentarios ?></span>
                        <?php endif; ?>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-700 rounded-lg shadow-lg z-20">
                        <div class="p-3 border-b border-gray-200 dark:border-slate-600">
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200"><?= $total_comentarios ?> Depoimentos Pendentes</h3>
                        </div>
                        <div class="max-h-64 overflow-y-auto custom-scrollbar">
                             <?php if(empty($res_comentarios)): ?>
                                <p class="text-center text-gray-500 dark:text-gray-400 p-4">Nenhum depoimento pendente.</p>
                             <?php else: ?>
                                 <?php foreach($res_comentarios as $coment): ?>
                                    <div class="p-3 border-b border-gray-100 dark:border-slate-600">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">Cliente: <span class="font-semibold"><?= htmlspecialchars($coment['nome']) ?></span></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <a href="comentarios" class="block text-center p-2 bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-600 text-sm font-medium text-blue-600 dark:text-blue-400 rounded-b-lg">Ver Comentários</a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Ícones de Status e Tema -->
                <i class="fab fa-whatsapp text-2xl <?= $whatsapp_color ?>" title="Status WhatsApp: <?= $whatsapp_status ?>"></i>
                
                <!-- Perfil Dropdown -->
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click.stop="open = !open" class="flex items-center space-x-2 focus:outline-none">
                        <img src="img/perfil/<?= htmlspecialchars($foto_usuario) ?>" alt="Foto" class="w-10 h-10 rounded-full object-cover">
                        <div class="hidden md:flex flex-col items-start">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($nome_usuario) ?></span>
                            <span class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($nivel_usuario) ?></span>
                        </div>
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-700 rounded-lg shadow-lg py-2 z-20">
                        <a href="#" onclick="showModal('modalPerfil')" class="flex items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600"><i class="fa fa-suitcase w-6 mr-2 text-gray-500 dark:text-gray-400"></i>Editar Perfil</a>
                        <?php if(@$_SESSION['nivel_usuario'] == 'administrador'): ?>
                        <a href="configuracoes" class="flex items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600"><i class="fa fa-cog w-6 mr-2 text-gray-500 dark:text-gray-400"></i>Config. Sistema</a>
                        <?php endif; ?>
                        <a href="conf_site" class="flex items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600"><i class="fa fa-link w-6 mr-2 text-gray-500 dark:text-gray-400"></i>Config. Site</a>
                        
                        <?php if(@$_SESSION['nivel_usuario'] == 'administrador' && $cliente_stripe == null): ?>
                            <a href="#" onclick="showModal('assinaturaModal')" class="flex items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600"><i class="fa fa-dollar-sign w-6 mr-2 text-gray-500 dark:text-gray-400"></i>Minha Assinatura</a>
                        <?php else: ?>
                            <a href="../../portal.php" target="_blank" class="flex items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600"><i class="fa fa-dollar-sign w-6 mr-2 text-gray-500 dark:text-gray-400"></i>Sua Assinatura</a>
                        <?php endif; ?>

                        <div class="border-t border-gray-200 dark:border-slate-600 my-1"></div>
                        <a href="logout.php" class="flex items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600"><i class="fa fa-sign-out w-6 mr-2 text-gray-500 dark:text-gray-400"></i>Sair</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 overflow-y-auto">
            <?php require_once("paginas/" . $pag . '.php'); ?>
        </main>
        
        <?php if ($caixa_aberto): ?>
        <a href="caixa" title="Caixa Aberto" class="fixed bottom-6 right-6 bg-green-500 text-white px-5 py-3 rounded-full shadow-lg hover:bg-green-600 transition flex items-center space-x-2">
            <i class="fas fa-cash-register text-xl"></i>
            <span class="font-semibold">Caixa Aberto</span>
        </a>
        <?php endif; ?>
    </div>
    
    <!-- MODAIS AQUI (perfil, aniversário, etc) -->
    <!-- Modal para Aniversariantes -->
    <div id="birthdayModal" class="fixed inset-0 hidden modal-background z-50 items-center justify-center p-4" x-data="{ present: 'Não' }" x-cloak>
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl overflow-hidden max-w-lg w-full">
            <div class="p-5 border-b dark:border-slate-700 flex justify-between items-center">
                <h5 class="text-xl font-bold text-gray-800 dark:text-gray-200">Aniversariantes do Dia</h5>
                <button onclick="hideModal('birthdayModal')" class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="oferecer_presente" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Oferecer Presente?</label>
                    <select id="oferecer_presente" x-model="present" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-800 dark:text-gray-200 shadow-sm">
                        <option value="Não">Não</option>
                        <option value="Sim">Sim</option>
                    </select>
                </div>
                <div x-show="present === 'Sim'" x-transition>
                    <label for="id_cupom" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecionar Cupom</label>
                    <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-800 dark:text-gray-200 shadow-sm" id="id_cupom">
                        <option value="">Selecione um cupom</option>
                        <?php
                        $query_cupons = $pdo->prepare("SELECT id, codigo, valor, tipo_desconto FROM cupons WHERE id_conta = ? AND data_validade >= CURDATE() AND (usos_atuais < max_usos OR max_usos = 0)");
                        $query_cupons->execute([$id_conta]);
                        foreach ($query_cupons->fetchAll(PDO::FETCH_ASSOC) as $cupom) {
                            $desconto = $cupom['tipo_desconto'] === 'porcentagem' ? "{$cupom['valor']}%" : "R$" . number_format($cupom['valor'], 2, ',', '.');
                            echo "<option value=\"{$cupom['id']}\">{$cupom['codigo']} ({$desconto})</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="max-h-48 overflow-y-auto custom-scrollbar border rounded-md dark:border-slate-700">
                     <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-700 dark:text-gray-300">
                           <tr><th class="px-4 py-2">Nome</th><th class="px-4 py-2">Selecionar</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($res_aniversariantes as $aniversariante): ?>
                            <tr class="bg-white dark:bg-slate-800 border-b dark:border-slate-700">
                                <td class="px-4 py-2 font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($aniversariante['nome']); ?></td>
                                <td class="px-4 py-2"><input type="checkbox" class="aniversariante-checkbox" value="<?= $aniversariante['id']; ?>" checked></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-4 bg-gray-50 dark:bg-slate-900 flex justify-end space-x-3">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300" onclick="hideModal('birthdayModal')">Fechar</button>
                <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" id="enviar_mensagens_aniversario" <?= empty($res_aniversariantes) ? 'disabled' : ''; ?>>Enviar Mensagens</button>
            </div>
        </div>
    </div>
    
    <div id="modalPerfil" class="fixed inset-0 hidden modal-background z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden max-w-2xl w-full">
            <div class="modal-header-gradient text-white p-5 flex justify-between items-center">
                <h4 class="text-xl font-bold">Editar Perfil</h4>
                <button onclick="hideModal('modalPerfil')" class="text-white text-2xl">&times;</button>
            </div>
            <form id="form-perfil" class="p-6 max-h-[80vh] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" name="nome" value="<?= htmlspecialchars($nome_usuario) ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($email_usuario) ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telefone</label>
                        <input type="text" id="telefone-perfil" name="telefone" value="<?= htmlspecialchars($telefone_usuario) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">CPF</label>
                        <input type="text" id="cpf-perfil" name="cpf" value="<?= htmlspecialchars($cpf_usuario) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nova Senha</label>
                        <input type="password" id="senha-perfil" name="senha" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Confirmar Senha</label>
                        <input type="password" id="conf-senha-perfil" name="conf_senha" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                         <label class="block text-sm font-medium text-gray-700">Atendimento</label>
                         <select name="atendimento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="Sim" <?= ($atendimento == 'Sim') ? 'selected' : '' ?>>Sim</option>
                            <option value="Não" <?= ($atendimento == 'Não') ? 'selected' : '' ?>>Não</option>
                         </select>
                    </div>
                </div>
                <div class="mt-4">
                     <label class="block text-sm font-medium text-gray-700">Endereço</label>
                     <input type="text" name="endereco" value="<?= htmlspecialchars($endereco_usuario) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                 <div class="mt-4 flex items-center justify-between">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Foto</label>
                        <input type="file" name="foto" id="foto-usu" onchange="carregarImgPerfil()" class="mt-1">
                    </div>
                    <img src="img/perfil/<?= htmlspecialchars($foto_usuario) ?>" width="80" height="80" id="target-usu" class="rounded-full object-cover">
                </div>
                <input type="hidden" name="id" value="<?= $id_usuario ?>">
                <div id="mensagem-perfil" class="mt-4 text-center text-red-500"></div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-5 rounded-lg hover:bg-blue-700 transition">Salvar</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="RelEntradas" class="fixed inset-0 hidden modal-background z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden max-w-xl w-full">
            <div class="modal-header-gradient text-white p-5 flex justify-between items-center">
                <h4 class="text-xl font-bold">Relatório de Ganhos</h4>
                <button onclick="hideModal('RelEntradas')" class="text-white text-2xl">&times;</button>
            </div>
            <form method="post" action="rel/rel_entradas_class.php" target="_blank" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Data Inicial</label>
                        <input type="date" name="dataInicial" value="<?= date('Y-m-d') ?>" required class="mt-1 block w-full rounded-md border-gray-300">
                    </div>
                     <div>
                        <label class="block text-sm font-medium">Data Final</label>
                        <input type="date" name="dataFinal" value="<?= date('Y-m-d') ?>" required class="mt-1 block w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Filtro</label>
                        <select name="filtro" class="mt-1 block w-full rounded-md border-gray-300">
                            <option value="">Todas</option>
                            <option value="Produto">Produtos</option>
                            <option value="Serviço">Serviços</option>
                            <option value="Conta">Demais Ganhos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Cliente</label>
                        <select name="cliente" class="mt-1 block w-full rounded-md border-gray-300 selcli">
                            <option value="">Todos</option>
                            <?php
                            $q = $pdo->query("SELECT id, nome FROM clientes WHERE id_conta = '$id_conta' ORDER BY nome ASC");
                            foreach($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['nome']).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                 <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-5 rounded-lg hover:bg-blue-700 transition">Gerar Relatório</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalSeuLink" class="fixed inset-0 hidden modal-background z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden max-w-md w-full">
            <div class="modal-header-gradient text-white p-5 flex justify-between items-center">
                <h5 class="text-xl font-bold">Compartilhe Seu Link</h5>
                <button onclick="hideModal('modalSeuLink')" class="text-white text-2xl">&times;</button>
            </div>
            <div class="p-6 text-center">
                 <p class="text-gray-600 mb-6">Escolha a melhor forma de compartilhar seu link com seus clientes.</p>
                 <div class="space-y-3">
                    <button onclick="copiarLink()" class="w-full bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg hover:bg-gray-300 transition flex items-center justify-center space-x-2">
                        <i class="fas fa-copy"></i>
                        <span>Copiar Link</span>
                    </button>
                    <button onclick="enviarLink()" class="w-full bg-green-500 text-white font-semibold py-3 px-4 rounded-lg hover:bg-green-600 transition flex items-center justify-center space-x-2">
                         <i class="fab fa-whatsapp"></i>
                        <span>Enviar por WhatsApp</span>
                    </button>
                     <button onclick="mostrarQRCode()" class="w-full bg-gray-800 text-white font-semibold py-3 px-4 rounded-lg hover:bg-gray-900 transition flex items-center justify-center space-x-2">
                         <i class="fas fa-qrcode"></i>
                        <span>Exibir QR Code</span>
                    </button>
                 </div>
                 <div id="qrcode-container" class="mt-6 hidden">
                     <div id="qrcode" class="p-2 bg-white inline-block rounded-lg shadow-md"></div>
                 </div>
            </div>
        </div>
    </div>
    
</body>
</html>

<script>
    // Global Modal Functions
    function showModal(id) {
        $('#' + id).removeClass('hidden').addClass('flex').find('> div').addClass('animate-pulse-once');
        setTimeout(() => $('#' + id).find('> div').removeClass('animate-pulse-once'), 1500);
    }
    function hideModal(id) {
        $('#' + id).addClass('hidden').removeClass('flex');
    }

    // Initialize scripts
    $(document).ready(function() {
        // Masking
        $('#telefone-perfil').mask('(00) 00000-0000');
        $('#cpf-perfil').mask('000.000.000-00', {reverse: true});
        
        // Select2
        $('.selcli').select2({ dropdownParent: $('#RelEntradas') });
        // Add other select2 initializations here for other report modals
        // $('.sel15').select2({ dropdownParent: $('#RelComissoes') });

        // Form Submissions
        $('#form-perfil').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "editar-perfil.php",
                type: 'POST', data: formData,
                success: function(response) {
                    if (response.trim() === "Editado com Sucesso") {
                        Swal.fire({ icon: 'success', title: 'Salvo!', showConfirmButton: false, timer: 1500 })
                        .then(() => location.reload());
                    } else {
                        $('#mensagem-perfil').text(response);
                    }
                },
                cache: false, contentType: false, processData: false
            });
        });
    });

    // Image Preview
    function carregarImgPerfil() {
        const target = document.getElementById('target-usu');
        const file = document.querySelector("#foto-usu").files[0];
        if (file) {
            const reader = new FileReader();
            reader.onloadend = () => { target.src = reader.result; };
            reader.readAsDataURL(file);
        }
    }

    // Password confirmation
    function validarConfirmacaoSenha() {
        const senha = $("#senha-perfil").val();
        const confSenhaInput = document.getElementById("conf-senha-perfil");
        if (senha) {
            confSenhaInput.setAttribute("required", "required");
            if (senha !== confSenhaInput.value) {
                confSenhaInput.setCustomValidity("As senhas não conferem.");
            } else {
                confSenhaInput.setCustomValidity("");
            }
        } else {
            confSenhaInput.removeAttribute("required");
            confSenhaInput.setCustomValidity("");
        }
    }

    // "Seu Link" Modal Logic
    const seuLink = `https://markai.skysee.com.br/site.php?u=<?= urlencode($username) ?>`;
    const mensagemWhatsApp = encodeURIComponent(`Confira nosso link para agendamentos: ${seuLink}`);

    function copiarLink() {
        navigator.clipboard.writeText(seuLink).then(() => {
            Swal.fire({ icon: 'success', title: 'Link Copiado!', timer: 1500, showConfirmButton: false });
        });
    }

    function enviarLink() {
        window.open(`https://api.whatsapp.com/send?text=${mensagemWhatsApp}`, '_blank');
    }

    function mostrarQRCode() {
        const container = $('#qrcode-container');
        const qrcodeEl = $('#qrcode');
        qrcodeEl.html('');
        new QRCode(qrcodeEl[0], { text: seuLink, width: 200, height: 200 });
        container.slideDown();
    }
</script>