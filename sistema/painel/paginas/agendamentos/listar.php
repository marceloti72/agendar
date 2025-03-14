<?php
require_once("../../../conexao.php");
@session_start();
$usuario = @$_SESSION['id_usuario'];
$data_atual = date('Y-m-d');
?>
<style>
	.service-item {
		display: flex;
		align-items: center;
		padding: 10px;
		border-radius: 8px;
		background-color: #f9f9f9;
		box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
		margin-bottom: 10px;
		transition: all 0.3s ease;
	}

	/* Ajuste das colunas */
	.dropdown-column {
		padding-right: 15px;
		/* Espaçamento à direita para separar */
		display: flex;
		justify-content: center;
		align-items: flex-start;
	}

	.service-column {
		padding-left: 15px;
		/* Espaçamento à esquerda para separar */
	}

	.service-item:hover {
		box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
		background-color: rgb(194, 229, 200);
	}

	.service-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		width: 100%;
		margin: 0;

	}

	.service-time {
		font-size: 16px;
		font-weight: 600;
		color: #333;
	}

	.service-actions {
		display: flex;
		gap: 8px;
	}

	.btn {
		padding: 6px 12px;
		font-size: 12px;
		font-weight: 500;
		border: none;
		border-radius: 5px;
		cursor: pointer;
		transition: all 0.2s ease;
	}

	.btn-history {
		background-color: #007bff;
		color: white;
	}

	.btn-history:hover {
		background-color: #0056b3;
	}

	.btn-finish {
		background-color: #dc3545;
		color: white;
	}

	.btn-finish:hover {
		background-color: #b02a37;
	}

	.payment-status {
		font-size: 12px;
		font-weight: 300;
		padding: 4px 8px;
		border-radius: 12px;
	}

	.verde {
		color: #28a745;
		background-color: #e6ffe6;
	}

	/* Classe dinâmica para status (exemplo) */
	.finalizado {
		background-color: #6c757d;
		cursor: not-allowed;
	}

	.finalizado:hover {
		background-color: #5c636a;
	}
</style>
<?php


$funcionario = @$_POST['funcionario'];
$data = @$_POST['data'];

if ($data == "") {
	$data = date('Y-m-d');
}

if ($funcionario == "") {
	echo '<small>Selecione um Funcionário!</small>';
	exit();
}

