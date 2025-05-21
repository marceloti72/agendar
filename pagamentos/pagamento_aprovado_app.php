
<?php
$id_pg = @$_GET['id_pg'];
$id_conta = @$_GET['id_conta'];

$query = $pdo->query("SELECT * FROM config WHERE id = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$empresa = @$res[0]['nome'];
$telefone_empresa = @$res[0]['telefone_whatsapp'];
$token = @$res[0]['token'];
$instancia = @$res[0]['instancia'];
$pgto_api = @$res[0]['pgto_api'];
$api = @$res[0]['api'];


$query = $pdo->query("SELECT * FROM receber WHERE ref_pix = '$ref_pix'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);

$total_reg = @count($res);
$cliente = $res[0]['cliente'];
$pessoa = $res[0]['pessoa'];
$data = $res[0]['data_venc'];
$data_lanc = $res[0]['data_lanc'];
$usuario = $res[0]['usuario'];
$hash = $res[0]['hash'];
$ref_pix = $res[0]['ref_pix'];
$id_conta = $res[0]['id_conta'];
$forma_pgto = $res[0]['pgto'];
$frequencia = $res[0]['frequencia'];
$descricao = $res[0]['descricao'];
$valor = $res[0]['valor'];
$id_pg = $res[0]['id'];

if ($id_pg != "") {    
    $pdo->query("UPDATE receber set pago = 'Sim', data_pgto = curDate() where id = '$id_pg'");
    
}

