<?php
require_once("sistema/conexao.php");

@session_start();

// INÍCIO - Adicionado para debugging (erro 500)

 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Relata erros do MySQLi

// FIM - Adicionado para debugging


$numero_aleatorio = rand(1000, 9999);
$cpf = rand(1000000000, 99999999999);

$nome_adm = $_POST['nome'];

$nome = 'Teste nº' . $numero_aleatorio;
$telefone = $_POST['telefone'];
$email_adm = $_POST['email'];
$nivel = 'administrador';
$plano = $_POST['plano'];
$frequencia = $_POST['frequencia'];
$valor = $_POST['valor'];



if($frequencia == '30'){
    $periodo = 'Mensal';
}else{
    $periodo = 'Anual';
}

if($plano == '1'){
    $plano2 = 'Individual';
}else{
    $plano2 = 'Empresa';
}



$ativo = 'teste';
$logo = 'logo-teste.png';

//$valor = '39.90';
$senha2 = '123';
//$hash = password_hash($senha, PASSWORD_DEFAULT);

$data_pgto = date('Y-m-d');
$pago = 'Não';

$servidor = 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
$usuario = 'skysee';
$senha = '9vtYvJly8PK6zHahjPUg';
$banco = 'barbearia';

$token_menuia = 'f4QGNF6L4KhSNvEWP1VTHaDAI57bDTEj89Kemni1iZckHne3j9';
$email_menuia = 'rtcorretora@gmail.com';


$hoje = date('Y-m-d');



if ($email_adm == "") {
    echo 'O email é obrigatório, pois é o login para acesso.';
    exit();
}

