<?php
use Marion\Controllers\FrontendController;
use Marion\Core\Marion;
use Marion\Entities\User;
use Marion\Entities\Country;
use Shop\{Cart,Order,Address,ShippingMethod,PaymentMethod,Eshop};
use Marion\Entities\Cms\Notification;
class CartController extends FrontendController{
	private $id_cart; //id del carrello;
	private $action; // stabilisce l'action corrente
	private $multilocale; // stabilisce se il sito è multilingua
	private $virtual_cart = false; //// stabilisce se il carrello contiene solo prodotti virtuali
	private $recurrent; // stabilisce se il carrello è relativo ad un pagamneto ricorrente
	private $logged; // stabilisce se l'utente è loggato
	private $cart;
	private $elements; //variabile che contiene gli elemneti dei form
	private $show_taxes = true; //mostra le tasse dell'ordine
	private $reload_cart_after_change_qnt = true;
	private $show_preview_orders = true; //mostra la composizione dell'ordine nel menu laterale
	private $info_cart = array();
	private $time_last_update = 1000; // stabilisce il tempo trascorso dall'ultimo aggiornamento dell carrello
	private $enable_buy_without_registration = true; //abilita carrello senza registrazione
	
	private $one_shipping_method = 4; //todo
	

	private $urls = array(
		1 => _MARION_BASE_URL_."index.php?ctrl=Cart&mod=cart_onepage&action=cart",
		2 => _MARION_BASE_URL_."index.php?ctrl=Cart&mod=cart_onepage&action=cart_datauser",
		3 => _MARION_BASE_URL_."index.php?ctrl=Cart&mod=cart_onepage&action=cart_address",
		4 => _MARION_BASE_URL_."index.php?ctrl=Cart&mod=cart_onepage&action=cart_shipment",
		5 => _MARION_BASE_URL_."index.php?ctrl=Cart&mod=cart_onepage&action=cart_payment",
	);

	private $last_url = _MARION_BASE_URL_."cart.htm";

	private $pages = array(
		'cart' => 'cart.htm',
		'cart_datauser' => 'onepage.htm',
		'cart_address' => 'onepage.htm',
		'cart_new_address' => 'onepage.htm',
		'cart_shipment' => 'onepage.htm',
		'cart_payment' => 'onepage.htm',
		'cart_thanks' => 'cart_thanks.htm',
	);

	function setMedia(){
		parent::setMedia();
		$this->registerJS('modules/cart_onepage/js/cart_general.js?v=15');
		$this->registerCSS('modules/cart_onepage/css/cart.css');
		$this->registerCSS('modules/cart_onepage/css/cart_new.css');
		$action = $this->getAction();
		switch($action){
			case 'cart_datauser':
				$this->registerJS('modules/cart_onepage/js/cart_datauser.js?v=10');
				break;
			case 'cart_new_address':
			case 'cart_address':
				$this->registerJS('modules/cart_onepage/js/cart_address.js?v=9');
				break;
			case 'cart_shipment':
				$this->registerJS('modules/cart_onepage/js/cart_shipment.js?v=5');
				break;
			case 'cart':
				$this->registerJS('modules/cart_onepage/js/cart.js?v=11');
				break;
			case 'cart_payment':
				$this->registerJS('modules/cart_onepage/js/cart_payment.js?v=10');
				break;
			default:
				if( !$this->logged ){
					
					$this->registerJS('modules/cart_onepage/js/cart_login.js?v=4');
				}
				break;

		}
		
		
		
	}

	function display(){
		

		

		$page = _var('page');
		
		if( $page ){
			$this->output($page.'.htm');
		}else{


		
		
			$this->dispatch();
		}
		
	}





	//metodo che stabilisce se il pagamento è ricorrente o meno
	function getRecurrentFlag(){
		if( $this->isAjaxRequest()){
			$formdata = _formdata();
		}else{
			$formdata = _var('formdata');
		}
		if( _var('recurrent_payment') == 1 || $formdata['recurrent_payment'] == 1 ){
			$this->recurrent = true;
		}else{
			$this->recurrent = false;
		}
	}

	function getMultilocaleFlag(){
		$this->multilocale = isMultilocale();
	}
	


	function getConfiguration(){
		$conf = Marion::getConfig('cart_setting');
		if( okArray($conf) ){
			
			$this->show_preview_orders = $conf['show_preview_orders'];
			$this->reload_cart_after_change_qnt = $conf['reload_cart_after_change_qnt'];
			$this->time_last_update = $conf['time_last_update'];
			$this->show_taxes = $conf['show_taxes'];
			$this->enable_buy_without_registration = $conf['enable_buy_without_registration'];
			$this->force_virtual_cart = $conf['virtual_cart'];
		}
	}

	
	function getIdCart(){
		$this->id_cart = _var('id_cart');
	}

	function getVirtualFlag(){
		//se il carrello è ricorrente
		if( $this->force_virtual_cart ){
			
			$this->virtual_cart = true;	
			return;
		}
		if( $this->recurrent ){
			$order = Cart::getCurrentRecurrentPaymentOrder();
			
			if( is_object($order) ){
				$product = $order->getProduct();
				$this->virtual_cart = $product->virtual_product;
			}
		}else{
			$this->virtual_cart = Cart::getVirtualFlag();
		}
	}
	



	//metodo che restituisce il peso del carrello
	function getWeight(){
		$weight = 0;
		if( $this->recurrent ){
			$order = Cart::getCurrentRecurrentPaymentOrder();
			if( is_object($order) ){
				$product = $order->getProduct();
				if( is_object($product) ){
					$weight =  $product->weight*$order->quantity;
					
				}
			}
		}else{
			//controllo se l'utente può trovarsi a questo step del carrello
			if( isset($_SESSION['ADMIN_CART_USER_MODIFY']) && $_SESSION['ADMIN_CART_USER_MODIFY'] ){
				$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
				$weight = $cart->getWeight();
			}else{
				$weight = $this->info_cart['total_weight'];
				//$weight = Cart::getCurrentWeight();
			}
		}

		return $weight;
	}
	
	//metodo che restituisce il totale del carrello
	function getTotal($discount=false){
		$total = 0;
		if( $this->recurrent ){
			$order = Cart::getCurrentRecurrentPaymentOrder();
			if( is_object($order) ){
				$total = $order->getTotalPrice();
			}
		}else{
			
			if( $_SESSION['ADMIN_CART_USER_MODIFY'] ){
				$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
				$total = $cart->getTotal();
			}else{
				$total = $this->info_cart['total'];
				//$total = Cart::getCurrentTotal($discount);
			}
		}

		return $total;
	}

	function getCart(){
		if( isset($this->_cart) && $this->_cart ) return $this->_cart;
		if( $this->id_cart ){
			$cart = Cart::withId($this->id_cart);
			if( is_object($cart) ){
				$this->_cart = $cart;
				return $cart;
			}
		}

		if( authUser() ){
			if( isset($_SESSION['ADMIN_CART_USER_MODIFY']) && $_SESSION['ADMIN_CART_USER_MODIFY'] ){
				$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
			}else{
				$cart = Cart::getCurrent();
			}
			$this->_cart = $cart;
			return $cart;
		}
		return false;
	}


	function getCurrentStep(){
		if( !$this->logged ){
			$step = 1;
		}else{
			$step = 2;
		}


		
		if( $this->logged ){
			$cart = $this->getCart();
			
			if( !$this->virtual_cart ){
				
				if( $cart->shippingMethod ){
					$step = 5;
					if( !$cart->shippingCountry ){
						$step = 4;
					}
					
				}else{
					
					if( $cart->shippingCountry ){
						$step = 4;
						
					}else{
						if( $cart->name ){
							$step = 3;
						}
					}
				}
			}else{
				if( $cart->name ){
					$step = 5;
				}
			}

		}else{
			$current_cart = Cart::getCurrent();

			if( isset($current_cart['data']) ){
				$data =  $current_cart['data'];
				if( !$this->virtual_cart ){
				
					if( $data['shippingMethod'] ){
						$step = 5;
						
					}else{
						if( $data['shippingCountry'] ){
							$step = 4;
							
						}else{
							if( $data['name'] ){
								$step = 3;
							/*}else{
								if( $data['email'] ){
									//$step = 2;
								}*/
							}
						}
					}
				}else{
					if( $data['email'] ){
						$step = 5;
					}
				}
			}

			
			

		}
		//debugga($step);exit;
		return $step;
	}

