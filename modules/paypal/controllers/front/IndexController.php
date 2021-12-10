<?php
require_once('modules/paypal/lib/vendor/autoload.php');
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersPatchRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException;
use Shop\{Cart,Order,ShippingMethod};
use Marion\Core\Marion;
use Marion\Controllers\FrontendController;

class IndexController extends FrontendController{
	private $options = array();
	private $rapid_checkout = false;
	private $type_checkout = '';
	private $token;


	private $mandadory_address_fields = array();


	/* REQUEST BODY  CREATE ORDER */
	private function getDataOrderRequest($cart,$update=false){
		
		
		$shippingPrice = round($cart->shippingPrice,2);
		$total = round($cart->total-$cart->discount+$cart->supplement,2);
		$paymentPrice = round($cart->paymentPrice,2);


		$amount = $shippingPrice+$total+$paymentPrice;
		
		//creo la descrizione dell'ordine
		$amountFormatted = number_format($amount, 2, ',', '');
		$message = sprintf(_translate('description_order_paypal','paypal'),$amountFormatted,$cart->currency,$cart->number,$GLOBALS['setting']['default']['GENERALE']['nomesito']);
		

		$data = array();
		
		$data = array(
			'reference_id' => "{$cart->id}",
			'description' => $message,
			//'custom_id' => _var('product'),
		);
		
		$data['amount'] =
			array(
				'currency_code' => $cart->currency,
				'value' => "{$amount}",
				'breakdown' =>
				 array(
					'item_total' =>
						array(
							'currency_code' =>  $cart->currency,
							'value' => "{$total}",
						),
					'shipping' =>
						array(
							'currency_code' => $cart->currency,
							'value' => "{$shippingPrice}"
						),
					'tax_total' =>
						array(
							'currency_code' => $cart->currency,
							'value' => "0"
						),
					'handling' =>
						array(
							'currency_code' => $cart->currency,
							'value' => "0"
						),
					'insurance' =>
						array(
							'currency_code' => $cart->currency,
							'value' => "0"
						),
					'shipping_discount' =>
						array(
							'currency_code' => $cart->currency,
							'value' => "0"
						),
					
				),
			);
		
		if( $cart->shippingMethod && $cart->shippingAddress ){
			$data['shipping'] = 
					array(
						'method' => $cart->getNameShippingMethod(),
						'name' =>
							array(
								'full_name' => $cart->shippingName." ".$cart->shippingSurname,
							),
						'address' =>
							array(
								'address_line_1' => $cart->shippingAddress,
								'admin_area_2' => $cart->shippingCity,
								'admin_area_1' => ($cart->shippingCountry=='IT')?$cart->shippingProvince:'',
								'postal_code' => $cart->shippingPostalCode,
								'country_code' => $cart->shippingCountry,
							),
					);

			
		}
		//debugga($data);exit;
		
		return $data;
	}


	private function buildRequestBodyCreate($cart)
    {
		if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$protocol = 'https://';	
		}else{
			$protocol = 'http://';
		}
		
		$data_request = $this->getDataOrderRequest($cart);
		$data =  array(
			'intent' => 'CAPTURE',
			'application_context' =>
				array(
					'return_url' => $protocol.$_SERVER['SERVER_NAME']._MARION_BASE_URL_.'index.php?mod=paypal&ctrl=Paypalback',
					'cancel_url' => $protocol.$_SERVER['SERVER_NAME']._MARION_BASE_URL_.'index.php?mod=paypal&ctrl=Paypalcancel',
					'landing_page' => 'BILLING',
					'user_action' => 'PAY_NOW',
					'shipping_preference' => 'SET_PROVIDED_ADDRESS',
					'user_action' => 'PAY_NOW',
				),
			'purchase_units' =>
				array($data_request)
		);

		if( !$data_request['shipping'] ){
			unset($data['application_context']['shipping_preference']);
		}
		
		
		

