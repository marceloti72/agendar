<?php 
@session_start(); // Inicia a sessão
@session_destroy(); // Destrói a sessão
header('Location: ../../login.php'); // Redireciona para a página de login
exit(); // Garante que o script pare de ser executado
?>