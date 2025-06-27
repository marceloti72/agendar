<?php
// --- Arquivo: paginas/SUA_PAGINA/inserir_servico.php (Exemplo de Caminho) ---
// Este script registra um serviço, verifica assinatura, cria comanda se necessário,
// lança cobrança (valor 0 se coberto), registra uso, atualiza total da comanda,
// e lança comissão.

// --- Configuração Inicial e Segurança ---
require_once("../../../conexao.php"); // Ajuste o caminho conforme necessário
@session_start(); // Inicia ou continua a sessão

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

// Estrutura de resposta padrão
$response = [
    'success' => false,
    'message' => 'Erro desconhecido ao processar solicitação.',
    'valor_cobrado' => null,
    'tipo_registro' => 'Erro',
    'detalhe_assinatura' => null,
    'nova_comanda_id' => null
];

// --- Validações Iniciais (Método e Sessão) ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método de requisição inválido.';
    echo json_encode($response); exit;
}
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_conta'])) {
    $response['message'] = 'Sessão inválida ou expirada. Faça login novamente.';
    echo json_encode($response); exit;
}

$id_conta_corrente = $_SESSION['id_conta'];
$usuario_logado = $_SESSION['id_usuario']; // Usuário que está registrando

// --- Recebe Dados do POST e Valida Tipos/Existência ---
$cliente_id = isset($_POST['cliente']) ? filter_var($_POST['cliente'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : 0;
$comanda_id_recebido = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : 0; // Permite 0 para nova comanda
$funcionario_id = isset($_POST['funcionario']) ? filter_var($_POST['funcionario'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : 0;
$servico_id = isset($_POST['servico']) ? filter_var($_POST['servico'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : 0;
$quantidade_a_usar = 1; // Quantidade do serviço sendo usado (normalmente 1)

// Valida se os IDs principais foram informados e são válidos
if (!$cliente_id || !$funcionario_id || !$servico_id || $comanda_id_recebido < 0) {
    $response['message'] = 'Dados inválidos: Cliente, Funcionário, Serviço ou ID da Comanda não informado corretamente.';
    echo json_encode($response); exit;
}

// --- Inicia Transação do Banco de Dados ---
$pdo->beginTransaction();

try {
    // Define o ID da comanda a ser usado (existente ou novo)
    $comanda_id_final = ($comanda_id_recebido > 0) ? $comanda_id_recebido : 0;

    // ***** INÍCIO: LÓGICA PARA CRIAR COMANDA SE NECESSÁRIO *****
    if ($comanda_id_final <= 0) {
        // Se ID é 0, CRIA uma nova comanda
        error_log("Info: Criando nova comanda para cliente ID {$cliente_id} pela inserção do serviço ID {$servico_id}");

        // AJUSTE colunas e tabela 'comandas' conforme sua estrutura
        $query_nova_comanda = $pdo->prepare("INSERT INTO comandas
            (cliente, status, data, hora, funcionario, id_conta, valor)
            VALUES
            (:cliente, :status, CURDATE(), CURTIME(), :usuario, :id_conta, 0)"); // Inicia com valor 0

        $query_nova_comanda->execute([
            ':cliente' => $cliente_id,
            ':status' => 'Aberta', // Status inicial da comanda
            ':usuario' => $usuario_logado, // Quem abriu a comanda
            ':id_conta' => $id_conta_corrente
        ]);

        $comanda_id_final = $pdo->lastInsertId(); // Pega o ID da comanda recém-criada
        if (!$comanda_id_final || $comanda_id_final <= 0) { // Verifica se o ID é válido
            throw new Exception("Falha crítica ao criar o registro da nova comanda.");
        }
        $response['nova_comanda_id'] = $comanda_id_final; // Informa ao JS o novo ID
        error_log("Info: Nova comanda criada com ID: " . $comanda_id_final);

    } else {
         error_log("Info: Usando comanda existente ID: " . $comanda_id_final);
    }
    // ***** FIM: LÓGICA PARA CRIAR COMANDA SE NECESSÁRIO *****


    // --- 1. Busca comanda ---
    $query_c = $pdo->prepare("SELECT id FROM agendamentos WHERE comanda_id = :id_comanda AND id_conta = :id_conta");
    $query_c->execute([':id_comanda' => $comanda_id_recebido, ':id_conta' => $id_conta_corrente]);
    $agenda_info = $query_c->fetch(PDO::FETCH_ASSOC);    
    $agenda_id = $agenda_info['id'];

    // --- 1. Busca detalhes do Serviço ---
    $query_s = $pdo->prepare("SELECT nome, valor, comissao FROM servicos WHERE id = :id_servico AND id_conta = :id_conta");
    $query_s->execute([':id_servico' => $servico_id, ':id_conta' => $id_conta_corrente]);
    $servico_info = $query_s->fetch(PDO::FETCH_ASSOC);
    if (!$servico_info) { throw new Exception("Serviço não encontrado (ID: {$servico_id})."); }

    $valor_servico_original = $servico_info['valor']; // Valor original para total e comissão
    $comissao_servico = $servico_info['comissao'];
    $nome_servico = $servico_info['nome'];
    $descricao_pagar = 'Comissão - ' . $nome_servico; // Descrição para comissão

    // --- 2. Calcula Comissão ---
    $query_f = $pdo->prepare("SELECT comissao FROM usuarios WHERE id = :id_funcionario AND id_conta = :id_conta");
    $query_f->execute([':id_funcionario' => $funcionario_id, ':id_conta' => $id_conta_corrente]);
    $func_info = $query_f->fetch(PDO::FETCH_ASSOC);
    $comissao_taxa = $comissao_servico; // Taxa base
    if ($func_info && isset($func_info['comissao']) && $func_info['comissao'] > 0) { $comissao_taxa = $func_info['comissao']; } // Sobrescreve se houver taxa do funcionário
    global $tipo_comissao; if (!isset($tipo_comissao)) $tipo_comissao = $tipo_comissao_global ?? 'Porcentagem'; // Busca tipo global ou usa Padrão
    $valor_comissao = ($tipo_comissao == 'Porcentagem') ? (($comissao_taxa * $valor_servico_original) / 100) : $comissao_taxa; // Calcula valor final

    // --- 3. VERIFICAÇÃO DE ASSINATURA ---
    $coberto_pela_assinatura = false;
    $mensagem_assinatura = '';
    $id_assinante_encontrado = null;
    $id_receber_ciclo_atual = null; // ID da COBRANÇA da assinatura
    $id_plano_servico_encontrado = null; // ID da linha em planos_servicos

    // a. Cliente tem assinatura ativa? (ativo = 1 e não vencida)
    $query_find_ass = $pdo->prepare("SELECT id, id_plano FROM assinantes WHERE id_cliente = :id_cliente AND id_conta = :id_conta AND ativo = 1 AND data_vencimento >= CURDATE()");
    $query_find_ass->execute([':id_cliente' => $cliente_id, ':id_conta' => $id_conta_corrente]);
    $assinante_info = $query_find_ass->fetch(PDO::FETCH_ASSOC);

    if ($assinante_info) { // É assinante ativo
        $id_assinante_encontrado = $assinante_info['id'];
        $id_plano_assinante = $assinante_info['id_plano'];

        // b. Serviço está incluído no plano? Qual o limite base (mensal)?
        $query_limite = $pdo->prepare("SELECT id, quantidade FROM planos_servicos WHERE id_plano = :id_plano AND id_servico = :id_servico AND id_conta = :id_conta");
        $query_limite->execute([':id_plano' => $id_plano_assinante, ':id_servico' => $servico_id, ':id_conta' => $id_conta_corrente]);
        $limite_info = $query_limite->fetch();

        if ($limite_info) { // Serviço incluído
            $limite_base = (int)$limite_info['quantidade'];
            $id_plano_servico_encontrado = $limite_info['id'];

            // c. Encontra ciclo atual (cobrança pendente da ASSINATURA)
            // *** VERIFIQUE A FK: 'cliente' ou 'pessoa' para ligar a assinantes.id? ***
            $query_rec = $pdo->prepare("SELECT id, frequencia FROM receber WHERE cliente = :id_ass AND id_conta = :id_conta AND pago = 'Não' AND tipo = 'Assinatura' ORDER BY data_venc ASC, id ASC LIMIT 1");
            $query_rec->execute([':id_ass' => $id_assinante_encontrado, ':id_conta' => $id_conta_corrente]);
            $rec_atual = $query_rec->fetch();

            if ($rec_atual) { // Ciclo encontrado
                $id_receber_ciclo_atual = $rec_atual['id'];
                $frequencia_ciclo = (int)$rec_atual['frequencia'];

                // d. Calcula limite real do ciclo (anual * 12)
                $limite_ciclo = $limite_base;
                if ($frequencia_ciclo == 365 && $limite_base > 0) { $limite_ciclo = $limite_base * 12; }
                elseif ($limite_base == 0) { $limite_ciclo = 0; }

                // e. Conta uso atual neste ciclo
                $usados_atualmente = 0;
                if ($limite_ciclo !== 0) { // Só conta se não for ilimitado
                    $query_uso = $pdo->prepare("SELECT SUM(quantidade_usada) as total_usado FROM assinantes_servicos_usados WHERE id_assinante = :id_ass AND id_servico = :id_serv AND id_receber_associado = :id_rec AND id_conta = :id_conta");
                    $query_uso->execute([':id_ass' => $id_assinante_encontrado, ':id_serv' => $servico_id, ':id_rec' => $id_receber_ciclo_atual, ':id_conta' => $id_conta_corrente]);
                    $uso_info = $query_uso->fetch();
                    $usados_atualmente = $uso_info ? (int)$uso_info['total_usado'] : 0;
                }

                // f. Verifica se há saldo
                if ($limite_ciclo === 0 || ($usados_atualmente + $quantidade_a_usar) <= $limite_ciclo) {
                    $coberto_pela_assinatura = true;
                    $novo_uso_num = $usados_atualmente + $quantidade_a_usar;
                    $limite_texto = ($limite_ciclo === 0) ? "Ilimitado" : $limite_ciclo;
                    $mensagem_assinatura = "(Uso: {$novo_uso_num} / {$limite_texto})";
                } else { $mensagem_assinatura = "(Limite Atingido: {$usados_atualmente} / {$limite_ciclo})"; }
            } else { $mensagem_assinatura = "(Ciclo Assinatura não localizado)";}
        } // else: serviço não incluído no plano
    } // else: não é assinante ativo

    // --- 4. Ação: INSERE EM RECEBER, REGISTRA USO (SE COBERTO), ATUALIZA COMANDA, INSERE COMISSÃO ---

    // Define valor e descrição para 'receber'
    $valor_final_receber = $coberto_pela_assinatura ? 0.00 : $valor_servico_original;
    $descricao_final_receber = $coberto_pela_assinatura ? $nome_servico . " (Assinatura)" : $nome_servico;
    $pago_receber = 'Não';
    $tipo_receber = 'Serviço';

    // a. INSERT into 'receber' (Sempre insere, com valor 0 ou normal)
    // *** Verifique os nomes das colunas da sua tabela 'receber' ***
    $query_receber = $pdo->prepare("INSERT INTO receber SET descricao = :desc, tipo = :tipo, valor = :val, data_lanc = CURDATE(), data_venc = CURDATE(), usuario_lanc = :user_lanc, foto = 'sem-foto.jpg', pessoa = :pessoa, cliente = :cli, pago = :pago, servico = :serv, funcionario = :func, func_comanda = :user_comanda, comanda = :comanda_id, id_conta = :id_conta, valor2 = :val, referencia = :referencia, id_agenda = :id_agenda");
    // Ajuste 'cliente' para 'pessoa' se necessário. Removi colunas não essenciais.
    $query_receber->bindValue(':desc', $descricao_final_receber);
    $query_receber->bindValue(':tipo', $tipo_receber);
    $query_receber->bindValue(':val', $valor_final_receber); // Pode ser 0.00
    $query_receber->bindValue(':user_lanc', $usuario_logado);
    $query_receber->bindValue(':cli', $id_assinante_encontrado); 
    $query_receber->bindValue(':pessoa', $cliente_id); // FK para CLIENTES
    $query_receber->bindValue(':pago', $pago_receber);
    $query_receber->bindValue(':serv', $servico_id);
    $query_receber->bindValue(':func', $funcionario_id);
    $query_receber->bindValue(':user_comanda', $usuario_logado); // Usuário que lançou na comanda
    $query_receber->bindValue(':comanda_id', $comanda_id_final, PDO::PARAM_INT); 
    $query_receber->bindValue(':referencia', $agenda_id, PDO::PARAM_INT); 
    $query_receber->bindValue(':id_agenda', $agenda_id, PDO::PARAM_INT);
    $query_receber->bindValue(':id_conta', $id_conta_corrente);    
    $query_receber->execute();
    $ult_id_receber = $pdo->lastInsertId(); // ID do registro em RECEBER (para ligar comissão)
    if (!$ult_id_receber) { throw new Exception("Falha ao registrar serviço na cobrança."); }

    // b. Se foi coberto, INSERE registro de uso na tabela específica
    if ($coberto_pela_assinatura) {
        if(empty($id_assinante_encontrado) || empty($id_receber_ciclo_atual)){ throw new Exception("Erro interno: IDs faltando para registrar uso coberto."); }
        $query_insert_uso = $pdo->prepare("INSERT INTO assinantes_servicos_usados (id_assinante, id_servico, id_plano_servico, id_receber_associado, quantidade_usada, data_uso, id_usuario_registro, id_conta, observacao) VALUES (:id_ass, :id_serv, :id_ps, :id_rec, :qtd, NOW(), :id_user, :id_conta, :obs)");
        // Binds...
        $query_insert_uso->bindValue(':id_ass', $id_assinante_encontrado, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_serv', $servico_id, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_ps', $id_plano_servico_encontrado, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_rec', $id_receber_ciclo_atual, PDO::PARAM_INT); // ID do CICLO da assinatura
        $query_insert_uso->bindValue(':qtd', $quantidade_a_usar, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_user', $usuario_logado, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
        $query_insert_uso->bindValue(':obs', "Comanda ID:{$comanda_id_final} (Coberto)", PDO::PARAM_STR); // Usa ID final da comanda
        $query_insert_uso->execute();
        if ($query_insert_uso->rowCount() <= 0) { throw new Exception("Falha ao registrar uso do serviço."); }
    }

    // ***** INÍCIO: ATUALIZAR VALOR TOTAL DA COMANDA *****
    // Atualiza o valor na tabela 'comandas', adicionando o VALOR FINAL A RECEBER (pode ser 0 se coberto)
    $query_update_comanda = $pdo->prepare("UPDATE comandas SET valor = valor + :valor_add WHERE id = :comanda_id AND id_conta = :id_conta");    
    $query_update_comanda->bindValue(':valor_add', $valor_final_receber); // Usa o valor que vai para 'receber' (0 ou original)
    // --- FIM CORREÇÃO ---
    $query_update_comanda->bindValue(':comanda_id', $comanda_id_final, PDO::PARAM_INT);
    $query_update_comanda->bindValue(':id_conta', $id_conta_corrente, PDO::PARAM_INT);
    $query_update_comanda->execute();
    if ($query_update_comanda->rowCount() <= 0) {
        error_log("Aviso: Não foi possível atualizar o valor total da comanda ID {$comanda_id_final}. Comanda existe?");
    } else {
         // Log agora mostra o valor real que foi somado (pode ser 0)
         error_log("Info: Valor da comanda ID {$comanda_id_final} atualizado com +{$valor_final_receber}.");
    }
    // ***** FIM: ATUALIZAR VALOR TOTAL DA COMANDA *****


    // d. INSERE comissão (ligada ao $ult_id_receber, com valor original da comissão)
    $query_pagar = $pdo->prepare("INSERT INTO pagar SET descricao = :desc, tipo = 'Comissão', valor = :val, data_lanc = CURDATE(), data_venc = CURDATE(), usuario_lanc = :user_lanc, foto = 'sem-foto.jpg', pago = 'Não', funcionario = :func, servico = :serv, cliente = :cli, id_ref = :id_ref, id_conta = :id_conta, comanda = :comanda");
    // Binds...
    $query_pagar->bindValue(':desc', $descricao_pagar);
    $query_pagar->bindValue(':val', $valor_comissao); // Comissão calculada sobre valor original
    $query_pagar->bindValue(':user_lanc', $usuario_logado);
    $query_pagar->bindValue(':func', $funcionario_id);
    $query_pagar->bindValue(':serv', $servico_id);
    $query_pagar->bindValue(':cli', $cliente_id);
    $query_pagar->bindValue(':id_ref', $ult_id_receber); // Link com o 'receber' criado
    $query_pagar->bindValue(':id_conta', $id_conta_corrente);
    $query_pagar->bindValue(':comanda', $comanda_id_recebido);
    $query_pagar->execute();
    if ($query_pagar->rowCount() <= 0) { throw new Exception("Falha ao lançar comissão."); }

    // e. Define a resposta JSON final
    $response['success'] = true;
    $response['valor_cobrado'] = $valor_final_receber; // Será 0.00 se coberto
    $response['tipo_registro'] = $coberto_pela_assinatura ? 'Assinante' : 'Servico';
    $response['detalhe_assinatura'] = trim($mensagem_assinatura);
    $response['message'] = $coberto_pela_assinatura ? 'Serviço Coberto pela Assinatura' : 'Serviço Lançado com Sucesso';
    // $response['nova_comanda_id'] já foi definido se aplicável

    // --- Commit Transaction ---
    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro DB: ' . $e->getMessage();
    error_log("Erro PDO no registro de serviço/assinatura: " . $e->getMessage() . " | Dados: " . print_r($_POST, true));
    $response['success'] = false; // Garante false
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro Geral no registro de serviço/assinatura: " . $e->getMessage() . " | Dados: " . print_r($_POST, true));
    $response['success'] = false; // Garante false
}

// --- Final JSON Output ---
echo json_encode($response);
?>