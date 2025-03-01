<?php 
require_once('../conexao.php');
@session_start();
$id_conta = $_SESSION['id_conta'];

$api = @$_POST['api'];
$nome = @$_POST['nome_sistema'];
$email = @$_POST['email_sistema'];
$whatsapp = @$_POST['whatsapp_sistema'];
$fixo = @$_POST['telefone_fixo_sistema'];
$endereco = @$_POST['endereco_sistema'];
$instagram = @$_POST['instagram_sistema'];
$tipo_comissao = @$_POST['tipo_comissao'];
$quantidade_cartoes = @$_POST['quantidade_cartoes'];
$texto_fidelidade = @$_POST['texto_fidelidade'];
$msg_agendamento = @$_POST['msg_agendamento'];
$cnpj_sistema = @$_POST['cnpj_sistema'];
$agendamento_dias = @$_POST['agendamento_dias'];
$minutos_aviso = @$_POST['minutos_aviso'];
$taxa_sistema = @$_POST['taxa_sistema'];
$lanc_comissao = @$_POST['lanc_comissao'];
$porc_servico = @$_POST['porc_servico'];
$api_mp = @$_POST['api_mp'];
$token_mp = @$_POST['token_mp'];
$key_mp = @$_POST['key_mp'];

if($minutos_aviso == ""){
	$minutos_aviso = 0;
}

$query = $pdo->prepare("UPDATE config SET nome = :nome, email = :email, api = :api, telefone_whatsapp = :whatsapp, telefone_fixo = :telefone_fixo, endereco = :endereco, instagram = :instagram, tipo_comissao = '$tipo_comissao', quantidade_cartoes = '$quantidade_cartoes', texto_fidelidade = :texto_fidelidade, msg_agendamento = :msg_agendamento, cnpj = :cnpj, agendamento_dias = '$agendamento_dias', token = :token, minutos_aviso = '$minutos_aviso', instancia = :instancia, taxa_sistema = :taxa_sistema, lanc_comissao = :lanc_comissao, porc_servico = :porc_servico, pgto_api = :pgto_api, token_mp = :token_mp, key_mp = :key_mp where id = '$id_conta'");
$query->bindValue(":api", "$api");
$query->bindValue(":nome", "$nome");
$query->bindValue(":email", "$email");
$query->bindValue(":whatsapp", "$whatsapp");
$query->bindValue(":telefone_fixo", "$fixo");
$query->bindValue(":endereco", "$endereco");
$query->bindValue(":instagram", "$instagram");
$query->bindValue(":texto_fidelidade", "$texto_fidelidade");
$query->bindValue(":msg_agendamento", "$msg_agendamento");
$query->bindValue(":cnpj", "$cnpj_sistema");
$query->bindValue(":token", "$token");
$query->bindValue(":instancia", "$instancia");
$query->bindValue(":taxa_sistema", "$taxa_sistema");
$query->bindValue(":lanc_comissao", "$lanc_comissao");
$query->bindValue(":porc_servico", "$porc_servico");
$query->bindValue(":pgto_api", "$api_mp");
$query->bindValue(":token_mp", "$token_mp");
$query->bindValue(":key_mp", "$key_mp");

$query->execute();

echo 'Editado com Sucesso';
 ?>