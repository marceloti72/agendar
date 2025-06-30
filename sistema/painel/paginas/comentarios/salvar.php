<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'comentarios';

$id = $_POST['id'];
$nome = $_POST['nome'];
$texto = $_POST['texto'];
$cliente = @$_POST['cliente'];

if($cliente == 1){
	$ativo = 'Não';
}else{
	$ativo = 'Sim';
}

//validar troca da foto
$query = $pdo->query("SELECT * FROM $tabela where id = '$id' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	$foto = $res[0]['foto'];
}else{
	$foto = 'sem-foto.jpg';
}


//SCRIPT PARA SUBIR FOTO NO SERVIDOR
$nome_img = date('d-m-Y H:i:s') .'-'.@$_FILES['foto']['name'];
$nome_img = preg_replace('/[ :]+/' , '-' , $nome_img);

$caminho = '../../img/comentarios/' .$nome_img;

$imagem_temp = @$_FILES['foto']['tmp_name']; 

if(@$_FILES['foto']['name'] != ""){
	$ext = pathinfo($nome_img, PATHINFO_EXTENSION);   
	if($ext == 'png' or $ext == 'jpg' or $ext == 'jpeg' or $ext == 'gif'){ 
	
			//EXCLUO A FOTO ANTERIOR
			if($foto != "sem-foto.jpg"){
				@unlink('../../img/comentarios/'.$foto);
			}

			$foto = $nome_img;
		
		move_uploaded_file($imagem_temp, $caminho);
	}else{
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}




if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, texto = :texto, ativo = '$ativo', foto = '$foto', id_conta = '$id_conta'");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, texto = :texto, foto = '$foto' WHERE id = '$id' and id_conta = '$id_conta'");
}

$query->bindValue(":nome", "$nome");
$query->bindValue(":texto", "$texto");
$query->execute();

if ($api == 'Sim') {    

    $telefone = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);    
    $mensagem = '*Depoimento recebido* ✔%0A%0A';
    $mensagem .= 'Cliente: ' . $nome . '%0A';
    $mensagem .= 'Depoimento: ' . $texto . '%0A%0A';
    $mensagem .= 'Ja esta disponível em seu APP para ativar.%0A';    

     require('../ajax/api-texto.php');    
}

echo 'Salvo com Sucesso';
 ?>