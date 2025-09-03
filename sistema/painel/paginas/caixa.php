<?php
session_start();
require_once("../conexao.php"); 
require_once '../../vendor/autoload.php';

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
    $sql = "SELECT 
                c.data_abertura, 
                c.data_fechamento, 
                c.valor_abertura, 
                c.valor_fechamento, 
                c.quebra, 
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
    
    // Removi a coluna 'Total' e adicionei a 'Quebra (R$)' no final
    $sheet->setCellValue('A1', 'Data Abertura');
    $sheet->setCellValue('B1', 'Data Fechamento');
    $sheet->setCellValue('C1', 'Operador');
    $sheet->setCellValue('D1', 'Usuário Abertura');
    $sheet->setCellValue('E1', 'Usuário Fechamento');
    $sheet->setCellValue('F1', 'Valor Abertura (R$)');
    $sheet->setCellValue('G1', 'Valor Fechamento (R$)');
    $sheet->setCellValue('H1', 'Sangrias (R$)');
    $sheet->setCellValue('I1', 'Observações');
    $sheet->setCellValue('J1', 'Quebra (R$)');
    
    $row = 2;
    foreach ($report_data as $item) {
        // Cálculo da quebra de acordo com a nova regra: fechamento - abertura - sangrias
        $quebra = ($item['valor_fechamento'] !== null) ? ($item['valor_fechamento'] - $item['valor_abertura'] - ($item['sangrias'] ?? 0)) : null;
        
        $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($item['data_abertura'])));
        $sheet->setCellValue('B' . $row, $item['data_fechamento'] ? date('d/m/Y', strtotime($item['data_fechamento'])) : '-');
        $sheet->setCellValue('C' . $row, $item['operador_nome']);
        $sheet->setCellValue('D' . $row, $item['usuario_abertura_nome']);
        $sheet->setCellValue('E' . $row, $item['usuario_fechamento_nome'] ?? '-');
        $sheet->setCellValue('F' . $row, number_format($item['valor_abertura'], 2, ',', '.'));
        $sheet->setCellValue('G' . $row, $item['valor_fechamento'] ? number_format($item['valor_fechamento'], 2, ',', '.') : '-');
        $sheet->setCellValue('H' . $row, $item['sangrias'] ? number_format($item['sangrias'], 2, ',', '.') : '-');
        $sheet->setCellValue('I' . $row, $item['obs'] ?? '-');
        $sheet->setCellValue('J' . $row, $quebra ? number_format($quebra, 2, ',', '.') : '-');
        $row++;
    }
    
    $sheet->getStyle('A1:J1')->getFont()->setBold(true);
    $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    foreach (range('A', 'J') as $col) {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
            --secondary-color: #6c757d;
            --light-bg: #f8f9fa;
            --white-bg: #ffffff;
            --card-border: rgba(0, 0, 0, 0.125);
            --shadow-md: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            --danger-bg: #f8d7da;
            --danger-color: #842029;
            --success-bg: #d1e7dd;
            --success-color-dark: #0f5132;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container-app {
            width: 100%;
            max-width: 850px;
            padding: 1rem;
        }
        
        .app-card {
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
            margin: 0;
        }
        
        .form-header p {
            color: #6c757d;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        
        .form-field {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }
        
        .form-input, .form-textarea {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.5rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-input:focus, .form-textarea:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.5rem;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            border: 1px solid transparent;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #0a58ca;
            border-color: #0a58ca;
            transform: translateY(-2px);
        }
        
        .btn-success {
            color: #fff;
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #146c43;
            border-color: #146c43;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            color: #fff;
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-secondary:hover {
            background-color: #5c636a;
            border-color: #5c636a;
        }

        .btn-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background-color: var(--success-bg);
            color: var(--success-color-dark);
            border-color: #badbcc;
        }
        
        .alert-danger {
            background-color: var(--danger-bg);
            color: var(--danger-color);
            border-color: #f5c2c7;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: auto;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 95%; 
            max-width: 1000px; 
            padding: 2rem;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .btn-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--secondary-color);
        }

        .modal-body {
            max-height: 500px; 
            overflow-y: auto;
        }
        
        .modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
        }
        
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 0.75rem;
            border: 1px solid #dee2e6;
        }
        
        .table-custom {
            width: 100%;
            margin-bottom: 0;
            color: #212529;
            border-collapse: collapse;
            background-color: var(--white-bg);
            font-size: 0.85rem; 
        }
        
        .table-custom thead {
            position: sticky;
            top: 0;
            background-color: var(--light-bg);
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table-custom th, .table-custom td {
            padding: 0.75rem 1rem; 
            vertical-align: top;
            border-top: 1px solid #dee2e6;
            white-space: nowrap; 
        }
        
        .table-custom tbody tr:hover {
            background-color: #f2f2f2;
        }

    </style>
</head>
<body>
    <div class="container-app">
        <div class="app-card">
            <div class="form-header">
                <h2><i class="fas fa-cash-register"></i> Abertura de Caixa</h2>
                <p>Preencha os dados para iniciar o dia de trabalho.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo strpos($message, 'Erro') === false ? 'success' : 'danger'; ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-field">
                    <label for="valor_abertura" class="form-label">Valor Inicial (R$)</label>
                    <input type="number" step="0.01" class="form-input" id="valor_abertura" 
                           name="valor_abertura" required placeholder="0.00">
                </div>
                <div class="form-field">
                    <label for="obs" class="form-label">Observações</label>
                    <textarea class="form-textarea" id="obs" name="obs" rows="3" 
                              placeholder="Digite observações importantes (opcional)"></textarea>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary btn-icon">
                        <i class="fas fa-box-open"></i> Abrir Caixa
                    </button>
                    <button type="button" class="btn btn-success btn-icon" onclick="openModal('relatorioModal')">
                        <i class="fas fa-file-alt"></i> Visualizar Relatórios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="relatorioModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Relatório Histórico de Caixas</h5>
                <button type="button" class="btn-close" onclick="closeModal('relatorioModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (empty($report_data)): ?>
                    <p class="text-center">Nenhum registro de caixa encontrado.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Data Abertura</th>
                                    <th>Data Fechamento</th>
                                    <th>Operador</th>
                                    <th>Usuário Abertura</th>
                                    <th>Usuário Fechamento</th>
                                    <th>Valor Abertura (R$)</th>
                                    <th>Valor Fechamento (R$)</th>
                                    <th>Sangrias (R$)</th>
                                    <th>Observações</th>
                                    <th>Quebra (R$)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report_data as $item): 
                                    $quebra = ($item['valor_fechamento'] !== null) ? ($item['valor_fechamento'] - $item['valor_abertura'] - ($item['sangrias'] ?? 0)) : null;
                                ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($item['data_abertura'])); ?></td>
                                        <td><?php echo $item['data_fechamento'] ? date('d/m/Y', strtotime($item['data_fechamento'])) : '-'; ?></td>
                                        <td><?php echo htmlspecialchars($item['operador_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($item['usuario_abertura_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($item['usuario_fechamento_nome'] ?? '-'); ?></td>
                                        <td><?php echo number_format($item['valor_abertura'], 2, ',', '.'); ?></td>
                                        <td><?php echo $item['valor_fechamento'] ? number_format($item['valor_fechamento'], 2, ',', '.') : '-'; ?></td>
                                        <td><?php echo $item['sangrias'] ? number_format($item['sangrias'], 2, ',', '.') : '-'; ?></td>
                                        <td><?php echo htmlspecialchars($item['obs'] ?? '-'); ?></td>
                                        <td><?php echo $quebra ? number_format($quebra, 2, ',', '.') : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="export_excel" value="1">
                    <button type="submit" class="btn btn-primary btn-icon">
                        <i class="fas fa-file-excel"></i> Exportar para Excel
                    </button>
                </form>
                <button type="button" class="btn btn-secondary" onclick="closeModal('relatorioModal')">Fechar</button>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            document.body.style.overflow = 'hidden'; 
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = '';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('relatorioModal');
            if (event.target == modal) {
                closeModal('relatorioModal');
            }
        }
    </script>
</body>
</html>