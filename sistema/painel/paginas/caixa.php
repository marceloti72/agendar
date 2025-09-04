<?php
ob_start();

session_start();
require_once("../conexao.php");
require_once '../../vendor/autoload.php';

// Define o fuso horário para garantir que a data PHP funcione corretamente para o Brasil
date_default_timezone_set('America/Sao_Paulo');

// Cabeçalhos anti-cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Verificar se o script está sendo chamado via pag=caixa (compatível com .htaccess)
if (!isset($_GET['pag']) || $_GET['pag'] !== 'caixa') {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['id_conta'])) {
    header('Location: login.php');
    exit;
}

$id_conta = $_SESSION['id_conta'];
$message = '';
$caixa_aberto = null;
$total_value_aberto = 0;

// 1. Lógica para verificar se já existe um caixa aberto
try {
    $sql_caixa_aberto = "SELECT id, valor_abertura, data_abertura FROM caixa WHERE id_conta = :id_conta AND data_fechamento IS NULL ORDER BY data_abertura DESC LIMIT 1";
    $stmt_caixa_aberto = $pdo->prepare($sql_caixa_aberto);
    $stmt_caixa_aberto->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
    $stmt_caixa_aberto->execute();
    $caixa_aberto = $stmt_caixa_aberto->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $message = "Erro ao verificar o status do caixa: " . $e->getMessage();
    error_log("Erro caixa aberto: " . $e->getMessage());
}

// 2. Lógica para carregar os dados do caixa aberto e do último fechado
$opening_value_aberto = 0;
$entrada_value_aberto = 0;
$last_closing_value = null;
$suggested_opening_value = 0;
$total_sangrias_aberto = 0;

if ($caixa_aberto) {
    $current_date = date('Y-m-d');
    
    try {
        $sql_entrada = "SELECT SUM(valor) AS valor_entrada FROM receber WHERE data_pgto = :current_date AND pago = 'Sim' AND tipo = 'Comanda' AND pgto = 'Dinheiro' AND id_conta = :id_conta";
        $stmt_entrada = $pdo->prepare($sql_entrada);
        $stmt_entrada->bindParam(':current_date', $current_date, PDO::PARAM_STR);
        $stmt_entrada->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
        $stmt_entrada->execute();
        $entrada_data = $stmt_entrada->fetch(PDO::FETCH_ASSOC);
        $entrada_value_aberto = $entrada_data['valor_entrada'] ?? 0;
        
        $sql_sangria = "SELECT SUM(sangrias) AS total_sangrias FROM caixa WHERE id = :id_caixa";
        $stmt_sangria = $pdo->prepare($sql_sangria);
        $stmt_sangria->bindParam(':id_caixa', $caixa_aberto['id'], PDO::PARAM_INT);
        $stmt_sangria->execute();
        $sangria_data = $stmt_sangria->fetch(PDO::FETCH_ASSOC);
        $total_sangrias_aberto = $sangria_data['total_sangrias'] ?? 0;

        $opening_value_aberto = $caixa_aberto['valor_abertura'];
        $total_value_aberto = $opening_value_aberto + $entrada_value_aberto - $total_sangrias_aberto;

    } catch(PDOException $e) {
        $message = "Erro ao calcular entradas/sangrias do caixa: " . $e->getMessage();
        error_log("Erro entradas/sangrias: " . $e->getMessage());
    }
} else {
    try {
        $sql_last_box = "SELECT valor_fechamento, sangrias FROM caixa WHERE id_conta = :id_conta AND valor_fechamento IS NOT NULL ORDER BY id DESC LIMIT 1";
        $stmt_last_box = $pdo->prepare($sql_last_box);
        $stmt_last_box->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
        $stmt_last_box->execute();
        $last_box_data = $stmt_last_box->fetch(PDO::FETCH_ASSOC);

        if ($last_box_data) {
            $last_closing_value = $last_box_data['valor_fechamento'];
            $sangrias = $last_box_data['sangrias'] ?? 0;
            $suggested_opening_value = $last_closing_value;
        }
    } catch(PDOException $e) {
        $message = "Erro ao carregar o último valor de fechamento: " . $e->getMessage();
        error_log("Erro último fechamento: " . $e->getMessage());
    }
}

