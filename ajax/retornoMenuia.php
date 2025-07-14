<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//require_once("../sistema/conexao.php");


$db_servidor = 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
$db_usuario = 'skysee';
$db_senha = '9vtYvJly8PK6zHahjPUg';
$db_nome = 'barbearia';

$url = "https://" . $_SERVER['HTTP_HOST'] . "/";


// Configuração do Fuso Horário
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o Banco de Dados
try {
	$pdo = new PDO("mysql:dbname=$db_nome;host=$db_servidor;charset=utf8", $db_usuario, $db_senha);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilita tratamento de erros
} catch (PDOException $e) {
	die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

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
    echo $numeroCliente;

    // Montando a consulta SQL com a condição desejada
    $query = "SELECT * FROM agendamentos WHERE phone = '$numeroCliente' AND status = 'Agendado' AND CONCAT(data, ' ', hora) > '$dataHoraAtual' ORDER BY CONCAT(data, ' ', hora) ASC LIMIT 1";
    $agendamento = $pdo->query($query);
    $resAgendamento = $agendamento->fetchAll(PDO::FETCH_ASSOC);
    $agendamento = $resAgendamento[0];
    $id_comanda = $resAgendamento[0]['comanda_id'];
    $id_conta = $resAgendamento[0]['id_conta'];
    //echo $agendamento;


    // Dados Agendamento
    $idCliente = $resAgendamento[0]['cliente'];
    $idFuncionario = $resAgendamento[0]['funcionario'];
    $idAgendamento = $resAgendamento[0]['id'];
    $dataAgendamento = date('d/m/Y', strtotime($resAgendamento[0]['data'])); // data Formatada para padrao 00/00/0000
    $horarioAgendamento = $resAgendamento[0]['hora'];
    
    $horaF = date("H:i", strtotime($horarioAgendamento));

    // Buscando infos cliente
    $query = "SELECT * FROM clientes where id = '$idCliente' and id_conta = '$id_conta' ";
    $cliente = $pdo->query($query);
    $resCliente = $cliente->fetchAll(PDO::FETCH_ASSOC);
    $cliente = $resCliente[0];
    $nomeCliente = $cliente['nome'];


    // Buscando infos funcionario
    $query = "SELECT * FROM usuarios where id = '$idFuncionario' and id_conta = '$id_conta' ";
    $funcionario = $pdo->query($query);
    $resFuncionario = $funcionario->fetchAll(PDO::FETCH_ASSOC);
    $funcionario = $resFuncionario[0];
    $nomeFuncionario = $funcionario['nome'];
    $telefoneFuncionario = $funcionario['telefone'];
    $username = $funcionario['username'];

    $query = "SELECT * FROM config where id = '$id_conta' ";
    $config = $pdo->query($query);
    $resConfig = $config->fetchAll(PDO::FETCH_ASSOC);
    $nome_loja = $resConfig[0]['nome'];
    $tel_loja = $resConfig[0]['telefone_whatsapp'];
    $instancia = $resConfig[0]['instancia'];
    $token = $resConfig[0]['token'];
    $encaixe = $resConfig[0]['encaixe'];

    $url2 = 'https://markai.skysee.com.br/agendamentos?u='.$username;

    if ($agendamento) {

        if ($resposta == 1) {
            $pdo->query("UPDATE agendamentos SET status = 'Confirmado' where id = '$idAgendamento' and id_conta = '$id_conta'");
            $mensagem = "✅ _Confirmado!_\n\n";
            $mensagem .= "Agendamento de *" . $nomeCliente . "*.\n\n";
            $mensagem .= "*Data:* " . $dataAgendamento . " às " . $horarioAgendamento . "\n\n";
            $mensagem .= "O profissional *" . $nomeFuncionario . "* lhe aguarda no horário agendado.\n\n";
            $mensagem .= "_Obrigado!_";

            //Notifica o cliente
            $telefone = $numeroCliente;
            require('api-texto.php');

            // Notifica o Dono da plataforma
            // $telefone = '55' . preg_replace('/[ ()-]+/', '', $tel_loja);
            // require('api-texto.php');

            // Notifica o funcionario se ele nao for o dono
            if ($telefoneFuncionario != $tel_loja) {
                $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefoneFuncionario);
                require('api-texto.php');
            }

            return "Confirmado!";
        } else if ($resposta == 2) {
            // 4. Exclui registros relacionados
            $query = $pdo->prepare("DELETE FROM agendamentos WHERE id = :id AND id_conta = :id_conta");
            $query->execute([':id' => $idAgendamento, ':id_conta' => $id_conta]);
            if ($query->rowCount() <= 0) {
                throw new Exception("Falha ao excluir agendamento ID {$idAgendamento}.");
            }            

            $query = $pdo->prepare("DELETE FROM comandas WHERE id = :comanda AND id_conta = :id_conta");
            $query->execute([':comanda' => $id_comanda, ':id_conta' => $id_conta]);
            if ($query->rowCount() <= 0) {
                error_log("Aviso: Nenhuma comanda excluída para ID {$id_comanda}.");
            }

            $query = $pdo->prepare("DELETE FROM pagar WHERE comanda = :comanda AND id_conta = :id_conta");
            $query->execute([':comanda' => $id_comanda, ':id_conta' => $id_conta]);
            if ($query->rowCount() <= 0) {
                error_log("Aviso: Nenhuma comissão excluída para comanda ID {$id_comanda}.");
            }

            $query = $pdo->prepare("DELETE FROM receber WHERE comanda = :comanda AND id_conta = :id_conta");
            $query->execute([':comanda' => $id_comanda, ':id_conta' => $id_conta]);
            if ($query->rowCount() <= 0) {
                error_log("Aviso: Nenhum registro excluído da tabela 'receber' para comanda ID {$id_comanda}.");
            }

            $mensagem = "❌ _Cancelado!_\n\n";
            $mensagem .= "Agendamento de *" . $nomeCliente . "*\n\n";
            $mensagem .= "*Data:* " . $dataAgendamento . " às " . $horarioAgendamento . "\n\n";
            $mensagem .= " Reagende novo horário pelo link: " . $url2;

            //Notifica o cliente
            $telefone = $numeroCliente;
            require('api-texto.php');

            //Notifica o Dono da plataforma
            // $telefone = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);
            // require('api-texto.php');

            // Notifica o funcionario se ele nao for o dono
            if ($telefoneFuncionario != $tel_loja) {
                $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefoneFuncionario);
                require('api-texto.php');
            }

            $nome_sistema_maiusculo = mb_strtoupper($nome_sistema);
            // 6. Notificação de encaixe (se aplicável)
            if ($encaixe == 'Sim') {
                $query = $pdo->prepare("SELECT nome, whatsapp FROM encaixe WHERE data = :data AND profissional = :profissional AND id_conta = :id_conta");
                $query->execute([':data' => $dataAgendamento, ':profissional' => $idFuncionario, ':id_conta' => $id_conta]);
                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                $num_clientes = count($res);

                if ($num_clientes > 0) {
                    $clientes_info = '';
                    foreach ($res as $cliente_encaixe) {
                        $mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
                        $mensagem .= '*_Horário disponível_* 📆%0A%0A';
                        $mensagem .= 'Olá ' . $cliente_encaixe['nome'] . ', uma vaga foi liberada. Corra para agendar! 😃%0A%0A';
                        $mensagem .= 'Profissional: *' . $nomeFuncionario . '* %0A';
                        $mensagem .= 'Data: *' . $dataAgendamento . '* %0A';
                        $mensagem .= 'Hora: *' . $horaF . '* %0A';
                        $mensagem .= 'Link de agendamento: %0A';
                        $mensagem .= $url2 . ' %0A';

                        $telefone = '55' . preg_replace('/[ ()-]+/', '', $cliente_encaixe['whatsapp']);
                        require('api-texto.php');

                        $clientes_info .= '✅ ' . htmlspecialchars($cliente_encaixe['nome']) . ', ' . htmlspecialchars($cliente_encaixe['whatsapp']) . '%0A';
                    }

                    $mens = $num_clientes . ' cliente' . ($num_clientes > 1 ? 's' : '') . ' que estava' . ($num_clientes > 1 ? 'm' : '') . ' aguardando encaixe para essa data e profissional fora' . ($num_clientes > 1 ? 'm' : '') . ' alertado' . ($num_clientes > 1 ? 's' : '') . '.%0A%0A' . $clientes_info;

                    $mensagem = '*_Alerta de Encaixe_* 🚨%0A%0A';
                    $mensagem .= 'Profissional: *' . $nomeFuncionario . '* %0A';
                    $mensagem .= 'Data: *' . $dataAgendamento . '* %0A';
                    $mensagem .= 'Hora: *' . $horaF . '* %0A%0A';
                    $mensagem .= $mens . ' %0A';                    

                    $telefone = '55' . preg_replace('/[ ()-]+/', '', $tel_loja);
                    require('api-texto.php');

                     if ($telefoneFuncionario != $tel_loja) {
                        $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefoneFuncionario);
                        require('api-texto.php');
                     }

                }
            }
            
            return "Cancelado!";
        } else {
            $mensagem = "Ops! Nenhuma opção valida digite:\n ✅ *1* Para confirmar ou ❌ *2* para cancelar ";

            //Notifica o cliente
            $telefone = $numeroCliente;
            require('api-texto.php');
        }
    }
}
