<?php
use \Marion\Core\Marion;


function monetaweb_action_payment(&$cart=NULL){
		
		if( $cart->paymentMethod != 'MONETAWEB') return false;
		
		if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$protocol = 'https://';	
		}else{
			$protocol = 'http://';
		}
		
		
		$sandbox = true;

		if( $sandbox ){
			$setefiPaymentGatewayDomain = 'https://test.monetaonline.it';
		}else{
			$setefiPaymentGatewayDomain = 'https://www.monetaonline.it';
		}
		$terminalId = '93491810';
		$terminalPassword = 'Password1';

		
		//calcolo l'importo da pagare
		//$amountFinal = $cart->getTotalFinal();
		$amountFinal = $cart->total-$cart->discount+$cart->supplement;
		//$amountFinal = 9999;
		
		//creo la descrizione dell'ordine
		$amountFormatted = number_format($amountFinal, 2, ',', '');

		$message = _translate('description_order','monetaweb');
		

		//creo la descrizione dell'ordine
		$description = sprintf($message,$GLOBALS['activecurrency'],$amountFormatted,$cart->number,$GLOBALS['setting']['default']['GENERALE']['nomesito']);
		


		$session_id = session_id();

		
		$url_notify = $protocol.$_SERVER['SERVER_NAME']._MARION_BASE_URL_."index.php?mod=monetaweb&ctrl=Notify&session_id={$session_id}&id_cart={$cart->id}";
		$url_recovery = $protocol.$_SERVER['SERVER_NAME']._MARION_BASE_URL_."index.php?mod=monetaweb&ctrl=Recovery&session_id={$session_id}&id_cart={$cart->id}";
		
		$parameters = array(
		  'id' => $terminalId,
		  'password' => $terminalPassword,
		  'operationType' => 'initialize',
		  'amount' => $amountFinal,
		  'language' => 'ITA',
		  'responseToMerchantUrl' => $url_notify,
		  'recoveryUrl' => $url_recovery,
		  'merchantOrderId' => $cart->id,
		  'cardHolderName' => $cart->name." ".$cart->surname,
		  'cardHolderEmail'  => $cart->email,
		  'description' => $description,
		  //'customField' => 'Custom Field'
	  );
	
		switch($cart->currency){
			case 'EUR':
				$parameters['currencyCode'] = '978';
				break;
		}

			
		switch($cart->locale){
			case 'it';
				$parameters['language'] = "ITA";
				break;
			case 'de';
				$parameters['language']= "DEU";
				break;
			case 'es';
				$parameters['language'] = "SPA";
				break;
			case 'en';
				$parameters['language'] = "USA";
				break;
			case 'fr';
				$parameters['language']= "FRA";
				break;
			case 'po';
				$parameters['language']= "POR";
				break;
			default:
				$parameters['language'] = "USA";
		}
		
	  $curlHandle = curl_init();
	  curl_setopt($curlHandle, CURLOPT_URL, $setefiPaymentGatewayDomain.'/monetaweb/payment/2/xml');
	  curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
	  curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
	  curl_setopt($curlHandle, CURLOPT_POST, true);
	  curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($parameters));
	  curl_setopt($curlHandle, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
	  $xmlResponse = curl_exec($curlHandle);
	  curl_close($curlHandle);

	  $response = new SimpleXMLElement($xmlResponse);

	 
	  $paymentId = $response->paymentid;
	  $paymentUrl = $response->hostedpageurl;

	  $securityToken = $response->securitytoken;

	  $setefiPaymentPageUrl = "$paymentUrl?PaymentID=$paymentId";
	  header("Location: $setefiPaymentPageUrl");
	  exit;
	
}



Marion::add_action('action_payment','monetaweb_action_payment');




function monetaweb_cart_payment_top($cart,$orders=null){
	$error = _var('error_monetaweb');
	
	if( $error ){
		
		$twig = Marion::getTwig('monetaweb');
	
		$params['error'] = $error;
		echo $twig->render('error.htm',$params);
		
	}
}



Marion::add_action('cart_payment_top','monetaweb_cart_payment_top');
?>