	//metodo che restituisce gli ordini del carrello
	function getOrders(){
		$orders = array();
		if( $this->id_cart ){
			$cart = Cart::withId($this->id_cart);
			
			if( $this->logged ){
				$current_user = Marion::getUser();
				if( $cart->user == $current_user->id){
					$orders = $cart->getOrders();
				}
			}else{
				$orders = $cart->getOrders();
			}
			$this->cart = $cart;
			
		}else{
			if( $this->recurrent ){
				$order = Cart::getCurrentRecurrentPaymentOrder();
				if( is_object($order) ){
					$orders[0] = $order;
				}
			}else{
				if( isset($_SESSION['ADMIN_CART_USER_MODIFY']) && $_SESSION['ADMIN_CART_USER_MODIFY'] ){
					$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
					$orders = $cart->getOrders();
				}else{
					$orders = Cart::getCurrentOrders();
				}
			}
		}
	
		return $orders;
	}

	

	

	function getAction(){
		return $this->action;
	}

	


	function getDataUser(){
		$data = null;
		if( $this->recurrent ){
			if( isset($_SESSION['sessionCart']['data']) ){
				$data =  $_SESSION['sessionCart']['data'];
				
				if( !okArray($data) ){
					if(authUser()){
						$cart = Cart::getCurrent();
						$data = $cart->prepareForm();
						$_SESSION['sessionCart']['data'] = $data;
						
					}
				}
			}
		}else{
			if( $this->logged ){
				// se l'utente è loggato
				$cart = $this->getCart();
				$data = $cart->prepareForm();
			
			}else{
				//se l'utente sta effettuando un acquisto senza registrazione
				if( isset($_SESSION['sessionCart']['data']) ){
					$data =  $_SESSION['sessionCart']['data'];	
				}

			}
		}

		return $data;
	}
	
	function getShippingMethod(){
		if( $this->recurrent || !$this->logged ){
			$cartData = $this->getDataUser();
			
			$id = $cartData['shippingMethod'];
				
		}else{
			if( isset($_SESSION['ADMIN_CART_USER_MODIFY']) &&  $_SESSION['ADMIN_CART_USER_MODIFY'] ){
				$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
			}else{
				$cart = Cart::getCurrent();
			}
			$id = $cart->shippingMethod;
		}

		return $id;
	}


	function getCountry(){
		$country = '';
		if( $this->recurrent || !$this->logged ){
			$cartData = $this->getDataUser();
			if( $this->recurrent ){
				
				if( $cartData['id_address'] ){
					$data_shipping = Address::withId($cartData['id_address']);
					if( is_object($data_shipping) ){
						$country = $data_shipping->country;
					}
				}else{
					$country = $cartData['shippingCountry'];

				}
			}else{
				$country = $cartData['shippingCountry'];
				
			}
		}else{
			if( isset($_SESSION['ADMIN_CART_USER_MODIFY']) && $_SESSION['ADMIN_CART_USER_MODIFY'] ){
				$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
			}else{
				$cart = Cart::getCurrent();
			}
			$data_shipping = Address::withId($cart->id_address);
			if( is_object($data_shipping) ){
				$country = $data_shipping->country;
			}
		}

		return $country;
	}


	function init($options=array()){
		

		
		parent::init();

		


		$this->action = _var('action');
		$this->setVar('action',$this->action);
		$this->getConfiguration();
		$this->getMultilocaleFlag();
		$this->getRecurrentFlag();
		$this->getVirtualFlag();
		if( authUser()){
			$this->logged = 1;
		}
		$this->getConfiguration();
		$this->getIdCart();
		if( $this->id_cart ){
			$cart = $this->getCart();
			$this->virtual_cart = $cart->virtual_cart;
			$this->info_cart = array(
				'total' => $cart->total,
				'total_without_tax' => $cart->total_without_tax,
				'payment_price' => $cart->paymentPrice,
				'shipping_price' => $cart->shippingPrice,
				'total_weight' => $cart->total_weight,
				'total_tax' => $cart->total_tax,
				'num_products' => $cart->num_products,
				'discount' => $cart->discount,
				'supplement' => $cart->supplement
			);
			
		}else{
			$this->info_cart = Cart::getCurrentInfo();
		}



	
		

		
		
		
		
		
		
		
	
	}

	
	public function closeDB(){
		Marion::closeDB();
	}

	function dispatch(){
		$step = $this->getCurrentStep();
		
		if( !$this->action ){
			if( !$this->logged && $step == 1 ){
				
				$this->login();
			}else{
				header('Location: '.$this->getUrlByStep($step));
			}
		}else{


			
	
			if( $this->checkStepAccess() ){
				
				$this->execute();
				
				$this->render();
				$this->closeDB();
			}else{
				
				$this->redirect();
			}
		}
		
		exit;
		

	}


	function redirect(){
		$step = $this->getStep();
		if( $this->virtual_cart && $step > 2 ){
				$url = $this->getUrlByStep(2);
		}else{
			$url = $this->getUrlByStep($step-1);
		}
		$this->closeDB();
		header('Location: '.$url);
	}

	function login(){
		if( $this->enable_buy_without_registration){ 
			$this->setVar('enable_buy_without_registration',$this->enable_buy_without_registration);
		}

		if( !$this->logged ){
			if( isset($_SESSION['sessionCart']['data']) ){
				$data =  $_SESSION['sessionCart']['data'];
				if( trim($data['email']) ){
					$this->setVar('email',$data['email']);
				}
			}
			
		}
		$this->output('cart_login.htm');
	}



	function checkStepAccess(){
		$check = true;
		switch($this->action){
			
			case 'cart_datauser':
				
				if( $this->isEmpty()){
					$check = false;
				}
				break;
			case 'cart_address':
				$datauser = $this->getDataUser();
				if( !$datauser['email'] ){
					$check = false;
				}
				break;
			case 'cart_shipment':
				$country = $this->getCountry();
				if( !$country ){
					$check = false;
				}
				break;
			case 'cart_payment':
				if( !$this->virtual_cart ){
					$shipping = $this->getShippingMethod();
					if( !$shipping ){
						$check = false;
					}
				}
				break;
		}

		
		return $check;
		
	}

	function render(){
		
		
		if( $this->virtual_cart ){
			$this->setVar('virtual_cart',1);
			$this->setVar("class_nosped","nosped");
		}
		//imposto lo step del carrello
		$step = $this->getStep();
		$this->setVar("step",$step);
		$this->setVar('logged',$this->logged);
		//imposto l'url del pulsante indietro
		if( $step > 1 ){

			if( $this->virtual_cart && $step > 2 ){
				$url_back = $this->getUrlByStep(2);
			}else{
				$url_back = $this->getUrlByStep($step-1);
			}
			$this->setVar("url_back_cart",$url_back);
		}

		$url_current = $this->getUrlByStep($step-1);
		$this->setVar('url_current_cart',$url_current);
		
		if( $this->recurrent ){
			$this->setVar("recurrent_payment",$this->recurrent);
		}
		$this->setVar('show_taxes',$this->show_taxes);
		$this->setVar('show_preview_orders',$this->show_preview_orders);
		if( $this->one_shipping_method ){
			//$this->setVar('one_shipping_method',$this->one_shipping_method);
		}
		if( $this->reload_cart_after_change_qnt){ 
			$this->setVar('reload_cart_after_change_qnt',$this->reload_cart_after_change_qnt);
		}
		
		if( $this->enable_buy_without_registration){ 
			$this->setVar('enable_buy_without_registration',$this->enable_buy_without_registration);
		}
		$this->setVar('main_color','red');
		
		//prendo la pagina template di riferimento
		$template_page = $this->getTemplatePage();
		
		
		//$this->setVar('action',$this->action);
		//$this->setVar('userdata',$_SESSION['userdata']);
		$this->setVar('urls',$this->urls);
		
		$this->loadCartFunctions();

	
		
		if( $this->virtual_cart ){ 
			$this->setVar('num_progress_payment', 2);
		}else{
			$this->setVar('num_progress_sped', 2);
			$this->setVar('num_progress_courier', 3);
			
			$this->setVar('num_progress_payment', 4);
		}
		
		
		
		$this->output($template_page);
		
		
	}

