<?php

    //require_once("../../../../funcoes.php");
     $mensagem = str_replace("%0A", "\n", $mensagem); 
     $mensagem = $mensagem == '' ? '.' : $mensagem;
    
  $curl = curl_init();
  
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://chatbot.menuia.com/api/create-message',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array(
      'appkey' => $instancia,
      'authkey' => $token,      
      'to' => $telefone,
      'message' => $mensagem ?? '.',      
      'file' => $url."sistema/painel/img/produtos/".$foto,
      'sandbox' => 'false'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    
    $res_hash = json_decode($response, true);
    //save_log($pdo, $token, $instancia, $res_hash, 'Campanha(foto)', $numeros_formatados, $mensagem);
    //$hash = $res_hash['id'];  

?>