<?php 
ini_set('display_errors', 1); // Habilita a exibição de erros
ini_set('display_startup_errors', 1); // Habilita erros de inicialização
error_reporting(E_ALL); // Reporta todos os tipos de erros PHP
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Opcional: Se usar MySQLi, reporta erros
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
$facebook = @$_POST['facebook_sistema'];
$tiktok = @$_POST['tiktok_sistema'];
$x = @$_POST['x_sistema'];
$tipo_comissao = @$_POST['tipo_comissao'];
$quantidade_cartoes = @$_POST['quantidade_cartoes'];
$texto_fidelidade = @$_POST['texto_fidelidade'];
$msg_agendamento = @$_POST['msg_agendamento'];
$cnpj_sistema = @$_POST['cnpj_sistema'];
$agendamento_dias = @$_POST['agendamento_dias'];
$minutos_aviso = @$_POST['minutos_aviso'];
$taxa_sistema = @$_POST['taxa_sistema'];
$porc_servico = @$_POST['porc_servico'];
$api_mp = @$_POST['api_mp'];
$token_mp = @$_POST['token_mp'];
$key_mp = @$_POST['key_mp'];
$encaixe = @$_POST['encaixe'];
$satisfacao = @$_POST['satisfacao'];
$dados_pagamento = @$_POST['dados_pagamento'];

if($minutos_aviso == ""){
	$minutos_aviso = 0;
}

$query = $pdo->prepare("UPDATE config SET nome = :nome, email = :email, api = :api, telefone_whatsapp = :whatsapp, telefone_fixo = :telefone_fixo, endereco = :endereco, instagram = :instagram, facebook = :facebook, tiktok = :tiktok, x = :x, tipo_comissao = '$tipo_comissao', quantidade_cartoes = '$quantidade_cartoes', texto_fidelidade = :texto_fidelidade, msg_agendamento = :msg_agendamento, cnpj = :cnpj, agendamento_dias = '$agendamento_dias', token = :token, minutos_aviso = '$minutos_aviso', instancia = :instancia, taxa_sistema = :taxa_sistema, dados_pagamento = :dados_pagamento, porc_servico = :porc_servico, pgto_api = :pgto_api, token_mp = :token_mp, key_mp = :key_mp, encaixe = :encaixe, satisfacao = :satisfacao where id = '$id_conta'");
$query->bindValue(":api", "$api");
$query->bindValue(":nome", "$nome");
$query->bindValue(":email", "$email");
$query->bindValue(":whatsapp", "$whatsapp");
$query->bindValue(":telefone_fixo", "$fixo");
$query->bindValue(":endereco", "$endereco");
$query->bindValue(":instagram", "$instagram");
$query->bindValue(":facebook", "$facebook");
$query->bindValue(":tiktok", "$tiktok");
$query->bindValue(":x", "$x");
$query->bindValue(":texto_fidelidade", "$texto_fidelidade");
$query->bindValue(":msg_agendamento", "$msg_agendamento");
$query->bindValue(":cnpj", "$cnpj_sistema");
$query->bindValue(":token", "$token");
$query->bindValue(":instancia", "$instancia");
$query->bindValue(":taxa_sistema", "$taxa_sistema");
$query->bindValue(":porc_servico", "$porc_servico");
$query->bindValue(":pgto_api", "$api_mp");
$query->bindValue(":token_mp", "$token_mp");
$query->bindValue(":key_mp", "$key_mp");
$query->bindValue(":encaixe", "$encaixe");
$query->bindValue(":satisfacao", "$satisfacao");
$query->bindValue(":dados_pagamento", "$dados_pagamento");

$query->execute();

// Conexão ao segundo banco de dados
try {
    $url = "https://{$_SERVER['HTTP_HOST']}/";
    $url2 = explode("//", $url);

    $host = ($url2[1] == 'localhost/') ? 'localhost' : 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
    $db = 'gestao_sistemas';
    $user = ($url2[1] == 'localhost/') ? 'root' : 'skysee';
    $pass = ($url2[1] == 'localhost/') ? '' : '9vtYvJly8PK6zHahjPUg';

    $pdo2 = new PDO("mysql:dbname=$db;host=$host;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Atualiza a tabela clientes na gestão
$query_clientes = $pdo2->prepare("UPDATE clientes SET instituicao = :instituicao, email = :email, telefone = :telefone, whatsapp = :whatsapp, endereco = :endereco WHERE id_conta = :id_conta");
$query_clientes->bindValue(":instituicao", "$nome");
$query_clientes->bindValue(":email", "$email");
$query_clientes->bindValue(":whatsapp", "$whatsapp");
$query_clientes->bindValue(":telefone", "$fixo");
$query_clientes->bindValue(":endereco", "$endereco");
$query_clientes->bindValue(":id_conta", "$id_conta");
$query_clientes->execute();


echo 'Editado com Sucesso';
 ?>