	function getDataMenuSide(){
		
		
		$this->setVar('cart_total_products',$this->info_cart['total']);
		$this->setVar('cart_total_products_without_tax',$this->info_cart['total_without_tax']);
		$this->setVar('cart_total_products',$this->info_cart['total']);
		$this->setVar('cart_total_tax',$this->info_cart['total_tax']); 
		
		$total = $this->info_cart['total'];
		$discount = 0;
		

		if( !$this->id_cart ){
			$shippingPrice = 0;
			if( !$this->virtual_cart ){
				$weight = $this->getWeight();
				$country = $this->getCountry();
				$shippingMethod_id = $this->getShippingMethod();
				$shippingMethod = ShippingMethod::withId($shippingMethod_id);
				//calcolo la tariffa di spedizione
				if(is_object($shippingMethod)){
					$shippingPrice = $shippingMethod->getPrice($country,$weight);
					
				}
			}
		}else{
			$shippingPrice = $this->info_cart['shipping_price'];
			
		}





		if( !$this->id_cart ){
			Marion::do_action('cart_discount',array(&$discount,$total,$shippingPrice));
			if( $discount > 0 ){
				$this->setVar('cart_discount',$discount);
			}
		}else{
			//debugga($this->info_cart);exit;
			$this->setVar('cart_discount',$this->info_cart['discount']);
		}
		
		
		
		$this->setVar('cart_shipping_price',$shippingPrice); 

		$total = $total-$discount;
		$this->setVar('cart_total',$total); 

	}
	

	function checkUpdateCart(){
		$diff = time()-strtotime($this->info_cart['dateLastUpdate']);
		
		if( $this->time_last_update <  $diff){
			//devo aggiornare il carrello

			
			$this->setVar('reload_cart',1);
			
		}
	}


	function updateCart(){

		$cart = $this->getCart();
		if( is_object($cart) ){
			$orders = $cart->getOrders();

			foreach($orders as $v){
				$res = $v->checkQuantity();
				if( $res != 1 ){
					$errors[$v->id] = $res;
				}
				$v->computePrice();
				$v->save();
			}
			$cart->updateTotal();
			return $errors;
		}


	}


