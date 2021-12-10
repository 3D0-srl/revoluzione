<?php
use Marion\Controllers\FrontendController;
use Shop\Cart;
use Marion\Core\Marion;
class GatewayController extends FrontendController{
    
    function display(){
        $action = $this->getAction();
        switch($action){
            case 'success':
                $this->success();
                break;
            case 'nak':
                $this->nak();
                break;
            default:
                $this->pay();
                break;
        }
    }

    function check($cart=null){
        if( is_object($cart) ){
            $orders = $cart->getOrders();
            if( !okArray($orders) ){
                header('Location: '._MARION_BASE_URL_.'cart.htm');
                exit;
            }
        }else{
            header('Location: '._MARION_BASE_URL_.'cart.htm');
            exit;
        }
    }

    function pay(){
        //debugga('qua');exit;
        $id_cart = _var('id');

        $cart = Cart::withId($id_cart);
        $this->check($cart);
        if( $cart->status == 'waiting' || $cart->status == 'active' ){
            
            Marion::do_action('action_payment',array(&$cart));
            
        }
    }


    function success(){
        $idCart = _var('id');
	
        $cart = Cart::withId($idCart);
        if( authUser() ){
            if( !$_SESSION['ADMIN_CART_USER_MODIFY'] ){
                $current_user = Marion::getUser();
                if($cart->user != $current_user->id){
                    //$template->not_auth();
                    header('Location: '._MARION_BASE_URL_.'index.php');
                    exit;

                } 
            }
        }else{
            if($cart->password_not_logged != $_SESSION['sessionCart']['data']['password_not_logged']){
                //$template->not_auth();
                header('Location: '._MARION_BASE_URL_.'index.php');
                exit;
            } 
            

        }

        if( $cart->sessionId != session_id() ){
            
            header( "Location: "._MARION_BASE_URL_."cart.htm");
            
        }
        if( isMultilocale()){
            header( "Location: "._MARION_BASE_URL_."{$GLOBALS['activelocale']}/cart-thanks/".$cart->id.".htm");
        }else{
            header( 'Location: '._MARION_BASE_URL_.'cart-thanks/'.$cart->id.'.htm');
        }
    }

    function nak(){
        header('Location: '._MARION_BASE_URL_.'index.php');
    }

}