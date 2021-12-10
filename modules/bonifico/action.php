<?php
use \Marion\Core\Marion;
use Shop\Eshop;
use Marion\Components\WidgetComponent;
function bonifico_action_payment(&$cart=NULL){
		
		if( $cart->paymentMethod != 'BONIFICO') return false;
		
		$cart->changeStatus('waiting_bonifico');
		$automatic_stock = getConfig('eshop','automaticStock');
		$automatic_stock_type = getConfig('eshop','automaticStockType');
		if($automatic_stock && $automatic_stock_type == 'onClose'){
			$cart->decreaseInventory();
		}
		if(!authUser()){
			unset($_SESSION['sessionCart']['data']['id']);
			unset($_SESSION['sessionCart']['data']['paymentMethod']);
			unset($_SESSION['sessionCart']['orders']);
		}
		Eshop::redirectSuccessPayment($cart->id);
		
	
}



Marion::add_action('action_payment','bonifico_action_payment');




function bonifico_cart_thanks_top($cart,$orders){
	if( $cart->paymentMethod != 'BONIFICO') return false;
	$datibonifico = getConfig('bonifico');

	$widget =  new WidgetComponent('bonifico');
	foreach($datibonifico as $k => $v){
		$widget->setVar($k,$v);
	}
	$widget->output('datibonifico.htm');

}	



Marion::add_action('cart_thanks_top','bonifico_cart_thanks_top');
Marion::add_action('mail_shop_info','bonifico_cart_thanks_top');




?>