	function execute(){
		
		$step = $this->getStep();
		$orders = array();
		if( $this->show_preview_orders OR ($step == 1 || $step == 6) ){
			//prendo gli ordini del carrello
			$orders = $this->getOrders();

			//prendo alcune informazioni degli ordini dai prodotti
			foreach($orders as $k => $ord){
				$prodotto = $ord->getProduct();
				if(is_object($prodotto)){
					
					$orders[$k]->productname = $prodotto->getName();
					$orders[$k]->link = $prodotto->getUrl();
					$orders[$k]->img = $prodotto->getUrlImage(0,'thumbnail');
				}
				
			}
		}
		
		$shop_data = Marion::getConfig('eshop');
		$free_shipping_rest = 0;
		$show_box_free_shipping = false;
		if( $shop_data['enableFreeShipping'] ){
			$show_box_free_shipping = true;
			$soglia = $shop_data['thresholdFreeShipping'];
			
			$free_shipping_rest =  $soglia - $this->info_cart['total'];
			if( $free_shipping_rest <=0 ){
				$show_box_free_shipping = false;
			}

			$message_free_shipping = sprintf(_translate('message_free_shipping','cart_onepage'),Eshop::formatMoney($free_shipping_rest),$GLOBALS['activecurrency']);
			
			$this->setVar('message_free_shipping',$message_free_shipping);

		}

		
		$this->setVar('free_shipping_rest',$free_shipping_rest);
		$this->setVar('show_box_free_shipping',$show_box_free_shipping);
		
		
		
		$this->orders = $orders;
		$this->setVar('ordini',$orders);


		if( $step >= 1 ){
			$cart_data = $this->getDatauser();
			$this->setVar('cart_datauser',$cart_data);
		}

		if( $step == 3 ){
			$user = Marion::getUser();
			$user_address = Address::prepareQuery()->where('id_user',$user->id)->get();
			if( okArray($user_address) ){
				$this->setVar('indirizzi',$user_address);
			}
		}

		if( $step > 4 ){
			$id_courier = $cart_data['shippingMethod'];
			if( $id_courier ){
				$courier = ShippingMethod::withId($id_courier);
				if( is_object($courier) ){
					$this->setVar('courier',$courier);
				}
			}
			
		}

		$this->getDataMenuSide();

		$action = $this->getAction();
		if( !$action ) $action = 'cart';
		switch($action){
			case 'redirect':

				$step = $this->getCurrentStep();
				$url = $this->getUrlByStep($step);
				header('Location: '.$url);
				exit;
				break;
			case 'cart':
				if( authUser()){

					$this->checkUpdateCart();
					
				}
				
				$this->setVar('loading_message','Aggiornamento prezzi e disponibilità');
				
				break;
			case 'cart_datauser':
			
				if( !$this->logged ){
					if( _var('registration') ){
						$this->setVar('registration',1);
					}
				}
				//debugga($cart_data);exit;
				$campi_modificati['country']['default'] = 'IT';
				$dataform = $this->getDataform('cart_datauser',$cart_data,null,$campi_modificati);
				$this->setVar('dataform',$dataform);
				
				break;
	
				
			case 'cart_address':
				if( $this->logged ){
					
				
					
					$cart = Cart::getCurrent();
					if( $this->recurrent ){
						$this->setVar('address_selected',$_SESSION['sessionCart']['data']['id_address']);
					}else{
						$this->setVar('address_selected',$cart->id_address);
					}
					
					if( okArray($user_address) ){
						break;

					}else{
						unset($cart_data['id']);
						$this->action = 'cart_new_address';
					}
					
				}else{
					/*$array = $datauser;
					$data = array(
						'name' => $array['shippingName'],
						'surname' => $array['shippingSurname'],
						'address' => $array['shippingAddress'],
						'email' => $array['shippingEmail'],
						'phone' => $array['shippingPhone'],
						'cellular' => $array['shippingCellular'],
						'postalCode' => $array['shippingPostalCode'],
						'city' => $array['shippingCity'],
						'country' => $array['shippingCountry'],
						'province' => $array['shippingProvince'],
					);
					if( !$data['name'] ){
						$data = $array;
					}*/
					if( $cart_data['shippingCountry'] ){
						$data = array(
							'name' => $cart_data['shippingName'],
							'surname' => $cart_data['shippingSurname'],
							'address' => $cart_data['shippingAddress'],
							'email' => $cart_data['shippingEmail'],
							'phone' => $cart_data['shippingPhone'],
							'cellular' => $cart_data['shippingCellular'],
							'postalCode' => $cart_data['shippingPostalCode'],
							'city' => $cart_data['shippingCity'],
							'country' => $cart_data['shippingCountry'],
							'province' => $cart_data['shippingProvince'],
						);
						$cart_data = $data;
					}
					$this->action = 'cart_new_address';
				}
				
				/*$dataform = $this->getDataForm('cart_address',$data);
				$this->setVar('dataform',$dataform);
				$this->setVar('recurrent_payment',$this->recurrent);*/
				/*ob_start();
				$this->output('form_address.htm',$elements);
				$html = ob_get_contents();
				ob_end_clean();*/

				
				
				
				//$this->setVar('form_address',$html);
				//break;
			case 'cart_new_address':
				$id = _var('id');
				
				if( (int)$id ){
					
					$address = Address::withId($id);
					if( is_object($address) ){
						$cart_data = $address->prepareForm();
						
						$this->setVar('confirm_delete_address_title',_translate('confirm_delete_address_title','cart_onepage'));
						$this->setVar('confirm_delete_address_message',sprintf(_translate('confirm_delete_address_message','cart_onepage'),$cart_data['label']));
					}
					
				}else{
					if( okArray($user_address) ){
						unset($cart_data);
						$cart_data['label'] = _translate('Il mio indirizzo','cart_onepage');
					}
				}

				
				

				$dataform = $this->getDataForm('cart_address',$cart_data);
				$this->setVar('dataform',$dataform);
				$this->setVar('recurrent_payment',$this->recurrent);

				
				
			case 'cart_shipment':
				

				//aggiorno i pesi dei prodotti memorizzati nel database
				if( $this->logged ){
					$ordini = $this->getOrders();
					foreach($ordini as $v){
						$prod = $v->getProduct();
						$v->weight = $prod->weight;
						$v->save();
					}
				}
				$country = $this->getCountry();

				
				$weight = $this->getWeight();
				$shippingMethod_id = $this->getShippingMethod();

				$shippingMethods= ShippingMethod::getAll($country,$weight);

				if( okArray($shippingMethods) ){

					//controllo se i metodi di spedizioni sono validi
					foreach($shippingMethods as $k => $v){
						if( !$v->checkConditions()){
							unset($shippingMethods[$k]);
						}
					}
				
					foreach($shippingMethods as $k => $v){
							$shippingMethods[$k]->price =  $v->getPrice($country,$weight);
					}
					uasort($shippingMethods,function($a,$b){
						if ($a->price == $b->price) {
							return 0;
						}
						return ($a->price < $b->price) ? -1 : 1;
					});
				}
				if( okArray($shippingMethods) ){
					$this->setVar('couriers',$shippingMethods);
				}else{
					$nazione = Country::withId($country);
					$this->setVar('country',$nazione);
				}
				
				$this->setVar('shipping_method_selected',$shippingMethod_id);
				
				/*if( $shippingMethod_id ){
					$shippingMethod = ShippingMethod::withId($shippingMethod_id);
					//calcolo la tariffa di spedizione
					if(is_object($shippingMethod)){
						$shippingPrice = $shippingMethod->getPrice($country,$weight);
						$this->setVar('shippingPrice',$shippingPrice);
					}
				}*/
				break;
			case 'cart_payment':
				
				/*if( !$this->virtual_cart ){
					$weight = $this->getWeight();
					$country = $this->getCountry();
					$shippingMethod_id = $this->getShippingMethod();
					$shippingMethod = ShippingMethod::withId($shippingMethod_id);
					

					

					//calcolo la tariffa di spedizione
					if(is_object($shippingMethod)){
						$shippingPrice = $shippingMethod->getPrice($country,$weight);
						
					}
				}else{
					$shippingPrice = 0;
				}*/
			
				
				if( $this->recurrent ){
					$query = PaymentMethod::prepareQuery()->where('code','PAYPAL');
					$default_data['paymentMethod'] = 'PAYPAL';
					$this->setVar('paymentMethod','PAYPAL');
				}else{
					/*if( $shippingMethod->codEnabled ){
						$query = PaymentMethod::prepareQuery()->where('enabled',1);
					}else{
						$query = PaymentMethod::prepareQuery()->where('enabled',1)->where('code','CONTRASSEGNO','<>');
					}*/
					$query = PaymentMethod::prepareQuery()->where('enabled',1);
				}
				
				$query->where('visibility',1);
				
				$metodi_pagamento = $query->get();
				
				
				if( $_SESSION['ADMIN_CART_USER_MODIFY'] ){
						$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
						$user = $cart->getUser();
						if( is_object($user) ){
							$userCategory = $user->category;
						}else{
							$userCategory = 1;
						}
				}else{
					if( $this->logged ){
						$current_user = Marion::getUser();
						$userCategory = $current_user->category;
					}else{
						$userCategory = 1;
					}

				}
				
				
				if( okArray($metodi_pagamento) ){
					foreach($metodi_pagamento as $k => $v){
						if( !$v->isAvailableForUserCategory($userCategory) ){
							unset($metodi_pagamento[$k]);
						}
					}
				}
				
				//debugga($userCategory);exit;

				//controllo se i metodi di pagamento sono validi
				if( okArray($metodi_pagamento) ){
					foreach($metodi_pagamento as $k => $v){
						if( !$v->checkOtherConditions()){
							unset($metodi_pagamento[$k]);
						}
					}
				}
				
				
				$this->setVar('metodi_pagamento',$metodi_pagamento);
				
				//$this->setVar('total_without_discount',$total_without_discount);
				//$this->setVar('shippingPrice',$shippingPrice);

				$message_redirect_payment = _translate('redirect_to_payment','cart_onepage');
				$this->setVar('loading_message',$message_redirect_payment);
				

		
				break;
			case 'cart_thanks':
				$this->setVar('cart',$this->cart);
				if( $this->cart->user ){
					$current_user = Marion::getUser();
					if( !$this->logged || ( $this->logged && $current_user->id != $this->cart->user)){
						header('Location: '._MARION_BASE_URL_."index.php");
						exit;
					}
				}
				
				//se è la priva volta che vedo il pagamento grazie allora invio la notifica
				//invio l'email di conferma ordine
				if( !$this->cart->mail_sent  ){
					$this->sendMailShop();
					$this->cart->mail_sent = 1;
					$this->cart->save();
					//invio la notifica all''aministratore nella dashboard
					Notification::newOrder($this->cart);
				}
					
				

				
				
				break;
		}


	}


	function sendMailShop(){
		$cart = $this->cart;

		$generale = Marion::getConfig('generale');
		$dati_eshop = Marion::getConfig('eshop');
		//debugga($generale);exit;

		
		$step = $this->getStep();
		$this->setVar("step",$step);
		$this->setVar('show_taxes',$this->show_taxes);
		

		$message_mail = _translate('message_mail_shop','cart_onepage');
		$message_mail = sprintf($message_mail,$cart->name,$cart->surname);
		$this->setVar('message_mail',$message_mail);

		//preparo l'html
		ob_start();
		$this->output('mail/mail_shop.htm');
		$html = ob_get_contents();
		ob_end_clean();
		
		
		
		



		//debugga($html);exit;
		$mail = _obj('Mail');
		$mail->setHtml($html);
		$subject = _translate('subject_order_confirm','cart_onepage');
		$subject = sprintf($subject,$cart->number,$generale['nomesito']);
		
		$mail->setSubject($subject);
		
		
		$mail->setToFromArray(
			array(
				$cart->email,
				$dati_eshop['mail']
			)
		);
		$mail->setFrom($dati_eshop['mail']);
		$res = $mail->send();

		return $res;

		//pippo
	}
	


