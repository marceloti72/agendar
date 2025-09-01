<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/teste_errors.log');

// Tenta criar um log
file_put_contents(__DIR__ . '/teste_log.txt', date('Y-m-d H:i:s') . " - Teste iniciado\n", FILE_APPEND);

// Tenta iniciar sessão
@session_start();
file_put_contents(__DIR__ . '/teste_log.txt', date('Y-m-d H:i:s') . " - Sessão iniciada, id_conta: " . ($_SESSION['id_conta'] ?? 'NÃO DEFINIDA') . "\n", FILE_APPEND);

// Verifica conexao.php
$conexaoPath = __DIR__ . '/../../../conexao.php';
if (file_exists($conexaoPath)) {
    file_put_contents(__DIR__ . '/teste_log.txt', date('Y-m-d H:i:s') . " - conexao.php encontrado\n", FILE_APPEND);
} else {
    file_put_contents(__DIR__ . '/teste_log.txt', date('Y-m-d H:i:s') . " - ERRO: conexao.php NÃO encontrado em $conexaoPath\n", FILE_APPEND);
}

// Verifica vendor/autoload.php
$vendorPath = '/../../../../../vendor/autoload.php';
if (file_exists($vendorPath)) {
    file_put_contents(__DIR__ . '/teste_log.txt', date('Y-m-d H:i:s') . " - vendor/autoload.php encontrado\n", FILE_APPEND);
} else {
    file_put_contents(__DIR__ . '/teste_log.txt', date('Y-m-d H:i:s') . " - ERRO: vendor/autoload.php NÃO encontrado em $vendorPath\n", FILE_APPEND);
}

echo "Teste concluído. Verifique teste_log.txt.";
?>