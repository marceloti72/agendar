<?php
require_once("../sistema/conexao.php");
@session_start();

if (!isset($_SESSION['id_conta'])) {
    header('Location: login.php');
    exit;
}
$id_conta = $_SESSION['id_conta'];

$telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
$senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';

if (empty($telefone) || empty($senha)) {
    header('Location: tela-assinaturas.php?erro=telefone_senha_vazio');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Assinatura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow-x: hidden;
        }
        .tela-cheia-detalhes {
            min-height: 100vh;
            /* background-color: #C0C0C0; */
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .tela-cheia-detalhes .header {
            background-color: #4682B4;
            border-radius: 10px;
            text-align: center;
            font-size: 1.5rem;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .tela-cheia-detalhes .body {
            flex: 1;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .tela-cheia-detalhes .footer {
            text-align: center;
            padding: 15px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        .table {
            background-color: #fff;
            color: #333;
        }
        .table th, .table td {
            border-color: #ddd;
        }
        #modalAssinaturaErro, #modalAssinaturaConteudo, #modalAssinaturaLoading {
            color: #333;
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
        }
        #modalAssinaturaLoading {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="tela-cheia-detalhes">
        <div class="header">Detalhes da Assinatura</div>
        <div class="body">
            <div id="modalAssinaturaLoading" style="display: none;" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p>Buscando detalhes...</p>
            </div>
            <div id="modalAssinaturaErro" class="alert alert-danger" style="display: none;">
                Não foi possível carregar os detalhes da assinatura.
            </div>
            <div id="modalAssinaturaConteudo">
                <p><strong>Cliente:</strong> <span id="modalAssinaturaClienteNome" class="text-primary"></span></p>
                <p><strong>Plano Atual:</strong> <span id="modalAssinaturaPlanoNome" class="fw-bold"></span></p>
                <p><strong>Próximo Vencimento:</strong> <span id="modalAssinaturaProximoVenc" class="text-danger"></span></p>
                <hr>
                <h6>Serviços Incluídos e Uso no Ciclo Atual:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Serviço</th>
                                <th class="text-center">Uso Atual</th>
                                <th class="text-center">Limite no Ciclo</th>
                            </tr>
                        </thead>
                        <tbody id="modalAssinaturaServicosBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="footer">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Fechar</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            function buscarDetalhesAssinatura() {
                $('#modalAssinaturaLoading').show();
                $('#modalAssinaturaErro').hide();
                $('#modalAssinaturaConteudo').hide();

                $.ajax({
                    url: 'buscar_detalhes_assinatura.php',
                    method: 'POST',
                    data: {
                        telefone: '<?php echo htmlspecialchars($telefone); ?>',
                        s: '<?php echo htmlspecialchars($senha); ?>',
                        id_conta: '<?php echo htmlspecialchars($id_conta); ?>'
                    },
                    dataType: 'json',
                    success: function (response) {                        
                        $('#modalAssinaturaLoading').hide();
                        if (response.success && response.assinatura) {
                            $('#modalAssinaturaConteudo').show();
                            $('#modalAssinaturaClienteNome').text(response.assinatura.cliente_nome || 'N/A');
                            $('#modalAssinaturaPlanoNome').text(response.assinatura.plano_nome || 'N/A');
                            $('#modalAssinaturaProximoVenc').text(response.assinatura.proximo_vencimento || 'N/A');

                            $('#modalAssinaturaProximoVenc').text(formatarData(response.assinatura.proximo_vencimento) || 'N/A');

                            const tbody = $('#modalAssinaturaServicosBody');
                            tbody.empty();
                            if (response.assinatura.servicos && response.assinatura.servicos.length > 0) {
                                response.assinatura.servicos.forEach(function (servico) {
                                    const limite = servico.limite === 0 ? 'Ilimitado' : servico.limite;
                                    const row = `
                                        <tr>
                                            <td>${servico.nome}</td>
                                            <td class="text-center">${servico.uso_atual}</td>
                                            <td class="text-center">${limite}</td>
                                        </tr>
                                    `;
                                    tbody.append(row);
                                });
                            } else {
                                tbody.append('<tr><td colspan="3">Nenhum serviço incluído.</td></tr>');
                            }
                        } else {
                            $('#modalAssinaturaErro').text(response.message || 'Não foi possível carregar os detalhes da assinatura.');
                            $('#modalAssinaturaErro').show();
                        }
                    },
                    error: function () {
                        $('#modalAssinaturaLoading').hide();
                        $('#modalAssinaturaErro').text('Erro ao buscar detalhes da assinatura.');
                        $('#modalAssinaturaErro').show();
                    }
                });
            }

            buscarDetalhesAssinatura();
        });


        function formatarData(data) {
            if (!data) return '';
            const [ano, mes, dia] = data.split('-');
            return `${dia}/${mes}/${ano}`;
        }

        
    </script>
</body>
</html>