	function ajax(){
		$action = $this->getAction();
		$formdata = _formdata();
		$step = $this->getStep();
		if( $this->virtual_cart && $step == 2){
			
			$url = $this->getUrlByStep(5);
			
		}else{
			$url = $this->getUrlByStep($step+1);
		}
		
		switch($action){
			case 'no_registration':
				$formdata = $this->getFormdata();
				$array = $this->checkDataForm('cart_no_registration',$formdata);
				if( $array[0] == 'ok' ){
					$_SESSION['sessionCart']['data']['email']= $formdata['email'];
					$_SESSION['sessionCart']['data']['country']= 'IT';

					$risposta = array(
						'result' => 'ok',
					);
				}else{
					$risposta = array(
						'result' => 'nak',
						'error' => $this->messageError($array),
						'field' => $array[2]
					);
				}
				

				break;
			
			case 'change_qnt':
				$id = _var('id');
				$qnt = (int)_var('qnt');
				
				if($id){
					$cart = Cart::getCurrent();
					if(authUser()){
						$order = Order::withId($id);
						if($order){ 
							$order->quantity = $qnt;

							$res = $order->checkQuantity();
							
							if( $res != 1 ){
								$risposta = array(
										'result'=> 'nak',
										'error' => $res
								);
								echo json_encode($risposta);
								exit;
							}
							$order->computePrice();
							$order->save();
						}
						$cart->updateTotal();

					}else{

						$ordini = $cart['orders'];
						
						$data = $ordini[$id];
						$data['quantity'] = $qnt;
						
						

						$order = Order::create();
						$order->set($data);
						
						$res = $order->checkQuantity();
						if( $res != 1 ){
							$risposta = array(
									'result'=> 'nak',
									'error' => $res
							);
							echo json_encode($risposta);
							exit;
						}else{
							$order->computePrice();
							$ordini[$id] = (array)$order;
							Cart::setCurrentOrders($ordini);

						}
					}
				}
				$risposta = array(
					'result' => 'ok',
				);


				break;
			case 'deleteOrder':
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
				
				
				$ordini_undder_cart = $this->under_cart(true);
				$this->setVar('ordini_under',$ordini_undder_cart);
				
				
				$total_carrello = Cart::getCurrentTotalFormatted();
				$risposta = array(
					'result' => 'ok',
					'number_products' => $number_products,
					'total' => $total_carrello,
					'undercart' => $ordini_undder_cart,
				);
				break;
			case 'update_cart':
				$errors = $this->updateCart();
				if( okArray($errors) ){
					$risposta = array(
							'result'=> 'nak',
							'errors' => $errors
					);
				}else{
					$risposta = array(
							'result'=> 'ok',
					);
				}
				break;
			
			case 'cart_ok':
				//debugga($this);exit;
				if( $this->recurrent ){
					$ordine = Cart::getCurrentRecurrentPaymentOrder();
					foreach($formdata as $id =>$qnt){
						if( (int)$id ){
							
							$res = Cart::setCurrentRecurrentPaymentOrder(array('quantity' => $qnt));
							
							if( $res != 1 ){
								$risposta = array(
										'result'=> 'nak',
										'error' => $res,
										'id' => $id,
								);
								echo json_encode($risposta);
								exit;
							}
						}
					}
				}else{
					
					if( $_SESSION['ADMIN_CART_USER_MODIFY'] ){
						$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
						$ordini = $cart->getOrders();
					}else{
						$ordini = Cart::getCurrentOrders();
					}
					// qua aggiorno il carrello qualora siano state cambiate le quantità dei prodotti ordinati
					if( authUser() ){
						foreach($ordini as $v){
							$ordini_old[$v->id] = $v;
						}
						foreach($formdata as $id =>$qnt){
							if( (int)$id ){
								$ord = $ordini_old[$id];

								$ord->set(
										array(
										'quantity'=>(int)$qnt
									));
								$res = $ord->checkQuantity();
								
								if( $res != 1 ){
									$risposta = array(
											'result'=> 'nak',
											'error' => $res,
											'id' => $id,
									);
									echo json_encode($risposta);
									exit;
								}

								$ord->computePrice();
								
								$ord->save();
								if( !$_SESSION['ADMIN_CART_USER_MODIFY'] ){
									$cart = Cart::getCurrent();
								}
								unset($ordini_old[$id]);
								
							}

						}
						if( okArray($ordini_old) ){
							foreach($ordini_old as $v){
								$v->delete();
							}
						}
						$cart->updateTotal();
					}else{
						$cart = Cart::getCurrent();
						$ordini = $cart['orders'];
						foreach($ordini as $k => $ordine){
							$ordini[$k]['quantity'] = (int)$formdata[$k];
						}
						
						

						$res = Cart::setCurrentOrders($ordini);

						if( $res != 1 ){
							$risposta = array(
									'result'=> 'nak',
									'error' => $res
							);
							echo json_encode($risposta);
							exit;
						}
					}
				}
				$step = $this->getCurrentStep();
				if( $step != 1 ){
					$url = $this->getUrlByStep($step);
				}
				$risposta = array(
						'result'=> 'ok',
						'url' => $url
				);
				break;
			case 'cart_datauser_ok':
					//ordine di controllo
					$campi_modificati = array(
						'name' => array('ordine' => 1),
						'surname' => array('ordine' => 2),
						'email' => array('ordine' => 3),
						'fiscalCode' => array('ordine' => 4),
						'phone' => array('ordine' => 5),
						'cellular' => array('ordine' => 6),
						'address' => array('ordine' => 7),
						'postalCode' => array('ordine' => 8),
						'city' => array('ordine' => 9),
						'country' => array('ordine' => 10),
						'province' => array('ordine' => 11),
						'company' => array('ordine' => 12),
						'vatNumber' => array('ordine' => 13),

					);



					if( !$this->logged ){
						//se l'acquisto è con registrazione allora rendo obbligatori i campi username e password. In caso contrario li annullo
						if($formdata['registration'] == 1 ){
							$campi_modificati['username']['obbligatorio'] = 1;
							$campi_modificati['password']['obbligatorio'] = 1;
						}else{
							$formdata['username'] = '';
							$formdata['password'] = '';
						}
					}

					if( $formdata['requestInvoice'] ){
						$formdata['typeBuyer'] = 'company';
					}else{
						$formdata['typeBuyer'] = 'private';
					}



					if( $formdata['typeBuyer'] == 'company' ){
						$campi_modificati['vatNumber']['obbligatorio'] = 1;
						$campi_modificati['company']['obbligatorio'] = 1;
						$campi_modificati['fiscalCode']['obbligatorio'] = 0;
						
					}

					if( $formdata['country'] != 'IT' ){
						$campi_modificati['vatNumber']['tipo'] = '';
						$campi_modificati['fiscalCode']['tipo'] = '';
						$campi_modificati['postalCode']['tipo'] = '';
						$campi_modificati['postalCode']['checklunghezza'] = 0;
						//unset($formdata['province']);
						$campi_modificati['province']['obbligatorio'] = 0;
					}
					
					$array = $this->checkDataForm('cart_datauser',$formdata,$campi_modificati);
					
					if( $array[0] == 'ok'){
						if( $array['requestInvoice'] ){
							if( !trim($array['pec']) && !trim($array['codice_univoco']) ){
								
								$risposta = array(
									'result' => 'nak',
									'error' =>  _translate('missing_sdi_and_pec','cart_onepage'),
									'fields' => array('pec','codice_univoco')
								);
								echo json_encode($risposta);
								exit;
								
							}
						}
					}

					if($array[0] == 'ok'){
						unset($array[0]);
						
						
						//se l'utente non è loggato  controllo i dati inseriti
						if( !$this->logged ){
							//se l'utente effettua un acquisto con registrazione lo salvo
							if($formdata['registration']){
								$array['active'] = 1; // rendo attivo l'utente
								$array['auth'] = 1; //assegno i permessi basi
								$array['category'] = 1; //assegno la categoria di default
								$user = User::create();

								$array['password'] = password_hash($array['password'], PASSWORD_DEFAULT);
								$result = $user->set($array)->save();
								
								// se la registrazione è avvenuta con successo metto in sessione l'utente
								if(is_object($result)){

									if( $formdata['use_for_shipping'] ){
											$url = $this->getUrlByStep(4);
											$data_address = $array;
											$data_address['mail'] = $array['email'];
											$address = Address::create();
											$data_address['id_user'] = $result->id;
											$data_address['label'] = 'My address';
											$address->set($data_address);
											
											
											$address->save();
											if( is_object($address) ){
												$array['id_address'] = $address->id;
											}
										
									}
									Notification::newUser($result);
									$this->sendEmailActivation($result);
									
									/*$generale = Marion::getConfig('generale');
									
									//mando la mail all'amministratore del sito
									$subject = sprintf($GLOBALS['gettext']->strings['subject_registrazione_utente'],$generale['nomesito']);
									

									$mail = _obj('Mail');
									$mail->user = $user;

									
									$mail->setTemplateHtml('mail_nuovo_utente.htm');
									$mail->setSubject($subject);
									
									
									$mail->setTo($generale['mail']);
									$mail->setFrom($generale['mail']);
									$mail->send();

									
									//mando la mail all'utente
									$subject = sprintf($GLOBALS['gettext']->strings['subject_registrazione_utente'],$generale['nomesito']);
							
									$array_activation = array(
											'username' => $result->username,
											'password' => $result->password,
											'key' => $result->buildKeyActivation(),

										);

									$mail = _obj('Mail');
									$mail->user = $result;

									$mail->serialized = base64_encode(serialize($array_activation));
									$mail->setTemplateHtml('mail_attivazione.htm');
									$mail->setSubject($subject);
									
									
									$mail->setTo($result->email);
									$mail->setFrom($generale['mail']);
									$mail->send();*/


									//Marion::sessionize('userdata',$result);
									Marion::setUser($result);
									//salvo i prodotti memorizzati in sessione
									Marion::do_action('action_after_login');
									$this->logged = true;
									//qui dovrei inviare l'email di iscrizione al sito

								}else{
									$risposta = array(
										'result' => 'nak',
										'error' => $GLOBALS['gettext']->strings[$result]
									);
									echo json_encode($risposta);
									exit;
								}
							}else{
								//genero una password temporanea e memorizzo i dati in sessione. Da ora l'utente avrà i permessi di authUserNotLogged()
								$array['username'] = $array['email'];
								$password_not_logged = Cart::randomPassword();
								$array['password_not_logged'] = $password_not_logged;
								$array['dateCreation'] = date('Y-m-d H:i:s');
								
							}

							


						}

						
						//se l'utente è loggato memorizzo i dati del carrello
						
						if( $this->recurrent ){
							foreach($array as $k => $v){
								$_SESSION['sessionCart']['data'][$k]= $v;
							}

						}else{
							if($this->logged){
								//prendo il carrello corrente
								if( $_SESSION['ADMIN_CART_USER_MODIFY'] ){
									$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
								}else{
									$cart = Cart::getCurrent();
								}
								$cart->set($array)->save();

								if( $array['update_data'] ){
									
									$user = Marion::getUser();

									$user->set($array)->save();

									Marion::setUser($user);
								}
								
							}else{
								if( $formdata['use_for_shipping'] ){
									$url = $this->getUrlByStep(4);
									
									$array['shippingName'] = $array['name'];
									$array['shippingSurname'] = $array['surname'];
									$array['shippingPostalCode'] = $array['postalCode'];
									$array['shippingAddress'] = $array['address'];
									$array['shippingCity'] = $array['city'];
									$array['shippingProvince'] = $array['province'];
									$array['shippingCountry'] = $array['country'];
									$array['shippingEmail'] = $array['email'];
									$array['shippingCellular'] = $array['cellular'];
									$array['shippingPhone'] = $array['phone'];
								}
								foreach($array as $k => $v){
									$_SESSION['sessionCart']['data'][$k]= $v;
								}
							}
						}
						
						
						

						$risposta = array(
								'result'=> 'ok',
								'url' => $this->last_url
							);

						
					}else{
						$risposta = array(
							'result' => 'nak',
							'error' => $this->messageError($array),
							'fields' => array($array[2])
						);
					}

				break;
				case 'del_address':
					$id = _var('id');
	
					if( (int)$id ){
						
						$address = Address::withId($id);
						
						if( is_object($address) ){
							$address->delete();
							$risposta = array(
								'result' => 'ok'
							);
						}else{
							$risposta = array(
								'result' => 'nak',
								'error' => 'empty_address'
							);

						}
					}else{
						$risposta = array(
								'result' => 'nak',
								'error' => 'empty_address'
							);
					}
					break;
				case 'add_address':

					$id = _var('id');
					
					if( (int)$id ){
						
						$address = Address::withId($id);
						if( is_object($address) ){
							$data = $address->prepareForm();
						}
					}
					$dataform = $this->getDataForm('cart_address',$data);
					$this->setVar('dataform',$dataform);
					$this->setVar('recurrent_payment',$this->recurrent);
					ob_start();
					
					$this->output('form_address.htm');
					$html = ob_get_contents();
					ob_end_clean();
					$risposta = array(
						'result' => 'ok',
						'html' => utf8_encode($html)
					);
					

					break;
				case 'cart_address_ok':
					$formdata = $this->getFormdata();
					
					
					if( authUser() && !$this->recurrent ){
					
						$id_address = $formdata['id_address'];
						if( $id_address ){
							$address = Address::withId($id_address);
							if( is_object($address) ){
								$cart = Cart::getCurrent();
								$cart->id_address = $id_address;
								$cart->save();
								
								$risposta = array(
									'result' => 'ok',
									'url' => $this->last_url
								);
								
							}else{
								$risposta = array(
									'result' => 'nak',
									'error' => utf8_encode(__('no_address_selected')),
								);
							}
							
						}else{
							$risposta = array(
								'result' => 'nak',
								'error' => utf8_encode(__('no_address_selected')),
							);
						}
					}else{
							
						if( authUser() && $this->recurrent ){
							$id_address = $formdata['id_address'];
							$data_shipping['id_address'] = $id_address;
							foreach($data_shipping as $k => $v){
								$_SESSION['sessionCart']['data'][$k] = $v;
							}

							
							
							$risposta = array(
								'result' => 'ok',
								'url' => $url
							);
						}else{

							$campi_modificati['label']['obbligatorio'] = 0;

							if( $formdata['country'] != 'IT' ){
								$campi_modificati['vatNumber']['tipo'] = '';
								$campi_modificati['fiscalCode']['tipo'] = '';
								$campi_modificati['postalCode']['tipo'] = '';
								$campi_modificati['postalCode']['checklunghezza'] = 0;
								//unset($formdata['province']);
								$campi_modificati['province']['obbligatorio'] = 0;
							}
							$array = $this->checkDataForm('cart_address',$formdata,$campi_modificati);
							
							if( $array[0] == 'ok'){
								
								//$_SESSION['sessionCart']['data']

								$data_shipping = array(
									'shippingName' => $array['name'],
									'shippingSurname' => $array['surname'],
									'shippingAddress' => $array['address'],
									'shippingEmail' => $array['email'],
									'shippingPhone' => $array['phone'],
									'shippingCellular' => $array['cellular'],
									'shippingPostalCode' => $array['postalCode'],
									'shippingCity' => $array['city'],
									'shippingCountry' => $array['country'],
									'shippingProvince' => $array['province'],
								);
								foreach($data_shipping as $k => $v){
									$_SESSION['sessionCart']['data'][$k] = $v;
								}
								
								$risposta = array(
									'result' => 'ok',
									'url' => $url
								);
								
							

								

							}else{
								$risposta = array(
									'result' => 'nak',
									'error' => $this->messageError($array),
									'field' => $array[2]
								);

							}
						}

					}
	
					break;
				case 'add_address_ok':
					
					
					if( authUser() && !$this->recurrent ){
					
						$id_address = $formdata['id_address'];
						if( $id_address ){
							$address = Address::withId($id_address);
							if( is_object($address) ){
								$cart = Cart::getCurrent();
								$cart->id_address = $id_address;
								$cart->save();
								
								$risposta = array(
									'result' => 'ok',
									//'url' => $url_redirect
								);
								
							}else{
								$risposta = array(
									'result' => 'nak',
									'error' => utf8_encode(__('no_address_selected')),
								);
							}
							
						}else{
							$risposta = array(
								'result' => 'nak',
								'error' => utf8_encode(__('no_address_selected')),
							);
						}
					}else{
							
						if( authUser() && $this->recurrent ){
							$id_address = $formdata['id_address'];
							$data_shipping['id_address'] = $id_address;
							foreach($data_shipping as $k => $v){
								$_SESSION['sessionCart']['data'][$k] = $v;
							}

							
							
							$risposta = array(
								'result' => 'ok',
								'url' => $url
							);
						}else{

							$campi_aggiuntivi['label']['obbligatorio'] = 'f';
							$array = $this->checkDataForm('cart_address',$formdata,$campi_aggiuntivi);
						
							if( $array[0] == 'ok'){
								
								//$_SESSION['sessionCart']['data']

								$data_shipping = array(
									'shippingName' => $array['name'],
									'shippingSurname' => $array['surname'],
									'shippingAddress' => $array['address'],
									'shippingEmail' => $array['email'],
									'shippingPhone' => $array['phone'],
									'shippingCellular' => $array['cellular'],
									'shippingPostalCode' => $array['postalCode'],
									'shippingCity' => $array['city'],
									'shippingCountry' => $array['country'],
									'shippingProvince' => $array['province'],
								);
								foreach($data_shipping as $k => $v){
									$_SESSION['sessionCart']['data'][$k] = $v;
								}
								
								$risposta = array(
									'result' => 'ok',
									'url' => $url
								);
								
							

								

							}else{
								$risposta = array(
									'result' => 'nak',
									'error' => $this->messageError($array),
									'field' => $array[2]
								);

							}
						}

					}
					break;
				case 'cart_new_address_ok':
					$recurrent_payment = $formdata['recurrent_payment'];
					$url = $this->getUrlByStep($step);


					if( $formdata['country'] != 'IT' ){
						$campi_modificati['postalCode']['checklunghezza'] = 0;
						$campi_modificati['postalCode']['tipo'] = '';
						$campi_modificati['province']['obbligatorio'] = 0;
					}
					
					$array = $this->checkDataForm('cart_address',$formdata,$campi_modificati);
					
					if( $array[0] == 'ok'){
						if( authUser()){
							$current_user = Marion::getUser();
							$obj = Address::create();
							$array['id_user'] = $current_user->id;
							$obj->set($array);
							
							
							$obj->save();
							$cart = $this->getCart();
							$cart->id_address = $obj->id;
							$cart->save();
							
						
							
							$risposta = array(
								'result' => 'ok',
								'url' => $url
							);
						}else{

						}

						

					}else{
						$risposta = array(
							'result' => 'nak',
							'error' => $this->messageError($array),
							'field' => $array[2]
						);

					}
					break;
				case 'cart_shipment_ok':
					$id_shipping = $formdata['shippingMethod'];
					if( $id_shipping ){
						$shippingMethod = ShippingMethod::withId($id_shipping);


						if( is_object($shippingMethod) ){

							
							//calcolo la tariffa di spedizione
							$country = $this->getCountry();
							$weight = $this->getWeight();
							$shippingPrice = $shippingMethod->getPrice($country,$weight);
							
							
							if( $this->recurrent ){
								$_SESSION['sessionCart']['data']['shippingMethod'] = $id_shipping;
							}else{
								if( authUser()){
									$cart = Cart::getCurrent();
									$cart->shippingMethod = $id_shipping;
									$cart->shippingPrice = $shippingPrice;
									$cart->save();
								}else{
									$_SESSION['sessionCart']['data']['shippingMethod'] = $id_shipping;
									$_SESSION['sessionCart']['data']['shippingPrice'] = $shippingPrice;
								}
							}

							
							$risposta = array(
								'result' => 'ok',
								'url' => $this->last_url
							);

							
							
						}else{
							$risposta = array(
								'result' => 'nak',
								'error' => utf8_encode(__('no_shipping_method_selected')),
							);
						}
						
					}else{
						$risposta = array(
							'result' => 'nak',
							'error' => utf8_encode(__('no_shipping_method_selected')),
						);
					}
					break;
				case 'cart_payment_ok':

					
					$formdata = $this->getFormdata();
					
					if( !$formdata ){
						$formdata = array('paymentMethod' => '');
					}
					
					
					
					$array = $this->checkDataForm('cart_payment_method',$formdata);
					
					
					
					/*if( $array[0] == 'ok'){

						if( $array['paymentMethod'] == 'STRIPE'){
							$stripe = _obj('Stripe');
							$check1 = check_form($formdata1,'stripeCard');
							if( $check1[0] == 'ok' ){
								$check = $stripe->checkCreditCard($formdata1['brand'], $formdata1['cardNumber'], $formdata1['cardExpiryMonth'], $formdata1['cardExpiryYear']);
								if( $check != 1){
									$array[0] = 'nak';
									$array[1] = $check;
								}else{
									$_SESSION['cardStripe'] = utf8_encode(serialize($formdata1));
								}
							}else{
								$array[0] = 'nak';
								$array[1] = $check1[1];
							}
							
						}
					}*/

					

					if($array[0] == 'ok'){
						unset($array[0]);
						if( $this->recurrent ){
							$_SESSION['sessionCart']['data']['paymentMethod'] = $array['paymentMethod'];
						}else{
							if( authUser() ){
								if( !$_SESSION['ADMIN_CART_USER_MODIFY'] ){
									$cart = Cart::getCurrent();
								}else{
									$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
								}
								$cart->set($array)->save();
							}else{
								 $_SESSION['sessionCart']['data']['paymentMethod'] = $array['paymentMethod'];
							}
						}
						
						$res = $this->close();
						
						if( !is_object($res) ){
							$risposta = array(
								'result' => 'nak',
								'error' => $res,
							);
						}else{

							$risposta = array(
								'result' => 'ok',
								'url' => _MARION_BASE_URL_.'index.php?ctrl=Gateway&mod=ecommerce&id='.$res->id,
							);
						}
					}else{
						$risposta = array(
							'result' => 'nak',
							'error' => $this->messageError($array),
						);
					}
					echo json_encode($risposta);
					exit;
					break;
		}
		if( okArray($risposta) ){
			$this->closeDB();
			echo json_encode($risposta);
			exit;
		}

	}
	

	
	

