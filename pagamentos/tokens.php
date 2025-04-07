<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];

$query = $pdo->query("SELECT * FROM config WHERE id = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$token = @$res[0]['token'];
$token = @$res[0]['token_mp'];
$instancia = @$res[0]['key_mp'];

$access_token = $token;
$public_key     = $instancia;





//  $access_token = 'APP_USR-5194938746509270-070420-5f8c4f8a406cfebf91215923b06a4fa1-1034833440';
//  $public_key     = 'APP_USR-9d70c2bb-8d81-473c-8c06-cb48aa4408ca';
 ?>