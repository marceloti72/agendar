<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../sistema/conexao.php");
$tabela = 'usuarios';
$nome = $_POST['nome'];
$email = $_POST['email'];
$senha = $_POST['senha'];
$nivel = $_POST['nivel'];
$foto = $_POST['nome_foto'];
$ativo = @$_POST['ativo'];
$senha_crip = md5($senha);
$id = $_POST['id'];

if($ativo == ""){
	$ativo = 'Não';
}

if($nome == ""){
	echo 'Preencha o Campo Nome!';
	exit();
}

if($email == ""){
	echo 'Preencha o Campo Email!';
	exit();
}

if($senha == ""){
	echo 'Preencha o Campo Senha!';
	exit();
}



//validar email
$query = $pdo->query("SELECT * from $tabela where email = '$email' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Email já Cadastrado, escolha outro!!';
	exit();
}




if($id == ""){

$query = $pdo->prepare("INSERT INTO usuarios SET nome = :nome, email = :email, senha = :senha, senha_crip = '$senha_crip', nivel = '$nivel', data = curDate(), foto = '$foto', ativo = '$ativo', id_conta = '$id_conta'");
$query->bindValue(":nome", "$nome");
$query->bindValue(":email", "$email");
$query->bindValue(":senha", "$senha");
$query->execute();

}else{

//tratamento para trocar a foto e apagar a antiga
$query = $pdo->query("SELECT * FROM usuarios where id = '$id' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$foto_antiga = $res[0]['foto'];

if($foto_antiga != "sem-foto.jpg" and $foto != $foto_antiga){	
	unlink("../../sistema/painel/img/perfil/".$foto_antiga);
}


$query = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, senha_crip = '$senha_crip', nivel = '$nivel', foto = '$foto', ativo = '$ativo' WHERE id = '$id' and id_conta = '$id_conta'");
$query->bindValue(":nome", "$nome");
$query->bindValue(":email", "$email");
$query->bindValue(":senha", "$senha");
$query->execute();	

}
echo 'Salvo';

/*
//enviar notificação
$mensagem_not = 'Usuário '.$nome;
$titulo_not = 'Novo Usuário Cadastrado!';
require("../not.php");
*/

 ?>