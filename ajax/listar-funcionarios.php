<?php 
require_once("../sistema/conexao.php");
@session_start();
$id_conta = @$_SESSION['id_conta'];

$serv = $_POST['serv'];

// Coloque a opção padrão aqui também, caso a lógica JS precise dela
// echo '<option value="">Selecione um Profissional</option>';

// Query para buscar funcionários ativos que atendem
$query = $pdo->prepare("SELECT id, nome, foto FROM usuarios WHERE (ativo = 'Sim' OR ativo = 'teste') AND atendimento = 'Sim' AND id_conta = :id_conta ORDER BY nome ASC");
$query->bindValue(':id_conta', $id_conta);
$query->execute();
$res = $query->fetchAll(PDO::FETCH_ASSOC);

if (count($res) > 0) {
    for ($i = 0; $i < count($res); $i++) {
        $nome_func = $res[$i]['nome'];
        $func = $res[$i]['id'];
        $foto_func = $res[$i]['foto']; // Nome do arquivo da foto

        // Verifica se este funcionário realiza o serviço selecionado
        $query2 = $pdo->prepare("SELECT id FROM servicos_func WHERE servico = :servico AND funcionario = :funcionario AND id_conta = :id_conta");
        $query2->bindValue(':servico', $serv);
        $query2->bindValue(':funcionario', $func);
        $query2->bindValue(':id_conta', $id_conta);
        $query2->execute();
        $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);

        if (count($res2) > 0) {
            // Monta o caminho completo da imagem - **AJUSTE ESTE CAMINHO**
            $caminho_foto = 'sistema/painel/img/perfil/' . ($foto_func ?: 'sem-foto.jpg'); // Usa sem-foto.jpg se não houver foto

            // Gera a opção com o atributo data-image
            echo '<option value="' . htmlspecialchars($func) . '" data-image="' . htmlspecialchars($caminho_foto) . '">' . htmlspecialchars($nome_func) . '</option>';
        }
    }
}
// Não é necessário o else aqui, o JS adiciona a opção padrão se nada for retornado.
?>

<script>


// --- Garanta que Select2 seja REINICIALIZADO após carregar opções via AJAX ---
// Modifique sua função listarFuncionarios para fazer isso:



// Chame listarFuncionarios() no carregamento inicial se um serviço padrão for selecionado
// $(document).ready(function(){ ... listarFuncionarios(); ... });




