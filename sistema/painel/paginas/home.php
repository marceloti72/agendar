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

$hoje = date('Y-m-d');
$data_inicio_ano = date('Y-m-d', strtotime('-12 months'));

// Busca dados para os cards
try {
    $stmt = $pdo->prepare("SELECT COUNT(id) FROM clientes WHERE id_conta = ?");
    $stmt->execute([$id_conta]);
    $total_clientes = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(id) FROM pagar WHERE data_venc = CURDATE() AND pago != 'Sim' AND id_conta = ?");
    $stmt->execute([$id_conta]);
    $contas_pagar_hoje = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(id) FROM receber WHERE data_venc = CURDATE() AND pago != 'Sim' AND valor > 0 AND id_conta = ?");
    $stmt->execute([$id_conta]);
    $contas_receber_hoje = $stmt->fetchColumn();
    
    // Estoque Baixo
    $stmt = $pdo->prepare("SELECT COUNT(id) FROM produtos WHERE estoque <= nivel_estoque AND id_conta = ?");
    $stmt->execute([$id_conta]);
    $estoque_baixo = $stmt->fetchColumn();

    // Saldo do Dia
    $stmt_pagar = $pdo->prepare("SELECT SUM(valor) FROM pagar WHERE data_pgto = CURDATE() AND id_conta = ?");
    $stmt_pagar->execute([$id_conta]);
    $total_debitos_dia = $stmt_pagar->fetchColumn() ?: 0;
    
    $stmt_receber = $pdo->prepare("SELECT SUM(valor) FROM receber WHERE data_pgto = CURDATE() AND valor > 0 AND id_conta = ?");
    $stmt_receber->execute([$id_conta]);
    $total_ganhos_dia = $stmt_receber->fetchColumn() ?: 0;
    
    $saldo_total_dia = $total_ganhos_dia - $total_debitos_dia;
    $saldo_total_diaF = 'R$ ' . number_format($saldo_total_dia, 2, ',', '.');
    $classe_saldo_dia = ($saldo_total_dia < 0) ? 'text-red-500' : 'text-green-500';

} catch (PDOException $e) {
    error_log("Erro ao buscar cards: " . $e->getMessage());
    // Tratar erro, talvez definindo valores padrão
    $total_clientes = $contas_pagar_hoje = $contas_receber_hoje = $estoque_baixo = 0;
    $saldo_total_diaF = 'Erro';
    $classe_saldo_dia = 'text-gray-500';
}

// Rankings (Funcionários, Serviços, Clientes)
function fetchRanking($pdo, $query, $params) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro no Ranking: " . $e->getMessage());
        return [];
    }
}

$params_ranking = [$id_conta, $data_inicio_ano, $hoje];
$ranking_funcionarios = fetchRanking($pdo, "SELECT u.nome AS nome, COUNT(r.id) AS total FROM receber r JOIN usuarios u ON r.funcionario = u.id WHERE r.id_conta = ? AND r.tipo = 'Serviço' AND r.pago = 'Sim' AND r.data_lanc BETWEEN ? AND ? GROUP BY u.id, u.nome ORDER BY total DESC LIMIT 5", $params_ranking);
$ranking_servicos = fetchRanking($pdo, "SELECT s.nome AS nome, COUNT(r.id) AS total FROM receber r JOIN servicos s ON r.servico = s.id WHERE r.id_conta = ? AND r.tipo = 'Serviço' AND r.pago = 'Sim' AND r.data_lanc BETWEEN ? AND ? GROUP BY s.id, s.nome ORDER BY total DESC LIMIT 5", $params_ranking);
$ranking_clientes = fetchRanking($pdo, "SELECT c.nome AS nome, COUNT(r.id) AS total FROM receber r JOIN clientes c ON r.pessoa = c.id WHERE r.id_conta = ? AND r.tipo = 'Serviço' AND r.pago = 'Sim' AND r.data_lanc BETWEEN ? AND ? GROUP BY c.id, c.nome ORDER BY total DESC LIMIT 5", $params_ranking);

// Encaixes de Hoje
$encaixes_hoje = fetchRanking($pdo, "SELECT e.nome, u.nome AS profissional FROM encaixe e JOIN usuarios u ON e.profissional = u.id WHERE e.id_conta = ? AND DATE(e.data) = ?", [$id_conta, $hoje]);
?>

