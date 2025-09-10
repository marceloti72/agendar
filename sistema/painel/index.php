<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];

require_once("verificar.php");
require_once("../conexao.php");

$pag_inicial = 'home';

$id_usuario = $_SESSION['id_usuario'];

//VERIFICA O STATUS DO WHATSAPP	
require __DIR__ . '../../../ajax/status.php'; 

?>
<style>
	@media (max-width: 768px) {
	.relatorio {
		display: flex;
		width: 100%;
		height: 30px;
		margin-bottom: 10px;
		font-size: 14px;
		align-items: center;
		justify-content: center;
			
        }
	}
</style>
<?php 

$query = $pdo->query("SELECT * from usuarios where id = '$id_usuario' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	$nome_usuario = $res[0]['nome'];
	$email_usuario = $res[0]['email'];
	$cpf_usuario = $res[0]['cpf'];
	$senha_usuario = $res[0]['senha'];
	$nivel_usuario = $res[0]['nivel'];
	$telefone_usuario = $res[0]['telefone'];
	$endereco_usuario = $res[0]['endereco'];
	$foto_usuario = $res[0]['foto'];
	$atendimento = $res[0]['atendimento'];
	$intervalo_horarios = $res[0]['intervalo'];
}

// if(@$_SESSION['nivel_usuario'] != 'administrador'){
// 	require_once("verificar-permissoes.php");
// }


if(@$_GET['pag'] == ""){
	$pag = $pag_inicial;

	if(@$_SESSION['nivel_usuario'] != 'administrador'){
	    $pag = 'agenda';
    }
}else{
	$pag = $_GET['pag'];
}




$data_atual = date('Y-m-d');
$mes_atual = Date('m');
$ano_atual = Date('Y');
$data_mes = $ano_atual."-".$mes_atual."-01";
$data_ano = $ano_atual."-01-01";


$partesInicial = explode('-', $data_atual);
$dataDiaInicial = $partesInicial[2];
$dataMesInicial = $partesInicial[1];



$query = $pdo->query("SELECT plano from config where id = '$id_conta'");
$res3 = $query->fetch(PDO::FETCH_ASSOC);
$plano = $res3['plano'];


// VERIFICAR SE O CAIXA ESTÁ ABERTO
$query_caixa = $pdo->query("SELECT * FROM caixa WHERE id_conta = '$id_conta' ORDER BY id DESC LIMIT 1");
$res_caixa = $query_caixa->fetchAll(PDO::FETCH_ASSOC);
$caixa_aberto = false;
if (@count($res_caixa) > 0 && $res_caixa[0]['data_fechamento'] === NULL) {
    $caixa_aberto = true;
}

?>

<!DOCTYPE HTML>
<html lang="pt-br">
<head>
	<title><?php echo $nome_sistema ?></title>
	<link rel="icon" type="image/png" href="../../images/favicon<?php echo $id_conta?>.png">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="" />
	<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>



	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.css" rel='stylesheet' type='text/css' />

	<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"> -->

	
	<!-- Custom CSS -->
	<link href="css/style.css" rel='stylesheet' type='text/css' />

	<!-- font-awesome icons CSS -->
	<link href="css/font-awesome.css" rel="stylesheet"> 

	<link href="../../css/icons.css" rel="stylesheet">
	<!-- //font-awesome icons CSS-->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">

	<!-- Incluindo Font Awesome via CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

	<!-- side nav css file -->
	<!-- <link href='css/SidebarNav.min2.css' media='all' rel='stylesheet' type='text/css'/> -->
	<link href='css/SidebarNav.min.css' media='all' rel='stylesheet' type='text/css' id="theme-stylesheet"/>
	<!-- //side nav css file -->

	<link rel="stylesheet" href="css/monthly.css">

	<!-- js-->
	<script src="js/jquery-1.11.1.min.js"></script>
	<script src="js/modernizr.custom.js"></script>

	<!--webfonts-->
	<link href="//fonts.googleapis.com/css?family=PT+Sans:400,400i,700,700i&amp;subset=cyrillic,cyrillic-ext,latin-ext" rel="stylesheet">
	<!--//webfonts--> 

	<!-- chart -->
	<script src="js/Chart.js"></script>
	<!-- //chart -->

	<!-- Metis Menu -->
	<script src="js/metisMenu.min.js"></script>
	<script src="js/custom.js"></script>
	<link href="css/custom.css" rel="stylesheet">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
	<!--//Metis Menu -->
	<style>
		#chartdiv {
			width: 100%;
			height: 295px;
		}

		
        .hovv:hover { transform: scale(3); transition: .5s;}
        .hovv{cursor: pointer; border-radius: 50px;object-fit: cover;} 

		.hovv{
    width: 50px;
    height: 50px;
  }

  .novo {
		background-color: #e99f35;
		color: white;
		font-weight: bold;
	}

	html {
    scroll-behavior: smooth;
}

	@media (max-width: 768px) {
	.novo {
		display: flex;
		width: 100%;
		height: 30px;
		margin-bottom: 10px;
		font-size: 14px;
		align-items: center;
		justify-content: center;
			
        }
	}

  .modal-header{	
	/* background-image: linear-gradient(to bottom, #d4a0e9, #a0d4e9);
	background-image: linear-gradient(to right, #6a85b6, #bac8e0);
	background-image: linear-gradient(to right, #ff9966, #ff5e62);
	background-image: linear-gradient(to right, #434371, #9669a0); */
	background-image: linear-gradient(to right, #6a85b6, #bac8e0);
	color: white;
	text-transform: uppercase;
  }

  @keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(2); /* Aumenta o tamanho */
        opacity: 0.7;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.icon-pulse {
    display: inline-block; /* Necessário para transform funcionar bem */
    animation: pulse 1.5s infinite ease-in-out; /* Aplica a animação */
    /* Ajuste a duração (1.5s) e o timing (ease-in-out) como desejar */
}

@keyframes wiggle-whatsapp {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(5deg); }  /* Inclina para a direita */
    75% { transform: rotate(-5deg); } /* Inclina para a esquerda */
}

.icon-wiggle-whatsapp {
    display: inline-block;
    animation: wiggle-whatsapp 1s infinite ease-in-out;
    transform-origin: bottom center; /* Define o ponto de rotação */
}

.navbar-header {
    /* Cor de fundo opcional como fallback */
    background-color: #516a88;
    /* Imagem de fundo */
    background-image: url('../../images/icone_512.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    /* Centraliza o conteúdo verticalmente */
    display: flex;
    align-items: center;
    padding: 3px 15px;	
}

.navbar-brand {
    /* Garante que o link não tenha sublinhado e ocupa o espaço necessário */
    padding: 0;
    height: auto;
    display: block;
}

.brand-container {
    /* Alinha o logo e o texto um ao lado do outro */
    display: flex;
    align-items: center;
}


.brand-text {
    /* Estilo para o texto */
    color: white;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
    line-height: 1.2;
	margin-top: 38px;
}

.system-name {
    /* Estilo para o nome do sistema */
    font-size: 0.4em;
    font-style: italic;
    color: #e0e0e0;
}

/* Estilo para os ícones da barra de navegação */
.navbar-toggle .icon-bar {
    background-color: white; /* Cor dos ícones para contraste */
}


  
  @media (max-width: 768px) {
    .sticky-header {
        padding: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: nowrap;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .header-left, .header-right {
        margin: 0;
        padding: 0;
        flex-shrink: 1;
    }
    #showLeftPush {
        font-size: 16px;
        padding: 4px;
        margin-right: 5px;
        line-height: 1;
        width: 24px; /* Simétrico */
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .nofitications-dropdown {
        display: flex;
        align-items: center;
        margin: 0;
        padding: 0;
    }
    .nofitications-dropdown li {
        margin-right: 5px;
        padding: 0;
        width: 30px; /* Largura fixa igual à altura */
        height: 30px; /* Altura fixa igual à largura */
        display: flex; /* Garante que o conteúdo interno se ajuste */
        align-items: center;
        justify-content: center;
        
    }
    .nofitications-dropdown li a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        padding: 0; /* Remove padding para controle total */
    }
    .nofitications-dropdown li a i {
        font-size: 16px; /* Tamanho do ícone */
        width: 16px; /* Largura fixa */
        height: 16px; /* Altura fixa */
        line-height: 16px;
        text-align: center;
    }
    .badge {
        font-size: 8px;
        padding: 2px 4px;
        top: -8px;
        right: -4px; /* Ajusta posição para não interferir na simetria */
        position: absolute;
        border-radius: 50%;
        min-width: 12px;
        height: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .dropdown-menu {
        font-size: 10px;
        max-width: 120px;
        padding: 2px;
    }
    .profile_details {
        margin-left: 110px;
        flex-shrink: 1;
		
    }

	
    .prfil-img img {
        width: 40px;
        height: 40px;
        margin-left: -50px;
        border-radius: 50%;
		
    }
    .user-name {
        display: none;
    }
    .fa-angle-down, .fa-angle-up {
        font-size: 12px;
        margin-left: 2px;
        width: 12px;
        height: 12px;
        line-height: 12px;
        text-align: center;
    }
    .dropdown-menu.drp-mnu {
        font-size: 10px;
        min-width: 80px;
        padding: 2px;
		margin-left: -110px !important;
    }
    .profile_details_drop a {
        display: flex;
        align-items: center;
        padding: 0;
    }
    /* Sobrescreve conflitos */
    .header-left, .header-right, .nofitications-dropdown, .profile_details {
        float: none !important;
        display: inline-flex !important;
    }

	.dropdown-toggle {
        width: 34px !important; /* Largura igual à altura para ser redondo */
        height: 34px !important;
        border-radius: 50%; /* Forma redonda */      
        
        text-decoration: none;
        
    }
	.foto_user {        
		margin-right: -30px;        
        
    }
	
}

.list-group-item-action {
    cursor: pointer;
    transition: background-color 0.3s;
}
.list-group-item-action:hover {
    background-color: #e9ecef;
}
.list-group-item-action i {
    color: #4682B4;
}
#qrcode-container {
    display: none;
}
#qrcode {
    margin: 0 auto;
}
.btn-primary {
    background-color: #4682B4;
    border: none;
    transition: all 0.3s;
}
.btn-primary:hover {
    background-color: #5a9bd4;
}

.theme-switcher-container {
    padding: 12px 15px;
    display: flex;
    align-items: center;
    justify-content: flex-start; /* Alinha os itens à esquerda */
    font-size: 14px;
    color: #f1f1f1;
}

.theme-label-text {
    margin-right: 10px;
}

.switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
    margin-right: 10px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
}

input:checked + .slider {
    background-color: #2196F3;
}

input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
    -webkit-transform: translateX(20px);
    -ms-transform: translateX(20px);
    transform: translateX(20px);
}

/* Arredonda os sliders */
.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}

.btn-floating {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    padding: 12px 20px;
    border-radius: 50px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-floating:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    background-color: #218838; /* Cor mais escura ao passar o mouse */
}

.btn-floating i {
    font-size: 18px;
}




/* --- Configuração geral do corpo da página --- */
body {
    font-family: 'Poppins', sans-serif; /* Aplica a nova fonte */
    background-color: #f4f7f6; /* Um fundo cinza bem claro */
}

/*
=========================================
ESTILOS DO MENU LATERAL (SIDEBAR)
=========================================
*/

.sidebar-left {
    background-color: #ffffff; /* Fundo branco para um visual limpo */
    border-right: 1px solid #e0e0e0; /* Borda direita sutil */
    box-shadow: 2px 0 15px rgba(0, 0, 0, 0.05); /* Sombra suave para dar profundidade */
}

/* --- Itens do Menu --- */
.sidebar-menu > li > a {
    color: #555; /* Cor de texto mais suave que o preto puro */
    font-weight: 500;
    padding: 15px 20px; /* Mais espaço interno para os links */
    border-left: 4px solid transparent; /* Borda à esquerda para indicar item ativo/hover */
    transition: all 0.3s ease; /* Transição suave para efeitos */
}

