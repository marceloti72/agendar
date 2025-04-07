<?php 
require_once('../conexao.php');
@session_start();
$id_conta = $_SESSION['id_conta'];


$texto_rodape = @$_POST['texto_rodape'];
$texto_sobre = @$_POST['texto_sobre'];
$mapa = @$_POST['mapa'];
$url_video = @$_POST['url_video'];
$posicao_video = @$_POST['posicao_video'];
$agendamentos = @$_POST['agendamentos2'];
$produtos = @$_POST['produtos2'];
$servicos = @$_POST['servicos2'];
$assinaturas = @$_POST['assinaturas2'];
$depoimentos = @$_POST['depoimentos2'];
$carrossel = @$_POST['carrossel'];


//SCRIPT PARA SUBIR FOTO NO SERVIDOR
$caminho = '../img/logo'.$id_conta.'.png';
$imagem_temp = @$_FILES['foto-logo']['tmp_name']; 
if(@$_FILES['foto-logo']['name'] != ""){
	$ext = pathinfo(@$_FILES['foto-logo']['name'], PATHINFO_EXTENSION);   
	if($ext == 'png'){ 
		move_uploaded_file($imagem_temp, $caminho);
	}else{
		echo 'Extensão da imagem para a Logo é somente *PNG';
		exit();
	}

}


$caminho = '../img/icon'.$id_conta.'.png';
$imagem_temp = @$_FILES['foto-icone']['tmp_name']; 
if(@$_FILES['foto-icone']['name'] != ""){
	$ext = pathinfo(@$_FILES['foto-icone']['name'], PATHINFO_EXTENSION);   
	if($ext == 'png'){ 
		move_uploaded_file($imagem_temp, $caminho);
	}else{
		echo 'Extensão da imagem para a ícone é somente *ICO';
		exit();
	}
}



$caminho = '../img/logo_rel'.$id_conta.'.jpg';
$imagem_temp = @$_FILES['foto-logo-rel']['tmp_name']; 
if(@$_FILES['foto-logo-rel']['name'] != ""){
	$ext = pathinfo(@$_FILES['foto-logo-rel']['name'], PATHINFO_EXTENSION);   
	if($ext == 'jpg'){ 
		move_uploaded_file($imagem_temp, $caminho);
	}else{
		echo 'Extensão da imagem para o Relatório é somente *Jpg';
		exit();
	}
}



$caminho = '../../images/banner'.$id_conta.'.jpg';
$imagem_temp = @$_FILES['foto-banner-index']['tmp_name']; 
if(@$_FILES['foto-banner-index']['name'] != ""){
	$ext = pathinfo(@$_FILES['foto-banner-index']['name'], PATHINFO_EXTENSION);   
	if($ext == 'jpg'){ 	
		move_uploaded_file($imagem_temp, $caminho);
	}else{
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}



$caminho = '../../images/foto-sobre'.$id_conta.'.jpg';
$imagem_temp = @$_FILES['foto-sobre']['tmp_name']; 
if(@$_FILES['foto-sobre']['name'] != ""){
	$ext = pathinfo(@$_FILES['foto-sobre']['name'], PATHINFO_EXTENSION);   
	if($ext == 'jpg'){  
		move_uploaded_file($imagem_temp, $caminho);
	}else{
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}



$caminho = '../../images/favicon'.$id_conta.'.png';
$imagem_temp = @$_FILES['foto-icone-site']['tmp_name']; 
if(@$_FILES['foto-icone-site']['name'] != ""){
	$ext = pathinfo(@$_FILES['foto-icone-site']['name'], PATHINFO_EXTENSION);   
	if($ext == 'png'){ 
		move_uploaded_file($imagem_temp, $caminho);
	}else{
		echo 'Extensão da imagem para a ícone é somente *PNG';
		exit();
	}

}


$query = $pdo->prepare("UPDATE config SET texto_rodape = :texto_rodape, texto_sobre = :texto_sobre, mapa = :mapa, url_video = :url_video, agendamentos = :agendamentos, produtos = :produtos, servicos = :servicos, assinaturas =:assinaturas, depoimentos = :depoimentos, carrossel = :carrossel where id = :id_conta");

$query->bindValue(":texto_rodape", "$texto_rodape");
$query->bindValue(":texto_sobre", "$texto_sobre");
$query->bindValue(":mapa", "$mapa");
$query->bindValue(":url_video", "$url_video");
$query->bindValue(":agendamentos", "$agendamentos");
$query->bindValue(":produtos", "$produtos");
$query->bindValue(":servicos", "$servicos");
$query->bindValue(":assinaturas", "$assinaturas");
$query->bindValue(":depoimentos", "$depoimentos");
$query->bindValue(":carrossel", "$carrossel");
$query->bindValue(":id_conta", "$id_conta");

$query->execute();

echo 'Editado com Sucesso';
 ?>