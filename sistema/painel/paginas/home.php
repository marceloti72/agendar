<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once __DIR__ . '/../../conexao.php';

$hoje = date('Y-m-d');

?>
<style>
    /* Estilos gerais */
    .main-page {
        padding: 20px;
        background-color: #e7edea;
        min-height: 100vh;
    }
    .widget, .stat, .content-top-2 {
        margin-bottom: 20px;
    }
    .r3_counter_box, .content-top-1 {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 15px;
        transition: transform 0.2s ease;
    }
    .r3_counter_box:hover, .content-top-1:hover {
        transform: translateY(-5px);
    }
    .icon-rounded {
        background-color:rgb(149, 186, 224);
        color: #fff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        line-height: 40px;
        font-size: 18px;
        text-align: center;
        margin-right: 10px;
    }
    .user1 { background-color: #e7032d; } /* Vermelho para débitos */
    .dollar2 { background-color: #8bcea6; } /* Verde para ganhos */
    .dollar1 { background-color: #ffca6e; } /* Amarelo para estoque */
    .stats h5 {
        font-size: 28px;
        margin: 0;
        color: #333;
    }
    .stats big {
        font-weight: bold;
    }
    hr {
        border: 0;
        border-top: 1px solid #eee;
        margin: 10px 0;
    }
    .top-content h5 {
        font-size: 18px;
        color: #555;
        margin: 0 0 5px;
    }
    .top-content label {
        font-size: 24px;
        color: #007bff;
        font-weight: bold;
    }
    .card-header h3 {
        font-size: 20px;
        color: #333;
        margin: 0;
        padding-bottom: 10px;
    }
    #Linegraph {
        width: 100% !important;
        height: 300px;
    }

    /* Aviso */
    .aviso {
        background: #ffc107;
        color: #333;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    /* Media Query para Mobile (max-width: 768px) */
    @media (max-width: 768px) {
        .main-page {
            padding: 10px;
        }
        .col_3 .col-md-3 {
            width: 50%; /* 2 por linha */
            float: left;
            padding: 5px;
        }
        .r3_counter_box {
            padding: 10px;
        }
        .icon-rounded {
            width: 30px;
            height: 30px;
            line-height: 30px;
            font-size: 14px;
        }
        .stats h5 {
            font-size: 20px;
        }
        .stats span {
            font-size: 12px;
        }
        .row .col-md-4 {
            width: 100%; /* Empilha verticalmente */
            padding: 5px;
        }
        .content-top-1 {
            padding: 10px;
            text-align: center;
        }
        .top-content {
            width: 100%;
            margin-bottom: 10px;
        }
        .top-content h5 {
            font-size: 16px;
        }
        .top-content label {
            font-size: 20px;
        }
        .top-content1 {
            width: 100%;
        }
        .pie-title-center {
            width: 100px !important;
            height: 100px !important;
            margin: 0 auto;
        }
        .card {
            padding: 10px;
        }
        .card-header h3 {
            font-size: 18px;
        }
        #Linegraph {
            height: 250px;
        }
        .aviso {
            font-size: 12px;
            padding: 8px;
        }
    }

    /* Ajuste para telas muito pequenas (max-width: 480px) */
    @media (max-width: 480px) {
        .col_3 .col-md-3 {
            width: 100%; /* 1 por linha */
        }
        .stats h5 {
            font-size: 18px;
        }
        .pie-title-center {
            width: 80px !important;
            height: 80px !important;
        }

        .agileinfo-cdr{
            display: none;
        }
    }
</style>
<?php 



//verificar se ele tem a permissão de estar nessa página
if(@$_SESSION['nivel_usuario'] != 'Administrador'){
	    echo "<script>window.location='agenda.php'</script>";
    }


$data_hoje = date('Y-m-d');
$data_ontem = date('Y-m-d', strtotime("-1 days",strtotime($data_hoje)));

$mes_atual = Date('m');
$ano_atual = Date('Y');
$data_inicio_mes = $ano_atual."-".$mes_atual."-01";

if($mes_atual == '4' || $mes_atual == '6' || $mes_atual == '9' || $mes_atual == '11'){
    $dia_final_mes = '30';
}else if($mes_atual == '2'){
    $dia_final_mes = '28';
}else{
    $dia_final_mes = '31';
}

$data_final_mes = $ano_atual."-".$mes_atual."-".$dia_final_mes;



$query = $pdo->query("SELECT * FROM clientes where id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_clientes = @count($res);

$query = $pdo->query("SELECT * FROM pagar where data_venc = curDate() and pago != 'Sim' and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_pagar_hoje = @count($res);


$query = $pdo->query("SELECT * FROM receber where data_venc = curDate() and pago != 'Sim' and valor > 0 and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_receber_hoje = @count($res);


$query = $pdo->query("SELECT * FROM produtos where id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$estoque_baixo = 0;
if($total_reg > 0){
    for($i=0; $i < $total_reg; $i++){
    foreach ($res[$i] as $key => $value){}
        $estoque = $res[$i]['estoque'];
        $nivel_estoque = $res[$i]['nivel_estoque'];

        if($nivel_estoque >= $estoque){
            $estoque_baixo += 1;
        }
    }
}


//totalizando agendamentos
$query = $pdo->query("SELECT * FROM agendamentos where data = curDate() and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_agendamentos_hoje = @count($res);

$query = $pdo->query("SELECT * FROM agendamentos where data = curDate() and status = 'Concluído' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_agendamentos_concluido_hoje = @count($res);


if($total_agendamentos_concluido_hoje > 0 and $total_agendamentos_hoje > 0){
    $porcentagemAgendamentos = ($total_agendamentos_concluido_hoje / $total_agendamentos_hoje) * 100;
}else{
    $porcentagemAgendamentos = 0;
}





//totalizando agendamentos pagos
$query = $pdo->query("SELECT * FROM receber where data_lanc = curDate() and tipo = 'Serviço' and id_conta = '$id_conta'  ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_servicos_hoje = @count($res);

$query = $pdo->query("SELECT * FROM receber where data_lanc = curDate() and tipo = 'Serviço' and pago = 'Sim' and id_conta = '$id_conta'  ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_servicos_pago_hoje = @count($res);


if($total_servicos_pago_hoje > 0 and $total_servicos_hoje > 0){
    $porcentagemServicos = ($total_servicos_pago_hoje / $total_servicos_hoje) * 100;
}else{
    $porcentagemServicos = 0;
}




//totalizando comissoes pagas mes
$query = $pdo->query("SELECT * FROM pagar where data_lanc >= '$data_inicio_mes' and data_lanc <= '$data_final_mes' and tipo = 'Comissão' and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_comissoes_mes = @count($res);

$query = $pdo->query("SELECT * FROM pagar where data_lanc >= '$data_inicio_mes' and data_lanc <= '$data_final_mes' and tipo = 'Comissão' and pago = 'Sim' and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_comissoes_mes_pagas = @count($res);


if($total_comissoes_mes_pagas > 0 and $total_comissoes_mes > 0){
    $porcentagemComissoes = ($total_comissoes_mes_pagas / $total_comissoes_mes) * 100;
}else{
    $porcentagemComissoes = 0;
}






//TOTALIZAR CONTAS DO DIA
$total_debitos_dia = 0;
$query = $pdo->query("SELECT * FROM pagar where data_pgto = curDate() and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
for($i=0; $i < @count($res); $i++){
    foreach ($res[$i] as $key => $value){}
        $total_debitos_dia += $res[$i]['valor'];
    }
}

$total_ganhos_dia = 0;
$query = $pdo->query("SELECT * FROM receber where data_pgto = curDate() and valor > 0 and id_conta = '$id_conta' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
for($i=0; $i < @count($res); $i++){
    foreach ($res[$i] as $key => $value){}
        $total_ganhos_dia += $res[$i]['valor'];
    }
}

$saldo_total_dia = $total_ganhos_dia - $total_debitos_dia;
$saldo_total_diaF = number_format($saldo_total_dia, 2, ',', '.');

if($saldo_total_dia < 0){
    $classe_saldo_dia = 'user1';
}else{
    $classe_saldo_dia = 'dollar2';
}






//dados para o gráfico
$dados_meses_despesas =  '';
$dados_meses_servicos =  '';
$dados_meses_vendas =  '';
        //ALIMENTAR DADOS PARA O GRÁFICO
        for($i=1; $i <= 12; $i++){

            if($i < 10){
                $mes_atual = '0'.$i;
            }else{
                $mes_atual = $i;
            }

        if($mes_atual == '4' || $mes_atual == '6' || $mes_atual == '9' || $mes_atual == '11'){
            $dia_final_mes = '30';
        }else if($mes_atual == '2'){
            $dia_final_mes = '28';
        }else{
            $dia_final_mes = '31';
        }


        $data_mes_inicio_grafico = $ano_atual."-".$mes_atual."-01";
        $data_mes_final_grafico = $ano_atual."-".$mes_atual."-".$dia_final_mes;


        //DESPESAS
        $total_mes_despesa = 0;
        $query = $pdo->query("SELECT * FROM pagar where pago = 'Sim' and tipo = 'Conta' and data_pgto >= '$data_mes_inicio_grafico' and data_pgto <= '$data_mes_final_grafico' and id_conta = '$id_conta' ORDER BY id asc");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $total_reg = @count($res);
        if($total_reg > 0){
            for($i2=0; $i2 < $total_reg; $i2++){
                foreach ($res[$i2] as $key => $value){}
            $total_mes_despesa +=  $res[$i2]['valor'];
        }
        }

        $dados_meses_despesas = $dados_meses_despesas. $total_mes_despesa. '-';





         //VENDAS
        $total_mes_vendas = 0;
        $query = $pdo->query("SELECT * FROM receber where pago = 'Sim' and tipo = 'Venda' and data_pgto >= '$data_mes_inicio_grafico' and data_pgto <= '$data_mes_final_grafico' and valor > 0 and id_conta = '$id_conta' ORDER BY id asc");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $total_reg = @count($res);
        if($total_reg > 0){
            for($i2=0; $i2 < $total_reg; $i2++){
                foreach ($res[$i2] as $key => $value){}
            $total_mes_vendas +=  $res[$i2]['valor'];
        }
        }

        $dados_meses_vendas = $dados_meses_vendas. $total_mes_vendas. '-';





        //SERVICOS
        $total_mes_servicos = 0;
        $query = $pdo->query("SELECT * FROM receber where pago = 'Sim' and tipo = 'Serviço' and data_pgto >= '$data_mes_inicio_grafico' and data_pgto <= '$data_mes_final_grafico' and id_conta = '$id_conta' ORDER BY id asc");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $total_reg = @count($res);
        if($total_reg > 0){
            for($i2=0; $i2 < $total_reg; $i2++){
                foreach ($res[$i2] as $key => $value){}
                    $valor_do_serv = $res[$i2]['valor'];
                    if($valor_do_serv == 0){
                        $valor_do_serv = $res[$i2]['valor2'];
                    }
            $total_mes_servicos += $valor_do_serv;
        }
        }

        $dados_meses_servicos = $dados_meses_servicos. $total_mes_servicos. '-';



    }



 ?>

  <input type="hidden" id="dados_grafico_despesa">
   <input type="hidden" id="dados_grafico_venda">
    <input type="hidden" id="dados_grafico_servico">
    <div class="main-page">
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

        // Verifica Ativação do Sistema
        $query4 = $pdo->query("SELECT * FROM config WHERE id = '$id_conta'");
        $res4 = $query4->fetchAll(PDO::FETCH_ASSOC);
        $ativo_sistema = $res4[0]['ativo'];
        $data_cad = $res4[0]['data_cadastro'];

        // Calcula Dias Restantes do Teste Grátis
        $data_inicio = new DateTime($data_cad);
        $data_fim = new DateTime($hoje);
        $dateInterval = $data_inicio->diff($data_fim);
        $dias = $dateInterval->days;
        $dias_rest = 7 - $dias;

        // Busca informações do cliente
        $query6 = $pdo2->query("SELECT * FROM clientes WHERE id_conta = '$id_conta'");
        $res6 = $query6->fetchAll(PDO::FETCH_ASSOC);
        $id_c = @$res6[0]['id'];

        // Busca informações da mensalidade
        $query7 = $pdo2->query("SELECT * FROM receber WHERE cliente = '$id_c' AND pago ='Não'");
        $res7 = $query7->fetchAll(PDO::FETCH_ASSOC);
        $id_m = isset($res7[0]['id']) ? $res7[0]['id'] : 0;

        // Exibe Avisos sobre Pagamento e Teste Grátis
        if ($ativo_sistema == '' && isset($id_m)) {
            echo "<div style=\"background: #B22222; color: white; padding:10px; font-size:14px; margin-bottom:10px; width: 100%; border-radius: 10px;\">
            <div><i class=\"fa fa-info-circle\"></i> <b>Aviso: </b> Prezado Cliente, não identificamos o pagamento de sua última mensalidade. Click <a href=\"https://www.gestao.skysee.com.br/pagar/{$id_m}\" target=\"_blank\" ><b style=\"color: #ffc341; font-size: 20px \" >AQUI</b></a> e regalarize sua assinatura, caso contário seu acesso ao sistema será desativado.</div>
            </div>";
        }

        if ($ativo_sistema == 'teste' && isset($id_m)) {
            if ($dias_rest == 0) {
                echo "<div style=\"background: #B22222; color: white; padding:10px; font-size:14px; margin-bottom:10px; width: 100%; border-radius: 10px; \">
                <div><i class=\"fa fa-info-circle\"></i> <b> Aviso: </b> Prezado Cliente, Seu período de teste do sistema termina <b>HOJE</b>. Click <a href=\"https://www.gestao.skysee.com.br/pagar/{$id_m}\" target=\"_blank\" ><b style=\"color: #ffc341; font-size: 20px \" >AQUI</b></a> e assine nosso serviço.</div>
                </div>";
            } else {
                echo "<div style=\"background: #836FFF; color: white; padding:10px; font-size:14px; margin-bottom:10px; width: 100%; border-radius: 10px; \">
                <div><i class=\"fa fa-info-circle\"></i> <b> Aviso: </b> Prezado Cliente, Seu período de teste do sistema termina em <b>{$dias_rest} dias</b>. Click <a href=\"https://www.gestao.skysee.com.br/pagar/{$id_m}\" target=\"_blank\" ><b style=\"color: #ffc341; font-size: 20px \" >AQUI</b></a> e assine nosso serviço.</div>
                </div>";
            }
        }?>

    <!-- <?php if($ativo_sistema == ''){ ?>
    <div class="aviso">
        <i class="fa fa-info-circle"></i> <b>Aviso:</b> Prezado Cliente, não identificamos o pagamento de sua última mensalidade, entre em contato conosco o mais rápido possível para regularizar o pagamento, caso contrário seu acesso ao sistema será desativado.
    </div>
    <?php } ?> -->

    <div class="col_3">
        <a href="clientes">
            <div class="col-md-3 widget widget1" style="border-radius: 12px;">
                <div class="r3_counter_box">
                    <i class="pull-left fa fa-users icon-rounded"></i>
                    <div class="stats">
                        <h5><strong><big><?php echo $total_clientes ?></big></strong></h5>
                    </div>
                    <hr>
                    <div align="center"><small>Total de Clientes</small></div>
                </div>
            </div>
        </a>

        <a href="pagar">
            <div class="col-md-3 widget widget1" style="border-radius: 12px;">
                <div class="r3_counter_box">
                    <i class="pull-left fa fa-money user1 icon-rounded"></i>
                    <div class="stats">
                        <h5><strong><big><?php echo $contas_pagar_hoje ?></big></strong></h5>
                    </div>
                    <hr>
                    <div align="center"><small>À Pagar Hoje</small></div>
                </div>
            </div>
        </a>

        <a href="receber">
            <div class="col-md-3 widget widget1" style="border-radius: 12px;">
                <div class="r3_counter_box">
                    <i class="pull-left fa fa-money dollar2 icon-rounded"></i>
                    <div class="stats">
                        <h5><strong><big><?php echo $contas_receber_hoje ?></big></strong></h5>
                    </div>
                    <hr>
                    <div align="center"><small>À Receber Hoje</small></div>
                </div>
            </div>
        </a>

        <a href="estoque">
            <div class="col-md-3 widget widget1" style="border-radius: 12px;">
                <div class="r3_counter_box">
                    <i class="pull-left fa fa-pie-chart dollar1 icon-rounded"></i>
                    <div class="stats">
                        <h5><strong><big><?php echo $estoque_baixo ?></big></strong></h5>
                    </div>
                    <hr>
                    <div align="center"><small>Estoque Baixo</small></div>
                </div>
            </div>
        </a>

        <div class="col-md-3 widget" style="border-radius: 12px;">
            <div class="r3_counter_box">
                <i class="pull-left fa fa-usd <?php echo $classe_saldo_dia ?> icon-rounded"></i>
                <div class="stats">                    
                    <h5><strong><?php echo @$saldo_total_diaF ?></strong></h5>
                </div>
                <hr>
                <div align="center"><small>Saldo do Dia</small></div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row" style="margin-top: 20px">
        <div class="col-md-4 stat stat2">
            <div class="content-top-1">
                <div class="col-md-7 top-content">
                    <h5>Agendamentos Dia</h5>
                    <label><?php echo $total_agendamentos_hoje ?>+</label>
                </div>
                <div class="col-md-5 top-content1">
                    <div id="demo-pie-1" class="pie-title-center" data-percent="<?php echo $porcentagemAgendamentos ?>">
                        <span class="pie-value"></span>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>

        <div class="col-md-4 stat">
            <div class="content-top-1">
                <div class="col-md-7 top-content">
                    <h5>Serviços Pagos Hoje</h5>
                    <label><?php echo $total_servicos_hoje ?>+</label>
                </div>
                <div class="col-md-5 top-content1">
                    <div id="demo-pie-2" class="pie-title-center" data-percent="<?php echo $porcentagemServicos ?>">
                        <span class="pie-value"></span>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>

        <div class="col-md-4 stat">
            <div class="content-top-1">
                <div class="col-md-7 top-content">
                    <h5>Comissões Pagas Mês</h5>
                    <label><?php echo $total_comissoes_mes ?>+</label>
                </div>
                <div class="col-md-5 top-content1">
                    <div id="demo-pie-3" class="pie-title-center" data-percent="<?php echo $porcentagemComissoes ?>">
                        <span class="pie-value"></span>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>



<!-- Additional CSS for Pie Charts -->
<style>
/* Ensure pie charts are responsive */
#pieChartReceberTipo, #pieChartReceberPgto, #pieChartPagarTipo {
    width: 100%;
    height: 250px;
}

/* Adjust for mobile */
@media (max-width: 768px) {
    #pieChartReceberTipo, #pieChartReceberPgto, #pieChartPagarTipo {
        height: 200px;
    }
    .content-top-1 {
        padding: 10px;
    }
    .card-header h3 {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    #pieChartReceberTipo, #pieChartReceberPgto, #pieChartPagarTipo {
        height: 180px;
    }
}
</style>





<?php
// Obter o ano corrente
$ano_atual = Date('Y');

// Query para Receber - Tipo (Assinatura, Serviço, Venda) no ano corrente
$query_receber_tipo = $pdo->query("SELECT tipo, COUNT(*) as count FROM receber WHERE id_conta = '$id_conta' AND YEAR(data_lanc) = '$ano_atual' GROUP BY tipo");
$res_receber_tipo = $query_receber_tipo->fetchAll(PDO::FETCH_ASSOC);
$data_receber_tipo = [];
foreach ($res_receber_tipo as $row) {
    if($row['tipo'] == 'Comanda'){
        continue;
    }
    $data_receber_tipo[] = ['category' => $row['tipo'], 'value' => $row['count']];
}

// Query para Receber - Pgto (Pix, Cartão de Crédito, Cartão de Débito, Dinheiro) no ano corrente
$query_receber_pgto = $pdo->query("SELECT pgto, COUNT(*) as count FROM receber WHERE id_conta = '$id_conta' AND pgto IS NOT NULL AND YEAR(data_lanc) = '$ano_atual' GROUP BY pgto");
$res_receber_pgto = $query_receber_pgto->fetchAll(PDO::FETCH_ASSOC);
$data_receber_pgto = [];
foreach ($res_receber_pgto as $row) {
    if($row['pgto'] == 'Assinatura'){
        continue;
    }
    $data_receber_pgto[] = ['category' => $row['pgto'], 'value' => $row['count']];
}

// Query para Pagar - Tipo (Comissão, Compra, Conta) no ano corrente
$query_pagar_tipo = $pdo->query("SELECT tipo, COUNT(*) as count FROM pagar WHERE id_conta = '$id_conta' AND YEAR(data_lanc) = '$ano_atual' GROUP BY tipo");
$res_pagar_tipo = $query_pagar_tipo->fetchAll(PDO::FETCH_ASSOC);
$data_pagar_tipo = [];
foreach ($res_pagar_tipo as $row) {
    $data_pagar_tipo[] = ['category' => $row['tipo'], 'value' => $row['count']];
}
?>

<!-- HTML para os Gráficos de Pizza -->
<div class="row" style="margin-top: 20px;">
    <div class="col-md-4 stat">
        <div class="content-top-1">
            <div class="card-header">
                <h3>Receitas (<?php echo $ano_atual; ?>)</h3>
            </div>
            <div id="pieChartReceberTipo" style="height: 250px;"></div>
        </div>
    </div>
    <div class="col-md-4 stat">
        <div class="content-top-1">
            <div class="card-header">
                <h3>Tipos de Pagamento (<?php echo $ano_atual; ?>)</h3>
            </div>
            <div id="pieChartReceberPgto" style="height: 250px;"></div>
        </div>
    </div>
    <div class="col-md-4 stat">
        <div class="content-top-1">
            <div class="card-header">
                <h3>Despesas (<?php echo $ano_atual; ?>)</h3>
            </div>
            <div id="pieChartPagarTipo" style="height: 250px;"></div>
        </div>
    </div>
</div>

<!-- Scripts para amCharts 4 -->
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

<!-- JavaScript para os Gráficos de Pizza -->
<script>
am4core.ready(function() {
    // Aplicar tema animado
    am4core.useTheme(am4themes_animated);

    // Gráfico de Pizza para Receber - Tipo
    var chartReceberTipo = am4core.create("pieChartReceberTipo", am4charts.PieChart);
    chartReceberTipo.data = <?php echo json_encode($data_receber_tipo); ?>;
    var pieSeriesReceberTipo = chartReceberTipo.series.push(new am4charts.PieSeries());
    pieSeriesReceberTipo.dataFields.value = "value";
    pieSeriesReceberTipo.dataFields.category = "category";
    pieSeriesReceberTipo.slices.template.stroke = am4core.color("#fff");
    pieSeriesReceberTipo.slices.template.strokeWidth = 2;
    pieSeriesReceberTipo.slices.template.strokeOpacity = 1;
    chartReceberTipo.legend = new am4charts.Legend();
    chartReceberTipo.legend.position = "bottom";

    // Gráfico de Pizza para Receber - Pgto
    var chartReceberPgto = am4core.create("pieChartReceberPgto", am4charts.PieChart);
    chartReceberPgto.data = <?php echo json_encode($data_receber_pgto); ?>;
    var pieSeriesReceberPgto = chartReceberPgto.series.push(new am4charts.PieSeries());
    pieSeriesReceberPgto.dataFields.value = "value";
    pieSeriesReceberPgto.dataFields.category = "category";
    pieSeriesReceberPgto.slices.template.stroke = am4core.color("#fff");
    pieSeriesReceberPgto.slices.template.strokeWidth = 2;
    pieSeriesReceberPgto.slices.template.strokeOpacity = 1;
    chartReceberPgto.legend = new am4charts.Legend();
    chartReceberPgto.legend.position = "bottom";

    // Gráfico de Pizza para Pagar - Tipo
    var chartPagarTipo = am4core.create("pieChartPagarTipo", am4charts.PieChart);
    chartPagarTipo.data = <?php echo json_encode($data_pagar_tipo); ?>;
    var pieSeriesPagarTipo = chartPagarTipo.series.push(new am4charts.PieSeries());
    pieSeriesPagarTipo.dataFields.value = "value";
    pieSeriesPagarTipo.dataFields.category = "category";
    pieSeriesPagarTipo.slices.template.stroke = am4core.color("#fff");
    pieSeriesPagarTipo.slices.template.strokeWidth = 2;
    pieSeriesPagarTipo.slices.template.strokeOpacity = 1;
    chartPagarTipo.legend = new am4charts.Legend();
    chartPagarTipo.legend.position = "bottom";

    // Adicionar mensagem para gráficos vazios
    if (chartReceberTipo.data.length === 0) {
        document.getElementById("pieChartReceberTipo").innerHTML = "<p style='text-align:center; padding:20px;'>Nenhum dado disponível para <?php echo $ano_atual; ?></p>";
    }
    if (chartReceberPgto.data.length === 0) {
        document.getElementById("pieChartReceberPgto").innerHTML = "<p style='text-align:center; padding:20px;'>Nenhum dado disponível para <?php echo $ano_atual; ?></p>";
    }
    if (chartPagarTipo.data.length === 0) {
        document.getElementById("pieChartPagarTipo").innerHTML = "<p style='text-align:center; padding:20px;'>Nenhum dado disponível para <?php echo $ano_atual; ?></p>";
    }
});
</script>



    <div class="row-one widgettable">
        <div class="col-md-12 content-top-2 card">
            <div class="agileinfo-cdr">
                <div class="card-header">
                    <h3>Demonstrativo Financeiro</h3>
                </div>
                <div id="Linegraph" style="width: 98%; height: 350px"></div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>


	
	<!-- for amcharts js -->
	<script src="js/amcharts.js"></script>
	<script src="js/serial.js"></script>
	<script src="js/export.min.js"></script>
	<link rel="stylesheet" href="css/export.css" type="text/css" media="all" />
	<script src="js/light.js"></script>
	<!-- for amcharts js -->

	<script  src="js/index1.js"></script>
	

</div>
<div class="clearfix"> </div>
</div>
<div class="clearfix"> </div>

</div>

</div>







<!-- for index page weekly sales java script -->
	<script src="js/SimpleChart.js"></script>
    <script>
        $('#dados_grafico_despesa').val('<?=$dados_meses_despesas?>'); 
        var dados = $('#dados_grafico_despesa').val();
        saldo_mes = dados.split('-'); 


         $('#dados_grafico_venda').val('<?=$dados_meses_vendas?>'); 
        var dados_venda = $('#dados_grafico_venda').val();
        saldo_mes_venda = dados_venda.split('-'); 


         $('#dados_grafico_servico').val('<?=$dados_meses_servicos?>'); 
        var dados_servico = $('#dados_grafico_servico').val();
        saldo_mes_servico = dados_servico.split('-'); 



        var graphdata1 = {
            linecolor: "#e32424",
            title: "Despesas",
            values: [
            { X: "Janeiro", Y: parseFloat(saldo_mes[0]) },
            { X: "Fevereiro", Y: parseFloat(saldo_mes[1]) },
            { X: "Março", Y: parseFloat(saldo_mes[2]) },
            { X: "Abril", Y: parseFloat(saldo_mes[3]) },
            { X: "Maio", Y: parseFloat(saldo_mes[4]) },
            { X: "Junho", Y: parseFloat(saldo_mes[5]) },
            { X: "Julho", Y: parseFloat(saldo_mes[6]) },
            { X: "Agosto", Y: parseFloat(saldo_mes[7]) },
            { X: "Setembro", Y: parseFloat(saldo_mes[8]) },
            { X: "Outubro", Y: parseFloat(saldo_mes[9]) },
            { X: "Novembro", Y: parseFloat(saldo_mes[10]) },
            { X: "Dezembro", Y: parseFloat(saldo_mes[11]) },
            
            ]
        };

        var graphdata2 = {
            linecolor: "#109447",
            title: "Vendas",
            values: [
            { X: "Janeiro", Y: parseFloat(saldo_mes_venda[0]) },
            { X: "Fevereiro", Y: parseFloat(saldo_mes_venda[1]) },
            { X: "Março", Y: parseFloat(saldo_mes_venda[2]) },
            { X: "Abril", Y: parseFloat(saldo_mes_venda[3]) },
            { X: "Maio", Y: parseFloat(saldo_mes_venda[4]) },
            { X: "Junho", Y: parseFloat(saldo_mes_venda[5]) },
            { X: "Julho", Y: parseFloat(saldo_mes_venda[6]) },
            { X: "Agosto", Y: parseFloat(saldo_mes_venda[7]) },
            { X: "Setembro", Y: parseFloat(saldo_mes_venda[8]) },
            { X: "Outubro", Y: parseFloat(saldo_mes_venda[9]) },
            { X: "Novembro", Y: parseFloat(saldo_mes_venda[10]) },
            { X: "Dezembro", Y: parseFloat(saldo_mes_venda[11]) },
            
            ]
        };


          var graphdata3 = {
            linecolor: "#0e248a",
            title: "Serviços",
            values: [
            { X: "Janeiro", Y: parseFloat(saldo_mes_servico[0]) },
            { X: "Fevereiro", Y: parseFloat(saldo_mes_servico[1]) },
            { X: "Março", Y: parseFloat(saldo_mes_servico[2]) },
            { X: "Abril", Y: parseFloat(saldo_mes_servico[3]) },
            { X: "Maio", Y: parseFloat(saldo_mes_servico[4]) },
            { X: "Junho", Y: parseFloat(saldo_mes_servico[5]) },
            { X: "Julho", Y: parseFloat(saldo_mes_servico[6]) },
            { X: "Agosto", Y: parseFloat(saldo_mes_servico[7]) },
            { X: "Setembro", Y: parseFloat(saldo_mes_servico[8]) },
            { X: "Outubro", Y: parseFloat(saldo_mes_servico[9]) },
            { X: "Novembro", Y: parseFloat(saldo_mes_servico[10]) },
            { X: "Dezembro", Y: parseFloat(saldo_mes_servico[11]) },
            
            ]
        };
       

      
       
        $(function () {          
           
           
            $("#Linegraph").SimpleChart({
                ChartType: "Line",
                toolwidth: "50",
                toolheight: "25",
                axiscolor: "#E6E6E6",
                textcolor: "#6E6E6E",
                showlegends: true,
                data: [graphdata3, graphdata2, graphdata1],
                legendsize: "30",
                legendposition: 'bottom',
                xaxislabel: 'Meses',
                title: '',
                yaxislabel: 'Totais R$',

            });
           
        });

    </script>
	<!-- //for index page weekly sales java script -->
	