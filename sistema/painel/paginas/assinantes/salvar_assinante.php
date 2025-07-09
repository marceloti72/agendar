<?php
require_once("../../../conexao.php");
@session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Erro desconhecido.'];

// Validações iniciais e sessão...
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { /* ... */ }
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_conta'])) { /* ... */ }

$id_conta_corrente = $_SESSION['id_conta'];
$id_usuario_logado = $_SESSION['id_usuario'];

// --- Recebe Dados ---
$id_assinante_edit = isset($_POST['id_assinante']) && !empty($_POST['id_assinante']) ? (int)$_POST['id_assinante'] : 0; // ID do registro 'assinantes' (se editando)
$id_cliente_encontrado = isset($_POST['id_cliente_encontrado']) && !empty($_POST['id_cliente_encontrado']) ? (int)$_POST['id_cliente_encontrado'] : 0; // ID do registro 'clientes' (se encontrado pelo telefone)

$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$cpf = isset($_POST['cpf']) ? trim($_POST['cpf']) : '';
$telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$plano_freq_selecionado = isset($_POST['plano_freq_selecionado']) ? trim($_POST['plano_freq_selecionado']) : '';
$data_vencimento_str = isset($_POST['data_vencimento']) ? $_POST['data_vencimento'] : '';

// --- Validações ---
if (empty($nome)) { $response['message'] = 'Nome é obrigatório.'; echo json_encode($response); exit; }
if (empty($telefone)) { $response['message'] = 'Telefone é obrigatório.'; echo json_encode($response); exit; } // Telefone agora é chave da busca
if (empty($plano_freq_selecionado) || strpos($plano_freq_selecionado, '-') === false) { /* ... Plano/Freq inválido ... */ }
if (empty($data_vencimento_str) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_vencimento_str)) { /* ... Data Venc inválida ... */ }
// ... outras validações ...

list($id_plano, $frequencia_escolhida) = explode('-', $plano_freq_selecionado);
$id_plano = (int)$id_plano;
$frequencia_escolhida = (int)$frequencia_escolhida;
$data_vencimento = $data_vencimento_str;

// --- Inicia Transação ---
$pdo->beginTransaction();

