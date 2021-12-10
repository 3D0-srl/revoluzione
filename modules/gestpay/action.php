<?php
use \Marion\Core\Marion;

function gestpay_action_payment(&$cart=NULL){
	
		if( $cart->paymentMethod != 'GESTPAY') return false;
		
		//calcolo l'importo da pagare
		$amountFinal = $cart->getTotalFinal();
		
		//creo la descrizione dell'ordine
		$amountFormatted = number_format($amountFinal, 2, ',', '');

		//creo la descrizione dell'ordine
		$description = sprintf($GLOBALS['gettext']->strings['description_order_paypal'],$amountFormatted,$cart->number,$GLOBALS['setting']['default']['GENERALE']['nomesito']);
		
		$database = _obj('Database');
		$check = $database->select('max(num) as max','transazione_gestpay',"id_cart={$cart->id}");
		
		if( okArray($check) ){
			$num = $check[0]['max'];
			if( $num ){ 
				$num +=1;
			}else{
				$num = 1;
			}
		}else{
			$num = 1;
		}
		

		$gestpay = getConfig('gestpay_module');

		//vedi questa pagina https://api.gestpay.it/#currency-codes
		switch($cart->currency){
			case 'EUR';
				$currency='242';
				break;
			case 'USD';
				$currency='1';
				break;
			case 'GBP';
				$currency='2';
				break;
		}
		

		//check where to connect: test or production environment?
		if ($gestpay['sandbox']) {
			$shopLogin = $gestpay['shopLoginTest'];
			$wsdl = "https://sandbox.gestpay.net/gestpay/GestPayWS/WsCryptDecrypt.asmx?wsdl"; //TESTCODES
			$action_pagamento = "https://sandbox.gestpay.net/pagam/pagam.aspx";
		} else {
			$shopLogin = $gestpay['shopLogin'];
			$wsdl = "https://ecomms2s.sella.it/gestpay/gestpayws/WSCryptDecrypt.asmx?wsdl"; //PRODUCTION
			//$wsdl = 'https://api.axerve.com/gestpay/gestpayws/WSCryptDecrypt.asmx?wsdl';
			$action_pagamento = "https://ecomm.sella.it/pagam/pagam.aspx";
			//$action_pagamento = 'https://api2.axerve.com/gestpay/gestpayws/WSS2S.asmx';
		}
		


		//create the payment object array
		$param = array(
			'shopLogin' => $shopLogin,
			'apikey' => 'R0VTUEFZNzE5MjcjI0VzZXJjZW50ZSBUZXN0IGRpIE5hcG9saXRhbm8jIzIxLzExLzIwMTggMTU6Mjk6Mzc=',
			'uicCode' => $currency,
			'amount' => round($amountFinal,2),
			'shopTransactionId' => $cart->id."_".$num."_".$cart->number,
		);

		if( $cart->company){
			$param['buyerName'] = $cart->company;
		}else{
			$param['buyerName'] = $cart->name." ".$cart->surname;
		}

		$param['buyerEmail'] = $cart->email;

		//debugga($param);exit;
		
		
		//instantiate a SoapClient from Gestpay Wsdl
		$client = new SoapClient($wsdl);
		
		$objectResult = null;

		try {
			$objectResult = $client->Encrypt($param);
			//debugga($objectResult);
		}
		//catch SOAP exceptions
		catch (SoapFault $fault) {
			trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
			//$template->errore_generico($fault->faultstring);
		}
		
		//parse the XML result
		$result = simplexml_load_string($objectResult->EncryptResult->any);
	
		$template = _obj('Template');
		$errCode= $result->ErrorCode;
		$errDesc= $result->ErrorDescription;
		if($errCode != 0){

			
			$template->errore_generico($errDesc);
		}
		
		$encString= $result->CryptDecryptString;
		$url = $action_pagamento."?a=".$shopLogin."&b=".$encString;
		

		$toinsert = array(
			'id_cart' => $cart->id,
			'checked' => 0,
			'num' => $num
		);
		$database->insert('transazione_gestpay',$toinsert);
		
		
		header('Location: '.$url);
		exit;
		
	
}



Marion::add_action('action_payment','gestpay_action_payment');


function gestpay_cart_payment_top($cart,$orders=null){
	$error = _var('error_gestpay');
	if( $error ){
		$twig = Marion::getTwig('gestpay');
		$params['error'] = "GESTPAY: ".$error;
		echo $twig->render('error.htm',$params);
	}
}	



Marion::add_action('cart_payment_top','gestpay_cart_payment_top');




?>