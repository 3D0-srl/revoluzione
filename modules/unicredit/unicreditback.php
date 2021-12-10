<?php
require ('../include.inc.php');
$database = _obj('Database');
$template = _obj('Template');
$action = _var('action');

if ($action == 'pagamento') {
	
	$codice_carrello = $_SESSION['carrello_utente']['codice'];
	$idpagamento = $_SESSION['carrello_utente']['idpagamento'];
	if (!$idpagamento || !$codice_carrello) {
		$codice_carrello = _var('carrello');
		/*recupero il codice del pagamento*/
		$cod_pagamento = $database->select('*','transactionUnicredit',"cartId = '{$codice_carrello}'");
		if (okArray($cod_pagamento)) {
			$idpagamento = $cod_pagamento[0]['paymentID'];
		}
	}   
	
	$unicredit = Marion::getConfig('unicredit_module');

	$cart = Cart::withId($codice_carrello);

	require('./IGFS_CG_API/init/IgfsCgVerify.php');
	
	$verify = new IgfsCgVerify();

	if ($unicredit['sandbox']) {
		$verify->serverURL = $unicredit['url_sandbox'];
		$verify->tid = $unicredit['kid_sandbox'];
		$verify->kSig = $unicredit['kSig_sandbox'];
	} else {
		$verify->serverURL = $unicredit['url_live'];
		$verify->tid = $unicredit['kid_live'];
		$verify->kSig = $unicredit['kSig_live'];
	}

	$verify->timeout = 15000;
	$verify->shopID = $cart->number;     //codice carrello
	$verify->paymentID = $idpagamento;

	if ($unicredit['sandbox']) {
		$init->disableCheckSSLCert();
	}

	if (!$verify->execute()) {
		//MODIFICA PER SALVARE L'ERRORE
		$array_errore = array(
			"timestamp_back"	=>  strftime('%Y-%m-%d %H:%M:%S',time()),
			"errore"            =>  $verify->rc,
			"errDescription"    =>  $verify->errorDesc
		);
		$database->update('transactionUnicredit', "cartId = {$codice_carrello}", $array_errore);
		//FINE SALVATAGGIO ERRORE
		
		$errore = $verify->errorDesc;
		//$cart->status = 'active';
		//$cart->save();
		$template->link = "p/errore_pagamento.htm";
		$template->messaggio = $errore;
		$template->output('continua.htm');
		exit;
	}
	
	//modificaaaaaa
	$brand = $verify->brand;
	$brand = strtolower($brand);
			
	$rc = $verify->rc;
	$tranID = $verify->tranID;
	$enrStatus = $verify->enrStatus;
	$authStatus = $verify->authStatus;
			
	if ($tranID) {
		$cart->status = 'confirmed';
		$cart->save();
		$database->update('transactionUnicredit',"cartId = {$cart->id}", array("tranId" => $tranID, "authStatus" => $authStatus, "brand" => $brand, "enrStatus" => $enrStatus, "timestamp_back" => strftime('%Y-%m-%d %H:%M:%S',time())));
		
		header( "Location: /payment.php?action=payment_success&id={$cartId}");
		
		//header( "Location: /payment.php?action=unicredit_ok&id={$cart->id}");
		exit;
	} else {
		//MODIFICA PER SALVARE L'ERRORE
		$array_errore = array(
			"timestamp_back"	=>  strftime('%Y-%m-%d %H:%M:%S',time()),
			"errore"            =>  $verify->rc,
			"errDescription"    =>  $verify->errorDesc
		);
		$database->update('transactionUnicredit', "cartId = {$codice_carrello}", $array_errore);
		//FINE SALVATAGGIO ERRORE
		
		$errore = $verify->errorDesc;
		//$cart->status = 'active';
		//$cart->save();
		$template->link = "p/errore_pagamento.htm";
		$template->messaggio = $errore;
		$template->output('continua.htm');
		exit;
	}
} elseif ($action == 'errore_pagamento') {
	$codice_carrello = $_SESSION['carrello_utente']['codice']; 
	$idpagamento = $_SESSION['carrello_utente']['idpagamento'];
	
	if (!$idpagamento || !$codice_carrello) {
		$codice_carrello = get_var('carrello');
		/*recupero il codice del pagamento*/
		$cod_pagamento = $database->select('*','transactionUnicredit',"cartId = '{$codice_carrello}'");
		if (okArray($cod_pagamento)) {
			$idpagamento = $cod_pagamento[0]['paymentID'];
		}
	}

	$cart = Cart::withId($codice_carrello);

	$unicredit = getConfig('unicredit_module');

	require('./IGFS_CG_API/init/IgfsCgVerify.php');
	
	$verify = new IgfsCgVerify();

	if ($unicredit['sandbox']) {
		$verify->serverURL = $unicredit['url_sandbox'];
		$verify->tid = $unicredit['kid_sandbox'];
		$verify->kSig = $unicredit['kSig_sandbox'];
	} else {
		$verify->serverURL = $unicredit['url_live'];
		$verify->tid = $unicredit['kid_live'];
		$verify->kSig = $unicredit['kSig_live'];
	}

	$verify->timeout = 15000;
	$verify->shopID = $codice_carrello;     //codice carrello
	$verify->paymentID = $idpagamento;
	
	if ($unicredit['sandbox']) {
		$init->disableCheckSSLCert();
	}

	if (!$verify->execute()) {
		//MODIFICA PER SALVARE L'ERRORE
		$array_errore = array(
			"timestamp_back"	=>  strftime('%Y-%m-%d %H:%M:%S',time()),
			"errore"            =>  $verify->rc,
			"errDescription"    =>  $verify->errorDesc
		);
		$database->update('transactionUnicredit', "cartId = {$codice_carrello}", $array_errore);
		//FINE SALVATAGGIO ERRORE
		
		$errore = $verify->errorDesc;

		$url = '/index.php?ctrl=Cart&mod=cart&action=cart_payment&lang='.$GLOBALS['activelocale']."&error_unicredit=". urlencode($errore);
		header( "Location: {$url}");
		//$cart->status = 'active';
		//$cart->save();
		/*$template->link = "p/errore_pagamento.htm";
		$template->messaggio = $errore;
		$template->output('continua.htm');*/
		exit;
	}
}

if ($database) $database->close();
?>
