<?php
require_once("cabecalho.php");
// Simulação de lógica para determinar preços (você pode ajustar conforme sua lógica de negócios)
$planos = [
    'empresa' => [
        'mensal' => ['preco' => 79.90, 'desconto_anual' => 18],
        'anual' => ['preco' => 786.21, 'economia' => 172.58]
    ],
    'individual' => [
        'mensal' => ['preco' => 39.90, 'desconto_anual' => 12],
        'anual' => ['preco' => 420.00, 'economia' => 58.80]
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
    <title>Planos MARKAI</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
    :root {
        --primary-color: #5d54a4;
        --secondary-color: #7c72c2;
        --light-bg: #f5f6fa;
        --dark-text: #2c3e50;
        --light-text: white;
        --border-color: #e0e0e0;
        --success-color: #27ae60;
        --gray-color: #bdc3c7;
        --icon-color: #5d54a4;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--light-bg);
        padding: 0;
        margin: 0;
    }

    .header-container {
        text-align: center;
        padding: 50px 20px;
        background-color: var(--light-bg);
    }

    .header-container h1 {
        font-weight: 700;
        color: var(--dark-text);
        margin-bottom: 10px;
    }

    .header-container h3 {
        font-weight: 400;
        color: var(--gray-color);
        max-width: 600px;
        margin: 0 auto;
    }

    .planos-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: center;
        gap: 20px;
        padding: 20px;
        flex-wrap: wrap;
    }

    .plano-card {
        flex: 1;
        min-width: 300px;
        max-width: 500px;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
        position: relative;
    }

    .plano-card:hover,
    .plano-card:focus-within {
        transform: translateY(-8px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
    }

    .plano-empresa {
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        color: var(--light-text);
    }

    .plano-individual {
        background: var(--light-text);
        color: var(--dark-text);
        border: 1px solid var(--border-color);
    }
    
    .plano-empresa .funcionalidade i, .plano-empresa .plano-titulo {
        color: var(--light-text) !important;
    }

    .plano-titulo {
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .preco {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .preco span {
        font-size: 1rem;
        font-weight: 400;
        color: var(--gray-color);
    }
    
    .plano-empresa .preco span {
        color: rgba(255, 255, 255, 0.7);
    }

    .economia {
        font-size: 0.9rem;
        color: var(--success-color);
        margin-bottom: 20px;
        font-weight: 500;
    }

    .funcionalidades {
        list-style: none;
        padding: 0;
        text-align: left;
    }

    .funcionalidade {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }

    .funcionalidade i {
        color: var(--icon-color);
        margin-right: 15px;
        font-size: 1.1rem;
    }
    
    .plano-empresa .funcionalidade i {
        color: var(--light-text);
    }

    .btn-teste {
        display: block;
        width: 100%;
        padding: 15px;
        background: var(--primary-color);
        border: none;
        border-radius: 10px;
        color: var(--light-text);
        text-align: center;
        text-decoration: none;
        font-weight: bold;
        margin-top: 30px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }
    
    .plano-individual .btn-teste {
        background: var(--primary-color);
        color: var(--light-text);
    }

    .btn-teste:hover,
    .btn-teste:focus {
        background: var(--secondary-color);
        transform: translateY(-2px);
    }

    .toggle-plano {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
        margin-top: 20px;
        background-color: var(--light-text);
        border-radius: 30px;
        padding: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        width: fit-content;
        margin-left: auto;
        margin-right: auto;
    }

    .toggle-plano .btn {
        border-radius: 20px;
        padding: 10px 25px;
        font-weight: 600;
        background: transparent;
        color: var(--dark-text);
        border: none;
        margin: 0 5px;
        min-width: 120px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .toggle-plano .btn.active {
        background: var(--primary-color);
        color: var(--light-text);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .desconto {
        background: var(--success-color);
        color: var(--light-text);
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
    }

    .modal-content {
        border-radius: 15px;
        border: none;
    }

    .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }

    .modal-title {
        font-weight: 600;
        color: var(--dark-text);
    }
    
    .modal-body {
        padding: 20px;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid var(--border-color);
        padding: 12px;
    }

    .btn-concluir {
        background: var(--primary-color);
        color: var(--light-text);
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: bold;
        width: 100%;
        transition: background-color 0.3s ease;
    }

    .btn-concluir:hover,
    .btn-concluir:focus {
        background: var(--secondary-color);
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .planos-container {
            flex-direction: column;
            align-items: center;
        }

        .plano-card {
            width: 100%;
            max-width: 400px;
            margin-bottom: 20px;
        }
        
        .header-container {
            padding: 30px 15px;
        }

        .header-container h1 {
            font-size: 1.8rem;
        }

        .header-container h3 {
            font-size: 1rem;
        }
        
        .toggle-plano {
            flex-direction: column;
            align-items: center;
            width: 90%;
        }

        .toggle-plano .btn {
            width: 100%;
            margin: 5px 0;
        }
    }
</style>
</head>
<body>
<div class="header-container">
    <h1>Planos e Preços ✨</h1>
    <h3>Escolha o plano da MARKAI que melhor se encaixa na sua gestão de negócio.</h3>
</div>

    <div class="toggle-plano">
        <button class="btn <?php echo $tipo_plano === 'mensal' ? 'active' : ''; ?>" onclick="window.location.href='?tipo=mensal'">Mensal</button>
        <button class="btn <?php echo $tipo_plano === 'anual' ? 'active' : ''; ?>" onclick="window.location.href='?tipo=anual'">Anual <small><small id="desc_anual" class="ms-2">18% off*</small></small></button>
    </div>

    <div class="planos-container">
        <div class="plano-card plano-empresa">
            <?php if ($tipo_plano === 'anual'): ?>
                <div class="desconto">18% OFF</div>
            <?php endif; ?>
            <h2 class="plano-titulo">Empresa</h2>
            <p>Ideal para negócios que possuem funcionários ou parceiros.</p>
            <div class="preco">
                R$ <?php echo number_format($planos['empresa'][$tipo_plano]['preco'], 2, ',', '.'); ?> 
                <span><?php echo $tipo_plano === 'anual' ? '/ano' : '/mês'; ?></span>
            </div>
            <?php if ($tipo_plano === 'anual'): ?>
                <div class="economia">
                    Pague de uma só vez e economize R$ <?php echo number_format($planos['empresa']['anual']['economia'], 2, ',', '.'); ?>
                </div>
            <?php endif; ?>
            <ul class="funcionalidades">
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Cadastro ilimitado de usuários</div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Gestão de profissionais</div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Agendamento online 24h, com fila de espera(Encaixes)</div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Vc poderá baixar o APP ou usar pela Web o MarkAi</div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Link ou APP personalizado para clientes, agendamentos, compra de produtos, venda de assinaturas e outras opções.</div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Comandas e controle de consumo</div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Campanhas de retorno de clientes, disparos em massa com opção de cupom de desconto</div>                
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Gráficos e métricas -> total de clientes, agendamentos de hoje, distribuição de receitas, saldo do dia, mês e ano, agendamentos por dia da semana, serviços por profissionais, serviços mais realizados, aniversariantes de hoje, clientes aguardando encaixe</div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Venda de produtos</div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Controle total de estoque</div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i>WhatsApp integrado <img src="images/whatsapp.png" alt="Ícone do WhatsApp" style="width: 20px; height: 20px;margin-left: 10px;"> </div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Mercado Pago integrado <img src="images/mercado-pago.png" alt="Ícone do Mecado Pago" style="background-color: white;width: 20px; height: 20px;margin-left: 10px;"> <small style="font-size: 12px;margin-left: 10px;"> (diversas formas de pagamentos e baixas automáticas)</small></div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Notificações automáticas de agendamentos, cancelamentos, lembretes, retornos etc...</div>                
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Cartão Fidelidade - Configure e premie seus clientes pela recorrência</div>
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> Diversos relatórios Financeiros</div>                     
                <div class="funcionalidade"><i class="fas fa-check-circle"></i></i> e muito mais...</div>
            </ul>
            <button class="btn-teste" data-bs-toggle="modal" data-bs-target="#modalEmpresa<?php echo $tipo_plano; ?>">Testar grátis por 7 dias</button>
        </div>

        <div class="plano-card plano-individual">
            <?php if ($tipo_plano === 'anual'): ?>
                <div class="desconto">12% OFF</div>
            <?php endif; ?>
            <h2 class="plano-titulo">Individual</h2>
            <p>Todas as funcionalidades, menos gestão de profissionais.</p>
            <div class="preco">
                R$ <?php echo number_format($planos['individual'][$tipo_plano]['preco'], 2, ',', '.'); ?> 
                <span><?php echo $tipo_plano === 'anual' ? '/ano' : '/mês'; ?></span>
            </div>
            <?php if ($tipo_plano === 'anual'): ?>
                <div class="economia">
                    Pague de uma só vez e economize R$ <?php echo number_format($planos['individual']['anual']['economia'], 2, ',', '.'); ?>
                </div>
            <?php endif; ?>
            <ul class="funcionalidades">
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> Agendamento online 24h, com fila de espera...</li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> Vc poderá baixar o APP ou usar pela Web o MarkAi</li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> Link ou APP personalizado para clientes...</li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> Comandas e controle de consumo</li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> Campanhas de retorno de clientes...</li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> Gráficos e métricas completas</li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> Venda de produtos e controle de estoque</li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i>WhatsApp integrado <img src="images/whatsapp.png" alt="Ícone do WhatsApp" style="width: 20px; height: 20px; margin-left: 10px;"></li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> Mercado Pago integrado <img src="images/mercado-pago.png" alt="Ícone do Mecado Pago" style="width: 20px; height: 20px; margin-left: 10px;"></li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> Notificações automáticas de agendamentos...</li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> Cartão Fidelidade - Configure e premie...</li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> Diversos relatórios Financeiros</li>
                <li class="funcionalidade"><i class="fas fa-check-circle"></i> e muito mais...</li>
            </ul>
            <button class="btn-teste" data-bs-toggle="modal" data-bs-target="#modalIndividual<?php echo $tipo_plano; ?>">Testar grátis por 7 dias</button>
        </div>
    </div><br><br><br><br>

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
                            <label for="telefoneEmpresaMensal">Telefone</label>
                            <input type="tel" class="form-control" id="telefoneEmpresaMensal" name="telefone" required>
                        </div>
                        <div class="form-group">
                            <label for="emailEmpresaMensal">Email</label>
                            <input type="email" class="form-control" id="emailEmpresaMensal" name="email" required>
                        </div>
                        <small><div id="mensagem-ativar1" align="center"></div></small>
                        <input type="hidden" name="plano" value="2">
                        <input type="hidden" name="frequencia" value="30">
                        <input type="hidden" name="valor" value="79.90">
                        <button type="submit" class="btn-concluir mt-3">Concluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                            <label for="telefoneEmpresaAnual">Telefone</label>
                            <input type="tel" class="form-control" id="telefoneEmpresaAnual" name="telefone" required>
                        </div>
                        <div class="form-group">
                            <label for="emailEmpresaAnual">Email</label>
                            <input type="email" class="form-control" id="emailEmpresaAnual" name="email" required>
                        </div>
                        <small><div id="mensagem-ativar2" align="center"></div></small>
                        <input type="hidden" name="plano" value="2">
                        <input type="hidden" name="frequencia" value="365">
                        <input type="hidden" name="valor" value="786.21">
                        <button type="submit" class="btn-concluir mt-3">Concluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                            <label for="telefoneIndividualMensal">Telefone</label>
                            <input type="tel" class="form-control" id="telefoneIndividualMensal" name="telefone" required>
                        </div>
                        <div class="form-group">
                            <label for="emailIndividualMensal">Email</label>
                            <input type="email" class="form-control" id="emailIndividualMensal" name="email" required>
                        </div>
                        <small><div id="mensagem-ativar3" align="center"></div></small>
                        <input type="hidden" name="plano" value="1">
                        <input type="hidden" name="frequencia" value="30">
                        <input type="hidden" name="valor" value="49.90">
                        <button type="submit" class="btn-concluir mt-3">Concluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                            <label for="telefoneIndividualAnual">Telefone</label>
                            <input type="tel" class="form-control" id="telefoneIndividualAnual" name="telefone" required>
                        </div>
                        <div class="form-group">
                            <label for="emailIndividualAnual">Email</label>
                            <input type="email" class="form-control" id="emailIndividualAnual" name="email" required>
                        </div>
                        <small><div id="mensagem-ativar4" align="center"></div></small>
                        <input type="hidden" name="plano" value="1">
                        <input type="hidden" name="frequencia" value="365">
                        <input type="hidden" name="valor" value="526.94">
                        <button type="submit" class="btn-concluir mt-3">Concluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once("rodape.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function submitForm(event, plano, frequencia, form) {
            event.preventDefault(); // Evitar o envio padrão do formulário

            const formData = new FormData(form);

            const username = formData.get('email'); 
            
            let n;
            if (plano == 2 && frequencia == 30) {
                n = '1';
            } else if (plano == 2 && frequencia == 365) {
                n = '2';
            } else if (plano == 1 && frequencia == 30) {
                n = '3';
            } else if (plano == 1 && frequencia == 365) {
                n = '4';
            }
            
            $.ajax({
                url: "cadastramento.php",
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (mensagem) {
                    if (mensagem.trim() == "Salvo com Sucesso") {
                        Swal.fire({
                            title: "Cadastro efetuado!",
                            html: "Segue os dados de acesso:📝<br><span style='color:blue'>Login: <b>" + username + "</b></span><br><span style='color:blue'>Senha: <b>123</b></span><br><br><small style='color:black'><small>🚨 Altere sua senha assim que acessar, em configurações de perfil.</small></small><br><small style='color:black'><small>Um email e WhatsApp foram enviados com os dados de acesso (verifique a caixa de spam)</small></small>",
                            icon: "success"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location = "login.php";
                            }
                        });
                    } else {
                        $('#mensagem-ativar' + n).addClass('text-danger');
                        $('#mensagem-ativar' + n).text(mensagem);
                    }
                },
            });
        }
    </script>
</body>
</html>