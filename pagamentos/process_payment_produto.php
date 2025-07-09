<?php
header('Content-Type: application/json');

include("./config.php");
require("../sistema/conexao.php");



$status_pag = array(
    "approved" => "Aprovado",
    "rejected" => "Rejeitado",
    "in_process" => "Pendente aprovação",
);

$status_pag_motivo = array(
"approved" => array("accredited" => "Pronto, seu pagamento foi aprovado!"),
"in_process" => array(
    "pending_contingency" => "Estamos processando o pagamento. Não se preocupe, em menos de 2 dias úteis informaremos por e-mail se foi creditado.",
    "pending_review_manual" => "Estamos processando seu pagamento. Não se preocupe, em menos de 2 dias úteis informaremos por e-mail se foi creditado ou se necessitamos de mais informação."
),
"rejected" => array(
    "cc_rejected_bad_filled_card_number" => "Revise o número do cartão.",
    "cc_rejected_bad_filled_date" => "Revise a data de vencimento.",
    "cc_rejected_bad_filled_other" => "Revise os dados.",
    "cc_rejected_bad_filled_security_code" => "Revise o código de segurança do cartão.",
    "cc_rejected_blacklist" => "Não pudemos processar seu pagamento.",
    "cc_rejected_call_for_authorize" => "Você deve autorizar ao payment_method_id o pagamento do valor ao Mercado Pago.",
    "cc_rejected_card_disabled" => "Ligue para o payment_method_id para ativar seu cartão. O telefone está no verso do seu cartão.",
    "cc_rejected_card_error" => "Não conseguimos processar seu pagamento.",
    "cc_rejected_duplicated_payment" => "Você já efetuou um pagamento com esse valor. Caso precise pagar novamente, utilize outro cartão ou outra forma de pagamento.",
    "cc_rejected_high_risk" => "Seu pagamento foi recusado. Escolha outra forma de pagamento. Recomendamos meios de pagamento em dinheiro.",
    "cc_rejected_insufficient_amount" => "O payment_method_id possui saldo insuficiente.",
    "cc_rejected_invalid_installments" => "O payment_method_id não processa pagamentos em installments parcelas.",
    "cc_rejected_max_attempts" => "Você atingiu o limite de tentativas permitido. Escolha outro cartão ou outra forma de pagamento.",
    "cc_rejected_other_reason" => "payment_method_id não processa o pagamento.",
    "cc_rejected_card_type_not_allowed" => "O pagamento foi rejeitado porque o usuário não tem a função crédito habilitada em seu cartão multiplo (débito e crédito)."
)
);

if ($_GET["acc"] == "check") {

    $id = $_GET['id'];
    $id_pg = $_GET['id_produto'];
    

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/' . $id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $TOKEN_MERCADO_PAGO,
        ),
    ));

    $response_original = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response_original, true);

    $idcobranca = $response["external_reference"];
    $status = $response["status"];
    $payment_method_id = $response["payment_method_id"];
    $transaction_amount = $response["transaction_amount"];
    $id_mercadopago = $response["id"];
    $valor_pago = $response['transaction_details']['total_paid_amount'];

    if(isset($id_pg) && $id_pg != "null") // Garantindo apenas a primeira vez será atualiza
    {
        $query = $pdo->query("SELECT * FROM produtos where id = '$id_pg' and id_conta = '$id_conta'");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);        
        $nome_produto = $res[0]['nome'];
        $valor = $res[0]['valor_venda'];
        $foto = $res[0]['foto'];

        $query = $pdo->prepare("INSERT INTO receber SET descricao = '$nome_produto', tipo = 'Produto', valor = '$valor', data_lanc = curDate(), data_venc = curDate(), usuario_lanc = curDate(), foto = '$foto', pago = 'Sim', obs = 'Site', id_conta = '$id_conta'");
    }
   
 
    // pix
    if($payment_method_id=="pix"){       
        
    }

    // bolbradesco
    if($payment_method_id=="bolbradesco"){       

    }
   
    if ($status == "approved") { // PAGAMENTO APROVADO;
        $id_pg = null; // Defindo como vazio novamente
        $ref_pix = $id_mercadopago;
        $valor_pago = $transaction_amount;
        $forma_pgto = $payment_method_id;
        require("pag_aprovado_produto.php");
        
        
        echo json_encode(array("status" => "pago"));
        die;
    
    } else {

        echo json_encode(array("status" => $status));
        die;

    }

}
// FIM

// GERAR PAGAMENTO
try {
    
    $parsed_body = json_decode(file_get_contents('php://input'), true);
    $TIPO_PAGAMENTO = $parsed_body["payment_method_id"];
    $parsed_body["notification_url"] = $URL_NOTIFICACAO;
    $parsed_body["capture"] = true;
    
} catch(Exception $exception) {

    $response_fields = array('error_message' => $exception->getMessage());
    echo json_encode($response_fields);
    die;

}

// ENVIAR
$curl = curl_init();
curl_setopt_array($curl, array(
CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_POSTFIELDS => json_encode($parsed_body),
CURLOPT_HTTPHEADER => array(
    'X-Idempotency-Key: '.date('Y-m-d-H:i:s-').rand(0, 1500),
    'Authorization: Bearer '.$TOKEN_MERCADO_PAGO,
    'Content-Type: application/json',
),
));

$response = curl_exec($curl);
curl_close($curl);

$payment = json_decode($response);

if($payment->id === null) {
    $error_message = 'Erro ao realizar o pagamento, contacte com o suporte.';
    if($payment->message !== null) {
        $sdk_error_message = $payment->message;
        $error_message = $sdk_error_message !== null ? $sdk_error_message : $error_message;
    }
    if($error_message == "Invalid transaction_amount"){
        $error_message = "Valor de pagamento inválido";
    }
    echo json_encode(array("status" => false, "message" => $error_message));
    die;
    //throw new Exception($error_message);
} 

$idcobranca = $payment->external_reference;
$status = $payment->status;
$payment_method_id = $payment->payment_method_id;
$transaction_amount = $payment->transaction_amount;
$id_mercadopago = $payment->id;

if($TIPO_PAGAMENTO=="pix"){

   
    $status_mostrar = ($payment->status=="pending")? true : false;

} elseif($TIPO_PAGAMENTO=="bolbradesco" || $TIPO_PAGAMENTO=="pec"){ // boleto

   
    $status_mostrar = ($payment->status=="pending")? true: false;

} else { // cartao

   
    $status_mostrar = true;

}

$transaction_data = array(
    'id' => $payment->id,
    'status' => $status_mostrar,
    'tipo' => $TIPO_PAGAMENTO,
    'message' => $status_pag_motivo[$payment->status][$payment->status_detail],
);

echo json_encode($transaction_data);
die;


