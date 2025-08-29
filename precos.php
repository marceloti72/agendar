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
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
    :root {
        --primary-color: #4a148c;
        --secondary-color: #7b1fa2;
        --light-bg: #f8f9fa;
        --dark-text: #333;
        --light-text: white;
        --border-color: #dee2e6;
        --success-color: #28a745;
        --gray-color: #6c757d;
        --icon-color: #e1bee7;
    }

    body {
        font-family: 'Arial', sans-serif;
        background-color: var(--light-bg);
        padding: 0;
        margin: 0;
    }

    .planos-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: center;
        gap: 15px; /* Reduced gap for smaller screens */
        padding: 15px;
        flex-wrap: nowrap; /* Prevent wrapping to keep cards side by side */
    }

    .plano-card {
        flex: 1;
        min-width: 0; /* Allow cards to shrink below their content width */
        padding: 15px; /* Slightly reduced padding */
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out;
    }

    .plano-card:hover,
    .plano-card:focus-within {
        transform: translateY(-5px);
    }

    .plano-empresa {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: var(--light-text);
    }

    .plano-individual {
        background: var(--light-text);
        color: var(--dark-text);
        border: 1px solid var(--border-color);
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
        color: var(--success-color);
        margin-bottom: 20px;
    }

    .funcionalidade {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }

    .funcionalidade i {
        color: var(--icon-color);
        margin-right: 10px;
    }

    .btn-teste {
        display: block;
        width: 100%;
        padding: 10px;
        background: var(--light-text);
        border: 1px solid var(--border-color);
        border-radius: 5px;
        color: var(--primary-color);
        text-align: center;
        text-decoration: none;
        font-weight: bold;
        margin-top: 20px;
        cursor: pointer;
        transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
    }

    .btn-teste:hover,
    .btn-teste:focus {
        background: var(--light-bg);
        color: var(--secondary-color);
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
        background: var(--gray-color);
        color: var(--light-text);
        border: none;
        margin: 5px;
        min-width: 100px;
        transition: background-color 0.3s ease-in-out;
    }

    .toggle-plano .btn.active,
    .toggle-plano .btn:focus {
        background: var(--primary-color);
        color: var(--light-text);
    }

    .desconto {
        background: var(--success-color);
        color: var(--light-text);
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.8rem;
        margin-bottom: 10px;
    }

    #desc_anual {
        background-color: var(--light-text);
        color: var(--dark-text);
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
        background: var(--primary-color);
        color: var(--light-text);
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: bold;
        width: 100%;
        transition: background-color 0.3s ease-in-out;
    }

    .btn-concluir:hover,
    .btn-concluir:focus {
        background: var(--secondary-color);
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .planos-container {
            gap: 10px; /* Further reduce gap */
            padding: 10px;
        }

        .plano-card {
            padding: 12px; /* Reduce padding */
        }

        .plano-titulo {
            font-size: 1.3rem;
        }

        .preco {
            font-size: 1.8rem;
        }

        .funcionalidade {
            font-size: 0.85rem;
        }

        .toggle-plano {
            flex-direction: column; /* Stack toggle buttons vertically */
            align-items: center;
            gap: 10px;
        }

        .toggle-plano .btn {
            width: 80%;
            padding: 12px;
        }

        .btn-teste {
            padding: 12px;
        }

        .modal-dialog {
            margin: 10px;
        }

        h1 {
            font-size: 1.5rem;
        }

        h3 {
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .planos-container {
            gap: 8px; /* Even smaller gap */
        }

        .plano-titulo {
            font-size: 1.2rem;
        }

        .preco {
            font-size: 1.5rem;
        }

        .funcionalidade {
            font-size: 0.8rem;
        }

        .toggle-plano .btn {
            width: 90%;
        }

        .plano-card {
            padding: 10px; /* Further reduce padding */
        }
    }
</style>
</head>
<body>
<div style="display: flex; align-items: center; justify-content: center; height: 200px; ">
  <div style="text-align: center;">
    <h1>Planos e Preços</h1>
    <h3>Escolha o plano da MARKAI que melhor encaixe na sua gestão de negócio.</h3>
  </div>
</div>
    <div class="toggle-plano">
        <button class="btn <?php echo $tipo_plano === 'mensal' ? 'active' : ''; ?>" onclick="window.location.href='?tipo=mensal'">Mensal</button>
        <button class="btn <?php echo $tipo_plano === 'anual' ? 'active' : ''; ?>" onclick="window.location.href='?tipo=anual'">Anual <small><small id="desc_anual" class="ms-2">18% off*</small></small></button>
    </div>

    <div class="planos-container">
        <div class="plano-card plano-empresa">
            <h2 class="plano-titulo">Empresa</h2>
            <?php if ($tipo_plano === 'anual'): ?>
                <div class="desconto">18% off</div>
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
                <div class="funcionalidade"><i class="fas fa-check"></i> Cadastro ilimitado de usuários</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Gestão de profissionais</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Agendamento online 24h, com fila de espera(Encaixes)</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Vc poderá baixar o APP ou usar pela Web o MarkAi</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Link ou APP personalizado para clientes, agendamentos, compra de produtos, venda de assinaturas e outras opções.</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Comandas e controle de consumo</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Campanhas de retorno de clientes, disparos em massa com opção de cupom de desconto</div>                
                <div class="funcionalidade"><i class="fas fa-check"></i> Gráficos e métricas -> total de clientes, agendamentos de hoje, distribuição de receitas, saldo do dia, mês e ano, agendamentos por dia da semana, serviços por profissionais, serviços mais realizados, aniversariantes de hoje, clientes aguardando encaixe</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Venda de produtos</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Controle total de estoque</div>
                <div class="funcionalidade"><i class="fas fa-check"></i>WhatsApp integrado <img src="images/whatsapp.png" alt="Ícone do WhatsApp" style="width: 20px; height: 20px;margin-left: 10px;"> </div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Mercado Pago integrado <img src="images/mercado-pago.png" alt="Ícone do Mecado Pago" style="background-color: white;width: 20px; height: 20px;margin-left: 10px;"> <small style="font-size: 12px;margin-left: 10px;"> (diversas formas de pagamentos e baixas automáticas)</small></div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Notificações automáticas de agendamentos, cancelamentos, lembretes, retornos etc...</div>                
                <div class="funcionalidade"><i class="fas fa-check"></i> Cartão Fidelidade - Configure e premie seus clientes pela recorrência</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Diversos relatórios Financeiros</div>                     
                <div class="funcionalidade"><i class="fas fa-check"></i> e muito mais...</div>
            </div>
            <button class="btn-teste" data-bs-toggle="modal" data-bs-target="#modalEmpresa<?php echo $tipo_plano; ?>">Testar grátis por 7 dias</button>
        </div>

        <div class="plano-card plano-individual">
            <h2 class="plano-titulo">Individual</h2>
            <?php if ($tipo_plano === 'anual'): ?>
                <div class="desconto">12% off</div>
            <?php endif; ?>
            <p>Todas as funcionalidades, menos gestão de profissionais.</p>
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
                <div class="funcionalidade"><i class="fas fa-check"></i> Agendamento online 24h, com fila de espera(Encaixes)</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Vc poderá baixar o APP ou usar pela Web o MarkAi</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Link ou APP personalizado para clientes, agendamentos, compra de produtos, venda de assinaturas e outras opções.</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Comandas e controle de consumo</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Campanhas de retorno de clientes, disparos em massa com opção de cupom de desconto</div>                
                <div class="funcionalidade"><i class="fas fa-check"></i> Gráficos e métricas -> total de clientes, agendamentos de hoje, distribuição de receitas, saldo do dia, mês e ano, agendamentos por dia da semana, serviços por profissionais, serviços mais realizados, aniversariantes de hoje, clientes aguardando encaixe</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Venda de produtos</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Controle total de estoque</div>
                <div class="funcionalidade"><i class="fas fa-check"></i>WhatsApp integrado <img src="images/whatsapp.png" alt="Ícone do WhatsApp" style="width: 20px; height: 20px;margin-left: 10px;"> </div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Mercado Pago integrado <img src="images/mercado-pago.png" alt="Ícone do Mecado Pago" style="background-color: white;width: 20px; height: 20px;margin-left: 10px;"> <small style="font-size: 12px;margin-left: 10px;"> (diversas formas de pagamentos e baixas automáticas)</small></div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Notificações automáticas de agendamentos, cancelamentos, lembretes, retornos etc...</div>                
                <div class="funcionalidade"><i class="fas fa-check"></i> Cartão Fidelidade - Configure e premie seus clientes pela recorrência</div>
                <div class="funcionalidade"><i class="fas fa-check"></i> Diversos relatórios Financeiros</div>                     
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        function submitForm(event, plano, frequencia, form) {
            event.preventDefault(); // Evitar o envio padrão do formulário

            const formData = new FormData(form);

            const username = formData.get('email');    
            
            if(plano == 2 && frequencia == 30){
                n='1';
            }
            if(plano == 2 && frequencia == 365){
                n='2';
            }
            if(plano == 1 && frequencia == 30){
                n='3';
            }
            if(plano == 1 && frequencia == 365){
                n='4';
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
                            html: "Segue os dados de acesso:📝<br><spam style = 'color:blue'>Login: <b>" + username +"</b></spam><br><spam style = 'color:blue'>Senha: <b>123</b><br><br><small style = 'color:black'><small>🚨 Altere sua senha assim que acessar, em configurações de perfil.</small></small><br><small style = 'color:black'><small>Um email e WhatsApp foram enviados com os dados de acesso (verifique a caixa de spam)</small></small>",				
                            icon: "success"
                            }).then((result) => {
                                if(result.isConfirmed){
                                window.location = "login.php";        
                        }});          

                    } else {

                        $('#mensagem-ativar'+n).addClass('text-danger')
                        $('#mensagem-ativar'+n).text(mensagem)
                    }                                                           
                
                },
            });
        }
    </script>
</body>
</html>