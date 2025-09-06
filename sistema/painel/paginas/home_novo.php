<?php
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once __DIR__ . '/../../conexao.php';

// Redireciona se não for administrador
if (@$_SESSION['nivel_usuario'] != 'administrador') {
    echo "<script>window.location='agenda'</script>";
    exit();
}

// Otimização das consultas PHP
try {
    $hoje = date('Y-m-d');
    $data_inicio_ano = date('Y-m-d', strtotime('-12 months'));
    $ano_atual = date('Y');
    $mes_atual = date('m');
    $data_inicio_mes = "$ano_atual-$mes_atual-01";
    $data_final_mes = date('Y-m-t', strtotime($hoje));

    // Função para executar consultas de forma segura
    function executarQuery($pdo, $sql, $params = []) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // Cards de Resumo
    $total_clientes = executarQuery($pdo, "SELECT COUNT(id) FROM clientes WHERE id_conta = ?", [$id_conta])->fetchColumn();
    $contas_pagar_hoje = executarQuery($pdo, "SELECT COUNT(id) FROM pagar WHERE data_venc = ? AND pago != 'Sim' AND id_conta = ?", [$hoje, $id_conta])->fetchColumn();
    $contas_receber_hoje = executarQuery($pdo, "SELECT COUNT(id) FROM receber WHERE data_venc = ? AND pago != 'Sim' AND valor > 0 AND id_conta = ?", [$hoje, $id_conta])->fetchColumn();
    $estoque_baixo = executarQuery($pdo, "SELECT COUNT(id) FROM produtos WHERE estoque <= nivel_estoque AND id_conta = ?", [$id_conta])->fetchColumn();

    // Saldo do Dia
    $total_debitos_dia = executarQuery($pdo, "SELECT SUM(valor) FROM pagar WHERE data_pgto = ? AND id_conta = ?", [$hoje, $id_conta])->fetchColumn() ?: 0;
    $total_ganhos_dia = executarQuery($pdo, "SELECT SUM(valor) FROM receber WHERE data_pgto = ? AND valor > 0 AND id_conta = ?", [$hoje, $id_conta])->fetchColumn() ?: 0;
    $saldo_total_dia = $total_ganhos_dia - $total_debitos_dia;
    $saldo_total_diaF = 'R$ ' . number_format($saldo_total_dia, 2, ',', '.');
    $classe_saldo_dia = ($saldo_total_dia < 0) ? 'text-red-500' : 'text-green-500';

    // Indicadores de Progresso
    $total_agendamentos_hoje = executarQuery($pdo, "SELECT COUNT(id) FROM agendamentos WHERE data = ? AND id_conta = ?", [$hoje, $id_conta])->fetchColumn();
    $total_agendamentos_concluido_hoje = executarQuery($pdo, "SELECT COUNT(id) FROM agendamentos WHERE data = ? AND status = 'Concluído' AND id_conta = ?", [$hoje, $id_conta])->fetchColumn();
    $porcentagemAgendamentos = ($total_agendamentos_hoje > 0) ? ($total_agendamentos_concluido_hoje / $total_agendamentos_hoje) * 100 : 0;

    $total_servicos_hoje = executarQuery($pdo, "SELECT COUNT(id) FROM receber WHERE data_lanc = ? AND tipo = 'Serviço' AND id_conta = ?", [$hoje, $id_conta])->fetchColumn();
    $total_servicos_pago_hoje = executarQuery($pdo, "SELECT COUNT(id) FROM receber WHERE data_lanc = ? AND tipo = 'Serviço' AND pago = 'Sim' AND id_conta = ?", [$hoje, $id_conta])->fetchColumn();
    $porcentagemServicos = ($total_servicos_hoje > 0) ? ($total_servicos_pago_hoje / $total_servicos_hoje) * 100 : 0;

    $total_comissoes_mes = executarQuery($pdo, "SELECT COUNT(id) FROM pagar WHERE data_lanc BETWEEN ? AND ? AND tipo = 'Comissão' AND id_conta = ?", [$data_inicio_mes, $data_final_mes, $id_conta])->fetchColumn();
    $total_comissoes_mes_pagas = executarQuery($pdo, "SELECT COUNT(id) FROM pagar WHERE data_lanc BETWEEN ? AND ? AND tipo = 'Comissão' AND pago = 'Sim' AND id_conta = ?", [$data_inicio_mes, $data_final_mes, $id_conta])->fetchColumn();
    $porcentagemComissoes = ($total_comissoes_mes > 0) ? ($total_comissoes_mes_pagas / $total_comissoes_mes) * 100 : 0;

    // Rankings
    $params_ranking = [$id_conta, $data_inicio_ano, $hoje];
    $ranking_funcionarios = executarQuery($pdo, "SELECT u.nome, COUNT(r.id) AS total FROM receber r JOIN usuarios u ON r.funcionario = u.id WHERE r.id_conta = ? AND r.tipo = 'Serviço' AND r.pago = 'Sim' AND r.data_lanc BETWEEN ? AND ? GROUP BY u.id, u.nome ORDER BY total DESC LIMIT 5", $params_ranking)->fetchAll(PDO::FETCH_ASSOC);
    $ranking_servicos = executarQuery($pdo, "SELECT s.nome, COUNT(r.id) AS total FROM receber r JOIN servicos s ON r.servico = s.id WHERE r.id_conta = ? AND r.tipo = 'Serviço' AND r.pago = 'Sim' AND r.data_lanc BETWEEN ? AND ? GROUP BY s.id, s.nome ORDER BY total DESC LIMIT 5", $params_ranking)->fetchAll(PDO::FETCH_ASSOC);
    $ranking_clientes = executarQuery($pdo, "SELECT c.nome, COUNT(r.id) AS total FROM receber r JOIN clientes c ON r.pessoa = c.id WHERE r.id_conta = ? AND r.tipo = 'Serviço' AND r.pago = 'Sim' AND r.data_lanc BETWEEN ? AND ? GROUP BY c.id, c.nome ORDER BY total DESC LIMIT 5", $params_ranking)->fetchAll(PDO::FETCH_ASSOC);
    
    // Gráficos de Pizza
    $data_receber_tipo = executarQuery($pdo, "SELECT tipo AS category, COUNT(*) AS value FROM receber WHERE id_conta = ? AND YEAR(data_lanc) = ? AND tipo != 'Comanda' GROUP BY tipo", [$id_conta, $ano_atual])->fetchAll(PDO::FETCH_ASSOC);
    $data_pagar_tipo = executarQuery($pdo, "SELECT tipo AS category, COUNT(*) AS value FROM pagar WHERE id_conta = ? AND YEAR(data_lanc) = ? GROUP BY tipo", [$id_conta, $ano_atual])->fetchAll(PDO::FETCH_ASSOC);

    // Encaixes de Hoje
    $encaixes_hoje = executarQuery($pdo, "SELECT e.nome, u.nome AS profissional FROM encaixe e JOIN usuarios u ON e.profissional = u.id WHERE e.id_conta = ? AND DATE(e.data) = ?", [$id_conta, $hoje])->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Tratamento de erro simplificado
    die("Erro de banco de dados: " . $e->getMessage());
}
?>
<style>
/* Add this to your stylesheet */
.chart-container {
    max-height: 100%;
    max-width: 100%;
    overflow: hidden;
}

