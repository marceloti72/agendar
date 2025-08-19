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
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"> -->
    <style>
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
    </style>
</head>
<body class="bg-gray-100 font-sans p-4">

    <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-lg p-6 md:p-8">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4 sm:mb-0">Gestão de Cupons</h1>
            <button onclick="openModal(false)" class="bg-blue-600 text-white px-6 py-2 rounded-full font-semibold shadow-md hover:bg-blue-700 transition-colors duration-300">
                <i class="fas fa-plus mr-2"></i>Novo Cupom
            </button>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_GET['success']); ?></span>
            </div>
        <?php endif; ?>

        <div class="space-y-4">
            <?php if (empty($cupons)): ?>
                <p class="text-center text-gray-500 py-10">Nenhum cupom cadastrado. Clique em "Novo Cupom" para começar.</p>
            <?php else: ?>
                <?php foreach ($cupons as $cupom): ?>
                    <?php
                    $is_invalid = strtotime($cupom['data_validade']) < time() || ($cupom['usos_atuais'] ?? 0) >= $cupom['max_usos'];
                    $validade = date('d/m/Y', strtotime($cupom['data_validade']));
                    $desconto_texto = ($cupom['tipo_desconto'] === 'porcentagem' ? $cupom['valor'] . '%' : 'R$' . number_format($cupom['valor'], 2, ',', '.'));
                    ?>
                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm flex flex-col sm:flex-row justify-between items-center <?php echo $is_invalid ? 'opacity-60 border-l-4 border-red-500' : 'border-l-4 border-green-500'; ?>">
                        <div class="flex-1 mb-4 sm:mb-0">
                            <h3 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($cupom['codigo']); ?></h3>
                            <p class="text-sm text-gray-600">Desconto: <span class="font-semibold"><?php echo $desconto_texto; ?></span></p>
                            <p class="text-sm text-gray-600">Validade: <span class="font-semibold"><?php echo $validade; ?></span></p>
                            <p class="text-sm text-gray-600">Usos: <span class="font-semibold"><?php echo ($cupom['usos_atuais'] ?? 0) . '/' . $cupom['max_usos']; ?></span></p>
                            <?php if ($is_invalid): ?>
                                <p class="text-xs text-red-500 italic mt-1">
                                    <?php echo strtotime($cupom['data_validade']) < time() ? 'Cupom Vencido' : 'Limite de Usos Atingido'; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick='editCoupon(<?php echo json_encode($cupom); ?>)' class="bg-yellow-500 text-white p-2 rounded-full hover:bg-yellow-600 transition-colors duration-300" title="Editar">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <form method="POST" id="delete-form-<?php echo $cupom['id']; ?>" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="editId" value="<?php echo $cupom['id']; ?>">
                                <button type="submit" class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition-colors duration-300" onclick="return confirm('Deseja excluir o cupom <?php echo htmlspecialchars($cupom['codigo']); ?>?')" title="Excluir">
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
        <div class="bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-lg animate-fade-in-up">
            <h2 id="modal-title" class="text-2xl font-bold text-center mb-6 text-gray-800">Criar Novo Cupom</h2>
            <form id="coupon-form" method="POST">
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="editId" id="edit-id" value="0">
                
                <div class="mb-4">
                    <label for="codigo" class="block text-gray-700 font-semibold mb-2">Código do Cupom</label>
                    <input type="text" id="codigo" name="codigo" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: DESCONTO10" maxlength="20" oninput="this.value = this.value.toUpperCase()">
                </div>
                
                <div class="mb-4">
                    <label for="valorDesconto" class="block text-gray-700 font-semibold mb-2">Valor do Desconto</label>
                    <input type="text" id="valorDesconto" name="valorDesconto" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: 10 ou 25.50" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                </div>
                
                <div class="mb-4">
                    <label for="tipoDesconto" class="block text-gray-700 font-semibold mb-2">Tipo de Desconto</label>
                    <select id="tipoDesconto" name="tipoDesconto" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="porcentagem">Porcentagem (%)</option>
                        <option value="fixo">Valor Fixo (R$)</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="dataValidade" class="block text-gray-700 font-semibold mb-2">Data de Validade</label>
                    <input type="text" id="dataValidade" name="dataValidade" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="DD/MM/YYYY" maxlength="10" oninput="formatDate(this)">
                </div>
                
                <div class="mb-6">
                    <label for="maxUsos" class="block text-gray-700 font-semibold mb-2">Máximo de Usos</label>
                    <input type="text" id="maxUsos" name="maxUsos" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: 100" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                
                <div id="modal-error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" style="display: none;"></div>
                
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeModal()" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-full font-semibold hover:bg-gray-400 transition-colors duration-300">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-full font-semibold shadow-md hover:bg-blue-700 transition-colors duration-300" id="confirm-button">
                        Criar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="loading-overlay" class="loading-overlay">
        <div class="text-white text-xl font-bold">Carregando...</div>
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