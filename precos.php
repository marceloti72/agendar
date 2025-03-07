<?php
require_once("cabecalho.php");
// Simulação de lógica para determinar preços (você pode ajustar conforme sua lógica de negócios)
$planos = [
    'empresa' => [
        'mensal' => ['preco' => 59.90, 'desconto_anual' => 25],
        'anual' => ['preco' => 539.00, 'economia' => 179.80]
    ],
    'individual' => [
        'mensal' => ['preco' => 29.90, 'desconto_anual' => 12],
        'anual' => ['preco' => 314.90, 'economia' => 43.90]
    ]
];

// Determinar o tipo de plano (padrão: mensal)
$tipo_plano = isset($_GET['tipo']) && $_GET['tipo'] === 'anual' ? 'anual' : 'mensal';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos AGENDAR</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            padding: 0;
        }
        .planos-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            gap: 30px;
        }
        .plano-card {
            flex: 1;
            min-width: 300px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .plano-card:hover {
            transform: translateY(-5px);
        }
        .plano-empresa {
            background: linear-gradient(135deg, #4a148c, #7b1fa2);
            color: white;
        }
        .plano-individual {
            background: white;
            color: #333;
            border: 1px solid #dee2e6;
        }
        .plano-titulo {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .preco {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .economia {
            font-size: 0.9rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        .funcionalidade {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        .funcionalidade i {
            color: #e1bee7;
            margin-right: 10px;
        }
        .btn-teste {
            display: block;
            width: 100%;
            padding: 10px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            color: #4a148c;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }
        .btn-teste:hover {
            background: #f8f9fa;
            color: #7b1fa2;
        }
        .toggle-plano {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            margin-top: 10px;
            padding: 10px;
        }
        .toggle-plano .btn {
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: bold;
            background: #6c757d;
            color: white;
            border: none;
            margin: 5px;
        }
        .toggle-plano .btn.active {
            background: #4a148c;
            color: white;
        }
        .desconto {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }
        #desc_anual {
            background-color: white;
            color: black;
            border-radius: 30px;
            padding: 5px;
        }
        .modal-content {
            border-radius: 15px;
        }
        .modal-body {
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .btn-concluir {
            background: #4a148c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-concluir:hover {
            background: #7b1fa2;
        }
    </style>
</head>
<body>
<div style="display: flex; align-items: center; justify-content: center; height: 200px; ">
  <div style="text-align: center;">
    <h1>Planos e Preços</h1>
    <h3>Escolha o plano da AGENDAR que melhor encaixe na sua gestão de negócio.</h3>
  </div>
</div>
    <div class="toggle-plano">
        <button class="btn <?php echo $tipo_plano === 'mensal' ? 'active' : ''; ?>" onclick="window.location.href='?tipo=mensal'">Mensal</button>
        <button class="btn <?php echo $tipo_plano === 'anual' ? 'active' : ''; ?>" onclick="window.location.href='?tipo=anual'">Anual <small><small id="desc_anual" class="ms-2">25% off*</small></small></button>
    </div>

    <div class="planos-container">
        <div class="plano-card plano-empresa">
            <h2 class="plano-titulo">Empresa</h2>
            <?php if ($tipo_plano === 'anual'): ?>
                <div class="desconto">25% off</div>
            <?php endif; ?>
            <p>Ideal para negócios que possuem funcionários ou parceiros.</p>
            <div class="preco">
                R$ <?php echo number_format($planos['empresa'][$tipo_plano]['preco'], 2, ',', '.'); ?> 
                <?php echo $tipo_plano === 'anual' ? '/ano' : '/mês'; ?>
            </div>
            <?php if ($tipo_plano === 'anual'): ?>
                <div class="economia">
                    Pague de uma só vez e economize R$ <?php echo number_format($planos['empresa']['anual']['economia'], 2, ',', '.'); ?>
                </div>
            <?php endif; ?>
            <div class="funcionalidades">
                <div class="funcionalidade"><i class="fas fa-check"></i> Cadastro ilimitado de colaboradores</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Calendário e Agendamento online</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Relatórios Financeiros</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Dashboards Financeiros e Gerenciais</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Controle de estoque</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Comissões</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Comandas e Controle de Consumo</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Gestão de clientes</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> WhatsApp integrado</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Pagamento On-line</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Envio de Notícias e Promoções</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Cartão Fidelidade</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Site do Estabelecimento</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Lista de Espera</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> e muito mais...</div>
            </div>
            <button class="btn-teste" data-bs-toggle="modal" data-bs-target="#modalEmpresa<?php echo $tipo_plano; ?>">Testar grátis por 7 dias</button>
        </div>

        <div class="plano-card plano-individual">
            <h2 class="plano-titulo">Individual</h2>
            <?php if ($tipo_plano === 'anual'): ?>
                <div class="desconto">12% off</div>
            <?php endif; ?>
            <p>Ideal para quem trabalha sozinho. Nesse plano não é possível cadastrar colaboradores ou parceiros.</p>
            <div class="preco">
                R$ <?php echo number_format($planos['individual'][$tipo_plano]['preco'], 2, ',', '.'); ?> 
                <?php echo $tipo_plano === 'anual' ? '/ano' : '/mês'; ?>
            </div>
            <?php if ($tipo_plano === 'anual'): ?>
                <div class="economia">
                    Pague de uma só vez e economize R$ <?php echo number_format($planos['individual']['anual']['economia'], 2, ',', '.'); ?>
                </div>
            <?php endif; ?>
            <div class="funcionalidades">
                <div class="funcionalidade"><i class="fas fa-check"></i> Calendário e Agendamento online</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Relatórios Financeiros</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Dashboards Financeiros e Gerenciais</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Controle de estoque</div>                
                <div class="funcionalidade"><i class="fas fa-check"></i> Comandas e Controle de Consumo</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Gestão de clientes</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> WhatsApp integrado</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Pagamento On-line</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Envio de Notícias e Promoções</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Cartão Fidelidade</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Site do Estabelecimento</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Lista de Espera</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> e muito mais...</div>
            </div>
            <button class="btn-teste" data-bs-toggle="modal" data-bs-target="#modalIndividual<?php echo $tipo_plano; ?>">Testar grátis por 7 dias</button>
        </div>
    </div><br><br><br><br>

    <!-- Modais -->
    <!-- Modal Empresa Mensal -->
    <div class="modal fade" id="modalEmpresamensal" tabindex="-1" aria-labelledby="modalEmpresamensalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEmpresamensalLabel">Inscreva-se no Plano Empresa Mensal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form id="formEmpresaMensal" onsubmit="return submitForm(event, '2', '30', this)">
                        <div class="form-group">
                            <label for="nomeEmpresaMensal">Nome</label>
                            <input type="text" class="form-control" id="nomeEmpresaMensal" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="usernameEmpresaMensal">Username</label>
                            <input type="text" class="form-control" id="usernameEmpresaMensal" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="telefoneEmpresaMensal">Telefone</label>
                            <input type="tel" class="form-control" id="telefoneEmpresaMensal" name="telefone" required>
                        </div>
                        <div class="form-group">
                            <label for="emailEmpresaMensal">Email</label>
                            <input type="email" class="form-control" id="emailEmpresaMensal" name="email" required>
                        </div>
                        <input type="hidden" name="plano" value="2">
                        <input type="hidden" name="frequencia" value="30">
                        <input type="hidden" name="valor" value="59.90">
                        <button type="submit" class="btn-concluir mt-3">Concluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Empresa Anual -->
    <div class="modal fade" id="modalEmpresaanual" tabindex="-1" aria-labelledby="modalEmpresaanualLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEmpresaanualLabel">Inscreva-se no Plano Empresa Anual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form id="formEmpresaAnual" onsubmit="return submitForm(event, '2', '365', this)">
                        <div class="form-group">
                            <label for="nomeEmpresaAnual">Nome</label>
                            <input type="text" class="form-control" id="nomeEmpresaAnual" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="usernameEmpresaAnual">Username</label>
                            <input type="text" class="form-control" id="usernameEmpresaAnual" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="telefoneEmpresaAnual">Telefone</label>
                            <input type="tel" class="form-control" id="telefoneEmpresaAnual" name="telefone" required>
                        </div>
                        <div class="form-group">
                            <label for="emailEmpresaAnual">Email</label>
                            <input type="email" class="form-control" id="emailEmpresaAnual" name="email" required>
                        </div>
                        <input type="hidden" name="plano" value="2">
                        <input type="hidden" name="frequencia" value="365">
                        <input type="hidden" name="valor" value="539.00">
                        <button type="submit" class="btn-concluir mt-3">Concluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Individual Mensal -->
    <div class="modal fade" id="modalIndividualmensal" tabindex="-1" aria-labelledby="modalIndividualmensalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalIndividualmensalLabel">Inscreva-se no Plano Individual Mensal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form id="formIndividualMensal" onsubmit="return submitForm(event, '1', '30', this)">
                        <div class="form-group">
                            <label for="nomeIndividualMensal">Nome</label>
                            <input type="text" class="form-control" id="nomeIndividualMensal" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="usernameIndividualMensal">Username</label>
                            <input type="text" class="form-control" id="usernameIndividualMensal" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="telefoneIndividualMensal">Telefone</label>
                            <input type="tel" class="form-control" id="telefoneIndividualMensal" name="telefone" required>
                        </div>
                        <div class="form-group">
                            <label for="emailIndividualMensal">Email</label>
                            <input type="email" class="form-control" id="emailIndividualMensal" name="email" required>
                        </div>
                        <input type="hidden" name="plano" value="1">
                        <input type="hidden" name="frequencia" value="30">
                        <input type="hidden" name="valor" value="29.90">
                        <button type="submit" class="btn-concluir mt-3">Concluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Individual Anual -->
    <div class="modal fade" id="modalIndividualanual" tabindex="-1" aria-labelledby="modalIndividualanualLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalIndividualanualLabel">Inscreva-se no Plano Individual Anual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form id="formIndividualAnual" onsubmit="return submitForm(event, '1', '365', this)">
                        <div class="form-group">
                            <label for="nomeIndividualAnual">Nome</label>
                            <input type="text" class="form-control" id="nomeIndividualAnual" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="usernameIndividualAnual">Username</label>
                            <input type="text" class="form-control" id="usernameIndividualAnual" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="telefoneIndividualAnual">Telefone</label>
                            <input type="tel" class="form-control" id="telefoneIndividualAnual" name="telefone" required>
                        </div>
                        <div class="form-group">
                            <label for="emailIndividualAnual">Email</label>
                            <input type="email" class="form-control" id="emailIndividualAnual" name="email" required>
                        </div>
                        <input type="hidden" name="plano" value="1">
                        <input type="hidden" name="frequencia" value="365">
                        <input type="hidden" name="valor" value="314.90">
                        <button type="submit" class="btn-concluir mt-3">Concluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once("rodape.php") ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        function submitForm(event, plano, frequencia, form) {
            event.preventDefault(); // Evitar o envio padrão do formulário

            const formData = new FormData(form);

            const username = formData.get('username');           

            $.ajax({
                url: "cadastramento.php",
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (mensagem) {                           
                                                        
                    Swal.fire({
                    title: "Cadastro efetuado!",                
                    html: "Segue os dados de acesso:📝<br><spam style = 'color:blue'>Login: <b>" + username +"</b></spam><br><spam style = 'color:blue'>Senha: <b>123</b><br><br><small style = 'color:black'><small>🚨 Altere sua senha assim que acessar, em configurações de perfil.</small></small><br><small style = 'color:black'><small>Um email e WhatsApp foram enviados com os dados de acesso (verifique a caixa de spam)</small></small>",				
                    icon: "success"
                    }).then((result) => {
                        if(result.isConfirmed){
                        window.location = "login.php";        
                        }});                                                        
                
                },
            });
        }
    </script>
</body>
</html>