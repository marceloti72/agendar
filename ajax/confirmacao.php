<?php
//require_once __DIR__ . '/../funcoes.php';  
    
    
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
      'appkey' => 'GESTAO',
      'authkey' => 'f4QGNF6L4KhSNvEWP1VTHaDAI57bDTEj89Kemni1iZckHne3j9',
      'to' => $telefone,
      'message' => $mensagem,
      'licence' => 'skysee',
      'agendamento' => $data_envio
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    
    
    $response = json_decode($response, true); // {"status":200,"id":5370,"message":"Agendamento realizado com sucesso."}
    //save_log($pdo, $token, $instancia, $response, 'agendamento', $telefone, $mensagem);
     $id = $response['id'];

?>