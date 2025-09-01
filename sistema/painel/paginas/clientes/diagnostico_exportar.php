<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Iniciando Diagnóstico...</h2>";

echo "<h3>Passo 1: Verificando a Sessão</h3>";
@session_start();
if (isset($_SESSION['id_conta'])) {
    echo "<p>✔️ Variável de sessão 'id_conta' encontrada. Valor: " . htmlspecialchars($_SESSION['id_conta']) . "</p>";
} else {
    echo "<p>❌ Erro: Variável de sessão 'id_conta' não está definida.</p>";
    exit();
}

echo "<h3>Passo 2: Verificando Arquivo de Conexão</h3>";
$conexaoPath = __DIR__ . '/../../../conexao.php';
if (file_exists($conexaoPath)) {
    echo "<p>✔️ Arquivo de conexão encontrado em: " . htmlspecialchars($conexaoPath) . "</p>";
} else {
    echo "<p>❌ Erro: Arquivo de conexão NÃO encontrado em: " . htmlspecialchars($conexaoPath) . "</p>";
    exit();
}

echo "<h3>Passo 3: Verificando Biblioteca PhpSpreadsheet</h3>";
$vendorPath = __DIR__ . '/../../../../vendor/autoload.php';
if (file_exists($vendorPath)) {
    echo "<p>✔️ Arquivo 'vendor/autoload.php' encontrado em: " . htmlspecialchars($vendorPath) . "</p>";
} else {
    echo "<p>❌ Erro: Arquivo 'vendor/autoload.php' NÃO encontrado em: " . htmlspecialchars($vendorPath) . "</p>";
    exit();
}

echo "<h3>Passo 4: Carregando a Biblioteca</h3>";
require_once $vendorPath;
echo "<p>✔️ Biblioteca PhpSpreadsheet carregada com sucesso!</p>";

echo "<h3>Passo 5: Conectando ao Banco de Dados</h3>";
require_once $conexaoPath;
try {
    $pdo->query("SELECT 1");
    echo "<p>✔️ Conexão PDO com o banco de dados OK!</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro de conexão com o banco de dados: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit();
}

echo "<h3>Diagnóstico Concluído com Sucesso!</h3>";
echo "<p>O problema não está em nenhum dos passos acima. Por favor, volte ao script original.</p>";
?>
