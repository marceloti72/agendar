<?php
require_once("../../../conexao.php");
@session_start();
$usuario_logado = @$_SESSION['id_usuario'];
$id_conta = @$_SESSION['id_conta'];

$tabela = 'comandas';
$data_hoje = date('Y-m-d');

$dataInicial = @$_POST['dataInicial'] ?: $data_hoje;
$dataFinal = @$_POST['dataFinal'] ?: $data_hoje;
$status = @$_POST['status'] ?: '';
$status2 = @$_POST['status'];

if($status != ''){
    $status_pdo = 'AND status = :status ';
}else{
    $status_pdo = '';
}

//verificar se ele tem a permissão de estar nessa página
if(@$_SESSION['nivel_usuario'] != 'administrador'){
    $func = "funcionario = :usuario_logado AND";	   
}else{
    $func = "";
}
?>

<style>
    .comanda-container {
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 10px;
        margin-right: 20px;
        background-color: #fff;
        overflow: hidden;
        height: 120px; /* Altura fixa para compactar */
        display: flex;
        flex-direction: column;
        padding: 0;
        box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4);
    }

    .comanda-top {
        display: flex;
        align-items: center;
        padding: 5px 8px;
        background-color: #007bff; /* Azul */
        color: #fff;
        height: 70%;
    }

    .comanda-top img {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .comanda-info {
        flex: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .comanda-info p {
        margin: 0;
        font-size: 12px;
    }

    .comanda-info .valor {
        font-weight: bold;
        font-size: 16px;
    }

    .comanda-bottom {
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 3px;
        background-color: #f8f9fa;
        height: 30%;
    }

    .comanda-bottom a {
        text-decoration: none;
        color: #007bff; /* Azul */
        font-size: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .comanda-bottom a i {
        font-size: 14px;
        margin-bottom: 1px;
    }

    .comanda-bottom a:hover {
        color: #0056b3; /* Azul mais escuro */
    }

    /* Responsividade para mobile */
    @media (max-width: 768px) {
        .comanda-container {
            height: 80px; /* Ainda mais compacto no mobile */
        }

        .comanda-top {
            padding: 4px 6px;
        }

        .comanda-top img {
            width: 25px;
            height: 25px;
        }

        .comanda-info p {
            font-size: 10px;
        }

        .comanda-info .valor {
            font-size: 12px;
        }

        .comanda-bottom a {
            font-size: 8px;
        }

        .comanda-bottom a i {
            font-size: 12px;
        }
    }
</style>
<?php
// Monta a query SQL
$sql = "SELECT * FROM $tabela WHERE data >= :dataInicial AND data <= :dataFinal $status_pdo AND $func id_conta = :id_conta ORDER BY id ASC";
$query = $pdo->prepare($sql);
$query->bindParam(':dataInicial', $dataInicial, PDO::PARAM_STR);
$query->bindParam(':dataFinal', $dataFinal, PDO::PARAM_STR);
if($status != ''){
    $query->bindParam(':status', $status, PDO::PARAM_STR);
}
if($func != ''){
    $query->bindParam(':usuario_logado', $usuario_logado, PDO::PARAM_INT);
}
$query->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);

try {
    $query->execute();
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $total_reg = count($res);
    if ($total_reg > 0) {        
        foreach ($res as $item) {
            $id = $item['id'];
            $valor = $item['valor'];
            $cliente = $item['cliente'];
            $obs = $item['obs'];
            $status = $item['status'];
            $data = $item['data'];
            $hora = $item['hora'];
            $funcionario_id = $item["funcionario"];

            $dataF = implode('/', array_reverse(explode('-', $data)));
            $valorF = number_format($valor, 2, ',', '.');

            // Define a imagem com base no status
            $cor_comanda = ($status == 'Agendado') ? '#007bff' : 'red';

            // Busca o nome do cliente
            $query_cliente = $pdo->prepare("SELECT nome FROM clientes WHERE id = :id AND id_conta = :id_conta");
            $query_cliente->execute([':id' => $cliente, ':id_conta' => $id_conta]);
            $res_cliente = $query_cliente->fetch(PDO::FETCH_ASSOC);
            $nome_pessoa = $res_cliente ? htmlspecialchars($res_cliente['nome']) : 'Cliente não encontrado';

            // Busca o nome do funcionário
            $query_func = $pdo->prepare("SELECT nome FROM usuarios WHERE id = :id AND id_conta = :id_conta");
            $query_func->execute([':id' => $funcionario_id, ':id_conta' => $id_conta]);
            $res_func = $query_func->fetch(PDO::FETCH_ASSOC);
            $nome_funcionario = $res_func ? htmlspecialchars($res_func['nome']) : 'Funcionário não encontrado';

            // Gera o HTML para cada comanda
            echo <<<HTML
            <div class="col-xs-12 col-sm-2 col-md-4 col-lg-3 comanda-container">
                <div class="comanda-top" style= "background-color: {$cor_comanda} ">
                    <img src="../../images/comanda2.jpg" alt="Ícone Comanda" style='width: 50px; height: 50px;'>
                    <div class="comanda-info">
                        <div>
                            <p class="valor">R$ {$valorF}</p>
                            <p>Comanda: {$id}</p>
                            <p>Cliente: {$nome_pessoa}</p>
                        </div>
                        <div>
                            <p>{$hora}</p>
                        </div>
                    </div>
                </div>
                <div class="comanda-bottom">
                    <a href="#" onclick="editar('{$id}', '{$valor}', '{$cliente}', '{$obs}', '{$status}', '{$nome_pessoa}', '{$nome_funcionario}', '{$dataF}')" title="Abrir Comanda">
                        <i class="fa fa-eye"></i> Abrir
                    </a>
                    <a href="rel/comprovante_comanda.php?id={$id}" target="_blank" title="Gerar Comprovante">
                        <i class="fa fa-print"></i> Comprovante
                    </a>
                    <a href="#" onclick="confirmarExclusao('{$id}')" title="Excluir Comanda">
                        <i class="fa fa-trash"></i> Excluir
                    </a>
                    <!-- <a href="#" onclick="excluirComanda('{$id}')" title="Excluir Comanda">
                        <i class="fa fa-trash"></i> Excluir
                    </a> -->
                </div>
            </div>
            HTML;
        }
    } else {
        echo "<small>Não possui nenhuma comanda {$status2}!</small>";
    }
} catch (PDOException $e) {
    echo "<small>Erro ao buscar comandas: " . htmlspecialchars($e->getMessage()) . "</small>";
}
?>

<script type="text/javascript">
    function confirmarExclusao(id) {
        if (confirm("Confirma Exclusão?")) {
            excluirComanda(id);
        }
    }
    function editar(id, valor, cliente, obs, status, nome_cliente, nome_func, data) {
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
            $('#obs').val(obs);

            $('#valor_serv_agd_restante').val('');

            $('#titulo_comanda').text('Editar Comanda Aberta');
            $('#btn_fechar_comanda').show();
            $('#modalForm').modal('show');

            listarServicos(id);
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

        listarServicos();
        listarProdutos();
        calcular();
    }

    function excluirComanda(id) {
        $.ajax({
            url: 'paginas/' + pag + "/excluir.php",
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