<?php
session_start();
require_once("../conexao.php"); // Ajuste o caminho conforme necessário

// Verificar se o usuário está autenticado
if (!isset($_SESSION['id_conta'])) {
    header('Location: login.php'); // Redireciona para login se não autenticado
    exit;
}

$id_conta = $_SESSION['id_conta'];

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

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campanhas de Marketing</title>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #F0F8FF;
    margin: 0;
    padding: 2vw;
}

.container {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 1rem;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.add-button {
    background-color: #4A90E2;
    color: #FFFFFF;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    text-decoration: none;
    font-size: clamp(0.75rem, 2.5vw, 0.875rem);
    font-weight: 600;
}

.chart-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin: 1rem 0;
    gap: 1rem;
}

.chart-wrapper {
    flex: 1;
    min-width: 250px;
    max-width: 48%;
    text-align: center;
}

.chart-title {
    font-size: clamp(0.75rem, 2.5vw, 0.875rem);
    font-weight: 600;
    color: #333333;
    margin: 0.5rem 0;
}

canvas {
    max-width: 100%;
    height: clamp(150px, 40vw, 300px) !important;
    border-radius: 0.5rem;
}

.no-data-text {
    font-size: clamp(0.7rem, 2vw, 0.75rem);
    color: #666666;
    text-align: center;
    margin: 0.5rem 0;
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
    border-radius: 0.625rem;
    padding: 1.25rem;
    width: 90%;
    max-width: 400px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-content2 {
    background-color: #FFFFFF;
    border-radius: 0.625rem;
    padding: 1.25rem;
    width: 90%;
    max-width: 300px;
}

.modal-title {
    font-size: clamp(1rem, 3vw, 1.125rem);
    font-weight: bold;
    color: #333333;
    margin-bottom: 1rem;
    text-align: center;
}

.modal-sub-text {
    font-size: clamp(0.75rem, 2.5vw, 0.875rem);
    color: #666666;
    margin-bottom: 0.75rem;
}

.segmento-button {
    background-color: #4A90E2;
    color: #FFFFFF;
    padding: 0.75rem;
    border-radius: 0.5rem;
    margin: 0.375rem 0;
    text-align: center;
    cursor: pointer;
    font-size: clamp(0.75rem, 2.5vw, 0.875rem);
}

.picker-container {
    margin: 0.5rem 0;
}

.picker-label {
    font-size: clamp(0.75rem, 2.5vw, 0.875rem);
    color: #333333;
    margin-bottom: 0.25rem;
}

select {
    width: 100%;
    padding: 0.5rem;
    border-radius: 0.5rem;
    border: 1px solid #DDDDDD;
    font-size: clamp(0.75rem, 2.5vw, 0.875rem);
}

.modal-button-container {
    display: flex;
    justify-content: space-between;
    margin-top: 1rem;
}

.modal-button {
    flex: 1;
    padding: 0.75rem;
    border-radius: 0.5rem;
    margin: 0 0.25rem;
    text-align: center;
    color: #FFFFFF;
    font-size: clamp(0.875rem, 2.5vw, 1rem);
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
    font-size: clamp(0.75rem, 2.5vw, 0.875rem);
    color: #FF4444;
    margin-bottom: 0.5rem;
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
    font-size: clamp(1rem, 3vw, 1.125rem);
    color: #FFFFFF;
    font-weight: 600;
}

.timeline-container {
    margin: 1rem 0;
    text-align: center;
}

.timeline-title {
    font-size: clamp(0.875rem, 2.5vw, 1rem);
    font-weight: 600;
    color: #333333;
    margin-bottom: 0.5rem;
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
    font-size: clamp(0.75rem, 2.5vw, 0.875rem);
    color: #333333;
    margin: 0.25rem 0;
}

.timeline-dot {
    width: 0.625rem;
    height: 0.625rem;
    border-radius: 50%;
    background-color: #4A90E2;
    margin: 0.25rem auto;
}

.timeline-line {
    width: 2px;
    height: 1.25rem;
    background-color: #4A90E2;
}

/* Media Queries para Responsividade */
@media (max-width: 600px) {
    .container {
        padding: 0 0.5rem;
    }
    h1 {
        font-size: 24px;
    }

    .chart-wrapper {
        max-width: 100%;
        min-width: 100%;
    }

    canvas {
        height: clamp(250px, 35vw, 180px) !important;
    }

    .modal-content, .modal-content2 {
        width: 95%;
        padding: 0.75rem;
    }

    .add-button, .segmento-button, .modal-button {
        font-size: clamp(0.7rem, 2.2vw, 0.8rem);
        padding: 0.4rem 0.8rem;
    }
}

@media (min-width: 600px) and (max-width: 1024px) {
    .chart-wrapper {
        max-width: 48%;
        min-width: 280px;
    }

    canvas {
        height: clamp(250px, 30vw, 220px) !important;
    }

    h1 {
        font-size: 24px;
    }

    .modal-content, .modal-content2 {
        width: 85%;
    }
}

@media (min-width: 1024px) {
    .chart-wrapper {
        max-width: 45%;
    }

    canvas {
        height: clamp(250px, 25vw, 300px) !important;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <div class="loading-overlay" id="loading-overlay">
            <span class="loading-text">Carregando...</span>
        </div>
        <div class="header">
            <h1>Campanhas de Marketing</h1>
            <a href="#" class="add-button" onclick="openCampanhaModal()">+ Nova Campanha</a>
        </div>
        <div class="chart-container">
            <div class="chart-wrapper">
                <h2 class="chart-title">Distribuição de Clientes por Tempo sem Retorno</h2>
                <canvas id="lineChart"></canvas>
            </div>
            <div class="chart-wrapper">
                <h2 class="chart-title">Distribuição Percentual de Clientes por Tempo sem Retorno</h2>
                <canvas id="pieChart"></canvas>
                <div id="no-data-text" class="no-data-text" style="display: none;">
                    Nenhum dado disponível para o gráfico de pizza
                </div>
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

            // if (!checkAndRefreshToken()) return;

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
</body>
</html>