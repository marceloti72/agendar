<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("../../../conexao.php");
$tabela = 'usuarios';

$id = $_POST['id'];
$nome = $_POST['nome'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$cpf = $_POST['cpf'];
$cargo = $_POST['cargo'];
$endereco = $_POST['endereco'];
$atendimento = $_POST['atendimento'];
$tipo_chave = $_POST['tipo_chave'];
$chave_pix = $_POST['chave_pix'];
$senha = '123';
$intervalo = $_POST['intervalo'];
$comissao = $_POST['comissao'];

if($cargo == ""){
	echo 'Cadastre um Cargo para o Profissional';
	exit();
}
if($cpf == ""){
	echo 'CPF é obrigatório!';
	exit();
}



// if($atendimento == "Não"){
// 	$ativo = 'Não';
// }else{
// 	$ativo = 'Sim';
// }

//validar email
$query = $pdo->query("SELECT * from $tabela where email = '$email' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Email já Cadastrado, escolha outro!!';
	exit();
}


//validar cpf
$query = $pdo->query("SELECT * from $tabela where cpf = '$cpf' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'CPF já Cadastrado, escolha outro!!';
	exit();
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

$caminho = '../../img/perfil/' .$nome_img;

$imagem_temp = @$_FILES['foto']['tmp_name']; 

if(@$_FILES['foto']['name'] != ""){
	$ext = pathinfo($nome_img, PATHINFO_EXTENSION);   
	if($ext == 'png' or $ext == 'jpg' or $ext == 'jpeg' or $ext == 'gif'){ 
	
			//EXCLUO A FOTO ANTERIOR
			if($foto != "sem-foto.jpg"){
				@unlink('../../img/perfil/'.$foto);
			}

			$foto = $nome_img;
		
		move_uploaded_file($imagem_temp, $caminho);
	}else{
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}




if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, email = :email, cpf = :cpf, senha = '$senha', nivel = '$cargo', data = curDate(), ativo = :ativo, telefone = :telefone, endereco = :endereco, foto = '$foto', atendimento = '$atendimento', tipo_chave = '$tipo_chave', chave_pix = :chave_pix, intervalo = '$intervalo', comissao = '$comissao', id_conta = '$id_conta'");

	$query->bindValue(":nome", "$nome");
	$query->bindValue(":email", "$email");
	$query->bindValue(":cpf", "$cpf");
	$query->bindValue(":telefone", "$telefone");
	$query->bindValue(":endereco", "$endereco");
	$query->bindValue(":chave_pix", "$chave_pix");
	$query->bindValue(":ativo", "Sim");
	$query->execute();

	// $ult_id = $pdo->lastInsertId();

	// if($atendimento == 'Sim'){

	// 	$pdo->query("INSERT INTO usuarios_permissoes SET permissao = '43', usuario = '$ult_id', id_conta = '$id_conta'");
	// 	$pdo->query("INSERT INTO usuarios_permissoes SET permissao = '44', usuario = '$ult_id', id_conta = '$id_conta'");
	// 	$pdo->query("INSERT INTO usuarios_permissoes SET permissao = '45', usuario = '$ult_id', id_conta = '$id_conta'");
	// 	$pdo->query("INSERT INTO usuarios_permissoes SET permissao = '46', usuario = '$ult_id', id_conta = '$id_conta'");

    // }

}else{
	$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, email = :email, cpf = :cpf, nivel = '$cargo', telefone = :telefone, endereco = :endereco, foto = '$foto', atendimento = '$atendimento', tipo_chave = '$tipo_chave', chave_pix = :chave_pix, intervalo = '$intervalo', comissao = '$comissao' WHERE id = '$id' and id_conta = '$id_conta'");

	$query->bindValue(":nome", "$nome");
	$query->bindValue(":email", "$email");
	$query->bindValue(":cpf", "$cpf");
	$query->bindValue(":telefone", "$telefone");
	$query->bindValue(":endereco", "$endereco");
	$query->bindValue(":chave_pix", "$chave_pix");
	$query->execute();
}


echo 'Salvo com Sucesso';
 ?>