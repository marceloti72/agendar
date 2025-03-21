<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'servicos';
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

	if($tipo_comissao == 'Porcentagem'){
		$tipo_comissao = '%';
	}

echo <<<HTML
	<small>
	<table class="table table-hover" id="tabela">
	<thead> 
	<tr> 
	<th>Nome</th>	
	<th class="esc">Categoria</th> 	
	<th class="esc">Valor</th> 	
	<th class="esc">Dias Retorno</th> 
	<th class="esc">Comissão <small>({$tipo_comissao})</small></th>	
	<th class="esc">Tempo</th>	
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];
	$nome = $res[$i]['nome'];	
	$ativo = $res[$i]['ativo'];
	$categoria = $res[$i]['categoria'];
	$dias_retorno = $res[$i]['dias_retorno'];
	$valor = $res[$i]['valor'];
	$foto = $res[$i]['foto'];
	$comissao = $res[$i]['comissao'];
	$tempo = $res[$i]['tempo'];

	$valorF = number_format($valor, 2, ',', '.');

	
	if($ativo == 'Sim'){
		$icone = 'fe-x';
		$titulo_link = 'Desativar';
		$acao = 'Não';
		$classe_linha = '';
		$cor = 'danger';
	}else{
		$icone = 'fe-check';
		$titulo_link = 'Ativar';
		$acao = 'Sim';
		$classe_linha = 'text-muted';
		$cor = 'success';
	}


		$query2 = $pdo->query("SELECT * FROM cat_servicos where id = '$categoria' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_cat = $res2[0]['nome'];
		}else{
			$nome_cat = 'Sem Referência!';
		}


		if($tipo_comissao == '%'){
			$comissaoF = number_format($comissao, 0, ',', '.').'%';
			
			}else{
				$comissaoF = 'R$ '.number_format($comissao, 2, ',', '.');
			}


echo <<<HTML
<tr class="{$classe_linha}">
<td>
<img src="img/servicos/{$foto}" onclick="mostrar('{$nome}', '{$valorF}', '{$nome_cat}', '{$dias_retorno}',  '{$ativo}', '{$foto}', '{$comissaoF}')" title="Ver Dados" width="50" height="50" class="hovv">
{$nome}
</td>
<td class="esc">{$nome_cat}</td>
<td class="esc">R$ {$valorF}</td>
<td class="esc">{$dias_retorno}</td>
<td class="esc">{$comissaoF}</td>
<td class="esc">{$tempo} Minutos</td>
<td>
		<a href="#" class="btn btn-primary btn-xs" onclick="editar('{$id}','{$nome}', '{$valor}', '{$categoria}', '{$dias_retorno}', '{$foto}', '{$comissao}', '{$tempo}')" title="Editar Dados"><i class="fe fe-edit"></i></a>

		<a href="#" class="btn btn-info btn-xs" onclick="mostrar('{$nome}', '{$valorF}', '{$nome_cat}', '{$dias_retorno}',  '{$ativo}', '{$foto}', '{$comissaoF}')" title="Ver Dados"><i class="fe fe-search"></i></a>



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



		<a href="#" class="btn btn-{$cor} btn-xs" onclick="ativar('{$id}', '{$acao}')" title="{$titulo_link}"><i class="fe {$icone}"></i></a>


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
	function editar(id, nome, valor, categoria, dias_retorno, foto, comissao, tempo){
		$('#id').val(id);
		$('#nome').val(nome);
		$('#valor').val(valor);
		$('#categoria').val(categoria).change();
		$('#dias_retorno').val(dias_retorno);
		$('#comissao').val(comissao);
		$('#tempo').val(tempo);
				
		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		$('#foto').val('');
		$('#target').attr('src','img/servicos/' + foto);
	}

	function limparCampos(){
		$('#id').val('');
		$('#nome').val('');
		$('#valor').val('');
		$('#dias_retorno').val('');		
		$('#comissao').val('');
		$('#foto').val('');
		$('#target').attr('src','img/servicos/sem-foto.jpg');
		$('#tempo').val('');
	}
</script>



<script type="text/javascript">
	function mostrar(nome, valor, categoria, dias_retorno, ativo, foto, comissao){

		$('#nome_dados').text(nome);
		$('#valor_dados').text(valor);
		$('#categoria_dados').text(categoria);
		$('#dias_retorno_dados').text(dias_retorno);
		$('#ativo_dados').text(ativo);
		$('#comissao_dados').text(comissao);
		
		$('#target_mostrar').attr('src','img/servicos/' + foto);

		$('#modalDados').modal('show');
	}
</script>