.sidebar-menu > li > a i {
    color: #6a85b6; /* Cor de destaque para os ícones */
    margin-right: 10px; /* Espaço entre o ícone e o texto */
    width: 20px; /* Garante alinhamento dos ícones */
    text-align: center;
}

/* --- Efeito ao passar o mouse (Hover) e no item ativo --- */
.sidebar-menu > li:hover > a,
.sidebar-menu > li.active > a {
    background-color: #f0f4f7; /* Fundo levemente azulado ao passar o mouse */
    color: #1a2a44; /* Cor mais escura para o texto */
    border-left-color: #6a85b6; /* Mostra a borda de destaque */
}

/* --- Título de Seção (Ex: MENU DO PROFISSIONAL) --- */
.sidebar-menu .header {
    background-color: #f9f9f9;
    color: #888;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: uppercase;
    padding: 10px 20px;
}

/* --- Submenus (Dropdowns) --- */
.treeview-menu {
    background-color: #f8f9fa; /* Fundo levemente diferente para submenus */
    padding-left: 25px; /* Indentação para criar hierarquia visual */
}

.treeview-menu > li > a {
    color: #666;
    padding: 10px 15px;
    font-weight: 400;
}

.treeview-menu > li:hover > a {
    color: #1a2a44;
}

/* --- Barra de Rolagem Customizada --- */
.sidebar-left::-webkit-scrollbar {
  width: 5px;
}
.sidebar-left::-webkit-scrollbar-track {
  background: #f1f1f1;
}
.sidebar-left::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 5px;
}
.sidebar-left::-webkit-scrollbar-thumb:hover {
  background: #aaa;
}

/*
=========================================
MODO ESCURO (DARK MODE)
=========================================
*/
body.dark .sidebar-left {
    background-color: #2c3e50; /* Azul escuro para o fundo do menu */
    border-right-color: #34495e;
    box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
}

body.dark .sidebar-menu > li > a {
    color: #bdc3c7; /* Cor de texto cinza claro */
}

body.dark .sidebar-menu > li > a i {
    color: #8e9eab; /* Cor dos ícones no modo escuro */
}

body.dark .sidebar-menu > li:hover > a,
body.dark .sidebar-menu > li.active > a {
    background-color: #34495e;
    color: #ffffff;
    border-left-color: #3498db; /* Azul mais vivo para o destaque */
}

body.dark .sidebar-menu .header {
    background-color: #34495e;
    color: #95a5a6;
}

body.dark .treeview-menu {
    background-color: #233140;
}

body.dark .treeview-menu > li > a {
    color: #bdc3c7;
}


:root {
    --sidebar-width-open: 260px;
    --sidebar-width-collapsed: 80px;
}

/* #page-wrapper, .sticky-header {
    margin-left: var(--sidebar-width-open);
    transition: margin-left 0.3s ease-in-out;
} */

.cbp-spmenu-left {
    width: var(--sidebar-width-open);
    transition: width 0.3s ease-in-out;
}

#showLeftPush {
    position: absolute;
    top: 15px;
    right: -50px; /* Posiciona o botão fora da barra lateral */
    background: #fff;
    border: 1px solid #ddd;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: #555;
    font-size: 1.2em;
    cursor: pointer;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: right 0.3s ease-in-out, transform 0.3s ease;
}

#showLeftPush:hover {
    background: #f5f5f5;
}

/*
=========================================
CSS CORRIGIDO E MAIS ESPECÍFICO
=========================================
*/

/* --- Movimentação do conteúdo principal --- */


/* --- Largura do Menu --- */
body.cbp-spmenu-push .cbp-spmenu-left {
    width: var(--sidebar-width-open);
    transition: width 0.3s ease-in-out;
}

body.cbp-spmenu-push.sidebar-collapsed .cbp-spmenu-left {
    width: var(--sidebar-width-collapsed) !important; /* !important para garantir a sobreposição */
}

/* --- Esconder textos e ajustar ícones no modo recolhido --- */
body.sidebar-collapsed .sidebar-menu span,
body.sidebar-collapsed .sidebar-menu .pull-right,
body.sidebar-collapsed .sidebar-menu .header {
    opacity: 0;
    visibility: hidden;
    width: 0;
    transition: all 0.1s ease;
}

body.sidebar-collapsed .sidebar-menu > li > a {
    justify-content: center;
}

body.sidebar-collapsed .treeview-menu {
    display: none !important;
}

/* Tooltip (dica de ferramenta) */
body.sidebar-collapsed .sidebar-menu > li {
    position: relative;
}
body.sidebar-collapsed .sidebar-menu > li > a::after {
    content: attr(data-tooltip);
    position: absolute;
    left: calc(var(--sidebar-width-collapsed) - 10px);
    top: 50%;
    transform: translateY(-50%);
    background-color: #333;
    color: #fff;
    padding: 6px 12px;
    border-radius: 4px;
    white-space: nowrap;
    font-size: 13px;
    margin-left: 15px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease;
    pointer-events: none;
    z-index: 1100;
}
body.sidebar-collapsed .sidebar-menu > li:hover > a::after {
    opacity: 1;
    visibility: visible;
}

/* Manter o estilo do botão que já estava funcionando */
#showLeftPush {
    position: absolute;
    top: 15px;
    right: -20px;
    background: #ffffff;
    border: 1px solid #e0e0e0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: #555;
    cursor: pointer;
    z-index: 1050;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease-in-out;
}
#showLeftPush i { transition: transform 0.3s ease; }
body.sidebar-collapsed #showLeftPush i { transform: rotate(180deg); }


	</style>
	<!--pie-chart --><!-- index page sales reviews visitors pie chart -->
	<script src="js/pie-chart.js" type="text/javascript"></script>
	<script type="text/javascript">

		$(document).ready(function () {
			$('#demo-pie-1').pieChart({
				barColor: '#2dde98',
				trackColor: '#eee',
				lineCap: 'round',
				lineWidth: 8,
				onStep: function (from, to, percent) {
					$(this.element).find('.pie-value').text(Math.round(percent) + '%');
				}
			});

			$('#demo-pie-2').pieChart({
				barColor: '#8e43e7',
				trackColor: '#eee',
				lineCap: 'butt',
				lineWidth: 8,
				onStep: function (from, to, percent) {
					$(this.element).find('.pie-value').text(Math.round(percent) + '%');
				}
			});

			$('#demo-pie-3').pieChart({
				barColor: '#e32424',
				trackColor: '#eee',
				lineCap: 'square',
				lineWidth: 8,
				onStep: function (from, to, percent) {
					$(this.element).find('.pie-value').text(Math.round(percent) + '%');
				}
			});


		});

	</script>
	<!-- //pie-chart --><!-- index page sales reviews visitors pie chart -->


	<link rel="stylesheet" type="text/css" href="DataTables/datatables.min.css"/>
 	<script type="text/javascript" src="DataTables/datatables.min.js"></script>


	
