<?php 
require_once("sistema/conexao.php");

$email = $_POST['email'];

if($email == ""){
    echo 'Preencha o Campo Email!';
    exit();
}

$query = $pdo->prepare("SELECT * FROM usuarios where email = :email");
$query->bindParam(':email', $email,);
$query->execute();
$res = $query->fetchAll(PDO::FETCH_ASSOC);

if(count($res) <= 0){
     echo 'Email não cadastrado!';
     exit();
}

$id = $res[0]['id'];
$nome = $res[0]['nome'];
$tel = $res[0]['telefone'];
$username = $res[0]['username'];

// if($res[0]['ativo'] == 'Não'){
//     echo 'Seu acesso esta bloqueado! Entre em contato com a instituição.';
//     exit();

// }



function gerarSenha($tamanho = 12, $maiusculas = true, $minusculas = true, $numeros = true, $simbolos = true) {
    $caracteres = '';

    if ($maiusculas) $caracteres .= 'ABCDEFGHIJ';
    if ($minusculas) $caracteres .= 'abcdefghij';
    if ($numeros)    $caracteres .= '0123456789';
    if ($simbolos) 
    $caracteres .= '!@#';

    $senha = '';
    for ($i = 0; $i < $tamanho; $i++) {
        $senha .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }

    return $senha;

    // ... (mesma função acima)
}

// Gerando e armazenando a senha em um banco de dados (Exemplo com PDO)
$senha = gerarSenha(6);
//$hash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE usuarios SET senha = :hash WHERE email = :email");
$stmt->bindParam(':hash', $senha);
$stmt->bindParam(':email', $email);
$stmt->execute();


$telefone = '55'.preg_replace('/[ ()-]+/' , '' , $tel);

$mensagem = '🔔 *Nova senha*%0A%0A';
$mensagem.= '*MARKAI - Gestão de Serviços*%0A%0A';
$mensagem.= 'Houve um pedido de recuperação de senha:%0A';
$mensagem.= 'Nome: *'.$nome.'*%0A'; 
$mensagem.= 'Nova senha: *'.$senha.'*%0A%0A';     
$mensagem.= '*Se desejar altera a senha, vá em configuração de perfil.*%0A';     

require("ajax/api-texto-recup.php");

echo 'Sua senha foi Enviada para seu WhatsApp!';

 ?>