<?php
// Configurações de cabeçalho
// header('Content-Type: application/json');
// header('Access-Control-Allow-Origin: *'); // Permite requisições de qualquer origem (para monitor_logs.js)

// // Função para verificar saúde do serviço
// function checkHealth() {
//     $response = ['status' => 'OK'];

//     // Opcional: Verificar conexão com banco de dados (exemplo com MySQL)
//     /*
//     try {
//         $db = new PDO('mysql:host=localhost;dbname=gestao', 'username', 'password');
//         $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//         // Teste simples: executar uma query leve
//         $stmt = $db->query('SELECT 1');
//         if (!$stmt) {
//             throw new Exception('Falha na consulta ao banco de dados');
//         }
//     } catch (Exception $e) {
//         http_response_code(500);
//         return ['status' => 'ERROR', 'error' => 'Falha na conexão com o banco: ' . $e->getMessage()];
//     }
//     */

//     return $response;
// }

// // Executar verificação de saúde
// $health = checkHealth();

// // Definir código de resposta HTTP
// http_response_code($health['status'] === 'OK' ? 200 : 500);

// // Retornar resposta JSON
// echo json_encode($health);



header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
sleep(10); // Simula um travamento (timeout)
http_response_code(500);
echo json_encode(['status' => 'ERROR', 'error' => 'Serviço travado']);
?>