</head> 
<body class="cbp-spmenu-push dark" >
	<div class="main-content">



		<div class="cbp-spmenu cbp-spmenu-vertical cbp-spmenu-left"  id="cbp-spmenu-s1" >
			<!--left-fixed -navigation-->
			<aside class="sidebar-left" style="overflow: scroll; height:100%; scrollbar-width: thin;">
				<nav class="navbar navbar-inverse" >
					<div class="navbar-header">						
						<a class="navbar-brand" href="index.php">
							<div class="brand-container">
								
								<div class="brand-text">
									
									<span class="system-name"><?php echo $nome_sistema ?></span>
								</div>
							</div>
						</a>
					</div>
					
					
					
					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						
						<ul class="sidebar-menu">	
							

							<?php 
                        // DEFINANDO SE É ADMINISTRADOR
							if(@$_SESSION['nivel_usuario'] == 'administrador'){
							?>
							
							<li class="treeview <?php echo @$home ?>">
								<a href="#" data-tooltip="Dashboards">
									<i class="fa fa-dashboard"></i>
									<span>Dashboards</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">
									<li class="<?php echo @$home ?>"><a href="index.php"></i>Financeiro</a></li>
									<li class="<?php echo @$home ?>"><a href="grafico_dias"></i>Agendamentos Mês</a></li>
									<li class="<?php echo @$home ?>"><a href="grafico_ano"></i>Agendamentos Ano</a></li>
					

								</ul>
							</li>

							<li class="treeview">
								<a href="caixa" data-tooltip="Abrir Caixa">
								<i class="fas fa-cash-register me-2"></i> <span>  Abrir Caixa</span>
								</a>
							</li>

							<li class="treeview <?php echo @$menu_agendamentos ?>">
								<a href="agendamentos" data-tooltip="Agendamentos">
								<i class="fe fe-clock"></i> <span> Agendamentos</span>
								</a>
							</li>

							<li class="treeview <?php echo @$menu_cadastros ?>" >
								<a href="#">
									<i class="fa fa-pencil"></i>
									<span>Cadastros</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">
								<li class="<?php echo @$clientes ?>"><a href="clientes"></i>Clientes</a></li>

								<?php 
								if($plano == '2'){
								?>
														 

								<li class="<?php echo @$funcionarios ?>"><a href="funcionarios"></i>Profissionais</a></li>								
								<?php 
								}?>								
								
									<li class="<?php echo @$fornecedores ?>"><a href="fornecedores"></i>Fornecedores</a></li>									

										<li class="<?php echo @$servicos ?>"><a href="servicos"></i>Serviços</a></li>

										<li class="<?php echo @$servicos ?>"><a href="cupons"></i>Cupons de Desconto</a></li>										
								
								</ul>
							</li>							

							<li class="treeview <?php echo @$menu_servicos ?>">							

								<li class="treeview <?php echo @$menu_produtos ?>">
								<a href="#">
								    <i class="fa fa-tags"></i>
									<span>Produtos</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">

									<li class="<?php echo @$produtos ?>"><a href="produtos"></i>Produtos</a></li>

									
									 <li class="<?php echo @$vendas ?>"><a href="vendas"></i>Vendas de Produtos</a></li>

									<li class="<?php echo @$compras ?>"><a href="compras"></i>Compras de Produtos</a></li>
									
									<li class="<?php echo @$estoque ?>"><a href="estoque"></i>Estoque Baixo</a></li>

									<li class="<?php echo @$saidas ?>"><a href="saidas"></i>Saídas</a></li>

									<li class="<?php echo @$entradas ?>"><a href="entradas"></i>Entradas</a></li>
								
								</ul>
							</li>	
							
							<!-- <li class="treeview <?= @$marketing ?>">
                                <a href="#" data-toggle="modal" data-target="#modalAssinaturas">
                                    <i class="fa fa-paper-plane"></i><span>Assinaturas</span>
                                </a>
                             </li>	 -->

							 <?php 
							 if($assinaturas2 == 'Sim'){?>
								<li class="treeview <?php echo @$assinaturas3 ?>" >
									<a href="#">
									<i class="fas fa-crown"></i>
										<span>Clube do Assinante</span>
										<i class="fa fa-angle-left pull-right"></i>
									</a>
									<ul class="treeview-menu">

										<li class="<?php echo @$vendas ?>"><a href="assinantes"></i>Assinantes</a></li>

										<li class="<?php echo @$compras ?>"><a href="conf_planos"></i>Configuração</a></li>			
																		
									
									</ul>
								</li>
							<?php 
							 }?>



							<li class="treeview <?php echo @$menu_financeiro ?>" >
								<a href="#">
									<i class="fa fa-usd"></i>
									<span>Financeiro</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">									
									
									<li class="<?php echo @$pagar ?>"><a href="pagar"></i>Contas à Pagar</a></li>

									<li class="<?php echo @$receber ?>"><a href="receber"></i>Contas à Receber</a></li>	

									<li class="<?php echo @$comissoes ?>"><a href="comissoes"></i>Comissões</a></li>									
								
								</ul>
							</li>

							<li class="treeview <?php echo @$menu_relatorio ?>" >
								<a href="#">
									<i class="fa fa-file-pdf-o"></i>
									<span>Relatórios</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">

									<li class="<?php echo @$rel_produtos ?>"><a href="rel/rel_produtos_class.php" target="_blank"></i>Relatório de Produtos</a></li>

									<li class="<?php echo @$rel_entradas ?>"><a href="#" data-toggle="modal" data-target="#RelEntradas"></i>Entradas / Ganhos</a></li>

									<li class="<?php echo @$rel_saidas ?>"><a href="#" data-toggle="modal" data-target="#RelSaidas"></i>Saídas / Despesas</a></li>

									<li class="<?php echo @$rel_comissoes ?>"><a href="#" data-toggle="modal" data-target="#RelComissoes"></i>Relatório de Comissões</a></li>

									<li class="<?php echo @$rel_contas ?>"><a href="#" data-toggle="modal" data-target="#RelCon"></i>Relatório de Contas</a></li>


									<li class="<?php echo @$rel_servicos ?>"><a href="#" data-toggle="modal" data-target="#RelServicos"></i>Relatório de Serviços</a></li>


									<li class="<?php echo @$rel_aniv ?>"><a href="#" data-toggle="modal" data-target="#RelAniv"></i>Relatório de Aniversáriantes</a></li>


									<li class="<?php echo @$rel_lucro ?>"><a href="#" data-toggle="modal" data-target="#RelLucro"></i>Demonstrativo de Lucro</a></li>	
															
								</ul>
							</li>

							<!-- <li class="treeview <?php echo @$clientes_retorno ?>">
                                <a href="clientes_retorno">
                                    <i class="fa fa-bell"></i><span>Clientes Retornos</span>
                                </a>
                             </li>	 -->


							<li class="treeview <?= @$whatsapp?>">
								<a href="#">
									<i class="fab fa-whatsapp"></i>
									<span> Whatsapp</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">

									<li class="treeview"><a href="whatsapp"><i class="fa fa-cog"></i>Configurações</a></li>								
								
									
																		
								
								</ul>
							</li>
                         

                            <li class="treeview <?= @$marketing ?>">
                                <a href="campanhas">
                                    <i class="fa fa-paper-plane"></i><span>Campanha de retorno</span>
                                </a>
                             </li> 

                            <li class="treeview">
								<a href="#" data-toggle="modal" data-target="#modalSeuLink">
									<i class="fa fa-link"></i><span> Seu Link</span>
								</a>
							</li>					
                            
								<!-- <li class="treeview <?php echo @$calendario ?>">
								<a href="calendario">
									<i class="fa fa-calendar-o"></i> <span>Calendário</span>
								</a>
							</li> -->

							
                            <li class="treeview <?= @$menu_site ?>">
                                <a href="comentarios">
                                    <i class="fa fa-comments"></i><span> Comentários</span>
                                </a>
                             </li>							                      
							<?php 
					        }
							?>

							<?php if(@$atendimento == 'Sim'){?>

								<li class="header">MENU DO PROFISSIONAL</li>
							<li class="treeview <?php echo @$minha_agenda ?>">
								<a href="agenda">
									<i class="fa fa-calendar-o"></i> <span>Minha Agenda</span>
								</a>
							</li>	
							
							<!-- <li class="treeview  <?php echo @$meus_servicos ?>">
								<a href="#">
									<i class="fa fa-server"></i>
									<span>Meus Serviços</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">
							
									<li><a href="meus_servicos"></i> <span>Serviços</span></a>
									</li>	

									<li><a href="servicos_func"></i>Ativar Serviços</a></li>
									</ul>
							</li>	 -->
							<li class="treeview  <?php echo @$minhas_comissoes ?>">
								<a href="servicos_func">
								<i class="fa fa-server"></i> <span>Meus Serviços</span>
								</a>
							</li>	


							<li class="treeview  <?php echo @$minhas_comissoes ?>">
								<a href="minhas_comissoes">
								<i class="fa fa-dollar-sign"></i> <span>Minhas Comissões</span>
								</a>
							</li>						


							<li class="treeview  <?php echo @$meus_dias ?>">
								<a href="#">
									<i class="fa fa-clock"></i>
									<span>Meus Horário / Dias</span>
									
								</a>
								<ul class="treeview-menu">

									<li><a href="dias"></i>Horários / Dias</a></li>

									<li><a href="dias_bloqueio_func"></i>Bloqueio de Dias</a></li>
																		
								
								</ul>
							</li>							

							<?php } ?>						


						</ul>
					</div>
					<!-- /.navbar-collapse -->
					 
				</nav>
			</aside>
		</div>
		<!--left-fixed -navigation-->




		<!-- header-starts -->
		<div class="sticky-header header-section ">
			
			<div class="header-left">
				<!--toggle button start-->
				<button id="showLeftPush" data-toggle="collapse" data-target=".collapse"><i class="fa fa-bars"></i></button>
						
				
				<div class="profile_details_left"><!--notifications of menu start -->
					<ul class="nofitications-dropdown">						

						<?php
						$id_conta = $_SESSION['id_conta'];
						if(@$_SESSION['nivel_usuario'] == 'administrador'){ 
							$query = $pdo->query("SELECT * FROM agendamentos where data = curDate() and status = 'Agendado' and id_conta = '$id_conta'");
							$res = $query->fetchAll(PDO::FETCH_ASSOC);
							$total_agendamentos_hoje_usuario_pendentes = @count($res);
							$link_ag = 'agendamentos';

							$query = $pdo->query("SELECT * FROM encaixe where data = curDate() and id_conta = '$id_conta'");
							$res_encaixes = $query->fetchAll(PDO::FETCH_ASSOC);
							$total_encaixes_hoje = @count($res_encaixes);
							
						}else{
							$query = $pdo->query("SELECT * FROM agendamentos where data = curDate() and funcionario = '$id_usuario' and status = 'Agendado' and id_conta = '$id_conta'");
							$res = $query->fetchAll(PDO::FETCH_ASSOC);
							$total_agendamentos_hoje_usuario_pendentes = @count($res);
							$link_ag = 'agenda';

							$query = $pdo->query("SELECT * FROM encaixe where data = curDate() and profissional = '$id_usuario' and id_conta = '$id_conta'");
							$res_encaixes = $query->fetchAll(PDO::FETCH_ASSOC);
							$total_encaixes_hoje = @count($res_encaixes);

						}
						if($total_agendamentos_hoje_usuario_pendentes != 0){
							$icon2 = 'icon-wiggle-whatsapp';					
						}else{
							$icon2='';
						}
						?>

						<li class="dropdown head-dpdn">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fas fa-calendar-check" title="Agendamentos hoje"></i>
							<?php 								
								if($total_agendamentos_hoje_usuario_pendentes != 0){							
									?>
                                    <span class="badge" style="background: #46c261ff"><?php echo $total_agendamentos_hoje_usuario_pendentes ?></span><?php 
								}?>
							</a>
							<ul class="dropdown-menu">
								<li>
									<div class="notification_header" align="center">
										<h3><?php echo $total_agendamentos_hoje_usuario_pendentes ?> Agendamento Pendente Hoje</h3>
									</div>
								</li>

								<?php 
								for($i=0; $i < @count($res); $i++){
									foreach ($res[$i] as $key => $value){}
								$id = $res[$i]['id'];								
								$cliente = $res[$i]['cliente'];
								$hora = $res[$i]['hora'];
								$servico = $res[$i]['servico'];
								$horaF = date("H:i", strtotime($hora));


									$query2 = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta'");
									$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
									if(@count($res2) > 0){
										$nome_serv = $res2[0]['nome'];
										$valor_serv = $res2[0]['valor'];
									}else{
										$nome_serv = 'Não Lançado';
										$valor_serv = '';
									}


									$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente' and id_conta = '$id_conta'");
									$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
									if(@count($res2) > 0){
										$nome_cliente = $res2[0]['nome'];
									}else{
										$nome_cliente = 'Sem Cliente';
									}
								 ?>
								<li>									
									<div class="notification_desc">
										<p><b><?php echo $horaF ?> </b> - <?php echo $nome_cliente ?> / <?php echo $nome_serv ?></p>
										<p><span></span></p>
									</div>
									<div class="clearfix"></div>	
								</li>
								<?php 
							}
								 ?>
								
								
							
								<li>
									<div class="notification_bottom" style="background: #ffe8e6">
										<a href="<?php echo $link_ag?>">Ver Agendamentos</a>
									</div> 
								</li>
							</ul>
						</li>	

						<li class="dropdown head-dpdn">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fas fa-bell" title="Encaixes hoje"></i>
							<?php 								
								if($total_encaixes_hoje != 0){							
									?>
                                    <span class="badge" style="background: #46c261ff"><?php echo $total_encaixes_hoje ?></span><?php 
								}?>
							</a>
							<ul class="dropdown-menu">
								<li>
									<div class="notification_header" align="center">					
											<h3><?php echo $total_encaixes_hoje ?> Encaixes Hoje Aguardando</h3>
									</div>
								</li>				
							
								<li>
									<div class="notification_bottom" style="background: #ffe8e6">
										<a href="#encaixes-hoje">Ver Encaixes</a>
									</div> 
								</li>
							</ul>
						</li>	




                <?php 
                // DEFINANDO SE É ADMINISTRADOR
				if(@$_SESSION['nivel_usuario'] == 'administrador'){
				?>
				 	<?php if(@$rel_aniv == ''){ 

						//totalizando aniversariantes do dia
						$query = $pdo->query("SELECT * FROM clientes where month(data_nasc) = '$dataMesInicial' and day(data_nasc) = '$dataDiaInicial' and id_conta = '$id_conta' order by data_nasc asc, id asc");
						$res = $query->fetchAll(PDO::FETCH_ASSOC);
						$total_aniversariantes_hoje = @count($res);

						if($total_aniversariantes_hoje != 0){
							$icon3 = 'icon-wiggle-whatsapp';					
						}else{
							$icon3='';
						}?>

						<li class="dropdown head-dpdn">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Aniversariantes de hoje"><i class="fas fa-birthday-cake <?php echo $icon3?>" ></i>
							<?php 								
								if($total_aniversariantes_hoje != 0){?>
                                    <span class="badge" style="background: #46c261ff"><?php echo $total_aniversariantes_hoje ?></span><?php 
								}?>
							</a>
							<ul class="dropdown-menu">
								<li>
									<div class="notification_header" align="center">
										<h3><?php echo $total_aniversariantes_hoje ?> Aniversariando Hoje</h3>
									</div>
								</li>

								<?php 
								for($i=0; $i < @count($res); $i++){
									foreach ($res[$i] as $key => $value){}
								
								$nome = $res[$i]['nome'];	
								$telefone = $res[$i]['telefone'];														

								 ?>
								<li>									
									<div class="notification_desc">
										<p><b><?php echo $nome ?> </b> - <?php echo $telefone ?> </p>
										<p><span></span></p>
									</div>
									<div class="clearfix"></div>	
								</li>
								<?php 
							}
								 ?>							
							
								<li>
									<div class="notification_bottom" style="background: #d9ffe1">
										<a href="#" data-toggle="modal" data-target="#RelAniv">Relatório Aniversáriantes</a>
									</div> 
									<?php if ($total_aniversariantes_hoje > 0): ?>
										<a href="#" class="notification_bottom" style="background: #46c261ff; color: white" data-toggle="modal" data-target="#birthdayModal">
											Enviar Parabéns
										</a>
									<?php endif; ?>
								</li>
							</ul>
						</li>	
					<?php } ?>

					<?php if(@$comentarios == ''){ 

						//totalizando aniversariantes do dia
						$query = $pdo->query("SELECT * FROM comentarios where ativo != 'Sim' and id_conta = '$id_conta'");
						$res = $query->fetchAll(PDO::FETCH_ASSOC);
						$total_comentarios = @count($res);

						if($total_comentarios != 0){
							$icon5 = 'icon-wiggle-whatsapp';					
						}else{
							$icon5='';
						}

							?>
						<li class="dropdown head-dpdn">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Depoimentos pendentes"><i class="fa fa-comment <?php echo $icon5?>" ></i><?php 
							if($total_comentarios != 0){?>
                                    <span class="badge" style="background: #46c261ff"><?php echo $total_comentarios ?></span><?php 
								}?>
							</a>
							<ul class="dropdown-menu">
								<li>
									<div class="notification_header" align="center">
										<h3><?php echo $total_comentarios ?> Depoimentos Pendente</h3>
									</div>
								</li>

								<?php 
								for($i=0; $i < @count($res); $i++){
									foreach ($res[$i] as $key => $value){}
								
								$nome = $res[$i]['nome'];
																					

								 ?>
								<li>									
									<div class="notification_desc">
										<p><b>Cliente: <?php echo $nome ?> </b> </p>
										<p><span></span></p>
									</div>
									<div class="clearfix"></div>	
								</li>
								<?php 
							}
								 ?>
								
								
							
								<li>
									<div class="notification_bottom" style="background: #d8d4fc">
										<a href="comentarios">Ver Depoimentos</a>
									</div> 
								</li>
								
							</ul>
						</li>	
					<?php }

					?>
					    <li class="dropdown head-dpdn">							
								<a href="#" class="dropdown-toggle" title="Escolher tema do sistema" ><i class="fas fa-sun" id="theme-icon"></i>						
						</li>

                        <li class="dropdown head-dpdn" style="margin-left: 20px; color: <?php echo $cor?>" title='<?php echo $status?>'><small><i class="fab fa-whatsapp fa-2x"></i></small></li>
						
					</ul>
					<?php 
				}
				?>
					<div class="clearfix"> </div>
				</div>
				
				
			</div>
			<div class="header-right">
				
				
				
				
				<div class="profile_details">		
					<ul>
						<li class="dropdown profile_details_drop">
							<a href="#" class="dropdown-toggle foto_user" data-toggle="dropdown" aria-expanded="false">
								<div class="profile_img">	
									<span class="prfil-img"><img src="img/perfil/<?php if(!empty($foto_usuario)){ echo $foto_usuario;}else{?>sem-foto.jpg<?php }?>" alt="" width="50" height="50"> </span> 
									<div class="user-name esc">
										<p><?php echo $nome_usuario ?></p>
										<span id="nome_usuario"><?php echo $nivel_usuario ?></span>
									</div>
									<i class="fa fa-angle-down lnr"></i>
									<i class="fa fa-angle-up lnr"></i>
									<div class="clearfix"></div>	
								</div>	
							</a>
						
							<ul class="dropdown-menu drp-mnu">
						<?php 
						// DEFINANDO SE É ADMINISTRADOR
						if(@$_SESSION['nivel_usuario'] == 'administrador'){
						?>
								<?php if(@$configuracoes == ''){ ?>
								<li> <a href="configuracoes" ><i class="fa fa-cog"></i> Config. Sistema</a> </li> 	
								<?php } }?>
								<li> <a href="conf_site" ><i class="fa fa-link"></i></i> Config.Seu Site</a> </li>
								

								<li> <a href="" data-toggle="modal" data-target="#modalPerfil"><i class="fa fa-suitcase"></i> Editar Perfil</a> </li> 
						<?php 
						// DEFINANDO SE É ADMINISTRADOR
						if(@$_SESSION['nivel_usuario'] == 'administrador' && $cliente_stripe == null){
						?>
								<li> <a href="" data-toggle="modal" data-target="#assinaturaModal"><i class="fa fa-dollar"></i> Minha Assinatura</a> </li> 
								<!-- <li> <a href="" data-toggle="modal" data-target="#tutoriaisModal"><i class="fa fa-question" style="color: #15b283;"></i> Vídeos Tutoriais</a> </li>  -->
						<?php }else{?>
							<li> <a href="../../portal.php" target="_blank" ><i class="fa fa-dollar"></i> Sua Assinatura</a> </li><?php 
						}
						
						?>
						
								<li> <a href="logout.php"><i class="fa fa-sign-out"></i> Sair</a> </li>
							</ul>
						</li>
					</ul>
				</div>
				<div class="clearfix"> </div>				
			</div>
			<div class="clearfix"> </div>	
		</div>
		<!-- //header-ends -->
		
		








		<!-- main content start-->
		<div id="page-wrapper">
			<?php require_once("paginas/".$pag.'.php') ?>
		</div>

		<!-- Botão Flutuante Caixa Aberto -->
		<?php if ($caixa_aberto): ?>
			<a href="caixa" class="btn btn-success btn-floating" title="Caixa Aberto">
				<i class="fas fa-cash-register"></i> Caixa Aberto
			</a>
		<?php endif; ?>









		<!--footer-->
		<div class="footer">
			<p> Desenvolvidor Por Skysee Soluções em TI <a href="https://www.skysee.com.br/" target="_blank">www.skysee.com.br.com.br</a></p>		
		</div>
		<!--//footer-->
	</div>




	<!-- Classie --><!-- for toggle left push menu script -->
		<script src="js/classie.js"></script>
		<script>
			var menuLeft = document.getElementById( 'cbp-spmenu-s1' ),
				showLeftPush = document.getElementById( 'showLeftPush' ),
				body = document.body;
				
			showLeftPush.onclick = function() {
				classie.toggle( this, 'active' );
				classie.toggle( body, 'cbp-spmenu-push-toright' );
				classie.toggle( menuLeft, 'cbp-spmenu-open' );
				disableOther( 'showLeftPush' );
			};
			

			function disableOther( button ) {
				if( button !== 'showLeftPush' ) {
					classie.toggle( showLeftPush, 'disabled' );
				}
			}


		showLeftPush2 = document.getElementById( 'showLeftPush2' ),
		
		showLeftPush2.onclick = function() {
			classie.toggle( this, 'active' );
			classie.toggle( body, 'cbp-spmenu-push-toright' );
			classie.toggle( menuLeft, 'cbp-spmenu-open' );
			disableOther2( 'showLeftPush2' );
		};


		function disableOther2( button ) {
			if( button !== 'showLeftPush2' ) {
				classie.toggle( showLeftPush2, 'disabled' );
			}
		}

		</script>
	<!-- //Classie --><!-- //for toggle left push menu script -->


	<!--scrolling js-->
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script> -->
	<!-- <script src="js/jquery.nicescroll.js"></script> -->
	<script src="js/scripts.js"></script>
	<!--//scrolling js-->
	
	<!-- side nav js -->
	<script src='js/SidebarNav.min.js' type='text/javascript'></script>
	<script>
		$('.sidebar-menu').SidebarNav()
	</script>
	<!-- //side nav js -->
	
	
	
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.js"> </script>
	<!-- //Bootstrap Core JavaScript -->
	
