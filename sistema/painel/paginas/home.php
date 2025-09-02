<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once __DIR__ . '/../../conexao.php';

$hoje = date('Y-m-d');

// Fetch ranking of employees with most services in the last 12 months
try {
    $data_inicio = date('Y-m-d', strtotime('-12 months'));
    $data_fim = date('Y-m-d');
    $query = $pdo->prepare("
        SELECT u.id AS funcionario_id, u.nome AS funcionario_nome, COUNT(r.id) AS total_servicos
        FROM receber r
        INNER JOIN usuarios u ON r.funcionario = u.id
        WHERE r.id_conta = ?
          AND r.tipo = 'Serviço'
          AND r.pago = 'Sim'
          AND r.data_lanc BETWEEN ? AND ?
        GROUP BY u.id, u.nome
        ORDER BY total_servicos DESC
        LIMIT 5
    ");
    $query->execute([$id_conta, $data_inicio, $data_fim]);
    $ranking_funcionarios = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erro ao carregar ranking de funcionários: ' . $e->getMessage());
    $ranking_error = 'Erro ao carregar ranking de funcionários.';
}

// Fetch ranking of most used services in the last 12 months
try {
    $query = $pdo->prepare("
        SELECT s.id AS servico_id, s.nome AS servico_nome, COUNT(r.id) AS quantidade
        FROM receber r
        INNER JOIN servicos s ON r.servico = s.id
        WHERE r.id_conta = ?
          AND r.tipo = 'Serviço'
          AND r.pago = 'Sim'
          AND r.data_lanc BETWEEN ? AND ?
        GROUP BY s.id, s.nome
        ORDER BY quantidade DESC
        LIMIT 5
    ");
    $query->execute([$id_conta, $data_inicio, $data_fim]);
    $ranking_servicos = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erro ao carregar ranking de serviços: ' . $e->getMessage());
    $servicos_error = 'Erro ao carregar ranking de serviços.';
}

// Fetch birthday clients for today
try {
    $query = $pdo->prepare("
        SELECT id, nome, telefone
        FROM clientes
        WHERE id_conta = ?
          AND DAY(data_nasc) = DAY(CURDATE())
          AND MONTH(data_nasc) = MONTH(CURDATE())
    ");
    $query->execute([$id_conta]);
    $aniversariantes = $query->fetchAll(PDO::FETCH_ASSOC);
    $total_aniversariantes = count($aniversariantes);
} catch (PDOException $e) {
    error_log('Erro ao carregar aniversariantes: ' . $e->getMessage());
    $aniversariantes_error = 'Erro ao carregar aniversariantes.';
}

// Fetch clients awaiting fit-in today
try {
    $query = $pdo->prepare("
        SELECT 
            e.id,
            e.nome AS cliente_nome,
            u.nome AS profissional_nome,
            e.whatsapp AS cliente_telefone
        FROM encaixe e
        INNER JOIN usuarios u ON e.profissional = u.id
        WHERE e.id_conta = ? AND DATE(e.data) = ?
    ");
    $query->execute([$id_conta, $hoje]);
    $encaixes_hoje = $query->fetchAll(PDO::FETCH_ASSOC);
    $total_encaixes_hoje = count($encaixes_hoje);
} catch (PDOException $e) {
    error_log('Erro ao carregar encaixes de hoje: ' . $e->getMessage());
    $encaixes_error = 'Erro ao carregar encaixes de hoje.';
}

// Fetch ranking of most active clients in the last 12 months
try {
    $query = $pdo->prepare("
        SELECT c.id AS cliente_id, c.nome AS cliente_nome, COUNT(r.id) AS total_servicos
        FROM receber r
        INNER JOIN clientes c ON r.pessoa = c.id
        WHERE r.id_conta = ?
          AND r.tipo = 'Serviço'
          AND r.pago = 'Sim'
          AND r.data_lanc BETWEEN ? AND ?
        GROUP BY c.id, c.nome
        ORDER BY total_servicos DESC
        LIMIT 5
    ");
    $query->execute([$id_conta, $data_inicio, $data_fim]);
    $ranking_clientes_ativos = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erro ao carregar ranking de clientes ativos: ' . $e->getMessage());
    $clientes_ativos_error = 'Erro ao carregar ranking de clientes ativos.';
}
?>

<style>
    /* Estilos gerais */
    .main-page {
        padding: 20px;
        background-color: #ffffffff;
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
        background-color: rgb(149, 186, 224);
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
    .birthday { background-color: #ff6f61; } /* Coral para aniversariantes */
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
    /* Ranking Section (Profissionais, Serviços e Clientes Ativos) */
    .ranking-section, .servicos-section, .encaixes-section, .clientes-ativos-section {
        margin-top: 20px;
        display: flex;
        gap: 20px;
    }
    .ranking-list-container, .servicos-list-container, .encaixes-list-container, .clientes-ativos-list-container {
        width: 50%;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 15px;
    }
    .ranking-chart-container, .servicos-chart-container, .clientes-ativos-chart-container {
        width: 70%;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 15px;
    }
    .ranking-list, .servicos-list, .encaixes-list, .clientes-ativos-list {
        padding: 0;
    }
    .ranking-item, .servicos-item, .encaixes-item, .clientes-ativos-item {
        display: flex;
        align-items: center;
        padding: 12px;
        margin-bottom: 8px;
        border-radius: 8px;
        background: linear-gradient(135deg, #f5f7fa, #e4e7eb);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .ranking-item:hover, .servicos-item:hover, .encaixes-item:hover, .clientes-ativos-item:hover {
        transform: translateX(5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .ranking-item.position-1, .servicos-item.position-1, .clientes-ativos-item.position-1 {
        background: linear-gradient(135deg, #ffd700, #ffec80);
    }
    .ranking-item.position-2, .servicos-item.position-2, .clientes-ativos-item.position-2 {
        background: linear-gradient(135deg, #c0c0c0, #e0e0e0);
    }
    .ranking-item.position-3, .servicos-item.position-3, .clientes-ativos-item.position-3 {
        background: linear-gradient(135deg, #cd7f32, #e6b8a2);
    }
    .ranking-item .rank-icon, .servicos-item .rank-icon, .clientes-ativos-item .rank-icon {
        background-color: #4A90E2;
        color: #fff;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        font-weight: bold;
        margin-right: 10px;
    }
    .ranking-item p, .servicos-item p, .encaixes-item p, .clientes-ativos-item p {
        margin: 0;
        color: #333;
        font-size: 16px;
        flex: 1;
    }
    .ranking-item .services, .servicos-item .quantidade, .clientes-ativos-item .services {
        font-weight: bold;
        color: #007bff;
    }
    .encaixes-item .whatsapp-icon {
        color: #25D366;
        font-size: 24px;
        margin-left: 10px;
        cursor: pointer;
    }
    .no-ranking, .no-servicos, .no-encaixes, .no-clientes-ativos {
        text-align: center;
        color: #666;
        font-size: 16px;
        padding: 12px;
    }
    #rankingChart, #servicosChart, #clientesAtivosChart {
        width: 100%;
        height: 300px;
    }
    /* Media Query para Mobile (max-width: 768px) */
    @media (max-width: 768px) {
        .main-page {
            padding: 10px;
        }
        .col_3 .col-md-3 {
            width: 50%;
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
            width: 100%;
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
        .ranking-section, .servicos-section, .encaixes-section, .clientes-ativos-section {
            flex-direction: column;
            gap: 10px;
        }
        .ranking-list-container, .ranking-chart-container, .servicos-list-container, .servicos-chart-container, .encaixes-list-container, .clientes-ativos-list-container, .clientes-ativos-chart-container {
            width: 100%;
        }
        .ranking-item p, .servicos-item p, .encaixes-item p, .clientes-ativos-item p {
            font-size: 14px;
        }
        #rankingChart, #servicosChart, #clientesAtivosChart {
            height: 250px;
        }
    }
    /* Ajuste para telas muito pequenas (max-width: 480px) */
    @media (max-width: 480px) {
        .col_3 .col-md-3 {
            width: 100%;
        }
        .stats h5 {
            font-size: 18px;
        }
        .pie-title-center {
            width: 80px !important;
            height: 80px !important;
        }
        .agileinfo-cdr {
            display: none;
        }
        .ranking-item p, .servicos-item p, .encaixes-item p, .clientes-ativos-item p {
            font-size: 12px;
        }
        .ranking-item .rank-icon, .servicos-item .rank-icon, .clientes-ativos-item .rank-icon {
            width: 25px;
            height: 25px;
            line-height: 25px;
            font-size: 12px;
        }
        .encaixes-item .whatsapp-icon {
            font-size: 20px;
        }
        #rankingChart, #servicosChart, #clientesAtivosChart {
            height: 200px;
        }
    }
    /* Additional CSS for Pie Charts */
    #pieChartReceberTipo, #pieChartReceberPgto, #pieChartPagarTipo, #servicosChart, #clientesAtivosChart {
        width: 100%;
        height: 250px;
    }
    @media (max-width: 768px) {
        #pieChartReceberTipo, #pieChartReceberPgto, #pieChartPagarTipo, #servicosChart, #clientesAtivosChart {
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
        #pieChartReceberTipo, #pieChartReceberPgto, #pieChartPagarTipo, #servicosChart, #clientesAtivosChart {
            height: 180px;
        }
    }
</style>

<?php 
// Verificar se o usuário tem permissão de administrador
if (@$_SESSION['nivel_usuario'] != 'administrador') {
    echo "<script>window.location='agenda.php'</script>";
    exit();
}

$data_hoje = date('Y-m-d');
$data_ontem = date('Y-m-d', strtotime("-1 days", strtotime($data_hoje)));

$mes_atual = date('m');
$ano_atual = date('Y');
$data_inicio_mes = $ano_atual . "-" . $mes_atual . "-01";

if ($mes_atual == '4' || $mes_atual == '6' || $mes_atual == '9' || $mes_atual == '11') {
    $dia_final_mes = '30';
} else if ($mes_atual == '2') {
    $dia_final_mes = '28';
} else {
    $dia_final_mes = '31';
}

$data_final_mes = $ano_atual . "-" . $mes_atual . "-" . $dia_final_mes;

// Total de clientes
$query = $pdo->query("SELECT * FROM clientes WHERE id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_clientes = @count($res);

// Contas a pagar hoje
$query = $pdo->query("SELECT * FROM pagar WHERE data_venc = CURDATE() AND pago != 'Sim' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_pagar_hoje = @count($res);

// Contas a receber hoje
$query = $pdo->query("SELECT * FROM receber WHERE data_venc = CURDATE() AND pago != 'Sim' AND valor > 0 AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_receber_hoje = @count($res);

// Estoque baixo
$query = $pdo->query("SELECT * FROM produtos WHERE id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$estoque_baixo = 0;
if ($total_reg > 0) {
    for ($i = 0; $i < $total_reg; $i++) {
        $estoque = $res[$i]['estoque'];
        $nivel_estoque = $res[$i]['nivel_estoque'];
        if ($nivel_estoque >= $estoque) {
            $estoque_baixo += 1;
        }
    }
}

// Total de agendamentos hoje
$query = $pdo->query("SELECT * FROM agendamentos WHERE data = CURDATE() AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_agendamentos_hoje = @count($res);

// Agendamentos concluídos hoje
$query = $pdo->query("SELECT * FROM agendamentos WHERE data = CURDATE() AND status = 'Concluído' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_agendamentos_concluido_hoje = @count($res);

$porcentagemAgendamentos = ($total_agendamentos_hoje > 0 && $total_agendamentos_concluido_hoje > 0) 
    ? ($total_agendamentos_concluido_hoje / $total_agendamentos_hoje) * 100 
    : 0;

// Total de serviços hoje
$query = $pdo->query("SELECT * FROM receber WHERE data_lanc = CURDATE() AND tipo = 'Serviço' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_servicos_hoje = @count($res);

// Serviços pagos hoje
$query = $pdo->query("SELECT * FROM receber WHERE data_lanc = CURDATE() AND tipo = 'Serviço' AND pago = 'Sim' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_servicos_pago_hoje = @count($res);

$porcentagemServicos = ($total_servicos_hoje > 0 && $total_servicos_pago_hoje > 0) 
    ? ($total_servicos_pago_hoje / $total_servicos_hoje) * 100 
    : 0;

// Total de comissões do mês
$query = $pdo->query("SELECT * FROM pagar WHERE data_lanc >= '$data_inicio_mes' AND data_lanc <= '$data_final_mes' AND tipo = 'Comissão' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_comissoes_mes = @count($res);

// Comissões pagas no mês
$query = $pdo->query("SELECT * FROM pagar WHERE data_lanc >= '$data_inicio_mes' AND data_lanc <= '$data_final_mes' AND tipo = 'Comissão' AND pago = 'Sim' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_comissoes_mes_pagas = @count($res);

$porcentagemComissoes = ($total_comissoes_mes > 0 && $total_comissoes_mes_pagas > 0) 
    ? ($total_comissoes_mes_pagas / $total_comissoes_mes) * 100 
    : 0;

// Totalizar contas do dia
$total_debitos_dia = 0;
$query = $pdo->query("SELECT * FROM pagar WHERE data_pgto = CURDATE() AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if (@count($res) > 0) {
    for ($i = 0; $i < @count($res); $i++) {
        $total_debitos_dia += $res[$i]['valor'];
    }
}

$total_ganhos_dia = 0;
$query = $pdo->query("SELECT * FROM receber WHERE data_pgto = CURDATE() AND valor > 0 AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if (@count($res) > 0) {
    for ($i = 0; $i < @count($res); $i++) {
        $total_ganhos_dia += $res[$i]['valor'];
    }
}

$saldo_total_dia = $total_ganhos_dia - $total_debitos_dia;
$saldo_total_diaF = number_format($saldo_total_dia, 2, ',', '.');

$classe_saldo_dia = ($saldo_total_dia < 0) ? 'user1' : 'dollar2';

// Dados para o gráfico
$dados_meses_despesas = '';
$dados_meses_vendas = '';
$dados_meses_servicos = '';

for ($i = 1; $i <= 12; $i++) {
    $mes_atual = ($i < 10) ? '0' . $i : $i;

    if ($mes_atual == '4' || $mes_atual == '6' || $mes_atual == '9' || $mes_atual == '11') {
        $dia_final_mes = '30';
    } else if ($mes_atual == '2') {
        $dia_final_mes = '28';
    } else {
        $dia_final_mes = '31';
    }

    $data_mes_inicio_grafico = $ano_atual . "-" . $mes_atual . "-01";
    $data_mes_final_grafico = $ano_atual . "-" . $mes_atual . "-" . $dia_final_mes;

    // DESPESAS
    $total_mes_despesa = 0;
    $query = $pdo->query("SELECT * FROM pagar WHERE pago = 'Sim' AND tipo = 'Conta' AND data_pgto >= '$data_mes_inicio_grafico' AND data_pgto <= '$data_mes_final_grafico' AND id_conta = '$id_conta' ORDER BY id ASC");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $total_reg = @count($res);
    if ($total_reg > 0) {
        for ($i2 = 0; $i2 < $total_reg; $i2++) {
            $total_mes_despesa += $res[$i2]['valor'];
        }
    }
    $dados_meses_despesas .= $total_mes_despesa . '-';

    // VENDAS
    $total_mes_vendas = 0;
    $query = $pdo->query("SELECT * FROM receber WHERE pago = 'Sim' AND tipo = 'Produto' AND data_pgto >= '$data_mes_inicio_grafico' AND data_pgto <= '$data_mes_final_grafico' AND valor > 0 AND id_conta = '$id_conta' ORDER BY id ASC");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $total_reg = @count($res);
    if ($total_reg > 0) {
        for ($i2 = 0; $i2 < $total_reg; $i2++) {
            $total_mes_vendas += $res[$i2]['valor'];
        }
    }
    $dados_meses_vendas .= $total_mes_vendas . '-';

    // SERVIÇOS
    $total_mes_servicos = 0;
    $query = $pdo->query("SELECT * FROM receber WHERE pago = 'Sim' AND tipo = 'Serviço' AND data_pgto >= '$data_mes_inicio_grafico' AND data_pgto <= '$data_mes_final_grafico' AND id_conta = '$id_conta' ORDER BY id ASC");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $total_reg = @count($res);
    if ($total_reg > 0) {
        for ($i2 = 0; $i2 < $total_reg; $i2++) {
            $valor_do_serv = $res[$i2]['valor'];
            if ($valor_do_serv == 0) {
                $valor_do_serv = $res[$i2]['valor2'];
            }
            $total_mes_servicos += $valor_do_serv;
        }
    }
    $dados_meses_servicos .= $total_mes_servicos . '-';
}
?>

<input type="hidden" id="dados_grafico_despesa">
<input type="hidden" id="dados_grafico_venda">
<input type="hidden" id="dados_grafico_servico">
<div class="main-page">
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
        <div><i class=\"fa fa-info-circle\"></i> <b>Aviso: </b> Prezado Cliente, não identificamos o pagamento de sua última mensalidade. Clique <a href=\"https://www.gestao.skysee.com.br/pagar/{$id_m}\" target=\"_blank\" ><b style=\"color: #ffc341; font-size: 20px \" >AQUI</b></a> e regularize sua assinatura, caso contrário seu acesso ao sistema será desativado.</div>
        </div>";
    }

    if ($ativo_sistema == 'teste' && isset($id_m)) {
        if ($dias_rest == 0) {
            echo "<div style=\"background: #B22222; color: white; padding:10px; font-size:14px; margin-bottom:10px; width: 100%; border-radius: 10px;\">
            <div><i class=\"fa fa-info-circle\"></i> <b>Aviso: </b> Prezado Cliente, Seu período de teste do sistema termina <b>HOJE</b>. Clique <a href=\"https://www.gestao.skysee.com.br/pagar/{$id_m}\" target=\"_blank\" ><b style=\"color: #ffc341; font-size: 20px \" >AQUI</b></a> e assine nosso serviço.</div>
            </div>";
        } else {
            echo "<div style=\"background: #836FFF; color: white; padding:10px; font-size:14px; margin-bottom:10px; width: 100%; border-radius: 10px;\">
            <div><i class=\"fa fa-info-circle\"></i> <b>Aviso: </b> Prezado Cliente, Seu período de teste do sistema termina em <b>{$dias_rest} dias</b>. Clique <a href=\"https://www.gestao.skysee.com.br/pagar/{$id_m}\" target=\"_blank\" ><b style=\"color: #ffc341; font-size: 20px \" >AQUI</b></a> e assine nosso serviço.</div>
        </div>";
        }
    }
    ?>

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

    <?php
    // Query para Receber - Tipo (Assinatura, Serviço, Venda) no ano corrente
    $query_receber_tipo = $pdo->query("SELECT tipo, COUNT(*) as count FROM receber WHERE id_conta = '$id_conta' AND YEAR(data_lanc) = '$ano_atual' GROUP BY tipo");
    $res_receber_tipo = $query_receber_tipo->fetchAll(PDO::FETCH_ASSOC);
    $data_receber_tipo = [];
    foreach ($res_receber_tipo as $row) {
        if ($row['tipo'] == 'Comanda') {
            continue;
        }
        $data_receber_tipo[] = ['category' => $row['tipo'], 'value' => $row['count']];
    }

    // Query para Receber - Pgto (Pix, Cartão de Crédito, Cartão de Débito, Dinheiro) no ano corrente
    $query_receber_pgto = $pdo->query("SELECT pgto, COUNT(*) as count FROM receber WHERE id_conta = '$id_conta' AND pgto IS NOT NULL AND YEAR(data_lanc) = '$ano_atual' GROUP BY pgto");
    $res_receber_pgto = $query_receber_pgto->fetchAll(PDO::FETCH_ASSOC);
    $data_receber_pgto = [];
    foreach ($res_receber_pgto as $row) {
        if ($row['pgto'] == 'Assinatura') {
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
        <div class="col-md-6 stat" style="padding-left: 0;">
            <div class="content-top-1">
                <div class="card-header">
                    <h3>Receitas (<?php echo $ano_atual; ?>)</h3>
                </div>
                <div id="pieChartReceberTipo" style="height: 250px;"></div>
            </div>
        </div>
        
        <div class="col-md-6 stat">
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

    <!-- JavaScript para os Gráficos de Pizza e Barras -->
    <script>
    am4core.ready(function() {
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

        // Gráfico de Barras para Ranking de Profissionais
        var chartRanking = am4core.create("rankingChart", am4charts.XYChart);
        chartRanking.data = <?php echo json_encode($ranking_funcionarios); ?>;
        var categoryAxis = chartRanking.xAxes.push(new am4charts.CategoryAxis());
        categoryAxis.dataFields.category = "funcionario_nome";
        categoryAxis.renderer.labels.template.rotation = -45;
        categoryAxis.renderer.labels.template.horizontalCenter = "right";
        categoryAxis.renderer.labels.template.verticalCenter = "middle";
        categoryAxis.renderer.minGridDistance = 20;
        var valueAxis = chartRanking.yAxes.push(new am4charts.ValueAxis());
        valueAxis.title.text = "Número de Serviços";
        valueAxis.min = 0;
        var series = chartRanking.series.push(new am4charts.ColumnSeries());
        series.dataFields.valueY = "total_servicos";
        series.dataFields.categoryX = "funcionario_nome";
        series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/] serviços";
        series.columns.template.fill = am4core.color("#4A90E2");
        series.columns.template.strokeWidth = 0;
        var valueLabel = series.bullets.push(new am4charts.LabelBullet());
        valueLabel.label.text = "{valueY}";
        valueLabel.label.dy = -10;

        // Gráfico de Pizza para Ranking de Serviços
        var chartServicos = am4core.create("servicosChart", am4charts.PieChart);
        chartServicos.data = <?php echo json_encode($ranking_servicos); ?>;
        var pieSeriesServicos = chartServicos.series.push(new am4charts.PieSeries());
        pieSeriesServicos.dataFields.value = "quantidade";
        pieSeriesServicos.dataFields.category = "servico_nome";
        pieSeriesServicos.slices.template.stroke = am4core.color("#fff");
        pieSeriesServicos.slices.template.strokeWidth = 2;
        pieSeriesServicos.slices.template.strokeOpacity = 1;
        pieSeriesServicos.labels.template.text = "{value}";
        chartServicos.legend = new am4charts.Legend();
        chartServicos.legend.position = "bottom";

        // Gráfico de Barras para Ranking de Clientes Ativos
        var chartClientesAtivos = am4core.create("clientesAtivosChart", am4charts.XYChart);
        chartClientesAtivos.data = <?php echo json_encode($ranking_clientes_ativos); ?>;
        var categoryAxisClientes = chartClientesAtivos.xAxes.push(new am4charts.CategoryAxis());
        categoryAxisClientes.dataFields.category = "cliente_nome";
        categoryAxisClientes.renderer.labels.template.rotation = -45;
        categoryAxisClientes.renderer.labels.template.horizontalCenter = "right";
        categoryAxisClientes.renderer.labels.template.verticalCenter = "middle";
        categoryAxisClientes.renderer.minGridDistance = 20;
        var valueAxisClientes = chartClientesAtivos.yAxes.push(new am4charts.ValueAxis());
        valueAxisClientes.title.text = "Número de Serviços";
        valueAxisClientes.min = 0;
        var seriesClientes = chartClientesAtivos.series.push(new am4charts.ColumnSeries());
        seriesClientes.dataFields.valueY = "total_servicos";
        seriesClientes.dataFields.categoryX = "cliente_nome";
        seriesClientes.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/] serviços";
        seriesClientes.columns.template.fill = am4core.color("#4A90E2");
        seriesClientes.columns.template.strokeWidth = 0;
        var valueLabelClientes = seriesClientes.bullets.push(new am4charts.LabelBullet());
        valueLabelClientes.label.text = "{valueY}";
        valueLabelClientes.label.dy = -10;

        // Adicionar mensagem para gráficos vazios
        if (chartReceberTipo.data.length === 0) {
            document.getElementById("pieChartReceberTipo").innerHTML = "<p style='text-align:center; padding:20px;'>Nenhum dado disponível para <?php echo $ano_atual; ?></p>";
        }
        if (chartPagarTipo.data.length === 0) {
            document.getElementById("pieChartPagarTipo").innerHTML = "<p style='text-align:center; padding:20px;'>Nenhum dado disponível para <?php echo $ano_atual; ?></p>";
        }
        if (chartRanking.data.length === 0) {
            document.getElementById("rankingChart").innerHTML = "<p style='text-align:center; padding:20px;'>Nenhum dado disponível para o ranking.</p>";
        }
        if (chartServicos.data.length === 0) {
            document.getElementById("servicosChart").innerHTML = "<p style='text-align:center; padding:20px;'>Nenhum dado disponível para serviços.</p>";
        }
        if (chartClientesAtivos.data.length === 0) {
            document.getElementById("clientesAtivosChart").innerHTML = "<p style='text-align:center; padding:20px;'>Nenhum dado disponível para clientes ativos.</p>";
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
        <!-- Ranking dos Profissionais -->
        <div class="col-md-12 content-top-2 card ranking-section">
            <div class="card-header">
                <h3>Ranking de Profissionais (Últimos 12 Meses)</h3>
            </div>
            <div class="ranking-list-container">
                <?php if (isset($ranking_error)): ?>
                    <p class="no-ranking"><?php echo htmlspecialchars($ranking_error); ?></p>
                <?php elseif (empty($ranking_funcionarios)): ?>
                    <p class="no-ranking">Nenhum serviço registrado nos últimos 12 meses.</p>
                <?php else: ?>
                    <div class="ranking-list">
                        <?php foreach ($ranking_funcionarios as $index => $funcionario): ?>
                            <div class="ranking-item position-<?php echo $index + 1; ?>">
                                <span class="rank-icon"><?php echo $index + 1; ?></span>
                                <p><?php echo htmlspecialchars($funcionario['funcionario_nome']); ?> <span class="services"><?php echo $funcionario['total_servicos']; ?> serviços</span></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="ranking-chart-container">
                <div id="rankingChart" style="height: 300px;"></div>
            </div>
        </div>
        <!-- Ranking dos Serviços -->
        <div class="col-md-12 content-top-2 card servicos-section">
            <div class="card-header">
                <h3>Serviços Mais Usados (Últimos 12 Meses)</h3>
            </div>
            <div class="servicos-list-container">
                <?php if (isset($servicos_error)): ?>
                    <p class="no-servicos"><?php echo htmlspecialchars($servicos_error); ?></p>
                <?php elseif (empty($ranking_servicos)): ?>
                    <p class="no-servicos">Nenhum serviço registrado nos últimos 12 meses.</p>
                <?php else: ?>
                    <div class="servicos-list">
                        <?php foreach ($ranking_servicos as $index => $servico): ?>
                            <div class="servicos-item position-<?php echo $index + 1; ?>">
                                <span class="rank-icon"><?php echo $index + 1; ?></span>
                                <p><?php echo htmlspecialchars($servico['servico_nome']); ?> <span class="quantidade"><?php echo $servico['quantidade']; ?> usos</span></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="servicos-chart-container">
                <div id="servicosChart" style="height: 300px;"></div>
            </div>
        </div>
        <!-- Ranking de Clientes Mais Ativos -->
        <div class="col-md-12 content-top-2 card clientes-ativos-section">
            <div class="card-header">
                <h3>Ranking de Clientes Mais Ativos (Últimos 12 Meses)</h3>
            </div>
            <div class="clientes-ativos-list-container">
                <?php if (isset($clientes_ativos_error)): ?>
                    <p class="no-clientes-ativos"><?php echo htmlspecialchars($clientes_ativos_error); ?></p>
                <?php elseif (empty($ranking_clientes_ativos)): ?>
                    <p class="no-clientes-ativos">Nenhum cliente ativo registrado nos últimos 12 meses.</p>
                <?php else: ?>
                    <div class="clientes-ativos-list">
                        <?php foreach ($ranking_clientes_ativos as $index => $cliente): ?>
                            <div class="clientes-ativos-item position-<?php echo $index + 1; ?>">
                                <span class="rank-icon"><?php echo $index + 1; ?></span>
                                <p><?php echo htmlspecialchars($cliente['cliente_nome']); ?> <span class="services"><?php echo $cliente['total_servicos']; ?> serviços</span></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="clientes-ativos-chart-container">
                <div id="clientesAtivosChart" style="height: 300px;"></div>
            </div>
        </div>
        <!-- Clientes Aguardando Encaixe Hoje -->
        <div class="col-md-12 content-top-2 card encaixes-section" id="encaixes-hoje">
            <div class="card-header">
                <h3>Clientes Aguardando Encaixe Hoje</h3>
            </div>
            <div class="encaixes-list-container">
                <?php if (isset($encaixes_error)): ?>
                    <p class="no-encaixes"><?php echo htmlspecialchars($encaixes_error); ?></p>
                <?php elseif (empty($encaixes_hoje)): ?>
                    <p class="no-encaixes">Nenhum cliente aguardando encaixe hoje.</p>
                <?php else: ?>
                    <div class="encaixes-list">
                        <?php foreach ($encaixes_hoje as $encaixe): ?>
                            <div class="encaixes-item">
                                <p>
                                    <?php echo htmlspecialchars($encaixe['cliente_nome']); ?> 
                                    <?php echo htmlspecialchars($encaixe['cliente_telefone']); ?> 
                                    - Profissional: <?php echo htmlspecialchars($encaixe['profissional_nome']); ?>
                                    <a href="https://wa.me/55<?php echo preg_replace('/[ ()-]+/', '', $encaixe['cliente_telefone']); ?>?text=Olá%20<?php echo urlencode($encaixe['cliente_nome']); ?>,%20estamos%20confirmando%20seu%20encaixe%20para%20hoje!" 
                                       target="_blank" class="whatsapp-icon">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal para Aniversariantes -->
        <div class="modal fade" id="birthdayModal" tabindex="-1" role="dialog" aria-labelledby="birthdayModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="birthdayModalLabel">Aniversariantes do Dia</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="oferecer_presente">Oferecer Presente?</label>
                            <select class="form-control" id="oferecer_presente">
                                <option value="Não">Não</option>
                                <option value="Sim">Sim</option>
                            </select>
                        </div>
                        <div class="form-group" id="cupom_group" style="display: none;">
                            <label for="id_cupom">Selecionar Cupom</label>
                            <select class="form-control" id="id_cupom">
                                <option value="">Selecione um cupom</option>
                                <?php
                                $query = $pdo->prepare("SELECT id, codigo, valor, tipo_desconto FROM cupons WHERE id_conta = ? AND data_validade >= CURDATE() AND (usos_atuais < max_usos OR usos_atuais IS NULL)");
                                $query->execute([$id_conta]);
                                $cupons = $query->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($cupons as $cupom) {
                                    $desconto = $cupom['tipo_desconto'] === 'porcentagem' ? "{$cupom['valor']}%" : "R$" . number_format($cupom['valor'], 2, ',', '.');
                                    echo "<option value=\"{$cupom['id']}\">{$cupom['codigo']} ({$desconto})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <h6>Lista de Aniversariantes</h6>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Telefone</th>
                                    <th>Selecionar</th>
                                </tr>
                            </thead>
                            <tbody id="aniversariantes_list">
                                <?php foreach ($aniversariantes as $aniversariante): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($aniversariante['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($aniversariante['telefone']); ?></td>
                                        <td><input type="checkbox" class="select-cliente" data-id="<?php echo $aniversariante['id']; ?>" checked></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (isset($aniversariantes_error)): ?>
                            <p class="text-danger"><?php echo htmlspecialchars($aniversariantes_error); ?></p>
                        <?php elseif (empty($aniversariantes)): ?>
                            <p>Nenhum aniversariante hoje.</p>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="button" class="btn btn-primary" id="enviar_mensagens" <?php echo empty($aniversariantes) ? 'disabled' : ''; ?>>Enviar Mensagens</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>
    </div>

    <!-- for amcharts js -->
    <script src="js/amcharts.js"></script>
    <script src="js/serial.js"></script>
    <script src="js/export.min.js"></script>
    <link rel="stylesheet" href="css/export.css" type="text/css" media="all" />
    <script src="js/light.js"></script>
    <!-- for amcharts js -->

    <script src="js/index1.js"></script>

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
        title: "Produtos",
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
        linecolor: "#2196f3",
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

    $(document).ready(function () {
        $("#Linegraph").SimpleChart({
            ChartType: "Line",
            toolwidth: "50",
            toolheight: "25",
            axiscolor: "#333",
            textcolor: "#666",
            showlegends: true,
            data: [graphdata3, graphdata2, graphdata1],
            legendsize: "30",
            legendposition: 'bottom',
            xaxislabel: 'Meses',
            title: 'Demonstrativo Anual',
            yaxislabel: 'Valores R$'
        });
    });
    </script>
</div>