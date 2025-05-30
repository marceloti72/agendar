<?php
$ca_path = 'C:\xampp\php\extras\ssl\cacert.pem'; // Confirme se este caminho está 100% correto

echo "Tentando verificar o arquivo CA em: " . $ca_path . "\n";
if (file_exists($ca_path)) {
    echo "Arquivo CA encontrado.\n";
} else {
    echo "ARQUIVO CA NÃO ENCONTRADO! Verifique o caminho.\n";
}
echo "\n";

echo "Informações de SSL do PHP:\n";
echo "curl.cainfo (do php.ini): " . ini_get('curl.cainfo') . "\n";
echo "openssl.cafile (do php.ini): " . ini_get('openssl.cafile') . "\n";
print_r(openssl_get_cert_locations());
echo "\n";

echo "Tentando cURL para https://getcomposer.org/versions com CAINFO explícito...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://getcomposer.org/versions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);

// Forçando o uso do seu arquivo cacert.pem
curl_setopt($ch, CURLOPT_CAINFO, $ca_path);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Força a verificação
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);   // Força a verificação do nome do host

// Para depuração detalhada do cURL
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$output = curl_exec($ch);

if ($output === false) {
    echo "---------------------------------------------\n";
    echo "ERRO NO CURL:\n";
    echo "Código do Erro: " . curl_errno($ch) . "\n";
    echo "Mensagem de Erro: " . curl_error($ch) . "\n";
    echo "---------------------------------------------\n";
} else {
    echo "---------------------------------------------\n";
    echo "SUCESSO NA CONEXÃO cURL!\n";
    // echo "Resposta (parcial): " . substr($output, 0, 200) . "...\n"; // Descomente para ver parte da resposta
    echo "---------------------------------------------\n";
}

!rewind($verbose);
$verboseLog = stream_get_contents($verbose);
echo "Informação Detalhada do cURL (Verbose Output):\n";
echo $verboseLog;
echo "---------------------------------------------\n";

curl_close($ch);
?>