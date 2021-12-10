<?php
use \Marion\Core\Marion;
use Marion\Components\WidgetComponent;



function paypal_action_payment(&$cart=NULL){
	if( $cart->paymentMethod != 'PAYPAL') return false;
	
	header('Location: '._MARION_BASE_URL_."index.php?mod=paypal&id=".$cart->id);
}



Marion::add_action('action_payment','paypal_action_payment');



function paypal_cart_payment_top($cart,$orders=null){
	$error = _var('error_paypal');
	
	if( $error ){
		
		$widget = new WidgetComponent('paypal');
	
		$widget->setVar('error', "PAYPAL: ".$error);
		$widget->output('error.htm');
		
	}
}	




Marion::add_action('cart_payment_top','paypal_cart_payment_top');




function paypal_cart_thanks_top($cart,$orders){
	
	if( $cart->paymentMethod != 'PAYPAL') return false;
	$database = _obj('Database');

	$data = $database->select('*','transactionPayPal',"cartId={$cart->id} AND status = 'completed'");
	if( okArray($data) ){
		$widget = new WidgetComponent('paypal');
		foreach($data[0] as $k => $v){
			$widget->setVar($k,$v);
		}
		$widget->output('success_message.htm');
	}
}	



Marion::add_action('cart_thanks_top','paypal_cart_thanks_top');
Marion::add_action('mail_shop_info','paypal_cart_thanks_top');


function paypal_set_media_ctrl($ctrl){
	
	$action = _var('action');
	$ctrl->registerJS('modules/paypal/js/checkout.js?v=3','end');
	$ctrl->registerCSS('modules/paypal/css/button.css');
	
}

Marion::add_action('action_register_media_front','paypal_set_media_ctrl');



function paypal_block_product_buttons($product){
	
	$action = _var('action');
	if( $action == 'product'){
		$widget = new WidgetComponent('paypal');
		$widget->output('paypal_button.htm');
	}
}

Marion::add_action('display_block_product_buttons','paypal_block_product_buttons');



function paypal_block_cart_review(){
	
	
	$widget = new WidgetComponent('paypal');
	$widget->output('paypal_button_cart_preview.htm');
	
}

Marion::add_action('display_block_cart_preview','paypal_block_cart_review');


function paypal_block_cart_button(){
	

	$widget = new WidgetComponent('paypal');
	$widget->output('paypal_button_cart.htm');
	
}

Marion::add_action('display_cart_buttons','paypal_block_cart_button');




function paypal_overlay(){
	
	
	$widget = new WidgetComponent('paypal');
	$widget->output('overlay.htm');


	
}

Marion::add_action('display_block_end_page','paypal_overlay');



?>