<?php
require_once("../../../conexao.php");
$data_hoje = date('Y-m-d');

@session_start();
if (!isset($_SESSION['id_conta']) || !isset($_SESSION['id_usuario'])) {
    echo '<small>Sessão inválida ou expirada.</small>';
    exit;
}
$id_conta = $_SESSION['id_conta'];
$usuario_logado = $_SESSION['id_usuario'];

$comanda_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$total_servicos = 0;
$html_output = '';

try {
    $query = $pdo->prepare("
        SELECT
            r.id, r.descricao, r.valor, r.funcionario as id_funcionario, r.pago, r.data_venc, r.servico as id_servico,
            s.nome as nome_servico,
            u.nome as nome_funcionario
        FROM receber r
        LEFT JOIN servicos s ON r.servico = s.id AND s.id_conta = r.id_conta
        LEFT JOIN usuarios u ON r.funcionario = u.id AND u.id_conta = r.id_conta
        WHERE r.tipo = 'Serviço'
          AND r.comanda = :comanda_id
          AND r.id_conta = :id_conta
        ORDER BY r.id ASC
    ");
    $query->execute([':comanda_id' => $comanda_id, ':id_conta' => $id_conta]);
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $total_reg = count($res);

    if ($total_reg > 0) {
        $html_output = <<<HTML
        <small>
        <table class="table table-hover table-sm" id="tabela_servicos">
        <thead>
        <tr>
        <th>Serviço</th>
        <th class="esc text-right">Valor</th> 
        <th class="esc">Profissional</th>
        <th class="text-center">Ações</th>
        </tr>
        </thead>
        <tbody>
HTML;

        foreach ($res as $item) {
            $id_item_receber = $item['id'];
            $descricao_item = htmlspecialchars($item['descricao'] ?: $item['nome_servico'] ?: 'Serviço Desconhecido');
            $valor_item = floatval($item['valor']);
            $nome_func_item = htmlspecialchars($item['nome_funcionario'] ?: 'N/D');
            $valor_item_formatado = number_format($valor_item, 2, ',', '.');
            $classe_valor = ($valor_item == 0) ? 'text-success font-weight-bold' : '';
            $total_servicos += $valor_item;

            $classe_debito = ($item['pago'] != 'Sim' && !empty($item['data_venc']) && strtotime($item['data_venc']) < strtotime($data_hoje)) ? 'text-danger' : '';

            $html_output .= <<<HTML
            <tr class="{$classe_debito}"> 
                <td>{$descricao_item}</td>
                <td class="esc text-right {$classe_valor}">R$ {$valor_item_formatado}</td> 
                <td class="esc">{$nome_func_item}</td>
                <td class="text-center">                    
                    <a href="#" class="excluir-servico" data-id="{$id_item_receber}" data-comanda="{$comanda_id}" title="Excluir Lançamento">
                        <i class="fa fa-trash-o text-danger"></i>
                    </a>                   
                </td>
            </tr>
HTML;
        }

        $total_servicosF = number_format($total_servicos, 2, ',', '.');        
        $html_output .= <<<HTML
        </tbody>
        </table>
        <div class="text-right mt-2" style="margin-right: 5px;">
            <strong>Total Serviços:</strong> <span class="text-success" id="total-servicos-display">R$ {$total_servicosF} </span>
        </div>
        </small>
        <script type="text/javascript">
            var pag = 'comanda'; // Ajuste conforme necessário
            var valor = {$total_servicos};            

            $(document).ready(function() {
                $('#valor_servicos').val(valor);
            });
            $(document).ready(function() {
                $('.excluir-servico').on('click', function(e) {
                    e.preventDefault();
                    var idItemReceber = $(this).data('id');
                    var idComanda = $(this).data('comanda');
                    excluirServico(idItemReceber, idComanda);
                });
            });

            function excluirServico(idItemReceber, idComanda) { 
                console.log("Tentando excluir serviço (item receber):", idItemReceber, "Comanda:", idComanda);
                if (!idItemReceber || idItemReceber <= 0 || !idComanda || idComanda <= 0) {
                    Swal.fire('Erro Interno', 'Não foi possível identificar o serviço a ser excluído corretamente.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Tem Certeza?',
                    text: "Deseja realmente excluir este serviço da comanda? A comissão associada também será removida.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Excluindo...',
                            text: 'Aguarde...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: 'paginas/' + pag + "/excluir_servico.php",
                            method: 'POST',
                            data: {
                                id_receber: idItemReceber,
                                id_comanda: idComanda,
                                id_conta: '{$id_conta}'
                            },
                            dataType: 'json',
                            success: function(response) {
                                Swal.close();
                                if (response && response.success) {
                                    Swal.fire('Excluído!', response.message || 'Serviço removido da comanda.', 'success');
                                    if (typeof listarServicos === 'function') listarServicos(idComanda);
                                    if (typeof calcular === 'function') calcular();
                                } else {
                                    Swal.fire('Erro!', response.message || 'Não foi possível excluir o serviço.', 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.close();
                                Swal.fire('Erro Crítico!', 'Falha na comunicação com o servidor.', 'error');
                                console.error("Erro AJAX:", status, error, xhr.responseText);
                            }
                        });
                    }
                });
            }
        </script>
HTML;
    } else {
        $html_output = '<small>Nenhum serviço lançado nesta comanda!</small>';
        $html_output .= "<script>$('#valor_servicos').val('0');</script>";
    }
} catch (PDOException $e) {
    error_log("Erro ao listar serviços da comanda {$comanda_id}: " . $e->getMessage());
    $html_output = '<small class="text-danger">Erro ao carregar serviços da comanda.</small>';
    $html_output .= "<script>$('#valor_servicos').val('0');</script>";
}

echo $html_output;
?>