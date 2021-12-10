<?php
/*
Marion::add_action('ecommerce_order_comes_from','amazon_ecommerce_order_view_filter');
function amazon_ecommerce_order_view_filter(&$comesFrom){	
	$comesFrom .= "comesFrom LIKE '%AMAZON%' OR ";
}


Marion::add_action('ecommerce_order_view','amazon_publicher_order_view');


function amazon_publicher_order_view(&$cart,&$ordini){
	if( preg_match('AMAZON',$cart->comesFrom) ){
	
		foreach($ordini as $k => $v){
			if( !$v->product ){
				
				$ordini[$k]->productname = $v->custom2;
				$ordini[$k]->sku = $v->custom2;
			}
		}
		
	}
}


Marion::add_action('product_form','amazon_product_form');


function amazon_product_form(&$campi_aggiuntivi){
	$formdata = _var('formdata');
	
	
	
	
	//debugga($formdata);exit;
}


function amazon_save_product($product){
	$formdata = _var('formdata');
	if( !okArray($formdata) ){
		$formdata = _formdata();
	}
	if( !okArray($formdata)) return false;
	if( $formdata['parent'] == 0 && $product->parent ) return false;
	require_once('classes/AmazonProduct.class.php');
	$list = AmazonProduct::prepareQuery()->where('id_product',$formdata['id'])->get();
	foreach($list as $v){
		$old[$v->id_account][$v->marketplace] = $v;
	}
	
	//CREO I CAMPI DEL FORM
	$GLOBALS['campi_amazon_form_product'] = array(	
		'disable_sync' =>  array(
			'campo'=>'disable_sync',
			'type'=>'checkbox',
			'tipo' => 'Boolean',
			'options'=> array(0,1),
			'default'=> 0,
			'unique_value' => 't',
			'obbligatorio'=>'f',
			'ifisnull' => 2,
			'value_ifisnull' => 0,
			'etichetta'=> 'disabilita amazon',
			'post_function' => '',
			'pre_function' => '',
		),
		'new_product' =>  array(
			'campo'=>'new_product',
			'type'=>'checkbox',
			'tipo' => 'Boolean',
			'options'=> array(0,1),
			'default'=> 0,
			'unique_value' => 't',
			'obbligatorio'=>'f',
			'ifisnull' => 2,
			'value_ifisnull' => 0,
			'etichetta'=> 'nuovo prodotto',
			'post_function' => '',
			'pre_function' => '',
		),
		'parent_description' =>  array(
			'campo'=>'parent_description',
			'type'=>'checkbox',
			'tipo' => 'Boolean',
			'options'=> array(0,1),
			'default'=> 0,
			'unique_value' => 't',
			'obbligatorio'=>'f',
			'ifisnull' => 2,
			'value_ifisnull' => 0,
			'etichetta'=> 'dati del prodotto padre',
			'post_function' => '',
			'pre_function' => '',
		),

		'bullet_1' =>  array(
			'campo'=>'bullet_1',
			'type'=>'text',
			'obbligatorio'=>'f',
			'default'=>'',
			'etichetta'=>'bullet 1'
		),
		'price' =>  array(
			'campo'=>'price',
			'type'=>'text',
			'tipo' => 'prezzo',
			'obbligatorio'=>'f',
			'default'=>'',
			'etichetta'=>'prezzo'
		),
		'bullet_2' =>  array(
			'campo'=>'bullet_2',
			'type'=>'text',
			'obbligatorio'=>'f',
			'default'=>'',
			'etichetta'=>'bullet 2'
		),
		'bullet_3' =>  array(
			'campo'=>'bullet_3',
			'type'=>'text',
			'obbligatorio'=>'f',
			'default'=>'',
			'etichetta'=>'bullet 3'
		),
	);
	
		
	
	foreach($formdata['modules']['amazon'] as $id_account => $data){
		
			foreach($data as $market => $values){
				$array = check_form($values,'amazon_form_product');
				
				if( $array[0] == 'ok'){
					$obj = $old[$id_account][$market];
					
					if( !$obj ){
						$obj = AmazonProduct::create();
						
					}
					$obj->set($array);
					$obj->id_account = $id_account;
					$obj->marketplace = $market;
					$obj->id_product = $product->id;
					
					$obj->save();
					
					
				}
			}
	}
	
}


Marion::add_action('after_save_product','amazon_save_product');
Marion::add_action('after_save_my_product','amazon_save_product');


Marion::add_action('ecommerce_change_status_cart','amazon_change_status_cart');


function amazon_change_status_cart(&$cart,&$array,&$risposta){
	
	if( preg_match('/amazon/',strtolower($cart->comesFrom) ) ){
		
		$status_old = $cart->status;
		$status_new = $array['status'];
		if( $status_old == $status_new ){
			$risposta = array(
				'result' => 'nak',
				'errore' => "Specificare un nuovo stato",
			);
		}else{
			
			if($status_new == 'sent'){
				$database = _obj('Database');
				$order_data = $database->select('*','amazon_order',"id_marion={$cart->id}");
				
				$order_rows = $database->select('*','amazon_order',"id_marion={$cart->id}");
				if( okArray($order_data)){
					$order_data = $order_data[0];
					$order_rows = $database->select('*','amazon_order_item',"id_order='{$order_data['id_amazon']}'");
					
					require('cpigroup/php-amazon-mws/includes/classes.php');
					require('classes/AmazonTool.class.php');
					require('classes/AmazonStore.class.php');
					require('classes/AmazonOrders.class.php');
					$store_obj = AmazonStore::withId($order_data['id_account']);
					
					
					$order_obj = AmazonOrders::init($store_obj);
					$corrieri = $store_obj->getCarriersExit();
					
					foreach($corrieri as $v){
						$map_corrieri_exit[$v['id_marion']][$v['market']] = $v['id_amazon'];
					}
					if( $map_corrieri_exit[$cart->shippingMethod][$order_data['market']] || $map_corrieri_exit[$cart->shippingMethod][0] ){
						if(  $map_corrieri_exit[$cart->shippingMethod][$order_data['market']] ){
							$order_data['CarrierName'] = $map_corrieri_exit[$cart->shippingMethod][$order_data['market']];
						}
						if(  $map_corrieri_exit[$cart->shippingMethod][0] ){
							$order_data['CarrierName'] = $map_corrieri_exit[$cart->shippingMethod][0];

						}
					}else{
						$order_data['CarrierName'] = 'standard';
					}

					
					
					$order_data['ShippingMethod'] = 'standard';
					$order_data['ShipperTrackingNumber'] = $array['trackingCode'];
					
					
					//$order_obj->changeStatus($order_data,$order_rows);
					


					$cart->changeStatus($status_new,null,$array['note']);
					if( $array['trackingCode'] ){
						$cart->set(
							array( 'trackingCode' => $array['trackingCode']
							))->save();
					}
					$template = _obj('Template');
					$risposta = array(
						'result' => 'ok',
						'history' => $html,
						'status' => $template->getStatusCart($cart->status),
						);
					if( $cart->datePayment){
						$risposta['datePayment'] = strftime('%d/%m/%Y',strtotime($cart->datePayment));
					}
					if( $cart->dateShipping){
						$risposta['dateShipping'] = strftime('%d/%m/%Y',strtotime($cart->dateShipping));
					}
					//$order_obj = AmazonOrders::init($store_obj);

					
				}
				
				
			}else{
				$risposta = array(
					'result' => 'nak',
					'errore' => "Cambio di stato non ammesso.",
				);
			}

		}
		

		
		
	}



}
*/

?>