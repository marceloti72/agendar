<?php
require_once("../../../conexao.php");
$tabela = 'pagar';
@session_start();
$id_conta = $_SESSION['id_conta'];
$id_usuario = $_SESSION['id_usuario'];

$id = $_POST['id'];
$descricao = $_POST['descricao'];
$valor = $_POST['valor'];
$valor = str_replace(',', '.', $valor);
$pessoa = $_POST['pessoa'];
$data_venc = $_POST['data_venc'];
$data_pgto = $_POST['data_pgto'];
$funcionario = $_POST['funcionario'];

if ($descricao == "") {
	echo 'Insira uma descrição!';
	exit();
}

if ($funcionario == "") {
	$funcionario = 0;
}

if ($pessoa == "") {
	$pessoa = 0;
}


if ($data_pgto != '') {
	$usuario_pgto = $id_usuario;
	$pago = 'Sim';
	$pgto = " ,data_pgto = '$data_pgto'";
} else {
	$usuario_pgto = 0;
	$pago = 'Não';
	$pgto = "";
}


//validar troca da foto
$query = $pdo->query("SELECT * FROM $tabela where id = '$id' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
	$foto = $res[0]['foto'];
} else {
	$foto = 'sem-foto.jpg';
}


//SCRIPT PARA SUBIR FOTO NO SERVIDOR
$nome_img = date('d-m-Y H:i:s') . '-' . @$_FILES['foto']['name'];
$nome_img = preg_replace('/[ :]+/', '-', $nome_img);

$caminho = '../../img/contas/' . $nome_img;

$imagem_temp = @$_FILES['foto']['tmp_name'];

if (@$_FILES['foto']['name'] != "") {
	$ext = pathinfo($nome_img, PATHINFO_EXTENSION);
	if ($ext == 'png' or $ext == 'jpg' or $ext == 'jpeg' or $ext == 'gif' or $ext == 'pdf' or $ext == 'rar' or $ext == 'zip') {

		//EXCLUO A FOTO ANTERIOR
		if ($foto != "sem-foto.jpg") {
			@unlink('../../img/contas/' . $foto);
		}

		$foto = $nome_img;

		move_uploaded_file($imagem_temp, $caminho);
	} else {
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}



if ($funcionario == "") {
	$funcionario = 0;
}

if ($id == "") {
	$query = $pdo->prepare("INSERT INTO $tabela SET descricao = :descricao, tipo = 'Conta', valor = :valor, data_lanc = curDate(), data_venc = '$data_venc',  usuario_lanc = '$id_usuario', usuario_baixa = '$usuario_pgto', foto = '$foto', pessoa = '$pessoa', pago = '$pago', funcionario = '$funcionario', id_conta = '$id_conta' $pgto");
} else {
	$query = $pdo->prepare("UPDATE $tabela SET descricao = :descricao, valor = :valor, data_venc = '$data_venc', data_pgto = '$data_pgto', foto = '$foto', pessoa = '$pessoa', funcionario = '$funcionario' WHERE id = '$id' and id_conta = '$id_conta'");
}

$query->bindValue(":descricao", "$descricao");
$query->bindValue(":valor", "$valor");
$query->execute();

echo 'Salvo com Sucesso';
