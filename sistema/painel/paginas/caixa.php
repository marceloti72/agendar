<?php
ob_start();

session_start();
require_once("../conexao.php");
require_once '../../vendor/autoload.php';

if (!isset($_SESSION['id_conta'])) {
    header('Location: login.php');
    exit;
}

$id_conta = $_SESSION['id_conta'];
$message = '';
$caixa_aberto = null;

// 1. Lógica para verificar se já existe um caixa aberto
try {
    $sql_caixa_aberto = "SELECT id, valor_abertura, data_abertura FROM caixa WHERE id_conta = :id_conta AND data_fechamento IS NULL ORDER BY data_abertura DESC LIMIT 1";
    $stmt_caixa_aberto = $pdo->prepare($sql_caixa_aberto);
    $stmt_caixa_aberto->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
    $stmt_caixa_aberto->execute();
    $caixa_aberto = $stmt_caixa_aberto->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $message = "Erro ao verificar o status do caixa: " . $e->getMessage();
}

// 2. Lógica para abrir o caixa, executada apenas se não houver um caixa aberto
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['export_pdf']) && !$caixa_aberto) {
    $operator = $_SESSION['id_usuario'];
    $opening_date = date('Y-m-d H:i:s');
    $opening_value = floatval($_POST['valor_abertura']);
    $opening_user = $_SESSION['id_usuario'];
    $obs = trim($_POST['obs']);

    try {
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO caixa (operador, data_abertura, valor_abertura, usuario_abertura, obs, id_conta)
                VALUES (:operador, :data_abertura, :valor_abertura, :usuario_abertura, :obs, :id_conta)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':operador', $operator, PDO::PARAM_INT);
        $stmt->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
        $stmt->bindParam(':data_abertura', $opening_date);
        $stmt->bindParam(':valor_abertura', $opening_value);
        $stmt->bindParam(':usuario_abertura', $opening_user, PDO::PARAM_INT);
        $stmt->bindParam(':obs', $obs);
        $stmt->execute();
        
        $pdo->commit();
        //$message = "Caixa aberto com sucesso! 🎉";
        header("Location: caixa.php");
        exit;
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        $message = "Erro ao abrir caixa: " . $e->getMessage();
    }
}

// 3. Lógica para carregar os dados do caixa aberto e do último fechado
$opening_value_aberto = 0;
$entrada_value_aberto = 0;
$total_value_aberto = 0;
$last_closing_value = null;

if ($caixa_aberto) {
    // Calcular o valor de entrada para o caixa atualmente aberto
    try {
        $sql_entrada = "SELECT SUM(valor) AS valor_entrada FROM receber WHERE data_pgto = CURDATE() AND pago = 'Sim' AND tipo = 'Comanda' AND pgto = 'Dinheiro' AND id_conta = :id_conta";
        $stmt_entrada = $pdo->prepare($sql_entrada);
        $stmt_entrada->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
        $stmt_entrada->execute();
        $entrada_data = $stmt_entrada->fetch(PDO::FETCH_ASSOC);
        $entrada_value_aberto = $entrada_data['valor_entrada'] ?? 0;
        
        $opening_value_aberto = $caixa_aberto['valor_abertura'];
        $total_value_aberto = $opening_value_aberto + $entrada_value_aberto;

    } catch(PDOException $e) {
        $message = "Erro ao calcular entradas do caixa: " . $e->getMessage();
    }
} else {
    // Lógica para buscar o último valor de fechamento para sugerir no formulário de abertura
    try {
        $sql_last_box = "SELECT valor_fechamento, sangrias FROM caixa WHERE id_conta = :id_conta AND valor_fechamento IS NOT NULL ORDER BY id DESC LIMIT 1";
        $stmt_last_box = $pdo->prepare($sql_last_box);
        $stmt_last_box->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
        $stmt_last_box->execute();
        $last_box_data = $stmt_last_box->fetch(PDO::FETCH_ASSOC);

        if ($last_box_data) {
            $last_closing_value = $last_box_data['valor_fechamento'];
            $sangrias = $last_box_data['sangrias'] ?? 0;
            $suggested_opening_value = $last_closing_value - $sangrias;
        }
    } catch(PDOException $e) {
        $message = "Erro ao carregar o último valor de fechamento: " . $e->getMessage();
    }
}