// Lógica para carregar os 30 últimos registros do relatório histórico
$report_data = [];
try {
    $sql_report = "SELECT
                     c.id,
                     c.data_abertura,
                     c.data_fechamento,
                     c.valor_abertura,
                     c.valor_fechamento,
                     c.sangrias,
                     c.quebra,
                     u_op.nome as operador_nome,
                     u_ab.nome as usuario_abertura_nome,
                     u_fe.nome as usuario_fechamento_nome,
                     c.obs
                   FROM caixa c
                   JOIN usuarios u_op ON c.operador = u_op.id
                   JOIN usuarios u_ab ON c.usuario_abertura = u_ab.id
                   LEFT JOIN usuarios u_fe ON c.usuario_fechamento = u_fe.id
                   WHERE c.id_conta = :id_conta
                   ORDER BY c.id DESC
                   LIMIT 30";
    $stmt_report = $pdo->prepare($sql_report);
    $stmt_report->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
    $stmt_report->execute();
    $report_data = $stmt_report->fetchAll(PDO::FETCH_ASSOC);

    // Log de depuração
    $ids = array_column($report_data, 'id');
    error_log("Registros retornados (IDs): " . implode(', ', $ids) . " (Total: " . count($report_data) . ")");
} catch(PDOException $e) {
    $message = "Erro ao carregar relatório: " . $e->getMessage();
    error_log("Erro relatório SQL: " . $e->getMessage());
}

