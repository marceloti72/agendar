<?php 
require_once("../../../conexao.php");
$tabela = 'comentarios';

?>
<style>
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            padding: 10px;
            text-align: left;
            vertical-align: middle;
        }
        .table th {
            background-color: #f8f8f8;
            font-weight: bold;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .btn {
            padding: 5px 10px;
            margin: 2px;
            font-size: 14px;
            text-decoration: none;
            color: #fff;
            border-radius: 4px;
            display: inline-block;
        }
        .btn-info { background-color: #17a2b8; }
        .btn-danger { background-color: #dc3545; }
        .btn-success { background-color: #28a745; }
        .dropdown-menu {
            position: absolute;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            z-index: 1000;
        }
        .dropdown-menu a { color: #dc3545; text-decoration: none; }
        .text-danger { color: #dc3545; }
        .verde { color: #28a745; }
        .vermelho-escuro { background-color: #ffe6e6; }
        .ocultar { display: none; }
        .total-footer { margin: 5px 0; }
        .esc-mobile { display: table-cell; } /* Visível em desktop */

        /* Media Query para Mobile (max-width: 768px) */
        @media (max-width: 768px) {
            .esc-mobile {
                display: none; /* Esconde colunas indesejadas em mobile */
            }
            .table th, .table td {
                padding: 6px;
                font-size: 10px;
            }
            .btn {
                padding: 8px 10px;
                font-size: 10px;
            }
            .btn i {
                font-size: 10px;
            }
            .dropdown-menu {
                margin-left: 0 !important;
                min-width: 100px;
                font-size: 10px;
            }
            .mr-2 { margin-right: 5px; }
            img { width: 30px; height: 30px; }
            #mensagem-excluir, .total-footer {
                font-size: 10px;
            }
            .table th:nth-child(1), .table td:nth-child(1) { width: 30%; } /* Produto */
            .table th:nth-child(2), .table td:nth-child(2) { width: 30%; } /* Valor */
            .table th:nth-child(3), .table td:nth-child(3) { width: 50%; } /* Ações */

			
			/* Oculta o elemento "Mostrar" em telas menores que 768px */
			.dataTables_length {
				display: none;
			}

			.notification_desc2{
				width: 80px;
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
	<th>Cliente</th>	
	<th class="">Texto</th> 		
	<th style='width: 200px;'>Ações</th>
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
<td class="">{$textoF}</td>
<td>
		<a href="#" class="btn btn-primary btn-xs" onclick="editar('{$id}','{$nome}', '{$texto}', '{$foto}')" title="Editar Dados"><i class="fe fe-edit"></i></a>

		<a href="#" class="btn btn-info btn-xs" onclick="mostrar('{$nome}', '{$texto}', '{$foto}')" title="Ver Dados"><i class="fe fe-search"></i></a>



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