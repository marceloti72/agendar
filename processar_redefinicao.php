<?php

// Inclua seu arquivo de conexão com o banco de dados.
// Exemplo: 'conexao.php'
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
        // Criptografe a nova senha antes de salvar no banco de dados.
        $senha_criptografada = password_hash($nova_senha, PASSWORD_DEFAULT);

        // Utilize a sua estrutura de UPDATE
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE email = :email");
        $stmt->bindParam(':senha', $senha_criptografada);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Verifique se alguma linha foi afetada para saber se a redefinição foi bem-sucedida
        if ($stmt->rowCount() > 0) {
            echo "Senha redefinida com sucesso! Redirecionando para a página de login...";
            
            // Redirecionar para a página de login
            header("Location: https://www.markai.skysee.com.br/login.php");
            exit();
        } else {
            echo "Email não encontrado ou senha já atualizada.";
        }

    } catch (PDOException $e) {
        die("Erro ao redefinir a senha: " . $e->getMessage());
    }
} else {
    // Se a requisição não for POST, redirecione o usuário para o formulário.
    header("Location: redefinir_senha.html");
    exit();
}
?>