<?php
// Arquivo: paginas/SUA_PAGINA/listar_produtos.php (Exemplo de Caminho)
// (Este script é chamado pela função JS listarProdutos(id))

require_once("../../../conexao.php"); // Ajuste o caminho se necessário
$data_hoje = date('Y-m-d'); // Não é usado aqui, mas mantido por enquanto

@session_start();
// Validação de sessão
if (!isset($_SESSION['id_conta']) || !isset($_SESSION['id_usuario'])) {
    echo '<small class="text-danger">Sessão inválida ou expirada.</small>';
    exit;
}
$id_conta = $_SESSION['id_conta'];
// $usuario_logado = $_SESSION['id_usuario']; // Não parece ser usado na listagem

// Pega ID da comanda via POST e valida
$comanda_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// --- DEBUG ---
// error_log("listar_produtos.php chamado para comanda ID: " . $comanda_id . " / Conta ID: " . $id_conta);
// --- FIM DEBUG ---

if ($comanda_id <= 0) {
    echo '<small class="text-warning">ID da Comanda inválido para listar produtos.</small>';
    exit();
}

$total_produtos = 0; // Nome de variável mais adequado
$html_output = ''; // Inicializa a string de saída HTML

try {
    // --- Query Principal Otimizada com JOINs e Prepared Statements ---
    // Busca itens do tipo 'Venda' da tabela 'receber' para esta comanda,
    // juntando com produtos e usuários (vendedor)
    $query = $pdo->prepare("
        SELECT
            r.id, r.quantidade, r.valor, r.funcionario as id_funcionario, r.produto as id_produto, r.pago, r.data_venc,
            p.nome as nome_produto, p.estoque as estoque_atual,
            u.nome as nome_vendedor
        FROM receber r
        LEFT JOIN produtos p ON r.produto = p.id AND p.id_conta = r.id_conta -- JOIN para nome/estoque do produto
        LEFT JOIN usuarios u ON r.funcionario = u.id AND u.id_conta = r.id_conta -- JOIN para nome do vendedor
        WHERE r.tipo = 'Venda'          -- Filtra apenas por tipo Venda
          AND r.comanda = :comanda_id   -- Filtra pela comanda específica
          AND r.id_conta = :id_conta    -- Filtra pela conta da sessão
        ORDER BY r.id ASC               -- Ordena pela ordem de inserção
    ");

    $query->execute([
        ':comanda_id' => $comanda_id,
        ':id_conta' => $id_conta
    ]);

    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $total_reg = count($res);

    if ($total_reg > 0) {
        // Cabeçalho da Tabela
        $html_output = <<<HTML
        <small>
        <table class="table table-hover table-sm" id="tabela_produtos"> 
        <thead >
        <tr>
        <th>Produto (Qtd)</th>
        <th class="esc text-right">Valor Total</th>
        <th class="esc text-center">Estoque Atual</th>
        <th>Vendedor</th>
        <th class="text-center">Ações</th>
        </tr>
        </thead>
        <tbody>
HTML;

        // Loop pelos Itens/Produtos da Comanda
        foreach ($res as $item) {
            $id_item_receber = $item['id']; // ID do registro em 'receber' (para exclusão)
            $quantidade_item = (int)$item['quantidade'];
            $valor_item = floatval($item['valor']); // Valor total (Qtd * Preço Unitário já calculado e salvo em receber)
            $nome_produto_item = htmlspecialchars($item['nome_produto'] ?: 'Produto Desconhecido');
            $estoque_atual_item = ($item['estoque_atual'] !== null) ? (int)$item['estoque_atual'] : 'N/D';
            $nome_vendedor_item = htmlspecialchars($item['nome_vendedor'] ?: 'N/D'); // Vendedor (funcionário no registro receber)
            $id_produto_item = $item['id_produto']; // ID do produto (para exclusão/reversão estoque)

            $valor_item_formatado = number_format($valor_item, 2, ',', '.');

            // Soma ao total geral de produtos
            $total_produtos += $valor_item;

            // Define classe para destacar linha se vencido/não pago (opcional)
            $classe_debito = '';
            if ($item['pago'] != 'Sim' && !empty($item['data_venc']) && strtotime($item['data_venc']) < strtotime($data_hoje)) {
                $classe_debito = 'text-danger'; // Aplica cor vermelha à linha inteira
            }

            // Prepara parâmetros para a função excluirProduto JS de forma segura
            // Passar: ID do item em receber, ID da comanda, ID do produto, Quantidade vendida (para reverter estoque)
            // $onclick_excluir = sprintf(
            //     "event.preventDefault(); excluirProduto('%s', '%s')",
            //     htmlspecialchars($id_item_receber, ENT_QUOTES, 'UTF-8'),
            //     htmlspecialchars($comanda_id, ENT_QUOTES, 'UTF-8'),
            //     htmlspecialchars($id_produto_item, ENT_QUOTES, 'UTF-8'),
            //     htmlspecialchars($quantidade_item, ENT_QUOTES, 'UTF-8')
            // );

            // Monta a linha da tabela
            $html_output .= <<<HTML
            <tr class="{$classe_debito}">
                <td>{$quantidade_item}x {$nome_produto_item}</td>
                <td class="esc text-right">R$ {$valor_item_formatado}</td>
                <td class="esc text-center">{$estoque_atual_item}</td>
                <td>{$nome_vendedor_item}</td>
                <td class="text-center">
				<a href="#" onclick="event.preventDefault(); excluirProduto('{$id_item_receber}', '{$comanda_id}')" title="Excluir Venda deste Produto">
                       <i class="fa fa-trash-o text-danger"></i>
                    </a>
                    
                </td>
            </tr>
HTML;
        } // Fim foreach

        // Formata o total de produtos
        $total_produtosF = number_format($total_produtos, 2, ',', '.');

        // Fecha a tabela e adiciona o total
        $html_output .= <<<HTML
        </tbody>
        </table>
        <div class="text-right mt-2" style="margin-right: 5px;"><strong>Total Produtos:</strong> <span class="text-primary" id="total-produtos-display">R$ {$total_produtosF}</span></div>
        </small>
HTML;

        // Script para atualizar o input hidden #valor_produtos na página principal
        // Idealmente, o JS que chama calcular() pegaria esse valor da tabela, mas mantendo o padrão:
        $html_output .= "<script> $('#valor_produtos').val('{$total_produtos}'); console.log('Total Produtos Atualizado via listarProdutos:', {$total_produtos}); </script>";


    } else { // Fim if ($total_reg > 0)
        $html_output = '<small>Nenhum produto lançado nesta comanda!</small>';
        // Garante que o total seja zerado se não houver produtos
        $html_output .= "<script> $('#valor_produtos').val('0'); </script>";
    }

} catch (PDOException $e) {
    error_log("Erro ao listar produtos da comanda {$comanda_id}: " . $e->getMessage());
    $html_output = '<small class="text-danger">Erro ao carregar produtos da comanda.</small>';
    $html_output .= "<script> $('#valor_produtos').val('0'); </script>";
}

echo $html_output; // Envia o HTML gerado
?>

<script type="text/javascript">
		// --- INÍCIO: Função para Excluir Produto da Comanda ---
function excluirProduto(idItemReceber, idComanda) { 

    Swal.fire({
        title: 'Tem Certeza?',
        text: `Deseja realmente excluir este produto da comanda? O estoque será revertido.`, // Mensagem de confirmação
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostra loading (opcional, mas bom para feedback)
            Swal.fire({ title: 'Excluindo...', text: 'Aguarde...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: 'paginas/' + pag + '/excluir_produto.php', 
                method: 'POST',
                data: {
                    id_receber: idItemReceber,         
                    id_comanda: idComanda,        
                    },
                dataType: 'json', 

                success: function(response) {
                    if (response && response.success) {
                        Swal.fire(
                            'Excluído!',
                            response.message || 'Produto removido da comanda.',
                            'success'
                        );
                        // Atualiza lista de produtos e totais APÓS excluir
                        if(typeof listarProdutos === 'function') {
                            listarProdutos(idComanda); // Chama a função de listar PRODUTOS
                        } 
                        
                        if(typeof calcular === 'function') {
                            calcular();
                        }
                    } else {
                        // Se success for false ou resposta inválida
                        Swal.fire(
                            'Erro!',
                            response.message || 'Não foi possível excluir o produto.',
                            'error'
                        );
                    }
                },
                error: function(xhr) {
                    // Erro de comunicação ou PHP não retornou JSON válido
                    Swal.fire('Erro Crítico!', 'Falha na comunicação ao excluir produto. Verifique o console (F12).', 'error');
                    console.error("Erro AJAX excluirProduto:", xhr.responseText);
                }
            });
        }
    });
}
