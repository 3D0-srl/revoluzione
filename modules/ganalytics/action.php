<?php
use Ganalytics\Generator;
use Marion\Core\Marion;
function ganalitycs_display_script_view_product($prodotto){
    echo Generator::scriptViewProduct($prodotto);
}


function ganalitycs_display_script_base(){
    echo Generator::scriptBase();
}


function ganalitycs_display_script_checkout($step=null){
    if( authUser()){
        $cart = \Shop\Cart::getCurrent();
        $orders = $cart->getOrders();
    }else{
        $orders = \Shop\Cart::getCurrentOrders();
       
    }
    echo Generator::scriptCheckout($orders,$step);
}


function ganalitycs_display_get_data_product($id_order){
		
    return Generator::getDataProduct($id_order);
}

function ganalitycs_display_script_cart_thanks($cart){
    
    echo Generator::scriptCartThanks($cart);
}

Marion::add_action('ganalitycs_display_script_base','ganalitycs_display_script_base');
Marion::add_action('ganalitycs_display_script_view_product','ganalitycs_display_script_view_product');
Marion::add_action('ganalitycs_display_script_checkout','ganalitycs_display_script_checkout');
Marion::add_action('ganalitycs_display_script_cart_thanks','ganalitycs_display_script_cart_thanks');




function ganalitycs_set_media_ctrl($ctrl){
		
    $ctrl->registerJS('modules/ganalitycs/js/script.js','head');
    
}
Marion::add_action('action_register_media_front','ganalitycs_set_media_ctrl');


?>