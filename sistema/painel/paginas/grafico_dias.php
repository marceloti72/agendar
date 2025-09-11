<?php 
require_once("verificar.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Agendamentos por Dia da Semana</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 80%;
            margin: 20px auto;
        }
        .filtros {
            text-align: center;
            margin: 20px;
        }
        .total-agendamentos {
            text-align: center;
            margin-top: 20px;
            font-size: 1.2rem;
            color: #28a745; /* Verde */
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="filtros">
        <label for="mes">Mês:</label>
        <select id="mes">
            <option value="1">Janeiro</option>
            <option value="2">Fevereiro</option>
            <option value="3">Março</option>
            <option value="4">Abril</option>
            <option value="5">Maio</option>
            <option value="6">Junho</option>
            <option value="7">Julho</option>
            <option value="8">Agosto</option>
            <option value="9">Setembro</option>
            <option value="10">Outubro</option>
            <option value="11">Novembro</option>
            <option value="12">Dezembro</option>
        </select>
        
        <label for="ano">Ano:</label>
        <select id="ano">
            <!-- Anos serão preenchidos via JavaScript -->
        </select>
        
        <button onclick="atualizarGrafico()">Atualizar</button>
    </div>

    <div class="chart-container">
        <canvas id="myChart"></canvas>
    </div>

    <div class="total-agendamentos" id="totalAgendamentos">
        <!-- Total será inserido aqui -->
    </div>

    <script>
        let chartInstance = null;

        // Preencher select de anos
        function preencherAnos() {
            const anoSelect = document.getElementById('ano');
            const anoAtual = new Date().getFullYear();
            for (let ano = anoAtual - 5; ano <= anoAtual + 5; ano++) {
                const option = document.createElement('option');
                option.value = ano;
                option.text = ano;
                anoSelect.appendChild(option);
            }
            // Selecionar ano atual
            anoSelect.value = anoAtual;
            // Selecionar mês atual
            document.getElementById('mes').value = new Date().getMonth() + 1;
        }

        // Função para atualizar o gráfico
        async function atualizarGrafico() {            
            try {
                const mes = document.getElementById('mes').value;
                const ano = document.getElementById('ano').value;
                
                // Buscar os dados do PHP
                const response = await fetch(`paginas/grafico_dias/agendamentos2.php?mes=${mes}&ano=${ano}`);
                const result = await response.json();
                
                if (result.error) {
                    throw new Error(result.error);
                }

                const data = result.dados;
                const mesNome = new Date(ano, mes - 1).toLocaleString('pt-BR', { month: 'long' });

                // Calcular o total de agendamentos
                const total = data.reduce((acc, curr) => acc + curr, 0);

                // Atualizar o texto do total
                document.getElementById('totalAgendamentos').textContent = `Total de Agendamentos: ${total}`;

                // Destruir gráfico anterior se existir
                if (chartInstance) {
                    chartInstance.destroy();
                }

                // Criar novo gráfico
                const ctx = document.getElementById('myChart').getContext('2d');
                chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                        datasets: [{
                            label: 'Número de Agendamentos',
                            data: data,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Quantidade de Agendamentos'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Dias da Semana'
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: `Agendamentos de ${mesNome} de ${ano}`
                            },
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Erro:', error);
                document.getElementById('totalAgendamentos').textContent = 'Erro ao carregar o total';
            }
        }

        // Inicializar página
        window.onload = function() {
            preencherAnos();
            atualizarGrafico();
        };
    </script>
</body>
</html>