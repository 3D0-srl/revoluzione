<?php
use \Marion\Core\Marion;
function manage_coupon_discount_cart(&$total){
	if( isset($_SESSION['manage_coupon']) && $_SESSION['manage_coupon'] ){
		if( $_SESSION['manage_coupon']['discount_type'] == "fixed" ) {
			$_SESSION['manage_coupon']['discount'] = $_SESSION['manage_coupon']['discount_value'];
			$total = $total - $_SESSION['manage_coupon']['discount_value'];
		}
		if( $_SESSION['manage_coupon']['discount_type'] == "percentage" ) {
			$_SESSION['manage_coupon']['discount'] = $total * $_SESSION['manage_coupon']['discount_value'] / 100;
			$total = $total - ($total * $_SESSION['manage_coupon']['discount_value'] / 100);
		}
	}

}

Marion::add_action('discount_cart','manage_coupon_discount_cart');


function manage_coupon_cart_discount(&$discount,$total,$shipping_price){
	if( isset($_SESSION['manage_coupon']) && $_SESSION['manage_coupon'] ){
		if( $_SESSION['manage_coupon']['discount_type'] == "fixed" ) {
			$_SESSION['manage_coupon']['discount'] = $_SESSION['manage_coupon']['discount_value'];
			$discount += $_SESSION['manage_coupon']['discount_value'];
		}
		if( $_SESSION['manage_coupon']['discount_type'] == "percentage" ) {
			
			$_SESSION['manage_coupon']['discount'] = $total * $_SESSION['manage_coupon']['discount_value'] / 100;
			$discount +=($total * $_SESSION['manage_coupon']['discount_value'] / 100);
		}

		
	}
	

}

Marion::add_action('cart_discount','manage_coupon_cart_discount');




function manage_coupon_save_discount(&$cart){
	
	
	if( isset($_SESSION['manage_coupon']) && $_SESSION['manage_coupon'] ){
	
		if( $_SESSION['manage_coupon']['discount_type'] == "fixed" ) {
			$sconto = $_SESSION['manage_coupon']['discount_value'];
		}
		if( $_SESSION['manage_coupon']['discount_type'] == "percentage" ) {
			$sconto = $cart->total * $_SESSION['manage_coupon']['discount_value'] / 100;
		}
		if( $cart->status == 'active' || !$cart->status ){
			$cart->discount = $sconto;
		}

		
	}

	


}




Marion::add_action('cart_before_save','manage_coupon_save_discount');



function manage_coupon_close_cart(&$cart){

	if( isset($_SESSION['manage_coupon']) && $_SESSION['manage_coupon'] ){
		
		$database = _obj('Database');
		
		$coupon = $database->select('*','coupon',"id = {$_SESSION['manage_coupon']['id']}");
		if( okArray($coupon) ){
			$coupon = $coupon[0];
			if( !$coupon['multiple_use'] ){
				$database->update('coupon',"id = {$_SESSION['manage_coupon']['id']}",array('used' => 1));
			}
		}
		

		$toinsert = array(
			'coupon_id'		=>	$_SESSION['manage_coupon']['id'],
			'coupon_name'	=>	$_SESSION['manage_coupon']['name'],
			'carrello'		=>	$cart->id
		);
		if( authUser()){
			$current_user = Marion::getUser();
			$toinsert['id_user'] = $current_user->id;
		}
		
		$database->insert('coupon_cart',$toinsert);
		if( $cart->paymentMethod != 'PAYPAL' ){
			unset($_SESSION['manage_coupon']);
		}
	}	
}

Marion::add_action('cart_close','manage_coupon_close_cart');




function manage_coupon_cart_side_menu_top(){

	require_once('classes/Coupon.class.php');
	
	$widget = new WidgetComponent('manage_coupon');

	$params = array();
	if( isset($_SESSION['manage_coupon']) && $_SESSION['manage_coupon'] ){
		$params['coupon_in_uso'] = true;
		$params['dati_coupon'] = $_SESSION['manage_coupon'];

		foreach($params as $k => $v){
			$widget->setVar($k,$v);
		}
	}
	
	
	
	
	$widget->output('widget.htm');
	

}



Marion::add_action('display_cart_side','manage_coupon_cart_side_menu_top');

/*function cart_coupon_widget(){

	require_once('classes/Coupon.class.php');
	$module_dir = 'manage_coupon';
	$widget = Marion::widget($module_dir);

	if( $_SESSION['manage_coupon'] ){
		+-$widget->coupon_in_uso = true;
		$widget->discount = $_SESSION['manage_coupon']['discount'];
	}


	ob_start();
	$widget->output('coupon_carrello_widget.htm');
	$html = ob_get_contents();
	ob_end_clean();
	
	return $html;


}*/





function manage_coupon_set_media_ctrl($ctrl){
	
	if( _var('ctrl') == 'Cart' ){ 
		$ctrl->registerJS('modules/manage_coupon/js/user.js?v=6','end');
	}
}

Marion::add_action('action_register_media_front','manage_coupon_set_media_ctrl');
?>