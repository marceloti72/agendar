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
    
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            padding: 0px 0;
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
            <a href="#" class="btn-teste">Testar grátis por 7 dias</a>
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
            <a href="#" class="btn-teste">Testar grátis por 7 dias</a>
        </div>
    </div><br><br><br><br>


    <?php require_once("rodape.php") ?>

    
</body>
</html>