<?php 
require_once("../../../conexao.php");

$id_usuario = $_POST['id'];

$pdo->query("DELETE FROM usuarios_permissoes where usuario = '$id_usuario' and id_conta = '$id_conta'");

$query = $pdo->query("SELECT * FROM acessos where id_conta = '$id_conta' order by id asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	for($i=0; $i < $total_reg; $i++){
		foreach ($res[$i] as $key => $value){}
			$nome = $res[$i]['nome'];
		$chave = $res[$i]['chave'];
		$id = $res[$i]['id'];

		$query = $pdo->query("INSERT INTO usuarios_permissoes SET permissao = '$id', usuario = '$id_usuario', id_conta = '$id_conta'");

	}
}

?>