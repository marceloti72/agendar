<?php 
@session_start();
require_once("verificar.php");
require_once __DIR__ . '/../../conexao.php';

$usuario_nivel = $_SESSION['nivel_usuario'];

$hoje = date('Y-m-d');

//verificar se ele tem a permissão de estar nessa página
if(@$home == 'ocultar'){
    echo "<script>window.location='../index.php'</script>";
    exit();
}

$query = $pdo->query("SELECT * FROM receber where data_lanc = '$hoje' and tipo = 'Serviço' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);

$query = $pdo->query("SELECT * FROM comandas where data = '$hoje' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_comandas = @count($res);

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
    
</head>
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
            background-color: #D2691E;
            color: white;
            border-radius: 10px;
            box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            width: 250px; /* Fixed width */
            height: 150px; /* Fixed height */
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
        }

        hr {
            margin: 10px 0;
            border: 0;
            border-top: 1px solid #ddd;
            width: 80%; /* Consistent width for hr */
        }

        .stats span {
            font-size: 0.9em;
            color: #7f8c8d;
        }

        .widget.disabled {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
<body>
    <div class="container">
        <div class="row">
            <a href="comanda" class="col_3">
                <div class="widget">
                    <div class="stats">
                        <h5><strong>COMANDAS</strong></h5>
                    </div>
                    <hr>
                    <div><span>Comandas Hoje: <?php echo $total_comandas; ?></span></div>
                </div>
            </a>

            <a href="meus_servicos" class="col_3">
                <div class="widget">
                    <div class="stats">
                        <h5><strong>SERVIÇOS</strong></h5>
                    </div>
                    <hr>
                    <div><span>Serviços Hoje: <?php echo $total_reg; ?></span></div>
                </div>
            </a>

            <a href="meus_servicos" class="col_3">
                <div class="widget">
                        <div class="stats">
                            <h5><strong>MARKETING</strong></h5>
                        </div>
                        <hr>
                        <div><span>Total de campanhas: <?php echo $total_marketing; ?></span></div>
                    </div>
                </a>           
        </div>
        <div class="row">
            <a href="calendario" class="col_3">
                <div class="widget">
                    <div class="stats">
                        <h5><strong>CALENDÁRIO</strong></h5>
                    </div>
                </div>
            </a>

            <a href="dias" class="col_3">
                <div class="widget">
                    <div class="stats">
                        <h5><strong>DIAS E HORÁRIOS</strong></h5>
                    </div>
                </div>
            </a>

            <a href="agenda" class="col_3">
                <div class="widget">
                    <div class="stats">
                        <h5><strong>AGENDA</strong></h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="row">
        <a href="meus_servicos" class="col_3">
                <div class="widget">
                        <div class="stats">
                            <h5><strong>VENDA PRODUTOS</strong></h5>
                        </div>
                        <hr>
                        <div><span>Total de produtos: <?php echo $total_produtos; ?></span></div>
                    </div>
                </a>            
    </div>
</body>
</html>
        

	

         