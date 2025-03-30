<?php
require_once("../../../conexao.php");
@session_start();
$usuario_logado = @$_SESSION['id_usuario'];
$id_conta = @$_SESSION['id_conta']; // Certifique-se de que $id_conta está definido!

$tabela = 'comandas';
$data_hoje = date('Y-m-d');

$dataInicial = @$_POST['dataInicial'] ?: $data_hoje;  // Usa data de hoje como padrão se dataInicial não for fornecida
$dataFinal = @$_POST['dataFinal'] ?: $data_hoje;      // Usa data de hoje como padrão se dataFinal não for fornecida
$status = @$_POST['status'] ?: '';  // Define $status como string vazia se não for fornecido
$status2 = @$_POST['status'];       // Usado para a mensagem, se não houver comandas

// Estilos CSS (dentro de uma tag <style> para serem incluídos no HTML)
echo <<<HTML
<style>
/* Estilos gerais para melhorar a aparência e responsividade */
.widget.cardTarefas {
    border-radius: 15px;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease-in-out;
    margin-bottom: 20px;
    overflow: hidden;
    background-color: #fff;
	
}

.widget.cardTarefas:hover {
    transform: scale(1.03);
}

.r3_counter_box {
    padding: 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.head-dpdn2 .close {
    position: absolute;
    top: 5px;
    right: 10px;
    font-size: 1.5em;
    color: #dc3545;
    opacity: 0.7;
    background: none;
    border: none;
}

.head-dpdn2 .close:hover {
    opacity: 1;
    cursor: pointer;
}

.dropdown-menu {
    min-width: 200px;
    padding: 10px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    display: none; /* Escondido por padrão */
    position: absolute; /* Posicionamento absoluto */
    z-index: 1000; /* Garante que fique acima de outros elementos */
    background-color: white; /*Fundo branco*/

}

/* Mostra o dropdown no hover */
/* .dropdown:hover .dropdown-menu {
    display: block;
} */

.notification_desc2 p {
    margin: 0;
    font-size: 0.9em;
}

.notification_desc2 a {
    text-decoration: none;
}

.notification_desc2 a:hover {
    text-decoration: underline;
}

.icon-rounded-vermelho {
    border-radius: 50%;
    padding: 5px;
}

.r3_counter_box h5 {
    margin-top: 5px;
    font-size: 1.4em;
    font-weight: bold;
}

.r3_counter_box h3 strong {
    color:rgb(255, 255, 255);
}

.stats.esc {
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    padding-top: 10px;
    margin-top: 10px;
    text-align: center;
    font-size: 0.9em;
}

.stats.esc small {
    color: #555;
    display: block; /* Adicionado */
    width: 100%;    /* Adicionado */
    white-space: nowrap; /* Adicionado */
    overflow: hidden;   /* Adicionado */
    text-overflow: ellipsis; /* Adicionado */
}

.status-agendado {
    background-color: #ffc107;
    color: #000;
}

.status-concluido {
    background-color: #28a745;
    color: #fff;
}

.status-cancelado {
    background-color: #dc3545;
    color: #fff;
}

.classe_imp {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1;
}

@media (max-width: 768px) {

	#valor{
		font-size: 15px;
	}
	#comanda_n{
		font-size: 10px;
	}
	img{
		width: 75px;
	}
}


</style>
HTML;