echo <<<HTML
<small>
HTML;
$query = $pdo->query("SELECT * FROM agendamentos where funcionario = '$funcionario' and data = '$data' and id_conta = '$id_conta' ORDER BY hora asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$id = $res[$i]['id'];
		$funcionario = $res[$i]['funcionario'];
		$cliente = $res[$i]['cliente'];
		$hora = $res[$i]['hora'];
		$data = $res[$i]['data'];
		$usuario = $res[$i]['usuario'];
		$data_lanc = $res[$i]['data_lanc'];
		$obs = $res[$i]['obs'];
		$status = $res[$i]['status'];
		$servico = $res[$i]['servico'];
		$valor_pago = $res[$i]['valor_pago'];
		$origem = $res[$i]['origem'];
		if($usuario == $funcionario){
			$origem = 'Profissional';
		}

		$valor_pagoF = number_format($valor_pago, 2, ',', '.');
		if ($valor_pago > 0 and $status == 'Agendado') {
			$classe_valor_pago = '';
		} else {
			$classe_valor_pago = 'ocultar';
		}

		$dataF = implode('/', array_reverse(explode('-', $data)));
		$horaF = date("H:i", strtotime($hora));


		if ($status == 'Concluído') {
			$classe_linha = '';
		} else {
			$classe_linha = 'text-muted';
		}



		if ($status == 'Agendado') {
			$imagem = 'relogio-vermelho.png';
			$classe_status = '';
			$classe_finalizado = 'ocultar';
			$finalizado = '';
			$cor2 = '';
			$cor3 = '';
		} else {
			$imagem = 'relogio-azul.png';
			$classe_status = 'ocultar';
			$classe_finalizado = '';
			$finalizado = 'Serviço Finalizado!';
			$cor2 = '#836FFF';
			$cor3 = 'white';
		}

		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_usu = $res2[0]['nome'];
		} else {
			$nome_usu = 'Sem Usuário';
		}


		$query2 = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_serv = $res2[0]['nome'];
			$valor_serv = $res2[0]['valor'];
		} else {
			$nome_serv = 'Não Lançado';
			$valor_serv = '';
		}


		$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_cliente = $res2[0]['nome'];
			$total_cartoes = $res2[0]['cartoes'];
		} else {
			$nome_cliente = 'Sem Cliente';
			$total_cartoes = 0;
		}

		if ($total_cartoes >= $quantidade_cartoes and $status == 'Agendado') {
			$ocultar_cartoes = '';
		} else {
			$ocultar_cartoes = 'ocultar';
		}

		//retirar aspas do texto do obs
		$obs = str_replace('"', "**", $obs);

		$classe_deb = '#043308';
		$total_debitos = 0;
		$total_pagar = 0;
		$total_vencido = 0;
		$total_debitosF = 0;
		$total_pagarF = 0;
		$total_vencidoF = 0;
		$query2 = $pdo->query("SELECT * FROM receber where pessoa = '$cliente' and pago != 'Sim' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$classe_deb = '#661109';
			for ($i2 = 0; $i2 < $total_reg2; $i2++) {
				$valor_s = $res2[$i2]['valor'];
				$data_venc = $res2[$i2]['data_venc'];

				$total_debitos += $valor_s;
				$total_debitosF = number_format($total_debitos, 2, ',', '.');


				if (strtotime($data_venc) < strtotime($data_atual)) {
					$total_vencido += $valor_s;
				} else {
					$total_pagar += $valor_s;
				}

				$total_pagarF = number_format($total_pagar, 2, ',', '.');
				$total_vencidoF = number_format($total_vencido, 2, ',', '.');
			}
		}

		if ($valor_serv == $valor_pago) {
			$valor_pagoF = 'Pagamento concluido!';
			$cor = '#90EE90';
		} else {
			$valor_pagoF = 'Sinal pago R$ ' . $valor_pagoF;
			$cor = '';
		}

		if ($valor_pago > 0) {
			$valor_serv = $valor_serv - $valor_pago;
		}



		echo <<<HTML
			<div class="col-xs-12 col-md-4 widget cardTarefas mobile100">
        		<div class="r3_counter_box">     		
        		
        		

				<li class="dropdown head-dpdn2" style="list-style-type: none;">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
		<button type="button" class="close" title="Excluir agendamento" style="margin-top: -10px">
					<span aria-hidden="true"><big>&times;</big></span>
				</button>
				</a>

		<ul class="dropdown-menu" style="margin-left:-30px;">
		<li>
		<div class="notification_desc2">
		<p>Confirmar Exclusão? <a href="#" onclick="excluir('{$id}', '{$horaF}')"><span class="text-danger">Sim</span></a></p>
		</div>
		</li>										
		</ul>
		</li>


		<div class="row">
        		<div class="col-md-3 dropdown-column">
				<li class="dropdown head-dpdn2" style="list-style-type: none;">
				<a href="#" class="dropdown-toggle" title="Histórico financeiro do cliente" data-toggle="dropdown" aria-expanded="false">
		<img class="icon-rounded-vermelho" src="img/{$imagem}" width="75px" height="75px">
				</a>

		<ul class="dropdown-menu" style="margin-left:-30px;">
		<li>
		<div class="notification_desc2">
		<p>
		<span style="margin-right: 20px; "><b>Débitos do Cliente</b></span><br>
		<span style="margin-right: 20px; ">Total Vencido <span style="color:red">R$ {$total_vencidoF}</span></span><br>
		<span style="margin-right: 20px; ">Total à Vencer <span style="color:blue">R$ {$total_pagarF}</span></span><br>
		<span >Total Pagar <span style="color:green">R$ {$total_debitosF}</span></span>
		</p>
		<p>Observações: {$obs}</p>
		</div>
		</li>										
		</ul>
		</li>
        			 
        		</div>
        		<div class="col-md-9 service-column" >
				<div class="service-item">
					<h5 class="service-header" >
					<a href="#" onclick="verificarStatus('{$id}', '{$cliente}', '{$servico}', '{$valor_serv}', '{$funcionario}', '{$nome_serv}', '{$status}')" title="Finalizar Serviço">
					<strong class="service-time">
						<h2>{$horaF}</h2>
					</strong>
					</a>
						<div class="service-actions">					
						
						</div>
												
					</h5>
					
				</div>        			
        		</div>
				<button style='cursor: default;width: 100%; background-color: {$cor};' class="payment-status {$classe_valor_pago}" >{$valor_pagoF}</button>
				<button style='cursor: default;width: 100%; background-color: {$cor2}; color: {$cor3};' class="payment-status {$classe_finalizado}" >{$finalizado}</button>
        		</div>
        		
        					
        		<hr style="margin-top:-2px; margin-bottom: 3px">                    
                    <div class="stats" align="center">
                      <span style="">                      
                        <small> <span class="{$ocultar_cartoes}" style=""><img class="icon-rounded-vermelho" src="img/presente.jpg" width="20px" height="20px"></span> <span style="color:{$classe_deb}; font-size:13px">{$nome_cliente}</span> (<i><span style="color:#061f9c; font-size:12px">{$nome_serv}</span></i>)</small></span>
						<small><small><button style='cursor: default;border-radius: 10px;border: 0px;background-color: #F0E68C;'>via {$origem}</button></small></small>
                    </div>
                </div>
        	</div>
HTML;
	}
} else {
	echo 'Nenhum horário para essa Data!';
}

?>





<script type="text/javascript">
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
			// Opcional: Exibir uma mensagem informando que o serviço já foi concluído
			alert('Este serviço já foi concluído.');
		}
	}
</script>