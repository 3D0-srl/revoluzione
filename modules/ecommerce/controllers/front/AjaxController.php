<?php
use Marion\Controllers\FrontendController;
use Marion\Core\Marion;
use \Product;
use \AttributeSet;
use Shop\{Cart,Order};
class AjaxController extends FrontendController{
	

	


	function ajax(){
		$action = $this->getAction();
		$current_user = Marion::getUser();
		switch($action){
			case 'addCart':
				$this->addToCart();
				break;
			case 'deleteOrder':
				$this->deleteOrder();
				break;
			case 'add_to_wishlist':
				$product = _var('product');
				
				if( !authUser()){
					$risposta = array(
						'result' => 'nak',
						'error' => _translate('not_logged_wishlist','ecommerce')
					);
				}else{
					$product = Product::withId($product);
					
					if( is_object($product) ){
						//debugga($product);exit;
						if( $product->isInWhishlist() ){
							$risposta = array(
								'result' => 'nak',
								'error' => _translate('product_in_wishlist','ecommerce')
							);
						}else{
							$product->addToWishlist();
							
							$database = _obj('Database');
							$num = $database->select('count(*) as cont',"wishlist","user={$current_user->id}");
							$risposta = array(
								'result' => 'ok',
								'info' => _translate('product_added_wishlist','catalogo'),
								'tot' => $num[0]['cont']
							);
						}
					}else{
						$risposta = array(
							'result' => 'nak',
							'error' => _translate('product_not_exists','ecommerce')
						);
					}
				}
				echo json_encode($risposta);
				break;
			case 'remove_from_wishlist':
				$product = _var('product');
				if( !authUser()){
					$risposta = array(
						'result' => 'nak',
						'error' => _translate('not_logged_wishlist','ecommerce')
					);
				}else{
					$product = Product::withId($product);
					if( is_object($product) ){
						if( !$product->isInWhishlist() ){
							$risposta = array(
								'result' => 'nak',
								'error' => _translate('product_not_in_wishlist','ecommerce')
							);
						}else{
							$product->removeFromWishlist();
							
							$database = _obj('Database');
							$num = $database->select('count(*) as cont',"wishlist","user={$current_user->id}");
							$risposta = array(
								'result' => 'ok',
								'info' => _translate('product_removed_wishlist','ecommerce'),
								'tot' => $num[0]['cont']
							);
						}
					}else{
						$risposta = array(
							'result' => 'nak',
							'error' => _translate('product_not_exists','ecommerce')
						);
					}
				}
				echo json_encode($risposta);
				break;
		}

	

	}


	function deleteOrder(){
		$id = _var('id');
		if($id){
			$cart = Cart::getCurrent();
			if(authUser()){
				$order = Order::withId($id);
				if($order) $order->delete();
				$cart->updateTotal();

			}else{
				
				unset($cart['orders'][$id]);
				
				Cart::setCurrentOrders($cart['orders']);
			}
		}
		$number_products = Cart::getCurrentNumberProduct();
		

			
		$undercart = $this->underCart(true);
		
		
		$total_carrello = Cart::getCurrentTotalFormatted();
		$risposta = array(
			'result' => 'ok',
			'number_products' => $number_products,
			'total' => $total_carrello,
			'undercart' => $undercart,
		);
		echo json_encode($risposta);
		exit;
	}


