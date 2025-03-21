<?php 
header('Content-Type: application/json');
require_once("../../../conexao.php"); 
@session_start();
$id_conta = $_SESSION['id_conta'];

try {
    // Pegar ano da requisição ou usar corrente como padrão
    $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

    // Validar ano
    $ano = max(2000, min(2100, $ano)); // Limites razoáveis para o ano

    // Calcular primeiro e último dia do ano selecionado
    $primeiroDia = "$ano-01-01";
    $ultimoDia = "$ano-12-31";

    // Query para contar agendamentos por dia da semana
    $sql = "SELECT 
            DAYOFWEEK(data) AS dia_semana,
            COUNT(*) AS total
            FROM agendamentos
            WHERE id_conta = :id_conta AND data BETWEEN :primeiroDia AND :ultimoDia
            GROUP BY dia_semana
            ORDER BY dia_semana";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':primeiroDia' => $primeiroDia,
        ':ultimoDia' => $ultimoDia,
        ':id_conta' => $id_conta
    ]);
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inicializar array com zeros para todos os dias (1=Domingo, 2=Segunda, etc)
    $dados = array_fill(1, 7, 0);
    
    // Preencher com os resultados do banco
    foreach ($resultados as $row) {
        $dados[$row['dia_semana']] = (int)$row['total'];
    }

    // Ajustar para começar de segunda (2) até domingo (1)
    $dadosOrdenados = [
        $dados[2], // Segunda
        $dados[3], // Terça
        $dados[4], // Quarta
        $dados[5], // Quinta
        $dados[6], // Sexta
        $dados[7], // Sábado
        $dados[1]  // Domingo
    ];

    echo json_encode([
        'dados' => $dadosOrdenados,
        'ano' => $ano
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>