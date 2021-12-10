<?php
use Marion\Controllers\FrontendController;
use Marion\Core\Marion;
use Catalogo\{Product,AttributeSet};
use Shop\{Order,Cart};
class AjaxController extends FrontendController{

	

	function ajax(){
		
		$action = $this->getAction();
		switch($action){
			case 'product':

				$this->buyProduct();
				exit;
			case 'checkout':

				$check = $this->checkCart();
				if( $check == 1 ){
					$risposta = array(
						'result' => 'ok'
					);
				}else{
					$risposta = array(
						'result' => 'nak',
						'error' => $check
					);
				}
				echo json_encode($risposta);
				exit;
		}
	}


	function buyProduct(){
		$formdata = $this->getFormdata();
		$database = Marion::getDB();
		
		//verifico se il prodotto � configurabile
		$product = Product::withId($formdata['product']);

		
		if(is_object($product)){
			
			//controllo se il prodotto � configurabile
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
				$min = $product->minOrder;
				$max = $product->maxOrder;
				//se il prodotto � configurabile prendo il prodotto figlio
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
					$product = $child;
				}

				$order = Order::create();
				$order->set($array);
				
				$check_qnt = $order->checkQuantity();
				if( $check_qnt != 1 ) {
					$risposta = array(
					'result' => 'nak',
						'error' => $check_qnt
					);

				}else{
					$risposta = array(
						'result' => 'ok',
						'url' => _MARION_BASE_URL_."index.php?mod=paypal&action=product&id=".$array['product']."&qnt=".$array['quantity']
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


	function checkCart(){
		$orders = Cart::getCurrentOrders();

		foreach($orders as $ord){
			$check = $ord->checkQuantity();
			if( $check != 1 ) return $check;
		}
		return 1;
	}

	function messageError($data){
		$message = '';
		switch($data[3]){
			case 'EMPTY_FIELD':
				$message = _translate('empty_attribute','ecommerce');
				$message = sprintf($message,$data[2]);
				break;
		}
		return $message;
	}
}


?>