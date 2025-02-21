<?php
header('Content-Type: application/json');

$response = array();
   
require_once("../../../conexao.php");
    
try {
    $stmt = $pdo->prepare("UPDATE config SET instancia = :appkey, token = :authkey, email_menuia = :email, plano_menuia = :plano, validade_menuia = :validade, senha_menuia = :senha ");
    $stmt->bindValue(':appkey', null);
    $stmt->bindValue(':authkey', null);
    $stmt->bindValue(':email', null);
    $stmt->bindValue(':plano', null);
    $stmt->bindValue(':validade', null);
    $stmt->bindValue(':senha', null);
    $stmt->execute();
    
    // Verifica se houve exceções durante a execução da consulta
    if($stmt->errorCode() === '00000') {
        $response['status'] = 200;
        $response['message'] = 'Atualização bem-sucedida';
    } else {
        $response['status'] = 500;
        $response['message'] = 'Erro interno ao atualizar';
    }
} catch (PDOException $e) {
    $response['status'] = 500;
    $response['message'] = 'Erro de banco de dados: ' . $e->getMessage();
}

echo json_encode($response);
?>