// Lógica para carregar os dados do relatório histórico
$report_data = [];
try {
    $sql_report = "SELECT
                c.data_abertura,
                c.data_fechamento,
                c.valor_abertura,
                c.valor_fechamento,
                c.sangrias,
                u_op.nome as operador_nome,
                u_ab.nome as usuario_abertura_nome,
                u_fe.nome as usuario_fechamento_nome,
                c.obs
            FROM caixa c
            JOIN usuarios u_op ON c.operador = u_op.id
            JOIN usuarios u_ab ON c.usuario_abertura = u_ab.id
            LEFT JOIN usuarios u_fe ON c.usuario_fechamento = u_fe.id
            WHERE c.id_conta = :id_conta
            ORDER BY c.data_abertura DESC";
    $stmt_report = $pdo->prepare($sql_report);
    $stmt_report->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
    $stmt_report->execute();
    $report_data = $stmt_report->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $message = "Erro ao carregar relatório: " . $e->getMessage();
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
    <script src="https://cdn.tailwindcss.com"></script>
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
        <div class="bg-white rounded-2xl shadow-lg p-6 md:p-10 mb-8">
            <div class="text-center mb-8">
                <?php if ($caixa_aberto): ?>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-green-700">
                        <i class="fas fa-cash-register mr-2"></i> Caixa Aberto
                    </h2>
                    <p class="text-gray-500 text-lg mt-2">Pronto para a jornada de trabalho!</p>
                <?php else: ?>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-blue-700">
                        <i class="fas fa-box-open mr-2"></i> Abertura de Caixa
                    </h2>
                    <p class="text-gray-500 text-lg mt-2">Preencha os dados para iniciar o dia.</p>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
                <div class="p-4 rounded-xl border-2 mb-6 text-center <?php echo strpos($message, 'Erro') === false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($caixa_aberto): ?>
                <!-- Conteúdo para o CAIXA ABERTO -->
                <div class="bg-gray-100 p-6 md:p-8 rounded-xl space-y-4 text-center">
                    <p class="text-lg text-gray-700 font-semibold">
                        Caixa aberto em <span class="text-green-600 font-bold"><?php echo date('d/m/Y', strtotime($caixa_aberto['data_abertura'])); ?></span>
                    </p>
                    <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                        <p class="text-xl font-medium text-gray-800">
                            Valor de Abertura: <span class="font-bold text-green-700">R$ <?php echo number_format($opening_value_aberto, 2, ',', '.'); ?></span>
                        </p>
                        <p class="text-xl font-medium text-gray-800 mt-2">
                            Entradas do Dia: <span class="font-bold text-green-700">R$ <?php echo number_format($entrada_value_aberto, 2, ',', '.'); ?></span>
                        </p>
                        <p class="text-2xl md:text-3xl font-extrabold text-green-800 mt-4 pt-4 border-t-2 border-green-300">
                            Total Previsto: <span class="text-green-900">R$ <?php echo number_format($total_value_aberto, 2, ',', '.'); ?></span>
                        </p>
                    </div>
                    <div class="flex flex-col md:flex-row gap-4 justify-center mt-6">
                        <a href="fechar_caixa.php?id=<?php echo $caixa_aberto['id']; ?>" class="bg-red-600 text-white font-semibold py-3 px-6 rounded-full shadow-lg hover:bg-red-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-lock mr-2"></i> Fechar Caixa
                        </a>
                        <button onclick="document.getElementById('reports-section').scrollIntoView({ behavior: 'smooth' });" class="bg-gray-600 text-white font-semibold py-3 px-6 rounded-full shadow-lg hover:bg-gray-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-file-alt mr-2"></i> Visualizar Relatórios
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <!-- Conteúdo para o CAIXA FECHADO (Formulário de Abertura) -->
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="valor_abertura" class="block text-gray-700 font-semibold mb-2">Valor Inicial (R$)</label>
                        <input type="number" step="0.01" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                                name="valor_abertura" required placeholder="0.00"
                                value="<?php echo isset($suggested_opening_value) ? htmlspecialchars(number_format($suggested_opening_value, 2, '.', '')) : ''; ?>">
                        <?php if (isset($suggested_opening_value)): ?>
                            <p class="text-sm text-gray-500 mt-2">
                                Valor da última sessão: R$ <?php echo number_format($suggested_opening_value, 2, ',', '.'); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="obs" class="block text-gray-700 font-semibold mb-2">Observações</label>
                        <textarea class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" id="obs" name="obs" rows="3"
                                    placeholder="Digite observações importantes (opcional)"></textarea>
                    </div>
                    <div class="flex flex-col md:flex-row gap-4 justify-center mt-8">
                        <button type="submit" class="bg-blue-600 text-white font-semibold py-3 px-6 rounded-full shadow-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-box-open mr-2"></i> Abrir Caixa
                        </button>
                        <button type="button" onclick="document.getElementById('reports-section').scrollIntoView({ behavior: 'smooth' });" class="bg-green-600 text-white font-semibold py-3 px-6 rounded-full shadow-lg hover:bg-green-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-file-alt mr-2"></i> Visualizar Relatórios
                        </button>
                    </div>
                </form>
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
                                $quebra = ($item['valor_fechamento'] !== null) ? ($item['valor_fechamento'] - $item['valor_abertura'] - ($item['sangrias'] ?? 0)) : null;
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d/m/Y', strtotime($item['data_abertura'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $item['data_fechamento'] ? date('d/m/Y', strtotime($item['data_fechamento'])) : '-'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($item['operador_nome']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">R$ <?php echo number_format($item['valor_abertura'], 2, ',', '.'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">R$ <?php echo $item['valor_fechamento'] ? number_format($item['valor_fechamento'], 2, ',', '.') : '-'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">R$ <?php echo $item['sangrias'] ? number_format($item['sangrias'], 2, ',', '.') : '-'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">R$ <?php echo $quebra ? number_format($quebra, 2, ',', '.') : '-'; ?></td>
                                    <td class="px-6 py-4 max-w-xs overflow-hidden text-ellipsis"><?php echo htmlspecialchars($item['obs'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
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
                    console.error('Erro ao gerar o PDF:', response.statusText);
                    // Usar um modal customizado ou div para a mensagem
                    const errorDiv = document.createElement('div');
                    errorDiv.innerHTML = '<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"><div class="bg-red-500 text-white rounded-lg p-6 max-w-sm text-center shadow-xl"><h4 class="font-bold text-lg mb-2">Erro</h4><p>Erro ao gerar o PDF.</p></div></div>';
                    document.body.appendChild(errorDiv);
                    setTimeout(() => errorDiv.remove(), 3000);
                }
            } catch (error) {
                console.error('Erro de rede:', error);
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
    </script>
</body>
</html>
