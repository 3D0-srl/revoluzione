<?php
use \Marion\Core\Marion;

function unicredit_action_payment(&$cart=NULL){
		
		if( $cart->paymentMethod != 'UNICREDIT') return false;

		
		//calcolo l'importo da pagare
		$amountFinal = $cart->getTotalFinal();
		
		//creo la descrizione dell'ordine
		$amountFormatted = number_format($amountFinal, 2, ',', '');

		//creo la descrizione dell'ordine
		$description = sprintf($GLOBALS['gettext']->strings['description_order_unicredit'],$amountFormatted,$cart->number,$GLOBALS['setting']['default']['GENERALE']['nomesito']);
		
		$unicredit = getConfig('unicredit_module');

		//calcolo l'importo da pagare
		$amountFinal = $cart->getTotalFinal();
		
		//creo la descrizione dell'ordine
		$amountFormatted = number_format($amountFinal, 2, ',', '');
		$description = sprintf($GLOBALS['gettext']->strings['description_order_paypal'],$amountFormatted,$cart->number,$GLOBALS['setting']['default']['GENERALE']['nomesito']);
				
		require('./IGFS_CG_API/init/IgfsCgInit.php');

        $init = new IgfsCgInit();
		
		if ($unicredit['sandbox']) {
			$init->serverURL = $unicredit['url_sandbox'];
			$init->tid = $unicredit['kid_sandbox'];
			$init->kSig = $unicredit['kSig_sandbox'];
		} else {
			$init->serverURL = $unicredit['url_live'];
			$init->tid = $unicredit['kid_live'];
			$init->kSig = $unicredit['kSig_live'];
		}
       
        $init->timeout = 15000;
        
		$init->shopID = $cart->number;     //codice carrello
        $init->shopUserRef = $cart->email;
        $init->shopUserName = $cart->surname.', '. $cart->name;
        $init->trType = "PURCHASE";
        $init->currencyCode = "EUR";
        $init->amount = ($amountFinal)*100;
        $init->langID = "IT";

		if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$protocol = 'https://';	
		}else{
			$protocol = 'http://';
		}
		$url_base = $protocol.$_SERVER['SERVER_NAME'];
		$url_redirect = $_SERVER['SERVER_NAME']."/modules/unicredit/unicreditback.php";


		$init->notifyURL = $url_redirect . "?action=pagamento&carrello=" . $cart->id;
		$init->errorURL = $url_redirect . "?action=errore_pagamento&carrello=" . $cart->id;
        
		if ($unicredit['sandbox']) {
			$init->disableCheckSSLCert();
		}

        if (!$init->execute()) {
           	$template = _obj('Template');
			$errore = $init->errorDesc;
			$template->link = "p/errore_pagamento.htm";
			$template->messaggio = $errore;
			$template->output('continua.htm');
			exit;
        }

        $idpagamento = $init->paymentID;

        $toinsert = array(
            "user"			=> $cart->user,
            "cartId"		=> $cart->id,
            "cartNumber"    => $cart->number,
            "importo"       => $cart->getTotalFinal(),
            "paymentID"		=> $init->paymentID,
            "timestamp_go"  => strftime('%Y-%m-%d %H:%M:%S',time()),
        );

		$database = _obj('Database');
        $database->insert("transactionUnicredit", $toinsert);

        $_SESSION['carrello_utente']['codice'] = $cart->id;
        $_SESSION['carrello_utente']['idpagamento'] = $idpagamento;
        $_SESSION['carrello_utente']['importo'] = $cart->total;

		header("Location: {$init->redirectURL}");
		exit;	
}



Marion::add_action('action_payment','unicredit_action_payment');



function unicredit_cart_payment_top($cart,$orders=null){
	$error = _var('error_unicredit');
	if( $error ){

		$twig = Marion::getTwig('paypal');
		//$widget = Marion::widget('paypal');
	
		$params['error'] = "PAYPAL: ".$error;
		echo $twig->render('error.htm',$params);
		
	}
}	



Marion::add_action('cart_payment_top','unicredit_cart_payment_top');
?>