<?php
session_start();
require_once("../conexao.php"); // Ajuste o caminho conforme necessário

// Verificar se o usuário está autenticado
if (!isset($_SESSION['id_conta'])) {
    header('Location: login.php'); // Redireciona para login se não autenticado
    exit;
}

$id_conta = $_SESSION['id_conta'];

// Buscar username (equivalente a fetchUsername)
// try {
//     $query = $pdo->prepare("SELECT nome FROM contas WHERE id = :id_conta");
//     $query->bindValue(':id_conta', $id_conta);
//     $query->execute();
//     $username = $query->fetch(PDO::FETCH_ASSOC)['nome'] ?? '';
// } catch (Exception $e) {
//     $username = '';
//     error_log('Erro ao buscar username: ' . $e->getMessage());
// }

// Buscar clientes segmentados por tempo sem retorno (equivalente a fetchClientesSegmentos)
$clientes_segmentos = [
    '30-90' => 0,
    '90-180' => 0,
    '180-365' => 0,
    '365+' => 0,
    'sem-retorno' => 0
];
try {
    $query = $pdo->prepare("
        SELECT 
            CASE 
                WHEN DATEDIFF(CURDATE(), data_retorno) BETWEEN 30 AND 90 THEN '30-90'
                WHEN DATEDIFF(CURDATE(), data_retorno) BETWEEN 91 AND 180 THEN '90-180'
                WHEN DATEDIFF(CURDATE(), data_retorno) BETWEEN 181 AND 365 THEN '180-365'
                WHEN DATEDIFF(CURDATE(), data_retorno) > 365 THEN '365+'
                ELSE 'sem-retorno'
            END AS segmento,
            COUNT(*) AS total
        FROM clientes
        WHERE id_conta = :id_conta
        GROUP BY segmento
    ");
    $query->bindValue(':id_conta', $id_conta);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result as $row) {
        $clientes_segmentos[$row['segmento']] = (int)$row['total'];
    }
} catch (Exception $e) {
    error_log('Erro ao buscar clientes segmentados: ' . $e->getMessage());
}