</body>
</html>





<!-- Mascaras JS -->
<script type="text/javascript" src="js/mascaras.js"></script>

<!-- Ajax para funcionar Mascaras JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script> 




<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>

<style type="text/css">
		.select2-selection__rendered {
			line-height: 36px !important;
			font-size:16px !important;
			color:#666666 !important;

		}

		.select2-selection {
			height: 36px !important;
			font-size:16px !important;
			color:#666666 !important;

		}
	</style>  


<!-- Modal Perfil-->
<div class="modal fade" id="modalPerfil" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header text-white" style="background-color: #4682B4;">
				<h4 class="modal-title" id="exampleModalLabel">Editar Perfil</h4>
				<button id="btn-fechar-perfil" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			<form method="post" id="form-perfil">
				<div class="modal-body">

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Nome</label>
								<input type="text" class="form-control" id="nome-perfil" name="nome" placeholder="Nome" value="<?php echo $nome_usuario ?>" required>    
							</div> 	
						</div>
						<div class="col-md-6">

							<div class="form-group">
								<label for="exampleInputEmail1">Email</label>
								<input type="email" class="form-control" id="email-perfil" name="email" placeholder="Email" value="<?php echo $email_usuario ?>" required>    
							</div> 	
						</div>
					</div>


					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Telefone</label>
								<input type="text" class="form-control" id="telefone-perfil" name="telefone" placeholder="Telefone" value="<?php echo $telefone_usuario ?>" >    
							</div> 	
						</div>
						<div class="col-md-6">
							
							<div class="form-group">
								<label for="exampleInputEmail1">CPF</label>
								<input type="text" class="form-control" id="cpf-perfil" name="cpf" placeholder="CPF" value="<?php echo $cpf_usuario ?>">    
							</div> 	
						</div>
					</div>


					<div class="row">
					    <div class="col-md-4">
							<div class="form-group">
								<label for="senha-perfil">Senha</label>
								<input type="password" class="form-control" id="senha-perfil" name="senha" placeholder="Senha" autocomplete="new-password" oninput="validarConfirmacaoSenha()">
							</div>
							</div>
							<div class="col-md-4">
							<div class="form-group">
								<label for="conf-senha-perfil">Confirmar Senha</label>
								<input type="password" class="form-control" id="conf-senha-perfil" name="conf_senha" placeholder="Confirmar Senha" oninput="validarConfirmacaoSenha()">
							</div>
							</div>					

						<div class="col-md-4">
							<div class="form-group">
								<label for="exampleInputEmail1">Atendimento</label>
								<select class="form-control" name="atendimento" id="atendimento-perfil">
									<option <?php if($atendimento == 'Sim'){ ?> selected <?php } ?> value="Sim">Sim</option>
									<option <?php if($atendimento == 'Não'){ ?> selected <?php } ?> value="Não">Não</option>
								</select>  
							</div> 	
						</div>						

					</div>


					<div class="row">
						<div class="col-md-8">
							<div class="form-group">
								<label for="exampleInputEmail1">Endereço</label>
								<input type="text" class="form-control" id="endereco-perfil" name="endereco" placeholder="Rua X Número 1 Bairro xxx" value="<?php echo $endereco_usuario ?>" >    
							</div> 	
						</div>

							<div class="col-md-4">
							<div class="form-group">
								<label for="exampleInputEmail1">Intervalo Minutos</label>
								<input type="number" class="form-control" id="intervalo_perfil" name="intervalo" placeholder="Intervalo Horários" value="<?php echo $intervalo_horarios ?>" required>    
							</div> 	
						</div>
						
					</div>





						<div class="row">
							<div class="col-md-8">						
								<div class="form-group"> 
									<label>Foto</label> 
									<input class="form-control" type="file" name="foto" onChange="carregarImgPerfil();" id="foto-usu">
								</div>						
							</div>
							<div class="col-md-4">
								<div id="divImg">
									<img src="img/perfil/<?php if(!empty($foto_usuario)){ echo $foto_usuario;}else{?>sem-foto.jpg<?php }?>"  width="80px" id="target-usu">									
								</div>
							</div>

						</div>


					
						<input type="hidden" name="id" value="<?php echo $id_usuario ?>">

					<br>
					<small><div id="mensagem-perfil" align="center"></div></small>
				</div>
				<div class="modal-footer">      
					<button type="submit" class="btn btn-primary"><i class="fa-regular fa-floppy-disk"></i> Salvar</button>
				</div>
			</form>
		</div>
	</div>
</div>









<!-- Modal Config-->
<div class="modal fade" id="modalConfig" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header text-white" style="background-color: #4682B4;">
				<h4 class="modal-title" id="exampleModalLabel">Editar Configurações</h4>
				<button id="btn-fechar-config" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
		</div>
	</div>
</div>


<!-- Modal Principal -->
<?php
// Forçar codificação UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
ini_set('default_charset', 'UTF-8');
?>

