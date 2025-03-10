<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../sistema/conexao.php");
$dados = json_decode(file_get_contents('php://input'), true);

$tipo = $dados['tipo'];


if ($tipo == "Sistema") {
    $numeroCliente = $dados['destinatario'];
    $id = $dados['idAgendamento'];
    $pdo->query("UPDATE agendamentos SET phone = '$numeroCliente' where hash = '$id' and id_conta = '$id_conta'"); // 01:20
} else if ($tipo == "Chat") {
    $resposta = $dados['mensagem'];
    $numeroCliente = $dados['remetente'];

    $dataHoraAtual = date('Y-m-d H:i:s');

    // Montando a consulta SQL com a condição desejada
    $query = "SELECT * FROM agendamentos WHERE phone = '$numeroCliente' AND status = 'Agendado' AND CONCAT(data, ' ', hora) > '$dataHoraAtual' and id_conta = '$id_conta' ORDER BY CONCAT(data, ' ', hora) ASC LIMIT 1";
    $agendamento = $pdo->query($query);
    $resAgendamento = $agendamento->fetchAll(PDO::FETCH_ASSOC);
    $agendamento = $resAgendamento[0];


    // Dados Agendamento
    $idCliente = $resAgendamento[0]['cliente'];
    $idFuncionario = $resAgendamento[0]['funcionario'];
    $dataAgendamento = date('d/m/Y', strtotime($resAgendamento[0]['data'])); // data Formatada para padrao 00/00/0000
    $horarioAgendamento = $resAgendamento[0]['hora'];

    // Buscando infos cliente
    $query = "SELECT * FROM clientes where id = '$idCliente' and id_conta = '$id_conta'";
    $cliente = $pdo->query($query);
    $resCliente = $cliente->fetchAll(PDO::FETCH_ASSOC);
    $cliente = $resCliente[0];
    $nomeCliente = $cliente['nome'];


    // Buscando infos funcionario
    $query = "SELECT * FROM usuarios where id = '$idFuncionario' and id_conta = '$id_conta'";
    $funcionario = $pdo->query($query);
    $resFuncionario = $funcionario->fetchAll(PDO::FETCH_ASSOC);
    $funcionario = $resFuncionario[0];
    $nomeFuncionario = $funcionario['nome'];
    $telefoneFuncionario = $funcionario['telefone'];

    if ($agendamento) {

        if ($resposta == 1) {
            $pdo->query("UPDATE agendamentos SET status = 'Confirmado' where phone = '$numeroCliente' and id_conta = '$id_conta'");
            $mensagem = "✅ _Confirmado!_\n\n";
            $mensagem .= "Agendamento de *" . $nomeCliente . "*.\n\n";
            $mensagem .= "*Data:* " . $dataAgendamento . " às " . $horarioAgendamento . "\n\n";
            $mensagem .= "O profissional *" . $nomeFuncionario . "* lhe aguarda no horário agendado.\n\n";
            $mensagem .= "_Obrigado!_";

            //Notifica o cliente
            $telefone = '55' . preg_replace('/[ ()-]+/', '', $numeroCliente);
            require('api-texto.php');

            //Notifica o Dono da plataforma
            $telefone = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);
            require('api-texto.php');

            //Notifica o funcionario se ele nao for o dono
            if ($telefoneFuncionario != $whatsapp_sistema) {
                $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefoneFuncionario);
                require('api-texto.php');
            }

            return "Confirmado!";
        } else if ($resposta == 2) {
            $pdo->query("DELETE FROM agendamentos where phone = '$numeroCliente' and id_conta = '$id_conta'");
            $mensagem = "❌ _Cancelado!_\n\n";
            $mensagem .= "Agendamento de *" . $nomeCliente . "*\n\n";
            $mensagem .= "*Data:* " . $dataAgendamento . " às " . $horarioAgendamento . "\n\n";
            $mensagem .= " Reagende novo horário pelo site: " . $url;

            //Notifica o cliente
            $telefone = '55' . preg_replace('/[ ()-]+/', '', $numeroCliente);
            require('api-texto.php');

            //Notifica o Dono da plataforma
            $telefone = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);
            require('api-texto.php');

            //Notifica o funcionario se ele nao for o dono
            if ($telefoneFuncionario != $whatsapp_sistema) {
                $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefoneFuncionario);
                require('api-texto.php');
            }
            return "Cancelado!";
        } else {
            $mensagem = "Ops! Nenhuma opção valida digite ✔ *1* Para confirmar ou ❌ *2* para cancelar ";

            //Notifica o cliente
            $telefone = '55' . preg_replace('/[ ()-]+/', '', $numeroCliente);
            require('api-texto.php');
        }
    }
}