/* Ensure the card itself doesn't overflow */
.bg-white {
    overflow: hidden;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <a href="clientes" class="bg-white p-5 rounded-xl shadow-md hover:shadow-lg transition-shadow flex items-center space-x-4">
            <div class="bg-blue-100 text-blue-500 rounded-full p-3"><i class="fa fa-users fa-lg"></i></div>
            <div><p class="text-gray-500 text-sm">Total de Clientes</p><p class="text-2xl font-bold text-gray-800"><?= $total_clientes ?></p></div>
        </a>
        <a href="pagar" class="bg-white p-5 rounded-xl shadow-md hover:shadow-lg transition-shadow flex items-center space-x-4">
            <div class="bg-red-100 text-red-500 rounded-full p-3"><i class="fa fa-arrow-circle-down fa-lg"></i></div>
            <div><p class="text-gray-500 text-sm">A Pagar Hoje</p><p class="text-2xl font-bold text-gray-800"><?= $contas_pagar_hoje ?></p></div>
        </a>
        <a href="receber" class="bg-white p-5 rounded-xl shadow-md hover:shadow-lg transition-shadow flex items-center space-x-4">
            <div class="bg-green-100 text-green-500 rounded-full p-3"><i class="fa fa-arrow-circle-up fa-lg"></i></div>
            <div><p class="text-gray-500 text-sm">A Receber Hoje</p><p class="text-2xl font-bold text-gray-800"><?= $contas_receber_hoje ?></p></div>
        </a>
        <a href="estoque" class="bg-white p-5 rounded-xl shadow-md hover:shadow-lg transition-shadow flex items-center space-x-4">
            <div class="bg-yellow-100 text-yellow-500 rounded-full p-3"><i class="fa fa-box-open fa-lg"></i></div>
            <div><p class="text-gray-500 text-sm">Estoque Baixo</p><p class="text-2xl font-bold text-gray-800"><?= $estoque_baixo ?></p></div>
        </a>
        <div class="bg-white p-5 rounded-xl shadow-md flex items-center space-x-4">
            <div class="bg-indigo-100 text-indigo-500 rounded-full p-3"><i class="fa fa-balance-scale fa-lg"></i></div>
            <div><p class="text-gray-500 text-sm">Saldo do Dia</p><p class="text-2xl font-bold <?= $classe_saldo_dia ?>"><?= $saldo_total_diaF ?></p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-5 rounded-xl shadow-md flex items-center space-x-4 overflow-hidden">
        <div id="chart-agendamentos" class="flex-shrink-0 w-24 h-24"></div>
        <div class="flex-grow">
            <h4 class="font-semibold text-gray-600">Agendamentos do Dia</h4>
            <p class="text-2xl font-bold text-gray-800"><?= $total_agendamentos_hoje ?></p>
            <p class="text-sm text-gray-500"><?= $total_agendamentos_concluido_hoje ?> concluídos</p>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-md flex items-center space-x-4 overflow-hidden">
        <div id="chart-servicos" class="flex-shrink-0 w-24 h-24"></div>
        <div class="flex-grow">
            <h4 class="font-semibold text-gray-600">Serviços de Hoje</h4>
            <p class="text-2xl font-bold text-gray-800"><?= $total_servicos_hoje ?></p>
            <p class="text-sm text-gray-500"><?= $total_servicos_pago_hoje ?> pagos</p>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-md flex items-center space-x-4 overflow-hidden">
        <div id="chart-comissoes" class="flex-shrink-0 w-24 h-24"></div>
        <div class="flex-grow">
            <h4 class="font-semibold text-gray-600">Comissões do Mês</h4>
            <p class="text-2xl font-bold text-gray-800"><?= $total_comissoes_mes ?></p>
            <p class="text-sm text-gray-500"><?= $total_comissoes_mes_pagas ?> pagas</p>
        </div>
    </div>
</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fa-solid fa-ranking-star mr-2 text-yellow-500"></i>Ranking de Profissionais</h3>
        <div id="chart-ranking-profissionais" class="h-64 chart-container"></div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fa-solid fa-star mr-2 text-blue-500"></i>Serviços Mais Usados</h3>
        <div id="chart-ranking-servicos" class="h-64 chart-container"></div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fa-solid fa-users mr-2 text-green-500"></i>Clientes Mais Ativos</h3>
        <div id="chart-ranking-clientes" class="h-64 chart-container"></div>
    </div>
</div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-3 bg-white p-6 rounded-xl shadow-md">
             <h3 class="text-lg font-semibold text-gray-800 mb-4">Visão Geral Financeira de <?= $ano_atual ?></h3>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                     <h4 class="text-center text-gray-600 font-medium mb-2">Distribuição de Receitas</h4>
                     <div id="pieChartReceitas" class="h-64"></div>
                </div>
                 <div>
                     <h4 class="text-center text-gray-600 font-medium mb-2">Distribuição de Despesas</h4>
                     <div id="pieChartDespesas" class="h-64"></div>
                </div>
            </div>
        </div>
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md" id="encaixes-hoje">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fa-solid fa-hourglass-half mr-2 text-cyan-500"></i>Aguardando Encaixe Hoje</h3>
             <div class="space-y-3 max-h-80 overflow-y-auto">
                 <?php if (empty($encaixes_hoje)): ?>
                    <div class="flex flex-col items-center justify-center h-full text-gray-500 py-10">
                        <i class="fa-regular fa-calendar-check text-4xl mb-2"></i>
                        <p>Nenhum encaixe para hoje.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($encaixes_hoje as $item): ?>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['nome']) ?></p>
                        <p class="text-sm text-gray-600">Profissional: <span class="font-medium"><?= htmlspecialchars($item['profissional']) ?></span></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
             </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Função para criar os gráficos radiais de progresso
    function createRadialChart(chartId, seriesData, color) {
        const options = {
            chart: { type: 'radialBar', sparkline: { enabled: true } }, // <--- ALTURA REMOVIDA
            series: [seriesData],
            plotOptions: {
                radialBar: {
                    hollow: { size: '65%' },
                    track: { background: '#e0e0e0' },
                    dataLabels: {
                        name: { show: false },
                        value: { fontSize: '20px', fontWeight: 600, offsetY: 8, color: color }
                    }
                }
            },
            colors: [color],
            stroke: { lineCap: 'round' }
        };
        new ApexCharts(document.querySelector(chartId), options).render();
    }

    // Função para criar gráficos de pizza (donut)
    function createPieChart(chartId, seriesData, labels) {
         if (!seriesData || seriesData.length === 0) {
            document.querySelector(chartId).innerHTML = `<div class="flex items-center justify-center h-full text-gray-500">Sem dados.</div>`;
            return;
        }
        const options = {
            chart: { type: 'donut', height: '100%' },
            series: seriesData,
            labels: labels,
            legend: { position: 'bottom' },
            responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: 'bottom' } } }]
        };
        new ApexCharts(document.querySelector(chartId), options).render();
    }

    // Função para criar gráficos de barras horizontais para os rankings
