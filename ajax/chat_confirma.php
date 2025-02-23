<?php

$data = [
  'appkey' => $instancia,
      'authkey' => $token,
      'to' => $telefone,
      "message" => $mensagem,
  
  "isStreamEnabled" => false,
  
  "isOnlyRegistering" => false,
 
  "textBubbleContentFormat" => "richText"
];

$json_data = json_encode($data); // Converte o array PHP para JSON

curl_setopt_array($curl, [
  CURLOPT_URL => "https://chat.menuia.com/api/v1/typebots/67ba0431bb9ac/startChat",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $json_data, // Envia o JSON
  CURLOPT_HTTPHEADER => [
      "Content-Type: application/json"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

var_dump($response);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}