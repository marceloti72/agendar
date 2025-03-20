<?php 
require_once("../../../conexao.php");
$tabela = 'clientes';
$data_atual = date('Y-m-d');
?>
<style>
        #tabela tr:nth-child(even) { /* Linhas pares */
            background-color: #f2f2f2;
        }
        #tabela tr:nth-child(odd) { /* Linhas ímpares */
            background-color: #ffffff;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            padding: 10px;
            text-align: left;
        }
        .table th {
            background-color: #f8f8f8;
            font-weight: bold;
        }
        .hovv {
            cursor: pointer;
            border-radius: 50%;
            object-fit: cover;
            width: 50px;
            height: 50px;
            transition: transform 0.5s;
        }
        .hovv:hover {
            transform: scale(3);
        }
        .btn {
            padding: 5px 10px;
            margin: 2px;
            font-size: 14px;
            text-decoration: none;
            color: #fff;
            border-radius: 4px;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .btn-info {
            background-color: #17a2b8;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-success {
            background-color: #28a745;
        }
        .dropdown-menu {
            position: absolute;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }
        .dropdown-menu a {
            color: #dc3545;
            text-decoration: none;
        }
        .text-muted {
            color: #6c757d !important;
        }
        .esc {
            display: table-cell; /* Visível por padrão em desktop */
        }

        /* Media Query para Mobile (max-width: 768px) */
        @media (max-width: 768px) {
            .table {
                display: block;
                overflow-x: auto; /* Permite rolagem horizontal se necessário */
                white-space: nowrap;
            }
            .table th, .table td {
                padding: 8px;
                font-size: 12px; /* Reduz tamanho da fonte */
            }
            .esc {
                display: none; /* Esconde colunas menos importantes em mobile */
            }
            .hovv {
                width: 30px;
                height: 30px; /* Reduz tamanho da foto em mobile */
            }
            .btn {
                padding: 4px 8px;
                font-size: 12px; /* Reduz tamanho dos botões */
            }
            .btn i {
                font-size: 12px;
            }
            .dropdown-menu {
                margin-left: 0 !important; /* Ajusta posição do dropdown */
                min-width: 120px;
                font-size: 12px;
            }
            #mensagem-excluir {
                font-size: 12px;
            }

			.dataTables_length {
				display: none;
			}

			.notification_desc2{
				width: 80px;
			}
        }
    </style>
<?php 




$query = $pdo->query("SELECT * FROM $tabela where id_conta = '$id_conta' ORDER BY id desc ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="tabela">
	<thead> 
	<tr> 
	<th>Nome</th>	
	<th class="esc" style='width: 100px;'>Telefone</th> 
	<th class="esc">CPF</th> 	
	<th class="esc">Cadastro</th> 	
	<th class="esc">Nascimento</th> 
	<th class="esc">Retorno</th> 
	<th class="esc">Cartões</th> 
	<th style='width: 270px;'>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];
	$nome = $res[$i]['nome'];	
	$data_nasc = $res[$i]['data_nasc'];
	$data_cad = $res[$i]['data_cad'];	
	$telefone = $res[$i]['telefone'];
	$endereco = $res[$i]['endereco'];
	$cartoes = $res[$i]['cartoes'];
	$data_retorno = $res[$i]['data_retorno'];
	$ultimo_servico = $res[$i]['ultimo_servico'];
	$cpf = $res[$i]['cpf'];

	$data_cadF = implode('/', array_reverse(@explode('-', $data_cad)));
	$data_nascF = implode('/', array_reverse(@explode('-', $data_nasc)));
	$data_retornoF = implode('/', array_reverse(@explode('-', $data_retorno)));
	
	if($data_nascF == '00/00/0000'){
		$data_nascF = 'Sem Lançamento';
	}
	
	
	$whats = '55'.preg_replace('/[ ()-]+/' , '' , $telefone);

	$query2 = $pdo->query("SELECT * FROM servicos where id = '$ultimo_servico' and id_conta = '$id_conta'");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res2) > 0){
		$nome_servico = $res2[0]['nome'];
	}else{
		$nome_servico = 'Nenhum!';
	}


	$query2 = $pdo->query("SELECT * FROM receber where pessoa = '$id' and id_conta = '$id_conta' order by id desc limit 1");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res2) > 0){
		$obs_servico = $res2[0]['obs'];
		$valor_servico = $res2[0]['valor'];
		$data_servico = $res2[0]['data_lanc'];
		$valor_servico = number_format($valor_servico, 2, ',', '.');
		$data_servico = implode('/', array_reverse(@explode('-', $data_servico)));
	}else{
		$obs_servico = '';
		$valor_servico = '';
		$data_servico = '';
	}

	
	

	if($data_retorno != "" and strtotime($data_retorno) <  strtotime($data_atual)){
		$classe_retorno = 'text-danger';
	}else{
		$classe_retorno = '';
	}
	
	


