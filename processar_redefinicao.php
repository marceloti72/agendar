<?php

// Inclua seu arquivo de conexão com o banco de dados.
require_once("sistema/conexao.php");

// Valida se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // 1. Verifique se as senhas coincidem
    if ($nova_senha !== $confirmar_senha) {
        echo "<script>
                alert('As senhas não coincidem. Por favor, tente novamente.');
                setTimeout(function() {
                    window.location.href = 'https://www.markai.skysee.com.br/redefinir_senha.html';
                }, 3000); // 3 segundos
              </script>";
        exit();
    }

    // 2. Valide o tamanho da senha
    if (strlen($nova_senha) !== 6) {
        echo "<script>
                alert('A senha deve ter exatamente 6 dígitos.');
                setTimeout(function() {
                    window.location.href = 'https://www.markai.skysee.com.br/redefinir_senha.html';
                }, 3000);
              </script>";
        exit();
    }

    try {
        // Criptografe a senha (prática de segurança essencial)
        //$senha_criptografada = password_hash($nova_senha, PASSWORD_DEFAULT);

        // Prepare a atualização no banco de dados
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE email = :email");
        $stmt->bindParam(':senha', $nova_senha);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Verifique se a atualização foi bem-sucedida
        if ($stmt->rowCount() > 0) {
            
            // REDIRECIONA PARA A PÁGINA DE SUCESSO E DEPOIS PARA O LOGIN
            // Primeiro, mostre a tela de sucesso
            echo '
            <!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Sucesso!</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        margin: 0;
                        text-align: center;
                    }
                    .container-success {
                        background-color: #fff;
                        padding: 40px;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    }
                    .icon-success {
                        color: #28a745;
                        font-size: 80px;
                        margin-bottom: 20px;
                    }
                    .message {
                        font-size: 24px;
                        color: #333;
                    }
                </style>
                <meta http-equiv="refresh" content="3;url=https://www.markai.skysee.com.br/login.php" />
            </head>
            <body>
                <div class="container-success">
                    <i class="fas fa-check-circle icon-success"></i>
                    <p class="message">Senha redefinida com sucesso!<br>Redirecionando para a página de login...</p>
                </div>
            </body>
            </html>
            ';
            exit();
            
        } else {
            // Se o e-mail não foi encontrado
            echo "<script>
                    alert('Email não encontrado ou a senha não foi alterada.');
                    setTimeout(function() {
                        window.location.href = 'https://www.markai.skysee.com.br/redefinir_senha.html';
                    }, 3000);
                  </script>";
            exit();
        }

    } catch (PDOException $e) {
        // Em caso de erro do banco de dados
        echo "<script>
                alert('Erro ao redefinir a senha: " . addslashes($e->getMessage()) . "');
                setTimeout(function() {
                    window.location.href = 'https://www.markai.skysee.com.br/redefinir_senha.html';
                }, 3000);
              </script>";
        exit();
    }
} else {
    // Redireciona se o formulário não foi enviado corretamente
    header("Location: https://www.markai.skysee.com.br/redefinir_senha.html");
    exit();
}
?>