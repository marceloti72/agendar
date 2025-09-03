<?php
session_start();
require_once("../conexao.php"); // Ajuste o caminho conforme necessário

// Verificar se o usuário está autenticado
if (!isset($_SESSION['id_conta'])) {
    header('Location: login.php'); // Redireciona para login se não autenticado
    exit;
}

$id_conta = $_SESSION['id_conta'];

// Processar formulário de abertura de caixa
$mensagem = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    </style>

<body>
    <div class="container">
        <div class="form-header">
            <h2>Abertura de Caixa</h2>
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
            <button type="submit" class="btn btn-primary w-100">Abrir Caixa</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
