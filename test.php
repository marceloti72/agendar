<?php 
$senha = '123';
$hash = password_hash($senha, PASSWORD_DEFAULT);
echo 'Hash gerado: ' . $hash . PHP_EOL;
$hashArmazenado = '$2y$10$gCXEydTcc1/Cs.QfQJemxej/hagBiEiMYXqmwE5wqa3H4dkSLO71m';
if (password_verify($senha, $hashArmazenado)) {
echo 'Senha corresponde ao hash? Sim' . PHP_EOL;
} else {
echo 'Senha corresponde ao hash? Não' . PHP_EOL;
}