function createRankingChart(chartId, seriesData, categories, color) {
    if (!seriesData || seriesData.length === 0) {
        document.querySelector(chartId).innerHTML = `<div class="flex items-center justify-center h-full text-gray-500">Sem dados para o ranking.</div>`;
        return;
    }
    const options = {
        chart: { 
            type: 'bar', 
            height: 240, // Explicitly set height to fit within h-64 (256px - padding)
            toolbar: { show: false } 
        },
        series: [{ name: 'Total', data: seriesData }],
        plotOptions: { 
            bar: { 
                horizontal: true, 
                barHeight: '60%', 
                borderRadius: 4, 
                distributed: true 
            } 
        },
        dataLabels: { 
            enabled: true, 
            style: { colors: ['#fff'] }, 
            offsetX: -25 
        },
        xaxis: { 
            categories: categories, 
            labels: { show: false } 
        },
        yaxis: { 
            labels: { 
                show: true, 
                style: { colors: '#333', fontSize: '12px' },
                trim: true,
                maxWidth: 110,
                formatter: function (val) {
                    if (typeof val === 'string' && val.length > 15) {
                        return val.slice(0, 15) + '...';
                    }
                    return val;
                }
            } 
        },
        grid: { 
            show: false, 
            padding: { left: 10, right: 10, top: 10, bottom: 10 } // Reduced padding
        },
        colors: [color],
        legend: { show: false },
        tooltip: { 
            y: { 
                formatter: (val, { dataPointIndex, w }) => {
                    const fullCategoryName = w.globals.labels[dataPointIndex];
                    return `${fullCategoryName}: ${val}`;
                }
            } 
        }
    };
    new ApexCharts(document.querySelector(chartId), options).render();
}


    // Renderizar Gráficos Radiais
    createRadialChart('#chart-agendamentos', <?= round($porcentagemAgendamentos) ?>, '#3b82f6');
    createRadialChart('#chart-servicos', <?= round($porcentagemServicos) ?>, '#16a34a');
    createRadialChart('#chart-comissoes', <?= round($porcentagemComissoes) ?>, '#9333ea');

    // Renderizar Gráficos de Pizza
    const receitasData = <?= json_encode(array_column($data_receber_tipo, 'value')) ?>;
    const receitasLabels = <?= json_encode(array_column($data_receber_tipo, 'category')) ?>;
    createPieChart('#pieChartReceitas', receitasData, receitasLabels);
    
    const despesasData = <?= json_encode(array_column($data_pagar_tipo, 'value')) ?>;
    const despesasLabels = <?= json_encode(array_column($data_pagar_tipo, 'category')) ?>;
    createPieChart('#pieChartDespesas', despesasData, despesasLabels);

    // Renderizar Gráficos de Ranking
    createRankingChart('#chart-ranking-profissionais', 
        <?= json_encode(array_column($ranking_funcionarios, 'total')) ?>,
        <?= json_encode(array_column($ranking_funcionarios, 'nome')) ?>, '#f59e0b');

    createRankingChart('#chart-ranking-servicos', 
        <?= json_encode(array_column($ranking_servicos, 'total')) ?>,
        <?= json_encode(array_column($ranking_servicos, 'nome')) ?>, '#3b82f6');

    createRankingChart('#chart-ranking-clientes', 
        <?= json_encode(array_column($ranking_clientes, 'total')) ?>,
        <?= json_encode(array_column($ranking_clientes, 'nome')) ?>, '#16a34a');
});
</script>