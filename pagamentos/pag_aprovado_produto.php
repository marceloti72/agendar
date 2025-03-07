<?php 
require("../sistema/conexao.php");
$id_conta = $_GET['id_conta'];


try {
    $stmt = $pdo->prepare("SELECT * FROM config WHERE id = :id_conta");
    $stmt->bindParam(':id_conta', $id_conta, PDO::PARAM_INT);
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($config) {
        $nome_sistema = htmlspecialchars($config['nome']);
        $email_sistema = htmlspecialchars($config['email']);
        $whatsapp_sistema = htmlspecialchars($config['telefone_whatsapp']);         
        $token = htmlspecialchars($config['token']);
        $instancia = htmlspecialchars($config['instancia']);       
        $pgto_api = htmlspecialchars($config['pgto_api']);
        $api = htmlspecialchars($config['api']);  
        $username = htmlspecialchars($config['username']);  
        

        $tel_whatsapp = '55' . preg_replace('/[ ()-]+/', '', $whatsapp_sistema);
    } else {
        echo "Configurações não encontradas para a conta.";
    }
} catch (PDOException $e) {
    echo "Erro ao buscar configurações: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Preencha seus dados</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Modal -->
    <div class="modal fade" id="dadosModal" tabindex="-1" aria-labelledby="dadosModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dadosModalLabel">Informe seus dados</h5>
                </div>
                <div class="modal-body">
                    <form id="formDados" method="GET" action="">
                        <input type="hidden" name="id_produto" value="<?php echo isset($_GET['id_produto']) ? htmlspecialchars($_GET['id_produto']) : ''; ?>">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Só executa o código PHP se os dados foram enviados
    if (isset($_GET['nome']) && isset($_GET['telefone'])) {
        $id_produto = @$_GET['id_produto'];
        $nome = $_GET['nome'];
        $telefone = $_GET['telefone'];

        // Verifica se o cliente já existe
        $query = $pdo->query("SELECT * FROM clientes WHERE telefone = '$telefone' AND id_conta = '$id_conta'");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        if (count($res) <= 0) {
            $query = $pdo->prepare("INSERT INTO clientes SET nome = :nome, telefone = :telefone, data_cad = curDate(), cartoes = '0', id_conta = :id_conta");
            $query->bindValue(":nome", "$nome");
            $query->bindValue(":telefone", "$telefone");
            $query->bindValue(":id_conta", "$id_conta");
            $query->execute();
        }

        // Busca informações do produto
        $query = $pdo->query("SELECT * FROM produtos WHERE id = '$id_produto' AND id_conta = '$id_conta'");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $nome_produto = @$res[0]['nome'];
        $valor = @$res[0]['valor_venda'];
        $foto = @$res[0]['foto'];

        $url = "https://" . $_SERVER['HTTP_HOST'] . "/";

        $nome_sistema_maiusculo = mb_strtoupper($nome_sistema);
        $telefone = '55' . preg_replace('/[ ()-]+/', '', $telefone);

        $mensagem = '*' . $nome_sistema_maiusculo . '*%0A%0A';
        $mensagem .= 'Olá ' . $nome . '%0A';
        $mensagem .= '✅ *Pagamento realizado com sucesso!*%0A';
        $mensagem .= 'Produto: ' . $nome_produto . '%0A';
        $mensagem .= 'Valor: ' . $valor . '%0A%0A';
        $mensagem .= '📦 _O produto já pode ser retirado em nossa loja. Se desejar envio pelos Correios entre em contato._%0A%0A';

        if(!empty($foto)){
            require('envio_foto.php');
        }else{
            require('api-texto.php');
        }
        

        header("Location: ../site.php?u=" . urlencode($username));
        exit;
    }
    ?>

    <!-- Bootstrap JS e jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostra o modal automaticamente ao carregar a página se os dados não foram enviados
        $(document).ready(function() {
            <?php if (!isset($_GET['nome']) || !isset($_GET['telefone'])): ?>
                $('#dadosModal').modal({ backdrop: 'static', keyboard: false });
                $('#dadosModal').modal('show');
            <?php endif; ?>
        });
    </script>
</body>
</html>