	function close(){
		if( $this->recurrent || !$this->logged ){
			$data = $_SESSION['sessionCart']['data'];
			unset($data['id']);
			$cart = Cart::create();
			$cart->set($data);
			$cart->createNumber();
			$cart->status = 'active';

			if( $this->recurrent ){
				$order = Cart::getCurrentRecurrentPaymentOrder();
				if( is_object($order) ){
					unset($order->id);
					$product = $order->getProduct();
					$frequency_recurrent_payment = $product->recurrent_payment_frequency;
				}
			}else{
				$orders_array = $_SESSION['sessionCart']['orders'];
			}
		}else{
			$cart = Cart::getCurrent();
		}

		$cart->virtual_cart = $this->virtual_cart;
		$cart->recurrentPayment = $this->recurrent;
		

		$data['id'] = $cart->save()->getId();
		
		if( $this->recurrent || !$this->logged ){
			if( $this->recurrent ){
				if( is_object($order) ){
					$order->addToCartWithId($data['id']);
				}
				$cart->recurrent_payment_frequency = $frequency_recurrent_payment;
				$cart->total = Cart::getCurrentRecurrentPaymentTotal();	
			}else{
				Cart::setCurrentData($data);
				foreach($orders_array as $v){
				
					$order = Order::create();
					$order->set($v);
					
					$order->addToCartWithId($data['id']);

				}
			}
		}
		//aggiorno il carrello
		$cart->update();
		
		
		$result = $cart->close();
		
		return $result;
	}

