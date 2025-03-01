<?php 
@session_start();
require_once("verificar.php");
require_once __DIR__ . '/../../conexao.php';

$usuario_nivel = $_SESSION['nivel_usuario'];

$comandas_disabled = ($usuario_nivel == "Individual") ? "disabled" : "";
$marketing_disabled = ($usuario_nivel == "Individual") ? "disabled" : "";
$comandas_opacity = ($usuario_nivel == "Individual") ? "0.5" : "1";
$marketing_opacity = ($usuario_nivel == "Individual") ? "0.5" : "1";

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
            background-color: #836FFF;
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
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col_3" <?php echo $comandas_disabled; ?> onclick="<?php if ($comandas_disabled == "") { echo "$('#modalFormComanda').modal('show')"; } ?>">
                <div class="widget <?php if ($comandas_disabled != "") { echo "disabled"; } ?>" style="opacity: <?php echo $comandas_opacity; ?>">
                    <div class="stats">
                        <h5><strong>COMANDAS</strong></h5>
                    </div>
                    <hr>
                    <div><span>Comandas Hoje: <?php echo $total_comandas; ?></span></div>
                </div>
            </div>

            <a href="meus_servicos" class="col_3">
                <div class="widget">
                    <div class="stats">
                        <h5><strong>SERVIÇOS</strong></h5>
                    </div>
                    <hr>
                    <div><span>Serviços Hoje: <?php echo $total_reg; ?></span></div>
                </div>
            </a>

            <div class="col_3" <?php echo $marketing_disabled; ?>>
                <a href="<?php if ($marketing_disabled == "") { echo "marketing"; } ?>" style="pointer-events: <?php if ($marketing_disabled != "") { echo "none"; } ?>">
                    <div class="widget <?php if ($marketing_disabled != "") { echo "disabled"; } ?>" style="opacity: <?php echo $marketing_opacity; ?>">
                        <div class="stats">
                            <h5><strong>MARKETING</strong></h5>
                        </div>
                        <hr>
                        <div><span>Total de campanhas: <?php echo $total_marketing; ?></span></div>
                    </div>
                </a>
            </div>
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
    </div>
</body>
</html>
        

	

         