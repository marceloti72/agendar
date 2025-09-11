<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'textos_index';

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

        #tabela {
			font-size: 12px; /* O padrão é 16px. Experimente valores como 13px ou 12px. */
		}

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
	<th>Título</th>	
	<th>Descrição</th>		
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];
	$titulo = $res[$i]['titulo'];
	$descricao = $res[$i]['descricao'];

	$descricaoF = mb_strimwidth($descricao, 0, 100, "...");
	
		
	
echo <<<HTML
<tr class="">
<td>{$titulo}</td>
<td>{$descricaoF}</td>
<td>
		<a href="#" class="btn btn-primary btn-xs" onclick="editar('{$id}','{$titulo}','{$descricao}')" title="Editar Dados"><i class="fe fe-edit"></i></a>

		
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
	function editar(id, titulo, descricao){
		$('#id').val(id);
		$('#titulo').val(titulo);
		$('#descricao').val(descricao);
		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
	}

	function limparCampos(){
		$('#titulo').val('');
		$('#descricao').val('');
		$('#id').val('');
	}
</script>