<!-- Modal Principal -->
<div class="modal fade" id="assinaturaModal" tabindex="-1" aria-labelledby="assinaturaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #4682B4, #3a75a7); border-bottom: none; border-top-left-radius: 15px; border-top-right-radius: 15px; padding: 20px 30px;">
                <h5 class="modal-title" id="assinaturaModalLabel" style="font-size: 1.5rem; font-weight: bold;">Gerenciar Assinatura</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <?php 
            // Configurações Iniciais e Conexões
            $url_sistema = explode("//", $url);
            $host = ($url_sistema[1] == 'localhost/markai/') ? 'localhost' : 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
            $usuario = ($url_sistema[1] == 'localhost/markai/') ? 'root' : 'skysee';
            $senha = ($url_sistema[1] == 'localhost/markai/') ? '' : '9vtYvJly8PK6zHahjPUg';
            $banco = 'gestao_sistemas';

            try {
                $pdo2 = new PDO("mysql:dbname=$banco;host=$host;charset=utf8", "$usuario", "$senha");
                $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                error_log("Erro ao conectar ao banco de dados: " . $e->getMessage());
                echo 'Erro ao conectar ao banco de dados!';
            }

            // Busca informações do cliente
            $query8 = $pdo2->prepare("SELECT * FROM clientes WHERE banco = :banco and id_conta = :id_conta");
            $query8->bindValue(':id_conta', $id_conta, PDO::PARAM_INT);
            $query8->bindValue(':banco', 'barbearia');
            $query8->execute();
            $res8 = $query8->fetchAll(PDO::FETCH_ASSOC);
            $id_cliente = $res8[0]['id'];
            $instituicao = $res8[0]['instituicao'];
            if($res8[0]['plano'] === '1'){
                $plano = 'Individual';
            } else {
                $plano = 'Empresa';
            } 

            // Busca informações da fatura
            $query9 = $pdo2->prepare("SELECT id, vencimento, taxa, valor, subtotal, frequencia FROM receber WHERE pago = 'Não' AND cliente =:cliente"); 
            $query9->bindValue(':cliente', $id_cliente, PDO::PARAM_INT);
            $query9->execute();
            $res9 = $query9->fetch(PDO::FETCH_ASSOC);

            $data_venc = null;
            $valorMensal = null;
            $frequencia = null;
            $id_pg = null;
            $dias_atraso = 0;

            if ($res9) {
                $data_venc = $res9['vencimento'];
                $id_pg = $res9['id'];
                $valorMensal = $res9['valor'];
                if($res9['frequencia'] == '30'){
                    $frequencia = 'Mensal';
                } else {
                    $frequencia = 'Anual';
                }
                
                // Calcula os dias em atraso
                $data_atual = date('Y-m-d');
                $data_venc_obj = new DateTime($data_venc);
                $data_atual_obj = new DateTime($data_atual);
                if ($data_venc_obj < $data_atual_obj) {
                    $interval = $data_atual_obj->diff($data_venc_obj);
                    $dias_atraso = $interval->days;
                }
            }
            ?>

            <div class="modal-body" style="background-color: #fcfcfc; padding: 30px;">
                <div class="card border-0 shadow-sm" style="border-radius: 10px;">
                    <div class="card-body" style="padding: 25px;">
                        <div class="text-center mb-4">
                            <h3 class="card-text" style="color: #333; font-weight: 700; font-size: 1.75rem;"><?php echo mb_strtoupper($instituicao, 'UTF-8'); ?></h3>                            
                        </div>
                        <hr style="border-top: 1px solid #e0e0e0; margin: 20px 0;">
                        
                        <?php if(isset($ativo_sistema) && $ativo_sistema == 'teste'): ?>
                            <div class="alert alert-info text-center" role="alert" style="font-size: 12px; margin-bottom: 20px; padding: 10px; border-radius: 5px;">
                                *Em teste grátis
                            </div>
                        <?php endif; ?>
                        
                        <p class="card-text" style="font-weight: 600; color: #444; font-size: 1.1rem;">Detalhes da Cobrança:</p>
                        <ul class="list-group list-group-flush mb-4" style="border: 1px solid #E9ECEF; border-radius: 8px;">
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="background: #fff; padding: 12px 15px;"><b>Valor:</b> <span>R$ <?php echo $valorMensal?></span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="background: #fff; padding: 12px 15px;"><b>Plano:</b> <span><?php echo $plano?></span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="background: #fff; padding: 12px 15px;"><b>Frequência:</b> <span><?php echo $frequencia ?></span></li>
                            
                        </ul>

                        <?php 
                        if ($dias_atraso > 0) {
                            ?>
                            <div class="alert alert-danger text-center" role="alert" style="font-size: 1rem; font-weight: 500; border-radius: 8px;">
                                <strong>Vencida há:</strong> <?php echo $dias_atraso; ?> dias
                            </div>
                            <?php
                        } elseif ($data_venc) {
                            ?>
                            <div class="alert alert-success text-center" role="alert" style="font-size: 1rem; font-weight: 500; border-radius: 8px;">
                                <strong>Próximo Vencimento:</strong> <?php echo date('d/m/Y', strtotime($data_venc)); ?>
                            </div>
                            <?php
                        }
                        ?>

                        <div class="d-flex gap-2 mt-4">
                            <a href="https://www.gestao.skysee.com.br/pagar/<?php echo $id_pg?>" target="_blank" class="btn btn-primary w-100" style="background: #4682B4; border: none; font-weight: bold; padding: 12px; border-radius: 8px; transition: background-color 0.3s; box-shadow: 0 4px 10px rgba(70, 130, 180, 0.3);">Pagar Agora</a>
                            <button type="button" class="btn btn-outline-secondary w-100" data-toggle="modal" data-target="#trocarPlanoModal" style="font-weight: bold; padding: 12px; border-radius: 8px; transition: all 0.3s;">Trocar Plano</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: none; padding: 15px 30px;">
                <button type="button" class="btn btn-secondary" id="btn-fechar2" data-dismiss="modal" style="border-radius: 8px; padding: 10px 25px;">Fechar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Trocar Plano -->
<div class="modal fade" id="trocarPlanoModal" tabindex="-1" aria-labelledby="trocarPlanoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
      <div class="modal-header" style="background: linear-gradient(45deg, #4682B4, #87CEEB); border-bottom: none;">
        <h5 class="modal-title text-white" id="trocarPlanoModalLabel" style="font-size: 25px;">Trocar Plano</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;margin-top: -30px">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form id="form-troca" method="POST">
		<div class="modal-body" style="background-color: #F8F9FA; padding: 25px;">
			<div class="card border-0 shadow-sm">
			<div class="card-body" style="padding: 20px;">
				<p class="text-center mb-4" style="color: #444; font-weight: 500;">Escolha o novo plano:</p>
				<div class="form-group">
				<select class="form-control" id="novoPlano" name="novoPlano" style="border-radius: 5px;">
					<option value="individual_mensal">Individual Mensal - R$ 49,90</option>
					<option value="individual_anual">Individual Anual - R$ 526,94 12% off</option>
					<option value="empresa_mensal">Empresa Mensal - R$ 79,90</option>
					<option value="empresa_anual">Empresa Anual - R$ 786,21 18% off</option>
				</select>
				</div>
				<button type="submit" class="btn btn-success w-100 mt-3" style="background: #28A745; border: none; padding: 10px; transition: all 0.3s;">Confirmar Troca</button>
			</div>
			</div>
		</div>
		<small><div id="mensagem-troca" align="center" class="mt-3"></div></small>
		<div class="modal-footer" style="border-top: none; padding: 15px 25px;">
			<button type="button" class="btn btn-secondary" id="btn-fechar" data-dismiss="modal" style="border-radius: 5px; padding: 8px 20px;">Cancelar</button>
		</div>
		
	  </form>
    </div>
  </div>
</div>


<div class="modal fade" id="modalAssinaturas">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">...</div>
            <div class="modal-body">

                <div class="text-center btn-inserir-depoimento mb-4">
                    <a href="/caminho/para/minha_assinatura.php" class="btn btn-outline-secondary btn-minha-assinatura">
                       <i class="fas fa-user-check"></i> Minha Assinatura Atual
                    </a>
                    <hr>
                </div>

                <div class="row justify-content-center">
                    <?php
                    try {
                        // Busca os planos ativos para a conta atual, ordenados
                        $query_planos = $pdo->prepare("SELECT * FROM planos WHERE ativo = 1 AND id_conta = :id_conta ORDER BY ordem ASC, id ASC");
                        $query_planos->execute([':id_conta' => $id_conta]);
                        $planos = $query_planos->fetchAll(PDO::FETCH_ASSOC);

                        if (count($planos) > 0) {
                            foreach ($planos as $plano) {
                                $id_plano_atual = $plano['id'];
                                $nome_plano = htmlspecialchars($plano['nome']);
                                $preco_mensal_plano = number_format($plano['preco_mensal'], 2, ',', '.');
                                $imagem_plano = htmlspecialchars($plano['imagem'] ?: 'default-plano.jpg'); // Imagem padrão
                                $caminho_imagem_plano = '../../images/' . $imagem_plano; // AJUSTE O CAMINHO

                                // Busca os serviços associados a este plano
                                $query_servicos_plano = $pdo->prepare("
                                    SELECT ps.quantidade, s.nome
                                    FROM planos_servicos ps
                                    JOIN servicos s ON ps.id_servico = s.id
                                    WHERE ps.id_plano = :id_plano AND ps.id_conta = :id_conta
                                    ORDER BY s.nome ASC
                                ");
                                $query_servicos_plano->execute([':id_plano' => $id_plano_atual, ':id_conta' => $id_conta]);
                                $servicos_incluidos = $query_servicos_plano->fetchAll(PDO::FETCH_ASSOC);

                                // Determina a classe do botão (exemplo)
                                $btn_class = 'btn-primary';
                                if (strtolower($plano['nome']) == 'ouro') $btn_class = 'btn-warning';
                                if (strtolower($plano['nome']) == 'diamante') $btn_class = 'btn-dark';
                                if (strtolower($plano['nome']) == 'bronze') $btn_class = 'btn-outline-primary';


                    ?>
                                <div class="col-md-6 col-lg-5 mb-4">
                                    <div class="plano-item card h-100 shadow-sm">
                                        <img src="<?php echo $caminho_imagem_plano; ?>" class="card-img-top plano-img" style='width: 80px;' alt="Plano <?php echo $nome_plano; ?>" onerror="this.onerror=null; this.src='images/planos/default-plano.jpg';">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title text-center plano-titulo"><?php echo $nome_plano; ?></h5>
                                            <p class="plano-preco text-center text-muted">R$ <?php echo $preco_mensal_plano; ?> / mês</p>
                                            <ul class="list-unstyled mt-3 mb-4 plano-beneficios">
                                                <?php if (count($servicos_incluidos) > 0): ?>
                                                    <?php foreach ($servicos_incluidos as $servico):
                                                        $qtd_texto = '';
                                                        if ($servico['quantidade'] == 0) {
                                                            $qtd_texto = 'Ilimitado - '; // Ou só o nome do serviço
                                                        } elseif ($servico['quantidade'] > 1) {
                                                            $qtd_texto = $servico['quantidade'] . 'x ';
                                                        }
                                                    ?>
                                                        <li><i class="fas fa-check text-success mr-2"></i><?php echo $qtd_texto . htmlspecialchars($servico['nome']); ?></li>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <li><small>Nenhum serviço principal incluído neste plano.</small></li>
                                                <?php endif; ?>
                                                 </ul>
                                            <button type="button" class="btn btn-lg btn-block <?php echo $btn_class; ?> btn-assinar mt-auto" data-plano="<?php echo $id_plano_atual; ?>">Assinar <?php echo $nome_plano; ?></button>
                                        </div>
                                    </div>
                                </div>
                    <?php
                            } // Fim foreach $planos
                        } else {
                            echo '<div class="col-12"><p class="text-center text-muted">Nenhum plano de assinatura disponível no momento.</p></div>';
                        }
                    } catch (PDOException $e) {
                         error_log("Erro ao buscar planos/serviços: " . $e->getMessage());
                         echo '<div class="col-12"><p class="text-center text-danger">Erro ao carregar os planos. Tente novamente mais tarde.</p></div>';
                    }
                    ?>
                </div> </div> 
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<style>
.btn:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}

