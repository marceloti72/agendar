<?php 

     $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://chatbot.menuia.com/api/developer',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array(
  'authkey' => $token,
  'message' => $instancia,
  'checkDispositivo' => 'true',
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$response = json_decode($response, false);
$status = $response->status;


    if($status == '200'){
       $cor= '#44bb52';
       $status = 'WhatsApp conectado';        
      }else {
        $cor= 'red';
        $status = 'WhatsApp desconectado';        
      }
       //echo $response; 
    
    