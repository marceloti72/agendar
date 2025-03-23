<?php 
require_once('../conexao.php');
@session_start();

// Verifica se a sessão está definida
if (!isset($_SESSION['id_conta'])) {
    die("Erro: Sessão inválida ou não iniciada.");
}

$id_conta = $_SESSION['id_conta'];
$novoPlano = isset($_POST['novoPlano']) ? $_POST['novoPlano'] : null;

// Configuração inicial dos valores
$plano = null;
$frequencia = null;
$valor = null;

// Usa switch para determinar os valores com base no plano selecionado
switch ($novoPlano) {
    case 'individual_mensal':
        $plano = 1;
        $frequencia = 30;
        $valor = 49.90;
        break;
    case 'individual_anual':
        $plano = 1;
        $frequencia = 365;
        $valor = 526.94;
        break;
    case 'empresa_mensal':
        $plano = 2;
        $frequencia = 30;
        $valor = 79.90;
        break;
    case 'empresa_anual':
        $plano = 2;
        $frequencia = 365;
        $valor = 786.21;
        break;
    default:
        die("Erro: Plano inválido selecionado.");
}

// Conexão ao segundo banco de dados
try {
    $url = "https://{$_SERVER['HTTP_HOST']}/";
    $url2 = explode("//", $url);

    $host = ($url2[1] == 'localhost/') ? 'localhost' : 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
    $db = 'gestao_sistemas';
    $user = ($url2[1] == 'localhost/') ? 'root' : 'skysee';
    $pass = ($url2[1] == 'localhost/') ? '' : '9vtYvJly8PK6zHahjPUg';

    $pdo2 = new PDO("mysql:dbname=$db;host=$host;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Inicia uma transação para garantir consistência
$pdo2->beginTransaction();

try {
    // Atualiza a tabela config
    $query_config = $pdo->prepare("UPDATE config SET plano = :plano WHERE id = :id_conta");
    $query_config->bindValue(":plano", $plano, PDO::PARAM_INT);
    $query_config->bindValue(":id_conta", $id_conta, PDO::PARAM_INT);
    $query_config->execute();

    // Atualiza a tabela clientes
    $query_clientes = $pdo2->prepare("UPDATE clientes SET plano = :plano, frequencia = :frequencia, valor = :valor WHERE id_conta = :id_conta");
    $query_clientes->bindValue(":plano", $plano, PDO::PARAM_INT);
    $query_clientes->bindValue(":frequencia", $frequencia, PDO::PARAM_INT);
    $query_clientes->bindValue(":valor", $valor);
    $query_clientes->bindValue(":id_conta", $id_conta, PDO::PARAM_INT);
    $query_clientes->execute();

    // Busca o id_cliente da tabela clientes
    $query_cliente = $pdo2->prepare("SELECT id FROM clientes WHERE id_conta = :id_conta");
    $query_cliente->bindValue(":id_conta", $id_conta, PDO::PARAM_INT);
    $query_cliente->execute();
    $resultado = $query_cliente->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $id_cliente = $resultado['id'];

        // Atualiza a tabela receber
        $query_receber = $pdo2->prepare("UPDATE receber SET frequencia = :frequencia, valor = :valor WHERE cliente = :id_cliente AND (data_pgto IS NULL OR data_pgto = '' OR data_pgto = 'null')");
        $query_receber->bindValue(":frequencia", $frequencia, PDO::PARAM_INT);
        $query_receber->bindValue(":valor", $valor);
        $query_receber->bindValue(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $query_receber->execute();
    } else {
        throw new Exception("Cliente não encontrado.");
    }

    // Confirma a transação
    $pdo2->commit();
    echo 'Alterado com Sucesso';

} catch (Exception $e) {
    // Reverte a transação em caso de erro
    $pdo2->rollBack();
    die("Erro ao atualizar os dados: " . $e->getMessage());
}
?>