.list-group-item {
  padding: 10px 15px;
  color: #555;
}

.modal-content {
  transition: all 0.3s ease;
}
</style>











	<!-- Modal Rel Entradas / Ganhos -->
	<div class="modal fade" id="RelEntradas" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header text-white" style="background-color: #4682B4;">
					<h4 class="modal-title" id="exampleModalLabel">Relatório de Ganhos
						<small style="color: black;">(
							<a href="#" onclick="datas('1980-01-01', 'tudo-Ent', 'Ent')">
								<span style="color:#000" id="tudo-Ent">Tudo</span>
							</a> / 
							<a href="#" onclick="datas('<?php echo $data_atual ?>', 'hoje-Ent', 'Ent')">
								<span id="hoje-Ent">Hoje</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_mes ?>', 'mes-Ent', 'Ent')">
								<span style="color:#000" id="mes-Ent">Mês</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_ano ?>', 'ano-Ent', 'Ent')">
								<span style="color:#000" id="ano-Ent">Ano</span>
							</a> 
						)</small>



					</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="post" action="rel/rel_entradas_class.php" target="_blank">
					<div class="modal-body">

						<div class="row">
							<div class="col-md-6">						
								<div class="form-group"> 
									<label>Data Inicial</label> 
									<input type="date" class="form-control" name="dataInicial" id="dataInicialRel-Ent" value="<?php echo date('Y-m-d') ?>" required> 
								</div>						
							</div>
							<div class="col-md-6">
								<div class="form-group"> 
									<label>Data Final</label> 
									<input type="date" class="form-control" name="dataFinal" id="dataFinalRel-Ent" value="<?php echo date('Y-m-d') ?>" required> 
								</div>
							</div>

							<div class="col-md-6">						
								<div class="form-group"> 
									<label>Entradas / Ganhos</label> 
									<select class="form-control sel13" name="filtro" style="width:100%;">
										<option value="">Todas</option>
										<option value="Produto">Produtos</option>
										<option value="Serviço">Serviços</option>
										<option value="Conta">Demais Ganhos</option>
										
									</select> 
								</div>						
							</div>


							<div class="col-md-6">						
								<div class="form-group"> 
									<label>Selecionar Cliente</label> 
									<select class="form-control selcli" name="cliente" style="width:100%;" > 
									<option value="">Todos</option>
									<?php 
									$query = $pdo->query("SELECT * FROM clientes where id_conta = '$id_conta'");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									if($total_reg > 0){
										for($i=0; $i < $total_reg; $i++){
											foreach ($res[$i] as $key => $value){}
												echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
										}
									}
									?>


								</select>    
								</div>						
							</div>


						</div>


						

					</div>

					<div class="modal-footer">
						<button type="submit" class="btn btn-primary relatorio"><i class="fa-solid fa-file-lines"></i> Gerar Relatório</button>
					</div>
				</form>

			</div>
		</div>
	</div>









	<!-- Modal Rel Saidas / Despesas -->
	<div class="modal fade" id="RelSaidas" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header text-white" style="background-color: #4682B4;">
					<h4 class="modal-title" id="exampleModalLabel">Relatório de Saídas
						<small style="color: black;">(
							<a href="#" onclick="datas('1980-01-01', 'tudo-Saida', 'Saida')">
								<span style="color:#000" id="tudo-Saida">Tudo</span>
							</a> / 
							<a href="#" onclick="datas('<?php echo $data_atual ?>', 'hoje-Saida', 'Saida')">
								<span id="hoje-Saida">Hoje</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_mes ?>', 'mes-Saida', 'Saida')">
								<span style="color:#000" id="mes-Saida">Mês</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_ano ?>', 'ano-Saida', 'Saida')">
								<span style="color:#000" id="ano-Saida">Ano</span>
							</a> 
						)</small>



					</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="post" action="rel/rel_saidas_class.php" target="_blank">
					<div class="modal-body">

						<div class="row">
							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Data Inicial</label> 
									<input type="date" class="form-control" name="dataInicial" id="dataInicialRel-Saida" value="<?php echo date('Y-m-d') ?>" required> 
								</div>						
							</div>
							<div class="col-md-4">
								<div class="form-group"> 
									<label>Data Final</label> 
									<input type="date" class="form-control" name="dataFinal" id="dataFinalRel-Saida" value="<?php echo date('Y-m-d') ?>" required> 
								</div>
							</div>

							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Saídas / Despesas</label> 
									<select class="form-control sel13" name="filtro" style="width:100%;">
										<option value="">Todas</option>
										<option value="Conta">Despesas</option>
										<option value="Comissao">Comissões</option>
										<option value="Compra">Compras</option>
										
									</select> 
								</div>						
							</div>

						</div>


						

					</div>

					<div class="modal-footer">
						<button type="submit" class="btn btn-primary relatorio"><i class="fa-solid fa-file-lines"></i> Gerar Relatório</button>
					</div>
				</form>

			</div>
		</div>
	</div>










	<!-- Modal Rel Comissoes -->
	<div class="modal fade" id="RelComissoes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header text-white" style="background-color: #4682B4;">
					<h4 class="modal-title" id="exampleModalLabel">Relatório de Comissões
						<small style="color: black;">(
							<a href="#" onclick="datas('1980-01-01', 'tudo-Com', 'Com')">
								<span style="color:#000" id="tudo-Com">Tudo</span>
							</a> / 
							<a href="#" onclick="datas('<?php echo $data_atual ?>', 'hoje-Com', 'Com')">
								<span id="hoje-Com">Hoje</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_mes ?>', 'mes-Com', 'Com')">
								<span style="color:#000" id="mes-Com">Mês</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_ano ?>', 'ano-Com', 'Com')">
								<span style="color:#000" id="ano-Com">Ano</span>
							</a> 
						)</small>



					</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="post" action="rel/rel_comissoes_class.php" target="_blank">
					<div class="modal-body">

						<div class="row">
							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Data Inicial</label> 
									<input type="date" class="form-control" name="dataInicial" id="dataInicialRel-Com" value="<?php echo date('Y-m-d') ?>" required> 
								</div>						
							</div>
							<div class="col-md-4">
								<div class="form-group"> 
									<label>Data Final</label> 
									<input type="date" class="form-control" name="dataFinal" id="dataFinalRel-Com" value="<?php echo date('Y-m-d') ?>" required> 
								</div>
							</div>

								<div class="col-md-4">						
								<div class="form-group"> 
									<label>Pago</label> 
									<select class="form-control " name="pago" style="width:100%;">
										<option value="">Todas</option>
										<option value="Sim">Somente Pagas</option>
										<option value="Não">Pendentes</option>
										
									</select> 
								</div>						
							</div>

						</div>

						<div class="row">
							<div class="col-md-12">						
								<div class="form-group"> 
									<label>Profissionais</label> 
									<select class="form-control sel15" name="funcionario" style="width:100%;">
										<option value="">Todos</option>
										<?php 
				$query = $pdo->query("SELECT * FROM usuarios where atendimento = 'Sim' and id_conta = '$id_conta' ORDER BY id desc");
				$res = $query->fetchAll(PDO::FETCH_ASSOC);
				$total_reg = @count($res);
				if($total_reg > 0){
					for($i=0; $i < $total_reg; $i++){
						foreach ($res[$i] as $key => $value){}
							echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
					}
				}?>
										
									</select> 
								</div>						
							</div>	
						</div>


						

					</div>

					<div class="modal-footer">
						<button type="submit" class="btn btn-primary relatorio"><i class="fa-solid fa-file-lines"></i> Gerar Relatório</button>
					</div>
				</form>

			</div>
		</div>
	</div>








	<!-- Modal Rel Contas -->
	<div class="modal fade" id="RelCon" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header text-white" style="background-color: #4682B4;">
					<h4 class="modal-title" id="exampleModalLabel">Relatório de Contas
						<small style="color: black;">(
							<a href="#" onclick="datas('1980-01-01', 'tudo-Con', 'Con')">
								<span style="color:#000" id="tudo-Con">Tudo</span>
							</a> / 
							<a href="#" onclick="datas('<?php echo $data_atual ?>', 'hoje-Con', 'Con')">
								<span id="hoje-Con">Hoje</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_mes ?>', 'mes-Con', 'Con')">
								<span style="color:#000" id="mes-Con">Mês</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_ano ?>', 'ano-Con', 'Con')">
								<span style="color:#000" id="ano-Con">Ano</span>
							</a> 
						)</small>



					</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="post" action="rel/rel_contas_class.php" target="_blank">
					<div class="modal-body">

						<div class="row">
							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Data Inicial</label> 
									<input type="date" class="form-control" name="dataInicial" id="dataInicialRel-Con" value="<?php echo date('Y-m-d') ?>" required> 
								</div>						
							</div>
							<div class="col-md-4">
								<div class="form-group"> 
									<label>Data Final</label> 
									<input type="date" class="form-control" name="dataFinal" id="dataFinalRel-Con" value="<?php echo date('Y-m-d') ?>" required> 
								</div>
							</div>

							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Pago</label> 
									<select class="form-control" name="pago" style="width:100%;">
										<option value="">Todas</option>
										<option value="Sim">Somente Pagas</option>
										<option value="Não">Pendentes</option>
										
									</select> 
								</div>						
							</div>

						</div>



							<div class="row">
							<div class="col-md-6">						
								<div class="form-group"> 
									<label>Pagar / Receber</label> 
									<select class="form-control sel13" name="tabela" style="width:100%;">
										<option value="pagar">Contas à Pagar</option>
										<option value="receber">Contas à Receber</option>
																				
									</select> 
								</div>						
							</div>
							<div class="col-md-6">
								<div class="form-group"> 
									<label>Consultar Por</label> 
									<select class="form-control sel13" name="busca" style="width:100%;">
										<option value="data_venc">Data de Vencimento</option>
										<option value="data_pgto">Data de Pagamento</option>
																				
									</select>
								</div>
							</div>

							

						</div>


						

					</div>

					<div class="modal-footer">
						<button type="submit" class="btn btn-primary relatorio"><i class="fa-solid fa-file-lines"></i> Gerar Relatório</button>
					</div>
				</form>

			</div>
		</div>
	</div>








	<!-- Modal Rel Lucro -->
	<div class="modal fade" id="RelLucro" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header text-white" style="background-color: #4682B4;">
					<h4 class="modal-title" id="exampleModalLabel">Demonstrativo de Lucro
						<small style="color: black;">(
							<a href="#" onclick="datas('1980-01-01', 'tudo-Lucro', 'Lucro')">
								<span style="color:#000" id="tudo-Lucro">Tudo</span>
							</a> / 
							<a href="#" onclick="datas('<?php echo $data_atual ?>', 'hoje-Lucro', 'Lucro')">
								<span id="hoje-Lucro">Hoje</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_mes ?>', 'mes-Lucro', 'Lucro')">
								<span style="color:#000" id="mes-Lucro">Mês</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_ano ?>', 'ano-Lucro', 'Lucro')">
								<span style="color:#000" id="ano-Lucro">Ano</span>
							</a> 
						)</small>



					</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="post" action="rel/rel_lucro_class.php" target="_blank">
					<div class="modal-body">

						<div class="row">
							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Data Inicial</label> 
									<input type="date" class="form-control" name="dataInicial" id="dataInicialRel-Lucro" value="<?php echo date('Y-m-d') ?>" required> 
								</div>						
							</div>
							<div class="col-md-4">
								<div class="form-group"> 
									<label>Data Final</label> 
									<input type="date" class="form-control" name="dataFinal" id="dataFinalRel-Lucro" value="<?php echo date('Y-m-d') ?>" required> 
								</div>
							</div>						

						</div>


						

					</div>

					<div class="modal-footer">
						<button type="submit" class="btn btn-primary relatorio">Gerar Relatório</button>
					</div>
				</form>

			</div>
		</div>
	</div>










	<!-- Modal Rel Anivesariantes -->
	<div class="modal fade" id="RelAniv" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header text-white" style="background-color: #4682B4;">
					<h4 class="modal-title" id="exampleModalLabel">Relatório de Aniversáriantes
						<small style="color: black;">(
							<a href="#" onclick="datas('1980-01-01', 'tudo-Aniv', 'Aniv')">
								<span style="color:#000" id="tudo-Aniv">Tudo</span>
							</a> / 
							<a href="#" onclick="datas('<?php echo $data_atual ?>', 'hoje-Aniv', 'Aniv')">
								<span id="hoje-Aniv">Hoje</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_mes ?>', 'mes-Aniv', 'Aniv')">
								<span style="color:#000" id="mes-Aniv">Mês</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_ano ?>', 'ano-Aniv', 'Aniv')">
								<span style="color:#000" id="ano-Aniv">Ano</span>
							</a> 
						)</small>



					</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="post" action="rel/rel_aniv_class.php" target="_blank">
					<div class="modal-body">

						<div class="row">
							<div class="col-md-4">						
								<div class="form-group"> 
									<label>Data Inicial</label> 
									<input type="date" class="form-control" name="dataInicial" id="dataInicialRel-Aniv" value="<?php echo date('Y-m-d') ?>" required> 
								</div>						
							</div>
							<div class="col-md-4">
								<div class="form-group"> 
									<label>Data Final</label> 
									<input type="date" class="form-control" name="dataFinal" id="dataFinalRel-Aniv" value="<?php echo date('Y-m-d') ?>" required> 
								</div>
							</div>						

						</div>


						

					</div>

					<div class="modal-footer">
						<button type="submit" class="btn btn-primary relatorio">Gerar Relatório</button>
					</div>
				</form>

			</div>
		</div>
	</div>







	<!-- Modal Rel Entradas / Ganhos -->
	<div class="modal fade" id="RelServicos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header text-white" style="background-color: #4682B4;">
					<h4 class="modal-title" id="exampleModalLabel">Relatório de Serviços
						<small style="color: black;">(
							<a href="#" onclick="datas('1980-01-01', 'tudo-Ser', 'Ser')">
								<span style="color:#000" id="tudo-Ser">Tudo</span>
							</a> / 
							<a href="#" onclick="datas('<?php echo $data_atual ?>', 'hoje-Ser', 'Ser')">
								<span id="hoje-Ser">Hoje</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_mes ?>', 'mes-Ser', 'Ser')">
								<span style="color:#000" id="mes-Ser">Mês</span>
							</a> /
							<a href="#" onclick="datas('<?php echo $data_ano ?>', 'ano-Ser', 'Ser')">
								<span style="color:#000" id="ano-Ser">Ano</span>
							</a> 
						)</small>



					</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="post" action="rel/rel_servicos_class.php" target="_blank">
					<div class="modal-body">

						<div class="row">
							<div class="col-md-6">						
								<div class="form-group"> 
									<label>Data Inicial</label> 
									<input type="date" class="form-control" name="dataInicial" id="dataInicialRel-Ser" value="<?php echo date('Y-m-d') ?>" required> 
								</div>						
							</div>
							<div class="col-md-6">
								<div class="form-group"> 
									<label>Data Final</label> 
									<input type="date" class="form-control" name="dataFinal" id="dataFinalRel-Ser" value="<?php echo date('Y-m-d') ?>" required> 
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6">						
								<div class="form-group"> 
									<label>Forma de Pagamento</label> 
									<select class="form-control" name="pgto" style="width:100%;" > 
									<option value="">Selecionar Pagamento</option>
									<?php 
									$query = $pdo->query("SELECT * FROM formas_pgto where id_conta = '$id_conta'");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									if($total_reg > 0){
										for($i=0; $i < $total_reg; $i++){
											foreach ($res[$i] as $key => $value){}
												echo '<option value="'.$res[$i]['nome'].'">'.$res[$i]['nome'].'</option>';
										}
									}
									?>


								</select>    
								</div>						
							</div>


							<div class="col-md-6">						
								<div class="form-group"> 
									<label>Selecionar Serviço</label> 
									<select class="form-control" name="servico" style="width:100%;" > 
									<option value="">Selecionar Serviço</option>
									<?php 
									$query = $pdo->query("SELECT * FROM servicos where id_conta = '$id_conta'");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									if($total_reg > 0){
										for($i=0; $i < $total_reg; $i++){
											foreach ($res[$i] as $key => $value){}
												echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
										}
									}
									?>


								</select>    
								</div>						
							</div>

						</div>


						

					</div>

					<div class="modal-footer">
						<button type="submit" class="btn btn-primary relatorio">Gerar Relatório</button>
					</div>
				</form>

			</div>
		</div>
	</div>

	<!-- Modal -->
    <div class="modal fade" id="tutoriaisModal" tabindex="-1" aria-labelledby="tutoriaisModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-white">
                    <h5 class="modal-title" id="tutoriaisModalLabel">Vídeos Tutoriais</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Lista de vídeos -->
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group">
                                <li class="list-group-item video-item" data-video-id="ynGq7XzOBrA">Tutorial 1: Configurando o Sistema</li>
                                <li class="list-group-item video-item" data-video-id="Tiur6MDk0RU">Tutorial 2: Configurando o Site</li>

                                <!-- <li class="list-group-item video-item" data-video-id="ejVM_av7KsQ">Tutorial 3: Utilizando a Comanda</li> -->

								<li class="list-group-item video-item" data-video-id="uJd1G-cFAZc">Tutorial 6: Cadastros</li>

                                <!-- <li class="list-group-item video-item" data-video-id="P7s_7ARQpVY">Tutorial 4: Agendamentos e Serviços</li> -->

                                <li class="list-group-item video-item" data-video-id="UApt6WNUvVs">Tutorial 5: Produtos e controle de estoque</li>
                                
                                <li class="list-group-item video-item" data-video-id="Yth8P51HsEE">Tutorial 7: Clube do Assinante</li>

                                <li class="list-group-item video-item" data-video-id="3bGNuRtlmAQ">Tutorial 8: Financeiro</li>

                                <!-- <li class="list-group-item video-item" data-video-id="k3-zaTr6OUQ">Tutorial 9: WhatsApp e Campanha de Marketing</li>  -->

                                <li class="list-group-item video-item" data-video-id="k3-zaTr6OUQ">Tutorial 10: Menu do Profissional</li>

                                <li class="list-group-item video-item" data-video-id="k3-zaTr6OUQ">Tutorial 11: APP</li>
                                
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <!-- Container do vídeo -->
                            <div id="videoPlayer" class="ratio ratio-16x9">
                                <iframe src="" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

	<!-- Modal Seu Link -->
