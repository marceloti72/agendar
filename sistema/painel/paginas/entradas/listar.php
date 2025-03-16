<?php 
require_once("../../../conexao.php");
$tabela = 'entradas';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entradas</title>
    <style>
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            padding: 10px;
            text-align: left;
            vertical-align: middle;
        }
        .table th {
            background-color: #f8f8f8;
            font-weight: bold;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .hovv {
            cursor: pointer;
            border-radius: 5px;
            object-fit: cover;
            width: 50px;
            height: 50px;
            transition: transform 0.3s;
        }
        .hovv:hover {
            transform: scale(2);
        }

        /* Media Query para Mobile (max-width: 768px) */
        @media (max-width: 768px) {
            .table {
                display: block;
                overflow-x: auto; /* Rolagem horizontal se necessário */
                white-space: nowrap; /* Evita quebra de linha */
            }
            .table th, .table td {
                padding: 8px;
                font-size: 12px; /* Reduz tamanho da fonte */
            }
            .hovv {
                width: 30px;
                height: 30px; /* Reduz tamanho da imagem */
            }

           
	    .dataTables_length {
				display: none;
			}
	
        }
    </style>
    <!-- Dependências -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body>
<?php 
$query = $pdo->query("SELECT * FROM $tabela where id_conta = '$id_conta' ORDER BY id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
echo <<<HTML
    <small>
    <table class="table table-hover" id="tabela">
    <thead> 
    <tr> 
    <th>Produto</th>    
    <th>Quantidade</th>     
    <th>Motivo</th>     
    <th>Usuário Lançou</th> 
    <th>Data</th>    
    </tr> 
    </thead> 
    <tbody>    
HTML;

for($i=0; $i < $total_reg; $i++){
    foreach ($res[$i] as $key => $value){}
    $id = $res[$i]['id'];
    $produto = $res[$i]['produto'];    
    $quantidade = $res[$i]['quantidade'];
    $motivo = $res[$i]['motivo'];
    $usuario = $res[$i]['usuario'];
    $data = $res[$i]['data'];
            
    $query2 = $pdo->query("SELECT * FROM produtos where id = '$produto' and id_conta = '$id_conta'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    $total_reg2 = @count($res2);
    if($total_reg2 > 0){
        $nome_produto = $res2[0]['nome'];
        $foto_produto = $res2[0]['foto'];
    }else{
        $nome_produto = 'Sem Referência!';
        $foto_produto = 'sem-foto.jpg';
    }

    $query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario' and id_conta = '$id_conta'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    $total_reg2 = @count($res2);
    if($total_reg2 > 0){
        $nome_usuario = $res2[0]['nome'];
    }else{
        $nome_usuario = 'Sem Referência!';
    }

    $dataF = implode('/', array_reverse(explode('-', $data)));

echo <<<HTML
<tr>
<td>
<img src="img/produtos/{$foto_produto}" width="50" height="50" class="hovv">
{$nome_produto}
</td>
<td>{$quantidade}</td>
<td>{$motivo}</td>
<td>{$nome_usuario}</td>
<td>{$dataF}</td>
</tr>
HTML;
}

echo <<<HTML
</tbody>
<small><div align="center" id="mensagem-excluir"></div></small>
</table>
</small>
HTML;
}else{
    echo '<small>Não possui nenhum registro Cadastrado!</small>';
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        $('#tabela').DataTable({
            "ordering": false,
            "stateSave": true,
            "responsive": true, // Ativa responsividade do DataTables
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json" // Traduz para português
            }
        });
        $('#tabela_filter label input').focus();
    });
</script>
</body>
</html>