<?php
namespace Shop;
use \Product;
use \Wkhtmltopdf;
use Marion\Core\Base;
use Marion\Core\Marion;
use Marion\Entities\User;
use Marion\Components\WidgetComponent;
class Cart extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'cart'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica


	const PATH_INVOICIES = 'invoices/'; //path in cui verranno memorizzate le fatture
	
	

	//restituisce il totale del carrello ovvero la somma complessiva dei prezzi degli articoli
	function getTotal(){
		return $this->total;
	}


	//restituisce il totale formattato del carrello ovvero la somma complessiva dei prezzi degli articoli
	function getTotalFormatted(){
		return Eshop::formatMoney($this->getTotal());
	}

	//restituisce il totale del carrello ovvero la somma complessiva dei prezzi degli articoli senza IVA
	function getTotalWithoutVAT(){
		return Eshop::removeVatFromPrice($this->total,$this->vatCode);
	}

	///restituisce il totale del carrello formattato ovvero la somma complessiva dei prezzi degli articoli senza IVA
	function getTotalWithoutVATFormatted(){
		return Eshop::formatMoney($this->getTotalWithoutVAT(),$this->vatCode);
	}

	//restituisce l'IVA del totale del carrello
	function getVAT(){
		return Eshop::extractVatFromPrice($this->total,$this->vatCode);
	}

	//restituisce l'IVA formattata del totale del carrello
	function getVATFormatted(){
		return Eshop::formatMoney($this->getVAT());
	}


	//crea il numero dell'ordine
	function createNumber(){
		$this->number = substr(time(), 0, 10);
	}
	//aggiorna il totale degli articoli nel carrello
	function updateTotal(){
		$database = Marion::getDB();
		
		$tot = $database->select('sum(quantity*(price+supplement-discount)) as total,sum(quantity*(price_without_tax+supplement_without_tax-discount_without_tax)) as total_without_tax,sum(quantity*(taxPrice+supplement_tax-discount_tax)) as total_tax,sum(quantity*weight) as total_weight,sum(quantity) as num_products','cartRow',"cart={$this->id} AND (parent is NULL OR parent=0)");

		
		if( okArray($tot) ){
			$update = $tot[0];
			if( $GLOBALS['activecurrency'] ){
				$update['currency'] = $GLOBALS['activecurrency'];
			}
			$update['dateLastUpdate'] = date('Y-m-d H:i:s');
			$database->update('cart',"id={$this->id}",$update);

			foreach($update as $k => $v){
				$this->$k = $v;
			}
			
		}
		
		
	}

	//aggiorna il carrello dopo delle modifiche agli ordini
	function update(){
		
		//nel caso in cui l'utente è loggato
		if($this->hasId()){
			$orders = Order::prepareQuery()->where('cart',$this->getId())->whereExpression("(parent is NULL OR parent=0)")->get();
			
		
			if(okArray($orders)){
				foreach($orders as $order){
					//ricalcolo il prezzo dell'ordine nel caso la quantità fosse cambiata
					$order->computePrice();
					
					$order->save();
				}
			}
			//setto la valuta
			//$currency = getConfig('eshop','defaultCurrency');
			
			/*if( $GLOBALS['activecurrency'] ){
				$this->currency = $GLOBALS['activecurrency'];
			}
			$this->dateLastUpdate = date('Y-m-d H:i:s');
				
			$this->save();*/

			$this->updateTotal();

		}
		return $this;
	}

	
	//restituisce l'id dell'utente che fa effettuato il carrello
	function getUserId(){
		return $this->user;
	}

	function getUser(){
		if($this->user){
			return User::withId($this->user);
		}
	}

	

	//restituisce il nome del metodo di spedizione
	function getNameShippingMethod($locale=NULL){
		if($this->shippingMethod){
			$shippingMethod = ShippingMethod::withId($this->shippingMethod);
			if(is_object($shippingMethod)){
				return $shippingMethod->get('name',$locale);
			}
		}
		return false;
	}
	
	//restituisce il nome del metodo di pagamento
	function getNamePaymentMethod($locale=NULL){
		if($this->paymentMethod){
			$paymentMethod = PaymentMethod::withCode($this->paymentMethod);
			if(is_object($paymentMethod)){
				return $paymentMethod->get('name',$locale);
			}
		}
		return false;
	}

	//prende il numero totale di articoli nel carrello
	function getNumberProduct(){
		if($this->hasId()){
			$orders = Order::prepareQuery()->where('cart',$this->getId())->whereExpression("(parent is NULL OR parent=0)")->get();
			$number = 0;
			if(okArray($orders)){
				foreach($orders as $order){
					$number += $order->getQuantity();
				}
			}
			return $number;

		}
		return 0;
	}

	
	//restituisce il peso complessivo del carrello
	function getWeight(){
		$weight = 0;
		if($this->hasId()){
			$orders = $this->getOrders();
			if(okArray($orders)){
				foreach($orders as $ord){
					$weight += $ord->getWeigth()*$ord->getQuantity();
				}
			}
		}
		return $weight;
	}
	

	//metodo che memorizza i dati di spedizione a partire dall'id dell'indirizzo
	function getDataShippingFromAdressId(){
		if( $this->id_address ){
			$address = Address::withId($this->id_address);
			if( is_object($address) ){
				$data_shipping = array(
					'shippingName' => $address->name,
					'shippingSurname' => $address->surname,
					'shippingAddress' => $address->address,
					'shippingEmail' => $address->email,
					'shippingPhone' => $address->phone,
					'shippingCellular' => $address->cellular,
					'shippingPostalCode' => $address->postalCode,
					'shippingCity' => $address->city,
					'shippingCountry' => $address->country,
					'shippingProvince' => $address->province,
				);
				
				foreach($data_shipping as $k => $v){
					$this->$k = $v;
				}
			}
		}
	}


	//restituisce gli ordini del carrello sotto forma di oggetti
	public function getOrders(){
		if($this->hasId()){
			$query =  Order::prepareQuery()->whereExpression('(parent = 0 OR parent IS NULL)')->where('cart',$this->id);
			$orders = $query->get();
			
			return $orders;
		}
		return false;

	}

	//metodo che restituisce il totale del carrello comprensivo del costo di spedizione, costo di pagamento e degli sconti
	function getTotalFinal(){
		//calcolo il peso
		//$weight = $this->getWeight();
		
		//calcolo il totale del carrello
		$subtotal = $this->getTotal();
		
		
		//calcolo i costi di spedizione e pagamento
		$shippingPrice = $this->getCostShipping();

		$paymentPrice = $this->getCostPayment();
		$discount = $this->getDiscount();

		//calcolo il totale
		$total = $subtotal + $shippingPrice + $paymentPrice - $discount;
	
		return $total;
	}

	//restituisce il metodo di pagamento sotto forma di oggetto
	function getPaymentMethod(){
		if( is_object($this->paymentMethod_object) ){
			if( $this->paymentMethod_object->code ==  $this->paymentMethod ){
				return $this->paymentMethod_object;
			}

		}
	
		if( $this->paymentMethod){
			if( (int)$this->paymentMethod){
				$paymentMethod = PaymentMethod::withId($this->paymentMethod);
			}else{
				$paymentMethod = PaymentMethod::withCode($this->paymentMethod);
			}
			if( is_object($paymentMethod) ){
				$this->paymentMethod_object = $paymentMethod;
			}
			return $paymentMethod;
		}
		return false;
		
	}

	//restituisce il costo del pagamento. Se il carrello è attivo lo ricalcola
	function getCostPayment(){
		$paymentPrice = 0;
		if( $this->status == 'active'){
			if( $this->paymentMethod){
				//prendo il metodo di pagamento selezionato
				
				$paymentMethod = $this->getPaymentMethod();
				if( is_object($paymentMethod)){
					if( $paymentMethod->percentage ){
						$paymentPrice = ($this->getTotal()+$this->getCostShipping())*$paymentMethod->price/100;
					}else{
						$paymentPrice = $paymentMethod->price;
					}
					
				}
				
			}
		}else{
			$paymentPrice = $this->paymentPrice;
		}

		$paymentPrice = number_format($paymentPrice,2,'.','.');
		return Eshop::priceValue($paymentPrice);
	}

	//restituisce il costo di spedizione. Se il carrello è attivo lo ricalcola
	function getCostShipping(){
		$shippingPrice = 0;
		if( $this->status == 'active'){
			if( $this->shippingMethod){
				//prendo il metodo di spedizione selezionato
				$shippingMethod = ShippingMethod::withId($this->shippingMethod);
				if(is_object($shippingMethod)){
					if( $this->shippingCountry){
						$weight = $this->getWeight();
						//calcolo la tariffa di spedizione
						$shippingPrice = $shippingMethod->getPrice($this->shippingCountry,$weight);
					}
				}
			}
		}else{
			$shippingPrice = $this->shippingPrice;
		}

		

		return $shippingPrice;

	}

	//metodo che restituisce lo sconto nel carrello
	function getDiscount(){
		//se il carrello non è attivo allora prendo il suo sconto altrimenti lo sconto è 0. Lo sconto infatti viene salvato alla chiusura del carrello
		if( $this->status != 'active'){
		
			if( $this->discount ){
				return $this->discount;
			}else{
				return 0;
			}
		}else{
			return 0;
		}
	}

	function getTotalFinalFormatted(){
		$total = $this->getTotalFinal();
		return Eshop::formatMoney($total);
	}

	//metodo che chiude un carrello:
	// calcola i costi di spedizione, di pagamento, e il totale del carrello e alla fine salva il carrello
	function close($computeTotalOrder=false){
		//ciro
		
		if( !$this->virtual_cart ){
			//controllo nazione spedizione
			if( !$this->shippingCountry){
				return 'missing_shipping_country';
			}

			//controllo metodo spedizione
			if( !$this->shippingMethod){
				return 'missing_shipping_method';
			}
		}
		
		//controllo metodo pagamento
		if( !$this->paymentMethod){
			return 'missing_payment_method';
		}


		//controllo quantita merce disponibile
		$checkQuantityEshop = getConfig('eshop','checkQuantityEshop');
		

		if( $computeTotalOrder || $checkQuantityEshop){
			//prendo gli ordini del carrello per effettuare opportuni controlli
			$orders = $this->getOrders();

		}
		

		if( $computeTotalOrder ){
			//ricalcolo il totale
			$this->total = 0;
		}
		
		if( okArray($orders) ){
			foreach($orders as $order){
				if($checkQuantityEshop){
					
					$check_qnt = $order->checkQuantity();
					if( $check_qnt != 1 ) return $check_qnt;
					
				}
				if( $computeTotalOrder ){
					$order->computePrice();
					$this->total += $order->getTotalPrice();
					$order->save();
				}

			}
		}

		$shippingPrice = 0;

		//determino i costi di spedizione
		if( !$this->virtual_cart ){
			$weight = $this->getWeight();

			$shippingMethod = ShippingMethod::withId($this->shippingMethod);
			
			$shippingPrice = $shippingMethod->getPrice($this->shippingCountry,$weight);
			if( $shippingMethod->taxCode){
				$tax = Tax::withId($shippingMethod->taxCode);
				$shippingPriceWithoutTax = Eshop::removeVatFromPrice($shippingPrice,$tax->percentage);
			}else{
				$shippingPriceWithoutTax = $shippingPrice;
			}

			

		}

		
		$currency = Marion::getConfig('eshop','defaultCurrency');
				
		if( $GLOBALS['activecurrency'] ){
			$this->currency = $GLOBALS['activecurrency'];
		}

		
		$paymentPrice = $this->getCostPayment();
		$paymentPriceWithoutTax = $paymentPrice;
		$paymentPricetax = $paymentPriceWithoutTax-$paymentPrice;
		$this->set(
			array(
				'paymentPrice' => $paymentPrice,
				'paymentPriceWithoutTax' => $paymentPriceWithoutTax,
				'paymentPriceWithoutTax' => $paymentPrice,
				'paymentPricetax' => $paymentPricetax,
				'shippingPrice' => $shippingPrice,
				'shippingPriceWithoutTax' => $shippingPriceWithoutTax,
				'shippingPriceTax' => $shippingPrice-$shippingPriceWithoutTax,
				'evacuationDate' => date('Y-m-d H:i:s'),
				'locale' => $GLOBALS['activelocale'],
				'sessionId' => session_id(),
			)
		);


		

		

		$this->save();
		
		
		

		$close_cart_flag = Marion::getConfig('eshop','closeCart'); //verifico se è abilitata la chiusura del carrello
		$close_cart = false;
		//prendo il metodo di pagamento
		$paymentMethod = $this->getPaymentMethod();
		if( $close_cart_flag ){
			if( is_object($paymentMethod) ){

				//se il pagamento è online allora imposto la chiusura del carrello
				if( $paymentMethod->online ){
					$this->changeStatus('waiting');
					
					$automatic_stock = getConfig('eshop','automaticStock');
					$automatic_stock_type = getConfig('eshop','automaticStockType');
					if($automatic_stock && $automatic_stock_type == 'onClose'){
						$this->decreaseInventory();
					}
					unset($_SESSION['sessionCart']['orders']);
				}
			}
		}
		
		unset($_SESSION['sessionCart']['data']['paymentMethod']);
		unset($_SESSION['sessionCart']['data']['id']);
		


		Marion::do_action('cart_close',array(&$this));
		return $this;
	}

	//metodo che scala merce ordinata dal magazzino
	/*function decreaseInventory(){
		$orders = $this->getOrders();
		foreach($orders as $order){
			$product = $order->getProduct();
			
			if(is_object($product)){
				if( $product->centralized_stock && $product->parent){
					$product = $product->getParent();
				}
				$new_quantity = (int)($product->stock - $order->quantity);
				if( $new_quantity < 0) $new_quantity = 0;
				
				$product->updateStock($new_quantity);

				$alertRunOut = getConfig('eshop','alertRunOut');
				if( $alertRunOut && $new_quantity == 0){
					$this->notifyProductOutOfStock($product);
				}

			}	
		}

	}
	//metodo che ripristina merce ordinata dal magazzino
	function increaseInventory(){
		$orders = $this->getOrders();
		foreach($orders as $order){
			$product = $order->getProduct();
			if(is_object($product)){
				if( $product->centralized_stock && $product->parent){
					$product = $product->getParent();
				}
				$new_quantity = (int)($product->stock + $order->quantity);
				$product->updateStock($new_quantity);
				
			}	
		}

	}*/

	function decreaseInventory(){
		$orders = $this->getOrders();
		foreach($orders as $order){
			$product = $order->getProduct();
			
			
			if(is_object($product)){
				if( $product->centralized_stock && $product->parent){
					$product = $product->getParent();
				}
				$qnt = $product->getInventory();
				$new_quantity = (int)($qnt - $order->quantity);
				if( $new_quantity < 0) $new_quantity = 0;
				
				$product->updateInventory($new_quantity);

				$alertRunOut = getConfig('eshop','alertRunOut');
				if( $alertRunOut && $new_quantity == 0){
					$this->notifyProductOutOfStock($product);
				}

			}	
		}

	}
	//metodo che ripristina merce ordinata dal magazzino
	function increaseInventory(){
		$orders = $this->getOrders();
		foreach($orders as $order){
			$product = $order->getProduct();
			if(is_object($product)){
				if( $product->centralized_stock && $product->parent){
					$product = $product->getParent();
				}
				$qnt = $product->getInventory();
				$new_quantity = (int)($qnt + $order->quantity);
				$product->updateStock($new_quantity);
				
			}	
		}

	}
	
	//metodo che stabilisce se un carrello è virtuale. Un carrello è virtuale se tutti i prodotti che ne fanno parte lo sono
	function isVirtual(){
		$check = true;
		$orders = $this->getOrders();
		foreach($orders as $order){
			$product = $order->getProduct();
			//debugga($product);exit;
			if(is_object($product)){
				if( !$product->isVirtual() ){
					$check = false;
					break;
				}
			}	
		}
		return $check;
	}


	//DA FARE
	public function notifyProductOutOfStock($product){
		if( is_object($product) ){
			//invio la notifica all'amministratore degli ordini
			$email = getConfig('eshop','mail');
			/*
			$infosito = getConfig('generale');
				
			$subject = sprintf($GLOBALS['gettext']->strings['subject_cambio_stato_carrello'],$infosito['nomesito']);
			
			$mail = _obj('Mail');
			$mail->cart = $this;
			$mail->note = $note;
			$mail->setTemplateHtml('mail_cambio_stato_ordine.htm');
			$mail->setSubject($subject);
			$mail->setTo( $this->email );
			$mail->setFrom($infosito['mail']);
			
			$res = $mail->send();*/
		}
	}
	

	//restituisce l'url da cui monitorare l'ordine
	function getUrlStatus(){
		if( $this->user ){
			return $_SERVER['SERVER_NAME']."/account.php?action=order_view&id=".$this->id;
		}else{
			return $_SERVER['SERVER_NAME']."/account.php?action=order_view&id=".$this->id."&pass=".$this->password_not_logged;
		}
	}

	

	//metodo che cambia lo stato dell'ordine
	// Riceve in input tre parametri:
	/*
		$status : lo stato dell'ordine (waiting,confirmed,canceled,etc)
		$send : valore che stabilisce se inviare una mail di notifica al cliente a seconda se vale 1 o 0
		$note : note aggiuntive per il cambio di stato dell'ordine

	*/
	function changeStatus($status,$note=NULL){
		if( $this->hasId() ){
			$database = Marion::getDB();
			$old_status = $this->status;
			

			$id_new_status = 0;
			$send_email = 0;
			$send_invoice = 0;
			$create_invoice = 0;
			$paid = 0;
			$sent = 0;

			$is_paid = 0;
			
			$cart_status = CartStatus::withLabel($status);
			
			//debugga($cart_status);exit;
			if( is_object($cart_status) ){
				$id_new_status = $cart_status->id;
				$create_invoice = $cart_status->invoice;
				$send_invoice = $cart_status->send_invoice;
				$send_email = $cart_status->send_mail;
				$status_name = $cart_status->get('name',$this->locale);
				$paid = $cart_status->paid;
				$sent = $cart_status->sent;
				$info_mail_status = $database->select('*','cart_status_mail',"id_status={$id_new_status} AND locale='{$this->locale}'");
				if( okArray($info_mail_status) ){
					$info_mail_status = $info_mail_status[0];		
			
				}

				if( !$this->paymentDate ){
					if($paid ){
						$is_paid = 1;
						$this->paymentDate =  date('Y-m-d H:i:s');
					}
				}

				if( !$this->shippingDate ){
					if($sent ){
						$this->shippingDate =  date('Y-m-d H:i:s');
					}
				}
			}
			$this->status = $status;
			
		
			


			$this->save();
			

			//se lo stato del carrello passa a confermato e se il cliente ha richiesto la fattura allora viene gtenerata in automatico
			$automaticInvoice = getConfig('eshop','automaticInvoice');
			if($automaticInvoice){
				if( is_object($cart_status) ){
					if($create_invoice ){
						if( !$this->hasInvoice){
							$this->createInvoice();

						}

					}
				}
			}

			$automatic_stock = getConfig('eshop','automaticStock');
			$automatic_stock_type = getConfig('eshop','automaticStockType');
			if($automatic_stock && $automatic_stock_type == 'onConfirmed' && $is_paid){
				$this->decreaseInventory();
			}


			/*if( $automatic_stock && $status != $old_status ){
				if( $status == 'canceled' || $status == 'deleted'){
					$this->increaseInventory();
				}
			}*/
		

		
			
			$toinsert['cartId'] = $this->id;
			$toinsert['status'] = $status;
			$toinsert['send'] = $send_email;
			$toinsert['note'] = $note;
			$toinsert['date'] = date('Y-m-d H:i:s');
			$database->insert('cartChangeStatus',$toinsert);

			if( $send_email ){
				
				$infosito = getConfig('generale');
				$subject = sprintf($GLOBALS['gettext']->strings['subject_cambio_stato_carrello'],$infosito['nomesito']);
				
				
				if( okArray($info_mail_status) ){

					$shippingMethod = ShippingMethod::withId($this->shippingMethod);
					$url_tracking  ='';
					if(is_object($shippingMethod)){
						$url_tracking = $shippingMethod->tracking_url;
					}

					$subject = $info_mail_status['subject'];
					$message = $info_mail_status['message'];
					$params = array(
						'/%CUSTOMER_FULLNAME_OR_COMPANY%/',
						'/%ORDER_NUMBER%/',
						'/%ORDER_DATE%/',
						'/%ORDER_PAID_DATE%/',
						'/%ORDER_SHIPPED_DATE%/',
						'/%ORDER_STATUS%/',
						'/%NOTE_STATUS%/',
						'/%ORDER_TRACKING%/',
						'/%LINK_COURIER%/'
					);

					$values = array(
						$this->company?$this->company:$this->name." ".$this->surname,
						$this->number,
						strftime('%d/%m/%Y',strtotime($this->evacuationDate)),
						strftime('%d/%m/%Y',strtotime($this->paymentDate)),
						strftime('%d/%m/%Y',strtotime($this->shippingDate)),
						$status_name,
						$note,
						$this->trackingCode,
						$url_tracking
					);

					$subject = preg_replace($params, $values, $subject);
					$message = nl2br(preg_replace($params, $values, $message));
					

					

					
				}
				$this->sendMailChangeStatus($infosito['mail'],$subject,$message,$send_invoice);

				
				
				
				
			}

			return true;
		}else{
			return false;
		}
	}

	//nmetodo che invia l'email di cambio stato ordine
	function sendMailChangeStatus($from,$subject,$message,$send_invoice){
		
		$widget = new WidgetComponent('ecommerce');
		$widget->addTwingTemplatesDir(_MARION_MODULE_DIR_."ecommerce/templates_twig");
		$widget->addTwingTemplatesDir(_MARION_THEME_DIR_._MARION_THEME_."/templates_twig");
		$widget->setVar('custom_message',$message);
		$widget->setVar('cart',$this);
		
		ob_start();
		$widget->output('ecommerce_mails/mail_change_status_order.htm');
		$html = ob_get_contents();
		ob_end_clean();

		

		$mail = _obj('Mail');
		
		$mail->setHtml($html);

		
		$mail->setSubject($subject);
		$mail->setTo( $this->email );
		$mail->setFrom($from);
		

		if( $send_invoice ){
			$db = Marion::getDB();
			$path_invoice = $db->select('*','invoice',"cartId={$this->id}");
			if( okArray($path_invoice) ){
				$files = array(_MARION_ROOT_DIR_.$path_invoice[0]['path']);
				$mail->addFiles($files);

			}

		}
		$res = $mail->send();
		return $res;

	}



	//metodo che invia la mail con il dettaglio dell'ordine
	function sendMailShop($mail_shop = 'mail_shop.htm',$send_admin_eshop=false,$show_tax=false){
		
		$ordini = $this->getOrders();
		foreach($ordini as $k => $ord){
			$prodotto = $ord->getProduct();
			if(is_object($prodotto)){
				$ordini[$k]->productname = $prodotto->getName();
				$ordini[$k]->sku = $prodotto->sku;
				$ordini[$k]->link = $prodotto->getUrl();
				$ordini[$k]->img = $prodotto->getUrlImage(0,'thumbnail');
			}
		}

	
		
		$infosito = getConfig('generale');
		$email_admin_eshop = getConfig('eshop','mail');

		if( !$email_admin_eshop ){
			$email_admin_eshop = $infosito['mail'];
		}
		
		
		//imposto i destinatari della mail
		$to = array(
			$this->email	
		);

		if( $send_admin_eshop ){
			$to[] = $email_admin_eshop;
		}

		//creo il subject della mail	
		$subject = sprintf($GLOBALS['gettext']->strings['subject_mail_shop'],$this->number,$infosito['nomesito']);
		
		$mail = _obj('Mail');
		$mail->cart = $this;
		$mail->show_tax = $show_tax;
		$mail->ordini = $ordini;
		$mail->totalFinal = $this->total + $this->shippingPrice + $this->paymentPrice - $this->discount; 
		$mail->setTemplateHtml($mail_shop);
		$mail->setSubject($subject);
		$mail->setToFromArray( $to );
		$mail->setFrom($email_admin_eshop);
		
		$res = $mail->send();
		if( $res ){
			$this->mail_sent = 1;
			$this->save();
		}

	}

	


	//metodo che restituisce la storia del carrello
	
	function getHistory(){
		if( $this->hasId() ){
			$database = Marion::getDB();
			$status = $database->select('*','cartChangeStatus',"cartId={$this->id}");
			return $status;

		}else{
			return false;
		}
	}


	function getFrequencyPaymentPaypal(){
		$frequency = $this->recurrent_payment_frequency;
		$period = preg_replace('/([0-9]+)/','',$frequency);
		$frequency = preg_replace('/([a-zA-Z]+)/','',$frequency);
		if( !$frequency ) $frequency = 1;
		
		if( $frequency > 1 ){
			if( $period == 'Month'){
				$period_name = __('description_payment_recurrent_paypal_months',NULL,array($period));
			}elseif( $period == 'Year'){
				$period_name = __('description_payment_recurrent_paypal_years',NULL,array($period));
			}elseif( $period == 'Day'){
				$period_name = __('description_payment_recurrent_paypal_days',NULL,array($period));
			}
		}else{
			if( $period == 'Month'){
				$period_name = __('description_payment_recurrent_paypal_month',NULL,array($period));
			}elseif( $period == 'Year'){
				$period_name = __('description_payment_recurrent_paypal_year',NULL,array($period));
			}elseif( $period == 'Day'){
				$period_name = __('description_payment_recurrent_paypal_day',NULL,array($period));
			}


		}
		
		$description = __('description_payment_recurrent_paypal',NULL,array($frequency,$period_name));
		
		
		return array(
			'period' => $period,
			'frequency' => $frequency,
			'description' => $description
		);
	}

	function checkStatusPaypal(){
		if( $this->paymentMethod == 'PAYPAL'){
			if( $this->recurrentPayment ){
				$this->getStatusRecurrentPaymentPaypal();
			}
		}
	}

	function getStatusRecurrentPaymentPaypal(){
		if( $this->recurrentPayment && $this->paymentMethod == 'PAYPAL'){
			$profileID = $this->paypalProfileID;
			$paypal = _obj('Paypal');
			$paypal->profile_id = $profileID;
			$paypal->get_recurring_payments_profile_details();

			
			$status = $paypal->Response['STATUS'];
			
			$info = array(
				'next_payment' => preg_replace('/Z/','',preg_replace('/T/',' ',$paypal->Response['NEXTBILLINGDATE'])),
				'status' => $status
			);
			

			if( $info['status'] == 'Cancelled'){
				if( $this->status != 'cancelled_subscription'){
					$this->changeStatus('cancelled_subscription');
				}
			}elseif( $info['status'] == 'Active'){
				if( $this->status != 'active_subscription'){
					$this->changeStatus('active_subscription');
				}
			}


			
			$database = Marion::getDB();
			$database->update('profilePaypal',"id_cart={$this->id}",$info);
		}
		return $info;
	}


	
	

	// PAGAMENTO CON PAYPAL
	function payWithStripe($number,$month,$year,$cvc){

		//calcolo l'importo da pagare
		$amountFinal = round($this->getTotalFinal(),2);

		//creo la descrizione dell'ordine
		$amountFormatted = number_format($amountFinal, 2, ',', '');
		$description = sprintf($GLOBALS['gettext']->strings['description_order_paypal'],$amountFormatted,$this->number,$GLOBALS['setting']['default']['GENERALE']['nomesito']);
		

		//istanzio l'oggetto PayPal
		$stripe = _obj('Stripe');
		$stripe->setCard($number,$month,$year,$cvc);
		$stripe->setDescription($description);
		$stripe->setAmount($amountFinal);
		
		$stripe->pay();
		if( $stripe->error){
			//inserire l'errore

			$this->changeStatus('active');
			
			if( __('stripe_'.$stripe->error['error']['code']) ){
				return __('stripe_'.$stripe->error['error']['code']);
			}else{
				return $stripe->error['error']['message'];
			}
		}else{
			$this->changeStatus('confirmed');
			return 1;
		}
			
	}


	
	//funzione che crea e salva la fattura del carrello
	function createInvoice($recreate=false){
		if( $this->hasId() ){
			$_values = array();
			//prendo i dati dell'azienda
			$azienda = Marion::getConfig('azienda');
			$_values['azienda'] = $azienda;
			
			$logo = "http://" . $_SERVER['SERVER_NAME']._MARION_BASE_URL_."img/".Marion::getConfig('eshop','logoInvoice')."/or/logo.png";
			
			$_values['logo'] = $logo;
			
			$database = Marion::getDB();
			//prendo l'oggetto template
			
			$twig = Marion::getTwig('ecommerce');
			
			$year = date('Y');
			if( $recreate ){
				$number = $database->select('number','invoice',"cartId={$this->id}");
				if( !okArray($number) ) return;
				$number = $number[0]['number'];

			}else{

				//prendo il numero corrente della fattura
				
				$number = $database->select('max(number) as max','invoice',"year='{$year}'");
				$number = $number[0]['max']+1;

			}
		

			
			
			
			//determino il path del file 
			$year = Date('Y');

			$dir = _MARION_MODULE_DIR_."ecommerce/".STATIC::PATH_INVOICIES.$year;
			
			if(	!file_exists($dir) ){
				mkdir($dir, 0777, true);
			}
			
			$name_file = "{$year}-{$this->number}_{$this->name}_{$this->surname}.pdf";
			$name_file = preg_replace('/\s/','_',$name_file);
			$name_file = preg_replace('/\//','',$name_file);
			$file =$dir . "/" . $name_file; 

			$relative_path = substr($file, strlen(_MARION_ROOT_DIR_));
			
			if( Marion::getConfig('eshop','nameInvoice') ){
				$_values['code_invoice'] = Marion::getConfig('eshop','nameInvoice')."/".$year."/".$number;
			}else{
				$_values['code_invoice'] = "invoice/{$year}/{$number}";
			}
			
			/*debugga($this);exit;
			$temp = $this;
		
			foreach($temp->getOrders() as $k => $v){
				$product = $v->getProduct();
				if( is_object($product) ){
					$v->sku = $product->sku;
					$v->name = $product->get('name');
				}
				$temp->orders[$k] = $v;
			}
			if( !$temp->paymentDate){
				$temp->paymentDate = $temp->evacuationDate;
			}
			

			//totale carrello senza iva
			$total = $temp->getTotalWithoutVAT($this->vatCode) + Eshop::removeVATFromPrice($temp->shippingPrice,$this->vatCode) + Eshop::removeVATFromPrice($temp->paymentPrice,$this->vatCode) - Eshop::removeVATFromPrice($temp->discount,$this->vatCode);
			$_values['totalFinal'] = Eshop::formatMoney($total);
			
			//totale iva del carrello
			$vat = $temp->getVAT($this->vatCode) + Eshop::extractVATFromPrice($temp->shippingPrice,$this->vatCode) + Eshop::extractVATFromPrice($temp->paymentPrice,$this->vatCode) - Eshop::extractVATFromPrice($temp->discount,$this->vatCode);
			
			if( $this->vatCode){
				$_values['vat'] = Eshop::formatMoney($this->vatCode);
			}else{
				$_values['vat'] = Eshop::formatMoney($vat);
			}*/
			
			
			$_values['now'] = strftime('%d/%m/%Y',time());


			
			
			$_values['cart'] = $this;

			
			
			
			/*if (defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_) {
				$pdf->setTemplate('pdf/modello_fattura.htm');
			}else{
				$pdf->setTemplate('modello_fattura.htm');
			}*/

			
			
			ob_start();
			echo $twig->render('invoice_templates/invoice1.htm', $_values);
			$html = ob_get_contents();
			ob_end_clean();
			
			//debugga($html);exit;
			$pdf = _obj('PDF2');
			
			$pdf->setHtml($html);
			$pdf->setPageSize(Wkhtmltopdf::SIZE_A4);
			$pdf->setOptions(" --disable-smart-shrinking");
			$pdf->output(Wkhtmltopdf::MODE_SAVE, $file);
			
			
			
			//$pdf->save($file);
			


			$toinsert = array(
				'number' => $number,
				'cartId' => $this->id,
				'date' => date('Y-m-d H:i:s'),
				'code' => "invoice/{$year}/{$number}",
				'path' => $relative_path,
				'year' => $year
			);
			if( Marion::getConfig('eshop','nameInvoice') ){
				$toinsert['code'] = Marion::getConfig('eshop','nameInvoice')."/".$year."/".$number;

			}
			
			$database->insert('invoice',$toinsert);



			
			$this->set(
				array(
					'invoice_code' => $toinsert['code'],
					'hasInvoice'=>1
				))->save();
			return true;
			
		}else{
			return false;
		}
		

	}


	function getPathInvoice(){
		if( $this->hasInvoice ){
			$database = Marion::getDB();
			$invoice = $database->select('*','invoice',"cartId={$this->id}");
			
			
			if(okArray($invoice)){
				$invoice = $invoice[0];
				$invoice['path'] = _MARION_ROOT_DIR_.$invoice['path'];
				if( file_exists($invoice['path']) ){
					return $invoice['path'];
				}
				
			}
		}
		return false;
	}	


	function showInvoice(){
		if( $this->hasInvoice ){
			$database = Marion::getDB();
			$invoice = $database->select('*','invoice',"cartId={$this->id}");
			
			
			if(okArray($invoice)){
				$invoice = $invoice[0];
				
				$invoice['path'] = _MARION_ROOT_DIR_.$invoice['path'];
				if( file_exists($invoice['path']) ){
					
					$name_file = "{$this->number}_{$this->name}_{$this->surname}";
					
					header('Content-type: application/pdf');
					header('Content-disposition: inline; filename="' . $name_file . '.pdf"');
					header('Content-Transfer-Encoding: binary');
					header('Content-Length: ' . filesize($invoice['path']));
					header('Accept-Ranges: bytes');
					readfile($invoice['path']);
					exit;
				}
				
			}
		}

	}
	//stampa l'ordine
	function printOrder($page_template = "print_order.htm"){
		
		$template = _obj('Template');
		$ordini = $this->getOrders();
		$azienda = getConfig('azienda');
		$pdf = _obj('PDF');
		
		
		$pdf->azienda = $azienda;
		
		foreach($ordini as $k => $ord){
			$prodotto = $ord->getProduct();
			//debugga($prodotto);exit;
			if(is_object($prodotto)){
				$ordini[$k]->productname = $prodotto->getName();
				$ordini[$k]->link = $prodotto->getUrl();
				$ordini[$k]->img = $prodotto->getUrlImage(0,'thumbnail');
			}
		}
		$template->ordini = $ordini;

		
		
		$temp = $this;

		//totale carrello senza iva
		
		$template->total_without_vat = $temp->getTotalWithoutVAT($this->vatCode);
		$template->vat_total = $temp->getVAT($this->vatCode);

		$template->shipping_without_vat =  Eshop::removeVATFromPrice($temp->shippingPrice,$this->vatCode);
		$template->vat_shipping = Eshop::extractVATFromPrice($temp->shippingPrice,$this->vatCode);

		$template->payment_without_vat =  Eshop::removeVATFromPrice($temp->paymentPrice,$this->vatCode);
		$template->vat_payment = Eshop::extractVATFromPrice($temp->paymentPrice,$this->vatCode);


		$total = $temp->getTotalWithoutVAT($this->vatCode) + Eshop::removeVATFromPrice($temp->shippingPrice,$this->vatCode) + Eshop::removeVATFromPrice($temp->paymentPrice,$this->vatCode) - Eshop::removeVATFromPrice($temp->discount,$this->vatCode);
		
		
		
		
		//totale iva del carrello
		$vat = $temp->getVAT($this->vatCode) + Eshop::extractVATFromPrice($temp->shippingPrice,$this->vatCode) + Eshop::extractVATFromPrice($temp->paymentPrice,$this->vatCode) - Eshop::extractVATFromPrice($temp->discount,$this->vatCode);
		

		$template->totalFinal_without_vat = Eshop::formatMoney($total);
		$template->vat_totaleFinal = Eshop::formatMoney($vat);


	
		$template->totale_finale =  Eshop::formatMoney($vat+$total);
		

		
		

		$pdf->ordini = $ordini;
		

		$pdf->cart = $this;
		$pdf->setTemplate($page_template);
		$pdf->build();
		
		$pdf->show($this->number.'.pdf');

	}


	//override metodo di BASE
	function onCreate(){

	}


	function beforeSave(){
		
		
		parent::beforeSave();
		$this->getDataShippingFromAdressId();
		Marion::do_action('cart_before_save',array(&$this));

		

	}
	

	function afterSave(){
		parent::afterSave();
		Marion::do_action('cart_after_save',array(&$this));
		
	}


	
	//salva l'oggetto nel database
	/*public function save(){
		
		$this->beforeSave();
		
		$check = $this->checkSave();
		
		$flag = (int)$check;
		if($check == 1){
			$database = _obj('Database');
		
			foreach($this as $k => $v){
				if( $this->existsColumn($k) ){
					if( is_array($v) ){
						$data[$k] = serialize($v);
					}else{
						$data[$k] = $v;
					}
				}
			}
			if( $this->existsColumn('dateLastUpdate') ){
				$data['dateLastUpdate'] = date('Y-m-d H:i:s');
			}
			
			$field_id = STATIC::TABLE_PRIMARY_KEY;
			
			if($this->$field_id){
				$res = $database->update(STATIC::TABLE,STATIC::TABLE_PRIMARY_KEY."={$this->$field_id}",$data);
				
				if( !$res ){
					$this->error_query = $database->error;
				}else{
					unset($this->error_query);
				}
				
			}else{
				$res = $database->insert(STATIC::TABLE,$data);
				if( !$res ){
					$this->error_query = $database->error;
				}else{
					$this->id = $res;
					unset($this->error_query);
				}		
			}
			$class_name = get_called_class();
			if( $class_name=='Cart'){
				//debugga($database->lastquery);exit;
			}
			
			$this->afterSave();
			return $this;
		}else{
			return $check;
		}

	}*/





	/****************************************************************** METODI STATICI ************************************************************************/
	

	//restituisce un carrello a partire dal suo number
	public static function withNumber($number){
		return Cart::prepareQuery()->where('number',$number)->getOne();
		
	}

	//restituisce il carrello corrente
	public static function getCurrent(){
		$user = getUser();
		
		
		//se l'utente è loggato
		if(is_object($user)){
			//debugga($user);
			$dataUser = $user->getDataCart();
			$currentCart = self::prepareQuery()
					->where('status','active')
					->where('user',$user->getId())
					->getOne();
			if( $currentCart->discount ){
				$currentCart->set(
						array(
						'discount'=>0
					))->save();
			}
			if(is_object($currentCart)){
				return $currentCart;
			}else{
				
				//creo il carrello
				$cart = Cart::create();
				$dataCart = array(
					'user' => $user->getId(),	
					'status' => 'active',
					'vatCode' => Marion::getConfig('eshop','vat'),
					'creationDate' => date('Y-m-d H:i:s'),
				);
				
				//setto la valuta
				$currency = getConfig('eshop','defaultCurrency');
				
				if( $GLOBALS['activecurrency'] ){
					$cart->currency = $GLOBALS['activecurrency'];
				}
				
				foreach($user as $k => $v){
					if( in_array($k,$cart->getColumnsArray()) && $k != 'id'){
						$dataCart[$k] = $v;
					}
				}
			   
			   //creo il numero dell'ordine
			   $cart->createNumber();
			   $cart->set($dataCart);
			   $cart->set($dataUser);
			   $res = $cart->save();
				
				return $res;
			}
		}else{
			if( !isset($_SESSION['sessionCart']) ){
				$_SESSION['sessionCart'] = array();
			}
			return $_SESSION['sessionCart'];
		}

	}


	//aggiunge un prodotto all carrello corrente. 
	public static function add($data,$id=NULL){
		if( !$data['product']) return false;
		
		// se l'utente è loggato
		if(authUser()){
			
			$order = Order::create();
			$order->set($data);
			
			$res = $order->addToCart($id);
		}else{
			$res = Order::addToCartNotLogged($data);
		}
		
		return $res;
	}
	

	//metodo che stabilisce se un carrello è virtuale
	public static function getVirtualFlag(){
		$cart = self::getCurrent();
		// se l'utente è loggato
		if(authUser()){
			
			return $cart->isVirtual();
		}else{
			$check = true;
			if( isset($cart['orders']) && okArray($cart['orders']) ){
				foreach($cart['orders'] as $k => $v){
					if( $v['product'] ){
						
						$product = Product::withId($v['product']);
						if( is_object($product) ){
							if( !$product->isVirtual()){
								$check = false;
								break;
							}
						}
					}
				}
			}
			return $check;
			
		}
	}
	
	//restituisce gli ordini del carrello corrente sottoforma di oggetti
	public static function getCurrentOrders(){
		$cart = self::getCurrent();
		// se l'utente è loggato
		if(authUser()){
			return $cart->getOrders();
		}else{
			
			$toreturn = array();
			if(isset($cart['orders']) && okArray($cart['orders'])){
				$children = array();
				foreach($cart['orders'] as $k => $v){
					if(isset($v['parent']) && $v['parent'] ){
						$v['id'] =  $k;
						$order = Order::create()->set($v);
						$children[$v['parent']][] = $order;
					}
				}
				foreach($cart['orders'] as $k => $v){
					if( !isset($v['parent']) || !$v['parent']){
						$v['id'] =  $k;
						$order = Order::create()->set($v);
						if( array_key_exists($v['id'],$children) && okArray($children[$v['id']])){
							$order->children = $children[$v['id']];
						}
						$toreturn[] = $order;
					}
				}
			}
			
			return $toreturn;
		}
	}

	


	//prende il carrello in sessione e lo unisce a quello corrente memorizzato nel database
	public static function load(){
		if(authUser()){
			if( isset($_SESSION['sessionCart']['orders']) ){
				$session_cart = $_SESSION['sessionCart']['orders'];
				
				if(okArray($session_cart)){
					foreach( $session_cart as $k => $data){
						if( $data['parent'] ){
							$parent = $data['parent'];
							unset($data['parent']);
							$session_cart[$parent]['children'][] = $data;
							unset($session_cart[$k]);
						}
					}
					

					foreach( $session_cart as $data){
						if( $data['children'] ){
							$children = $data['children'];
							unset($data['children']);
						}
						$order = Order::create();
						$order->set($data);
						$id_order = $order->addToCart();
						
						if( okArray($children) ){
							foreach($children as $child){

								$child['parent'] = $id_order;
								$order = Order::create();
								$order->set($child);
								$order->addToCart();
							}
						}

					}
				}
				unset($_SESSION['sessionCart']['orders']);
			}
		}
	}


	public function updateCurrent(){
		$cart = self::getCurrent();
		if( authUser() ){
			$cart->update();
		}else{
			$orders = $cart['orders'];
			if(okArray($orders) ){
				Cart::setCurrentOrders($orders);
			}
		}

	}

	//restituisce il numero totale di articoli nel carrello corrente
	public static function getCurrentNumberProduct(){
		if( authUser() ){
			$cart = self::getCurrent();
			return $cart->getNumberProduct();
		}else{
			$cart = self::getCurrent();
			$number = 0;
			if(isset($cart['orders']) && okArray($cart['orders'])){
			
				foreach($cart['orders'] as $order){
					if( !$order['parent'] ){
						$number += $order['quantity']; 
					}
				}
		
			}
			return $number;
		}

	}

	//restituisce il totale del carrello corrente escludendo le spese di spedizione e pagamento. Se il parmaetro discount è impostato a true allora applica anche gli sconti
	public static function getCurrentTotal(){
		if( authUser() ){
			$cart = self::getCurrent();
			$total = $cart->getTotal();
		}else{
			$cart = self::getCurrent();
			$total = 0;
			if(okArray($cart['orders'])){
				
				foreach($cart['orders'] as $order){
					
					if( !isset($order['parent']) || !$order['parent'] ){
						if( !isset($order['supplement']) ) $order['supplement'] = 0;
						if( !isset($order['discount']) ) $order['discount'] = 0;
						$total += ((float)$order['price']  + (float)$order['supplement'] - (float)$order['discount'] )*$order['quantity']; 
					}
				}
		
			}
		}
		
		return $total;

	}

	

	//restituisce il totale del carrello corrente formattato escludendo le spese di spedizione e pagamento
	public static function getCurrentTotalFormatted(){
		$total = self::getCurrentTotal();
		return number_format($total, 2, ',', '');

	}




	//restituisce il peso del carrello corrente
	public static function getCurrentWeight(){
		if( authUser() ){
			$cart = self::getCurrent();
			return $cart->getWeight();
		}else{
			$cart = self::getCurrent();
			$weight = 0;
			if(okArray($cart['orders'])){
				foreach($cart['orders'] as $order){
					$weight += $order['weight']*$order['quantity']; 
				}
		
			}
			return $weight;
		}

	}


	//restituisce il costo di spedizione del carrello corrente
	public static function getCurrentCostShipping(){
		$cart = self::getCurrent();
		if( authUser() ){
			return $cart->getCostShipping();

		}else{
			$weight = self::getCurrentWeight();
			if( $cart['data']['shippingMethod'] ){
				$shippingMethod = ShippingMethod::withId($cart['data']['shippingMethod']);
				if(is_object($shippingMethod)){
					if( $cart['data']['shippingCountry']){
						
						$shippingPrice = $shippingMethod->getPrice($cart['data']['shippingCountry'],$weight);
						return $shippingPrice;
					}
				}
			}
		}

		return 0;
	}
	
	//restituisce il totale del carrello comprensivo delle spese di spedizione
	/*public static function getCurrentTolalFinal(){
		if(authUser()){
			$cart = self::getCurrent();
			return $cart->getTotalFinal();
		}else{
			//$subTotal =  self::getCurrentTolal();
			$subTotal = 0;
			$shippingPrice = 0;
			$weight =  self::getCurrentWeight();
			if(  $_SESSION['sessionCart']['data']['shippingMethod']){
				$shippingMethod = ShippingMethod::withId( $_SESSION['sessionCart']['data']['shippingMethod']);
			}
			if(is_object($shippingMethod)){
				$shippingPrice = $shippingMethod->getPrice( $_SESSION['sessionCart']['data']['shippingCountry'],$weight);
			}
			return $subTotal + $shippingPrice;
		}
		
		
	}*/


	public static function getCurrentInfo($id_cart=null){
		$num_products = 0;
		$total = 0;
		$total_without_tax = 0;
		$total_tax = 0;
		$shipping_price = 0;
		$shipping_price_without_tax = 0;
		$shipping_price_tax = 0;
		$payment_price = 0;
		$payment_price_without_tax = 0;
		$payment_price_tax = 0;
		$total_weight = 0;
		if( authUser() || $id_cart){
			if( $id_cart ){
				$cart = Cart::withId($id_cart);
			}else{
				$cart = Cart::getCurrent();
			}
			$data = array(
				'num_products' => $cart->num_products,
				'total' => $cart->total,
				'total_without_tax' => $cart->total_without_tax,
				'total_tax' => $cart->total_tax,
				'total_weight' => $cart->total_weight,
				'shipping_price' =>$cart->shippingPrice,
				'payment_price' =>$cart->paymentPrice,
				'dateLastUpdate' => $cart->dateLastUpdate,
				'discount' => $cart->discount,
				'supplement' => $cart->supplement,
			);
			
		}else{
			if( isset($_SESSION['sessionCart']['orders']) && okArray($_SESSION['sessionCart']['orders']) ){
				foreach($_SESSION['sessionCart']['orders'] as $v){
					$num_products += $v['quantity'];
					$total += $v['quantity']*((float)$v['price']+(float)$v['supplement']-(float)$v['discount']);
					$total_without_tax += $v['quantity']*((float)$v['price_without_tax']+(float)$v['supplement_without_tax']-(float)$v['discount_without_tax']);
					$total_tax += $v['quantity']*((float)$v['taxPrice']+(float)$v['supplement_tax']-(float)$v['discount_tax']);
					$total_weight += $v['quantity']*$v['weight'];
				}
			}

			if( isset($cart['data']['shippingMethod']) && $cart['data']['shippingMethod'] ){
				$shippingMethod = ShippingMethod::withId($cart['data']['shippingMethod']);
				if(is_object($shippingMethod)){
					if( $cart['data']['shippingCountry']){
						
						$shipping_price = $shippingMethod->getPrice($cart['data']['shippingCountry'],$total_weight);
						
					}
				}
			}

			$data = array(
				'num_products' => $num_products,
				'total' => $total,
				'total_without_tax' => $total_without_tax,
				'total_tax' => $total_tax,
				'total_weight' => $total_weight,
				'shipping_price' =>$shipping_price
			);
		}

		return $data;
		
	}

	public static function getCurrentDataUser(){
		if(!authUser()){
			return $_SESSION['sessionCart']['data'];
		} else {
			self::getCurrent();
		}
		
	}
	


	//metodo che crea un password random
	public static function randomPassword() {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}





	//inserisce nel carrello corrente gli ordini (nel caso in cui non  si è loggati)
	public static function setCurrentOrders($orders,$check = false){
		if(!authUser()){
			$_SESSION['sessionCart']['orders'] = $orders;
			
		}
		return 1;
	}
	//inserisce nel carrello i dai di fatturazione/spedizione (nel caso in cui non  si è loggati)
	public static function setCurrentData($data){
		if(!authUser()){
			$_SESSION['sessionCart']['data'] = $data;
		}
	}


	//inserisce nel carrello corrente un pagamento ricorente
	public static function setCurrentRecurrentPaymentOrder($data){
		
		//controllo sulle quantità
		//controllo quantità disponibile
		$checkQuantityCatalog = getConfig('eshop','checkQuantityCatalog');
		
		if( !$data['product']){
			$data['product'] = $_SESSION['sessionCart']['recurrent_payment']['product'];
		}

		$product = Product::withId($data['product']);
		if( !is_object($product) ){
			return "error";
		}

		//controllo minimo quantità
		if( $data['quantity'] < $product->minOrder ){
			return sprintf($GLOBALS['gettext']->strings['minOrder_product'],$product->getName(),$product->minOrder);
		}

		//controllo quantità massima
		if( $product->maxOrder ){
			if( $data['quantity'] > $product->maxOrder ){
				return sprintf($GLOBALS['gettext']->strings['maxOrder_product'],$product->getName(),$product->maxOrder);
			}
		}


		if( $checkQuantityCatalog ){
			if( is_object($product) ){
				if( $product->centralized_stock && $product->parent){
					$parent = $product->getParent();
					$qnt = $parent->getInventory();
					$check = (int)($qnt -  $data['quantity']);
					if( $check < 0 ){
						return sprintf($GLOBALS['gettext']->strings['quantity_product_not_aviable'],$product->getName());
					}
				}else{
					$qnt = $product->getInventory();
					$check = (int)($qnt -  $data['quantity']);
					if( $check < 0 ){
						return sprintf($GLOBALS['gettext']->strings['quantity_product_not_aviable'],$product->getName());
					}
				}
			}
		}

		if( $data['product'] ){
			$product = Product::withId($data['product']);
			if( is_object($product) ){
				$data['price'] = $product->getPriceValue($data['quantity']);
			}
		}

		if( okArray($data) ){
			foreach($data as $k => $v){
				$_SESSION['sessionCart']['recurrent_payment'][$k] = $v;
			}
		}

		return 1;
		
	}

	
	public static function getCurrentRecurrentPaymentOrder(){
		
		$data = $_SESSION['sessionCart']['recurrent_payment'];
		$data['id'] = 1;
		$order = Order::create()->set($data);

		return $order;
		
	}

	public static function getCurrentRecurrentPaymentTotal(){
		
		$data = $_SESSION['sessionCart']['recurrent_payment'];
		$data['id'] = 1;
		$order = Order::create()->set($data);
		if( is_object($order) ){
			return $order->getTotalPrice();
		}else{
			return 0;
		}
	}

	public static function getCurrentRecurrentPaymentWeight(){
		$weight = 0;
		$data = $_SESSION['sessionCart']['recurrent_payment'];
		$data['id'] = 1;
		$order = Order::create()->set($data);
		if( is_object($order) ){
			$product = $order->getProduct();
			if( is_object($product) ){
				$weight =$product->weight*$order->quantity;
			}
			
		}
		return $weight;
	}


	//restituisce il totale dell'ordine con pagamento ricorrente escludendo le spese di spedizione e pagamento. Se il parmaetro discount è impostato a true allora applica anche gli sconti
	public static function getCurrentTotalRecurrentPaymentOrder($discount=true){
		
		$order = self::getCurrentRecurrentPaymentOrder();
		$total = 0;
		if( is_object($order) ){
			$total = $order->price;
			if( $discount ){
				$total +=  $order->supplement - $order->discount;
			}
			$total = $total*$order->quantity;		
		}
		
		return $total;

	}


	
}

?>