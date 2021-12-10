<?php

/*
function gls_export_save_cart($cart){

	if( is_object($cart) ){
		$database = _obj('Database');
		$check = $database->select('*','gls_cart_tracking',"id_cart='{$cart->id}'");
		if( !okArray($check) ){
			$toinsert = array(
				'id_cart' => $cart->id
			);
			$database->insert('gls_cart_tracking',$toinsert);
		}
	}
	//codice da eseguire quando la funzione viene invocata
}




Marion::add_action('cart_close','gls_export_save_cart');

*/

?>