if ($nome_adm == "") {
    echo 'O nome é Obrigatório!';
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


// Verifica se o email já existe no banco de dados
$query = $pdo->prepare("SELECT email FROM config WHERE email = :email_adm");
$query->bindValue(":email_adm", $email_adm);
$query->execute();
$res = $query->fetchAll(PDO::FETCH_ASSOC);

if (count($res) > 0) {
    echo 'O email já existente no banco de dados!';
    exit();
}

// Inicia a transação
try {
    $pdo->beginTransaction();

    // Cadastra a instituição no AGENDAR
    $res1 = $pdo->prepare("INSERT INTO config SET nome = :nome, telefone_whatsapp = :telefone, email = :email_adm, ativo = :ativo, senha_menuia = 'mof36001', token = :token, email_menuia = :email_menuia, plano = :plano, api = 'Sim', data_cadastro = NOW()");
    $res1->bindValue(":nome", $nome);
    $res1->bindValue(":telefone", $telefone);
    $res1->bindValue(":email_adm", $email_adm);
    $res1->bindValue(":ativo", $ativo);    
    $res1->bindValue(":token", $token_menuia);
    $res1->bindValue(":email_menuia", $email_menuia);
    $res1->bindValue(":plano", $plano);
    $res1->execute();

    $id_conta = $pdo->lastInsertId();

    // Inserir username
    $res9 = $pdo->prepare("UPDATE config SET username = :username WHERE id = :id");
    $res9->bindValue(":username", $id_conta);
    $res9->bindValue(":id", $id_conta);
    $res9->execute();

    // Cadastra o perfil ADM-MASTER
    $res2 = $pdo->prepare("INSERT INTO usuarios SET nome = :nome, cpf = :cpf, email = :email, telefone = :telefone, senha = :senha, nivel = :nivel, id_conta = :id_conta, ativo = :ativo, atendimento = 'Sim', intervalo = '15', username = :username, foto = 'sem-foto.jpg', data = NOW()");
    $res2->bindValue(":nome", $nome_adm);
    $res2->bindValue(":cpf", $cpf);
    $res2->bindValue(":email", $email_adm);
    $res2->bindValue(":telefone", $telefone);
    $res2->bindValue(":senha", $senha2);
    $res2->bindValue(":nivel", $nivel);
    $res2->bindValue(":ativo", $ativo);
    $res2->bindValue(":id_conta", $id_conta);
    $res2->bindValue(":username", $id_conta);
    $res2->execute();

    $id_usuario = $pdo->lastInsertId();


    // Cadastra o planos
    // Dados dos planos padrão
    $planos_para_cadastrar = [
        // Adicione preços e status ativo conforme a definição da sua tabela planos
        // Coloquei NULL para preco_anual como exemplo, ajuste se necessário
        ['nome' => 'Bronze',   'imagem' => 'bronze.png',   'ordem' => 10],
        ['nome' => 'Prata',    'imagem' => 'prata.png',    'ordem' => 20],
        ['nome' => 'Ouro',     'imagem' => 'Ouro.png',     'ordem' => 30],
        ['nome' => 'Diamante', 'imagem' => 'diamante.png', 'ordem' => 40] // Usei .png conforme seu pedido
    ];
    try {
        // PASSO 1: VERIFICAR SE JÁ EXISTEM PLANOS PARA ESTA CONTA
        $check_planos = $pdo->prepare("SELECT COUNT(*) FROM planos WHERE id_conta = :id_conta");
        $check_planos->execute([':id_conta' => $id_conta]);
        $count = $check_planos->fetchColumn();

        if ($count == 0) {            
            $sql = "INSERT INTO planos (nome, imagem, ordem, id_conta, data_cadastro)
                    VALUES (:nome, :imagem, :ordem, :id_conta, NOW())"; // Usar NOW() do SQL para data atual
            $query_insert_plano = $pdo->prepare($sql);

            $inseridos_count = 0;
            // PASSO 3: LOOP PELOS PLANOS E EXECUTA A QUERY PARA CADA UM
            foreach ($planos_para_cadastrar as $plano3) {
                // Monta o array de parâmetros para este plano específico
                $params = [
                    ':nome' => $plano3['nome'],
                    ':imagem' => $plano3['imagem'],
                    ':ordem' => $plano3['ordem'],                    
                    ':id_conta' => $id_conta, // A variável da conta atual
                ];

                // Tenta executar a query para este plano
                if ($query_insert_plano->execute($params)) {
                    $inseridos_count++;
                } else {
                    // Loga um erro se um plano específico falhar
                    error_log("Falha ao inserir plano padrão '{$plano3['nome']}' para id_conta {$id_conta}. Erro: " . print_r($query_insert_plano->errorInfo(), true));
                }
            } // Fim do foreach

            if ($inseridos_count == count($planos_para_cadastrar)) {
                 error_log("{$inseridos_count} planos padrão inseridos com sucesso para id_conta {$id_conta}.");
                 // echo "Planos padrão criados!"; // Mensagem de sucesso (opcional)
            } else {
                 error_log("Atenção: Falha ao inserir um ou mais planos padrão para id_conta {$id_conta}. Inseridos: {$inseridos_count}");
                 // echo "Erro ao criar alguns planos padrão."; // Mensagem de erro (opcional)
            }

        } else {
            // Planos já existem, não faz nada ou apenas loga
            error_log("Planos padrão já existem para id_conta {$id_conta}. Nenhuma nova inserção realizada.");
            // echo "Planos padrão já existem."; // Mensagem informativa (opcional)
        }
    } catch (PDOException $e) {
        // Trata erros de banco de dados durante a verificação ou inserção
        echo "Erro ao verificar/cadastrar planos padrão: " . $e->getMessage(); // Mantenha ou remova o echo
        error_log("Erro PDO ao inserir planos padrão para id_conta {$id_conta}: " . $e->getMessage());
        // Se este bloco estiver dentro de uma transação maior, considere $pdo->rollBack(); aqui.
    } catch (Exception $e) {
        // Trata outros erros gerais
         echo "Erro inesperado ao configurar planos: " . $e->getMessage(); // Mantenha ou remova o echo
         error_log("Erro geral ao inserir planos padrão para id_conta {$id_conta}: " . $e->getMessage());
         // Se estiver dentro de uma transação maior, considere $pdo->rollBack(); aqui.
    }
// Fim do else que verifica $id_conta
   

    // Configurações do banco de dados (variam conforme ambiente)
    $url = "https://$_SERVER[HTTP_HOST]/";
    $url2 = explode("//", $url);
    
    if ($url2[1] == 'localhost/') {
        // Banco de dados local
        $host = 'localhost';
        $db = 'gestao_sistemas';
        $user = 'root';
        $pass = '';
    } else {
        // Banco de dados hospedado
        $host = 'app-rds.cvoc8ge8cth8.us-east-1.rds.amazonaws.com';
        $user = 'skysee';
        $pass = '9vtYvJly8PK6zHahjPUg';
        $db = 'gestao_sistemas';
    }

    // Conecta ao segundo banco de dados
    $pdo2 = new PDO("mysql:dbname=$db;host=$host;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Habilita exibição de erros
        PDO::ATTR_EMULATE_PREPARES => false // Desliga emulação de prepared statements
    ]);

    $query8 = $pdo2->query("SELECT * from config");
    $res8 = $query8->fetchAll(PDO::FETCH_ASSOC);
    $token = $res8[0]['token'];
	$instancia = $res8[0]['instancia'];

    // Insere o cliente no segundo banco
    $res3 = $pdo2->prepare("INSERT INTO clientes SET nome = :nome_adm, instituicao = :instituicao, telefone = :telefone, email = :email_adm, valor = :valor, data_pgto = :data_pgto, pago = :pago, ativo = :ativo, servidor = :servidor, banco = :banco, usuario = :usuario, senha = :senha, id_conta = :id_cliente, data_cad = NOW(), plano = :plano, frequencia = :frequencia");
    $res3->bindValue(":id_cliente", $id_conta);
    $res3->bindValue(":nome_adm", $nome_adm);
    $res3->bindValue(":instituicao", $nome);
    $res3->bindValue(":telefone", $telefone);
    $res3->bindValue(":email_adm", $email_adm);
    $res3->bindValue(":valor", $valor);
    $res3->bindValue(":data_pgto", $data_pgto);
    $res3->bindValue(":pago", $pago);
    $res3->bindValue(":servidor", $servidor);
    $res3->bindValue(":banco", $banco);
    $res3->bindValue(":usuario", $usuario);
    $res3->bindValue(":senha", $senha);
    $res3->bindValue(":ativo", $ativo);
    $res3->bindValue(":plano", $plano);
    $res3->bindValue(":frequencia", $frequencia);
    $res3->execute();

    $id_cliente = $pdo2->lastInsertId();

    // Calcula a data de vencimento (7 dias após a data de pagamento)
    $nova_data_vencimento = date('Y-m-d', strtotime("+7 days", strtotime($data_pgto)));

    // Insere o registro na tabela 'receber'
    $res4 = $pdo2->prepare("INSERT INTO receber SET empresa = :empresa, tipo = :tipo, descricao = :descricao, pessoa = :pessoa, valor = :valor, subtotal = :subtotal, vencimento = :vencimento, data_lanc = :data_lanc, arquivo = :arquivo, pago = :pago, cliente = :cliente, frequencia = :frequencia");
    $res4->bindValue(":empresa", '0');
    $res4->bindValue(":tipo", 'Empresa');
    $res4->bindValue(":descricao", 'Mensalidade');
    $res4->bindValue(":pessoa", $id_cliente);
    $res4->bindValue(":valor", $valor);
    $res4->bindValue(":subtotal", $valor);
    $res4->bindValue(":vencimento", $nova_data_vencimento);
    $res4->bindValue(":arquivo", 'sem-foto.png');
    $res4->bindValue(":pago", $pago);
    $res4->bindValue(":cliente", $id_cliente);
    $res4->bindValue(":frequencia", $frequencia);
    $res4->bindValue(":data_lanc", date('Y-m-d'));
    $res4->execute();

    $ult_id_conta = $pdo2->lastInsertId(); // ID da última inserção na tabela 'receber'

    // Link de pagamento
    $link_pgto = 'https://www.gestao.skysee.com.br/pagar/' . $ult_id_conta;

    // Formata a data de vencimento
    $data_vencF = implode('/', array_reverse(explode('-', $nova_data_vencimento)));

    // Calcula a data de cobrança (1 dia antes do vencimento)
    $data_cobranca = date('Y-m-d', strtotime("-1 days", strtotime($nova_data_vencimento)));

    // Formata o nome da escola para maiúsculas
    mb_internal_encoding('UTF-8');
    $nome = mb_strtoupper($nome);

    // Formata o telefone para envio
    $telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone);

    // Define a saudação de acordo com a hora
    $hora = date('H');
    $grinning = json_decode('"\uD83D\uDE00"'); // 😀
    $robo = json_decode('"\ud83e\udd16"'); // 🤖
    $point_down = json_decode('"\ud83d\udc47"'); // 👇
    $sino = json_decode('"\ud83d\udd14"'); // 🔔

    if ($hora < 12 && $hora >= 6) {
        $saudacao = "Bom dia";
    } elseif ($hora >= 12 && $hora < 18) {
        $saudacao = "Boa tarde";
    } elseif ($hora >= 18 && $hora <= 23) {
        $saudacao = "Boa noite";
    } else {
        $saudacao = "Boa madrugada";
    }

    // Primeiro nome
    $primeiroNome = explode(" ", $nome_adm);

    // Envia email com PHPMailer (apenas se não for localhost)
    // if ($url2[1] != 'localhost/escolar/') {
    //     require './vendor/autoload.php';

    //     $mail = new PHPMailer(true);

    //     try {
    //         //$mail->SMTPDebug = SMTP::DEBUG_SERVER; // Descomente para depuração
    //         $mail->CharSet = 'UTF-8';
    //         $mail->isSMTP();
    //         $mail->Host = 'email-ssl.com.br';
    //         $mail->SMTPAuth = true;
    //         $mail->Username = 'contato@skysee.com.br';
    //         $mail->Password = 'x,H,6,$B6!b[';
    //         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    //         $mail->Port = 587;

    //         $mail->setFrom('contato@skysee.com.br', 'SKYSEE - Soluções em TI');
    //         $mail->addAddress($email_adm, $primeiroNome[0]);
    //         $mail->addCC('contato@skysee.com.br');

    //         $mail->isHTML(true);
    //         $mail->Subject = 'Dados para Acesso';
    //         $mail->Body = "<small style=\"opacity: 0.5\"><i>Mensagem automática gerada pelo sistema <b>Skysse Soluções em TI</b>, favor não responder.<i></small><br>";
    //         $mail->Body .= "$saudacao, {$primeiroNome[0]}<br>";
    //         $mail->Body .= "Seu periodo de 7 dias de teste grátis foi concluido com sucesso. Segue os dados para acesso ao sistema:<br><br>";
    //         $mail->Body .= "Link do sistema: <a href='{$link_pgto}'>www.agendar.skysee.com.br</a><br>";
    //         $mail->Body .= "Login: <b>$email_adm</b><br>";
    //         $mail->Body .= "Senha: <b>123</b><br><br>";
    //         $mail->Body .= "<b>Obs:</b> Altere sua senha, basta acessar o seu perfil.<br><br>";

    //         $mail->addEmbeddedImage('../img/ass_skysee.png', 'logo');
    //         $mail->Body .= '<img src="cid:logo">';

    //         $mail->send();
    //     } catch (Exception $e) {
    //         error_log("Erro ao enviar email: " . $mail->ErrorInfo);
    //     }
    // }

    // Mensagem para WhatsApp
    $mensagem = "*MARKAI - Sistema de Gestão de Serviços*%0A%0A";
    $mensagem .= "$saudacao, *" . $primeiroNome[0] . "*%0A%0A";
    $mensagem .= "Seja bem-vindo ao nosso sistema!$grinning%0A%0A";
    $mensagem .= "Segue os dados para acesso:%0A";
    $mensagem .= "*Login:* $email_adm%0A";
    $mensagem .= "*Senha:* 123%0A";
    $mensagem .= "Altere sua senha assim que acessar e complete seus dados!%0A%0A";
    $mensagem .= "Você tem 7 dias grátis para conhecer nosso sistema.%0A%0A";
    $mensagem .= "*Segue os dados para assinatura*" . $point_down . "  %0A%0A";
    $mensagem .= "Cliente: *" . $nome . "* %0A";
    $mensagem .= "Plano: *" . $plano2 . "* %0A";
    $mensagem .= "Período: *" . $periodo . "* %0A";
    $mensagem .= "Valor: R$ " . $valor . "%0A";
    $mensagem .= "Vencimento: *" . $data_vencF . "* %0A%0A";
    $mensagem .= "Baixe o APP: https://www.markai.skysee.com.br/download/markai.apk";

    require("./ajax/api-texto-ass.php");

    // Mensagem de lembrete para WhatsApp (1 dia antes do vencimento)
    $mensagem = $sino . " _Lembrete Automático de Vencimento!_ %0A%0A";
    $mensagem .= "*MARKAI - Sistema de Gestão de Serviços* %0A%0A";
    $mensagem .= "*" . $saudacao . "* tudo bem? " . $grinning . "%0A%0A";
    $mensagem .= "Queremos lembra que sua mensalidade, referente ao teste grátis, vençerá amanhã %0A";
    $mensagem .= "Efetue o pagamento para continuar usando nosso sistema! %0A%0A";
    $mensagem .= "Plano: *" . $plano2 . "* %0A";
    $mensagem .= "Período: *" . $periodo . "* %0A";
    $mensagem .= "Mensalidade: *R$ " . $valor . "* %0A";
    $mensagem .= "Vencimento: *" . $data_vencF . "* %0A%0A";
    $mensagem .= "Efetue o pagamento no link abaixo " . $point_down . " %0A";
    $mensagem .= $link_pgto;

    $data_agd = $data_cobranca . ' 09:00:00';

    require("./ajax/api-agendar-ass.php");

    // Atualiza o hash na tabela 'receber'
    $res5 = $pdo2->prepare("UPDATE receber SET hash = :hash WHERE id = :id");
    $res5->bindValue(":hash", $hash);
    $res5->bindValue(":id", $ult_id_conta);
    $res5->execute();

    // Insere o lead
    $res7 = $pdo2->prepare("INSERT INTO leads SET situacao = :situacao, instituicao = :instituicao, responsavel = :responsavel, telefone = :telefone, email = :email, data_cad = :data_cad");
    $res7->bindValue(":situacao", 'Teste');
    $res7->bindValue(":instituicao", $nome);
    $res7->bindValue(":responsavel", $nome_adm);
    $res7->bindValue(":telefone", $telefone);
    $res7->bindValue(":email", $email_adm);
    $res7->bindValue(":data_cad", date('Y-m-d H:i:s'));
    $res7->execute();

    $pdo->commit(); // Confirma as alterações
    echo 'Salvo com Sucesso';
} catch (Exception $e) {
    $pdo->rollBack(); // Desfaz as alterações em caso de erro
    error_log("Erro ao salvar: " . $e->getMessage());
    echo 'Erro ao salvar. Consulte o administrador do sistema.';
}
