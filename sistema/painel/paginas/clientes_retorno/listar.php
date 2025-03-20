<?php 
require_once("../../../conexao.php");
$tabela = 'clientes';
$data_atual = date('Y-m-d');

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
            .table th:nth-child(1), .table td:nth-child(1) { width: 40%; } /* Produto */
            .table th:nth-child(3), .table td:nth-child(3) { width: 10%; } /* Valor */
            .table th:nth-child(5), .table td:nth-child(5) { width: 10%; } /* Ações */
            .table th:nth-child(6), .table td:nth-child(6) { width: 60%; } /* Ações */

			
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

$query = $pdo->query("SELECT * FROM $tabela where alertado != 'Sim' and data_retorno < curDate() and id_conta = '$id_conta' ORDER BY data_retorno asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="tabela">
	<thead> 
	<tr> 
	<th>Nome</th>	
	<th class="esc">Telefone</th> 		
	<th class="">Retorno</th> 
	<th class="esc">Último Serviço</th>
	<th class="">Dias</th> 	
	<th>Ações</th>
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

	$data_cadF = implode('/', array_reverse(@explode('-', $data_cad)));
	$data_nascF = implode('/', array_reverse(@explode('-', $data_nasc)));
	$data_retornoF = implode('/', array_reverse(@explode('-', $data_retorno)));
	
	if($data_nascF == '00/00/0000' || $data_nascF == null){
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

	if($data_retorno != "" and @strtotime($data_retorno) <  @strtotime($data_atual)){
		$classe_retorno = 'text-danger';
	}else{
		$classe_retorno = '';
	}


//diferença de dias
$data_inicio = new DateTime($data_retorno);
$data_fim = new DateTime($data_atual);
$intvl = $data_inicio->diff($data_fim);

//echo $intvl->y . " year, " . $intvl->m." months and ".$intvl->d." day"; 
//echo "\n";
// Total amount of days
//echo $intvl->days . " days ";	
$dias = $intvl->days;

$url_agendamento = $url.'agendamento';


echo <<<HTML
<tr class="">
<td>{$nome}</td>
<td class="esc">{$telefone}</td>
<td class="{$classe_retorno}">{$data_retornoF}</td>
<td class="esc">{$nome_servico}</td>
<td class="">{$dias}</td>
<td>
		
		<a href="#" class="btn btn-info btn-xs" onclick="mostrar('{$nome}', '{$telefone}', '{$cartoes}', '{$data_cadF}', '{$data_nascF}', '{$endereco}', '{$data_retornoF}', '{$nome_servico}')" title="Ver Dados"><i class="fe fe-search"></i></a>

		<a onclick="alertar('{$id}')" class="btn btn-success btn-xs" href="http://api.whatsapp.com/send?1=pt_BR&phone=$whats&text=Olá $nome, já faz $dias dias que você não vem dar um trato no visual, caso queira fazer um novo agendamento é só acessar nosso site $url_agendamento, será um prazer atendê-lo novamente!!" target="_blank" title="Abrir Whatsapp"><i class="fab fa-whatsapp " style='font-size: 18px;' ></i></a>

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
	function editar(id, nome, telefone, endereco, data_nasc, cartoes){
		$('#id').val(id);
		$('#nome').val(nome);		
		$('#telefone').val(telefone);		
		$('#endereco').val(endereco);
		$('#data_nasc').val(data_nasc);
		$('#cartao').val(cartoes);

		
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
	}
</script>



<script type="text/javascript">
	function mostrar(nome, telefone, cartoes, data_cad, data_nasc, endereco, retorno, servico){

		$('#nome_dados').text(nome);		
		$('#data_cad_dados').text(data_cad);
		$('#data_nasc_dados').text(data_nasc);
		$('#cartoes_dados').text(cartoes);
		$('#telefone_dados').text(telefone);
		$('#endereco_dados').text(endereco);
		$('#retorno_dados').text(retorno);		
		$('#servico_dados').text(servico);

		$('#modalDados').modal('show');
	}
</script>



<script type="text/javascript">
	function alertar(id){
		
    $.ajax({
        url: 'paginas/' + pag + "/alertar.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success:function(mensagem){
             if (mensagem.trim() === "Salvo com Sucesso") {
             	
                //$('#btn-fechar-horarios').click();
                listar(); 
            } 
        }
    });
	}
</script>