try {
    $idClienteFinal = 0;

    // --- Etapa 1: Gerenciar Tabela 'clientes' ---
    if ($id_cliente_encontrado > 0) {
        // Cliente já existe (foi encontrado pelo telefone)
        $idClienteFinal = $id_cliente_encontrado;

        // APENAS atualiza o flag 'assinante' para 'Sim' (e talvez outros dados se permitido editar)
        $query_upd_cli = $pdo->prepare("UPDATE clientes SET assinante = 'Sim', nome = :nome, cpf = :cpf, email = :email, telefone = :telefone WHERE id = :id_cliente AND id_conta = :id_conta");
        $query_upd_cli->bindValue(':nome', $nome); // Permite editar nome/cpf/email do cliente existente
        $query_upd_cli->bindValue(':cpf', $cpf ?: null);
        $query_upd_cli->bindValue(':email', $email ?: null);
        $query_upd_cli->bindValue(':telefone', $telefone); // Atualiza telefone se mudou
        $query_upd_cli->bindValue(':id_cliente', $idClienteFinal, PDO::PARAM_INT);
        $query_upd_cli->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_upd_cli->execute();

    } else {
        // Cliente não existe, INSERE novo cliente
        $query_ins_cli = $pdo->prepare("INSERT INTO clientes (nome, cpf, telefone, email, assinante, id_conta, data_cad) VALUES (:nome, :cpf, :telefone, :email, 'Sim', :id_conta, NOW())");
        $query_ins_cli->bindValue(':nome', $nome);
        $query_ins_cli->bindValue(':cpf', $cpf ?: null);
        $query_ins_cli->bindValue(':telefone', $telefone); // Salva telefone
        $query_ins_cli->bindValue(':email', $email ?: null);
        $query_ins_cli->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_ins_cli->execute();

        $idClienteFinal = $pdo->lastInsertId();
        if (!$idClienteFinal) { throw new Exception("Falha ao criar novo cliente."); }
    }

    // --- Etapa 2: Gerenciar Tabela 'assinantes' ---
    $idAssinanteFinal = 0; // Para usar na tabela receber

    if ($id_assinante_edit > 0) {
        // MODO EDIÇÃO (Atualiza assinante existente)
         // Verifica se pertence à conta
         $check_ass = $pdo->prepare("SELECT id FROM assinantes WHERE id = :id AND id_conta = :id_conta");
         $check_ass->execute([':id' => $id_assinante_edit, ':id_conta' => $id_conta_corrente]);
         if ($check_ass->rowCount() == 0) { throw new Exception("Assinante (para edição) não encontrado."); }

        $query_upd_ass = $pdo->prepare("UPDATE assinantes SET
                                            id_cliente = :id_cliente, -- Atualiza o link para o cliente correto
                                            id_plano = :id_plano,
                                            data_vencimento = :data_vencimento
                                        WHERE id = :id_assinante AND id_conta = :id_conta");
        $query_upd_ass->bindValue(':id_cliente', $idClienteFinal, PDO::PARAM_INT);
        $query_upd_ass->bindValue(':id_plano', $id_plano, PDO::PARAM_INT);
        $query_upd_ass->bindValue(':data_vencimento', $data_vencimento);
        $query_upd_ass->bindValue(':id_assinante', $id_assinante_edit, PDO::PARAM_INT);
        $query_upd_ass->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_upd_ass->execute();
        $idAssinanteFinal = $id_assinante_edit; // Usa o ID existente para 'receber'

    } else {
        // MODO ADIÇÃO (Insere novo assinante)
         // Verifica se já não existe um assinante para este CLIENTE
         $check_ass_exist = $pdo->prepare("SELECT id FROM assinantes WHERE id_cliente = :id_cliente AND id_conta = :id_conta");
         $check_ass_exist->execute([':id_cliente' => $idClienteFinal, ':id_conta' => $id_conta_corrente]);
         if($check_ass_exist->rowCount() > 0){
             // Cliente já era assinante, talvez só atualizar? Ou dar erro?
             // Vamos dar erro por enquanto para evitar duplicidade não intencional
             throw new Exception("Este cliente já possui uma assinatura ativa ou inativa. Edite a existente.");
         }


        $query_ins_ass = $pdo->prepare("INSERT INTO assinantes (id_cliente, id_plano, data_vencimento, id_conta, ativo)
                                        VALUES (:id_cliente, :id_plano, :data_vencimento, :id_conta, 1)");
        $query_ins_ass->bindValue(':id_cliente', $idClienteFinal, PDO::PARAM_INT); // Link com o cliente criado/encontrado
        $query_ins_ass->bindValue(':id_plano', $id_plano, PDO::PARAM_INT);
        $query_ins_ass->bindValue(':data_vencimento', $data_vencimento);
        $query_ins_ass->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_ins_ass->execute();

        $idAssinanteFinal = $pdo->lastInsertId(); // Usa o NOVO ID para 'receber'
        if (!$idAssinanteFinal) { throw new Exception("Falha ao criar novo registro de assinante."); }
    }

    // --- Etapa 3: Gerenciar Tabela 'receber' ---
    // Busca detalhes do plano para valor/descrição
    $query_plano_rec = $pdo->prepare("SELECT nome, preco_mensal, preco_anual FROM planos WHERE id = :id_plano AND id_conta = :id_conta");
    $query_plano_rec->execute([':id_plano' => $id_plano, ':id_conta' => $id_conta_corrente]);
    $plano_info_rec = $query_plano_rec->fetch(PDO::FETCH_ASSOC);
    if (!$plano_info_rec) { throw new Exception("Plano selecionado inválido ao gerar cobrança."); }
    $nome_plano = $plano_info_rec['nome'];


    // Determina valor, descrição e frequência
     if ($frequencia_escolhida == 365 && !empty($plano_info_rec['preco_anual'])) {        
         $valor_cobrar = $plano_info_rec['preco_anual'];         
         $valorF = @number_format($valor_cobrar, 2, ',', '.');
         $descricao_cobranca = "Assinatura Plano " . $plano_info_rec['nome'] . " - Anual";
         $frequencia_plano = 365;
         $nome_freq = 'Anual';
     } else {
        
         $valor_cobrar = $plano_info_rec['preco_mensal'];
         $valorF = @number_format($valor_cobrar, 2, ',', '.');
         $descricao_cobranca = "Assinatura Plano " . $plano_info_rec['nome'] . " - Mensal";
         $frequencia_plano = 30;
         $nome_freq = 'Mensal';
     }

     // Lógica para 'receber': Sempre cria um novo ao adicionar, atualiza ao editar?
     // VAMOS MANTER A LÓGICA ANTERIOR: Cria novo em ADD, Atualiza o pendente em EDIT
     if ($id_assinante_edit > 0) {
         // MODO EDIÇÃO: Atualiza a cobrança pendente mais antiga
         $query_find_rec = $pdo->prepare("SELECT id FROM receber WHERE pessoa = :pessoa AND id_conta = :id_conta AND pago = 'Não' AND tipo = 'Assinatura' ORDER BY data_venc ASC, id ASC LIMIT 1");
         $query_find_rec->execute([':pessoa' => $idClienteFinal, ':id_conta' => $id_conta_corrente]);
         $receber_para_atualizar = $query_find_rec->fetch(PDO::FETCH_ASSOC);

         if ($receber_para_atualizar) {
             $id_receber_atualizar = $receber_para_atualizar['id'];
             $query_update_rec = $pdo->prepare("UPDATE receber SET descricao = :desc, valor = :valor, subtotal = :subtotal, frequencia = :freq, data_venc = :venc, usuario_lanc = :user_lanc WHERE id = :id_receber AND id_conta = :id_conta");
             $query_update_rec->bindValue(':desc', $descricao_cobranca);
             $query_update_rec->bindValue(':valor', $valor_cobrar);
             $query_update_rec->bindValue(':subtotal', $valor_cobrar);
             $query_update_rec->bindValue(':freq', $frequencia_plano, PDO::PARAM_INT);
             $query_update_rec->bindValue(':venc', $data_vencimento);
             $query_update_rec->bindValue(':user_lanc', $id_usuario_logado, PDO::PARAM_INT);
             $query_update_rec->bindValue(':id_receber', $id_receber_atualizar, PDO::PARAM_INT);
             $query_update_rec->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
             $query_update_rec->execute();
         } else {
              error_log("Aviso EDIT: Assinante ID {$idClienteFinal} atualizado, mas nenhuma cobrança pendente encontrada para atualizar.");
              // Opcional: Criar uma nova cobrança mesmo na edição se nenhuma pendente foi achada?
              // Depende da regra de negócio. Por ora, não cria.
         }
     } else {
         // MODO ADIÇÃO: Cria a primeira cobrança
         $query_ins_rec = $pdo->prepare("INSERT INTO receber (descricao, tipo, valor, subtotal, data_lanc, data_venc, usuario_lanc, pessoa, pago, id_conta, frequencia, cliente) VALUES (:desc, :tipo, :valor, :subtotal, CURDATE(), :venc, :user_lanc, :pessoa, 'Não', :id_conta, :freq, :cliente_id)");
          $query_ins_rec->bindValue(':desc', $descricao_cobranca);
          $query_ins_rec->bindValue(':tipo', 'Assinatura');
          $query_ins_rec->bindValue(':valor', $valor_cobrar);
          $query_ins_rec->bindValue(':subtotal', $valor_cobrar);
          $query_ins_rec->bindValue(':venc', $data_vencimento);
          $query_ins_rec->bindValue(':user_lanc', $id_usuario_logado, PDO::PARAM_INT);
          $query_ins_rec->bindValue(':pessoa', $idClienteFinal, PDO::PARAM_INT); // ID do NOVO assinante
          $query_ins_rec->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
          $query_ins_rec->bindValue(':freq', $frequencia_plano, PDO::PARAM_INT);
          $query_ins_rec->bindValue(':cliente_id', $idAssinanteFinal, PDO::PARAM_INT); // ID do cliente

          $query_ins_rec->execute();
          if($query_ins_rec->rowCount() <= 0){ throw new Exception("Falha ao criar cobrança inicial."); }

          $ult_id_receber = $pdo->lastInsertId();
     }


    // --- Confirma Transação ---
    $pdo->commit();
    $response['success'] = true;
    $response['message'] = 'Assinante salvo com sucesso!';

} catch (PDOException $e) {
    $pdo->rollBack();
    // ... (Tratamento de erro PDO, incluindo UNIQUE constraints) ...
    $response['message'] = 'Erro de Banco de Dados: ' . $e->getMessage(); // Seja mais específico se possível
     error_log("Erro PDO em salvar_assinante: " . $e->getMessage() . " | Dados: " . print_r($_POST, true));
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral em salvar_assinante: " . $e->getMessage() . " | Dados: " . print_r($_POST, true));
}

echo json_encode($response);

if(isset($ult_id_receber)){

    if ($api == 'Sim') {
        $nome_sistema_maiusculo = mb_strtoupper($nome_sistema);   
        $dataF = implode('/', array_reverse(explode('-', $data_vencimento)));
        // Link de pagamento
        $link_pgto = 'https://www.markai.skysee.com.br/pagar_ass/' . $ult_id_receber;
    
        $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefone);
    
        $mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
        $mensagem .= '*Assinatura realizada com sucesso!* 😀%0A%0A';
        $mensagem .= 'Olá '.$nome.', seja bem-vindo ao nosso *CLUBE DO ASSINANTE* 👑.%0A%0A';    
        $mensagem .= '*Segue os dados da assinatura:* 📝%0A';
        $mensagem .= '*Assinante:* ' . $nome . '%0A';
        $mensagem .= '*Plano:* '.$nome_plano.' - '.$nome_freq.'%0A';
        $mensagem .= '*Valor:* R$ '.$valorF.'%0A';
        $mensagem .= '*Data de Vencimento:* ' . $dataF . '%0A%0A'; 
        if($pgto_api == 'Sim'){
            $mensagem .= '*Link para pagamento:* 🔗%0A'; 
            $mensagem .= $link_pgto; 
        }else{
            $mensagem .= '*Dados para pagamento:*%0A'; 
            $mensagem .= $dados_pagamento; 
        } 
        
    
        require('../../../../ajax/api-texto.php');

        
        $telefone = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema); 
    
        $mensagem = '*Assinatura realizada pelo site!* 👑%0A%0A';       
        $mensagem .= '*Segue os dados da assinatura:* 📝%0A';
        $mensagem .= '*Assinante:* ' . $nome . '%0A';
        $mensagem .= '*Plano:* '.$nome_plano.' - '.$nome_freq.'%0A';
        $mensagem .= '*Valor:* R$ '.$valorF.'%0A';
        $mensagem .= '*Data de Vencimento:* ' . $dataF . '%0A';   
    
        require('../../../../ajax/api-texto.php');
    }
}

?>