<div class="modal fade" id="modalSeuLink" tabindex="-1" role="dialog" aria-labelledby="modalSeuLinkLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);">

            <div class="modal-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #6a82fb, #fc5c7d); border: none; padding: 25px 30px;">
                <h5 class="modal-title text-white font-weight-bold" id="modalSeuLinkLabel" style="font-size: 1.5rem;">Compartilhe Seu Link</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="background-color: #f7f9fc; padding: 30px;">
                <p class="text-center text-secondary mb-4">Escolha a melhor forma de compartilhar seu link com seus clientes.</p><br>

                <div class="d-grid gap-3">
                    <button class="btn btn-lg btn-light w-100 py-3" onclick="copiarLink()" style="border-radius: 10px; border: 1px solid #e0e6ed; color: #4a5568; font-weight: 600; transition: all 0.3s ease; margin-bottom: 20px">
                        <i class="fas fa-copy fa-lg mr-3" style="color: #6a82fb;"></i> Copiar Link
                    </button><br>
                    <button class="btn btn-lg btn-success w-100 py-3" onclick="enviarLink()" style="background-color: #25D366; border-color: #25D366; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; margin-bottom: 20px">
                        <i class="fab fa-whatsapp fa-lg mr-3"></i> Enviar por WhatsApp
                    </button><br>
                    <button class="btn btn-lg btn-light w-100 py-3" onclick="mostrarQRCode()" style="border-radius: 10px; border: 1px solid #e0e6ed; color: #4a5568; font-weight: 600; transition: all 0.3s ease;">
                        <i class="fas fa-qrcode fa-lg mr-3" style="color: #fc5c7d;"></i> Exibir QR Code
                    </button>
                </div>

                <div id="qrcode-container" class="mt-4 text-center" style="display: none; border-top: 1px solid #e0e6ed; padding-top: 20px;">
                    <p class="text-muted mb-3">Escaneie o código com seu celular para acessar.</p>
                    <div id="qrcode" class="d-inline-block p-2" style="background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);"></div>
                    <button class="btn btn-outline-secondary mt-3" onclick="imprimirQRCode()" style="border-radius: 50px; font-weight: 600; padding: 10px 25px;">
                        <i class="fas fa-print mr-2"></i> Imprimir
                    </button>
                </div>
            </div>

            <div class="modal-footer" style="border: none; padding: 15px 30px; background-color: #f7f9fc;">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal" style="border-radius: 50px; font-weight: 600; padding: 10px 25px;">Fechar</button>
            </div>
        </div>
    </div>
</div>


	<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script> -->

	<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>


	


<script type="text/javascript">
	$(document).ready(function() {		
		$('.sel15').select2({	
			dropdownParent: $('#RelComissoes')		
		});

		$('.selcli').select2({	
			dropdownParent: $('#RelEntradas')		
		});
	});
</script>


 <script type="text/javascript">
	$("#form-perfil").submit(function () {

		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: "editar-perfil.php",
			type: 'POST',
			data: formData,

			success: function (mensagem) {
				$('#mensagem-perfil').text('');
				$('#mensagem-perfil').removeClass()
				if (mensagem.trim() == "Editado com Sucesso") {

					$('#btn-fechar-perfil').click();
					location.reload();			
					
				} else {

					$('#mensagem-perfil').addClass('text-danger')
					$('#mensagem-perfil').text(mensagem)
				}


			},

			cache: false,
			contentType: false,
			processData: false,

		});

	});
</script>








<script type="text/javascript">
	function carregarImgPerfil() {
    var target = document.getElementById('target-usu');
    var file = document.querySelector("#foto-usu").files[0];
    
        var reader = new FileReader();

        reader.onloadend = function () {
            target.src = reader.result;
        };

        if (file) {
            reader.readAsDataURL(file);

        } else {
            target.src = "";
        }
    }
</script>







 <script type="text/javascript">
	$("#form-config").submit(function () {

		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: "editar-config.php",
			type: 'POST',
			data: formData,

			success: function (mensagem) {
				$('#mensagem-config').text('');
				$('#mensagem-config').removeClass()
				if (mensagem.trim() == "Editado com Sucesso") {

					Swal.fire({
						position: "top-center",
						icon: "success",
						title: "Alterado com sucesso!",
						showConfirmButton: false,
						timer: 1500,
										
						willClose: () => {
						setTimeout(() => {
							$('#btn-fechar-config').click();
							location.reload();
							}, 300);
						}  
					}); 								
					
				} else {

					$('#mensagem-config').addClass('text-danger')
					$('#mensagem-config').text(mensagem)
				}


			},

			cache: false,
			contentType: false,
			processData: false,

		});

	});

	$("#form-config2").submit(function () {

		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: "editar-config-site.php",
			type: 'POST',
			data: formData,

			success: function (mensagem) {
				$('#mensagem-config2').text('');
				$('#mensagem-config2').removeClass()
				if (mensagem.trim() == "Editado com Sucesso") {

					Swal.fire({
						position: "top-center",
						icon: "success",
						title: "Alterado com sucesso!",
						showConfirmButton: false,
						timer: 1500,
										
						willClose: () => {
						setTimeout(() => {
							$('#btn-fechar-config2').click();
							location.reload();
							}, 300);
						}  
					}); 
							
					
				} else {

					$('#mensagem-config2').addClass('text-danger')
					$('#mensagem-config2').text(mensagem)
				}


			},

			cache: false,
			contentType: false,
			processData: false,

		});

	});
