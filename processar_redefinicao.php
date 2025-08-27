<?php

// Inclua seu arquivo de conexão com o banco de dados.
require_once("sistema/conexao.php");

// Verifique se os dados do formulário foram enviados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $nova_senha = $_POST['nova_senha'];

    // Validação básica
    if (empty($email) || empty($nova_senha)) {
        die("Email e senha são obrigatórios.");
    }

    // Verifique se a senha tem 6 dígitos.
    if (strlen($nova_senha) !== 6) {
        die("A senha deve ter exatamente 6 dígitos.");
    }

    try {
        // ATENÇÃO: Nunca armazene senhas em texto puro! 
        // Use password_hash() para criptografar a senha antes de salvar no banco de dados.
        // O código abaixo está vulnerável se for usado em produção.
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE email = :email");
        $stmt->bindParam(':senha', $nova_senha);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Verifique se alguma linha foi afetada para saber se a redefinição foi bem-sucedida
        if ($stmt->rowCount() > 0) {
            echo "Senha redefinida com sucesso! Redirecionando para a página de login...";
            
            // Redirecionar para a página de login após 3 segundos.
            header("refresh:3; url=https://www.markai.skysee.com.br/login.php");
            exit();
        } else {
            // Exibir o alerta e redirecionar com JavaScript
            echo "<script>
                    alert('Email não encontrado ou senha já atualizada.');
                    setTimeout(function() {
                        window.location.href = 'https://www.markai.skysee.com.br/redefinir_senha.html';
                    }, 3000); // 3000ms = 3 segundos
                  </script>";
            exit();
        }

    } catch (PDOException $e) {
        // Exibir o alerta de erro e redirecionar
        echo "<script>
                alert('Erro ao redefinir a senha: " . addslashes($e->getMessage()) . "');
                setTimeout(function() {
                    window.location.href = 'https://www.markai.skysee.com.br/redefinir_senha.html';
                }, 3000);
              </script>";
        exit();
    }
} else {
    // Se a requisição não for POST, redirecione o usuário para o formulário.
    header("Location: https://www.markai.skysee.com.br/redefinir_senha.html");
    exit();
}
?>