	function getTemplatePage(){

		
		return $this->pages[$this->action?$this->action:'cart'];
	}
	


	function sendEmailActivation($user){
		
		

		$generale = Marion::getConfig('generale');

		

		$array_activation = array(
				'username' => $user->username,
				'password' => $user->password,
				'key' => $user->buildKeyActivation(),

		);

		
		$this->setVar('user',$user);
		$this->setVar('serialized',base64_encode(serialize($array_activation)));
		
		//preparo l'html
		ob_start();
		$this->output('mail/mail_activation.htm');
		$html = ob_get_contents();
		ob_end_clean();

		
		$mail = _obj('Mail');
		$mail->setHtml($html);

		//$subject = sprintf($GLOBALS['gettext']->strings['subject_registrazione_utente'],$generale['nomesito']);
		$subject = sprintf(_translate('registrazione_subject'),$generale['nomesito']);
		$mail->setSubject($subject);
		
		
		$mail->setTo($user->email);
		$mail->setFrom($generale['mail']);
		$res = $mail->send();


	}

	

	//prende lo step corrente del carrello
	function getStep(){
		//prendo lo step
		switch($this->getAction()){
			case 'cart':
			case 'cart_ok':
				$step = 1;
				break;
			case 'cart_datauser':
			case 'cart_datauser_ok':
				$step = 2;
				break;
			case 'cart_address':
			case 'cart_address_ok':
			case 'del_address':
			case 'add_address':
			case 'cart_new_address':
			case 'cart_new_address_ok':
				$step = 3;
				break;
			case 'cart_shipment':
			case 'cart_shipment_ok':
				$step = 4;
				break;
			case 'cart_payment':
			case 'cart_payment_ok':
				$step = 5;
				break;
			case 'cart_thanks':
				$step = 6;
				break;
			default:
				$step = 1;
				break;
		}

		return $step;
	}

	
	//restituisce l'url a partire dallo step
	function getUrlByStep($step){
		
		$base_url = _MARION_BASE_URL_;
		if( $this->multilocale ){
			$base_url = $GLOBALS['activelocale']."/";
		}
		if( $this->recurrent ){
				$urls = array(
					1 => $base_url."cart-recurrent-payment.htm",
					2 => $base_url."cart-recurrent-payment-datauser.htm",
					3 => $base_url."cart-recurrent-payment-address.htm",
					4 => $base_url."cart-recurrent-payment-shipment.htm",
					5 => $base_url."cart-recurrent-payment-checkout.htm",
				);
		}else{

			$urls = $this->urls;
			
		}
		if( array_key_exists($step,$urls) ){
			return $urls[$step];
		}else{
			return '';
		}

		

	}

