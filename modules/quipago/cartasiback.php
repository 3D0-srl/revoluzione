<?php
require ('../../../config.inc.php');
$database = _obj('Database');
$template = _obj('Template');

$transaction = $_GET;
//prendo il carrello di riferimento
$cartNumber = $transaction['codTrans'];

$status_confirmed = Marion::getConfig('quipago_module','status_confirmed');
if( $transaction['esito'] == 'KO' ){


	$cart = Cart::withNumber($cartNumber);

	if( authUser() ) {
		if(is_object($cart)){

			$cart->changeStatus('payment_quipago_nak');
			$cart->createNumber();
			$cart->set(
				array(	
					'status'=>'active',
					'paymentMethod'=> NULL
				)
			)->save();

			//ripristino la merce ordinata
			$automatic_stock_type = Marion::getConfig('eshop','automaticStockType');
		
			if( $automatic_stock_type == 'onClose' ){
				$automatic_stock = Marion::getConfig('eshop','automaticStock');
				if($automatic_stock){
					$cart->increaseInventory();
				}
			}
		}
	}else{
		if(is_object($cart)){
			$cart->changeStatus('payment_quipago_nak');
			$cart->changeStatus('canceled');
		}
	}
	$template->messaggio = $transaction['messaggio'];
	$template->link = "/{$GLOBALS['activelocale']}/cart-payment.htm";
	$template->output('continua.htm');
	
	
}else{
	$database->insert("transactionCartaSi",$transaction);

	$cart = Cart::withNumber($cartNumber);
	if(is_object($cart)){
		$cart->changeStatus($status_confirmed);
		if (Marion::exists_action('redirect_payment_success')){
			Marion::do_action('redirect_payment_success',array($cart));
		}
		header( "Location: /payment.php?action=payment_success&id={$cart->id}");
		exit;
	}else{
		$template->errore_generico('898');
	}
	
		
}


?>