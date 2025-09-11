<?php
// Inicia a sessão para poder verificar
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Verifica se a variável de sessão NÃO existe
if (!isset($_SESSION['id_usuario'])) {
    // Se não existir, destrói qualquer resquício de sessão
    session_destroy();
    // Redireciona para o login e para a execução
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}
?>