	function isEmpty(){
		$check = false;
		
		if( $this->recurrent ){
			
			$order = Cart::getCurrentRecurrentPaymentOrder();
			$check = !is_object($order);
		}else{
			if( $_SESSION['ADMIN_CART_USER_MODIFY'] ){
				$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
				$numberProducts = $cart->getNumberProduct();
			}else{
				$numberProducts = Cart::getCurrentNumberProduct();
			}
			if( $numberProducts == 0 ){
				$check = true;
			}
		}

		return $check;
		
	}


	//UTILITY
	function isAjaxRequest(){
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		}else{
			return false;	
		}
	}

	function isMobile(){
		$_server_name = $_SERVER['SERVER_NAME'];
		$_data_tmp =  Marion::getConfig('theme_domain',$_server_name);
		if( $_data_tmp['directory'] == 'mobile' ){
			return true;
		}else{
			return false;
		}
	}





	
	
	function loadCartFunctions(){
	
		$this->addTemplateFunction(
			new \Twig\TwigFunction('cart_menu_selected', function ($val1,$val2) {
				$class = '';
				

				$flag = true;

				if( (int)$val1 && (int)$val2 ){
				$num1 = $val1;
					$num2 = $val2;
				}else{
					$items = array(
						'cart' => 1,
						'datauser' => 2,
						'address' => 3,
						'shipment' => 4,
						'payment' => 5,
						'review' => 6
					);
					$num1 = $items[$val1];
					$num2 = $items[$val2];
				}
				if( $num1 <= $num2 ){
					$flag = true;
				}else{
					$flag = false;
				}




				if( $flag ){
					
					$class .="pallaselezionata";
				}
				
				if( $val1 == $val2){
					$class .= " current";
					return $class;
				}
				return $class;
			})
		);

			$this->addTemplateFunction(
			new \Twig\TwigFunction('cart_menu_flag', function ($val1,$val2) {
					if( (int)$val1 && (int)$val2 ){
						$num1 = $val1;
						$num2 = $val2;
					}else{
						$items = array(
							'cart' => 1,
							'datauser' => 2,
							'address' => 3,
							'shipment' => 4,
							'payment' => 5,
							'review' => 6
						);
						$num1 = $items[$val1];
						$num2 = $items[$val2];
					}
					if( $num1 <= $num2 ){
						return true;
					}else{
						return false;
					}
			})
		);
	}


	function array_province(){
		$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$database = _obj('Database');
		$prov = $database->select('sigla','provincia','','sigla ASC');
		if( okArray($prov) ){			
			foreach($prov as $v){
				$toreturn[$v['sigla']] = $v['sigla'];
			}
		}
		
		return $toreturn;
	}

	function array_type_buyer(){
		$labels = array('private','company');
		foreach($labels as $label){
			$toreturn[$label] = __("type_buyer_".$label);
		}
		return $toreturn;
	}

	function array_nazioni(){
		//$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$nazioni = Country::getAll();
		foreach($nazioni as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		return $toreturn;
	}

	function array_nazioni_spedizione(){
		//$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$shippingMethods = ShippingMethod::prepareQuery()->where('visibility',1)->get();
		
		$nazioni_spedizioni = array();
		foreach($shippingMethods as $v){
			$countries = $v->getCountries();
			if( okArray($countries) ){
				if( okArray($nazioni_spedizioni) ){
					$nazioni_spedizioni = array_merge($nazioni_spedizioni,$countries);
				}else{
					$nazioni_spedizioni = $countries;
				}

			}
			
		}
		$nazioni_spedizioni = array_unique($nazioni_spedizioni);
		
		//getCountries
		$nazioni = Country::getAll();
		foreach($nazioni as $v){
			if( in_array($v->id,$nazioni_spedizioni) ){
				$toreturn[$v->id] = $v->get('name');
			}
		}
		return $toreturn;
	}


	function under_cart(){
		
		if( $_SESSION['ADMIN_CART_USER_MODIFY'] ){
			$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
			$ordini = $cart->getOrders();
		}else{
			$ordini = Cart::getCurrentOrders();
		}
	
	
		foreach($ordini as $k => $ord){
			$prodotto = $ord->getProduct();
			//debugga($prodotto);exit;
			if(is_object($prodotto)){
				$ordini[$k]->productname = $prodotto->getName();
				$ordini[$k]->productname_title = $prodotto->getName(null,false);
				$ordini[$k]->link = $prodotto->getUrl();
				$ordini[$k]->img = $prodotto->getUrlImage(0,'thumbnail');
			}
		}
		return $ordini;
		
		
	}



	function array_metodi_pagamento(){
		$list = PaymentMethod::prepareQuery()->get();
		//$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		foreach($list as $v){
			$toreturn[$v->code] = $v->get('name');
		}
		return $toreturn;
	}

	function messageError($data){
		$campo = _translate($data[2],'cart_onepage');
		$message = '';
		switch($data[3]){
			case 'EMPTY_FIELD':
				
				$message = _translate('mandatory_field','cart_onepage');
				$message = sprintf($message,$campo);
				break;
			case 'ILLEGAL_FIELD':
				
				$message = _translate('not_valid_field','cart_onepage');
				$message = sprintf($message,$campo);
				break;
				
		}
		return $message;
	}





}



?>