</script>




<script type="text/javascript">
	function carregarImgLogo() {
    var target = document.getElementById('target-logo');
    var file = document.querySelector("#foto-logo").files[0];
    
        var reader = new FileReader();

        reader.onloadend = function () {
            target.src = reader.result;
        };

        if (file) {
            reader.readAsDataURL(file);

        } else {
            target.src = "";
        }
    }
</script>





<script type="text/javascript">
	function carregarImgLogoRel() {
    var target = document.getElementById('target-logo-rel');
    var file = document.querySelector("#foto-logo-rel").files[0];
    
        var reader = new FileReader();

        reader.onloadend = function () {
            target.src = reader.result;
        };

        if (file) {
            reader.readAsDataURL(file);

        } else {
            target.src = "";
        }
    }
</script>





<script type="text/javascript">
	function carregarImgIcone() {
    var target = document.getElementById('target-icone');
    var file = document.querySelector("#foto-icone").files[0];
    
        var reader = new FileReader();

        reader.onloadend = function () {
            target.src = reader.result;
        };

        if (file) {
            reader.readAsDataURL(file);

        } else {
            target.src = "";
        }
    }
</script>





<script type="text/javascript">
	function carregarImgIconeSite() {
    var target = document.getElementById('target-icone-site');
    var file = document.querySelector("#foto-icone-site").files[0];
    
        var reader = new FileReader();

        reader.onloadend = function () {
            target.src = reader.result;
        };

        if (file) {
            reader.readAsDataURL(file);

        } else {
            target.src = "";
        }
    }
</script>






<script type="text/javascript">
	function carregarImgBannerIndex() {
    var target = document.getElementById('target-banner-index');
    var file = document.querySelector("#foto-banner-index").files[0];
    
        var reader = new FileReader();

        reader.onloadend = function () {
            target.src = reader.result;
        };

        if (file) {
            reader.readAsDataURL(file);

        } else {
            target.src = "";
        }
    }
</script>





<script type="text/javascript">
	function carregarImgSobre() {
    var target = document.getElementById('target-sobre');
    var file = document.querySelector("#foto-sobre").files[0];
    
        var reader = new FileReader();

        reader.onloadend = function () {
            target.src = reader.result;
        };

        if (file) {
            reader.readAsDataURL(file);

        } else {
            target.src = "";
        }
    }
</script>




	<script type="text/javascript">
		function datas(data, id, campo){		

			var data_atual = "<?=$data_atual?>";
			var separarData = data_atual.split("-");
			var mes = separarData[1];
			var ano = separarData[0];

			var separarId = id.split("-");

			if(separarId[0] == 'tudo'){
				data_atual = '2100-12-31';
			}

			if(separarId[0] == 'ano'){
				data_atual = ano + '-12-31';
			}

			if(separarId[0] == 'mes'){
				if(mes == 1 || mes == 3 || mes == 5 || mes == 7 || mes == 8 || mes == 10 || mes == 12){
					data_atual = ano + '-'+ mes + '-31';
				}else if (mes == 4 || mes == 6 || mes == 9 || mes == 11){
					data_atual = ano + '-'+ mes + '-30';
				}else{
					data_atual = ano + '-'+ mes + '-28';
				}

			}

			$('#dataInicialRel-'+campo).val(data);
			$('#dataFinalRel-'+campo).val(data_atual);

			document.getElementById('hoje-'+campo).style.color = "#000";
			document.getElementById('mes-'+campo).style.color = "#000";
			document.getElementById(id).style.color = "blue";	
			document.getElementById('tudo-'+campo).style.color = "#000";
			document.getElementById('ano-'+campo).style.color = "#000";
			document.getElementById(id).style.color = "blue";		
		}


		
	function validarConfirmacaoSenha() {
		var senha = document.getElementById("senha-perfil").value;
		var confirmacaoSenhaInput = document.getElementById("conf-senha-perfil");
		var confirmacaoSenhaValue = confirmacaoSenhaInput.value;

		// Apenas define 'required' para o campo de confirmação se houver algo no campo de senha
		if (senha) {
		confirmacaoSenhaInput.setAttribute("required", "required");
		} else {
		confirmacaoSenhaInput.removeAttribute("required");
		}

		// Valida se as senhas são diferentes
		if (senha !== confirmacaoSenhaValue) {
		confirmacaoSenhaInput.setCustomValidity("As senhas não conferem.");
		} else {
		// Se as senhas conferem, limpa a mensagem de erro personalizada
		confirmacaoSenhaInput.setCustomValidity("");
		}
	}

	</script>

	<script>
	$(document).ready(function () {
		$('#tabela').DataTable({
			lengthChange: false,
			searching: true,
			paging: true,
		});
    });
	</script>

	<script>
		$("#form-troca").submit(function () {		
		event.preventDefault();
		
		var formData = new FormData(this);

		$.ajax({
			url: 'troca.php',
			type: 'POST',
			data: formData,

			success: function (mensagem) {				
				$('#mensagem-troca').text('');
				$('#mensagem-troca').removeClass()
				if (mensagem.trim() == "Alterado com Sucesso") {  
					Swal.fire({
						position: "top-center",
						icon: "success",
						title: "Troca efetuada com sucesso!",
						showConfirmButton: false,
						timer: 2000,
										
						willClose: () => {
						setTimeout(() => {
							location.reload();
						
							}, 300);
						}  
					});  
					
					
				} else {
					$('#mensagem-troca').addClass('text-danger')
					$('#mensagem-troca').text(mensagem)
				}

			},

			cache: false,
			contentType: false,
			processData: false,

		});

	});
	</script>

<script>
        document.addEventListener('DOMContentLoaded', function () {
            const videoItems = document.querySelectorAll('.video-item');
            const iframe = document.querySelector('#videoPlayer iframe');

            videoItems.forEach(item => {
                item.addEventListener('click', function () {
                    // Remove a classe ativa de todos os itens
                    videoItems.forEach(i => i.classList.remove('active'));
                    // Adiciona a classe ativa ao item clicado
                    this.classList.add('active');

                    // Obtém o ID do vídeo
                    const videoId = this.getAttribute('data-video-id');
                    // Define a URL do vídeo com autoplay
                    iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
                });
            });

            // Limpa o iframe ao fechar o modal
            const modal = document.getElementById('tutoriaisModal');
            modal.addEventListener('hidden.bs.modal', function () {
                iframe.src = '';
                videoItems.forEach(i => i.classList.remove('active'));
            });
        });
    </script>

    <!-- Estilos opcionais -->
    <style>
        .video-item {
            cursor: pointer;
        }
        .video-item:hover {
            background-color: #f8f9fa;
        }
        .video-item.active {
            background-color: #e9ecef;
            font-weight: bold;
        }
    </style>

	<script>
    // Define the link (replace with your dynamic link)
    const seuLink = 'https://markai.skysee.com.br/site.php?u=<?=$username?>'; // Adjust this to your actual link, e.g., a specific URL for the user	
    const mensagemWhatsApp = encodeURIComponent("Confira este link incrível: " + seuLink);

    function copiarLink() {
        navigator.clipboard.writeText(seuLink).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Link Copiado!',
                text: 'O link foi copiado para a área de transferência.',
                showConfirmButton: false,
                timer: 1500
            });
        }).catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Não foi possível copiar o link.',
                showConfirmButton: false,
                timer: 1500
            });
        });
    }

    function enviarLink() {
        const whatsappUrl = `https://api.whatsapp.com/send?text=${mensagemWhatsApp}`;
        window.open(whatsappUrl, '_blank');
    }

    function mostrarQRCode() {
        const qrcodeContainer = document.getElementById('qrcode-container');
        const qrcodeElement = document.getElementById('qrcode');
        qrcodeElement.innerHTML = ''; // Clear previous QR code
        qrcodeContainer.style.display = 'block';
        new QRCode(qrcodeElement, {
            text: seuLink,
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    function imprimirQRCode() {
    // Get the QR code canvas from qrcode.js
    const qrcodeCanvas = document.getElementById('qrcode').querySelector('canvas');
    const qrCodeDataURL = qrcodeCanvas.toDataURL('image/png');

    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Imprimir QR Code</title>
                <style>
                    @media print {
                        body {
                            margin: 0;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            height: 100vh;
                            font-family: Arial, sans-serif;
                        }
                        .qr-container {
                            text-align: center;
                            width: 100%;
                            height: 100%;
                            display: flex;
                            flex-direction: column;
                            justify-content: center;
                            align-items: center;
                        }
                        .qr-container img {
                            width: 600px;
                            height: 600px;
                        }
                        .qr-container p {
                            margin-top: 60px;
                            font-size: 32px;
                            font-weight: bold;
                            color: #333;
                        }
                        @page {
                            size: A4;
                            margin: 0;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="qr-container">
                    <img src="${qrCodeDataURL}" alt="QR Code">
                    <p>Acesse nosso Link! 😃</p>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.onload = function() {
        printWindow.print();
        printWindow.onafterprint = function() {
            printWindow.close();
        };
    };
}

// Reset QR code visibility when modal is closed
$('#modalSeuLink').on('hidden.bs.modal', function () {
    document.getElementById('qrcode-container').style.display = 'none';
    document.getElementById('qrcode').innerHTML = '';
});
</script>

<script>
    // Encontra os elementos do ícone e do link do CSS
    const themeLink = document.getElementById('theme-stylesheet');
    const themeIcon = document.getElementById('theme-icon');

    // Função para aplicar o tema com base no nome
    function applyTheme(themeName) {
        if (themeName === 'Claro') {
            themeLink.href = 'css/SidebarNav.min2.css';
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        } else { // Tema 'Escuro'
            themeLink.href = 'css/SidebarNav.min.css';
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        }
    }

    // Verifica a preferência do usuário no localStorage
    // Usa 'Escuro' como padrão se nada for encontrado
    const currentTheme = localStorage.getItem('theme') || 'Escuro';
    applyTheme(currentTheme);

    // Adiciona um "ouvinte" de evento de clique para o ícone
    themeIcon.addEventListener('click', function() {
        const newTheme = (localStorage.getItem('theme') === 'Escuro') ? 'Claro' : 'Escuro';
        localStorage.setItem('theme', newTheme);
        location.reload(); // Recarrega a página para aplicar o novo CSS
    });


	
</script>

<!-- <script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('showLeftPush');
    const body = document.body;
    
    // As classes que o seu tema usa para abrir o menu
    const themeOpenClass = 'cbp-spmenu-open';

    if (toggleButton) {
        // Função para APLICAR o estado visual do menu
        const applySidebarState = (state) => {
            if (state === 'collapsed') {
                // Adiciona a classe para RECOLHER e garante que a classe para ABRIR está presente
                body.classList.add('sidebar-collapsed');
                body.classList.add(themeOpenClass);
            } else { // 'open'
                // Remove a classe para RECOLHER e garante que a classe para ABRIR está presente
                body.classList.remove('sidebar-collapsed');
                body.classList.add(themeOpenClass);
            }
        };

        // 1. VERIFICA O ESTADO SALVO AO CARREGAR A PÁGINA
        const savedState = localStorage.getItem('sidebarState') || 'open'; // Padrão é 'open'
        applySidebarState(savedState);

        // 2. CONTROLA O CLIQUE NO BOTÃO
        toggleButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Impede que outros scripts de clique sejam acionados

            const isCollapsed = body.classList.contains('sidebar-collapsed');
            const newState = isCollapsed ? 'open' : 'collapsed';
            
            applySidebarState(newState);
            localStorage.setItem('sidebarState', newState); // Salva a preferência
        });
    }
});
</script> -->

