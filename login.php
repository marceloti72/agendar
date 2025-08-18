<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Login MARKAI - Gestão de Serviços</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <style>
        .login-card {
            background-color: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 3rem;
            width: 100%;
            max-width: 420px;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            outline: none;
            transition: all 0.3s;
        }
        .form-input:focus {
            border-color: #a78bfa;
            box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.4);
        }
        .login-button {
            width: 100%;
            padding: 0.75rem;
            background-image: #5c5ff6ff;
            color: white;
            font-weight: bold;
            border-radius: 0.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        /* NOVO CSS PARA O EFEITO PULSANTE DO LOGO */
        .logo-pulse {
            animation: pulse-glow 2s infinite cubic-bezier(0.4, 0, 0.6, 1);
            transition: transform 0.3s ease-in-out;
            cursor: pointer;
        }

        .logo-pulse:hover {
            transform: scale(1.05);
        }

        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(139, 92, 246, 0);
            }
        }

        /* NOVO ESTILO PARA O BOTÃO DE DOWNLOAD */
        .download-app-button {
            width: 100%;
            padding: 0.75rem;
            background-color: #10b981; /* green-500 */
            color: white;
            font-weight: bold;
            border-radius: 0.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .download-app-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            color: white; /* Mantém a cor do texto branca ao passar o mouse */
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">

    <div class="flex flex-col items-center justify-center min-h-screen p-6" style="background-image: linear-gradient(to right, #4A90E2, #50C9C3);">
        
        <div class="login-card">
            <div class="text-center mb-8">
                <img src="sistema/img/icon.png" alt="Logo MarkAi" class="mx-auto w-3/4 max-w-xs mb-4 rounded-full logo-pulse">
                
                <h2 class="text-2xl font-semibold text-gray-800">Bem-vindo de volta!</h2>
            </div>
            
            <form action="sistema/autenticar.php" method="post" class="space-y-6">
                <div>
                    <input class="form-input" type="text" name="usuario" placeholder="Usuário">
                </div>

                <div class="relative">
                    <input class="form-input pr-10" type="password" name="senha" placeholder="Senha" id="senhaInput">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer" id="togglePassword">
                        <i class="fas fa-eye text-gray-400"></i>
                    </span>
                </div>

                <div class="text-center">
                    <button type="submit" class="login-button">
                        Entrar
                    </button>
                </div>
            </form>

            <div class="text-center mt-4">
                <a href="app.html" class="download-app-button">
                    <i class="fas fa-download"></i>
                    Baixar o App MARKAI
                </a>
            </div>
            
            <div class="text-center mt-6 text-sm">
                <p class="text-gray-600 mb-2">Primeiro acesso? 
                    <a href="#" data-toggle="modal" data-target="#modalRecuperar" class="font-bold text-indigo-600 hover:underline">
                        Cadastrar Senha
                    </a>
                </p>
                <p class="text-gray-600">Esqueceu a senha? 
                    <a href="#" data-toggle="modal" data-target="#modalRecuperar" class="font-bold text-red-600 hover:underline">
                        Recuperar
                    </a>
                </p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRecuperar" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #6d28d9;">
                    <h5 class="modal-title font-bold" id="staticBackdropLabel">Cadastrar Nova Senha</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="form">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Seu Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                        </div>
                        <small><div id="mensagem" class="text-center mt-2"></div></small>
                    </div>
                    <div class="modal-footer">
                        <button id="btn-fechar" type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-info" style="background-color: #7c3aed; border-color: #7c3aed;">Recuperar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        $("#form").submit(function (event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "recuperar.php",
                type: 'POST',
                data: formData,
                success: function (mensagem) {
                    $('#mensagem').removeClass().addClass('text-danger').text(mensagem.trim());
                    if (mensagem.trim() == "Sua senha foi Enviada para seu Email!") {
                        Swal.fire({
                            title: "Enviado com sucesso!",
                            text: "Sua senha foi enviada para seu Email e WhatsApp! Verifique a caixa de spam.",
                            icon: "success"
                        }).then((result) => {
                            if(result.isConfirmed){
                                window.location = "index.php";
                            }
                        });
                        $('#mensagem').removeClass().addClass('text-success').text(mensagem);
                    }
                },
                cache: false,
                contentType: false,
                processData: false,
                xhr: function () {
                    var myXhr = $.ajaxSettings.xhr();
                    if (myXhr.upload) {
                        myXhr.upload.addEventListener('progress', function () { /* ... */ }, false);
                    }
                    return myXhr;
                }
            });
        });

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('senhaInput');
        togglePassword.addEventListener('click', function (e) {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>