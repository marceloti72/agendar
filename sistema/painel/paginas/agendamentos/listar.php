<?php
require_once("../../../conexao.php");
@session_start();
$id_conta = $_SESSION['id_conta'];
$usuario = @$_SESSION['id_usuario'];
$data_atual = date('Y-m-d');
?>

<style>
/* Estilização geral e fontes */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f2f5;
    color: #333;
}

/* Contêiner da lista de serviços */
.service-list-container {
    display: grid;
    gap: 20px;
    padding: 20px;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

/* Estilo do cartão de serviço */
.service-card {
    background-color: #c0bebeff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

/* Seção do cabeçalho */
.service-header {
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #f7f9fc;
    border-bottom: 1px solid #e1e4e8;
}

.service-header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.service-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ddd;
}

.service-time {
    font-size: 2em;
    font-weight: 600;
    color: #007bff;
}

/* Ações no cabeçalho */
.service-actions {
    position: relative;
}

.delete-button {
    background: none;
    border: none;
    font-size: 1.5em;
    color: #999;
    cursor: pointer;
    transition: color 0.2s ease;
}

.delete-button:hover {
    color: #dc3545;
}

/* Menu suspenso para confirmação de exclusão */
.delete-confirm-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 10;
    min-width: 200px;
    padding: 10px;
    display: none; /* Oculto por padrão */
}

.delete-confirm-dropdown p {
    margin: 0;
    font-size: 0.9em;
}

.delete-confirm-dropdown a {
    color: #dc3545;
    font-weight: 600;
    text-decoration: none;
    margin-left: 5px;
}

/* Seção do corpo do cartão */
.service-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    flex-grow: 1;
}

