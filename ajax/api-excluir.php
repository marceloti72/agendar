<?php
require_once("../../../../funcoes.php");

  $mensagem = str_replace("%0A", "\n", $mensagem); 
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
      'message' => $hash,
      'licence' => 'skysee',
      'cancelarAgendamento' => 'true',
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    $response = json_decode($response, true);
    save_log($pdo, $token, $instancia, $response, 'texto', $telefone, $mensagem);

?>

  