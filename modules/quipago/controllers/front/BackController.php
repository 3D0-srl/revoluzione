<?php
use Marion\Controllers\FrontendController;
use Shop\Cart;
use Marion\Core\Marion;
class BackController extends FrontController{
	

	function display(){
        $database = _obj('Database');

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
				   $close_cart = Marion::getConfig('eshop','closeCart');
		
				   if( $close_cart && $automatic_stock_type == 'onClose' ){
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
            $messaggio_errore = $transaction['messaggio'];
           
            
            
        }else{
            $database->insert("transactionCartaSi",$transaction);
			$automatic_stock_type = Marion::getConfig('eshop','automaticStockType');
		
			if( $automatic_stock_type == 'onConfirmed' ){
				$automatic_stock = Marion::getConfig('eshop','automaticStock');
				if($automatic_stock){
					$cart->decreaseInventory();
				}
			}
            $cart = Cart::withNumber($cartNumber);
            if(is_object($cart)){
                $cart->changeStatus($status_confirmed);
                
                header('Location: '._MARION_BASE_URL_.'cart-thanks/'.$cart->id.'.htm');
                exit;
            }else{
                //errore 898
                
                //$template->errore_generico('898');
            }
            
                
        }
		

		
		
	}

	


	


}



?>