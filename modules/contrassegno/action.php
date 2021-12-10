<?php
use \Marion\Core\Marion;

function contrassegno_action_payment(&$cart=NULL){
		
		if( $cart->paymentMethod != 'COD') return false;
		
		$cart->changeStatus('waiting_cod');
		$automatic_stock = getConfig('eshop','automaticStock');
		$automatic_stock_type = getConfig('eshop','automaticStockType');
		if($automatic_stock && $automatic_stock_type == 'onClose'){
			$cart->decreaseInventory();
		}
		Eshop::redirectSuccessPayment($cart->id);
		
	
}



Marion::add_action('action_payment','contrassegno_action_payment');





function contrassegno_shipping_methods($payment_method,&$check){
	if( $payment_method->code != 'COD') return;

	$cart = Cart::getCurrent();
	if( !authUser() ){
		$shipping_method = $_SESSION['sessionCart']['data']['shippingMethod'];
	}else{
		$shipping_method = $cart->shippingMethod;
	}
	$shippingMethods = unserialize(Marion::getConfig('cod','shippingMethods'));
	if( !in_array($shipping_method,$shippingMethods)) $check = false;
	
	
}	
Marion::add_action('check_conditions_payment','contrassegno_shipping_methods');



?>