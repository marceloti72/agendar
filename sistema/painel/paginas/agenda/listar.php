<?php
require_once("../../../conexao.php");
@session_start();
$id_conta = $_SESSION['id_conta'];
$usuario = @$_SESSION['id_usuario'];
$data_atual = date('Y-m-d');
?>

<style>
/* General styling and fonts */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #e3e2e0;
    color: #333;
    margin: 0;
    /* padding: 20px; */
}

/* Container for the appointment list */
.appointment-list-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Individual appointment row */
.appointment-row {
    display: grid;
    grid-template-columns: 80px 1fr 1fr 1fr 120px 100px;
    align-items: center;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 12px 20px;
    transition: opacity 0.3s ease;
}

/* Transparency for completed status */
.appointment-row.completed {
    opacity: 0.5;
}

/* Professional color coding */
.professional-color-1 { border-left: 4px solid #007bff; }
.professional-color-2 { border-left: 4px solid #28a745; }
.professional-color-3 { border-left: 4px solid #dc3545; }
.professional-color-4 { border-left: 4px solid #ffc107; }
.professional-color-5 { border-left: 4px solid #17a2b8; }
.professional-color-6 { border-left: 4px solid #6f42c1; }

/* Appointment details */
.appointment-time {
    font-weight: 600;
    font-size: 1.1em;
    color: #333;
}

.appointment-client, .appointment-service, .appointment-professional {
    font-size: 1em;
    color: #444;
}

.appointment-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: 600;
    text-align: center;
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

/* Action buttons */
.appointment-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.action-btn {
    background: none;
    border: none;
    font-size: 1.2em;
    cursor: pointer;
    transition: color 0.2s ease;
}

.command-btn {
    color: #007bff;
}

.command-btn:hover {
    color: #0056b3;
}

.delete-btn {
    color: #999;
}

.delete-btn:hover {
    color: #dc3545;
}

/* Delete confirmation dropdown */
.delete-confirm-dropdown {
    position: absolute;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 10;
    min-width: 150px;
    padding: 10px;
    display: none;
    right: 0;
    margin-top: 5px;
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .appointment-row {
        grid-template-columns: 80px 1fr 1fr 100px;
        gap: 10px;
    }
    .appointment-service {
        display: none;
    }
}
</style>

<?php
$funcionario = $_SESSION['id_usuario'];
$data = @$_POST['data'];

if ($data == "") {
    $data = date('Y-m-d');
}

echo <<<HTML
<div class="appointment-list-container">
HTML;

// Array to assign colors to professionals
$professional_colors = [
    'professional-color-1',
    'professional-color-2',
    'professional-color-3',
    'professional-color-4',
    'professional-color-5',
    'professional-color-6'
];
$professional_index = [];

$query = $pdo->query("SELECT * FROM agendamentos WHERE funcionario = '$funcionario' and data = '$data' AND id_conta = '$id_conta' ORDER BY hora asc");
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

        if ($item) {
            $id2 = $item['id'];
            $valor2 = $item['valor'];
            $cliente2 = $item['cliente'];
            $obs2 = $item['obs'];
            $status2 = $item['status'];
            $data2 = $item['data'];
            $hora2 = $item['hora'];
            $funcionario_id = $item['funcionario'];

            $dataF = implode('/', array_reverse(explode('-', $data2)));
        } else {
            echo "Comanda não encontrada.";
            continue;
        }

        if (!empty($cupom)) {
            $query_cupom = $pdo->prepare("SELECT valor, tipo_desconto, usos_atuais FROM cupons WHERE id = :id AND id_conta = :id_conta");
            $query_cupom->bindValue(":id", $cupom);
            $query_cupom->bindValue(":id_conta", $id_conta, PDO::PARAM_INT);
            $query_cupom->execute();
            $dados_cupom = $query_cupom->fetch(PDO::FETCH_ASSOC);

            if ($dados_cupom) {
                $valor_cupom = $dados_cupom['valor'];
                $tipo_desconto = $dados_cupom['tipo_desconto'];
                if ($tipo_desconto === 'porcentagem') {
                    $desconto_aplicado = $valor2 * ($valor_cupom / 100);
                    $valor_cupom = $desconto_aplicado;
                }
            }
        }

        $query_client = $pdo->query("SELECT nome, cartoes FROM clientes WHERE id = '$cliente' AND id_conta = '$id_conta'");
        $res_client = $query_client->fetch(PDO::FETCH_ASSOC);
        $nome_cliente = $res_client ? $res_client['nome'] : 'Sem Cliente';
        $total_cartoes = $res_client ? $res_client['cartoes'] : 0;

        $query_service = $pdo->query("SELECT nome, valor FROM servicos WHERE id = '$servico' AND id_conta = '$id_conta'");
        $res_service = $query_service->fetch(PDO::FETCH_ASSOC);
        $nome_serv = $res_service ? $res_service['nome'] : 'Não Lançado';
        $valor_serv = $res_service ? $res_service['valor'] : 0;

        $query_prof = $pdo->query("SELECT nome FROM usuarios WHERE id = '$funcionario' AND id_conta = '$id_conta'");
        $res_prof = $query_prof->fetch(PDO::FETCH_ASSOC);
        $nome_prof = $res_prof ? $res_prof['nome'] : '';

        // Assign a color to the professional
        if (!isset($professional_index[$funcionario])) {
            $professional_index[$funcionario] = count($professional_index) % count($professional_colors);
        }
        $color_class = $professional_colors[$professional_index[$funcionario]];

        $status_badge_class = ($status == 'Concluído') ? 'status-completed' : 'status-pending';
        $status_text = ($status == 'Concluído') ? 'Concluído' : 'Em Aberto';
        $row_class = ($status == 'Concluído') ? 'completed' : '';

        echo <<<HTML
        <div class="appointment-row {$row_class} {$color_class}">
            <div class="appointment-time">{$horaF}</div>
            <div class="appointment-client">{$nome_cliente}</div>
            <div class="appointment-service">{$nome_serv}</div>
            <div class="appointment-professional">{$nome_prof}</div>
            <div class="appointment-status {$status_badge_class}">{$status_text}</div>
            <div class="appointment-actions">
                <button class="action-btn command-btn" onclick="editar('{$id2}', '{$valor2}', '{$cliente2}', '{$obs2}', '{$status2}', '{$nome_cliente}', '{$nome_prof}', '{$dataF}', '{$valor_sinal}', '{$valor_cupom}')" title="Abrir Comanda">
                    <i class="fa fa-eye"></i>
                </button>
                <div style="position: relative;">
                    <button class="action-btn delete-btn" onclick="showDeleteDropdown(this)">
                        &times;
                    </button>
                    <div class="delete-confirm-dropdown">
                        <p>Confirmar exclusão? <a href="#" onclick="excluir('{$id}', '{$horaF}')">Sim</a></p>
                    </div>
                </div>
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

document.addEventListener('click', function(e) {
    const dropdowns = document.querySelectorAll('.delete-confirm-dropdown');
    dropdowns.forEach(dropdown => {
        const parentButton = dropdown.previousElementSibling;
        if (!parentButton.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
});

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

function confirmarExclusao(id) {
    if (confirm("Confirma Exclusão?")) {
        excluirComanda(id);
    }
}

function editar(id, valor, cliente, obs, status, nome_cliente, nome_func, data, valor_sinal, valor_cupom) {
    // Código para preencher os campos do modal, que é o mesmo para ambos os casos
    $('#id').val(id);
    $('#cliente').val(cliente).change();
    $('#valor_serv').val(valor);
    var sinal = parseFloat(valor_sinal) || 0;
    var cupom = parseFloat(valor_cupom) || 0;
    var total_descontos = sinal + cupom;
    $('#valor_sinal').val(sinal.toFixed(2));
    $('#valor_cupom').val(cupom.toFixed(2));
    $('#valor_descontos').val(total_descontos.toFixed(2));
    $('#obs').val(obs);
    $('#valor_serv_agd_restante').val('');
    $('#titulo_comanda').text('Editar Comanda Aberta');
    $('#nome_do_cliente_aqui').text('Cliente: ' + nome_cliente);
    listarServicos2(id);
    listarProdutos(id);
    calcular();

    // Verificação de status para mostrar ou esconder o botão
    if (status.trim() === 'Fechada') {
        // Se a comanda estiver fechada, esconde o botão de fechar.
        $('#btn_fechar_comanda').hide();
    } else {
        // Se estiver aberta, mostra o botão.
        $('#btn_fechar_comanda').show();
    }

    // Por fim, mostra o modal. Isso deve ser feito fora do if/else.
    $('#modalForm2').modal('show');
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