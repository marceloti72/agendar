<?php
require_once("../sistema/conexao.php");
// Iniciar a sessão
@session_start();

// Verificar se id_conta está na sessão
if (!isset($_SESSION['id_conta'])) {
    header('Location: login.php');
    exit;
}
$id_conta = $_SESSION['id_conta'];

// Obter id_plano da URL
$id_plano = isset($_GET['id_plano']) ? intval($_GET['id_plano']) : 0;
if ($id_plano <= 0) {
    header('Location: tela-assinaturas.php');
    exit;
}

// Supondo que $pdo já está configurado
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Assinatura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow-x: hidden;
        }
        .tela-cheia-assinante {
            min-height: 100vh;
            background-color: #4682B4;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .tela-cheia-assinante .header {
            text-align: center;
            font-size: 1.75rem;
            font-weight: bold;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .tela-cheia-assinante .body {
            flex: 1;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .tela-cheia-assinante .footer {
            text-align: center;
            padding: 15px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        .form-group label {
            color: #fff;
        }
        .form-control {
            background-color: #fff;
            color: #333;
        }
        .input-group-text {
            background-color: #fff;
            color: #333;
        }
        #mensagem-assinante2 {
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="tela-cheia-assinante">
        <div class="header">Adicionar Assinatura</div>
        <div class="body">
            <form id="form-assinante2" method="post">
                <input type="hidden" id="id_assinante" name="id_assinante">
                <input type="hidden" id="id_cliente_encontrado" name="id_cliente_encontrado">
                <input type="hidden" name="id_conta" value="<?php echo htmlspecialchars($id_conta); ?>">
                <input type="hidden" name="id_plano" value="<?php echo $id_plano; ?>">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ass_telefone">Telefone / WhatsApp <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="ass_telefone" name="telefone" placeholder="(DDD) Número" required onblur="buscarClientePorTelefone(this.value)">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="telefone-loading" style="display: none;"><i class="fas fa-spinner fa-spin"></i></span>
                                    <span class="input-group-text" id="telefone-status"></span>
                                </div>
                            </div>
                            <small id="mensagem-busca-cliente" class="form-text text-white"></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ass_nome">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ass_nome" name="nome" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ass_cpf">CPF</label>
                            <input type="text" class="form-control" id="ass_cpf" name="cpf">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ass_email">Email</label>
                            <input type="email" class="form-control" id="ass_email" name="email">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ass_senha">Senha</label>
                            <input type="password" class="form-control" id="ass_senha" name="senha" placeholder="Digite a senha">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ass_confirma_senha">Confirmar Senha</label>
                            <input type="password" class="form-control" id="ass_confirma_senha" name="confirma_senha" placeholder="Confirme a senha">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ass_plano_freq">Plano e Frequência <span class="text-danger">*</span></label>
                            <select class="form-control" id="ass_plano_freq" name="plano_freq_selecionado" required>
                                <option value=""><b>*Selecione o Plano e a Frequência*<b></option>
                                <?php
                                $planos_disponiveis = [];
                                try {
                                    $query_p = $pdo->prepare("SELECT id, nome FROM planos WHERE id_conta = :id_conta ORDER BY nome ASC");
                                    $query_p->execute([':id_conta' => $id_conta]);
                                    $planos_disponiveis = $query_p->fetchAll(PDO::FETCH_ASSOC);
                                } catch (PDOException $e) {
                                    error_log("Erro ao buscar planos: " . $e->getMessage());
                                }
                                if (isset($planos_disponiveis) && count($planos_disponiveis) > 0) {
                                    foreach ($planos_disponiveis as $plano_opt) {
                                        $id_plano_opt = $plano_opt['id'];
                                        $nome_plano_opt = htmlspecialchars($plano_opt['nome']);
                                        $query_precos = $pdo->prepare("SELECT preco_mensal, preco_anual FROM planos WHERE id = :id AND id_conta = :id_conta");
                                        $query_precos->execute([':id' => $id_plano_opt, ':id_conta' => $id_conta]);
                                        $precos = $query_precos->fetch(PDO::FETCH_ASSOC);
                                        if ($precos) {
                                            $preco_m_fmt = number_format($precos['preco_mensal'], 2, ',', '.');
                                            $selected = ($id_plano_opt == $id_plano && !isset($_GET['freq']) || $_GET['freq'] == '30') ? 'selected' : '';
                                            echo "<option value='{$id_plano_opt}-30' {$selected}>{$nome_plano_opt} - Mensal (R$ {$preco_m_fmt})</option>";
                                            if (!empty($precos['preco_anual']) && $precos['preco_anual'] > 0) {
                                                $preco_a_fmt = number_format($precos['preco_anual'], 2, ',', '.');
                                                $selected = ($id_plano_opt == $id_plano && isset($_GET['freq']) && $_GET['freq'] == '365') ? 'selected' : '';
                                                echo "<option value='{$id_plano_opt}-365' {$selected}>{$nome_plano_opt} - Anual (R$ {$preco_a_fmt})</option>";
                                            }
                                        }
                                    }
                                } else {
                                    echo '<option value="">Nenhum plano cadastrado</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <small><div id="mensagem-assinante2" class="mt-2"></div></small>
            </form>
        </div>
        <div class="footer">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancelar</button>
            <button type="button" class="btn btn-warning" id="btnSalvarAssinante2" onclick="validarSenhas()">Assinar</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

    

    <script>
        $('#ass_telefone').mask('(00) 00000-0000');
        $('#inputTelefoneBusca').mask('(00) 00000-0000');
        $('#ass_cpf').mask('000.000.000-00', {reverse: true});

        function buscarClientePorTelefone(telefone) {
            if (!telefone) return;
            $('#telefone-loading').show();
            $('#telefone-status').html('');
            $('#mensagem-busca-cliente').html('');

            $.ajax({
                url: 'buscar_cliente.php',
                method: 'POST',
                data: { telefone: telefone, id_conta: '<?php echo htmlspecialchars($id_conta); ?>' },
                dataType: 'json',
                success: function (response) {
                    $('#telefone-loading').hide();
                    if (response.success && response.cliente) {
                        $('#telefone-status').html('<i class="fas fa-check text-success"></i>');
                        $('#mensagem-busca-cliente').html('Cliente encontrado!');
                        $('#id_cliente_encontrado').val(response.cliente.id);
                        $('#ass_nome').val(response.cliente.nome);
                        $('#ass_cpf').val(response.cliente.cpf);
                        $('#ass_email').val(response.cliente.email);
                    } else {
                        $('#telefone-status').html('<i class="fas fa-times text-danger"></i>');
                        $('#mensagem-busca-cliente').html(response.message || 'Cliente não encontrado.');
                        $('#id_cliente_encontrado').val('');
                        $('#ass_nome').val('');
                        $('#ass_cpf').val('');
                        $('#ass_email').val('');
                    }
                },
                error: function () {
                    $('#telefone-loading').hide();
                    $('#telefone-status').html('<i class="fas fa-times text-danger"></i>');
                    $('#mensagem-busca-cliente').html('Erro ao buscar cliente.');
                }
            });
        }

        function validarSenhas() {
            const senha = $('#ass_senha').val();
            const confirmaSenha = $('#ass_confirma_senha').val();
            const mensagem = $('#mensagem-assinante2');

            if (senha && senha !== confirmaSenha) {
                mensagem.html('<span class="text-danger">As senhas não coincidem!</span>');
                return;
            }

            // Submeter o formulário
            const formData = $('#form-assinante2').serialize();
            $.ajax({
                url: 'salvar_assinatura.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        mensagem.html('<span class="text-success">' + response.message + '</span>');
                        setTimeout(() => {
                            window.location.href = 'tela-assinaturas.php';
                        }, 2000);
                    } else {
                        mensagem.html('<span class="text-danger">' + response.message + '</span>');
                    }
                },
                error: function () {
                    mensagem.html('<span class="text-danger">Erro ao salvar assinatura.</span>');
                }
            });
        }


        function validarSenhas() {    
    const senha = document.getElementById('ass_senha').value;
    const confirmaSenha = document.getElementById('ass_confirma_senha').value;
    const mensagem = document.getElementById('mensagem-assinante2');

    if (senha && confirmaSenha && senha !== confirmaSenha) {
        mensagem.innerHTML = '<span class="text-danger">As senhas não coincidem.</span>';
        return;
    }

    // If passwords match or are empty, proceed with form submission    
    $('#form-assinante2').submit();
}


$('#form-assinante2').on('submit', function(e) {    
     e.preventDefault(); // Impede envio normal
     const form = this;
     const formData = new FormData(form); // Pega todos os dados, incluindo id_cliente_encontrado
     const $btnSubmit = $('#btnSalvarAssinante');
     const $msgDiv = $('#mensagem-assinante2');

     $btnSubmit.prop('disabled', true).text('Salvando...');
     $msgDiv.text('').removeClass('text-danger text-success');

     $.ajax({
        url: 'salvar_assinante.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        success: function(response) {
             if (response.success) {                
                 $msgDiv.addClass('text-success').text(response.message);
                  setTimeout(function() {                    
                     //$('#modalAssinante2').modal('hide');
                     window.location="../pagar_ass/"+response.id_receber;
                  }, 1500);
             } else {
                 $msgDiv.addClass('text-danger').text(response.message);
                 $btnSubmit.prop('disabled', false).text('Salvar Assinante');
             }
         },
         error: function(xhr) {
             $msgDiv.addClass('text-danger').text('Erro de comunicação. Verifique o console.');
             console.error("Erro ao salvar assinante:", xhr.responseText);
             $btnSubmit.prop('disabled', false).text('Salvar Assinante');
         }
     });
});


// Função para verificar se o telefone existe no banco de clientes e se está associado a um assinante ativo
function buscarClientePorTelefone(telefone) {
    // Se o telefone estiver vazio, limpa os campos e habilita o botão
    if (!telefone) {
        document.getElementById('mensagem-busca-cliente').innerHTML = '';
        document.getElementById('id_cliente_encontrado').value = '';
        document.getElementById('ass_nome').value = '';
        document.getElementById('ass_cpf').value = '';
        document.getElementById('ass_email').value = '';
        document.getElementById('telefone-loading').style.display = 'none';
        document.getElementById('telefone-status').innerHTML = '';
        document.getElementById('btnSalvarAssinante2').disabled = false;
        return;
    }

    // Exibe o ícone de carregamento
    document.getElementById('telefone-loading').style.display = 'inline-block';
    document.getElementById('mensagem-busca-cliente').innerHTML = '';
    document.getElementById('telefone-status').innerHTML = '';

    // Faz a requisição AJAX para verificar o cliente e o status de assinante
    fetch('verificar_telefone.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'telefone=' + encodeURIComponent(telefone) + '&id_conta=<?= $id_conta ?>'
    })
    .then(response => response.json())
    .then(data => {
        // Oculta o ícone de carregamento
        document.getElementById('telefone-loading').style.display = 'none';

        // Se houver erro na requisição
        if (data.error) {
            document.getElementById('mensagem-busca-cliente').innerHTML = '<span class="text-danger">' + data.error + '</span>';
            document.getElementById('id_cliente_encontrado').value = '';
            document.getElementById('ass_nome').value = '';
            document.getElementById('ass_cpf').value = '';
            document.getElementById('ass_email').value = '';
            document.getElementById('telefone-status').innerHTML = '<i class="fas fa-times text-danger"></i>';
            document.getElementById('btnSalvarAssinante2').disabled = false;
            document.getElementById('ass_telefone').focus();
            return;
        }

        // Se o cliente for encontrado
        if (data.cliente) {
            // Se o cliente já for um assinante ativo, bloqueia o formulário
            if (data.is_assinante) {
                document.getElementById('mensagem-busca-cliente').innerHTML = '<span  style="color:rgb(243, 247, 7);"><i class="fas fa-times text-danger"></i> Este telefone já está associado a um assinante ativo. Insira outro número.</span>';
                document.getElementById('id_cliente_encontrado').value = '';
                document.getElementById('ass_nome').value = '';
                document.getElementById('ass_cpf').value = '';
                document.getElementById('ass_email').value = '';
                document.getElementById('telefone-status').innerHTML = '<i class="fas fa-times text-danger"></i>';
                document.getElementById('btnSalvarAssinante2').disabled = true;
                document.getElementById('ass_telefone').focus();
            } else {
                // Cliente encontrado, mas não é assinante: permite prosseguir
                document.getElementById('id_cliente_encontrado').value = data.cliente.id;
                document.getElementById('ass_nome').value = data.cliente.nome || '';
                document.getElementById('ass_cpf').value = data.cliente.cpf || '';
                document.getElementById('ass_email').value = data.cliente.email || '';
                document.getElementById('mensagem-busca-cliente').innerHTML = '<span class="text-success">Cliente encontrado.</span>';
                document.getElementById('telefone-status').innerHTML = '<i class="fas fa-check text-success"></i>';
                document.getElementById('btnSalvarAssinante2').disabled = false;
            }
        } else {
            // Nenhum cliente encontrado: permite prosseguir
            document.getElementById('id_cliente_encontrado').value = '';
            document.getElementById('ass_nome').value = '';
            document.getElementById('ass_cpf').value = '';
            document.getElementById('ass_email').value = '';
            document.getElementById('mensagem-busca-cliente').innerHTML = '<span class="text-info">Novo cliente para cadastro.</span>';
            document.getElementById('telefone-status').innerHTML = '<i class="fas fa-check text-success"></i>';
            document.getElementById('btnSalvarAssinante2').disabled = false;
        }
    })
    .catch(error => {
        // Em caso de erro na requisição
        document.getElementById('telefone-loading').style.display = 'none';
        document.getElementById('mensagem-busca-cliente').innerHTML = '<span class="text-danger">Erro ao verificar telefone.</span>';
        document.getElementById('id_cliente_encontrado').value = '';
        document.getElementById('ass_nome').value = '';
        document.getElementById('ass_cpf').value = '';
        document.getElementById('ass_email').value = '';
        document.getElementById('telefone-status').innerHTML = '<i class="fas fa-times text-danger"></i>';
        document.getElementById('btnSalvarAssinante2').disabled = false;
        document.getElementById('ass_telefone').focus();
        console.error('Erro:', error);
    });
}
    </script>
</body>
</html>