		return $data;
    }


	/* REQUEST BODY UPDATE ORDER */
	
	private function buildRequestBodyUpdate($cart)
    {
		
		
		$data_request = $this->getDataOrderRequest($cart,1);
		
        return array (
            0 =>
                array (
                    'op' => 'replace',
                    'path' => '/intent',
                    'value' => 'CAPTURE',
                ),
            1 =>
                array (
                    'op' => 'replace',
                    'path' => "/purchase_units/@reference_id=='{$cart->id}'",
                    'value' =>$data_request
                       
                ),
        );
    }




	
	
	function getParameters(){
		
		$this->options = marion::getConfig('paypal_module');
		
		$this->mandadory_address_fields = unserialize($this->options['mandadory_address_fields']);
		$this->PayerID = _var('PayerID');
		$this->token = _var('token');
		
	}

	
	
	function display(){
		$this->getParameters();
		$action = $this->getAction();
		switch($action){
		
			
			case 'get_order':
				
				$data_order = $this->getOrder();
				$id_order = $data_order['id_order'];
				
			case 'saved_order':
				if( isset($id_order) && !$id_order ) $id_order = _var('id_order');
				
				$cart = Cart::withId($id_order);

		
				if( isset($data_order) ){
					$data_order = null;
				}
				//debugga($cart);exit;
				if( okArray($data_order) ){
					//if( $data_order['shippingCountry'] != $cart->shippingCountry ){
					
					$cart->set($data_order);

					$totale = $cart->total - $cart->discount + $cart->paymentPrice;
					$shippingPrice = $this->buildShippingPrice($cart,$totale);
					//debugga($shippingPrice);exit;
					if( $shippingPrice !== 0 && (is_nan($shippingPrice) || $shippingPrice == 'NAN') ){
						
						$this->error = _translate("no_shipping_methods",'paypal');
						unset($data_order['shippingProvince']);
						unset($data_order['shippingCountry']);
					}else{
						$cart->shippingPrice = $shippingPrice;
						$res = $this->updateOrder($cart);
						
						if( $res != 1){
							$this->error = $res;
						}
					}
					
					if( !$this->error ){
						if( okArray($this->mandadory_address_fields) ){
							foreach($this->mandadory_address_fields as $ind => $v){
								if( trim($data_order[$v]) ){
									
									unset($this->mandadory_address_fields[$ind]);			
								}
							}
							
							if( okArray($this->mandadory_address_fields) ){
								$this->buildErrorAddress();
							}
						}
					}
					
					
					//}
					if( $this->error ){
						$this->setVar('error',$this->error);
					}
					
					$cart->set($data_order)->save();

					if( okArray($this->mandadory_address_fields)){
						if( !_var('edit_address') ){
							header('Location: '.$_SERVER['REQUEST_URI']."&edit_address=1");
							exit;
						}
					}
					
					
				}else{
					$data_order = (array)$cart;
				}
				
				
				$orders = $cart->getOrders();
				if( $this->options['enable_registration'] ){
					if( !authUser() ){
						$this->setVar('registration',1);
					}
				}
				
				$this->setVar('cart_shipping_price',$cart->shippingPrice);
				$this->setVar('cart_total_products',$cart->total);
				$this->setVar('cart_payment_price',$cart->paymentPrice);
				$this->setvar('cart_total_tax',$cart->total_tax);
				$this->setvar('cart_total_products_without_tax',$cart->total_without_tax);

				//prendo alcune informazioni degli ordini dai prodotti
				foreach($orders as $k => $ord){
					$prodotto = $ord->getProduct();
					if(is_object($prodotto)){
						
						$orders[$k]->productname = $prodotto->getName();
						$orders[$k]->link = $prodotto->getUrl();
						$orders[$k]->img = $prodotto->getUrlImage(0,'thumbnail');
					}
					
				}
				
				$this->setVar('paypal_orders',$orders);
				$this->setVar('paypal_cart',$cart);
				
				$edit_address = _var('edit_address');
				$update_payment = _var('update_payment');
				if( $update_payment ){
					$this->setVar('update_payment',1);
				}
				$this->setVar('edit_address',$edit_address);
				if( $edit_address ){
					
				
					$dataform = $this->getDataForm('paypal_address',$data_order);
					$this->setVar('dataform',$dataform);
				}

				$this->setVar('id_order',$id_order);
				$this->setVar('PayerID',$this->PayerID);
				$this->setVar('token',$this->token);
				
				$this->output('confirm_address.htm');
				exit;
				break;
			case 'checkout':
				$this->rapid_checkout = true;
				$this->type_checkout = 'checkout';
				if( authUser()){
					$cart = Cart::getCurrent();
					$cart->paymentMethod = 'PAYPAL';
					$cart->shippingCellular = '';
					$cart->shippingCity = '';
					$cart->shippingProvince = '';
					$cart->shippingAddress = '';
					$cart->shippingCountry= '';
					$cart->shippingPostalCode = '';
					$cart->shippingPhone = '';
					$cart->id_address = '';
					
					
					$cart->virtual_cart = 1;
					$orders = $cart->getOrders();
					foreach($orders as $ord){

						$check = $ord->checkQuantity();
						if( $check != 1 ) return $check;
						$prod = $ord->getProduct();
						if( !$prod->virtual_product ){
							$cart->virtual_cart = 0;
							break;
						}
					}

					if( $cart->virtual_cart ){
						$shippingPrice = 0;
					}else{
						$shippingPrice = $this->buildShippingPrice($cart);
					}
					
					$cart->save();
					
					
					$cart->updateTotal();
					$this->create($cart);
				}else{
					
					$cart = Cart::create();
					$cart->createNumber();
					$cart->paymentMethod = 'PAYPAL';
					
					$cart->virtual_cart = 1;
					$orders = Cart::getCurrentOrders();

					
					foreach($orders as $ord){
						$check = $ord->checkQuantity();
						if( $check != 1 ) return $check;
						
						$prod = $ord->getProduct();
						if( !$prod->virtual_product ){
							$cart->virtual_cart = 0;
							break;
						}
					}
					$cart->save();
					
					
					foreach($orders as $ord){
						unset($ord->id);
						$ord->addToCart($cart->id);
					}
					
					
					if( $cart->virtual_cart ){
						$shippingPrice = 0;
					}else{
						$shippingPrice = $this->buildShippingPrice($cart);
					}
					$cart->updateTotal();
					$cart->save(); //altrimenti non funziona in coupon
					
					$this->create($cart);

					
				}
				break;
			
			case 'product':
				$id_product = _var('id');
				$qnt = (int)_var('qnt');
				
				$this->rapid_checkout = true;
				$this->type_checkout = 'product';
				$product = Product::withId($id_product);

			
			
				$cart = $this->createCart($product,$qnt);
				$this->create($cart);
				break;
			default:
				$id = _var('id');
				$cart = Cart::withId($id);
				$this->create($cart);
				break;
		}
		
        

		
	}


	function createCart($product,$qnt = 1){
		
	
		$cart = Cart::create();

		$user = Marion::getUser();
		$dataCart = array(
			'user' => is_object($user)?$user->getId():0,	
			'status' => 'paypal_checkout',
			//'vatCode' => Marion::getConfig('eshop','vat'),
			'creationDate' => date('Y-m-d H:i:s'),
			'virtual_cart' => $product->virtual_product?1:0
		);
		
		//setto la valuta
		$currency = getConfig('eshop','defaultCurrency');
		
		if( $GLOBALS['activecurrency'] ){
			$cart->currency = $GLOBALS['activecurrency'];
		}
		$cart->paymentMethod = 'PAYPAL';
		$cart->createNumber();
	    $cart->set($dataCart);
	    $res = $cart->save();
		
		$data_product = array();
		$data_product['product'] = $product->getId();
		$data_product['weight'] = $product->getWeigth();
		$data_product['quantity'] = $qnt;
		

		$order = Order::create();
		$order->set($data_product);
		

		
		$check = $order->addToCartWithId($res->id);
		$res->updateTotal();
		
		//debugga($check);exit;

		if( !$product->virtual_product ){
			$shippingPrice = $this->buildShippingPrice($res,$product->getPriceValue($qnt)*$qnt);
		}else{
			$shippingPrice = 0;
		}

		
		
		
		$res->updateTotal();
	
		
		
		
		

		
		
		
		return $res;

					
	}


	function buildShippingPrice(&$cart,$total_cart = false){
		$cart->shippingMethod = $this->options['courier'];

		//$list = ShippingMethod::prepareQuery()->where('active',1)->get();
		


		$shippingMethod = ShippingMethod::withId($this->options['courier']);
		if( is_object($shippingMethod) ){
			$country = $cart->shippingCountry?$cart->shippingCountry:'IT';
			if( !$total_cart ){
				$shippingPrice = $shippingMethod->getPrice($country,$cart->total_weight);
			}else{
				
				$shippingPrice = $shippingMethod->getPrice($country,$cart->total_weight,true,$total_cart);
			}
			
		}

		
		if( $shippingPrice != 'NAN' ){
			$cart->shippingPrice = $shippingPrice;
		}
		return $shippingPrice;
		
		
	}


	function getApproveLink($response){
		$url = '';
		
		foreach($response->result->links as $link){
			if( $link->rel == 'approve'){
				$url = $link->href;
			}	
		}
		return $url;
	}

	private function getClient(){
		
        
		
		if( $this->options['sandbox'] ){
			$clientId = $this->options['sandbox_client_id'];
			$clientSecret = $this->options['sandbox_client_secret'];
			$env = new SandboxEnvironment($clientId, $clientSecret);
		}else{
			$clientId = $this->options['production_client_id'];
			$clientSecret = $this->options['production_client_secret'];
			$env = new ProductionEnvironment($clientId, $clientSecret);
		}
		


        return new PayPalHttpClient($env);
    
	}


	function saveTransaction($response,$cart){
		
	   $database = Marion::getDB();
	   $transaction = array(
			'cartId' => $cart->getId(),
			'cartNumber' => $cart->number,
			'token' => $response->result->id,
		    'rapid_checkout' => $this->rapid_checkout,
		    'type_checkout' => $this->type_checkout
		);

	  
	   $id = $database->insert('transactionPayPal',$transaction);
	   return $id;
	}


	public static function prettyPrint($jsonData, $pre="")
    {
        $pretty = "";
        foreach ($jsonData as $key => $val)
        {
            $pretty .= $pre . ucfirst($key) .": ";
            if (strcmp(gettype($val), "array") == 0){
                $pretty .= "\n";
                $sno = 1;
                foreach ($val as $value)
                {
                    $pretty .= $pre . "\t" . $sno++ . ":\n";
                    $pretty .= self::prettyPrint($value, $pre . "\t\t");
                }
            }
            else {
                $pretty .= $val . "\n";
            }
        }
        return $pretty;
    }



	function create($cart){
		
		$this->getParameters();
		
	
		
		$request = new OrdersCreateRequest();
        $request->headers["prefer"] = "return=representation";
		$requestBody = $this->buildRequestBodyCreate($cart);
		

        $request->body = $requestBody;

		
		$client = $this->getClient();
		try{
			$response = $client->execute($request);
			//debugga($response);exit;
			$this->saveTransaction($response,$cart);
			$link = $this->getApproveLink($response);
			
			
			header('Location: '.$link);
			exit;
		}catch(HttpException $exception){
			$message = json_decode($exception->getMessage(), true);
            print "Status Code: {$exception->statusCode}\n";
            print(self::prettyPrint($message));
		}
	}



	function getOrder(){
		
		$client = $this->getClient();

		try{
        $response = $client->execute(new OrdersGetRequest($this->token));
		}catch(Exception $e ){
			debugga($e);exit;
		}
		
		
		return $this->getDataOrder($response);
		
	
		//$this->displayAddress($datauser);
	}

	
	function getDataOrder($response){
		
		$payer = $response->result->payer;
		$data = array(
			'shippingCellular' => '',
			'shippingPhone' => '',
			'name' => $payer->name->given_name,
			'surname' => $payer->name->surname,
			'shippingName' => $payer->name->given_name,
			'shippingSurname' => $payer->name->surname,
			'email' => $payer->email_address,
			'shippingEmail' => $payer->email_address,

		);
		
		$data_order = $response->result->purchase_units[0];
		$data['id_order'] = $data_order->reference_id;
		$address = $data_order->shipping->address;
		$data['shippingAddress'] = $address->address_line_1;
		$data['shippingCity'] = $address->admin_area_2;
		$data['shippingPostalCode'] = $address->postal_code;
		$data['shippingCountry'] = $address->country_code;
		
		if( $data['shippingCountry'] == 'IT' ){
			$data['shippingProvince'] = $address->admin_area_1;
			if( strlen(trim($data['shippingProvince'])) > 3 ){
				$database = Marion::getDB();
				$select = $database->select('*','provincia');
				foreach($select as $v){
					$nome_provincia[strtolower($v['nome'])] = $v['sigla'];
				}
				
				$data['shippingProvince'] = $nome_provincia[strtolower(trim($data['shippingProvince']))];
				
				
			}
		}
		
		

		return $data;

	}



	function updateOrder($cart){
		
		$request = new OrdersPatchRequest($this->token);
		
        $request->body = $this->buildRequestBodyUpdate($cart);
        
		$client = $this->getClient();
		
		try{
			$response = $client->execute($request);
			
			return true;
			//$response = $client->execute(new OrdersGetRequest($this->token));
			//debugga($response);exit;
		}catch(HttpException $exception ){
			
			//debugga($exception);exit;
			$message = json_decode($exception->getMessage(), true);
			return self::prettyPrint($message);
		}

        //$response = $client->execute(new OrdersGetRequest($orderId));
		//$request = 
	}


	function displayAddress($data){
		
		$this->output('confirm_address.htm');
		exit;
	}

	function array_province(){
		$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$database = Marion::getDB();
		$prov = $database->select('sigla','provincia','','sigla ASC');
		if( okArray($prov) ){			
			foreach($prov as $v){
				$toreturn[$v['sigla']] = $v['sigla'];
			}
		}
		
		return $toreturn;
	}



	function array_nazioni_spedizione(){
		//$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$shippingMethods = ShippingMethod::prepareQuery()->where('visibility',1)->where('id',$this->options['courier'])->get();
		
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



	function ajax(){
		$this->getParameters();
		$action = $this->getAction();
		$formdata = $this->getFormdata();
		switch($action){
			case 'get_price_shipping':
				$id_cart = _var('id_order');
				$cart = Cart::withId($id_cart);
				$cart->shippingCountry = _var('country');
				$shippingPrice = (float)$this->buildShippingPrice($cart);

				$risposta = array(
					'result' => 'ok',
					'price' => $shippingPrice
				);
				break;
			case 'confirm_order':
				$register = _var('register');
				$id_cart = _var('id_cart');
				$redirect_url = 'index.php?mod=paypal&ctrl=Paypalback&id_order='.$id_cart.'&token='._var('token')."&PayerID="._var('PayerID')."&checked=1";
				if( $register ){
					
					$cart = Cart::withId($id_cart);
					$data = (array)$cart;
					$array = $this->checkDataForm('paypal_register',$formdata);
					if( $array[0] == 'ok'){	
						$query = User::prepareQuery()->where('deleted',0)->whereExpression("(username='{$array['username']}' OR email='{$array['username']}' OR email = '{$data['email']}')");
						$check = $query->get();
						
						if( okArray($check) ){
							$array[0] = 'nak';
							$array[1] = 'USER EXISTS';
						}
					}


					if( $array[0] == 'ok'){	
						$id_cart = _var('id_cart');
						
						
					
						
						

						$array['password'] = password_hash($array['password'], PASSWORD_DEFAULT); 

						
						$user = User::create();
						$user->set($data);
						$user->set($array);
						$user->category = 1;
						$user->locale = $GLOBALS['activelocale'];
						$user->active = 1;
						$res = $user->save();
						
						MArion::setUser($res);
						$cart->user = $res->id;
						$cart->save();
						$risposta = array(
							'result' => 'ok',
							'url' => $redirect_url,
						);
					}else{
						$risposta = array(
							'result' => 'nak',
							'error' => $array[1],
							'field' => $array[2]
						);

					}
				}else{
					$risposta = array(
							'result' => 'ok',
							'url' => $redirect_url,
						);
				}
				break;
			case 'confirm_address':
				
				
				if( $formdata['shippingCountry'] != 'IT' ){
					
					$campi_modificati['shippingPostalCode']['tipo'] = '';
					$campi_modificati['shippingPostalCode']['checklunghezza'] = 0;
					//unset($formdata['province']);
					$campi_modificati['shippingProvince']['obbligatorio'] = 0;
				}

				if( okArray($this->mandadory_address_fields) ){
				
					foreach($this->mandadory_address_fields as $field){
						$campi_modificati[$field]['obbligatorio'] = 1;
					}
				}
				
				$array = $this->checkDataForm('paypal_address',$formdata,$campi_modificati);

				if( $array[0] == 'ok'){
					
					$id_cart = _var('id_cart');
					$cart = Cart::withId($id_cart);

					$country_old = $cart->shippingCountry;
					$shipping_price_old = $cart->shippingPrice;
					$array['email'] = $array['shippingEmail'];
					$cart->set($array);

					
					
					
					
					$totale = $cart->total - $cart->discount + $cart->paymentPrice;
					$shippingPrice = (float)$this->buildShippingPrice($cart,$totale);

					
					
					$redirect_url = 'index.php?mod=paypal&action=saved_order&id_order='.$cart->id.'&token='._var('token')."&PayerID="._var('PayerID');
					//if( $country_old != $array['shippingCountry'] && $shippingPrice != $shipping_price_old){
						//debugga($shippingPrice);exit;
					$cart->shippingPrice = (float)$shippingPrice;
					
					$check = $this->updateOrder($cart);
					if( $check != 1 ){
						$risposta = array(
							'result' => 'nak',
							'error' => $check
						);
						echo json_encode($risposta);
						exit;
					}
					$cart->save();
					$cart->updateTotal();
					$redirect_url .= "&update_payment=1";
					//}

					$risposta = array(
						'result' => 'ok',
						'url' =>  $redirect_url
						//'url' =>  'index.php?mod=paypal&ctrl=Paypalback&token='._var('token')."&PayerID="._var('PayerID')
						
					);
					
				}else{
					$risposta = array(
						'result' => 'nak',
						'error' => $array[1],
						'field' => $array[2]
					);

				}
				
				break;

		}
		echo json_encode($risposta);
		exit;
	}


	function buildErrorAddress(){
		$this->error = _translate('missing_data_address','paypal');
		
		foreach($this->mandadory_address_fields as $v){
			$this->error .= " <b>"._translate($v,'paypal')."</b>, ";
		}
		$this->error = trim($this->error,', ');
	}


	function setMedia(){
		parent::setMedia();
		$this->registerJS('modules/paypal/js/script.js?v=15');
		$this->registerJS('modules/cart_onepage/js/cart_general.js');
		$this->registerCSS('modules/cart_onepage/css/cart.css');
		$this->registerCSS('modules/cart_onepage/css/cart_new.css');
		$this->registerCSS('modules/paypal/css/style.css');

		if( _var('edit_address') ){
			
			$this->registerJS('modules/paypal/js/address.js');
		}
	}










}