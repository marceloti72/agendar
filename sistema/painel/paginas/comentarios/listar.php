<?php 
require_once("../../../conexao.php");
$tabela = 'comentarios';

$query = $pdo->query("SELECT * FROM $tabela where id_conta = '$id_conta' ORDER BY id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="tabela">
	<thead> 
	<tr> 
	<th>Cliente</th>	
	<th class="esc">Texto</th> 		
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];	
	$foto = $res[$i]['foto'];
	$texto = $res[$i]['texto'];
	$nome = $res[$i]['nome'];
	$ativo = $res[$i]['ativo'];

	$textoF = mb_strimwidth($texto, 0, 100, "...");

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


echo <<<HTML
<tr class="{$classe_linha}">
<td>
<img src="img/comentarios/{$foto}" onclick="mostrar('{$nome}', '{$texto}', '{$foto}')" width="50" height="50" class="hovv">
{$nome}
</td>
<td class="esc">{$textoF}</td>
<td>
		<a href="#" class="btn btn-primary btn-xs" onclick="editar('{$id}','{$nome}', '{$texto}', '{$foto}')" title="Editar Dados"><i class="fe fe-edit"></i></a>

		<a href="#" class="btn btn-info btn-xs" onclick="mostrar('{$nome}', '{$texto}', '{$foto}')" title="Ver Dados"><i class="fe fe-search"></i></a>



		<li class="dropdown head-dpdn2" style="display: inline-block;">
		<a href="#" class="btn btn-danger btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fe fe-trash-2"></i></a>

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
	function editar(id, nome, texto, foto){
		$('#id').val(id);
		$('#nome').val(nome);
		$('#texto').val(texto);
					
		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		$('#foto').val('');
		$('#target').attr('src','img/comentarios/' + foto);
	}

	function limparCampos(){
		$('#id').val('');
		$('#nome').val('');
		$('#texto').val('');
		
		$('#foto').val('');
		$('#target').attr('src','img/comentarios/sem-foto.jpg');
	}
</script>



<script type="text/javascript">
	function mostrar(nome, texto, foto){

		$('#nome_dados').text(nome);
		$('#texto_dados').text(texto);
				
		$('#target_mostrar').attr('src','img/comentarios/' + foto);

		$('#modalDados').modal('show');
	}
</script>