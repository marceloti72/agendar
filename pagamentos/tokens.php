<?php 
if(isset($_GET['id_pg'])){
    $id_pg = $_GET['id_pg'];

    $query = $pdo->query("SELECT id_conta FROM receber WHERE id = '$id_pg'");
    $res = $query->fetch(PDO::FETCH_ASSOC);
    $id_conta = $res['id_conta'];

    $query = $pdo->query("SELECT * FROM config WHERE id = '$id_conta'");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $token = @$res[0]['token'];
    $instancia = @$res[0]['instancia'];
    $username = @$res[0]['username'];
    }
if(isset($_GET['id_conta'])){
    $id_conta = $_GET['id_conta'];

    $query = $pdo->query("SELECT * FROM config WHERE id = '$id_conta'");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $token = @$res[0]['token'];
    $instancia = @$res[0]['instancia'];
    $username = @$res[0]['username'];
}

$access_token = $token;
$public_key     = $instancia;
// $access_token = 'APP_USR-5194938746509270-070420-5f8c4f8a406cfebf91215923b06a4fa1-1034833440';
// $public_key     = 'APP_USR-9d70c2bb-8d81-473c-8c06-cb48aa4408ca';
 ?>