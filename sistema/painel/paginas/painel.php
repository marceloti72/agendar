<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once __DIR__ . '/../../conexao.php';

$usuario_nivel = $_SESSION['nivel_usuario'];

$hoje = date('Y-m-d');

// Verificar se ele tem a permissão de estar nessa página
if(@$_SESSION['nivel_usuario'] != 'administrador'){
	    echo "<script>window.location='agenda.php'</script>";
    }

$query = $pdo->query("SELECT * FROM receber where data_lanc = '$hoje' and tipo = 'Serviço' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);

$query = $pdo->query("SELECT * FROM receber where data_lanc = '$hoje' and tipo = 'Produto' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_vendas = @count($res);

$query = $pdo->query("SELECT * FROM agendamentos where data = '$hoje' and status = 'Agendado' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$agendados = @count($res);

$query = $pdo->query("SELECT * FROM agendamentos where data = '$hoje' and status = 'Concluído' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$concluidos = @count($res);

$query = $pdo->query("SELECT * FROM comandas where data = '$hoje' and status = 'Aberta' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_comandas_abertas = @count($res);

$query = $pdo->query("SELECT * FROM comandas where data = '$hoje' and status = 'Fechada' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_comandas_fechadas = @count($res);

$query = $pdo->query("SELECT * FROM marketing where id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_marketing = @count($res);

$query = $pdo->query("SELECT * FROM produtos where id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_produtos = @count($res);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel</title>
    <!-- Incluindo Font Awesome via CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            padding: 20px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .col_3 {
            text-align: center;
        }

        .widget {            
            background-color: #FFA500;
            color: white;
            border-radius: 10px;
            box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            width: 250px; /* Largura fixa */
            height: 150px; /* Altura fixa padrão */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .widget:hover {
            transform: translateY(-5px);
            box-shadow: 6px 6px 12px rgba(0, 0, 0, 0.5);
        }

        .stats h5 {
            margin: 0;
            font-size: 1.2em;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px; /* Espaço entre ícone e texto */
        }

        hr {
            margin: 10px 0;
            border: 0;
            border-top: 1px solid #ddd;
            width: 80%; /* Largura consistente para hr */
        }

        .stats span {
            font-size: 0.9em;
            color: #7f8c8d;
        }

        .widget.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Media query para mobile (max-width: 768px) */
        @media (max-width: 768px) {
            .container{
                margin-top: -70px;
            }
            .row {
                flex-direction: column; /* Um embaixo do outro */
                align-items: center; /* Alinhado ao centro */
                gap: 15px; /* Espaçamento menor entre widgets */
            }

            .widget {
                width: 250px; /* Mantém a largura */
                height: 75px; /* Metade da altura original (150px / 2) */
                padding: 10px; /* Reduz o padding para caber o conteúdo */
            }

            .stats h5 {
                font-size: 1em; /* Reduz o tamanho da fonte */
                gap: 6px; /* Menor espaço entre ícone e texto em mobile */
            }

            hr {
                margin: 5px 0; /* Reduz a margem do hr */
            }

            .stats span {
                font-size: 0.7em; /* Reduz ainda mais o tamanho do span em mobile */
                line-height: 1.2; /* Ajusta o espaçamento entre linhas */
            }

            /* Reduzir o tamanho inline para evitar overflow */
            [style*="font-size: 12px"] {
                font-size: 10px !important; /* Sobrescreve o inline style */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <a href="comanda" class="col_3">
                <div class="widget">
                    <div class="stats">
                        <h5><i class="fas fa-ticket-alt"></i> <strong>ABRIR COMANDA</strong></h5>
                    </div>
                    <hr>
                    <div style="font-size: 12px;" class="sub"><span>Comandas Abertas: <?php echo $total_comandas_abertas; ?></span></div>
                    <div style="font-size: 12px;" class="sub"><span>Comandas Fechadas: <?php echo $total_comandas_fechadas; ?></span></div>
                </div>
            </a>

            <a href="servicos_agenda" class="col_3">
                <div class="widget">
                    <div class="stats">
                        <h5><i class="fas fa-tools"></i> <strong>NOVO SERVIÇO</strong></h5>
                    </div>
                    <hr>
                    <div style="font-size: 12px;" class="sub"><span>Hoje: <?php echo $total_reg; ?></span></div>
                </div>
            </a>
        
            <!-- <a href="meus_servicos" class="col_3">
                <div class="widget">
                    <div class="stats">
                        <h5><i class="fas fa-shopping-cart"></i> <strong>VENDA DE PRODUTOS</strong></h5>
                    </div>
                    <hr>
                    <div style="font-size: 12px;" class="sub"><span>Total de produtos: <?php echo $total_produtos; ?></span></div>
                    <div style="font-size: 12px;" class="sub"><span>Vendidos hoje: <?php echo $total_vendas; ?></span></div>
                </div>
            </a>  -->
            
            <a href="agendamentos" class="col_3">
                <div class="widget">
                    <div class="stats">
                        <h5><i class="fas fa-calendar-check"></i> <strong>AGENDAMENTO</strong></h5>
                    </div>
                    <hr>
                    <div style="font-size: 12px;" class="sub"><span>À concluir: <?php echo $agendados; ?></span></div>
                    <div style="font-size: 12px;" class="sub"><span>Concluidos: <?php echo $concluidos; ?></span></div>
                </div>
            </a>
        
            <a href="calendario" class="col_3">
                <div class="widget">
                    <div class="stats">
                        <h5><i class="fas fa-calendar-alt"></i> <strong>CALENDÁRIO</strong></h5>
                    </div>
                </div>
            </a>
        </div>
    </div>
</body>
</html>