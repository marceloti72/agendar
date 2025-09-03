<?php
session_start();
require_once("../conexao.php"); // Ajuste o caminho conforme necessário
require_once '../../vendor/autoload.php'; // Inclua o PHPspreadsheet (instale via Composer)

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verificar se o usuário está autenticado
if (!isset($_SESSION['id_conta'])) {
    header('Location: login.php');
    exit;
}

$id_conta = $_SESSION['id_conta'];

// Processar formulário de abertura de caixa
$mensagem = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['export_excel'])) {
    $operador = $_SESSION['id_usuario'];
    $data_abertura = date('Y-m-d');
    $valor_abertura = floatval($_POST['valor_abertura']);
    $usuario_abertura = $_SESSION['id_usuario'];
    $obs = trim($_POST['obs']);

    try {
        $sql = "INSERT INTO caixa (operador, data_abertura, valor_abertura, usuario_abertura, obs, id_conta) 
                VALUES (:operador, :data_abertura, :valor_abertura, :usuario_abertura, :obs, :id_conta)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':operador', $operador, PDO::PARAM_INT);
        $stmt->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
        $stmt->bindParam(':data_abertura', $data_abertura);
        $stmt->bindParam(':valor_abertura', $valor_abertura);
        $stmt->bindParam(':usuario_abertura', $usuario_abertura, PDO::PARAM_INT);
        $stmt->bindParam(':obs', $obs);
        $stmt->execute();
        $mensagem = "Caixa aberto com sucesso!";
    } catch(PDOException $e) {
        $mensagem = "Erro ao abrir caixa: " . $e->getMessage();
    }
}

// Buscar dados para o relatório
$relatorio = [];
try {
    $sql = "SELECT id, operador, data_abertura, valor_abertura, usuario_abertura, obs 
            FROM caixa WHERE id_conta = :id_conta ORDER BY data_abertura DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
    $stmt->execute();
    $relatorio = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensagem = "Erro ao carregar relatório: " . $e->getMessage();
}

// Exportar para Excel
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Cabeçalhos
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Operador');
    $sheet->setCellValue('C1', 'Data Abertura');
    $sheet->setCellValue('D1', 'Valor Abertura (R$)');
    $sheet->setCellValue('E1', 'Usuário Abertura');
    $sheet->setCellValue('F1', 'Observações');
    
    // Dados
    $row = 2;
    foreach ($relatorio as $item) {
        $sheet->setCellValue('A' . $row, $item['id']);
        $sheet->setCellValue('B' . $row, $item['operador']);
        $sheet->setCellValue('C' . $row, date('d/m/Y', strtotime($item['data_abertura'])));
        $sheet->setCellValue('D' . $row, number_format($item['valor_abertura'], 2, ',', '.'));
        $sheet->setCellValue('E' . $row, $item['usuario_abertura']);
        $sheet->setCellValue('F' . $row, $item['obs']);
        $row++;
    }
    
    // Estilizar cabeçalho
    $sheet->getStyle('A1:F1')->getFont()->setBold(true);
    $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal('center');
    
    // Ajustar largura das colunas
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Gerar arquivo Excel
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="relatorio_caixa.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
}
?>


    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 700px;
            margin: 50px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-header h2 {
            color: #343a40;
            font-weight: 600;
        }
        .form-header p {
            color: #6c757d;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-success {
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #218838;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .form-control, .form-select {
            border-radius: 8px;
        }
        .alert {
            border-radius: 8px;
            margin-top: 20px;
        }
        .btn-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .modal-lg {
            max-width: 900px;
        }
        .table {
            margin-bottom: 0;
        }
    </style>

<body>
    <div class="container">
        <div class="form-header">
            <h2><i class="fas fa-cash-register me-2"></i>Abertura de Caixa</h2>
            <p>Preencha os dados para abrir um novo caixa</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo strpos($mensagem, 'Erro') === false ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="valor_abertura" class="form-label">Valor Inicial (R$)</label>
                <input type="number" step="0.01" class="form-control" id="valor_abertura" 
                       name="valor_abertura" required placeholder="0.00">
            </div>
            <div class="mb-3">
                <label for="obs" class="form-label">Observações</label>
                <textarea class="form-control" id="obs" name="obs" rows="4" 
                          placeholder="Digite observações (opcional)"></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary w-50 btn-icon">
                    <i class="fas fa-box-open"></i> Abrir Caixa
                </button>
                <button type="button" class="btn btn-success w-50 btn-icon" data-bs-toggle="modal" data-bs-target="#relatorioModal">
                    <i class="fas fa-file-alt"></i> Relatórios
                </button>
            </div>
        </form>
    </div>

    <!-- Modal de Relatórios -->
    <div class="modal fade" id="relatorioModal" tabindex="-1" aria-labelledby="relatorioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="relatorioModalLabel">Relatório de Caixas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($relatorio)): ?>
                        <p>Nenhum registro encontrado.</p>
                    <?php else: ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Operador</th>
                                    <th>Data Abertura</th>
                                    <th>Valor Abertura (R$)</th>
                                    <th>Usuário Abertura</th>
                                    <th>Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($relatorio as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                                        <td><?php echo htmlspecialchars($item['operador']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($item['data_abertura'])); ?></td>
                                        <td><?php echo number_format($item['valor_abertura'], 2, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($item['usuario_abertura']); ?></td>
                                        <td><?php echo htmlspecialchars($item['obs'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="export_excel" value="1">
                        <button type="submit" class="btn btn-primary btn-icon">
                            <i class="fas fa-file-excel"></i> Imprimir Excel
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    
</body>