// Monta a query SQL *fora* do loop
$sql = "SELECT * FROM $tabela WHERE data >= :dataInicial AND data <= :dataFinal AND status LIKE :status AND funcionario = :usuario_logado AND id_conta = :id_conta ORDER BY id ASC";
$query = $pdo->prepare($sql);
$query->bindParam(':dataInicial', $dataInicial, PDO::PARAM_STR);
$query->bindParam(':dataFinal', $dataFinal, PDO::PARAM_STR);
$query->bindParam(':status', $status, PDO::PARAM_STR);  // Já inclui o '%'
$query->bindParam(':usuario_logado', $usuario_logado, PDO::PARAM_INT); //User ID é int
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

            // Define a cor e a imagem com base no status.  Muito mais limpo com switch.
            switch ($status) {
                case 'Aberta':
                    $cor = '#FFA500'; // Amarelo
                    $classe_status = 'status-agendado';
                    $imagem = 'icone-relogio.png';
                    break;
                case 'Fechada':
                    $cor = '#28a745'; // Verde
                    $classe_status = 'status-concluido';
                    $imagem = 'icone-relogio-verde.png';
                    break;
                default:
                    $cor = '#f8f9fa'; // Cor padrão (cinza claro, se o status não for nem Aberta nem Fechada)
                    $imagem = 'icone-relogio.png'; //Uma imagem padrão.
            }

            // Busca o nome do cliente (com tratamento de erro)
            $query_cliente = $pdo->prepare("SELECT nome FROM clientes WHERE id = :id AND id_conta = :id_conta");
            $query_cliente->execute([':id' => $cliente, ':id_conta' => $id_conta]);
            $res_cliente = $query_cliente->fetch(PDO::FETCH_ASSOC);
            $nome_pessoa = $res_cliente ? htmlspecialchars($res_cliente['nome']) : 'Cliente não encontrado';

            // Busca o nome do funcionário (com tratamento de erro)
            $query_func = $pdo->prepare("SELECT nome FROM usuarios WHERE id = :id AND id_conta = :id_conta");
            $query_func->execute([':id' => $funcionario_id, ':id_conta' => $id_conta]);
            $res_func = $query_func->fetch(PDO::FETCH_ASSOC);
            $nome_funcionario = $res_func ? htmlspecialchars($res_func['nome']) : 'Funcionário não encontrado';


            // Gera o HTML para cada comanda.  Note o uso do operador ternário para simplificar.
			echo <<<HTML
			<div class="col-xs-12 col-sm-2 col-md-4 col-lg-3 widget cardTarefas">
				<div class="r3_counter_box {$classe_status}" style="background-color: {$cor};">
					<div class="dropdown">
						<button type="button" class="close" title="Excluir comanda" data-toggle="dropdown" aria-expanded="false" style="margin-top: -20px; font-size: 2em; position: relative; z-index:2;">
							<span aria-hidden="true">&times;</span>
						</button>
						<ul class="dropdown-menu" style='margin-left: -25px' >
							<li>
								<div class="notification_desc2">
									<p>Confirmar<br> Exclusão? <a href="#" onclick="excluirComanda('{$id}')"><span class="text-danger">Sim</span></a></p>
								</div>
							</li>
						</ul>
					</div>
			
					<a class="classe_imp" href="rel/comprovante_comanda.php?id={$id}" target="_blank" title="Gerar Comprovante" style="position: absolute; top: 10px; right: 10px; z-index: 1;">
						<i class="fa-solid fa-print"></i>
					</a>
			
					<div class="row">
						<div class="col-md-3" style="display: flex; flex-direction:column; align-items: center; justify-content: center;">
							<a href="#" onclick="editar('{$id}', '{$valor}', '{$cliente}', '{$obs}', '{$status}', '{$nome_pessoa}', '{$nome_funcionario}', '{$dataF}')">
								<img class="icon-rounded-vermelho" src="../../images/comanda2.jpg" width="110px">
							</a>
						</div>
						<div class="col-md-9" style='margin-top: 30px;'>
							<h3 id='valor'><strong>R$ {$valorF}</strong></h3>
							<div id='comanda_n'> <small>Comanda Nº: {$id}</small><br><small style='font-size: 10px;'>{$hora}</small>
							</div>
						</div>
					</div>
					<div class="stats esc">
						<small>{$nome_pessoa}</small>
					</div>
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
        $('#salvar_comanda').val('').change(); // Esse ID não existe no seu código original.  Verifique!
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
            url: 'paginas/' + pag + "/excluir.php",  // Certifique-se de que 'pag' está definida!
            method: 'POST',
            data: { id },
            dataType: "text",
            success: function(mensagem) {
                if (mensagem.trim() === "Excluído com Sucesso") {
                    // $('#btn-fechar').click(); // Não use isso, pois modalExcluir não foi aberto.
                    listar(); //Recarrega a lista após excluir.
                } else {
                    alert(mensagem); // Usa um alert simples, já que o modal não está aberto.
                }
            },
            error: function(xhr, status, error) {
                alert("Erro ao excluir: " + error); // Mostra um erro se a requisição AJAX falhar.
            }
        });
    }
</script>