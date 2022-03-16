<?php
use Marion\Controllers\ModuleController;
use Shop\Cart;
use Marion\Core\Marion;
class IndexController extends ModuleController{
    
    private $transaction;

    function cancel(){
        //prendo il carrello di riferimento
        $cartNumber = $this->transaction['codTrans'];
            
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
    }

    function success(){
        $database = Marion::getDB();
        $cartNumber = $this->transaction['codTrans'];

        $status_confirmed = Marion::getConfig('quipago_module','status_confirmed');
        if( $this->transaction['esito'] == 'KO' ){


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
            $messaggio_errore = $this->transaction['messaggio'];
           
            
            
        }else{
            $database->insert("transactionCartaSi",$this->transaction);
            $automatic_stock_type = Marion::getConfig('eshop','automaticStockType');
            
            $cart = Cart::withNumber($cartNumber);
            if( $automatic_stock_type == 'onConfirmed' ){
                $automatic_stock = Marion::getConfig('eshop','automaticStock');
                if($automatic_stock){
                    $cart->decreaseInventory();
                }
            }
            
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

	function display(){
		
        $this->transaction = $_GET;
        
        if(  $this->transaction['esito'] == 'ANNULLO' || $this->transaction['esito'] == 'ERRORE' ){
            $this->cancel();
            
        }else if( $this->transaction['esito'] == 'OK' ){
    
            $this->success();
        }else{
            
           header('Location: '._MARION_BASE_URL_.'p/404');
           die();
                
        }

			
			
		
		
	}

	


	


}



?>