	function addToCart(){
		
		$formdata = _var('formdata');
		$formdata = parse_str($formdata, $params);
		$indice = _var('indice');
		if( $indice ){
			$formdata = $params['formdata'.$indice];
			if( !$formdata['quantity'] ){
				$formdata['quantity'] = 1;
			}
		}else{
			$formdata = $params['formdata'];
		}
		
		//verifico se il prodotto è configurabile
		$product = Product::withId($formdata['product']);

		
		if(is_object($product)){
			
			//controllo se il prodotto è configurabile
			if( $product->isConfigurable()){

				//prendo l'insieme di attributi del prodotto
				$attributeSet = AttributeSet::withId($product->attributeSet);
				
				if($attributeSet){
					
					$attributeSelect = $attributeSet->getAttributeWithValues(); 

					foreach($attributeSelect as $k => $v){
						$campi_controllo_aggiuntivi[$k] = array(
								'campo'=>$k,
								'type'=>'select',
								'options' => $v,
								'obbligatorio'=>'t',
								'default'=>'0',
								'etichetta'=>$k
							);
					}
					
				}else{
					$opzioni_prodotto = array();
					$children = $product->getChildren();
					if( okArray($children) ){
						foreach($children as $v){
							$opzioni_prodotto[$v->id] = $v->id;
						}

					}
					
					$campi_controllo_aggiuntivi['child'] = array(
						'campo'=> 'child',
						'type'=>'select',
						'options' => $opzioni_prodotto,
						'obbligatorio'=>'t',
						'default'=>'0',
						'etichetta'=> __('option_product')
					);
				}
				
			}

			
			
			
			//controllo dei dati
			$array = $this->checkDataForm('addCart',$formdata,$campi_controllo_aggiuntivi);
			
			if($array[0] == 'ok'){
				unset($array[0]);
				$array['weight'] = $product->getWeigth();
				//se il prodotto è configurabile prendo il prodotto figlio
				if($product->isConfigurable()){
					if( $product->attributeSet ){
						foreach($attributeSelect as $k=>$v){
							$dataAttributes[$k] = $array[$k];
						}

						$child = $product->getChildWithAttributes($dataAttributes);
					}else{
						$child = Product::withId($array['child']);
					}
					
					$array['product'] = $child->getId();
					$array['weight'] = $child->getWeigth();
				}
				
				//verifico se il prodotto ha un pagamento ricorrente

				
				if( $product->recurrent_payment ){
					$res = Cart::setCurrentRecurrentPaymentOrder($array);
					if( $res == 1 ){
						if( isMultilocale() ){
							$url_recurrent_payment = "/".$GLOBALS['activelocale']."/cart-recurrent-payment.htm";
						}else{
							$url_recurrent_payment = "/cart-recurrent-payment.htm";
						}
						
						$risposta = array(
							'result' => 'ok',
							'url_recurrent_payment' => $url_recurrent_payment
						);
					}else{
						$risposta = array(
							'result' => 'nak',
							'error' => $res
						);
					}
					echo json_encode($risposta);
					exit;
				}else{
				
					//aggiungo il prodotto al carrello
					if( $_SESSION['ADMIN_CART_USER_MODIFY'] ){
						$res = Cart::add($array,$_SESSION['ADMIN_CART_USER_MODIFY']);
					}else{
						$res = Cart::add($array);
					}
				}
				
				
				
				if( $res == 0){
					$risposta = array(
						'result' => 'nak',
						'error' => $res
					);

				}else{
					//ricalcolo il numero di prodotti e il totale del carrello
					$number_products = Cart::getCurrentNumberProduct();
					$undercart = $this->underCart(true);
					$total_carrello = Cart::getCurrentTotalFormatted();
					if( is_object($child) ){
						$name_product = $child->get('name');
					}else{
						$name_product = $product->get('name');
					}
					$text_popup = __('add_to_cart_ok',
									NULL,
									array(
										$name_product,	
										$number_products,
										$total_carrello,
										_MARION_CURRENCY_
									)
								);

					$text_popup_mobile = __('add_to_cart_mobile_ok',
									NULL,
									array(
										$name_product
									)
								);
					if( $child ){
						$html_popup = $this->successPopup($child,$array['quantity']);
					}else{
						$html_popup = $this->successPopup($product,$array['quantity']);
					}
					//debugga($html_popup);exit;
					
					$risposta = array(
						'result' => 'ok',
						'text_popup' => $text_popup,
						'text_popup_mobile' => $text_popup_mobile,
						'number_products' => $number_products,
						'total' => $total_carrello,
						'undercart' => $undercart,
						'html_popup' => $html_popup,
						'text_success_btn' => _translate('Aggiunto al carrello','ecommerce')
					);
				}


			}else{
				$risposta = array(
					'result' => 'nak',
					'error' => $this->messageError($array)
				);
		
			}
			
		}else{
			$risposta = array(
				'result' => 'nak',
				'error' => $GLOBALS['gettext']->strings['no_product_in_catalog']
				
			);
		
		}

		echo json_encode($risposta);
		exit;
	}


	function successPopup($product,$qnt){
		$this->setVar('product',$product);
		$this->setVar('qnt',$qnt);
		ob_start();
		$this->output('partials/success.htm');
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}


	function messageError($data){
		$message = '';
		switch($data[3]){
			case 'EMPTY_FIELD':
				$message = _translate('empty_attribute','ecommerce');
				$message = sprintf($message,$data[2]);
				break;
			case 'ILLEGAL_FIELD':
				$message = _translate('not_valid_field','ecomerce');
				$message = sprintf($message,$data[2]);
				break;
		}
		return $message;
	}


	
}


?>