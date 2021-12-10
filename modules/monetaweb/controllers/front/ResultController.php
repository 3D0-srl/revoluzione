<?php
class ResultController extends FrontendController{
	public $paymentid;
	public $status_confirmed = 'confirmed';
	public $logged = true;

	function display(){
		$this->paymentid = _var('paymentid');
		$this->logged = authUser();

		$id_cart = _var('id_cart');
		$cart = Cart::withId($id_cart);
		
		
		$data = $_SESSION['monetaweb-payment-result'][$this->paymentid];
		
		
		switch( $data['result'] ){
			case 'CANCELED':
				$this->canceled($cart);
				break;
			case 'NOT APPROVED';
				$error = _translate('payment_not_aproved','monetaweb');
				
				$this->canceled($cart,$error);
				break;
			case 'APPROVED':
				$this->approved($data);
				break;
			default:
				debugga($data);
				break;
		}
		
		
		
	}


	function getCart($data){
		
		$id_cart = $data['merchantOrderId'];
		$cart = Cart::withId($id_cart);
		return $cart;
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


	function approved($data){
		$cart = $this->getCart($data);
		unset($_SESSION['sessionCart']['data']['id']);
		unset($_SESSION['sessionCart']['data']['paymentMethod']);
		unset($_SESSION['sessionCart']['orders']);
	

		$status_confirmed = $this->status_confirmed;
		$cart->changeStatus($status_confirmed);
		
		$automatic_stock_type = Marion::getConfig('eshop','automaticStockType');
		
		if( $automatic_stock_type == 'onConfirmed' ){
			$automatic_stock = Marion::getConfig('eshop','automaticStock');
			if($automatic_stock){
				$cart->decreaseInventory();
			}
		}

		header('Location: '._MARION_BASE_URL_."cart-thanks/".$cart->id.".htm");
		exit;
	}
}	


?>