<?php
// Enable error reporting for debugging
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Start session only if not active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("verificar.php");

// Check if user is logged in
if (!isset($_SESSION['id_conta'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$id_conta = $_SESSION['id_conta'];

// Database connection
require '../conexao.php';

// Initialize variables
$cupons = [];
$error = '';
$success_message = '';

// Fetch all coupons
try {
    $query = $pdo->prepare("SELECT * FROM cupons WHERE id_conta = ? ORDER BY id DESC");
    $query->execute([$id_conta]);
    $cupons = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Erro ao carregar cupons: ' . $e->getMessage();
    error_log($error);
}

// Handle form submission for creating/updating/deleting coupons
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'delete') {
        $edit_id = intval($_POST['editId'] ?? 0);
        if ($edit_id > 0) {
            try {
                $query = $pdo->prepare("DELETE FROM cupons WHERE id = ? AND id_conta = ?");
                $query->execute([$edit_id, $id_conta]);
                $success_message = 'Cupom excluído com sucesso!';
                echo "<script>alert('$success_message'); window.location='cupons.php';</script>";
                exit();
            } catch (PDOException $e) {
                $error = 'Erro ao excluir cupom: ' . $e->getMessage();
                error_log($error);
            }
        } else {
            $error = 'ID do cupom inválido.';
        }
    } else {
        $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
        $valor = floatval($_POST['valorDesconto'] ?? 0);
        $tipo_desconto = $_POST['tipoDesconto'] ?? 'porcentagem';
        $data_validade = $_POST['dataValidade'] ?? '';
        $max_usos = intval($_POST['maxUsos'] ?? 0);
        $edit_id = intval($_POST['editId'] ?? 0);

        // Validate inputs for create/edit
        if (!preg_match('/^[A-Z0-9]{3,20}$/', $codigo)) {
            $error = 'O código do cupom deve ter entre 3 e 20 caracteres alfanuméricos.';
        } elseif ($valor <= 0) {
            $error = 'O valor do desconto deve ser um número maior que zero.';
        } elseif ($tipo_desconto === 'porcentagem' && $valor > 100) {
            $error = 'O desconto em porcentagem não pode exceder 100%.';
        } elseif (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data_validade)) {
            $error = 'Por favor, insira uma data válida no formato DD/MM/YYYY.';
        } else {
            $date_parts = explode('/', $data_validade);
            $data_validade_sql = "{$date_parts[2]}-{$date_parts[1]}-{$date_parts[0]}";
            if (strtotime($data_validade_sql) < strtotime(date('Y-m-d'))) {
                $error = 'A data de validade deve ser futura.';
            }
        }
        if ($max_usos <= 0) {
            $error = 'O número máximo de usos deve ser um número maior que zero.';
        }

        if (!$error && ($action === 'create' || ($action === 'edit' && $edit_id > 0))) {
            try {
                // Check for duplicate code
                $check_query = $pdo->prepare("SELECT id FROM cupons WHERE id_conta = ? AND codigo = ? AND id != ?");
                $check_query->execute([$id_conta, $codigo, $edit_id]);
                if ($check_query->fetch()) {
                    $error = 'Código do cupom já existe.';
                } else {
                    if ($action === 'create') {
                        $query = $pdo->prepare("
                            INSERT INTO cupons (id_conta, codigo, valor, tipo_desconto, data_validade, max_usos, usos_atuais)
                            VALUES (?, ?, ?, ?, ?, ?, 0)
                        ");
                        $query->execute([$id_conta, $codigo, $valor, $tipo_desconto, $data_validade_sql, $max_usos]);
                        $success_message = 'Cupom criado com sucesso!';
                    } else {
                        $query = $pdo->prepare("
                            UPDATE cupons
                            SET codigo = ?, valor = ?, tipo_desconto = ?, data_validade = ?, max_usos = ?
                            WHERE id = ? AND id_conta = ?
                        ");
                        $query->execute([$codigo, $valor, $tipo_desconto, $data_validade_sql, $max_usos, $edit_id, $id_conta]);
                        $success_message = 'Cupom atualizado com sucesso!';
                    }
                    echo "<script>alert('$success_message'); window.location='cupons.php';</script>";
                    exit();
                }
            } catch (PDOException $e) {
                $error = 'Erro ao ' . ($action === 'edit' ? 'atualizar' : 'criar') . ' cupom: ' . $e->getMessage();
                error_log($error);
            }
        }
    }
}

// Ensure no output before this point
ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Cupons</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 1rem;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
        }
        .header {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 640px) {
            .header {
                flex-direction: row;
            }
        }
        .header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        @media (min-width: 640px) {
            .header h1 {
                margin-bottom: 0;
            }
        }
        .add-button {
            background-color: #e99f35;
            color: #fff;
            padding: 0.5rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: none;
        }
        .add-button:hover {
            background-color: #1d4ed8;
        }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #f87171;
            color: #b91c1c;
        }
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #34d399;
            color: #065f46;
        }
        .coupon-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .coupon-item {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            background-color: #f9fafb;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #10b981;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .coupon-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .coupon-item.invalid {
            opacity: 0.6;
            border-left-color: #ef4444;
        }
        @media (min-width: 640px) {
            .coupon-item {
                flex-direction: row;
                align-items: center;
            }
        }
        .coupon-info {
            flex: 1;
            margin-bottom: 1rem;
        }
        @media (min-width: 640px) {
            .coupon-info {
                margin-bottom: 0;
            }
        }
        .coupon-info h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        .coupon-info p {
            font-size: 0.875rem;
            color: #4b5563;
            margin: 0.25rem 0;
        }
        .coupon-info .status {
            font-size: 0.75rem;
            color: #ef4444;
            font-style: italic;
            margin-top: 0.25rem;
        }
        .button-group {
            display: flex;
            gap: 0.5rem;
        }
        .action-button {
            width: 36px; /* Set a fixed width */
            height: 36px; /* Set a fixed height to make it a circle */
            padding: 0; /* Remove padding to avoid distortion */
            border-radius: 50%; /* Make it a perfect circle */
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex; /* Use flexbox */
            justify-content: center; /* Center the icon horizontally */
            align-items: center; /* Center the icon vertically */
        }
        /* Ensure the Font Awesome icons have a consistent size */
        .action-button i {
            font-size: 1rem; /* Adjust icon size as needed */
        }
        .edit-button {
            background-color: #f59e0b;
        }
        .edit-button:hover {
            background-color: #d97706;
        }
        .delete-button {
            background-color: #ef4444;
        }
        .delete-button:hover {
            background-color: #dc2626;
        }
        .no-coupons {
            text-align: center;
            color: #6b7280;
            padding: 2.5rem 0;
        }
        
        /* Modal */
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
        .modal-overlay.active {
            display: flex;
        }
        .modal-content {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
            padding: 1.5rem;
            width: 90%;
            max-width: 500px;
            animation: fadeIn 0.3s ease-in-out;
        }
        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .form-input, .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }
        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .modal-button {
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .cancel-button {
            background-color: #d1d5db;
            color: #374151;
        }
        .cancel-button:hover {
            background-color: #9ca3af;
        }
        .confirm-button {
            background-color: #2563eb;
            color: #fff;
        }
        .confirm-button:hover {
            background-color: #1d4ed8;
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }
        .loading-overlay.active {
            display: flex;
        }
        .loading-text {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        /* Keyframes */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Gestão de Cupons</h1>
            <button onclick="openModal(false)" class="add-button">
                <i class="fas fa-plus"></i> Novo Cupom
            </button>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <div class="coupon-list">
            <?php if (empty($cupons)): ?>
                <p class="no-coupons">Nenhum cupom cadastrado. Clique em "Novo Cupom" para começar.</p>
            <?php else: ?>
                <?php foreach ($cupons as $cupom): ?>
                    <?php
                    $is_invalid = strtotime($cupom['data_validade']) < time() || ($cupom['usos_atuais'] ?? 0) >= $cupom['max_usos'];
                    $validade = date('d/m/Y', strtotime($cupom['data_validade']));
                    $desconto_texto = ($cupom['tipo_desconto'] === 'porcentagem' ? $cupom['valor'] . '%' : 'R$' . number_format($cupom['valor'], 2, ',', '.'));
                    ?>
                    <div class="coupon-item <?php echo $is_invalid ? 'invalid' : ''; ?>">
                        <div class="coupon-info">
                            <h3><?php echo htmlspecialchars($cupom['codigo']); ?></h3>
                            <p>Desconto: <strong><?php echo $desconto_texto; ?></strong></p>
                            <p>Validade: <strong><?php echo $validade; ?></strong></p>
                            <p>Usos: <strong><?php echo ($cupom['usos_atuais'] ?? 0) . '/' . $cupom['max_usos']; ?></strong></p>
                            <?php if ($is_invalid): ?>
                                <p class="status">
                                    <?php echo strtotime($cupom['data_validade']) < time() ? 'Cupom Vencido' : 'Limite de Usos Atingido'; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="button-group">
                            <button onclick='editCoupon(<?php echo json_encode($cupom); ?>)' class="action-button edit-button" title="Editar">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <form method="POST" id="delete-form-<?php echo $cupom['id']; ?>" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="editId" value="<?php echo $cupom['id']; ?>">
                                <button type="submit" class="action-button delete-button" onclick="return confirm('Deseja excluir o cupom <?php echo htmlspecialchars($cupom['codigo']); ?>?')" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div id="modal-overlay" class="modal-overlay">
        <div class="modal-content">
            <h2 id="modal-title" class="modal-title">Criar Novo Cupom</h2>
            <form id="coupon-form" method="POST">
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="editId" id="edit-id" value="0">
                
                <div class="form-group">
                    <label for="codigo" class="form-label">Código do Cupom</label>
                    <input type="text" id="codigo" name="codigo" class="form-input" placeholder="Ex: DESCONTO10" maxlength="20" oninput="this.value = this.value.toUpperCase()">
                </div>
                
                <div class="form-group">
                    <label for="valorDesconto" class="form-label">Valor do Desconto</label>
                    <input type="text" id="valorDesconto" name="valorDesconto" class="form-input" placeholder="Ex: 10 ou 25.50" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                </div>
                
                <div class="form-group">
                    <label for="tipoDesconto" class="form-label">Tipo de Desconto</label>
                    <select id="tipoDesconto" name="tipoDesconto" class="form-select">
                        <option value="porcentagem">Porcentagem (%)</option>
                        <option value="fixo">Valor Fixo (R$)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="dataValidade" class="form-label">Data de Validade</label>
                    <input type="text" id="dataValidade" name="dataValidade" class="form-input" placeholder="DD/MM/YYYY" maxlength="10" oninput="formatDate(this)">
                </div>
                
                <div class="form-group">
                    <label for="maxUsos" class="form-label">Máximo de Usos</label>
                    <input type="text" id="maxUsos" name="maxUsos" class="form-input" placeholder="Ex: 100" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                
                <div id="modal-error" class="alert alert-error" style="display: none;"></div>
                
                <div class="modal-buttons">
                    <button type="button" onclick="closeModal()" class="modal-button cancel-button">
                        Cancelar
                    </button>
                    <button type="submit" class="modal-button confirm-button" id="confirm-button">
                        Criar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-text">Carregando...</div>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loading-overlay').classList.add('active');
        }

        function hideLoading() {
            document.getElementById('loading-overlay').classList.remove('active');
        }

        function showError(message) {
            const errorDiv = document.getElementById('modal-error');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }

        function clearError() {
            const errorDiv = document.getElementById('modal-error');
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
        }

        function formatDate(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 0) {
                value = value.substring(0, 2) + (value.length > 2 ? '/' + value.substring(2, 4) : '') + (value.length > 4 ? '/' + value.substring(4, 8) : '');
            }
            input.value = value;
        }

        function openModal(editing, coupon = null) {
            document.getElementById('modal-title').textContent = editing ? 'Editar Cupom' : 'Criar Novo Cupom';
            document.getElementById('confirm-button').textContent = editing ? 'Salvar' : 'Criar';
            document.getElementById('form-action').value = editing ? 'edit' : 'create';
            document.getElementById('edit-id').value = coupon ? coupon.id : '0';
            document.getElementById('codigo').value = coupon ? coupon.codigo : '';
            document.getElementById('valorDesconto').value = coupon ? coupon.valor : '';
            document.getElementById('tipoDesconto').value = coupon ? coupon.tipo_desconto : 'porcentagem';
            document.getElementById('dataValidade').value = coupon ? new Date(coupon.data_validade).toLocaleDateString('pt-BR') : '';
            document.getElementById('maxUsos').value = coupon ? coupon.max_usos : '';
            clearError();
            document.getElementById('modal-overlay').classList.add('active');
        }

        function closeModal() {
            document.getElementById('modal-overlay').classList.remove('active');
            document.getElementById('coupon-form').reset();
            document.getElementById('form-action').value = 'create';
            document.getElementById('edit-id').value = '0';
            clearError();
        }

        function editCoupon(coupon) {
            openModal(true, coupon);
        }

        document.getElementById('coupon-form').addEventListener('submit', function(event) {
            const action = document.getElementById('form-action').value;
            if (action === 'create' || action === 'edit') {
                const codigo = document.getElementById('codigo').value.trim().toUpperCase();
                const valorDesconto = document.getElementById('valorDesconto').value;
                const tipoDesconto = document.getElementById('tipoDesconto').value;
                const dataValidade = document.getElementById('dataValidade').value;
                const maxUsos = document.getElementById('maxUsos').value;

                if (!codigo || !/^[A-Z0-9]{3,20}$/.test(codigo)) {
                    showError('O código do cupom deve ter entre 3 e 20 caracteres alfanuméricos.');
                    event.preventDefault();
                    return;
                }
                if (!valorDesconto || isNaN(parseFloat(valorDesconto)) || parseFloat(valorDesconto) <= 0) {
                    showError('O valor do desconto deve ser um número maior que zero.');
                    event.preventDefault();
                    return;
                }
                if (tipoDesconto === 'porcentagem' && parseFloat(valorDesconto) > 100) {
                    showError('O desconto em porcentagem não pode exceder 100%.');
                    event.preventDefault();
                    return;
                }
                const dateRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
                if (!dataValidade || !dateRegex.test(dataValidade)) {
                    showError('Por favor, insira uma data válida no formato DD/MM/YYYY.');
                    event.preventDefault();
                    return;
                }
                const [_, day, month, year] = dataValidade.match(dateRegex);
                const date = new Date(`${year}-${month}-${day}`);
                if (isNaN(date.getTime()) || date < new Date().setHours(0, 0, 0, 0)) {
                    showError('A data de validade deve ser futura.');
                    event.preventDefault();
                    return;
                }
                if (!maxUsos || isNaN(parseInt(maxUsos)) || parseInt(maxUsos) <= 0) {
                    showError('O número máximo de usos deve ser um número maior que zero.');
                    event.preventDefault();
                    return;
                }
            }
            showLoading();
        });
    </script>
</body>
</html>
<?php
// Flush output buffer
ob_end_flush();
?>