echo <<<HTML
<tr class="">
<td>{$nome}</td>
<td class="esc">{$telefone}</td>
<td class="esc">{$cpf}</td>
<td class="esc">{$data_cadF}</td>
<td class="esc">{$data_nascF}</td>
<td class="esc {$classe_retorno}">{$data_retornoF}</td>
<td class="esc">{$cartoes}</td>
<td>
		<a href="#" class="btn btn-primary btn-xs" onclick="editar('{$id}','{$nome}', '{$telefone}', '{$endereco}', '{$data_nasc}', '{$cartoes}', '{$cpf}')" title="Editar Dados"><i class="fe fe-edit"></i></a>

		<a href="#" class="btn btn-info btn-xs" onclick="mostrar('{$id}','{$nome}', '{$telefone}', '{$cartoes}', '{$data_cadF}', '{$data_nascF}', '{$endereco}', '{$data_retornoF}', '{$nome_servico}', '{$obs_servico}', '{$valor_servico}', '{$data_servico}')" title="Ver Dados"><i class="fe fe-search"></i></a>



		<li class="dropdown head-dpdn2" style="display: inline-block;">
		<a href="#" class="btn btn-danger btn-xs" data-toggle="dropdown" aria-expanded="false"><i class="fe fe-trash-2"></i></a>

		<ul class="dropdown-menu" style="margin-left:-230px;">
		<li>
		<div class="notification_desc2">
		<p>Confirmar Exclusão? <a href="#" onclick="excluir('{$id}')"><span class="text-danger">Sim</span></a></p>
		</div>
		</li>										
		</ul>
		</li>


		<a href="http://api.whatsapp.com/send?1=pt_BR&phone=$whats&text=" target="_blank" class="btn btn-success btn-xs" title="Abrir Whatsapp"><i class="fab fa-whatsapp fa-2x" style = 'font-size: 17px;'></i></a>



		<a class="btn btn-info btn-xs" href="#" onclick="contrato('{$id}','{$nome}')" title="Contrato de Serviço"><i class="fe fe-file-text"></i></a>

		<!-- <a class="btn btn-primary btn-xs" href="paginas/clientes.php?funcao=ultserv&id={$id}" title="Últimos Serviços"><i class="fe fe-list"></i></a> -->

		<a class="btn btn-primary btn-xs" href="rel/rel_servicos_clientes.php?id={$id}" target="_blank" title="Últimos Serviços"><i class="fe fe-list"></i></a>

		</td>
</tr>
HTML;

}

echo <<<HTML
</tbody>
<small><div align="center" id="mensagem-excluir"></div></small>
</table>
</small>

HTML;  

}else{
	echo '<small>Não possui nenhum registro Cadastrado!</small>';
}

?>

<script type="text/javascript">
	$(document).ready( function () {
    $('#tabela').DataTable({
    		"ordering": false,
			"stateSave": true
    	});
    $('#tabela_filter label input').focus();
} );
</script>


<script type="text/javascript">
	function editar(id, nome, telefone, endereco, data_nasc, cartoes, cpf){		
		$('#id').val(id);
		$('#nome').val(nome);		
		$('#telefone').val(telefone);		
		$('#endereco').val(endereco);
		$('#data_nasc').val(data_nasc);
		$('#cartao').val(cartoes);
		$('#cpf').val(cpf);

		
		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		
	}

	function limparCampos(){
		$('#id').val('');
		$('#nome').val('');
		$('#telefone').val('');
		$('#endereco').val('');
		$('#data_nasc').val('');
		$('#cartao').val('0');
		$('#cpf').val('');
	}
</script>



<script type="text/javascript">
	function mostrar(id, nome, telefone, cartoes, data_cad, data_nasc, endereco, retorno, servico, obs, valor, data){

		$('#nome_dados').text(nome);		
		$('#data_cad_dados').text(data_cad);
		$('#data_nasc_dados').text(data_nasc);
		$('#cartoes_dados').text(cartoes);
		$('#telefone_dados').text(telefone);
		$('#endereco_dados').text(endereco);
		$('#retorno_dados').text(retorno);		
		$('#servico_dados').text(servico);
		$('#obs_dados_tab').text(obs);
		$('#servico_dados_tab').text(servico);
		$('#data_dados_tab').text(data);
		$('#valor_dados_tab').text(valor);

		$('#modalDados').modal('show');
		listarDebitos(id)
	}
</script>



<script type="text/javascript">
	function contrato(id, nome){		
		$('#titulo_contrato').text(nome);
		$('#id_contrato').val(id);		
		$('#modalContrato').modal('show');
		listarTextoContrato(id);
		
	}



</script>