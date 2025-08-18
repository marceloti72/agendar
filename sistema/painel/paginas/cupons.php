<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session only if not active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
                echo "<script>alert('$success_message'); window.location.reload();</script>";
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
                    echo "<script>alert('$success_message'); window.location.reload();</script>";
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F0F8FF;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #333333;
            font-weight: bold;
        }
        .add-button {
            background-color: #4A90E2;
            color: #FFFFFF;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .add-button:hover {
            background-color: #357ABD;
        }
        .coupon-list {
            margin-top: 20px;
        }
        .coupon-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #FFFFFF;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .coupon-item.invalid {
            background-color: #FFE6E6;
        }
        .coupon-info p {
            margin: 4px 0;
            color: #333333;
            font-size: 16px;
        }
        .coupon-info p.subtext {
            color: #666666;
            font-size: 14px;
        }
        .coupon-info p.status {
            color: #FF4444;
            font-size: 12px;
            font-style: italic;
        }
        .button-container {
            display: flex;
            gap: 8px;
        }
        .edit-button, .delete-button {
            padding: 8px;
            border-radius: 8px;
            color: #FFFFFF;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
        }
        .edit-button {
            background-color: #4A90E2;
        }
        .edit-button:hover {
            background-color: #357ABD;
        }
        .delete-button {
            background-color: #FF4444;
        }
        .delete-button:hover {
            background-color: #CC3333;
        }
        .no-coupons {
            text-align: center;
            color: #666666;
            font-size: 16px;
            margin-top: 20px;
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
        .modal-overlay.active {
            display: flex;
        }
        .modal-content {
            background-color: #FFFFFF;
            border-radius: 10px;
            padding: 20px;
            width: 80%;
            max-width: 400px;
        }
        .modal-title {
            font-size: 18px;
            font-weight: bold;
            color: #333333;
            text-align: center;
            margin-bottom: 16px;
        }
        .input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #DDDDDD;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .picker-container {
            margin: 8px 0;
        }
        .picker-label {
            font-size: 14px;
            color: #333333;
            margin-bottom: 4px;
        }
        .picker {
            width: 100%;
            padding: 12px;
            border: 1px solid #DDDDDD;
            border-radius: 8px;
            font-size: 16px;
            background-color: #FFFFFF;
        }
        .modal-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
        }
        .modal-button {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            color: #FFFFFF;
            font-size: 16px;
            font-weight: 600;
            margin: 0 4px;
            cursor: pointer;
        }
        .cancel-button {
            background-color: #FF4444;
        }
        .cancel-button:hover {
            background-color: #CC3333;
        }
        .confirm-button {
            background-color: #4A90E2;
        }
        .confirm-button:hover {
            background-color: #357ABD;
        }
        .error-text, .success-text {
            font-size: 14px;
            text-align: center;
            margin-bottom: 8px;
        }
        .error-text {
            color: #FF4444;
        }
        .success-text {
            color: #4A90E2;
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
            z-index: 2000;
        }
        .loading-overlay.active {
            display: flex;
        }
        .loading-text {
            color: #FFFFFF;
            font-size: 18px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cupons de Desconto</h1>
            <button class="add-button" onclick="openModal(false)">+ Novo Cupom</button>
        </div>
        <?php if ($error): ?>
            <div class="error-text"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="success-text"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <div class="coupon-list">
            <?php if (empty($cupons)): ?>
                <p class="no-coupons">Nenhum cupom cadastrado.</p>
            <?php else: ?>
                <?php foreach ($cupons as $cupom): ?>
                    <?php
                    $is_invalid = strtotime($cupom['data_validade']) < time() || ($cupom['usos_atuais'] ?? 0) >= $cupom['max_usos'];
                    $validade = date('d/m/Y', strtotime($cupom['data_validade']));
                    ?>
                    <div class="coupon-item <?php echo $is_invalid ? 'invalid' : ''; ?>">
                        <div class="coupon-info">
                            <p><?php echo htmlspecialchars($cupom['codigo']); ?></p>
                            <p class="subtext">Desconto: <?php echo $cupom['valor'] . ($cupom['tipo_desconto'] === 'porcentagem' ? '%' : ' R$'); ?></p>
                            <p class="subtext">Validade: <?php echo $validade; ?></p>
                            <p class="subtext">Máximo de Usos: <?php echo $cupom['max_usos']; ?> (Usos atuais: <?php echo $cupom['usos_atuais'] ?? 0; ?>)</p>
                            <?php if ($is_invalid): ?>
                                <p class="status"><?php echo strtotime($cupom['data_validade']) < time() ? 'Cupom Vencido' : 'Limite de Usos Atingido'; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="button-container">
                            <button class="edit-button" onclick='editCoupon(<?php echo json_encode($cupom); ?>)'>✎</button>
                            <form method="POST" id="delete-form-<?php echo $cupom['id']; ?>" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="editId" value="<?php echo $cupom['id']; ?>">
                                <button type="submit" class="delete-button" onclick="return confirm('Deseja excluir o cupom <?php echo htmlspecialchars($cupom['codigo']); ?>?')">🗑</button>
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
                <input type="text" id="codigo" name="codigo" class="input" placeholder="Código do Cupom (ex.: DESCONTO10)" maxlength="20" oninput="this.value = this.value.toUpperCase()">
                <input type="text" id="valorDesconto" name="valorDesconto" class="input" placeholder="Valor do Desconto" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                <div class="picker-container">
                    <label class="picker-label">Tipo de Desconto:</label>
                    <select id="tipoDesconto" name="tipoDesconto" class="picker">
                        <option value="porcentagem">Porcentagem (%)</option>
                        <option value="fixo">Valor Fixo (R$)</option>
                    </select>
                </div>
                <input type="text" id="dataValidade" name="dataValidade" class="input" placeholder="Data de Validade (DD/MM/YYYY)" maxlength="10" oninput="formatDate(this)">
                <input type="text" id="maxUsos" name="maxUsos" class="input" placeholder="Máximo de Usos (ex.: 100)" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                <div id="modal-error" class="error-text" style="display: none;"></div>
                <div class="modal-buttons">
                    <button type="button" class="modal-button cancel-button" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="modal-button confirm-button" id="confirm-button">Criar</button>
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

        // Client-side form validation for create/edit only
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