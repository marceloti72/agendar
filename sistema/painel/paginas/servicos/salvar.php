<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'servicos';

$id = $_POST['id'];
$nome = $_POST['nome'];
$valor = $_POST['valor'];
$valor = str_replace(',', '.', $valor);
// $comissao = $_POST['comissao'];
// $comissao = str_replace(',', '.', $comissao);
// $comissao = str_replace('%', '', $comissao);
$tempo = $_POST['tempo'];

$dias_retorno = $_POST['dias_retorno'];
//$categoria = $_POST['categoria'];

// if($categoria == 0){
// 	echo 'Cadastre uma Categoria de Serviços para o Serviço';
// 	exit();
// }

//validar nome
$query = $pdo->query("SELECT * from $tabela where nome = '$nome' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Nome já Cadastrado, escolha outro!!';
	exit();
}


//validar troca da foto
// $query = $pdo->query("SELECT * FROM $tabela where id = '$id' and id_conta = '$id_conta'");
// $res = $query->fetchAll(PDO::FETCH_ASSOC);
// $total_reg = @count($res);
// if($total_reg > 0){
// 	$foto = $res[0]['foto'];
// }else{
// 	$foto = 'sem-foto.jpg';
// }


//SCRIPT PARA SUBIR FOTO NO SERVIDOR
// $nome_img = date('d-m-Y H:i:s') .'-'.@$_FILES['foto']['name'];
// $nome_img = preg_replace('/[ :]+/' , '-' , $nome_img);

// $caminho = '../../img/servicos/' .$nome_img;

// $imagem_temp = @$_FILES['foto']['tmp_name']; 

// if(@$_FILES['foto']['name'] != ""){
// 	$ext = pathinfo($nome_img, PATHINFO_EXTENSION);   
// 	if($ext == 'png' or $ext == 'jpg' or $ext == 'jpeg' or $ext == 'gif'){ 
	
// 			//EXCLUO A FOTO ANTERIOR
// 			if($foto != "sem-foto.jpg"){
// 				@unlink('../../img/servicos/'.$foto);
// 			}

// 			$foto = $nome_img;
		
// 		move_uploaded_file($imagem_temp, $caminho);
// 	}else{
// 		echo 'Extensão de Imagem não permitida!';
// 		exit();
// 	}
// }




if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, valor = :valor, dias_retorno = '$dias_retorno', ativo = 'Sim', tempo = :tempo, id_conta = '$id_conta'");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, valor = :valor, dias_retorno = '$dias_retorno', tempo = :tempo WHERE id = '$id' and id_conta = '$id_conta'");
}

$query->bindValue(":nome", "$nome");
$query->bindValue(":valor", "$valor");
$query->bindValue(":tempo", "$tempo");
$query->execute();

echo 'Salvo com Sucesso';
 ?>