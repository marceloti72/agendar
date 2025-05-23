<?php

	$mensagem_not = 'Texto da API250';
	$titulo_not = 'Título da Notificação';

	$content = array(
			"en" => $mensagem_not, //MIOLO DA NOTIFICAÇÃO
			"pt" => $mensagem_not
			);
			
			$heading = array(
			   "en" => $titulo_not,  //TITULO DA NOTIFICAÇÃO
			   "pt" => $titulo_not
			);
	
	$fields = array(
			'app_id' => "00d0cf8f-910f-4ff1-819d-1a429d468a4e", //IDENTIFICADOR DO APP
			//'include_external_user_ids' => array('123456789', '555'), //EXEMPLO PLAYER ID
			'included_segments' => array('All'),
			'data' => array("foo" => "bar"),
			'contents' => $content,
			'android_channel_id'=>'07fc531e-8b8d-4858-8bca-b48e266bf90e', //CANAL PARA SOM PERSONALIZADO
			'headings' => $heading
			//'send_after' => "2018-12-20 15:50:00 UTC-0200" //PARA PROGRAMAR UM DIA E HORA ESPECÍFICO
		);

	$fields = json_encode($fields);
	//print("\nJSON sent:\n");
	//print($fields);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
		'Authorization: os_v2_app_adim7d4rb5h7dam5djbj2rukjzb3anqwjptetxvs7m5swskvakxja3tabvgzmil3sxsgaoz6zc5dysja5nhubcy74kfmjchlxs5mhna'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	$response = curl_exec($ch);
	curl_close($ch);


$return["allresponses"] = $response;
$return = json_encode( $return);

//print("\n\nJSON received:\n");
//print($return);
//print("\n");
?>