<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://repo.packagist.org/packages.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CAINFO, "C:\\xampp\\php\\extras\\ssl\\cacert.pem"); // Força o uso do certificado
$response = curl_exec($ch);
if ($response === false) {
    echo "Erro: " . curl_error($ch) . " (Código: " . curl_errno($ch) . ")";
} else {
    echo "Sucesso! Resposta recebida.";
}
curl_close($ch);
?>