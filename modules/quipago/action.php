<?php
use Marion\Core\Marion;

function quipago_action_payment(&$cart=NULL){
		
		if( $cart->paymentMethod != 'QUIPAGO') return false;

		
		//calcolo l'importo da pagare
		$amountFinal = $cart->getTotalFinal();
		
		//creo la descrizione dell'ordine
		$amountFormatted = number_format($amountFinal, 2, ',', '');

		//creo la descrizione dell'ordine
		
		
		$description = sprintf(_translate('description_order','quipago'),$amountFormatted,$cart->currency,$cart->number,$GLOBALS['setting']['default']['GENERALE']['nomesito']);
		//$description = sprintf($GLOBALS['gettext']->strings['description_order_paypal'],$amountFormatted,$cart->number,$GLOBALS['setting']['default']['GENERALE']['nomesito']);
		

		

		$quipago = Marion::getConfig('quipago_module');
		
		if( $quipago['sandbox'] ){
			$options_c['url_payment'] = $quipago['url_sandbox'];
			$options_c['alias'] = $quipago['alias_sandbox'];
			$options_c['mac'] = $quipago['mac_sandbox'];
			$importo = 0.01;
		}else{
			$options_c['url_payment'] = $quipago['url_live'];
			$options_c['alias'] = $quipago['alias_live'];
			$options_c['mac'] = $quipago['mac_live'];
			//calcolo l'importo da pagare
			$amountFinal = $cart->getTotalFinal();
			$importo = number_format($amountFinal, 2, '.', '');

		}
		

		if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$protocol = 'https://';	
		}else{
			$protocol = 'http://';
		}
		
		$url_back = $protocol.$_SERVER['SERVER_NAME']._MARION_BASE_URL_."index.php?mod=quipago&ctrl=Back";
		$url_cancel = $protocol.$_SERVER['SERVER_NAME']._MARION_BASE_URL_."index.php?mod=quipago&ctrl=Cancel";
		
		$payment['url'] = $url_back;
		$payment['url_back'] = $url_cancel;
		$payment['codTrans'] = $cart->number;
		$payment['OPTION_CF'] =  $cart->fiscalCode;
		$payment['mail'] = $cart->email;
		$payment['importo'] = number_format($importo, 2, '.', '')*100;
	   
		$payment['divisa'] = "EUR";
		
		$payment['descrizione'] = $description;
		$payment['session_id'] = session_id();
		$payment['nome'] = $cart->name;
		$payment['cognome'] = $cart->surname;
		

		switch($cart->locale){
			case 'it';
				$payment['languageId'] = "ITA";
				break;
			case 'de';
				$payment['languageId'] = "GER";
				break;
			case 'es';
				$payment['languageId'] = "SPA";
				break;
			case 'en';
				$payment['languageId'] = "ENG";
				break;
			case 'fr';
				$payment['languageId'] = "FRA";
				break;
			default:
				$payment['languageId'] = "ENG";
		}
		
		$payment['divisa'] = strtoupper($cart->currency);
		if(!$payment['divisa']){
			$payment['divisa'] = 'EUR';
		}
		
		//debugga($payment);exit;
		$url =$options_c['url_payment']."?alias=".$options_c['alias'];
		

		
		
		//calcolo mac 
		$mac = "codTrans={$payment['codTrans']}divisa={$payment['divisa']}importo={$payment['importo']}{$options_c['mac']}";
		$mac = sha1($mac);
		$url .= "&mac={$mac}";
		
		unset($payment['url_payment']);
		unset($payment['alias']);

		
		foreach($payment as $k => $v){
			$url.="&{$k}={$v}";	
		}

		//debugga($url);exit;
		
		header("Location: {$url}");
		exit;
	
}



Marion::add_action('action_payment','quipago_action_payment');



?>