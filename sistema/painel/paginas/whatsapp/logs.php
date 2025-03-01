<?php 
require_once("../../../conexao.php");
$tabela = 'logs';
$filtro = $_POST['filtro'];


$query = $pdo->query("SELECT * FROM $tabela" . ($filtro == "sucesso" ? " WHERE codigo_status = 200" : ($filtro == "erro" ? " WHERE id_conta = '$id_conta' and codigo_status != 200" : "")));

 

$res = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<small>
	<table class="table table-hover" id="tabela2">
    	<thead> 
        	<tr> 
            	<th>ID</th>	
            	<th>Mensagem</th>
            	<th>Destinatario</th> 
            	<th>data</th>
            	<th>tipo</th>
            	<th>resposta</th>
            	<th>Status</th>
            	
    	    </tr> 
    	</thead> 
	<tbody>	
	
<?php foreach($res as $row => $list): ?>
<tr>
    <td class="esc"><?= $list['id'];?></td>
    <td class="esc"><?= $list['mensagem'];?></td>
    <td class="esc"><?= strpos($list['destinatario'], '[') !== false || strpos($list['destinatario'], ']') !== false ? "Multiplos Contatos" : $list['destinatario'];?></td>
    <td class="esc"><?= date('d/m/Y H:i:s', strtotime($list['created_at'])); ?></td>
	<td class="esc"><?= $list['tipo'];?></td>
	<td class="esc"><?= $list['mensagem_status'] ?? '';?> </td>
	<td class="esc"><?= $Status = $list['codigo_status'] == 200 ? "sucesso" : "erro " . "(".$list['codigo_status'].")"?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</small>

<script type="text/javascript">
	$(document).ready( function () {
    $('#tabela2').DataTable({
    		"ordering": false,
			"stateSave": true
    	});
    $('#tabela_filter label input').focus();
} );
</script>




