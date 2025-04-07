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
    if (@$porc_servico > 0) {
        echo 'Faça o pagamento antes de ir para o agendamento';
        exit();
    }
    require("../sistema/conexao.php");
    $valor_pago = '0';
    $query = $pdo->query("SELECT * FROM agendamentos WHERE id = '$id_pg'");
} else {
    $query = $pdo->query("SELECT * FROM agendamentos WHERE ref_pix = '$ref_pix'");
}
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$cliente = $res[0]['cliente'];
$servico = $res[0]['servico'];
$funcionario = $res[0]['funcionario'];
$data = $res[0]['data'];
$hora = $res[0]['hora'];
$obs = $res[0]['obs'];
$data_lanc = $res[0]['data_lanc'];
$usuario = $res[0]['usuario'];
$status = $res[0]['status'];
$hash = $res[0]['hash'];
$ref_pix = $res[0]['ref_pix'];
$data_agd = $res[0]['data'];
$hora_do_agd = $res[0]['hora'];
$id_conta = $res[0]['id_conta'];

if (@$forma_pgto == "pix") {
    $forma_pgto = "Pix";
} else {
    $forma_pgto = "Cartão de Crédito";
}

$query = $pdo->query("SELECT * FROM servicos WHERE id = '$servico' AND id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$nome_serv = @$res[0]['nome'];
$tempo = @$res[0]['tempo'];

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
                window.location.href = '../meus-agendamentos.php?u=$username';
            });
        });
    </script>";
}
?>
</body>
</html>