<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <a href="clientes" class="bg-white p-5 rounded-xl shadow-md hover:shadow-lg transition-shadow flex items-center space-x-4">
            <div class="bg-blue-100 text-blue-500 rounded-full p-3">
                <i class="fa fa-users fa-lg"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Total de Clientes</p>
                <p class="text-2xl font-bold text-gray-800"><?= $total_clientes ?></p>
            </div>
        </a>

        <a href="pagar" class="bg-white p-5 rounded-xl shadow-md hover:shadow-lg transition-shadow flex items-center space-x-4">
            <div class="bg-red-100 text-red-500 rounded-full p-3">
                <i class="fa fa-money-bill-wave fa-lg"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">A Pagar Hoje</p>
                <p class="text-2xl font-bold text-gray-800"><?= $contas_pagar_hoje ?></p>
            </div>
        </a>
        
        <a href="receber" class="bg-white p-5 rounded-xl shadow-md hover:shadow-lg transition-shadow flex items-center space-x-4">
            <div class="bg-green-100 text-green-500 rounded-full p-3">
                <i class="fa fa-hand-holding-dollar fa-lg"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">A Receber Hoje</p>
                <p class="text-2xl font-bold text-gray-800"><?= $contas_receber_hoje ?></p>
            </div>
        </a>

        <a href="estoque" class="bg-white p-5 rounded-xl shadow-md hover:shadow-lg transition-shadow flex items-center space-x-4">
            <div class="bg-yellow-100 text-yellow-500 rounded-full p-3">
                <i class="fa fa-box-open fa-lg"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Estoque Baixo</p>
                <p class="text-2xl font-bold text-gray-800"><?= $estoque_baixo ?></p>
            </div>
        </a>
        
        <div class="bg-white p-5 rounded-xl shadow-md flex items-center space-x-4">
            <div class="bg-indigo-100 text-indigo-500 rounded-full p-3">
                <i class="fa fa-balance-scale fa-lg"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Saldo do Dia</p>
                <p class="text-2xl font-bold <?= $classe_saldo_dia ?>"><?= $saldo_total_diaF ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fa-solid fa-ranking-star mr-2 text-yellow-500"></i>Ranking de Profissionais</h3>
            <div class="space-y-3">
                <?php if (empty($ranking_funcionarios)): ?>
                    <p class="text-center text-gray-500 py-4">Nenhum dado disponível.</p>
                <?php else: ?>
                    <?php foreach($ranking_funcionarios as $index => $item): ?>
                    <div class="flex items-center space-x-3">
                        <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-bold text-white bg-slate-500"><?= $index + 1 ?></span>
                        <p class="text-gray-700 flex-grow"><?= htmlspecialchars($item['nome']) ?></p>
                        <span class="font-semibold text-blue-600"><?= $item['total'] ?> serviços</span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fa-solid fa-star mr-2 text-blue-500"></i>Serviços Mais Usados</h3>
            <div class="space-y-3">
                <?php if (empty($ranking_servicos)): ?>
                    <p class="text-center text-gray-500 py-4">Nenhum dado disponível.</p>
                <?php else: ?>
                    <?php foreach($ranking_servicos as $index => $item): ?>
                    <div class="flex items-center space-x-3">
                        <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-bold text-white bg-slate-500"><?= $index + 1 ?></span>
                        <p class="text-gray-700 flex-grow"><?= htmlspecialchars($item['nome']) ?></p>
                        <span class="font-semibold text-green-600"><?= $item['total'] ?> vezes</span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fa-solid fa-users mr-2 text-green-500"></i>Clientes Mais Ativos</h3>
             <div class="space-y-3">
                <?php if (empty($ranking_clientes)): ?>
                    <p class="text-center text-gray-500 py-4">Nenhum dado disponível.</p>
                <?php else: ?>
                    <?php foreach($ranking_clientes as $index => $item): ?>
                    <div class="flex items-center space-x-3">
                        <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-bold text-white bg-slate-500"><?= $index + 1 ?></span>
                        <p class="text-gray-700 flex-grow"><?= htmlspecialchars($item['nome']) ?></p>
                        <span class="font-semibold text-purple-600"><?= $item['total'] ?> serviços</span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Visão Geral de <?= date('Y') ?></h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                     <h4 class="text-center text-gray-600 font-medium mb-2">Distribuição de Receitas</h4>
                     <div id="pieChartReceitas" style="height: 250px;"></div>
                </div>
                 <div>
                     <h4 class="text-center text-gray-600 font-medium mb-2">Distribuição de Despesas</h4>
                     <div id="pieChartDespesas" style="height: 250px;"></div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md" id="encaixes-hoje">
             <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fa-solid fa-hourglass-half mr-2 text-cyan-500"></i>Aguardando Encaixe Hoje</h3>
             <div class="space-y-3 max-h-80 overflow-y-auto">
                 <?php if (empty($encaixes_hoje)): ?>
                    <p class="text-center text-gray-500 py-4">Nenhum cliente aguardando encaixe.</p>
                <?php else: ?>
                    <?php foreach($encaixes_hoje as $item): ?>
                    <div class="p-3 bg-gray-50 rounded-lg">
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
    am4core.ready(function() {
        am4core.useTheme(am4themes_animated);

        // Função para criar gráfico de pizza
        function createPieChart(chartId, data) {
            if (!data || data.length === 0) {
                document.getElementById(chartId).innerHTML = `<div class="flex items-center justify-center h-full text-gray-500">Sem dados.</div>`;
                return;
            }
            
            let chart = am4core.create(chartId, am4charts.PieChart);
            chart.data = data;
            
            let pieSeries = chart.series.push(new am4charts.PieSeries());
            pieSeries.dataFields.value = "total";
            pieSeries.dataFields.category = "nome";
            pieSeries.slices.template.stroke = am4core.color("#fff");
            pieSeries.slices.template.strokeWidth = 2;
            pieSeries.slices.template.strokeOpacity = 1;
            
            pieSeries.labels.template.disabled = true;
            pieSeries.ticks.template.disabled = true;

            chart.legend = new am4charts.Legend();
            chart.legend.position = "right";
            chart.legend.valign = "middle";
        }
        
        // Dados PHP para JS
        const receitasData = <?= json_encode($ranking_servicos) ?>;
        const despesasData = <?= json_encode(fetchRanking($pdo, "SELECT tipo AS nome, COUNT(id) AS total FROM pagar WHERE id_conta = ? AND YEAR(data_lanc) = ? GROUP BY tipo", [$id_conta, date('Y')])) ?>;

        // Criar gráficos
        createPieChart("pieChartReceitas", receitasData);
        createPieChart("pieChartDespesas", despesasData);
    });
});
</script>