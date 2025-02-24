<?php 
require_once("../../../conexao.php");
$tabela = 'usuarios';
?>
<style>
#tabela tr:nth-child(even) { /* Linhas pares */
  background-color: #f2f2f2; /* Tom mais claro */
}

#tabela tr:nth-child(odd) { /* Linhas ímpares */
  background-color: #ffffff; /* Tom mais escuro (ou branco) */
}
</style>
<?php 

$query = $pdo->query("SELECT * FROM $tabela ORDER BY id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="tabela">
	<thead> 
	<tr> 
	<th>Nome</th>	
	<th class="esc">Email</th> 	
	<th class="esc">Senha</th> 	
	<th class="esc">Nível</th> 	
	<th class="esc">Cadastro</th>
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];
	$nome = $res[$i]['nome'];
	$email = $res[$i]['email'];
	$cpf = $res[$i]['cpf'];
	$senha = $res[$i]['senha'];
	$nivel = $res[$i]['nivel'];
	$data = $res[$i]['data'];
	$ativo = $res[$i]['ativo'];
	$telefone = $res[$i]['telefone'];
	$endereco = $res[$i]['endereco'];
	$foto = $res[$i]['foto'];
	$atendimento = $res[$i]['atendimento'];

	$dataF = implode('/', array_reverse(explode('-', $data)));
	
	if($nivel == 'Administrador'){
		$senhaF = '******';
	}else{
		$senhaF = $senha;
	}


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
<img src="img/perfil/{$foto}" onclick="mostrar('{$nome}', '{$email}', '{$cpf}', '{$senhaF}', '{$nivel}', '{$dataF}', '{$ativo}', '{$telefone}', '{$endereco}', '{$foto}', '{$atendimento}')" title="Ver Dados" width="50" height="50" class="hovv">
{$nome}
</td>
<td class="esc">{$email}</td>
<td class="esc">{$senhaF}</td>
<td class="esc">{$nivel}</td>
<td class="esc">{$dataF}</td>
<td>
		<a href="#" class="btn btn-primary btn-xs" onclick="editar('{$id}','{$nome}', '{$email}', '{$telefone}', '{$cpf}', '{$nivel}', '{$endereco}', '{$foto}', '{$atendimento}')" title="Editar Dados"><i class="fe fe-edit"></i></a>

		<a href="#" class="btn btn-info btn-xs" onclick="mostrar('{$nome}', '{$email}', '{$cpf}', '{$senhaF}', '{$nivel}', '{$dataF}', '{$ativo}', '{$telefone}', '{$endereco}', '{$foto}', '{$atendimento}')" title="Ver Dados"><i class="fe fe-search"></i></a>



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

		<a href="#" class="btn btn-primary btn-xs" onclick="permissoes('{$id}', '{$nome}')" title="Definir Permissões"><i class="fe fe-lock"></i></a>


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
	function editar(id, nome, email, telefone, cpf, nivel, endereco, foto, atendimento){
		$('#id').val(id);
		$('#nome').val(nome);
		$('#email').val(email);
		$('#telefone').val(telefone);
		$('#cpf').val(cpf);
		$('#cargo').val(nivel).change();
		$('#endereco').val(endereco);
		$('#atendimento').val(atendimento).change();

		
		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		$('#foto').val('');
		$('#target').attr('src','img/perfil/' + foto);
	}

	function limparCampos(){
		$('#id').val('');
		$('#nome').val('');
		$('#telefone').val('');
		$('#email').val('');
		$('#cpf').val('');
		$('#endereco').val('');
		$('#foto').val('');
		$('#target').attr('src','img/perfil/sem-foto.jpg');
	}
</script>



<script type="text/javascript">
	function mostrar(nome, email, cpf, senha, nivel, data, ativo, telefone, endereco, foto, atendimento){

		$('#nome_dados').text(nome);
		$('#email_dados').text(email);
		$('#cpf_dados').text(cpf);
		$('#senha_dados').text(senha);
		$('#nivel_dados').text(nivel);
		$('#data_dados').text(data);
		$('#ativo_dados').text(ativo);
		$('#telefone_dados').text(telefone);
		$('#endereco_dados').text(endereco);
		$('#atendimento_dados').text(atendimento);

		$('#target_mostrar').attr('src','img/perfil/' + foto);

		$('#modalDados').modal('show');
	}
</script>

<script type="text/javascript">
	function permissoes(id, nome){		
    $('#id-usuario').val(id);        
    $('#nome-usuario').text(nome);   
    $('#modalPermissoes').modal('show');
    $('#mensagem-permissao').text(''); 
    listarPermissoes(id);
}
</script>