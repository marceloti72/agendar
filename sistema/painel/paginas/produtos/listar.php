<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'produtos';
?>
<style>
	@media (max-width: 768px) {
	    .dataTables_length {
				display: none;
			}
	}
</style>
<?php 

$query = $pdo->query("SELECT * FROM $tabela where id_conta = '$id_conta' ORDER BY id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="tabela">
	<thead> 
	<tr> 
	<th>Nome</th>	
	<!-- <th class="esc">Categoria</th> 	 -->
	<th class="esc">Valor Compra</th> 	
	<th class="esc">Valor Venda</th> 
	<th class="esc">Estoque</th>	
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];
	$nome = $res[$i]['nome'];	
	$descricao = $res[$i]['descricao'];
	$categoria = $res[$i]['categoria'];
	$valor_compra = $res[$i]['valor_compra'];
	$valor_venda = $res[$i]['valor_venda'];
	$foto = $res[$i]['foto'];
	$estoque = $res[$i]['estoque'];
	$nivel_estoque = $res[$i]['nivel_estoque'];

	$valor_vendaF = number_format($valor_venda, 2, ',', '.');
	$valor_compraF = number_format($valor_compra, 2, ',', '.');

	

		$query2 = $pdo->query("SELECT * FROM cat_produtos where id = '$categoria' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_cat = $res2[0]['nome'];
		}else{
			$nome_cat = 'Sem Referência!';
		}


		if($nivel_estoque >= $estoque){
			$alerta_estoque = 'text-danger';
		}else{
			$alerta_estoque = '';
		}
		


echo <<<HTML
<tr class="{$alerta_estoque}">
<td>
<img src="img/produtos/{$foto}" onclick="mostrar('{$nome}', '{$nome_cat}', '{$descricao}', '{$valor_compraF}', '{$valor_vendaF}', '{$estoque}', '{$foto}', '{$nivel_estoque}')" title="Ver Dados" width="50" height="50" class="hovv">
{$nome}
</td>
<!-- <td class="esc">{$nome_cat}</td> -->
<td class="esc">R$ {$valor_compraF}</td>
<td class="esc">R$ {$valor_vendaF}</td>
<td class="esc">{$estoque}</td>

<td>
		<a href="#" class="btn btn-primary btn-xs" onclick="editar('{$id}','{$nome}', '{$categoria}', '{$descricao}', '{$valor_compra}', '{$valor_venda}', '{$foto}', '{$nivel_estoque}')" title="Editar Dados"><i class="fe fe-edit"></i></a>

		<a href="#" class="btn btn-info btn-xs" onclick="mostrar('{$nome}', '{$nome_cat}', '{$descricao}', '{$valor_compraF}',  '{$valor_vendaF}', '{$estoque}', '{$foto}', '{$nivel_estoque}')" title="Ver Dados"><i class="fe fe-search"></i></a>



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


		<a href="#" class="btn btn-danger btn-xs" onclick="saida('{$id}','{$nome}', '{$estoque}')" title="Saída de Produto"><i class="fe fe-log-out"></i></a>

		<a href="#" class="btn btn-success btn-xs" onclick="entrada('{$id}','{$nome}', '{$estoque}')" title="Entrada de Produto"><i class="fe fe-log-in"></i></a>

	
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
	function editar(id, nome, categoria, descricao, valor_compra, valor_venda, foto, nivel_estoque){
		$('#id').val(id);
		$('#nome').val(nome);
		$('#valor_venda').val(valor_venda);
		$('#valor_compra').val(valor_compra);
		$('#categoria').val(categoria).change();
		$('#descricao').val(descricao);
		$('#nivel_estoque').val(nivel_estoque);
						
		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		$('#foto').val('');
		$('#target').attr('src','img/produtos/' + foto);
	}

	function limparCampos(){
		$('#id').val('');
		$('#nome').val('');
		$('#valor_compra').val('');
		$('#valor_venda').val('');
		$('#descricao').val('');		
		$('#foto').val('');
		$('#target').attr('src','img/produtos/sem-foto.jpg');
	}
</script>



<script type="text/javascript">
	function mostrar(nome, categoria, descricao, valor_compra, valor_venda, estoque, foto, nivel_estoque){

		$('#nome_dados').text(nome);
		$('#valor_compra_dados').text(valor_compra);
		$('#categoria_dados').text(categoria);
		$('#valor_venda_dados').text(valor_venda);
		$('#descricao_dados').text(descricao);
		$('#estoque_dados').text(estoque);
		$('#nivel_estoque_dados').text(nivel_estoque);
		
		$('#target_mostrar').attr('src','img/produtos/' + foto);

		$('#modalDados').modal('show');
	}
</script>




<script type="text/javascript">
	function saida(id, nome, estoque){

		$('#nome_saida').text(nome);
		$('#estoque_saida').val(estoque);
		$('#id_saida').val(id);		

		$('#modalSaida').modal('show');
	}
</script>


<script type="text/javascript">
	function entrada(id, nome, estoque){

		$('#nome_entrada').text(nome);
		$('#estoque_entrada').val(estoque);
		$('#id_entrada').val(id);		

		$('#modalEntrada').modal('show');
	}
</script>