// Buscar cupons válidos (equivalente a fetchCupons)
$cupons = [];
try {
    $query = $pdo->prepare("
        SELECT id, codigo, valor, tipo_desconto, data_validade, max_usos, usos_atuais
        FROM cupons
        WHERE id_conta = :id_conta
        AND data_validade >= CURDATE()
        AND (usos_atuais < max_usos OR usos_atuais IS NULL)
    ");
    $query->bindValue(':id_conta', $id_conta);
    $query->execute();
    $cupons = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Erro ao buscar cupons: ' . $e->getMessage());
}
?>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F0F8FF;
            margin: 0;
            padding: 16px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .add-button {
            background-color: #4A90E2;
            color: #FFFFFF;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .chart-container {
            text-align: center;
            margin: 16px 0;
        }
        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: #333333;
            margin: 16px 0 8px;
        }
        canvas {
            max-width: 100%;
            border-radius: 16px;
        }
        .no-data-text {
            font-size: 14px;
            color: #666666;
            text-align: center;
            margin: 8px 0;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: #FFFFFF;
            border-radius: 10px;
            padding: 20px;
            width: 80%;
            max-width: 400px;
            max-height: 80%;
            overflow-y: auto;
        }
        .modal-content2 {
            background-color: #FFFFFF;
            border-radius: 10px;
            padding: 20px;
            width: 80%;
            max-width: 300px;
        }
        .modal-title {
            font-size: 18px;
            font-weight: bold;
            color: #333333;
            margin-bottom: 16px;
            text-align: center;
        }
        .modal-sub-text {
            font-size: 14px;
            color: #666666;
            margin-bottom: 12px;
        }
        .segmento-button {
            background-color: #4A90E2;
            color: #FFFFFF;
            padding: 12px;
            border-radius: 8px;
            margin: 6px 0;
            text-align: center;
            cursor: pointer;
        }
        .picker-container {
            margin: 8px 0;
        }
        .picker-label {
            font-size: 14px;
            color: #333333;
            margin-bottom: 4px;
        }
        select {
            width: 100%;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #DDDDDD;
            font-size: 14px;
        }
        .modal-button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
        }
        .modal-button {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            margin: 0 4px;
            text-align: center;
            color: #FFFFFF;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .cancel-button {
            background-color: #FF4444;
        }
        .confirm-button {
            background-color: #4A90E2;
        }
        .error-text {
            font-size: 14px;
            color: #FF4444;
            margin-bottom: 8px;
            text-align: center;
        }
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1001;
        }
        .loading-text {
            font-size: 18px;
            color: #FFFFFF;
            font-weight: 600;
        }
        .timeline-container {
            margin: 16px 0;
            text-align: center;
        }
        .timeline-title {
            font-size: 16px;
            font-weight: 600;
            color: #333333;
            margin-bottom: 8px;
        }
        .timeline {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .timeline-point {
            text-align: center;
        }
        .timeline-text {
            font-size: 14px;
            color: #333333;
            margin: 4px 0;
        }
        .timeline-dot {
            width: 10px;
            height: 10px;
            border-radius: 5px;
            background-color: #4A90E2;
            margin: 4px auto;
        }
        .timeline-line {
            width: 2px;
            height: 20px;
            background-color: #4A90E2;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <div class="container">
        <div class="loading-overlay" id="loading-overlay">
            <span class="loading-text">Carregando...</span>
        </div>
        <div class="header">
            <h1>Campanhas de Marketing</h1>
            <a href="#" class="add-button" onclick="openCampanhaModal()">+ Nova Campanha</a>
        </div>
        <div class="chart-container">
            <h2 class="chart-title">Distribuição de Clientes por Tempo sem Retorno</h2>
            <canvas id="lineChart"></canvas>
            <h2 class="chart-title">Distribuição Percentual de Clientes por Tempo sem Retorno</h2>
            <canvas id="pieChart"></canvas>
            <div id="no-data-text" class="no-data-text" style="display: none;">
                Nenhum dado disponível para o gráfico de pizza
            </div>
        </div>
        <div id="error-text" class="error-text" style="display: none;"></div>
    </div>

    <!-- Modal para selecionar segmento -->
    <div class="modal-overlay" id="modal-campanha">
        <div class="modal-content">
            <h2 class="modal-title">Nova Campanha</h2>
            <p class="modal-sub-text">Selecione o segmento de clientes:</p>
            <div class="segmento-button" onclick="selectSegmento('30-90')">
                30 a 90 dias sem retorno (<?php echo $clientes_segmentos['30-90']; ?> clientes)
            </div>
            <div class="segmento-button" onclick="selectSegmento('90-180')">
                90 a 180 dias sem retorno (<?php echo $clientes_segmentos['90-180']; ?> clientes)
            </div>
            <div class="segmento-button" onclick="selectSegmento('180-365')">
                180 a 365 dias sem retorno (<?php echo $clientes_segmentos['180-365']; ?> clientes)
            </div>
            <div class="segmento-button" onclick="selectSegmento('365+')">
                Mais de 365 dias sem retorno (<?php echo $clientes_segmentos['365+']; ?> clientes)
            </div>
            <div class="segmento-button" onclick="selectSegmento('sem-retorno')">
                Sem serviço (<?php echo $clientes_segmentos['sem-retorno']; ?> clientes)
            </div>
            <div class="modal-button cancel-button" onclick="closeCampanhaModal()">Cancelar</div>
        </div>
    </div>

    <!-- Modal para configurar oferta -->
    <div class="modal-overlay" id="modal-oferta">
        <div class="modal-content2">
            <h2 class="modal-title">Configurar Oferta</h2>
            <p class="modal-sub-text">Deseja oferecer um cupom?</p>
            <div class="picker-container">
                <select id="oferecer-cupom" onchange="toggleCupomPicker()">
                    <option value="Não">Não</option>
                    <option value="Sim">Sim</option>
                </select>
            </div>
            <div class="picker-container" id="cupom-picker" style="display: none;">
                <label class="picker-label">Selecione o cupom:</label>
                <select id="selected-cupom">
                    <option value="">Selecione um cupom</option>
                    <?php foreach ($cupons as $cupom): ?>
                        <option value="<?php echo $cupom['id']; ?>">
                            <?php echo htmlspecialchars($cupom['codigo']) . ' (' . $cupom['valor'] . ($cupom['tipo_desconto'] === 'porcentagem' ? '%' : 'R$') . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="timeline-container" id="timeline-container" style="display: none;">
                <h3 class="timeline-title">Linha do Tempo da Campanha</h3>
                <div class="timeline">
                    <div class="timeline-point">
                        <span class="timeline-text" id="timeline-start"></span>
                        <div class="timeline-dot"></div>
                    </div>
                    <div class="timeline-line"></div>
                    <div class="timeline-point">
                        <span class="timeline-text" id="timeline-end"></span>
                        <div class="timeline-dot"></div>
                    </div>
                    <span class="timeline-text" id="timeline-progress"></span>
                </div>
            </div>
            <div id="error-oferta" class="error-text" style="display: none;"></div>
            <div class="modal-button-container">
                <div class="modal-button cancel-button" onclick="closeOfertaModal()">Cancelar</div>
                <div class="modal-button confirm-button" onclick="startCampaign()">Iniciar Campanha</div>
            </div>
        </div>
    </div>

    <script>
        const clientesSegmentos = <?php echo json_encode($clientes_segmentos); ?>;
        let selectedSegmento = null;
        let intervalId = null;

        // Função para verificar token (simulada com sessionStorage)
        // function checkAndRefreshToken() {
        //     const token = sessionStorage.getItem('token');
        //     if (!token) {
        //         Swal.fire('Sessão Expirada', 'Por favor, faça login novamente.', 'error');
        //         return false;
        //     }
        //     return true;
        // }

        // Inicializar gráficos
        const totalClientes = Object.values(clientesSegmentos).reduce((sum, val) => sum + val, 0);
        const lineChart = new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: ['30-90', '90-180', '180-365', '>365', 'Sem Retorno'],
                datasets: [{
                    data: [
                        clientesSegmentos['30-90'] || 0,
                        clientesSegmentos['90-180'] || 0,
                        clientesSegmentos['180-365'] || 0,
                        clientesSegmentos['365+'] || 0,
                        clientesSegmentos['sem-retorno'] || 0
                    ],
                    borderColor: '#4A90E2',
                    backgroundColor: '#4A90E2',
                    fill: false,
                    tension: 0.4
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        title: { display: true, text: 'Clientes' }
                    }
                }
            }
        });

        const pieChartData = [
            { label: '% 30-90', value: totalClientes > 0 ? ((clientesSegmentos['30-90'] / totalClientes) * 100).toFixed(2) : 0, color: '#FFD700' },
            { label: '% 90-180', value: totalClientes > 0 ? ((clientesSegmentos['90-180'] / totalClientes) * 100).toFixed(2) : 0, color: '#50C878' },
            { label: '% 180-365', value: totalClientes > 0 ? ((clientesSegmentos['180-365'] / totalClientes) * 100).toFixed(2) : 0, color: '#FFD700' },
            { label: '% >365', value: totalClientes > 0 ? ((clientesSegmentos['365+'] / totalClientes) * 100).toFixed(2) : 0, color: '#FF6347' },
            { label: 'Sem serviço', value: totalClientes > 0 ? ((clientesSegmentos['sem-retorno'] / totalClientes) * 100).toFixed(2) : 0, color: '#6A5ACD' }
        ].filter(item => item.value > 0);

        const pieChart = new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: pieChartData.map(item => item.label),
                datasets: [{
                    data: pieChartData.map(item => item.value),
                    backgroundColor: pieChartData.map(item => item.color)
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'right' },
                    tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.raw}%` } }
                }
            }
        });

        if (totalClientes === 0) {
            document.getElementById('pieChart').style.display = 'none';
            document.getElementById('no-data-text').style.display = 'block';
        }

        // Funções do modal
        function openCampanhaModal() {
            document.getElementById('modal-campanha').style.display = 'flex';
            document.getElementById('error-text').style.display = 'none';
        }

        function closeCampanhaModal() {
            document.getElementById('modal-campanha').style.display = 'none';
            selectedSegmento = null;
        }

        function selectSegmento(segmento) {
            selectedSegmento = segmento;
            document.getElementById('modal-campanha').style.display = 'none';
            document.getElementById('modal-oferta').style.display = 'flex';
            document.getElementById('error-oferta').style.display = 'none';
        }

        function closeOfertaModal() {
            document.getElementById('modal-oferta').style.display = 'none';
            document.getElementById('oferecer-cupom').value = 'Não';
            document.getElementById('cupom-picker').style.display = 'none';
            document.getElementById('selected-cupom').value = '';
            document.getElementById('timeline-container').style.display = 'none';
            document.getElementById('error-oferta').style.display = 'none';
            if (intervalId) clearInterval(intervalId);
        }

        function toggleCupomPicker() {
            const oferecerCupom = document.getElementById('oferecer-cupom').value;
            document.getElementById('cupom-picker').style.display = oferecerCupom === 'Sim' ? 'block' : 'none';
        }

        function startCampaign() {
            if (!selectedSegmento) {
                document.getElementById('error-oferta').style.display = 'block';
                document.getElementById('error-oferta').innerText = 'Selecione um segmento de clientes.';
                return;
            }
            const oferecerCupom = document.getElementById('oferecer-cupom').value;
            const selectedCupomId = document.getElementById('selected-cupom').value;
            if (oferecerCupom === 'Sim' && !selectedCupomId) {
                document.getElementById('error-oferta').style.display = 'block';
                document.getElementById('error-oferta').innerText = 'Selecione um cupom válido.';
                return;
            }

            if (!checkAndRefreshToken()) return;

            document.getElementById('loading-overlay').style.display = 'flex';
            $.ajax({
                url: 'paginas/campanhas/enviar-campanha.php',
                method: 'POST',
                data: {
                    id_conta: <?php echo $id_conta; ?>,
                    segmento: selectedSegmento,
                    oferecer_cupom: oferecerCupom,
                    id_cupom: selectedCupomId
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('loading-overlay').style.display = 'none';
                    if (response.success) {
                        const clientes = response.clientes || [];
                        const startTime = new Date().toLocaleString('pt-BR');
                        const estimatedEndTime = new Date(Date.now() + clientes.length * 15000).toLocaleString('pt-BR');
                        document.getElementById('timeline-container').style.display = 'block';
                        document.getElementById('timeline-start').innerText = `Início: ${startTime}`;
                        document.getElementById('timeline-end').innerText = `Fim estimado: ${estimatedEndTime}`;
                        document.getElementById('timeline-progress').innerText = `Progresso: 0 de ${clientes.length} clientes`;

                        let current = 0;
                        intervalId = setInterval(() => {
                            current++;
                            if (current >= clientes.length) {
                                clearInterval(intervalId);
                            }
                            document.getElementById('timeline-progress').innerText = `Progresso: ${current} de ${clientes.length} clientes`;
                        }, 15000);

                        Swal.fire('Sucesso', response.message || 'Campanha iniciada! As mensagens estão sendo enviadas.', 'success');
                        setTimeout(() => {
                            document.getElementById('modal-oferta').style.display = 'none';
                            location.reload(); // Recarregar para atualizar dados
                        }, 1500);
                    } else {
                        Swal.fire('Erro', response.message || 'Erro ao iniciar a campanha.', 'error');
                        document.getElementById('timeline-container').style.display = 'none';
                    }
                },
                error: function(xhr, status, error) {
                    document.getElementById('loading-overlay').style.display = 'none';
                    Swal.fire('Erro', 'Erro ao conectar com o servidor.', 'error');
                    console.error('Erro AJAX:', status, error, xhr.responseText);
                }
            });
        }
    </script>
