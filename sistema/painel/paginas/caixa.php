<?php
session_start();
require_once("../conexao.php"); // Adjust the path as needed
require_once '../../vendor/autoload.php'; // Include PHPspreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['id_conta'])) {
    header('Location: login.php');
    exit;
}

$id_conta = $_SESSION['id_conta'];

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['export_excel'])) {
    $operator = $_SESSION['id_usuario'];
    $opening_date = date('Y-m-d');
    $opening_value = floatval($_POST['valor_abertura']);
    $opening_user = $_SESSION['id_usuario'];
    $obs = trim($_POST['obs']);

    try {
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
        $message = "Caixa aberto com sucesso! 🎉";
    } catch(PDOException $e) {
        $message = "Erro ao abrir caixa: " . $e->getMessage();
    }
}

$report_data = [];
try {
    $sql = "SELECT c.id, u.nome as operador_nome, c.data_abertura, c.valor_abertura, u2.nome as usuario_abertura_nome, c.obs 
            FROM caixa c
            JOIN usuarios u ON c.operador = u.id_usuario
            JOIN usuarios u2 ON c.usuario_abertura = u2.id_usuario
            WHERE c.id_conta = :id_conta ORDER BY c.data_abertura DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
    $stmt->execute();
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $message = "Erro ao carregar relatório: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Operador');
    $sheet->setCellValue('C1', 'Data Abertura');
    $sheet->setCellValue('D1', 'Valor Abertura (R$)');
    $sheet->setCellValue('E1', 'Usuário Abertura');
    $sheet->setCellValue('F1', 'Observações');
    
    $row = 2;
    foreach ($report_data as $item) {
        $sheet->setCellValue('A' . $row, $item['id']);
        $sheet->setCellValue('B' . $row, $item['operador_nome']);
        $sheet->setCellValue('C' . $row, date('d/m/Y', strtotime($item['data_abertura'])));
        $sheet->setCellValue('D' . $row, number_format($item['valor_abertura'], 2, ',', '.'));
        $sheet->setCellValue('E' . $row, $item['usuario_abertura_nome']);
        $sheet->setCellValue('F' . $row, $item['obs']);
        $row++;
    }
    
    $sheet->getStyle('A1:F1')->getFont()->setBold(true);
    $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="relatorio_caixa.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abertura de Caixa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
            --light-bg: #f8f9fa;
            --white-bg: #ffffff;
            --card-border: rgba(0, 0, 0, 0.125);
            --shadow-md: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
        }
        body {
            background-color: var(--light-bg);
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s ease;
        }
        .container {
            max-width: 850px;
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            background-color: var(--white-bg);
            padding: 2.5rem;
        }
        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .form-header h2 {
            color: #212529;
            font-weight: 700;
            font-size: 2.25rem;
        }
        .form-header p {
            color: #6c757d;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        .form-control, .form-select, .btn {
            border-radius: 0.5rem;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0a58ca;
            border-color: #0a58ca;
            transform: translateY(-2px);
        }
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            background-color: #146c43;
            border-color: #146c43;
            transform: translateY(-2px);
        }
        .btn-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border-color: #badbcc;
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
            border-color: #f5c2c7;
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        .modal-content {
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            border-radius: 0.75rem;
            border: 1px solid #dee2e6;
        }
        .table {
            margin-bottom: 0;
            background-color: var(--white-bg);
        }
        .table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: var(--light-bg);
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        .modal-header, .modal-footer {
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="form-header">
                <h2><i class="fas fa-cash-register me-2"></i>Abertura de Caixa</h2>
                <p>Preencha os dados para iniciar o dia de trabalho.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo strpos($message, 'Erro') === false ? 'success' : 'danger'; ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label for="valor_abertura" class="form-label">Valor Inicial (R$)</label>
                    <input type="number" step="0.01" class="form-control form-control-lg" id="valor_abertura" 
                           name="valor_abertura" required placeholder="0.00">
                </div>
                <div class="mb-4">
                    <label for="obs" class="form-label">Observações</label>
                    <textarea class="form-control" id="obs" name="obs" rows="3" 
                              placeholder="Digite observações importantes (opcional)"></textarea>
                </div>
                <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                    <button type="submit" class="btn btn-primary btn-lg btn-icon">
                        <i class="fas fa-box-open"></i> Abrir Caixa
                    </button>
                    <button type="button" class="btn btn-success btn-lg btn-icon" data-bs-toggle="modal" data-bs-target="#relatorioModal">
                        <i class="fas fa-file-alt"></i> Visualizar Relatórios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="relatorioModal" tabindex="-1" aria-labelledby="relatorioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="relatorioModalLabel">Relatório Histórico de Caixas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($report_data)): ?>
                        <p class="text-center text-muted">Nenhum registro de caixa encontrado.</p>
                    <?php else: ?>
                        <div class="table-responsive table-container">
                            <table class="table table-hover">
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
                                    <?php foreach ($report_data as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['id']); ?></td>
                                            <td><?php echo htmlspecialchars($item['operador_nome']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($item['data_abertura'])); ?></td>
                                            <td><?php echo number_format($item['valor_abertura'], 2, ',', '.'); ?></td>
                                            <td><?php echo htmlspecialchars($item['usuario_abertura_nome']); ?></td>
                                            <td><?php echo htmlspecialchars($item['obs'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer justify-content-between">
                    <form method="POST">
                        <input type="hidden" name="export_excel" value="1">
                        <button type="submit" class="btn btn-success btn-icon">
                            <i class="fas fa-file-excel"></i> Exportar para Excel
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>