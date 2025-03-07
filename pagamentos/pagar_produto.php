<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
//error_reporting(E_ALL);

include("config.php");
require("../sistema/conexao.php");

$id_produto = $_GET['id_produto'];


$query = $pdo->query("SELECT * FROM produtos where id = '$id_produto' and id_conta = '$id_conta'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$nome_produto = $res[0]['nome'];
$valor = $res[0]['valor_venda'];
$foto = $res[0]['foto'];
$data_atual = date('Y-m-d');
$dataF = implode('/', array_reverse(explode('-', $data_atual)));
$hora = date("H:i");

$valorF = number_format($valor, 2, ',', '.');

$token_valor = ($valor != "") ? sha1($valor) : "";
$doc = $_REQUEST["cpf"];
$doc =  str_replace(array(",", ".", "-", "/", " "), "", $doc);
$ref = $_REQUEST["ref"];
$email = $_REQUEST["email"];
$gerarDireto = $_REQUEST["gerarDireto"];
$descricao = $nome_produto;
$nome = $_REQUEST["nome"];
$sobrenome = $_REQUEST["sobrenome"];

?>
<html lang="pt-br">

<head>
    <title>Pagamento</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <link href="./assets/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/signin.css" rel="stylesheet">
    <script src="./assets/jquery-3.6.4.min.js"></script>
</head>

<body class="text-center">


    <form action="agendamento_confirmado" method="post" style="display:none">
        <input type="hidden" name="id" value="<?= $id_produto; ?>">
        <input type="hidden" name="enviar" value="Sim">
        <button id="btn_form" type="submit"></button>
    </form>



    <div style="max-width: 500px; max-height: 800px; margin: 0 auto;  text-align: center; margin-bottom: 20px; word-break: break-all;">


        <div id="info_pagamento" style="text-align: center;">
            <p class="h3 font-weight-normal" style=" font-size: 21px; border-radius: 4px;"><span><b>Produto <?= $nome_produto; ?> - </span><span style="color:green; ">R$ <?= $valorF; ?><?= $public_key; ?></b></span> </p>               

        </div>

        <div id="paymentBrick_container">
        </div>
        <div id="statusScreenBrick_container">
        </div>
        <div class="form-signin" id="form-pago" style="display:none;text-align: center;">
            <h1 class="h3 mb-3 font-weight-normal">Obrigado!</h1>
            <img class="mb-4" src="<?= $url; ?>pagamentos/assets/check_ok.png" alt="" width="120" height="120">
            <br>
            <h5><?= $MSG_APOS_PAGAMENTO; ?></h5>
            <br>
            Código do pagamento: <?php echo $_GET["id_produto"]; ?>
        </div>
    </div>
    <style>
        body {
            font-family: arial
        }
    </style>
    <script>
        var payment_check;
        const mp = new MercadoPago('<?= $TOKEN_MERCADO_PAGO_PUBLICO; ?>', {
            locale: 'pt-BR'
        });
        const bricksBuilder = mp.bricks();
        const renderPaymentBrick = async (bricksBuilder) => {
            const settings = {
                initialization: {
                    amount: '<?= $valor; ?>',
                    payer: {
                        firstName: "<?= $nome; ?>",
                        lastName: "<?= $sobrenome; ?>",
                        email: "<?= $email; ?>",
                        identification: {
                            type: '<?= (strlen($doc) > 11 ? "CNPJ" : "CPF"); ?>',
                            number: '<?= $doc; ?>',
                        },
                        address: {
                            zipCode: '',
                            federalUnit: '',
                            city: '',
                            neighborhood: '',
                            streetName: '',
                            streetNumber: '',
                            complement: '',
                        }
                    },
                },
                customization: {
                    visual: {
                        style: {
                            theme: "dark",
                        },
                    },
                    paymentMethods: {
                        <?php if ($ATIVAR_CARTAO_CREDITO == "1") { ?>creditCard: "all",
                    <?php } ?>
                    <?php if ($ATIVAR_CARTAO_DEBIDO == "1") { ?>debitCard: "all",
                    <?php } ?>
                    <?php if ($ATIVAR_BOLETO == "1") { ?>ticket: "all",
                    <?php } ?>
                    <?php if ($ATIVAR_PIX == "1") { ?>bankTransfer: "all"
                    <?php } ?>,
                    maxInstallments: 12
                    },
                },
                callbacks: {
                    onReady: () => {},
                    onSubmit: ({
                        selectedPaymentMethod,
                        formData
                    }) => {

                        formData.external_reference = '<?= $ref; ?>';
                        formData.description = '<?= $descricao; ?>';
                        var id_conta = '<?= $id_produto; ?>';

                        return new Promise((resolve, reject) => {
                            fetch("<?= $url; ?>pagamentos/process_payment.php", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                    },
                                    body: JSON.stringify(formData),
                                })
                                .then((response) => response.json())
                                .then((response) => {
                                    // receber o resultado do pagamento
                                    if (response.status == true) {
                                        window.location.href = "<?= $url; ?>pagamentos/pag_aprovado_produto.php?id=" + response.id + '&id_produto=' + id_conta;
                                    }
                                    if (response.status != true) {
                                        alert(response.message);
                                    }
                                    resolve();
                                })
                                .catch((error) => {
                                    reject();
                                });
                        });
                    },
                    onError: (error) => {
                        console.error(error);
                    },
                },
            };
            window.paymentBrickController = await bricksBuilder.create(
                "payment",
                "paymentBrick_container",
                settings
            );
        };

        const renderStatusScreenBrick = async (bricksBuilder) => {
            const settings = {
                initialization: {
                    paymentId: '<?= $_GET["id"]; ?>',
                },
                customization: {
                    visual: {
                        hideStatusDetails: false,
                        hideTransactionDate: false,
                        style: {
                            theme: 'dark', // 'default' | 'dark' | 'bootstrap' | 'flat'
                        }
                    },
                    backUrls: {
                        //'error': '<http://<your domain>/error>',
                        //'return': '<http://<your domain>/homepage>'
                    }
                },
                callbacks: {
                    onReady: () => {
                        check("<?= $_GET["id"]; ?>", "<?= $_GET["id_produto"]; ?>");
                    },
                    onError: (error) => {},
                },
            };
            window.statusScreenBrickController = await bricksBuilder.create('statusScreen', 'statusScreenBrick_container', settings);
        };

        <?php if ($_GET["id"] != "") { ?>
            renderStatusScreenBrick(bricksBuilder);
        <?php } else { ?>
            <?php if ($valor == "") { ?>
                alert("O valor do pagamento está vazio.");
            <?php } ?>
            renderPaymentBrick(bricksBuilder);
        <?php } ?>
        var redi = "<?= $URL_REDIRECIONAR; ?>";

        function check(id, id_conta) {
            var settings = {
                "url": "<?= $url; ?>pagamentos/process_payment.php?acc=check&id=" + id + "&id_produto=" + id_conta,
                "method": "GET",
                "timeout": 0
            };
            $.ajax(settings).done(function(response) {
                try {
                    if (response.status == "pago") {
                        $("#statusScreenBrick_container").slideUp("fast");
                        $("#form-pago").slideDown("fast");
                        if (redi.trim() == "Sim") {
                            setTimeout(() => {
                                //window.location = "../meus-agendamentos.php";
                                alert('Pagamento efetuado com sucesso!');
                                //$("#btn_form").click();
                            }, 6000);
                        }
                    } else {
                        setTimeout(() => {
                            check(id, null)
                        }, 3000);
                    }
                } catch (error) {
                    alert("Erro ao localizar o pagamento, contacte com o suporte");
                }
            });
        }
    </script>

</body>

</html>