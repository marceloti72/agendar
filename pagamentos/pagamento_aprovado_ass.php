
<?php
$id_pg = @$_GET['id_pg'];
$id_conta = @$_GET['id_conta'];

$query = $pdo->query("SELECT * FROM config WHERE id = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$empresa = @$res[0]['nome'];
$telefone_empresa = @$res[0]['telefone_whatsapp'];
$token = @$res[0]['token'];
$instancia = @$res[0]['instancia'];
$pgto_api = @$res[0]['pgto_api'];
$api = @$res[0]['api'];


$query = $pdo->query("SELECT * FROM receber WHERE ref_pix = '$ref_pix'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);

$total_reg = @count($res);
$cliente = $res[0]['cliente'];
$pessoa = $res[0]['pessoa'];
$data = $res[0]['data_venc'];
$data_lanc = $res[0]['data_lanc'];
$usuario = $res[0]['usuario'];
$hash = $res[0]['hash'];
$ref_pix = $res[0]['ref_pix'];
$id_conta = $res[0]['id_conta'];
$forma_pgto = $res[0]['pgto'];
$frequencia = $res[0]['frequencia'];
$descricao = $res[0]['descricao'];
$valor = $res[0]['valor'];
$id_pg = $res[0]['id'];


$query = $pdo->query("SELECT * FROM assinantes WHERE id = '$cliente' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_plano = @$res[0]['id_plano'];

$query = $pdo->query("SELECT * FROM planos WHERE id = '$id_plano' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$nome_plano = @$res[0]['nome'];
$valor_mensal = @$res[0]['preco_mensal'];
$valor_anual = @$res[0]['preco_anual'];


//$servico_conc = $nome_serv . " (Site)";

// if ($id_pg == "") {
//     $pdo->query("INSERT INTO receber SET descricao = '$servico_conc', tipo = 'Serviço', valor = '$valor_pago', data_lanc = CURDATE(), data_venc = CURDATE(), data_pgto = CURDATE(), usuario_lanc = '0', usuario_baixa = '0', foto = 'sem-foto.jpg', pessoa = '$cliente', pago = 'Sim', servico = '$servico', funcionario = '$funcionario', obs = '', pgto = '$forma_pgto', referencia = '$ult_id', id_conta = '$id_conta'");
// }

if ($id_pg != "") {    
    $pdo->query("UPDATE receber set pago = 'Sim', data_pgto = curDate() where id = '$id_pg'");


    //CRIAR A PRÓXIMA CONTA A PAGAR
    $dias_frequencia = $frequencia;

    if ($dias_frequencia == 30) {
        $nova_data_vencimento = date('Y-m-d', strtotime("+1 month", strtotime($data)));
        $nome_freq = 'Mensal';
    
    } else if ($dias_frequencia == 365) {
        $nova_data_vencimento = date('Y-m-d', strtotime("+1 year", strtotime($data)));
        $nome_freq = 'Anual';

    } else {
        $nova_data_vencimento = date('Y-m-d', strtotime("+$dias_frequencia days", strtotime($data)));
        $nome_freq = '';
    }

    
    if (@$dias_frequencia > 0) {
        $pdo->query("INSERT INTO receber set descricao = '$descricao', cliente = '$cliente', valor = '$valor', data_lanc = curDate(), data_venc = '$nova_data_vencimento', frequencia = '$frequencia', pago = 'Não', tipo = 'Assinatura', pessoa = '$pessoa', subtotal = '$valor'");
        $id_ult_registro = $pdo->lastInsertId();

        $valorF = @number_format($valor, 2, ',', '.');


        if($api == 'Sim'){

            //telefone da empresa
            $query = $pdo->query("SELECT * FROM clientes where id = '$pessoa'");
            $res = $query->fetchAll(PDO::FETCH_ASSOC);
            @$telefone = $res[0]['telefone'];
            @$nome_cliente = $res[0]['nome'];
            
            $url = "https://" . $_SERVER['HTTP_HOST'] . "/";
            //lembrete do vencimento da conta
            $link_pgto = $url . 'pagar_ass/' . $id_ult_registro;
            $telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone);
            $nova_data_vencimentoF = implode('/', array_reverse(@explode('-', $nova_data_vencimento)));


            $nome_sistema_maiusculo = mb_strtoupper($empresa);              
            $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefone);

            $mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';            
            $mensagem .= 'Olá *'.$nome_cliente.'*, estou voltando para avisar que seu pagamento foi processado com sucesso! Obrigado 😃%0A%0A';
            $mensagem .= '*Plano:* '.$nome_plano.' - '.$nome_freq.'%0A';
            $mensagem .= '*Valor:* R$ '.$valorF.'%0A';
            $mensagem .= '*Próximo Vencimento:* ' . $nova_data_vencimentoF . '%0A'; 
            require('../ajax/api-texto.php');


            $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefone_empresa); 

            $mensagem = '✅ *Pagamento processado com sucesso!*%0A%0A';        
            $mensagem .= 'Dados da assinatura:%0A';
            $mensagem .= '*Assinante:* ' . $nome_cliente . '%0A';
            $mensagem .= '*Plano:* '.$nome_plano.' - '.$nome_freq.'%0A';
            $mensagem .= '*Valor:* R$ '.$valorF.'%0A';
            $mensagem .= '*Próximo Vencimento:* ' . $nova_data_vencimentoF . '%0A';   

            require('../ajax/api-texto.php');        


        
            // PEGAR UM DIA ANTES DO VENCIMENTO #############################################################
            $data_cobranca = date('Y-m-d', strtotime("-1 days", strtotime($nova_data_vencimento)));                       

            //lembrete do vencimento da conta

            $mensagem = '🔔 _Lembrete Automático de Vencimento!_ %0A%0A';
            $mensagem .= '* '.$nome_sistema_maiusculo.'* %0A%0A';
            $mensagem .= '*Olá '.$nome_cliente.', tudo bem? 😃%0A%0A';
            $mensagem .= 'Queremos lembra que sua assinatura vençerá amanhã.%0A';
            $mensagem .= 'Dados da assinatura: %0A%0A';
            $mensagem .= '*Plano:* '.$nome_plano.' - '.$nome_freq.'%0A';
            $mensagem .= 'Valor: *R$ ' . $valorF . '* %0A';
            $mensagem .= 'Vencimento: *' . $nova_data_vencimentoF . '* %0A%0A';

            if ($pgto_api == "Sim") {
                $mensagem .= 'Efetue o pagamento no link abaixo ' . $point_down . ' %0A';
                $mensagem .= $link_pgto;
            } else {
                $mensagem .= 'Dados de Pagamento Abaixo ' . $point_down . ' %0A';
                $mensagem .= '$dados_pagamento';
            }

            $data_agd = $data_cobranca . ' 09:00:00';


            // AGENDAR ENVIO
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://chatbot.menuia.com/api/create-message',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                'appkey' => $instancia,
                'authkey' => $token,
                'to' => $telefone_envio,
                'message' => $mensagem,        
                'agendamento' => $data_agd
                ),
            ));
            
            $response = curl_exec($curl);  
            
            curl_close($curl);
            $response = json_decode($response, true);       
            
            $hash = $response['id'];
            

            $pdo->query("UPDATE receber SET hash = '$hash' where id = '$id_ult_registro'");


        }
    }
       
    
}

