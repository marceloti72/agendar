<?php
require_once("../sistema/conexao.php");
// Iniciar a sessão
@session_start();

// Verificar se id_conta está na sessão
if (!isset($_SESSION['id_conta'])) {
    // Redirecionar para login ou página de erro
    header('Location: login.php');
    exit;
}
$id_conta = $_SESSION['id_conta'];

// Supondo que $pdo já está configurado (conexão com o banco de dados)
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos de Assinaturas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow-x: hidden;
        }
        .tela-cheia-assinaturas {
            min-height: 100vh;
            background-color: #295f41;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .tela-cheia-assinaturas .header {
            text-align: center;
            font-size: 1.75rem;
            font-weight: bold;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .tela-cheia-assinaturas .body {
            flex: 1;
            padding: 20px 0;
        }
        .tela-cheia-assinaturas .footer {
            text-align: center;
            padding: 15px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        .plano-img {
            max-width: 200px;
            height: auto;
        }
        .plano-item {
            background-color: #fff;
            color: #333;
            border-radius: 8px;
            overflow: hidden;
        }
        .plano-beneficios {
            font-size: 0.9rem;
        }
        .btn-inserir-depoimento hr {
            border-color: rgba(255, 255, 255, 0.3);
        }
        .btn-assinar {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="tela-cheia-assinaturas">
        <div class="header">Planos de Assinaturas</div>
        <div class="body">
            <div class="text-center btn-inserir-depoimento mb-4">
                <button type="button" id="btnIniciarBuscaAssinatura" class="btn btn-info btn-sm">
                    Ver Assinatura
                </button>
                <hr>
            </div>

            <div class="row justify-content-center">
                <?php
                try {
                    // Busca os planos ativos para a conta atual, ordenados
                    $query_planos = $pdo->prepare("SELECT * FROM planos WHERE id_conta = :id_conta ORDER BY ordem ASC, id ASC");
                    $query_planos->execute([':id_conta' => $id_conta]);
                    $planos = $query_planos->fetchAll(PDO::FETCH_ASSOC);

                    if (count($planos) > 0) {
                        foreach ($planos as $plano) {
                            $id_plano_atual = $plano['id'];
                            $nome_plano = htmlspecialchars($plano['nome']);
                            $preco_mensal_plano = number_format($plano['preco_mensal'], 2, ',', '.');
                            $imagem_plano = htmlspecialchars($plano['imagem'] ?: 'default-plano.jpg');
                            $caminho_imagem_plano = '../images/' . $imagem_plano;

                            // Busca os serviços associados a este plano
                            $query_servicos_plano = $pdo->prepare("
                                SELECT ps.quantidade, s.nome
                                FROM planos_servicos ps
                                JOIN servicos s ON ps.id_servico = s.id
                                WHERE ps.id_plano = :id_plano AND ps.id_conta = :id_conta
                                ORDER BY s.nome ASC
                            ");
                            $query_servicos_plano->execute([':id_plano' => $id_plano_atual, ':id_conta' => $id_conta]);
                            $servicos_incluidos = $query_servicos_plano->fetchAll(PDO::FETCH_ASSOC);

                            // Determina a classe do botão
                            $btn_class = 'btn-primary';
                            if (strtolower($plano['nome']) == 'ouro') $btn_class = 'btn-warning';
                            if (strtolower($plano['nome']) == 'diamante') $btn_class = 'btn-dark';
                            if (strtolower($plano['nome']) == 'bronze') $btn_class = 'btn-outline-primary';
                ?>
                            <div class="col-md-6 col-lg-5 mb-4">
                                <div class="plano-item card h-100 shadow-sm text-center">
                                    <img src="<?php echo $caminho_imagem_plano; ?>"
                                         class="card-img-top plano-img mt-3 mx-auto d-block"
                                         alt="Plano <?php echo $nome_plano; ?>"
                                         onerror="this.onerror=null; this.src='images/planos/default-plano.jpg';">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title plano-titulo"><?php echo $nome_plano; ?></h5>
                                        <p class="plano-preco">
                                            <strong>R$ <?php echo $preco_mensal_plano; ?></strong> / mês
                                        </p>
                                        <?php
                                        if (!empty($plano['preco_anual']) && $plano['preco_anual'] > 0):
                                            $preco_anual_plano = number_format($plano['preco_anual'], 2, ',', '.');
                                        ?>
                                            <p class="plano-preco-anual small text-muted mb-3">
                                                ou R$ <?php echo $preco_anual_plano; ?> / ano
                                                <?php
                                                $economia = ($plano['preco_mensal'] * 12) - $plano['preco_anual'];
                                                if ($economia > 0) {
                                                    echo '<br><span class="text-success font-weight-bold">(Economize R$ ' . number_format($economia, 2, ',', '.') . '!)</span>';
                                                }
                                                ?>
                                            </p>
                                        <?php else: ?>
                                            <div style="height: 3.5em;" class="mb-3"></div>
                                        <?php endif; ?>
                                        <ul class="list-unstyled mt-3 mb-4 plano-beneficios text-left">
                                            <?php if (count($servicos_incluidos) > 0): ?>
                                                <?php foreach ($servicos_incluidos as $servico):
                                                    $qtd_texto = '';
                                                    if ($servico['quantidade'] == 0) { $qtd_texto = 'Ilimitado - '; }
                                                    elseif ($servico['quantidade'] > 1) { $qtd_texto = $servico['quantidade'] . 'x '; }
                                                ?>
                                                    <li><i class="fas fa-check text-success mr-2"></i><?php echo $qtd_texto . htmlspecialchars($servico['nome']); ?></li>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <li><small>Consulte os benefícios incluídos.</small></li>
                                            <?php endif; ?>
                                        </ul>
                                        <a href="tela-assinante.php?id_plano=<?php echo $id_plano_atual; ?>" class="btn btn-lg btn-block <?php echo $btn_class; ?> btn-assinar mt-auto">
                                            Assinar <?php echo $nome_plano; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                <?php
                        }
                    } else {
                        echo '<div class="col-12"><p class="text-center text-muted">Nenhum plano de assinatura disponível no momento.</p></div>';
                    }
                } catch (PDOException $e) {
                    error_log("Erro ao buscar planos/serviços: " . $e->getMessage());
                    echo '<div class="col-12"><p class="text-center text-danger">Erro ao carregar os planos. Tente novamente mais tarde.</p></div>';
                }
                ?>
            </div>
        </div>
        <div class="footer">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Fechar</button>
        </div>
    </div>


    <div class="modal fade" id="modalPedirTelefone" tabindex="-1" aria-labelledby="modalPedirTelefoneLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm"> 
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #295f41;">
                <h5 class="modal-title" id="modalPedirTelefoneLabel">Buscar Assinatura</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <label for="inputTelefoneBusca" class="form-label">Digite o Telefone:</label>
                <input type="tel" class="form-control" id="inputTelefoneBusca" placeholder="(XX) XXXXX-XXXX" required>                 
                 <div class="invalid-feedback">Por favor, informe um telefone válido.</div>
                 <label for="senha2" class="form-label">Senhe:</label>
                 <input type="text" class="form-control" id="senha2" required>
            </div>
            <div class="modal-footer text-white" style="background-color: #295f41;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnBuscarPorTelefone">Buscar Detalhes</button>
            </div>
        </div>
    </div>
</div>


    

    <!-- jQuery e Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

    <script src="js/ass.js"></script>

    <script>
        $('#inputTelefoneBusca').mask('(00) 00000-0000');
    </script>
</body>
</html>