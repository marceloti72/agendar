<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../sistema/conexao.php");
$url_img = $_POST['url_img'];

$tabela = 'pagar';
$data_hoje = date('Y-m-d');

$dataInicial = @$_POST['dataInicial'];
$dataFinal = @$_POST['dataFinal'];
$status = '%'.@$_POST['status'].'%';
$funcionario = @$_POST['id_usuario'];


$query2 = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_func2 = $res2[0]['nome'];
		}else{
			$nome_func2 = 'Sem Referência!';
		}

$total_pago = 0;
$total_a_pagar = 0;

$query = $pdo->query("SELECT * FROM $tabela where data_lanc >= '$dataInicial' and data_lanc <= '$dataFinal' and pago LIKE '$status' and funcionario = '$funcionario' and tipo = 'Comissão' and id_conta = '$id_conta' ORDER BY pago asc, data_venc asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

for($i=0; $i < $total_reg; $i++){
	$id = $res[$i]['id'];	
	$descricao = $res[$i]['descricao'];
	$tipo = $res[$i]['tipo'];
	$valor = $res[$i]['valor'];
	$data_lanc = $res[$i]['data_lanc'];
	$data_pgto = $res[$i]['data_pgto'];
	$data_venc = $res[$i]['data_venc'];
	$usuario_lanc = $res[$i]['usuario_lanc'];
	$usuario_baixa = $res[$i]['usuario_baixa'];
	$foto = $res[$i]['foto'];
	$pessoa = $res[$i]['pessoa'];
	$funcionario = $res[$i]['funcionario'];
	$cliente = $res[$i]['cliente'];
	
	$pago = $res[$i]['pago'];
	$servico = $res[$i]['servico'];
	
	$valorF = number_format($valor, 2, ',', '.');
	$data_lancF = implode('/', array_reverse(explode('-', $data_lanc)));
	$data_pgtoF = implode('/', array_reverse(explode('-', $data_pgto)));
	$data_vencF = implode('/', array_reverse(explode('-', $data_venc)));
	

		$query2 = $pdo->query("SELECT * FROM clientes where id = '$pessoa' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_pessoa = $res2[0]['nome'];
			$telefone_pessoa = $res2[0]['telefone'];
		}else{
			$nome_pessoa = 'Nenhum!';
			$telefone_pessoa = 'Nenhum';
		}


		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_baixa' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_usuario_pgto = $res2[0]['nome'];
		}else{
			$nome_usuario_pgto = 'Nenhum!';
		}



		$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_cliente = $res2[0]['nome'];
		}else{
			$nome_cliente = 'Nenhum!';
		}



		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_lanc' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_usuario_lanc = $res2[0]['nome'];
		}else{
			$nome_usuario_lanc = 'Sem Referência!';
		}



		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$funcionario' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_func = $res2[0]['nome'];
		}else{
			$nome_func = 'Sem Referência!';
		}


		$query2 = $pdo->query("SELECT * FROM servicos where id = '$servico' and id_conta = '$id_conta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_serv = $res2[0]['nome'];
		}else{
			$nome_serv = 'Sem Referência!';
		}



		if($data_pgto == '0000-00-00' || $data_pgto == null){
			$classe_alerta = 'red';
			$data_pgtoF = 'Pendente';
			$visivel = '';
			$total_a_pagar += $valor;
			$japago = 'ocultar';
			$oc = '';
		}else{
			$classe_alerta = 'green';
			$visivel = 'ocultar';
			$total_pago += $valor;
			$japago = '';
			$oc = 'none';
		}


			//extensão do arquivo
$ext = pathinfo($foto, PATHINFO_EXTENSION);
if($ext == 'pdf'){
	$tumb_arquivo = 'pdf.png';
}else if($ext == 'rar' || $ext == 'zip'){
	$tumb_arquivo = 'rar.png';
}else{
	$tumb_arquivo = $foto;
}
		

if($data_venc < $data_hoje and $pago != 'Sim'){
	$classe_debito = 'red';
}else{
	$classe_debito = '';
}




$whats = '55'.preg_replace('/[ ()-]+/' , '' , $telefone_pessoa);

		 echo '<li>';
             echo '<a href="#" class="item-link item-content" onclick="editarComissoes('.$id.', \''.$descricao.'\', \''.$valorF.'\', \''.$nome_cliente.'\', \''.$data_vencF.'\', \''.$data_pgtoF.'\', \''.$tumb_arquivo.'\', \''.$nome_usuario_lanc.'\', \''.$nome_usuario_pgto.'\', \''.$foto.'\', \''.$ext.'\', \''.$oc.'\', \''.$telefone_pessoa.'\', \''.$whats.'\', \''.$nome_func.'\', \''.$nome_serv.'\', \''.$data_lancF.'\')">'; 
                        
              echo ' <div class="item-inner">';
                echo ' <div class="item-title" style="font-size:11px;">';
                  echo ' <div class="item-header " style="font-size:9px"><i class="mdi mdi-square" style="color:'.$classe_alerta.'"></i> '.$nome_serv.'</div>R$'.$valorF.' ('.$nome_cliente.')';
                  echo'<div class="item-footer" style="font-size:9px">Vencimento: '.$data_vencF.'</div>';
                 echo '</div>';
                
               echo '</div>';
             echo '</a>';
           echo '</li>';

	}
	$total_pagoF = number_format($total_pago, 2, ',', '.');
$total_a_pagarF = number_format($total_a_pagar, 2, ',', '.');

	echo '<div align="center" style="margin-top:10px"><small><small><span>Total Recebido: <span  style="margin-right:15px; color:green">R$ '.$total_pagoF.'</span> 
<span>Total à Receber: <span style="color:red">R$ '.$total_a_pagarF.'</span></small></small></div>';
}else{
	echo '<br><small><small><div align="center">Não encontramos nenhum registro!</div></small></small>';
}

?>

