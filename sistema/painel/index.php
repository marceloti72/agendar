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

if(@$_SESSION['nivel_usuario'] != 'Administrador'){
	require_once("verificar-permissoes.php");
}

if(@$_GET['pag'] == ""){
	$pag = $pag_inicial;
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
	<link href='css/SidebarNav.min.css' media='all' rel='stylesheet' type='text/css'/>
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

  .modal-header{
	background-image: linear-gradient(to left,rgb(172, 172, 172), #787879);
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
<body class="cbp-spmenu-push" >
	<div class="main-content">
		<div class="cbp-spmenu cbp-spmenu-vertical cbp-spmenu-left"  id="cbp-spmenu-s1" >
			<!--left-fixed -navigation-->
			<aside class="sidebar-left" style="overflow: scroll; height:100%; scrollbar-width: thin;">
				<nav class="navbar navbar-inverse" >
					<div class="navbar-header" style = "background-image: linear-gradient(to left,rgb(212, 130, 78), #913e09);">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".collapse" aria-expanded="false" id="showLeftPush2">
							<span class="sr-only">Toggle navigation</span>				
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<h1><a style="color: white;text-shadow: 1px 1px 3px black; " class="navbar-brand" href="index.php"><i class="bi bi-clock-history"></i> Agendar<span class="dashboard_text"><?php echo $nome_sistema ?></span></a></h1>
					</div>
					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						<ul class="sidebar-menu">
							<li class="header">MENU ADMINISTRATIVO</li>


							<!-- <li class="treeview <?php echo @$home ?>">
								<a href="painel">
									<i class="fa fa-home"></i> <span>Painel</span>
								</a>
							</li> -->

							<!-- <li class="treeview <?php echo @$home ?>">
								<a href="index.php">
									<i class="fa fa-dashboard"></i> <span>Dashboards</span>
								</a>
							</li> -->
							<li class="treeview <?php echo @$home ?>">
								<a href="#">
									<i class="fa fa-dashboard"></i>
									<span>Dashboards</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">
									<li class="<?php echo @$home ?>"><a href="index.php"><i class="fa fa-angle-right"></i>Financeiro</a></li>
									<li class="<?php echo @$home ?>"><a href="grafico_dias"><i class="fa fa-angle-right"></i>Agendamentos Mês</a></li>
									<li class="<?php echo @$home ?>"><a href="grafico_ano"><i class="fa fa-angle-right"></i>Agendamentos Ano</a></li>
					

								</ul>
							</li>

							<li class="treeview <?php echo @$comanda ?>">
								<a href="comanda">
								<i class="fa fa-clipboard"></i> <span>Nova Comanda</span>
								</a>
							</li>

							<li class="treeview <?php echo @$menu_agendamentos ?>">
								<a href="#">
									<i class="fe fe-clock"></i>&nbsp;
									<span>Agendamento / Serviço</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">

									<li class="<?php echo @$agendamentos ?>"><a href="agendamentos"><i class="fa fa-angle-right"></i>Agendamentos</a></li>

									<li class="<?php echo @$servicos_agenda ?>"><a href="servicos_agenda"><i class="fa fa-angle-right"></i>Serviços</a></li>
									
																	
								
								</ul>
							</li>


							<!-- <li class="treeview <?php echo @$menu_pessoas ?>">
								<a href="#">
									<i class="fa fa-users"></i>
									<span>Pessoas</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">
									<li class="<?php echo @$usuarios ?>"><a href="usuarios"><i class="fa fa-angle-right"></i>Usuários</a></li>
									<li class="<?php echo @$funcionarios ?>"><a href="funcionarios"><i class="fa fa-angle-right"></i>Funcionários</a></li>
									<li class="<?php echo @$clientes ?>"><a href="clientes"><i class="fa fa-angle-right"></i>Clientes</a></li>

									<li class="<?php echo @$clientes_retorno ?>"><a href="clientes_retorno"><i class="fa fa-angle-right"></i>Clientes Retornos</a></li>

									<li class="<?php echo @$fornecedores ?>"><a href="fornecedores"><i class="fa fa-angle-right"></i>Fornecedores</a></li>

								</ul>
							</li> -->



							<li class="treeview <?php echo @$menu_cadastros ?>" >
								<a href="#">
									<i class="fa fa-pencil"></i>
									<span>Cadastros</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">

								<?php 
								if($plano == '2'){
								?>
								<li class="<?php echo @$cargos ?>"><a href="cargos"><i class="fa fa-angle-right"></i>Cargos</a></li>
								<li class="<?php echo @$usuarios ?>"><a href="usuarios"><i class="fa fa-angle-right"></i>Usuários</a></li>
								<li class="<?php echo @$funcionarios ?>"><a href="funcionarios"><i class="fa fa-angle-right"></i>Funcionários</a></li>								
								<?php 
								}
								
								?>							


								<li class="<?php echo @$clientes ?>"><a href="clientes"><i class="fa fa-angle-right"></i>Clientes</a></li>
								
									<li class="<?php echo @$fornecedores ?>"><a href="fornecedores"><i class="fa fa-angle-right"></i>Fornecedores</a></li>	
									
									<?php 
									if($id_conta == '1'){?>
										<li class="<?php echo @$grupos ?>"><a href="grupos"><i class="fa fa-angle-right"></i>Grupo Acessos</a></li>

										<li class="<?php echo @$acessos ?>"><a href="acessos"><i class="fa fa-angle-right"></i>Acessos</a></li>

									<?php }
									?>									

										<li class="<?php echo @$pgto ?>"><a href="pgto"><i class="fa fa-angle-right"></i>Formas de Pagamento</a></li>

										<li><a href="dias"><i class="fa fa-angle-right"></i>Horários / Dias</a></li>

										<li class="<?php echo @$dias_bloqueio ?>"><a href="dias_bloqueio"><i class="fa fa-angle-right"></i>Bloqueio de Dias</a></li>
								
								</ul>
							</li>							

							<li class="treeview <?php echo @$menu_servicos ?>">
								<a href="#">
								<i class="fa fa-briefcase"></i>
									<span>Serviços</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">

								   <li class="<?php echo @$servicos ?>"><a href="servicos"><i class="fa fa-angle-right"></i>Serviços</a></li>

								   <li class="<?php echo @$cat_servicos ?>"><a href="cat_servicos"><i class="fa fa-angle-right"></i>Categoria Serviços</a></li>
								</ul>

								<li class="treeview <?php echo @$menu_produtos ?>">
								<a href="#">
								    <i class="fa fa-tags"></i>
									<span>Produtos</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">

									<li class="<?php echo @$produtos ?>"><a href="produtos"><i class="fa fa-angle-right"></i>Produtos</a></li>

									<li class="<?php echo @$cat_produtos ?>"><a href="cat_produtos"><i class="fa fa-angle-right"></i>Categorias</a></li>
									
									<li class="<?php echo @$estoque ?>"><a href="estoque"><i class="fa fa-angle-right"></i>Estoque Baixo</a></li>

									<li class="<?php echo @$saidas ?>"><a href="saidas"><i class="fa fa-angle-right"></i>Saídas</a></li>

									<li class="<?php echo @$entradas ?>"><a href="entradas"><i class="fa fa-angle-right"></i>Entradas</a></li>
								
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

										<li class="<?php echo @$vendas ?>"><a href="assinantes"><i class="fa fa-angle-right"></i>Assinantes</a></li>

										<li class="<?php echo @$compras ?>"><a href="conf_planos"><i class="fa fa-angle-right"></i>Configuração</a></li>			
																		
									
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

									<li class="<?php echo @$vendas ?>"><a href="vendas"><i class="fa fa-angle-right"></i>Vendas</a></li>

									<li class="<?php echo @$compras ?>"><a href="compras"><i class="fa fa-angle-right"></i>Compras</a></li>
									
									<li class="<?php echo @$pagar ?>"><a href="pagar"><i class="fa fa-angle-right"></i>Contas à Pagar</a></li>

									<li class="<?php echo @$receber ?>"><a href="receber"><i class="fa fa-angle-right"></i>Contas à Receber</a></li>	

									<li class="<?php echo @$comissoes ?>"><a href="comissoes"><i class="fa fa-angle-right"></i>Comissões</a></li>									
								
								</ul>
							</li>

							<li class="treeview <?php echo @$menu_relatorio ?>" >
								<a href="#">
									<i class="fa fa-file-pdf-o"></i>
									<span>Relatórios</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">

									<li class="<?php echo @$rel_produtos ?>"><a href="rel/rel_produtos_class.php" target="_blank"><i class="fa fa-angle-right"></i>Relatório de Produtos</a></li>

									<li class="<?php echo @$rel_entradas ?>"><a href="#" data-toggle="modal" data-target="#RelEntradas"><i class="fa fa-angle-right"></i>Entradas / Ganhos</a></li>

									<li class="<?php echo @$rel_saidas ?>"><a href="#" data-toggle="modal" data-target="#RelSaidas"><i class="fa fa-angle-right"></i>Saídas / Despesas</a></li>

									<li class="<?php echo @$rel_comissoes ?>"><a href="#" data-toggle="modal" data-target="#RelComissoes"><i class="fa fa-angle-right"></i>Relatório de Comissões</a></li>

									<li class="<?php echo @$rel_contas ?>"><a href="#" data-toggle="modal" data-target="#RelCon"><i class="fa fa-angle-right"></i>Relatório de Contas</a></li>


									<li class="<?php echo @$rel_servicos ?>"><a href="#" data-toggle="modal" data-target="#RelServicos"><i class="fa fa-angle-right"></i>Relatório de Serviços</a></li>


									<li class="<?php echo @$rel_aniv ?>"><a href="#" data-toggle="modal" data-target="#RelAniv"><i class="fa fa-angle-right"></i>Relatório de Aniversáriantes</a></li>


									<li class="<?php echo @$rel_lucro ?>"><a href="#" data-toggle="modal" data-target="#RelLucro"><i class="fa fa-angle-right"></i>Demonstrativo de Lucro</a></li>	
															
								</ul>
							</li>

							<li class="treeview <?php echo @$clientes_retorno ?>">
                                <a href="clientes_retorno">
                                    <i class="fa fa-bell"></i><span>Clientes Retornos</span>
                                </a>
                             </li>	


							<li class="treeview <?= @$whatsapp?>">
								<a href="#">
									<i class="fab fa-whatsapp"></i>
									<span>Whatsapp</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">

									<li class="treeview"><a href="whatsapp"><i class="fa fa-cog"></i>Configurações</a></li>								
								
									
																		
								
								</ul>
							</li>
                         

                            <li class="treeview <?= @$marketing ?>">
                                <a href="marketingp">
                                    <i class="fa fa-paper-plane"></i><span>Campanha Marketing</span>
                                </a>
                             </li>					
                            
								<li class="treeview <?php echo @$calendario ?>">
								<a href="calendario">
									<i class="fa fa-calendar-o"></i> <span>Calendário</span>
								</a>
							</li>


							<li class="treeview <?php echo @$menu_site ?>" >
								<a href="#">
									<i class="fa fa-globe"></i>
									<span>Dados do Site</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">
									
									<li> <a href="conf_site" ><i class="fa fa-angle-right"></i> Configurações</a> </li> 		

									<li class="<?php echo @$textos_index ?>"><a href="textos_index"><i class="fa fa-angle-right"></i>Textos Carrossel</a></li>


									<li class="<?php echo @$comentarios ?>"><a href="comentarios"><i class="fa fa-angle-right"></i>Comentários</a></li>

														
								
								</ul>
							</li>                           
							

							<?php if(@$atendimento == 'Sim'){?>

								<li class="header">MENU DO PROFISSIONAL</li>
							<li class="treeview <?php echo @$minha_agenda ?>">
								<a href="agenda">
									<i class="fa fa-calendar-o"></i> <span>Minha Agenda</span>
								</a>
							</li>	
							
							<li class="treeview  <?php echo @$meus_servicos ?>">
								<a href="#">
									<i class="fa fa-server"></i>
									<span>Meus Serviços</span>
									<i class="fa fa-angle-left pull-right"></i>
								</a>
								<ul class="treeview-menu">
							
									<li><a href="meus_servicos"><i class="fa fa-angle-right"></i> <span>Serviços</span></a>
									</li>	

									<li><a href="servicos_func"><i class="fa fa-angle-right"></i>Ativar Serviços</a></li>
									</ul>
							</li>	


							<li class="treeview  <?php echo @$minhas_comissoes ?>">
								<a href="minhas_comissoes">
								<i class="fa fa-dollar-sign"></i> <span>Minhas Comissões</span>
								</a>
							</li>						


							<li class="treeview  <?php echo @$meus_dias ?>">
								<a href="#">
									<i class="fa fa-server"></i>
									<span>Meus Horário / Dias</span>
									<i class="fa fa-clock-o pull-right"></i>
								</a>
								<ul class="treeview-menu">

									<li><a href="dias"><i class="fa fa-angle-right"></i>Horários / Dias</a></li>

									<li><a href="dias_bloqueio_func"><i class="fa fa-angle-right"></i>Bloqueio de Dias</a></li>
																		
								
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
				<!--toggle button end-->
				<div class="profile_details_left"><!--notifications of menu start -->
					<ul class="nofitications-dropdown">						

						<?php
						$id_conta = $_SESSION['id_conta'];
						if(@$_SESSION['nivel_usuario'] == 'Administrador'){ 
							$query = $pdo->query("SELECT * FROM agendamentos where data = curDate() and status = 'Agendado' and id_conta = '$id_conta'");
							$res = $query->fetchAll(PDO::FETCH_ASSOC);
							$total_agendamentos_hoje_usuario_pendentes = @count($res);
							$link_ag = 'agendamentos';
						}else{
							$query = $pdo->query("SELECT * FROM agendamentos where data = curDate() and funcionario = '$id_usuario' and status = 'Agendado' and id_conta = '$id_conta'");
							$res = $query->fetchAll(PDO::FETCH_ASSOC);
							$total_agendamentos_hoje_usuario_pendentes = @count($res);
							$link_ag = 'agenda';

						}
						if($total_agendamentos_hoje_usuario_pendentes != 0){
							$icon2 = 'icon-wiggle-whatsapp';					
						}else{
							$icon2='';
						}
						?>

						<li class="dropdown head-dpdn">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-bell <?php echo $icon2?>" title="Agendamentos hoje"></i>
							<?php 								
								if($total_agendamentos_hoje_usuario_pendentes != 0){							
									?>
                                    <span class="badge text-danger"><?php echo $total_agendamentos_hoje_usuario_pendentes ?></span><?php 
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
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Aniversariantes de hoje"><i class="fa fa-birthday-cake <?php echo $icon3?>" style="color: #FFF"></i>
							<?php 								
								if($total_aniversariantes_hoje != 0){?>
                                    <span class="badge" style="background: #2b6b39"><?php echo $total_aniversariantes_hoje ?></span><?php 
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
								</li>
							</ul>
						</li>	
					<?php } ?>






					<?php if(@$clientes_retorno == ''){ 

						//totalizando aniversariantes do dia
						$query = $pdo->query("SELECT * FROM clientes where alertado != 'Sim' and data_retorno < curDate() and id_conta = '$id_conta' ORDER BY data_retorno asc");
						$res = $query->fetchAll(PDO::FETCH_ASSOC);
						$total_clientes_retorno = @count($res);

						if($total_clientes_retorno != 0){
							$icon4 = 'icon-wiggle-whatsapp';					
						}else{
							$icon4='';
						}

							?>
						<li class="dropdown head-dpdn">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Clientes com retorno pendente"><i class="fa fa-users <?php echo $icon4?>" style="color:#FFF"></i>
								<?php 								
								if($total_clientes_retorno != 0){?>
                                    <span class="badge" style="background: #c93504"><?php echo $total_clientes_retorno ?></span><?php 
								}?>
							</a>
							<ul class="dropdown-menu">
								<li>
									<div class="notification_header" align="center">
										<h3><?php echo $total_clientes_retorno ?> Cliente com Retorno Pendente</h3>
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
									<div class="notification_bottom" style="background: #ffcdbd">
										<a href="clientes_retorno">Ver Clientes</a>
									</div> 
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
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Depoimentos pendentes"><i class="fa fa-comment <?php echo $icon5?>" style="color:#FFF"></i><?php 
							if($total_comentarios != 0){?>
                                    <span class="badge" style="background: #22168a"><?php echo $total_comentarios ?></span><?php 
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
                        <li class="dropdown head-dpdn" style="margin-left: 20px; color: <?php echo $cor?>" title='<?php echo $status?>'><small><i class="fab fa-whatsapp fa-2x"></i></small></li>




						


					</ul>
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
										<span><?php echo $nivel_usuario ?></span>
									</div>
									<i class="fa fa-angle-down lnr"></i>
									<i class="fa fa-angle-up lnr"></i>
									<div class="clearfix"></div>	
								</div>	
							</a>
							<ul class="dropdown-menu drp-mnu">
								<?php if(@$configuracoes == ''){ ?>
								<li> <a href="configuracoes" ><i class="fa fa-cog"></i> Config. Sistema</a> </li> 	
								<?php } ?>
								

								<li> <a href="" data-toggle="modal" data-target="#modalPerfil"><i class="fa fa-suitcase"></i> Editar Perfil</a> </li> 
								<li> <a href="" data-toggle="modal" data-target="#assinaturaModal"><i class="fa fa-dollar"></i> Minha Assinatura</a> </li> 
								<li> <a href="" data-toggle="modal" data-target="#tutoriaisModal"><i class="fa fa-question" style="color: #15b283;"></i> Vídeos Tutoriais</a> </li> 
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
								<label for="exampleInputEmail1">Senha</label>
								<input type="password" class="form-control" id="senha-perfil" name="senha" placeholder="Senha" autocomplete="new-password" oninput="validarConfirmacaoSenha()">
							</div>
						</div>
						<div class="col-md-4">
							
							<div class="form-group">
								<label for="exampleInputEmail1">Confirmar Senha</label>
								<input type="password" class="form-control" id="conf-senha-perfil" name="conf_senha" placeholder="Confirmar Senha">    
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
<div class="modal fade" id="assinaturaModal" tabindex="-1" aria-labelledby="assinaturaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
      <div class="modal-header" style="background: linear-gradient(45deg, #4682B4, #87CEEB); border-bottom: none;">
        <h5 class="modal-title text-white" id="assinaturaModalLabel" style="font-size: 25px;">Dados da Assinatura</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;margin-top: -30px">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

	  <?php     
	           

            // Configurações Iniciais e Conexões
        $url_sistema = explode("//", $url);
        $host = ($url_sistema[1] == 'localhost/agendar/') ? 'localhost' : 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
        $usuario = ($url_sistema[1] == 'localhost/agendar/') ? 'root' : 'skysee';
        $senha = ($url_sistema[1] == 'localhost/agendar/') ? '' : '9vtYvJly8PK6zHahjPUg';
        $banco = 'gestao_sistemas';

        try {
            $pdo2 = new PDO("mysql:dbname=$banco;host=$host;charset=utf8", "$usuario", "$senha");
            $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            error_log("Erro ao conectar ao banco de dados: " . $e->getMessage());
            echo 'Erro ao conectar ao banco de dados!';
        }
		

        // Busca informações do cliente        
        $query8 = $pdo2->prepare("SELECT * FROM clientes WHERE id_conta = :id_conta");
        $query8->bindValue(':id_conta', $id_conta, PDO::PARAM_INT);
        $query8->execute();
        $res8 = $query8->fetchAll(PDO::FETCH_ASSOC);
		$id_cliente = $res8[0]['id'];
		if($res8[0]['plano'] == '1'){
			$plano = 'Individual';
		}else{
			$plano = 'Empresa';
		}	
		
        

        $query9 = $pdo2->prepare("SELECT id, vencimento, taxa, valor, subtotal, frequencia FROM receber WHERE pago = 'Não' AND cliente =:cliente");        
        $query9->bindValue(':cliente', $id_cliente, PDO::PARAM_INT);
        $query9->execute();
        $res9 = $query9->fetch(PDO::FETCH_ASSOC);

        if ($res9) {
            $data_venc = $res9['vencimento'];              
            $id_pg = $res9['id'];
            $valorMensal = $res9['valor'];	
			if($res9['frequencia'] == '30'){
				$frequencia = 'Mensal';
			}else{
				$frequencia = 'Anual';
			}	
			
        } else {
            // Lidar com o caso em que não há resultados, por exemplo:
            $data_venc = null;
            $taxa = 0;
            $sub_total = 0;
        }?>




      <div class="modal-body" style="background-color: #F8F9FA; padding: 25px;">
        <div class="card border-0 shadow-sm">
          <div class="card-body" style="padding: 20px;">
            <div class="text-center mb-3">
              <h3 class="card-text" style="color: #333; font-weight: 700;"><?php echo mb_strtoupper($id_cliente, 'UTF-8'); ?></h3>
            </div>
            <hr style="border-top: 1px solid #E0E0E0;">
            
            <?php if($ativo_sistema == 'teste'): ?>
              <small class="d-block text-center mb-3" style="font-size: 12px; color: #DC3545;">*Em teste grátis</small>
            <?php endif; ?>
            
            <p class="card-text" style="font-weight: 600; color: #444;">Detalhes da Cobrança:</p>
            <ul class="list-group list-group-flush mb-4" style="border: 1px solid #E9ECEF; border-radius: 5px;">			  
              <li class="list-group-item d-flex justify-content-between" style="background: #fff; padding-top: 10px"><b>Valor:</b> <span>R$ <?php echo $valorMensal?></span></li>
              <li class="list-group-item d-flex justify-content-between" style="background: #fff;"><b>Plano:</b> <span><?php echo $plano?></span></li>
              <li class="list-group-item d-flex justify-content-between" style="background: #fff;  padding-bottom: 10px"><b>Pagamento:</b> <span><?php echo $frequencia ?></span></li>			  
            </ul>

            <?php 
            if($data_venc < $data_atual){?>
              <p style="color: #DC3545; font-weight: 500;"><strong>Vencida em:</strong> <?php echo date('d/m/Y', strtotime($data_venc)); ?></p>
            <?php }else{?>
              <p style="color: #28A745; font-weight: 500;"><strong>Próximo Vencimento:</strong> <?php echo date('d/m/Y', strtotime($data_venc)); ?></p>
            <?php } ?>

            <div class="d-flex gap-2">
              <a href="https://www.gestao.skysee.com.br/pagar/<?php echo $id_pg?>" target="_blank" class="btn btn-primary w-100" style="background: #4682B4; border: none; transition: all 0.3s;">Pagar</a>
              <button type="button" class="btn btn-outline-secondary w-100" data-toggle="modal" data-target="#trocarPlanoModal" style="transition: all 0.3s;">Trocar Plano</button>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="border-top: none; padding: 15px 25px;">
        <button type="button" class="btn btn-secondary" id="btn-fechar2" data-dismiss="modal" style="border-radius: 5px; padding: 8px 20px;">Fechar</button>
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
										<option value="Venda">Vendas</option>
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
									<label>Funcionário</label> 
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

                                <li class="list-group-item video-item" data-video-id="ejVM_av7KsQ">Tutorial 3: Utilizando a Comanda</li>

								<li class="list-group-item video-item" data-video-id="uJd1G-cFAZc">Tutorial 6: Cadastros</li>

                                <li class="list-group-item video-item" data-video-id="P7s_7ARQpVY">Tutorial 4: Agendamentos e Serviços</li>

                                <li class="list-group-item video-item" data-video-id="3bGNuRtlmAQ">Tutorial 5: Produtos e controle de estoque</li>
                                
                                <li class="list-group-item video-item" data-video-id="ynGq7XzOBrA">Tutorial 7: Clube do Assinante</li>

                                <li class="list-group-item video-item" data-video-id="3bGNuRtlmAQ">Tutorial 8: Financeiro</li>

                                <li class="list-group-item video-item" data-video-id="k3-zaTr6OUQ">Tutorial 9: WhatsApp e Campanha de Marketing</li> 

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


	<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script> -->


	



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
			var confirmacaoSenha = document.getElementById("conf-senha-perfil");

			if (senha) {
				confirmacaoSenha.setAttribute("required", "required");
			} else {
				confirmacaoSenha.removeAttribute("required");
			}

			if(senha !== confirmacaoSenha.value){
				confirmacaoSenha.setCustomValidity("As senhas não conferem.");
			}else{
				confirmacaoSenha.setCustomValidity("");
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


