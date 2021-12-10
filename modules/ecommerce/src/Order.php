<?php
namespace Shop;
use \Product;
use Marion\Core\Base;
use Marion\Core\Marion;
use Marion\Entities\User;
class Order extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'cartRow'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica

	
	// ennupla univoca per la tabella. Importante per determinare il codice dell'ordine nel caso in cui l'utente non è registrato l'acquisto 
	public static $_unique_tuple = array('product','user','cart','parent');
	
	

	//setta l'identificativo del prodotto
	function setProductId($id){
		$this->product = $id;
	}

	//setta l'identificativo dell'utente
	function setUserId($id){
		$this->user = $id;
	}

	//setta il quantitativo di prodotto da ordinare
	function setQuantity($quantity){
		$this->quantity = $quantity;
	}


	//setta il prezzo per unità del prodotto
	function setPrice($price){
		$this->price = $price;
	}
	
	
	//restituisce l'identificativo del prodotto nell'ordine
	function getProductId(){
		return $this->product;
	}
	
	//restituisce l'identificativo dell'utente dell'ordine
	function getUserId(){
		return $this->user;
	}


	//restituisce la quantità del prodotto nell'ordine
	function getQuantity(){
		return $this->quantity;
	}

	//restituisce il peso del prodotto per unità
	function getWeigth(){
		return $this->weight;
	}
	
	//restituisce il prezzo per unità del prodotto nell'ordine
	function getPrice(){
		//mofifica nuova
		$price = $this->price;
		if( isset($this->supplement) ){
			$price += $this->supplement;
		}
		if( isset($this->discount) ){
			$price -= $this->discount;
		}
		return $price;
		//return $this->price;
	}

	function getPriceWithoutTax(){
		//mofifica nuova
		return $this->price_without_tax+$this->supplement_without_tax-$this->discount_without_tax;
		//return $this->price;
	}

	function getTax(){
		//mofifica nuova
		return $this->taxPrice+$this->supplement_tax-$this->discount_tax;
		//return $this->price;
	}
	
	//restituisce il prezzo formattato per unità del prodotto nell'ordine
	function getPriceFormatted(){
		$value = $this->getPrice();
		return number_format($value, 2, ',', '');
	}


	
	
	

	//restitusce il prdootto ordinato sotto forma di oggetto
	function getProduct(){
		if( $this->product ){
			$product = Product::withId($this->product);
			if(is_object($product)){
				return $product;
			}
			return false;
		}
	}

	
	//override metodo
	function afterLoad(){
		parent::afterLoad();
		$children = $this->getChildren();
		if( okArray($children) ){
			$this->children = $children;
		}
	}

	function checkQuantity(){
		$product = $this->getProduct();
		$message_error_not_exists = _translate('error_product_not_exists','ecommerce');
		if( is_object($product)){
			$message_min_order = _translate('error_product_min_order','ecommerce');
			$message_max_order = _translate('error_product_max_order','ecommerce');
			$message_qnt_not_available = _translate('error_product_qnt_not_available','ecommerce');
			
			
			/*if( !$product->visibility ){
				return sprintf($GLOBALS['gettext']->strings['product_not_available'],$product->getName());
			}*/
			//controllo minimo quantità
			if( $this->quantity < $product->minOrder ){
				
				//return sprintf($GLOBALS['gettext']->strings['minOrder_product'],$product->getName(),$product->minOrder);
				return sprintf($message_min_order,$product->getName(),$product->minOrder);
			}
			//controllo quantità massima
			if( $product->maxOrder ){
				if( $this->quantity > $product->maxOrder ){
					
					//return sprintf($GLOBALS['gettext']->strings['maxOrder_product'],$product->getName(),$product->maxOrder);
					return sprintf($message_max_order,$product->getName(),$product->maxOrder);
				}
			}

			//controllo quantità disponibile
			$checkQuantityCatalog = Marion::getConfig('eshop','checkQuantityCatalog');
			
			if( $checkQuantityCatalog ){
				
				
				if( $product->centralized_stock && $product->parent){
					$parent = $product->getParent();
					$qnt_dispo = (int)($parent->getInventory());
					if( $qnt_dispo > 0 ){
						$check = (int)($qnt_dispo - $this->quantity);
						if( $check < 0 ){
							
							//return sprintf($GLOBALS['gettext']->strings['quantity_product_not_available'],$product->getName(),$qnt_dispo);
							return sprintf($message_qnt_not_available,$product->getName(),$qnt_dispo);
						}
					}else{
						return sprintf($GLOBALS['gettext']->strings['product_is_over'],$product->getName());
					}
					
				}else{
					$qnt_dispo = (int)($product->getInventory());
					if( $qnt_dispo > 0 ){
						$check = (int)($qnt_dispo - $this->quantity);
						if( $check < 0 ){
							
							//return sprintf($GLOBALS['gettext']->strings['quantity_product_not_available'],$product->getName(),$qnt_dispo);
							return sprintf($message_qnt_not_available,$product->getName(),$qnt_dispo);
						}
					}else{
						return sprintf($GLOBALS['gettext']->strings['product_is_over'],$product->getName());
					}
				}
				
			}
		}else{
			return $message_error_not_exists;
		}

		return 1;
	}
	
	//Calcola e restituisce il prezzo per unità di prodotto a partire dalla quantità ordinata e la categoria dell'utente nel caso l'utente sia passato in input o loggato
	function computePrice($id_user = NULL){
		// se la quantità non è settata la imposto a 1

		if( !$this->quantity ) $this->quantity=1;
		
		//controllo dell'esistenza dell'id del prdootto
		if( $this->product){
			
			//prendo il prodotto
			$product = Product::withId($this->product);
			if($product){
				if($id_user){
					$user = User::withId($id_user);
					if(!is_object($user)){ 
						static::writeLog("utente non esistente << computePrice >>");
						return false;
					}
				}else{
					$user = getUser();
				}
				//prendo la categoria dell'utente
				if(is_object($user) ){
					$userCategory = $user->category;
				}
				if(!$userCategory){
					$userCategory = 1;
				}
				
				//prendo il prezzo del prodotto per la quantità specificata e la categoria di appartenenza dell'utente
				$this->price = $product->getPriceValue($this->quantity,$userCategory);
				
				$this->taxPrice = 0;
				$this->price_without_tax = $this->price;
				
				//calcolo le tasse
				if( $product->taxCode ){

					$tax = Tax::withId($product->taxCode);
					if( is_object($tax) ){
						$this->taxPrice = Eshop::extractVatFromPrice($this->price,$tax->percentage);
						$this->price_without_tax = $this->price-$this->taxPrice;
						
					}
				}
			}else{
				static::writeLog("prodotto non esistente << computePrice >>");
				return false;
			}
			
			//Di seguito puoi inserire altro codice per aggiungere o togliere un costo aggiuntivo
			


		}else{
			static::writeLog("id prodotto non specidficato << computePrice >>");
			return false;
			
		}

		return $this;


	}
	
	
	//restituisce il prezzo coplessivo del prodotto ordinato ovvero il prezzo moltiplicato per la quantità ordinata
	function getTotalPrice(){
		return $this->getPrice() *$this->getQuantity();
	}

	//restituisce il prezzo coplessivo senza tasse del prodotto ordinato ovvero il prezzo moltiplicato per la quantità ordinata
	function getTotalPriceWithoutTax(){
		return $this->getPriceWithoutTax() *$this->getQuantity();
	}

	//restituisce il prezzo coplessivo formattaton del prodotto ordinato ovvero il prezzo moltiplicato per la quantità ordinata
	function getTotalPriceWithoutTaxFormatted(){
		return Eshop::formatMoney($this->getTotalPriceWithoutTax());
	}

	//restituisce il prezzo coplessivo formattaton del prodotto ordinato ovvero il prezzo moltiplicato per la quantità ordinata
	function getTotalPriceFormatted(){
		return Eshop::formatMoney($this->getTotalPrice());
	}

	//restituisce il valore dell'IVA per l'ordine
	function getVAT($codeVat = null){
		
		$price = $this->getTotalPrice();
		return Eshop::extractVatFromPrice($price,$codeVat);
	}

	//restituisce il valore dell'IVA per l'ordine formattato
	function getVATFormatted($codeVat = null){
		$price = $this->getVAT($codeVat);
		return Eshop::formatMoney($price);
	}
	

	//restituisce il totale dell'ordine senza IVA
	function getTotalWithoutVAT($codeVat = null){
		$price = $this->getTotalPrice();
		return Eshop::removeVatFromPrice($price,$codeVat);
	}

	//restituisce il totale dell'ordine senza IVA formattato
	function getTotalWithoutVATFormatted($codeVat = null){
		$price = $this->getTotalWithoutVAT($codeVat);
		return Eshop::formatMoney($price);
	}
	
	public static function addToCartNotLogged($data){
			if ( Marion::exists_action('add_cart_not_logged') )  return Marion::do_action('add_cart_not_logged',func_get_args());
			if(!$data['quantity']) $data['quantity'] = 1; 

			$orders = array();
			$cart = Cart::getCurrent();
			if( okarray($cart) ){
				$orders = $cart['orders'];
			}

			//debugga($orders);exit;

			
			$id = '';
			if( !$data['id'] ){
				foreach($data as $k => $v){
					if(in_array($k, self::$_unique_tuple)){
						$id .= "{$v}-"; 
					}
				}
				$id = preg_replace('/-$/','',$id);
			}else{
				$id = $data['id'];
				unset($data['id']);
			}
			
			if(array_key_exists($id,$orders)){
				$data['quantity'] = $orders[$id]['quantity']+$data['quantity'];
				
			}

			$order = Order::create();
			$order->set($data);
			
			$check_qnt = $order->checkQuantity();
			if( $check_qnt != 1 ) return $check_qnt;


			$order->computePrice();
			$orders[$id] = (array)$order;
			Cart::setCurrentOrders($orders);

			return true;
			
	}
	

	function addToCart($id_cart=NULL){

		 if ( Marion::exists_action('add_cart') ) return Marion::do_action('add_cart',array($this));

		 if( !$id_cart ){
			$cart = Cart::getCurrent();
			$id_cart = $cart->id;
		 }else{
			//prendo il carrello a partire dal suo id
			$cart = Cart::withId($id_cart);
		 }
		 if( !is_object($cart)  ){
			static::writeLog("carrello non esistente << addTocartAdmin >>");
			return false;
		 }
		 $this->cart = $id_cart;


		 //prendo l'id dell'utente se presente nel carrello
		 $id_user = $cart->getUserId();
		
		 if( $id_user ){
			
			$user = User::withId($id_user);
			
			if(!is_object($user)){ 
				static::writeLog("utente non esistente << addTocartAdmin >>");
				return false;
			}
			$this->user = $user->getId();
		}
		$query = Order::prepareQuery();
			
		//condizione che indica l'univocità del prodotto nel carrello
		foreach( self::$_unique_tuple as $v){
			if( $v == 'parent' && !$this->$v) continue; 
			if( !$this->$v ) continue;
			$query->where($v,$this->$v);
		}

		// se la quantità non è settata la imposto a 1
		if( !$this->quantity ) $this->quantity = 1;
		
		//prendo il vecchio ordine nel carrello se esiste
		$old = $query->getOne();
		
		if( is_object($old) ){
			$this->id = $old->id;
			$this->quantity = $old->quantity + $this->quantity;
		}


		$check_qnt = $this->checkQuantity();

		
		if( $check_qnt != 1 ) return $check_qnt;
	

		$this->computePrice();
		
		$this->save();
		
		$cart->updateTotal();

		return $this->id;
	}
	

	//aggiunge l'ordine ad un carrello già esistente specificando l'identificativo del carrello
	function addToCartWithId($id_cart=NULL){
		return $this->addToCart($id_cart);

	}



	function getChildren($where = NULL){
		if( $this->id){
			
			$query = self::prepareQuery();
			$orders = $query->whereExpression("parent = {$this->id}")
				->where('cart',$this->cart)->get();
			
			return $orders;
		}

		return false;
	}


	function beforeSave(){
		if( !$this->taxPrice ){
			$this->taxPrice = 0;
		}
	}



	function delete(){
		parent::delete();
		if( $this->children ){
			foreach($this->children as $v){
				$v->delete();
			}
		}
		
		
	}



	






	/**************************************************** OVERRIDE METODI DELLA CLASSE Base **********************************************/

	
	

}