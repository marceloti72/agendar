<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
sleep(10); // Simula um travamento (timeout)
http_response_code(500);
echo json_encode(['status' => 'ERROR', 'error' => 'Serviço travado']);
?>