if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Caixa</title>
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .modal {
            display: none;
        }
        .alert-success { background-color: #d1e7dd; color: #0f5132; }
        .alert-danger { background-color: #f8d7da; color: #842029; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-8">
    <div class="container mx-auto p-4 md:p-8 max-w-4xl">
        <div id="status-container" class="bg-white rounded-2xl shadow-lg p-6 md:p-10 mb-8">
            <div id="caixa-aberto-content" class="text-center" style="<?php echo $caixa_aberto ? 'display: block;' : 'display: none;'; ?>">
                <h2 class="text-3xl md:text-4xl font-extrabold text-green-700">
                    <i class="fas fa-cash-register mr-2"></i> Caixa Aberto
                </h2>
                <p class="text-gray-500 text-lg mt-2">Pronto para a jornada de trabalho!</p>
                <div class="bg-gray-100 p-6 md:p-8 rounded-xl space-y-4 text-center mt-6">
                    <p class="text-lg text-gray-700 font-semibold">
                        Caixa aberto em <span class="text-green-600 font-bold"><?php echo date('d/m/Y', strtotime($caixa_aberto['data_abertura'] ?? 'now')); ?></span>
                    </p>
                    <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                        <p class="text-xl font-medium text-gray-800">
                            Valor de Abertura: <span class="font-bold text-green-700" id="valor-abertura-aberto">R$ <?php echo number_format($opening_value_aberto, 2, ',', '.'); ?></span>
                        </p>
                        <p class="text-xl font-medium text-gray-800 mt-2">
                            Entradas de Dinheiro: <span class="font-bold text-green-700" id="entradas-aberto">R$ <?php echo number_format($entrada_value_aberto, 2, ',', '.'); ?></span>
                        </p>
                        <p class="text-xl font-medium text-gray-800 mt-2">
                            Sangrias: <span class="font-bold text-green-700" id="sangrias-aberto">R$ <?php echo number_format($total_sangrias_aberto, 2, ',', '.'); ?></span>
                        </p>
                        <p class="text-2xl md:text-3xl font-extrabold text-green-800 mt-4 pt-4 border-t-2 border-green-300">
                            Total Previsto: <span class="text-green-900" id="total-previsto">R$ <?php echo number_format($total_value_aberto, 2, ',', '.'); ?></span>
                        </p>
                    </div>
                    <div class="flex flex-col md:flex-row gap-4 justify-center mt-6">
                        <button id="sangriaBtn" class="bg-orange-500 text-white font-semibold py-3 px-6 rounded-full shadow-lg hover:bg-orange-600 transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-tint mr-2"></i> Sangria
                        </button>
                        <button id="fecharCaixaBtn" data-caixa-id="<?php echo $caixa_aberto['id'] ?? ''; ?>" data-total-previsto="<?php echo htmlspecialchars($total_value_aberto); ?>" class="bg-red-600 text-white font-semibold py-3 px-6 rounded-full shadow-lg hover:bg-red-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-lock mr-2"></i> Fechar Caixa
                        </button>
                        <button onclick="document.getElementById('reports-section').scrollIntoView({ behavior: 'smooth' });" class="bg-gray-600 text-white font-semibold py-3 px-6 rounded-full shadow-lg hover:bg-gray-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-file-alt mr-2"></i> Visualizar Relatórios
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="caixa-fechado-content" class="text-center" style="<?php echo !$caixa_aberto ? 'display: block;' : 'display: none;'; ?>">
                <h2 class="text-3xl md:text-4xl font-extrabold text-blue-700">
                    <i class="fas fa-box-open mr-2"></i> Abertura de Caixa
                </h2>
                <p class="text-gray-500 text-lg mt-2">Preencha os dados para iniciar o dia.</p>
                <form id="openCaixaForm" class="space-y-6 mt-6">
                    <div>
                        <label for="valor_abertura" class="block text-gray-700 font-semibold mb-2 text-left">Valor Inicial (R$)</label>
                        <input type="number" step="0.01" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                               name="valor_abertura" required placeholder="0.00"
                               value="<?php echo htmlspecialchars(number_format($suggested_opening_value, 2, '.', '')); ?>">
                        <?php if (isset($suggested_opening_value) && $suggested_opening_value > 0): ?>
                            <p class="text-sm text-gray-500 mt-2 text-left">
                                Valor do último fechamento: R$ <?php echo number_format($suggested_opening_value, 2, ',', '.'); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="obs" class="block text-gray-700 font-semibold mb-2 text-left">Observações</label>
                        <textarea class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" id="obs" name="obs" rows="3"
                                  placeholder="Digite observações importantes (opcional)"></textarea>
                    </div>
                    <div class="flex flex-col md:flex-row gap-4 justify-center mt-8">
                        <button type="submit" id="submitBtn" class="bg-blue-600 text-white font-semibold py-3 px-6 rounded-full shadow-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-box-open mr-2"></i> Abrir Caixa
                        </button>
                        <button type="button" onclick="document.getElementById('reports-section').scrollIntoView({ behavior: 'smooth' });" class="bg-green-600 text-white font-semibold py-3 px-6 rounded-full shadow-lg hover:bg-green-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-file-alt mr-2"></i> Visualizar Relatórios
                        </button>
                    </div>
                </form>
            </div>
            
            <?php if ($message): ?>
                <div id="statusMessage" class="p-4 rounded-xl border-2 mb-6 text-center <?php echo strpos($message, 'Erro') === false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php else: ?>
                <div id="statusMessage" class="hidden"></div>
            <?php endif; ?>
        </div>

        <div id="reports-section" class="bg-white rounded-2xl shadow-lg p-6 md:p-10">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-800">
                    Relatório Histórico de Caixas
                </h2>
                <button id="exportPdfBtn" class="bg-blue-500 text-white font-semibold py-2 px-4 rounded-full hover:bg-blue-600 transition-colors flex items-center">
                    <span id="exportText"><i class="fas fa-file-pdf mr-2"></i> Exportar</span>
                    <span id="loadingSpinner" class="hidden"><i class="fas fa-spinner fa-spin mr-2"></i> Gerando...</span>
                </button>
            </div>
            
            <?php if (empty($report_data)): ?>
                <p class="text-center text-gray-500 p-8">Nenhum registro de caixa encontrado.</p>
            <?php else: ?>
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="w-full text-sm text-gray-600">
                        <thead class="bg-gray-50 font-semibold uppercase text-gray-700 text-left sticky top-0">
                            <tr>
                                <th scope="col" class="px-6 py-3">ID</th>
                                <th scope="col" class="px-6 py-3">Data Abertura</th>
                                <th scope="col" class="px-6 py-3">Data Fechamento</th>
                                <th scope="col" class="px-6 py-3">Operador</th>
                                <th scope="col" class="px-6 py-3">Abertura</th>
                                <th scope="col" class="px-6 py-3">Fechamento</th>
                                <th scope="col" class="px-6 py-3">Sangrias</th>
                                <th scope="col" class="px-6 py-3">Quebra</th>
                                <th scope="col" class="px-6 py-3">Observações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($report_data as $item):
                                $quebra = ($item['quebra'] !== null) ? $item['quebra'] : null;
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($item['id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d/m/Y', strtotime($item['data_abertura'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $item['data_fechamento'] ? date('d/m/Y', strtotime($item['data_fechamento'])) : '-'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($item['operador_nome']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">R$ <?php echo number_format($item['valor_abertura'], 2, ',', '.'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">R$ <?php echo $item['valor_fechamento'] ? number_format($item['valor_fechamento'], 2, ',', '.') : '-'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">R$ <?php echo $item['sangrias'] ? number_format($item['sangrias'], 2, ',', '.') : '-'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">R$ <?php echo $quebra !== null ? number_format($quebra, 2, ',', '.') : '-'; ?></td>
                                    <td class="px-6 py-4 max-w-xs overflow-hidden text-ellipsis"><?php echo htmlspecialchars($item['obs'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para Sangria -->
    <div id="sangriaModal" class="modal fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 w-full max-w-md">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">Realizar Sangria</h3>
                <button id="closeSangriaModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <form id="sangriaForm" class="space-y-6">
                <input type="hidden" name="caixa_id" value="<?php echo $caixa_aberto['id'] ?? ''; ?>">
                <div>
                    <label for="sangria_valor" class="block text-gray-700 font-semibold mb-2">Valor da Sangria (R$)</label>
                    <input type="number" step="0.01" id="sangria_valor" name="sangria_valor" required 
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors"
                           placeholder="0.00">
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" id="cancelSangriaBtn" class="bg-gray-300 text-gray-800 font-semibold py-2 px-6 rounded-full hover:bg-gray-400 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" id="submitSangriaBtn" class="bg-orange-500 text-white font-semibold py-2 px-6 rounded-full hover:bg-orange-600 transition-colors flex items-center justify-center">
                        <i class="fas fa-tint mr-2"></i> Confirmar Sangria
                    </button>
                </div>
            </form>
            <div id="sangriaStatusMessage" class="hidden mt-4 p-3 rounded-lg text-center" role="alert"></div>
        </div>
    </div>
    
    <!-- Modal para Fechamento de Caixa -->
    <div id="fecharCaixaModal" class="modal fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 w-full max-w-md">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">Fechar Caixa</h3>
                <button id="closeFecharCaixaModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <form id="fecharCaixaForm" class="space-y-6">
                <input type="hidden" id="fechar_caixa_id" name="caixa_id">
                <input type="hidden" id="valor_abertura_fechamento" name="valor_abertura">
                <div>
                    <label for="valor_fechamento" class="block text-gray-700 font-semibold mb-2">Valor no Caixa para Fechamento (R$)</label>
                    <input type="number" step="0.01" id="valor_fechamento" name="valor_fechamento" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors"
                           placeholder="0.00">
                </div>
                <div>
                    <label for="fechar_obs" class="block text-gray-700 font-semibold mb-2">Observações (opcional)</label>
                    <textarea id="fechar_obs" name="obs" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors"
                              placeholder="Motivo de quebra de caixa, etc."></textarea>
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" id="cancelFecharCaixaBtn" class="bg-gray-300 text-gray-800 font-semibold py-2 px-6 rounded-full hover:bg-gray-400 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" id="submitFecharCaixaBtn" class="bg-red-600 text-white font-semibold py-2 px-6 rounded-full hover:bg-red-700 transition-colors flex items-center justify-center">
                        <i class="fas fa-lock mr-2"></i> Confirmar Fechamento
                    </button>
                </div>
            </form>
            <div id="fecharStatusMessage" class="hidden mt-4 p-3 rounded-lg text-center" role="alert"></div>
        </div>
    </div>
    
    <script>
        document.getElementById('openCaixaForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const statusMessage = document.getElementById('statusMessage');
            const submitBtn = document.getElementById('submitBtn');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Abrindo...';
            statusMessage.classList.add('hidden');

            try {
                const response = await fetch('paginas/caixa/abrir_caixa_api.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    window.location.reload();
                } else {
                    statusMessage.textContent = result.message;
                    statusMessage.classList.remove('hidden', 'alert-success');
                    statusMessage.classList.add('alert-danger');
                }
            } catch (error) {
                statusMessage.textContent = "Erro de rede ao comunicar com o servidor.";
                statusMessage.classList.remove('hidden', 'alert-success');
                statusMessage.classList.add('alert-danger');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-box-open mr-2"></i> Abrir Caixa';
            }
        });

        document.getElementById('exportPdfBtn').addEventListener('click', async () => {
            const exportBtn = document.getElementById('exportPdfBtn');
            const exportText = document.getElementById('exportText');
            const loadingSpinner = document.getElementById('loadingSpinner');

            exportText.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');
            exportBtn.disabled = true;

            try {
                const response = await fetch('paginas/caixa/exportar_pdf.php', {
                    method: 'POST'
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'relatorio_caixa.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                } else {
                    const errorDiv = document.createElement('div');
                    errorDiv.innerHTML = '<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"><div class="bg-red-500 text-white rounded-lg p-6 max-w-sm text-center shadow-xl"><h4 class="font-bold text-lg mb-2">Erro</h4><p>Erro ao gerar o PDF.</p></div></div>';
                    document.body.appendChild(errorDiv);
                    setTimeout(() => errorDiv.remove(), 3000);
                }
            } catch (error) {
                const errorDiv = document.createElement('div');
                errorDiv.innerHTML = '<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"><div class="bg-red-500 text-white rounded-lg p-6 max-w-sm text-center shadow-xl"><h4 class="font-bold text-lg mb-2">Erro</h4><p>Erro de rede ao gerar o PDF.</p></div></div>';
                document.body.appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 3000);
            } finally {
                exportText.classList.remove('hidden');
                loadingSpinner.classList.add('hidden');
                exportBtn.disabled = false;
            }
        });

        // Lógica do Modal de Sangria
        const sangriaBtn = document.getElementById('sangriaBtn');
        const sangriaModal = document.getElementById('sangriaModal');
        const sangriaForm = document.getElementById('sangriaForm');
        const closeSangriaModal = document.getElementById('closeSangriaModal');
        const cancelSangriaBtn = document.getElementById('cancelSangriaBtn');
        const sangriaStatusMessage = document.getElementById('sangriaStatusMessage');

        function showSangriaModal() {
            sangriaModal.style.display = 'flex';
        }

        function hideSangriaModal() {
            sangriaModal.style.display = 'none';
            sangriaForm.reset();
            sangriaStatusMessage.classList.add('hidden');
        }
        
        sangriaBtn.addEventListener('click', showSangriaModal);
        closeSangriaModal.addEventListener('click', hideSangriaModal);
        cancelSangriaBtn.addEventListener('click', hideSangriaModal);
        
        sangriaForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const submitSangriaBtn = document.getElementById('submitSangriaBtn');

            submitSangriaBtn.disabled = true;
            submitSangriaBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processando...';
            sangriaStatusMessage.classList.add('hidden');
            
            try {
                const response = await fetch('paginas/caixa/sangria.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    window.location.reload();
                } else {
                    sangriaStatusMessage.textContent = result.message;
                    sangriaStatusMessage.classList.remove('hidden', 'alert-success');
                    sangriaStatusMessage.classList.add('alert-danger');
                }
            } catch (error) {
                sangriaStatusMessage.textContent = "Erro de rede ao comunicar com o servidor.";
                sangriaStatusMessage.classList.remove('hidden', 'alert-success');
                sangriaStatusMessage.classList.add('alert-danger');
            } finally {
                submitSangriaBtn.disabled = false;
                submitSangriaBtn.innerHTML = '<i class="fas fa-tint mr-2"></i> Confirmar Sangria';
            }
        });
        
        // Lógica do Modal de Fechamento de Caixa
        const fecharCaixaBtn = document.getElementById('fecharCaixaBtn');
        const fecharCaixaModal = document.getElementById('fecharCaixaModal');
        const fecharCaixaForm = document.getElementById('fecharCaixaForm');
        const closeFecharCaixaModal = document.getElementById('closeFecharCaixaModal');
        const cancelFecharCaixaBtn = document.getElementById('cancelFecharCaixaBtn');
        const fecharStatusMessage = document.getElementById('fecharStatusMessage');

        function showFecharCaixaModal() {
            const caixaId = fecharCaixaBtn.getAttribute('data-caixa-id');
            const totalPrevisto = fecharCaixaBtn.getAttribute('data-total-previsto');
            
            document.getElementById('fechar_caixa_id').value = caixaId;
            document.getElementById('valor_abertura_fechamento').value = totalPrevisto;
            document.getElementById('valor_fechamento').value = totalPrevisto;
            
            fecharCaixaModal.style.display = 'flex';
        }

        function hideFecharCaixaModal() {
            fecharCaixaModal.style.display = 'none';
            fecharCaixaForm.reset();
            fecharStatusMessage.classList.add('hidden');
        }

        if (fecharCaixaBtn) {
            fecharCaixaBtn.addEventListener('click', showFecharCaixaModal);
        }
        closeFecharCaixaModal.addEventListener('click', hideFecharCaixaModal);
        cancelFecharCaixaBtn.addEventListener('click', hideFecharCaixaModal);

        fecharCaixaForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const submitFecharCaixaBtn = document.getElementById('submitFecharCaixaBtn');

            submitFecharCaixaBtn.disabled = true;
            submitFecharCaixaBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processando...';
            fecharStatusMessage.classList.add('hidden');

            try {
                const response = await fetch('paginas/caixa/fechar_caixa_api.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    window.location.reload();
                } else {
                    fecharStatusMessage.textContent = result.message;
                    fecharStatusMessage.classList.remove('hidden', 'alert-success');
                    fecharStatusMessage.classList.add('alert-danger');
                }
            } catch (error) {
                fecharStatusMessage.textContent = "Erro de rede ao comunicar com o servidor.";
                fecharStatusMessage.classList.remove('hidden', 'alert-success');
                fecharStatusMessage.classList.add('alert-danger');
            } finally {
                submitFecharCaixaBtn.disabled = false;
                submitFecharCaixaBtn.innerHTML = '<i class="fas fa-lock mr-2"></i> Confirmar Fechamento';
            }
        });
    </script>
</body>
</html>