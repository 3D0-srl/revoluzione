<?php
class RecoveryController extends FrontendController{
	public $logged = true;
	

	function display(){
		$this->logged = authUser();
		$id_cart = _var('id_cart');
		$cart = Cart::withId($id_cart);
		if( is_object($cart) ){
			$this->canceled($cart);
		}
		header('Location: '._MARION_BASE_URL_."cart-payment.htm");
	}

	function canceled($cart,$error=''){
		$automatic_stock_type = Marion::getConfig('eshop','automaticStockType');
		$close_cart = Marion::getConfig('eshop','closeCart');

		if( $close_cart && $automatic_stock_type == 'onClose' ){
			$cart->changeStatus('payment_paypal_canceled');
			$automatic_stock = Marion::getConfig('eshop','automaticStock');
			if($automatic_stock){
				$cart->increaseInventory();
			}
			if( $this->logged ) {
				$cart->set(
					array(	
						'status'=>'active',
						'paymentMethod'=> NULL
					)
				)->save();
			}
		}

		if( !$this->logged ) {
			
			$cart->changeStatus('deleted');
		}
		if( $error ){
			header('Location: '._MARION_BASE_URL_."cart-payment.htm&error_monetaweb=".$error);
			
		}else{
			header('Location: '._MARION_BASE_URL_."cart-payment.htm");
		}
		exit;
	}
}	


?>