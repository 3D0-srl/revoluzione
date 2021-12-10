<?php
use Marion\Controllers\FrontendController;
use Marion\Core\Marion;
use Shop\Cart;
class PaypalcancelController extends FrontendController{

	private $token = '';
	private $logged = false;

    function display(){

		$this->logged = authUser();

		$this->token = _var('token');
		$transaction = $this->getTransaction();
		if( okArray($transaction) ){
			$transaction = $transaction[0];
			$cart = Cart::withId($transaction['cartId']);
			
			if( $transaction['status'] == 'pending' ){
			
				$this->cancelTransaction();
				


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

			}
			
			
			
		}
		//stiamo nel caso di un carrello checkout rapido
		if( $transaction['rapid_checkout'] ){
			if( $transaction['type_checkout'] == 'product' ){
				
				$orders = $cart->getOrders();
				foreach($orders as $v){
					$product = $v->getProduct();
				}
				if( is_object($product) ){
					$url = $product->getUrl();
					header('Location: '.$url);
					exit;
				}
			}else{
				header('Location: '._MARION_BASE_URL_.'cart-review.htm');
				exit;
			}
		}
		header('Location: '._MARION_BASE_URL_.'cart-payment.htm');

        
        
    }



	function getTransaction(){
		

		$database = Marion::getDB();
	    $transaction = $database->select('*','transactionPayPal',"token = '{$this->token}'");
	    return $transaction;
	}

	function cancelTransaction(){
		$database = Marion::getDB();
		$database->update('transactionPayPal',"token = '{$this->token}'",array('status'=>'canceled','checked'=>1));
		return;
	}

}