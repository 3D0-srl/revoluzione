<?php
use Marion\Controllers\ModuleController;
use Shop\Cart;
use Marion\Core\Marion;
class IndexController extends ModuleController{
	

	function display(){
		
        $transaction = $_GET;
      
        if( $transaction['esito'] == 'ANNULLO' ){
        
            
            //prendo il carrello di riferimento
            $cartNumber = $transaction['codTrans'];
            
            $cart = Cart::withNumber($cartNumber);
           
            if( authUser() ) {
                if(is_object($cart)){
                    
                    $cart->changeStatus('payment_quipago_canceled');
                    $cart->createNumber();
                    $cart->set(
                        array(	
                            'status'=>'active',
                            'paymentMethod'=> NULL
                        )
                    )->save();
        
                    //ripristino la merce ordinata
                    $automatic_stock = getConfig('eshop','automaticStock');
                    $automatic_stock_type = getConfig('eshop','automaticStockType');
                    if($automatic_stock && $automatic_stock_type == 'onClose'){
                        $cart->increaseInventory();
                    }
                }
            }else{
                if(is_object($cart)){
                    $cart->changeStatus('payment_quipago_canceled');
                    $cart->changeStatus('deleted');
                }
            }
            if (Marion::exists_action('redirect_payment_cancel')){
                Marion::do_action('redirect_payment_cancel',array($cart));
            }
            header("Location: "._MARION_BASE_URL_."cart-payment.htm");
        }else{
            
           header('Location: '._MARION_BASE_URL_.'p/404');
           die();
                
        }

			
			
		
		
	}

	


	


}



?>