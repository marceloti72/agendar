<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Processando Agendamento</title>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
    <!-- Canvas Confetti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
</head>
<body>
<?php
$id_pg = @$_GET['id_agd'];
$id_conta = @$_GET['id_conta'];

if ($id_pg != null) {    
    require("../sistema/conexao.php");
    $valor_pago = '0';
    $query = $pdo->query("SELECT * FROM receber WHERE id = '$id_pg'");
} else {
    $query = $pdo->query("SELECT * FROM receber WHERE ref_pix = '$ref_pix'");
}

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


$query = $pdo->query("SELECT * FROM assinante WHERE id = '$cliente' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_plano = @$res[0]['id_plano'];

$query = $pdo->query("SELECT * FROM planos WHERE id = '$id_plano' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$nome_plano = @$res[0]['nome'];
$valor_mensal = @$res[0]['preco_mensal'];
$valor_anual = @$res[0]['preco_anual'];


$servico_conc = $nome_serv . " (Site)";

if ($id_pg == "") {
    $pdo->query("INSERT INTO receber SET descricao = '$servico_conc', tipo = 'Serviço', valor = '$valor_pago', data_lanc = CURDATE(), data_venc = CURDATE(), data_pgto = CURDATE(), usuario_lanc = '0', usuario_baixa = '0', foto = 'sem-foto.jpg', pessoa = '$cliente', pago = 'Sim', servico = '$servico', funcionario = '$funcionario', obs = '', pgto = '$forma_pgto', referencia = '$ult_id', id_conta = '$id_conta'");
}

if ($id_pg != "") {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Sucesso!',
                text: 'Agendamento realizado com sucesso.',
                icon: 'success',
                timer: 6000,
                showConfirmButton: false,
                width: '600px', // Define a largura da janela (aumente conforme necessário)
                padding: '2rem', // Aumenta o espaçamento interno (opcional)
                didOpen: () => {
                    confetti({
                        particleCount: 150,
                        spread: 90,
                        origin: { y: 0.5 },
                        colors: ['#ff0000', '#00ff00', '#0000ff'], // Cores personalizadas (vermelho, verde, azul)
                        angle: 90,                          // Direção do lançamento
                        decay: 0.9,                         // Velocidade de desaceleração
                        startVelocity: 45                   // Velocidade inicial
                    });
                    // Acessa o canvas criado pelo confetti e define o zIndex
                    const confettiCanvas = document.querySelector('canvas');
                    if (confettiCanvas) {
                        confettiCanvas.style.zIndex = '9999';
                    }
                }
            }).then(() => {
                window.location.href = '../meus-agendamentos.php';
            });
        });
    </script>";
}
?>
</body>
</html>