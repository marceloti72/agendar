<?php
// --- paginas/agendamentos/concluir.php (MODIFICADO) ---
require_once("../../../conexao.php"); // Garanta que este caminho esteja correto
$data_atual = date('Y-m-d');

// Define o cabeçalho da resposta como JSON para interagir com SweetAlert/JS
header('Content-Type: application/json');

// Estrutura de resposta padrão
$response = [
    'status' => 'error', // 'success' ou 'error'
    'message' => 'Ocorreu um erro inesperado.',
    'detail' => '' // Detalhes adicionais (ex: info da assinatura)
];

@session_start();
// Verifica se as variáveis de sessão estão definidas antes de usá-las
$id_conta_corrente = isset($_SESSION['id_conta']) ? filter_var($_SESSION['id_conta'], FILTER_VALIDATE_INT) : null;
$usuario_logado = isset($_SESSION['id_usuario']) ? filter_var($_SESSION['id_usuario'], FILTER_VALIDATE_INT) : null;

// Valida variáveis de sessão obrigatórias
if ($id_conta_corrente === null || $usuario_logado === null) {
    $response['message'] = 'Sessão inválida ou expirada.';
    echo json_encode($response); exit;
}

// --- Recebe Dados do POST e Valida ---
$cliente_id = isset($_POST['cliente']) ? filter_var($_POST['cliente'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : 0;
$data_pgto_prevista = isset($_POST['data_pgto']) ? $_POST['data_pgto'] : '';
$id_agd = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : 0;
$valor_serv_postado = isset($_POST['valor_serv']) ? filter_var($_POST['valor_serv'], FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0.0;
$funcionario_id = isset($_POST['funcionario']) ? filter_var($_POST['funcionario'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : 0;
$servico_id = isset($_POST['servico']) ? filter_var($_POST['servico'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : 0;
$obs = isset($_POST['obs']) ? trim($_POST['obs']) : '';
$pgto_metodo = isset($_POST['pgto']) ? trim($_POST['pgto']) : '';

// Tratamento do valor restante com mais cuidado
$valor_serv_restante_postado = 0.0;
if (isset($_POST['valor_serv_agd_restante']) && trim($_POST['valor_serv_agd_restante']) !== '') {
    $valor_serv_restante_postado = filter_var($_POST['valor_serv_agd_restante'], FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    if ($valor_serv_restante_postado === false) $valor_serv_restante_postado = 0.0; // Garante que é float
}
$pgto_metodo_restante = isset($_POST['pgto_restante']) ? trim($_POST['pgto_restante']) : '';
$data_pgto_prevista_restante = isset($_POST['data_pgto_restante']) ? $_POST['data_pgto_restante'] : '';



// Validação de datas e métodos de pagamento
if (empty($data_pgto_prevista) || empty($pgto_metodo)) {
     $response['message'] = 'Data ou Método de pagamento principal não informado.';
     echo json_encode($response); exit;
}
if ($valor_serv_restante_postado > 0 && (empty($data_pgto_prevista_restante) || empty($pgto_metodo_restante))) {
     $response['message'] = 'Data ou Método de pagamento restante não informado para valor restante maior que zero.';
     echo json_encode($response); exit();
}

// --- Inicia Transação ---
$pdo->beginTransaction();

try {

    // --- 1. Busca detalhes do Serviço (Nome, Valor Base Nominal, Comissão Base) ---
    $query_s = $pdo->prepare("SELECT nome, valor, comissao FROM servicos WHERE id = :id_servico AND id_conta = :id_conta");
    $query_s->execute([':id_servico' => $servico_id, ':id_conta' => $id_conta_corrente]);
    $servico_info = $query_s->fetch(PDO::FETCH_ASSOC);
    if (!$servico_info) { throw new Exception("Serviço não encontrado (ID: {$servico_id})."); }

    $nome_servico = $servico_info['nome'];
    // Valor nominal do serviço para cálculo de comissão (vem do cadastro do serviço)
    $valor_servico_nominal_base = floatval($servico_info['valor']);
    $comissao_base_servico = floatval($servico_info['comissao']);
    $descricao_receber_padrao = $nome_servico;
    $descricao_comissao_pagar = 'Comissão - ' . $nome_servico;

    // --- 2. Calcula Comissão Final (Considera override do funcionário) ---
    $query_f = $pdo->prepare("SELECT comissao FROM usuarios WHERE id = :id_funcionario AND id_conta = :id_conta");
    $query_f->execute([':id_funcionario' => $funcionario_id, ':id_conta' => $id_conta_corrente]);
    $func_info = $query_f->fetch(PDO::FETCH_ASSOC);
    $comissao_taxa_final = $comissao_base_servico;
    if ($func_info && isset($func_info['comissao']) && $func_info['comissao'] > 0) {
        $comissao_taxa_final = floatval($func_info['comissao']);
    }

    // Busca tipo global ou usa Padrão (Assume $tipo_comissao existe em conexao.php ou config)
    global $tipo_comissao; // Tenta buscar de uma variável global
    $tipo_comissao_usado = $tipo_comissao ?? 'Porcentagem'; // Usa global ou padrão 'Porcentagem'

    $valor_comissao_calculado = 0.0;
    if ($tipo_comissao_usado == 'Porcentagem') {
        $valor_comissao_calculado = ($comissao_taxa_final * $valor_servico_nominal_base) / 100;
    } else { // Assume tipo Fixo
        $valor_comissao_calculado = $comissao_taxa_final;
    }
    $valor_comissao_calculado = round($valor_comissao_calculado, 2); // Arredonda para 2 casas decimais

    // --- 3. VERIFICAÇÃO DE ASSINATURA ---
    $coberto_pela_assinatura = false;
    $mensagem_assinatura = ''; // Mensagem para feedback
    $id_assinante_encontrado = '';
    $id_receber_ciclo_atual = null; // ID da cobrança do ciclo da assinatura
    $id_plano_servico_encontrado = null;
    $quantidade_a_usar = 1;

    // a. Cliente tem assinatura ativa?
    $query_find_ass = $pdo->prepare("SELECT id, id_plano FROM assinantes WHERE id_cliente = :id_cliente AND id_conta = :id_conta AND ativo = 1 AND data_vencimento >= CURDATE()");
    $query_find_ass->execute([':id_cliente' => $cliente_id, ':id_conta' => $id_conta_corrente]);
    $assinante_info = $query_find_ass->fetch(PDO::FETCH_ASSOC);

    if ($assinante_info) {
        $id_assinante_encontrado = $assinante_info['id'];
        $id_plano_assinante = $assinante_info['id_plano'];
        error_log("[CONCLUIR AGD {$id_agd}] Cliente {$cliente_id} é assinante (ID Ass: {$id_assinante_encontrado}, Plano: {$id_plano_assinante}). Verificando serv {$servico_id}.");		

        // b. Serviço incluído no plano?
        $query_limite = $pdo->prepare("SELECT id, quantidade FROM planos_servicos WHERE id_plano = :id_plano AND id_servico = :id_servico AND id_conta = :id_conta");
        $query_limite->execute([':id_plano' => $id_plano_assinante, ':id_servico' => $servico_id, ':id_conta' => $id_conta_corrente]);
        $limite_info = $query_limite->fetch(PDO::FETCH_ASSOC);
		
        if ($limite_info) {
            $limite_base = (int)$limite_info['quantidade']; // Limite cadastrado (geralmente mensal)
            $id_plano_servico_encontrado = $limite_info['id'];
            error_log("[CONCLUIR AGD {$id_agd}] Serviço {$servico_id} incluído. Limite base: {$limite_base}. ID PS: {$id_plano_servico_encontrado}.");

            // c. Encontra ciclo ATUAL da assinatura (cobrança PENDENTE em 'receber')
            // ** ASSUMINDO QUE 'receber.pessoa' LIGA A 'clientes.id' **
            // ** E precisamos achar a cobrança de assinatura pendente para ESTE cliente **
            $query_rec = $pdo->prepare("SELECT id, frequencia FROM receber WHERE pessoa = :id_cliente AND id_conta = :id_conta AND pago = 'Não' AND tipo = 'Assinatura' ORDER BY data_venc ASC, id ASC LIMIT 1");
            $query_rec->execute([':id_cliente' => $cliente_id, ':id_conta' => $id_conta_corrente]);
            $rec_atual = $query_rec->fetch(PDO::FETCH_ASSOC);

            if ($rec_atual) {
                $id_receber_ciclo_atual = $rec_atual['id'];
                $frequencia_ciclo = (int)$rec_atual['frequencia']; // 30=Mensal, 365=Anual etc.
                error_log("[CONCLUIR AGD {$id_agd}] Ciclo de assinatura pendente encontrado (ID Receber: {$id_receber_ciclo_atual}, Freq: {$frequencia_ciclo}).");

                // d. Calcula limite REAL do ciclo
                $limite_ciclo = $limite_base; // Padrão
                if ($frequencia_ciclo == 365 && $limite_base > 0) { $limite_ciclo = $limite_base * 12; } // Anual * 12
                elseif ($limite_base == 0) { $limite_ciclo = 0; } // Ilimitado

                // e. Conta uso atual DENTRO DESTE CICLO
                $usados_atualmente = 0;
                if ($limite_ciclo !== 0) {
                    $query_uso = $pdo->prepare("SELECT SUM(quantidade_usada) as total_usado FROM assinantes_servicos_usados WHERE id_assinante = :id_ass AND id_servico = :id_serv AND id_receber_associado = :id_rec AND id_conta = :id_conta");
                    $query_uso->execute([':id_ass' => $id_assinante_encontrado, ':id_serv' => $servico_id, ':id_rec' => $id_receber_ciclo_atual, ':id_conta' => $id_conta_corrente]);
                    $uso_info = $query_uso->fetch(PDO::FETCH_ASSOC);
                    $usados_atualmente = $uso_info ? (int)$uso_info['total_usado'] : 0;
                }

                // f. Verifica se há saldo/limite
                if ($limite_ciclo === 0 || ($usados_atualmente + $quantidade_a_usar) <= $limite_ciclo) {
                    $coberto_pela_assinatura = true;
                    $novo_uso_num = $usados_atualmente + $quantidade_a_usar;
                    $limite_texto = ($limite_ciclo === 0) ? "Ilimitado" : $limite_ciclo;
                    $mensagem_assinatura = "Coberto (Uso {$novo_uso_num}/{$limite_texto})"; // Mensagem curta para detalhe
                    error_log("[CONCLUIR AGD {$id_agd}] COBERTO. {$mensagem_assinatura}");
                } else {
                    $mensagem_assinatura = "Limite Atingido ({$usados_atualmente}/{$limite_ciclo})";
                    error_log("[CONCLUIR AGD {$id_agd}] NÃO COBERTO. {$mensagem_assinatura}");
                }
            } else {
                 $mensagem_assinatura = "Ciclo pendente não encontrado";
                 error_log("[CONCLUIR AGD {$id_agd}] ERRO: Ciclo de assinatura pendente não localizado para cliente {$cliente_id}.");
            }
        } else {
             $mensagem_assinatura = "Serviço não incluso no plano";
             error_log("[CONCLUIR AGD {$id_agd}] INFO: Serviço {$servico_id} não incluído no plano {$id_plano_assinante}.");
        }
    } else {
         // Não é assinante ativo, não faz nada aqui, $coberto_pela_assinatura continua false.
         error_log("[CONCLUIR AGD {$id_agd}] Cliente {$cliente_id} não é assinante ativo.");
    }

    // --- 4. Define Valores Finais, Status de Pagamento e Método ---
    // Inicializa com os valores postados/padrão
    $valor_final_receber = $valor_serv_postado;
    $valor_final_restante = $valor_serv_restante_postado;
    $pago_receber = 'Não';
    $data_pgto_efetivo = ''; // Ou null
    $usuario_baixa = 0; // Ou null
    $pago_restante = 'Não';
    $data_pgto_efetivo_restante = ''; // Ou null
    $usuario_baixa_restante = 0; // Ou null
    $pgto_metodo_final = $pgto_metodo;
    $pgto_metodo_final_restante = $pgto_metodo_restante;
    $descricao_final_receber = $descricao_receber_padrao;

    if ($coberto_pela_assinatura) {
        // SOBRESCREVE se coberto pela assinatura
        $valor_final_receber = 0.00;
        $valor_final_restante = 0.00; // Assinatura cobre o TOTAL
        $pago_receber = 'Sim';
        $pago_restante = 'Sim'; // Ambas partes "pagas" pela assinatura
        $data_pgto_efetivo = $data_atual;
        $data_pgto_efetivo_restante = $data_atual;
        $usuario_baixa = $usuario_logado;
        $usuario_baixa_restante = $usuario_logado;
        $pgto_metodo_final = 'Assinatura';
        $pgto_metodo_final_restante = 'Assinatura';
        $descricao_final_receber = $descricao_receber_padrao . " (Assinatura)";

    } else {
        // --- Processamento Normal de Pagamento (Taxas e Status por Data) ---

        // Aplica Taxa para a primeira parte (baseado no método e data prevista)
        if ($pgto_metodo_final) {
            $query_taxa = $pdo->prepare("SELECT taxa FROM formas_pgto where nome = :pgto and id_conta = :id_conta");
            $query_taxa->execute([':pgto' => $pgto_metodo_final, ':id_conta' => $id_conta_corrente]);
            $res_taxa = $query_taxa->fetch(PDO::FETCH_ASSOC);
            $valor_taxa = ($res_taxa) ? floatval($res_taxa['taxa']) : 0;

            global $taxa_sistema; // Assume definida em conexao.php
            if ($valor_taxa > 0 && isset($taxa_sistema) && strtotime($data_pgto_prevista) <= strtotime($data_atual)) {
                if ($taxa_sistema == 'Cliente') {
                    $valor_final_receber = $valor_serv_postado + ($valor_serv_postado * ($valor_taxa / 100));
                } else { // Empresa absorve
                    $valor_final_receber = $valor_serv_postado - ($valor_serv_postado * ($valor_taxa / 100));
                }
                $valor_final_receber = round($valor_final_receber, 2);
            } // else: mantém $valor_final_receber = $valor_serv_postado;
        }

        // Aplica Taxa para a parte restante
        if ($valor_final_restante > 0 && $pgto_metodo_final_restante) {
            $query_taxa_r = $pdo->prepare("SELECT taxa FROM formas_pgto where nome = :pgto_restante and id_conta = :id_conta");
            $query_taxa_r->execute([':pgto_restante' => $pgto_metodo_final_restante, ':id_conta' => $id_conta_corrente]);
            $res_taxa_r = $query_taxa_r->fetch(PDO::FETCH_ASSOC);
            $valor_taxa_r = ($res_taxa_r) ? floatval($res_taxa_r['taxa']) : 0;

            if ($valor_taxa_r > 0 && isset($taxa_sistema) && $data_pgto_prevista_restante && strtotime($data_pgto_prevista_restante) <= strtotime($data_atual)) {
                if ($taxa_sistema == 'Cliente') {
                    $valor_final_restante = $valor_serv_restante_postado + ($valor_serv_restante_postado * ($valor_taxa_r / 100));
                } else {
                    $valor_final_restante = $valor_serv_restante_postado - ($valor_serv_restante_postado * ($valor_taxa_r / 100));
                }
                 $valor_final_restante = round($valor_final_restante, 2);
            } // else: mantém $valor_final_restante = $valor_serv_restante_postado;
        }

        // Determina status de pagamento baseado nas DATAS PREVISTAS
        if (strtotime($data_pgto_prevista) <= strtotime($data_atual)) {
            $pago_receber = 'Sim';
            $data_pgto_efetivo = $data_pgto_prevista;
            $usuario_baixa = $usuario_logado;
        } // else: mantém 'Não', '', 0

        if ($valor_final_restante > 0) {
             if ($data_pgto_prevista_restante && strtotime($data_pgto_prevista_restante) <= strtotime($data_atual)) {
                 $pago_restante = 'Sim';
                 $data_pgto_efetivo_restante = $data_pgto_prevista_restante;
                 $usuario_baixa_restante = $usuario_logado;
             } // else: mantém 'Não', '', 0
        } else {
             $pago_restante = 'N/A'; // Não aplicável se valor 0
        }

    } // Fim do processamento normal

    // --- 5. Lançamentos no Banco de Dados ---
    $id_receber_principal = null; // ID do lançamento principal em 'receber'

    // a. REGISTRA USO (somente se coberto)
    if ($coberto_pela_assinatura) {
        if(empty($id_assinante_encontrado) || empty($id_receber_ciclo_atual) || empty($id_plano_servico_encontrado)){
             throw new Exception("Erro interno: IDs faltando para registrar uso coberto.");
        }
        $query_insert_uso = $pdo->prepare("INSERT INTO assinantes_servicos_usados
            (id_assinante, id_servico, id_plano_servico, id_receber_associado, quantidade_usada, data_uso, id_usuario_registro, id_conta, observacao)
            VALUES
            (:id_ass, :id_serv, :id_ps, :id_rec, :qtd, NOW(), :id_user, :id_conta, :obs)");
        $query_insert_uso->execute([
            ':id_ass' => $id_assinante_encontrado,
            ':id_serv' => $servico_id,
            ':id_ps' => $id_plano_servico_encontrado,
            ':id_rec' => $id_receber_ciclo_atual, // ID do CICLO da assinatura
            ':qtd' => $quantidade_a_usar,
            ':id_user' => $usuario_logado,
            ':id_conta' => $id_conta_corrente,
            ':obs' => "Agendamento ID: {$id_agd} (Coberto)"
        ]);
        if ($query_insert_uso->rowCount() <= 0) { throw new Exception("Falha ao registrar uso do serviço."); }
        error_log("[CONCLUIR AGD {$id_agd}] Uso registrado em assinantes_servicos_usados.");
    }

    // b. Lança Conta a Receber - Parte RESTANTE (somente se houver valor)
    if ($valor_final_restante > 0) {
        // ** CONFIRME O NOME DA COLUNA: 'pessoa' ou 'cliente' para ligar a clientes.id **
        $query_rec_res = $pdo->prepare("INSERT INTO receber
            (descricao, tipo, valor, data_lanc, data_venc, data_pgto, usuario_lanc, usuario_baixa, foto, pessoa, cliente, pago, servico, funcionario, obs, pgto, id_agenda, id_conta, referencia, cliente)
            VALUES
            (:desc, 'Serviço', :val, CURDATE(), :dvenc, :dpgto, :user_lanc, :user_baixa, 'sem-foto.jpg', :pessoa, :cliente, :pago, :serv, :func, :obs, :pgto, :id_agd, :idc, :ref, :cliente)");
        $query_rec_res->execute([
            ':desc' => $nome_servico . ' (Restante)',
            ':val' => $valor_final_restante,
            ':dvenc' => $data_pgto_prevista_restante,
            ':dpgto' => $data_pgto_efetivo_restante ?: null,
            ':user_lanc' => $usuario_logado,
            ':user_baixa' => $usuario_baixa_restante ?: null,
            ':pessoa' => $cliente_id, 
            ':cliente' => $id_assinante_encontrado,
            ':pago' => $pago_restante,
            ':serv' => $servico_id,
            ':func' => $funcionario_id,
            ':obs' => $obs,
            ':pgto' => $pgto_metodo_final_restante,
            ':id_agd' => $id_agd,
            ':idc' => $id_conta_corrente,
            ':ref' => $id_agd,
            ':cliente' => $id_assinante_encontrado

        ]);
         error_log("[CONCLUIR AGD {$id_agd}] Lançamento restante (R$ {$valor_final_restante}) inserido em 'receber'.");
    }

    // c. Lança/Atualiza Conta a Receber - Parte PRINCIPAL    
	$query_rec_princ = $pdo->prepare("INSERT INTO receber
		(descricao, tipo, valor, data_lanc, data_venc, data_pgto, usuario_lanc, usuario_baixa, foto, pessoa, pago, servico, funcionario, obs, pgto, id_agenda, id_conta, referencia, cliente)
		VALUES
		(:desc, 'Serviço', :val, CURDATE(), :dvenc, :dpgto, :user_lanc, :user_baixa, 'sem-foto.jpg', :pessoa, :pago, :serv, :func, :obs, :pgto, :id_agd, :idc, :ref, :cliente)");
		$query_rec_princ->execute([
		':desc' => $descricao_final_receber,
		':val' => $valor_final_receber, // Pode ser 0.00
		':dvenc' => $data_pgto_prevista,
		':dpgto' => $data_pgto_efetivo ?: null,
		':user_lanc' => $usuario_logado,
		':user_baixa' => $usuario_baixa ?: null,
		':pessoa' => $cliente_id, // Assume 'pessoa'
		':pago' => $pago_receber,
		':serv' => $servico_id,
		':func' => $funcionario_id,
		':obs' => $obs,
		':pgto' => $pgto_metodo_final,
		':id_agd' => $id_agd,
		':idc' => $id_conta_corrente,
		':ref' => $id_agd,
		':cliente' => $id_assinante_encontrado
	]);
	$id_receber_principal = $pdo->lastInsertId();
	if (!$id_receber_principal) { throw new Exception("Falha crítica ao inserir registro principal em 'receber'."); }
		error_log("[CONCLUIR AGD {$id_agd}] Lançamento principal 'receber' (ID: {$id_receber_principal}) INSERIDO. Valor: {$valor_final_receber}, Pago: {$pago_receber}.");
    

    // d. Lança Conta a Pagar - Comissão
    $lancar_comissao_agora = false;
    if ($pago_receber == 'Sim') { // Se a parte principal foi paga (hoje ou via assinatura)
        $lancar_comissao_agora = true;
        $data_base_comissao = $data_pgto_efetivo; // Usa data do pagamento efetivo
    } else {
        global $lanc_comissao; // Assume definida em conexao.php ('Sempre' ou 'Pago')
        if (isset($lanc_comissao) && $lanc_comissao == 'Sempre') {
            $lancar_comissao_agora = true;
            $data_base_comissao = $data_pgto_prevista; // Usa data prevista se lança sempre
        }
    }

    if ($lancar_comissao_agora && $valor_comissao_calculado > 0 && $id_receber_principal) {
         // ** CONFIRME SE 'pagar' tem a coluna 'id_ref' **
         $query_pagar = $pdo->prepare("INSERT INTO pagar
             (descricao, tipo, valor, data_lanc, data_venc, usuario_lanc, foto, pago, funcionario, servico, cliente, id_ref, id_conta)
             VALUES
             (:desc, 'Comissão', :val, CURDATE(), :dvenc, :user_lanc, 'sem-foto.jpg', 'Não', :func, :serv, :cli, :id_ref, :id_conta)");
         $query_pagar->execute([
            ':desc' => $descricao_comissao_pagar,
            ':val' => $valor_comissao_calculado, // Valor calculado sobre o nominal
            ':dvenc' => $data_base_comissao, // Vencimento baseado em quando foi pago ou previsto
            ':user_lanc' => $usuario_logado,
            ':func' => $funcionario_id,
            ':serv' => $servico_id,
            ':cli' => $cliente_id,
            ':id_ref' => $id_receber_principal, // Link com 'receber'
            ':id_conta' => $id_conta_corrente
        ]);
        if ($query_pagar->rowCount() <= 0) { throw new Exception("Falha ao lançar comissão na tabela 'pagar'."); }
        error_log("[CONCLUIR AGD {$id_agd}] Comissão (R$ {$valor_comissao_calculado}) lançada em 'pagar', ref ID Receber: {$id_receber_principal}.");
    } else {
        error_log("[CONCLUIR AGD {$id_agd}] Comissão NÃO lançada (Lançar?: {$lancar_comissao_agora}, Valor: {$valor_comissao_calculado}, ID Receber: {$id_receber_principal}).");
    }
    
    // Atualização Cliente (Cartões, Data Retorno)
    $query_cli_data = $pdo->prepare("SELECT cartoes, nome, telefone FROM clientes WHERE id = :id AND id_conta = :idc"); // Pega nome e tel aqui
    $query_cli_data->execute([':id' => $cliente_id, ':idc' => $id_conta_corrente]);
    $cli_data = $query_cli_data->fetch(PDO::FETCH_ASSOC);
    $total_cartoes_atual = $cli_data ? (int)$cli_data['cartoes'] : 0;
    $nome_cliente_final = $cli_data ? $cli_data['nome'] : 'Cliente';
    $telefone_cliente = $cli_data ? $cli_data['telefone'] : '';


    $query_srv_ret = $pdo->prepare("SELECT dias_retorno FROM servicos WHERE id = :id AND id_conta = :idc");
    $query_srv_ret->execute([':id' => $servico_id, ':idc' => $id_conta_corrente]);
    $srv_data = $query_srv_ret->fetch(PDO::FETCH_ASSOC);
    $dias_retorno = $srv_data ? (int)$srv_data['dias_retorno'] : 0;

    global $quantidade_cartoes; // Assume definida em conexao.php
    $cartoes_fidelidade_novo = $total_cartoes_atual;
    if (isset($quantidade_cartoes) && $quantidade_cartoes > 0) {
         if ($total_cartoes_atual >= $quantidade_cartoes) {
             $cartoes_fidelidade_novo = 0;
         } else {
             $cartoes_fidelidade_novo = $total_cartoes_atual + 1;
             if ($cartoes_fidelidade_novo == $quantidade_cartoes && !empty($telefone_cliente)) {
                 // Lógica para ENVIAR MENSAGEM de cartão fidelidade preenchido
                 global $nome_sistema, $texto_fidelidade; // Assumindo que existem
                 if(isset($nome_sistema, $texto_fidelidade)){
                     try {
                         $telefone_api = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);
                         $nome_sistema_maiusculo = mb_strtoupper($nome_sistema);
                         $mensagem_fidelidade = '*' . $nome_sistema_maiusculo . '* %0A %0A';
                         $mensagem_fidelidade .= $texto_fidelidade . '🎁';

						 $mensagem = str_replace("%0A", "\n", $mensagem_fidelidade); 

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
						'to' => $telefone_api,      
						'message' => $mensagem,
						),
						));
						
						$response = curl_exec($curl);
						curl_close($curl);
						$response = json_decode($response, true);
                         
                         error_log("[CONCLUIR AGD {$id_agd}] Tentaria enviar msg fidelidade para {$telefone_api}");
                     } catch(Exception $apiErr){
                          error_log("[CONCLUIR AGD {$id_agd}] Erro ao tentar enviar msg fidelidade: ".$apiErr->getMessage());
                     }
                 }
             }
         }
    }

    $data_retorno_calculada = date('Y-m-d', strtotime("+$dias_retorno days", strtotime($data_atual)));

    $query_upd_cli = $pdo->prepare("UPDATE clientes SET cartoes = :cartoes, data_retorno = :dret, ultimo_servico = :serv, alertado = 'Não' where id = :id and id_conta = :idc");
    $query_upd_cli->execute([
        ':cartoes' => $cartoes_fidelidade_novo,
        ':dret' => $data_retorno_calculada,
        ':serv' => $servico_id,
        ':id' => $cliente_id,
        ':idc' => $id_conta_corrente
    ]);
    error_log("[CONCLUIR AGD {$id_agd}] Dados do cliente {$cliente_id} atualizados.");


    // --- 7. Lógica de Mensagem de Satisfação (Opcional) ---
    global $satisfacao, $url, $username, $nome_sistema; // Assumindo que existem
    if (isset($satisfacao) && $satisfacao == 'Sim' && !empty($telefone_cliente) && isset($url, $username, $nome_sistema)) {
        try {
            $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);
            $nome_cliente_trim = trim($nome_cliente_final);
            $link_agenda = $url . 'agendar/agendamentos?u=' . $username;
            $nome_sistema_maiusculo = mb_strtoupper($nome_sistema);
            $data_mensagem_agendada = $data_retorno_calculada . ' 08:00:00'; // Agenda para 8h da data de retorno

            $mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
            $mensagem .= 'Olá *' . $nome_cliente_trim . '*, tudo bem! 😃%0A%0A';
            $mensagem .= 'Queremos ouvir você!%0A';
            $mensagem .= '✅Como foi seu último serviço de *' . $nome_servico . '* conosco?%0A';
            $mensagem .= '✅Você teria alguma sugestão de melhoria?%0A%0A';
            $mensagem .= 'Você é muito importante pra gente!%0A';   
            $mensagem .= '📆Acesse e agende: ' . $link_agenda . '%0A';

			
            $data_mensagem_obj = new DateTime($data_mensagem_agendada);
			$data_mensagem_obj->modify("-$antAgendamento hours");
			$data_mensagem = $data_mensagem_obj->format('Y-m-d H:i:s');

            $mensagem = str_replace("%0A", "\n", $mensagem);
		
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
				'to' => $telefone,
				'message' => $mensagem,        
				'agendamento' => $data_mensagem
				),
			));
			
			$response = curl_exec($curl);  
			
			curl_close($curl);
			$response = json_decode($response, true);		
			//$hash = $response['id'];

            error_log("[CONCLUIR AGD {$id_agd}] Tentaria agendar msg satisfação para {$telefone} em {$data_mensagem_agendada}");

        } catch(Exception $apiErrSat){
            error_log("[CONCLUIR AGD {$id_agd}] Erro ao tentar agendar msg satisfação: ".$apiErrSat->getMessage());
        }
    }

    // --- Commit Transaction ---
    $pdo->commit();
    error_log("[CONCLUIR AGD {$id_agd}] Transação COMMITADA com sucesso.");

    // --- Resposta Final (JSON) ---
    $response['status'] = 'success';
    $response['message'] = 'Pagamento Concluído com Sucesso!';
    if ($coberto_pela_assinatura) {
        $response['message'] = 'Serviço Coberto pela Assinatura!'; // Mensagem principal mais específica
        $response['detail'] = $mensagem_assinatura; // Ex: "(Uso 2/Ilimitado)" ou "(Uso 5/10)"
    } elseif (!empty($mensagem_assinatura)) {
         // Não coberto, mas houve alguma interação com assinatura (ex: limite atingido)
         $response['detail'] = "Assinante: " . $mensagem_assinatura;
    }

    echo json_encode($response);


} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("[CONCLUIR AGD {$id_agd}] ERRO PDO: " . $e->getMessage() . " | Dados POST: " . print_r($_POST, true));
    $response['message'] = "Erro de Banco de Dados ao concluir. Verifique os logs."; // Mensagem mais genérica para o usuário
    $response['detail'] = $e->getMessage(); // Pode incluir o erro no detalhe para debug (ou remover em produção)
    echo json_encode($response);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("[CONCLUIR AGD {$id_agd}] ERRO GERAL: " . $e->getMessage() . " | Dados POST: " . print_r($_POST, true));
    $response['message'] = "Erro ao processar conclusão: " . $e->getMessage();
    echo json_encode($response);
}

?>