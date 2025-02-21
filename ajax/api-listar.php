<?php
require_once("../sistema/conexao.php");

if ($api == "menuia") 
{
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
      'message' => 'false',
      'licence' => 'hugocursos',
      'listaAgendamento' => 'true',
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    echo "<pre>";print_r($response['message']);echo"</pre>";

}
else
{
     $url = "http://api.wordmensagens.com.br/agendar-list";

  $data = array('instance' => $instancia,
                'token' => $token);

  $options = array('http' => array(
               'method' => 'POST',
               'content' => http_build_query($data)
));

$stream = stream_context_create($options);

$result = @file_get_contents($url, false, $stream);

$result = json_decode($result);

echo "<pre> ";print_r($result);echo"</pre> ";
}

 
?>
  
  