/* Informações de status e pagamento */
.status-info {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-completed {
    background-color: #d4edda;
    color: #155724;
}

.payment-badge {
    background-color: #e9ecef;
    color: #495057;
}

.payment-badge.paid {
    background-color: #d4edda;
    color: #155724;
}

/* Seção de detalhes */
.service-details {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
    border-top: 1px dashed #e1e4e8;
    padding-top: 15px;
}

.detail-item {
    font-size: 0.9em;
    color: #555;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Estilização para o ícone. Necessita de uma biblioteca como Font Awesome. */
.detail-icon {
    width: 18px;
    height: 18px;
    color: #007bff;
}

.client-name {
    font-weight: 600;
    color: #000;
}

.service-name {
    font-style: italic;
    color: #666;
}

.professional-name {
    font-weight: 500;
    color: #444;
}

.origin-badge {
    background-color: #e9f0ff;
    color: #004085;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75em;
    font-weight: 500;
    text-transform: capitalize;
}

/* Rodapé com botões de ação */
.service-footer {
    padding: 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid #e1e4e8;
}

.footer-btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.btn-finish {
    background-color: #28a745;
    color: #fff;
}

.btn-finish:hover {
    background-color: #218838;
}

/* Classes de utilidade */
.hidden {
    display: none;
}
</style>

<?php
$funcionario = @$_POST['funcionario'];
$data = @$_POST['data'];

if ($data == "") {
    $data = date('Y-m-d');
}

if ($funcionario == "") {
    $func = '';
} else {
    $func = "funcionario = '$funcionario' and";
}

echo <<<HTML
<div class="service-list-container">
HTML;

$query = $pdo->query("SELECT * FROM agendamentos WHERE $func data = '$data' AND id_conta = '$id_conta' ORDER BY hora asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);

if ($total_reg > 0) {
    for ($i = 0; $i < $total_reg; $i++) {
        $id = $res[$i]['id'];
        $funcionario = $res[$i]['funcionario'];
        $cliente = $res[$i]['cliente'];
        $hora = $res[$i]['hora'];
        $servico = $res[$i]['servico'];
        $valor_pago = $res[$i]['valor_pago'];
        $origem = $res[$i]['origem'];
        $status = $res[$i]['status'];
        $comanda = $res[$i]['comanda_id'];
        $valor_sinal = $res[$i]['valor_pago'];
        $cupom = $res[$i]['cupom'];
        $obs = str_replace('"', "**", $res[$i]['obs']);

        $horaF = date("H:i", strtotime($hora));

        $stmt = $pdo->prepare("SELECT * FROM comandas WHERE id = :comanda AND id_conta = :id_conta");
        $stmt->bindValue(':comanda', $comanda);
        $stmt->bindValue(':id_conta', $id_conta);
        $stmt->execute();

        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifique se a consulta retornou um item antes de tentar acessá-lo
        if ($item) {
            $id2 = $item['id'];
            $valor2 = $item['valor'];
            $cliente2 = $item['cliente'];
            $obs2 = $item['obs'];
            $status2 = $item['status'];
            $data2 = $item['data'];
            $hora2 = $item['hora'];
            $funcionario_id = $item['funcionario'];

            // O código para formatar a data está correto
            $dataF = implode('/', array_reverse(explode('-', $data2)));
                        
        } else {
            // Caso a comanda não seja encontrada
            echo "Comanda não encontrada.";
        }

        // Verifica se um cupom foi enviado
        if (!empty($cupom)) {

            // 1. Obter os dados do cupom (incluindo o tipo de desconto)
            $query_cupom = $pdo->prepare("SELECT valor, tipo_desconto, usos_atuais FROM cupons WHERE id = :id AND id_conta = :id_conta");
            $query_cupom->bindValue(":id", $cupom);
            $query_cupom->bindValue(":id_conta", $id_conta, PDO::PARAM_INT);
            $query_cupom->execute();
            $dados_cupom = $query_cupom->fetch(PDO::FETCH_ASSOC);

            // 2. Verificar se o cupom existe e é válido (a validação de data e usos já foi feita no SELECT anterior, mas podemos reforçar)
            if ($dados_cupom) {
                $valor_cupom = $dados_cupom['valor'];
                $tipo_desconto = $dados_cupom['tipo_desconto'];
                
                if ($tipo_desconto === 'porcentagem') {
                    // Calcula o valor do desconto em reais (ou na sua moeda)
                    $desconto_aplicado = $valor2 * ($valor_cupom / 100); 
                    
                    // Arredonda o valor do desconto para duas casas decimais
                    $valor_cupom = $desconto_aplicado;
                }                
            }
        }        

        // Obter informações do cliente
        $query_client = $pdo->query("SELECT nome, cartoes FROM clientes WHERE id = '$cliente' AND id_conta = '$id_conta'");
        $res_client = $query_client->fetch(PDO::FETCH_ASSOC);
        $nome_cliente = $res_client ? $res_client['nome'] : 'Sem Cliente';
        $total_cartoes = $res_client ? $res_client['cartoes'] : 0;

        // Obter informações do serviço
        $query_service = $pdo->query("SELECT nome, valor FROM servicos WHERE id = '$servico' AND id_conta = '$id_conta'");
        $res_service = $query_service->fetch(PDO::FETCH_ASSOC);
        $nome_serv = $res_service ? $res_service['nome'] : 'Não Lançado';
        $valor_serv = $res_service ? $res_service['valor'] : 0;

        // Obter informações do profissional
        $query_prof = $pdo->query("SELECT nome FROM usuarios WHERE id = '$funcionario' AND id_conta = '$id_conta'");
        $res_prof = $query_prof->fetch(PDO::FETCH_ASSOC);
        $nome_prof = $res_prof ? $res_prof['nome'] : '';

        // Calcular status do pagamento
        $valor_pagoF = number_format($valor_pago, 2, ',', '.');
        $payment_status_text = ($valor_pago == $valor_serv) ? 'Pagamento Concluído' : 'Sinal Pago: R$ ' . $valor_pagoF;
        $payment_badge_class = ($valor_pago > 0) ? 'payment-badge' : 'hidden';
        if ($valor_pago == $valor_serv) {
             $payment_badge_class .= ' paid';
        }

        // Definir classes e texto relacionados ao status
        $status_badge_class = ($status == 'Concluído') ? 'status-completed' : 'status-pending';
        $status_text = ($status == 'Concluído') ? 'Concluído' : 'Em Aberto';
        
        $hide_finish_button = ($status == 'Concluído') ? 'hidden' : '';
        
        // Débitos do cliente (lógica não exibida no card por padrão, mas pode ser adicionada ao dropdown de histórico)
        $total_debitos = 0;
        $total_pagar = 0;
        $total_vencido = 0;
        $query_debitos = $pdo->query("SELECT valor, data_venc FROM receber WHERE pessoa = '$cliente' AND pago != 'Sim' AND id_conta = '$id_conta'");
        $res_debitos = $query_debitos->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res_debitos as $debito) {
            $total_debitos += $debito['valor'];
            if (strtotime($debito['data_venc']) < strtotime($data_atual)) {
                $total_vencido += $debito['valor'];
            } else {
                $total_pagar += $debito['valor'];
            }
        }
        $total_debitosF = number_format($total_debitos, 2, ',', '.');
        $total_vencidoF = number_format($total_vencido, 2, ',', '.');
        $total_pagarF = number_format($total_pagar, 2, ',', '.');

        echo <<<HTML
        <div class="service-card">
            <div class="service-header">
                <div class="service-header-left">
                    <img class="service-icon" src="img/relogio-vermelho.png" alt="Ícone de Relógio">
                    <span class="service-time">{$horaF}</span>
                </div>
                <div class="service-actions">
                    <button class="delete-button" onclick="showDeleteDropdown(this)">
                        &times;
                    </button>
                    <div class="delete-confirm-dropdown">
                        <p>Confirmar exclusão? <a href="#" onclick="excluir('{$id}', '{$horaF}')">Sim</a></p>
                    </div>
                </div>
            </div>

            <div class="service-body">
                <div class="status-info">
                    <span class="status-badge {$status_badge_class}">{$status_text}</span>
                    <span class="payment-badge {$payment_badge_class}">{$payment_status_text}</span>
                </div>
                
                <div class="service-details">
                    <div class="detail-item">
                        <i class="fas fa-user detail-icon"></i>
                        <span class="client-name">{$nome_cliente}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-cut detail-icon"></i>
                        <span class="service-name">{$nome_serv}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-briefcase detail-icon"></i>
                        <span class="professional-name">Profissional: {$nome_prof}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-credit-card detail-icon"></i>
                        <span class="origin-badge">via {$origem}</span>
                    </div>
                </div>
            </div>

            <div class="service-footer">
                <a href="#" onclick="editar('{$id2}', '{$valor2}', '{$cliente2}', '{$obs2}', '{$status2}', '{$nome_cliente}', '{$nome_prof}', '{$dataF}', '{$valor_sinal}', '{$valor_cupom}')" title="Abrir Comanda">
                        <i class="fa fa-eye"></i> Comanda
                    </a>
            </div>
        </div>
HTML;
    }
} else {
    echo '<p>Nenhum horário para essa data!</p>';
}

echo <<<HTML
</div>
HTML;
?>

<script>
    function showDeleteDropdown(button) {
        const dropdown = button.nextElementSibling;
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    // Fecha os dropdowns ao clicar fora deles
    document.addEventListener('click', function(e) {
        const dropdowns = document.querySelectorAll('.delete-confirm-dropdown');
        dropdowns.forEach(dropdown => {
            const parentButton = dropdown.previousElementSibling;
            if (!parentButton.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    });

    // Seus scripts existentes fecharServico e verificarStatus...
    // (Apenas adicionei um alerta para o verificarStatus, mas a função original é a mesma)
    function fecharServico(id, cliente, servico, valor_servico, funcionario, nome_serv) {
        $('#id_agd').val(id);
        $('#cliente_agd').val(cliente);
        $('#servico_agd').val(servico);
        $('#valor_serv_agd').val(valor_servico);
        $('#funcionario_agd').val(funcionario).change();
        $('#titulo_servico').text(nome_serv);
        $('#descricao_serv_agd').val(nome_serv);
        $('#obs2').val('');

        $('#valor_serv_agd_restante').val('');
        $('#data_pgto_restante').val('');
        $('#pgto_restante').val('').change();

        $('#modalServico').modal('show');
    }

    function verificarStatus(id, cliente, servico, valor_serv, funcionario, nome_serv, status) {
        if (status !== 'Concluído') {
            fecharServico(id, cliente, servico, valor_serv, funcionario, nome_serv);
        } else {
            alert('Este serviço já foi concluído.');
        }
    }
</script>

<script type="text/javascript">
    function confirmarExclusao(id) {
        if (confirm("Confirma Exclusão?")) {
            excluirComanda(id);
        }
    }
    function editar(id, valor, cliente, obs, status, nome_cliente, nome_func, data, valor_sinal, valor_cupom) {
        if (status.trim() === 'Fechada') {
            $('#cliente_dados').text(nome_cliente);
            $('#valor_dados').text(valor);
            $('#data_dados').text(data);
            $('#func_dados').text(nome_func);
            $('#modalDados').modal('show');

            listarServicosDados(id);
            listarProdutosDados(id);
        } else {
            $('#id').val(id);
            $('#cliente').val(cliente).change();
            $('#valor_serv').val(valor);
            // Converte os valores para números antes de somar
            var sinal = parseFloat(valor_sinal) || 0;
            var cupom = parseFloat(valor_cupom) || 0;
            var total_descontos = sinal + cupom;

            // Preenche os inputs com os valores e a soma
            $('#valor_sinal').val(sinal.toFixed(2));
            $('#valor_cupom').val(cupom.toFixed(2));
            $('#valor_descontos').val(total_descontos.toFixed(2));         

            
            $('#obs').val(obs);

            $('#valor_serv_agd_restante').val('');

            $('#titulo_comanda').text('Editar Comanda Aberta');
            $('#btn_fechar_comanda').show();
            $('#modalForm2').modal('show');
            $('#nome_do_cliente_aqui').text('Cliente: '+nome_cliente);

            listarServicos2(id);
            listarProdutos(id);
            calcular();
        }
    }

    function limparCampos() {
        $('#btn_fechar_comanda').hide();
        $('#titulo_comanda').text('Nova Comanda');
        $('#id').val('');
        $('#valor_serv').val('');
        $('#cliente').val('').change();
        $('#data_pgto').val('<?= $data_hoje ?>');
        $('#valor_serv_agd_restante').val('');
        $('#data_pgto_restante').val('');
        $('#pgto_restante').val('').change();

        listarServicos2();
        listarProdutos();
        calcular();
    }

    function excluirComanda(id) {
        $.ajax({
            url: 'paginas/comanda/excluir.php',
            method: 'POST',
            data: { id },
            dataType: "text",
            success: function(mensagem) {
                if (mensagem.trim() === "Excluído com Sucesso") {
                    listar();
                } else {
                    alert(mensagem);
                }
            },
            error: function(xhr, status, error) {
                alert("Erro ao excluir: " + error);
            }
        });
    }
</script>