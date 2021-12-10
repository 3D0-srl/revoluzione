<?php
class AmazonOrders{
	
	private static $enable_download_product_not_in_store = false;
	private static $enable_download_product_out_of_stock = false;
	
	private $store;
	private $limit = "- 2 days"; // 48 hours
	private $status_array =  array("Unshipped", "PartiallyShipped", "Unfulfillable",'Shipped');

	private $carriers;

	function setStore($store){
		$this->store = $store;
		
	}
	public static function init($store){
		if( is_object($store) ){
			$obj = new AmazonOrders();
			$obj->setStore($store);
			return $obj;
		}
		return false;
	}


	public function download(){
		$database = _obj('Database');
		$time = strftime('%Y-%m-%d',strtotime($this->limit));
		
		$uploaded_orders = $database->select('*','amazon_order',"1=1");
		if( okArray($uploaded_orders) ){
			foreach($uploaded_orders as $v){
				$list_old[] = $v['id_amazon'];
			}
		}
		

		//unset($list_old);//commentare
		foreach($this->store->marketplace as $s){
			
			$this->store->initMarket($s);
			
		}
		global $store;
		foreach($store as $k => $v){
			if( $k != 'Europe'){
				$name_market_by_id[$v['marketplaceId']] = $k;
				$_markets_id[] = $v['marketplaceId'];
			}
		}
		
		$amz = new AmazonOrderList($s); //store name matches the array key in the config file
		$amz->setMarketplaceFilter($_markets_id);

		$amz->setLimits('Modified', $this->limit); //accepts either specific timestamps or relative times 
		//$amz->setLimits('Modified', "- 48 hours"); //accepts either specific timestamps or relative times 
		$amz->setFulfillmentChannelFilter("MFN"); //no Amazon-fulfilled orders
		$amz->setOrderStatusFilter(
			$this->status_array
		 ); 
		
		
		$amz->setUseToken(); //tells the object to automatically use tokens right away
		
		
		$amz->fetchOrders(); //this is what actually sends the request
		$list = $amz->getList();

		//406-4899086-5700308","402-3196715-4579551
		//debugga($list);exit;
		//$version = curl_version();
		//debugga($amz);exit;
		//$database->update('amazon_order',"id_marion=1",array('id_marion'=>1));
		if( okArray($list) ){
			foreach($list as $v){
				$data_tmp = $v->getData();
				
				
				

				if( !in_array($data_tmp['AmazonOrderId'],$list_old)){
					
					
					$data_order= $this->parseOrder($v,$name_market_by_id);
					
					$data_order['cart']['id_account'] = $this->store->id;
					if( !in_array($data_order['preview']['order_id'],$list_old)){
						$items[$data_order['preview']['order_id']] = $data_order;
					}
				}
			}
		}
		
		/*foreach($this->store->marketplace as $s){
			
			
			$amz = new AmazonOrderList($s); //store name matches the array key in the config file
			
			$amz->setLimits('Modified', $this->limit); //accepts either specific timestamps or relative times 
			//$amz->setLimits('Modified', "- 48 hours"); //accepts either specific timestamps or relative times 
			$amz->setFulfillmentChannelFilter("MFN"); //no Amazon-fulfilled orders
			$amz->setOrderStatusFilter(
				$this->status_array
			 ); 
			
			
			$amz->setUseToken(); //tells the object to automatically use tokens right away
			$amz->fetchOrders(); //this is what actually sends the request
			$list = $amz->getList();
			
			
			
			

			if( okArray($list) ){
				foreach($list as $v){
					$data_order= $this->parseOrder($v,$s);
					$data_order['cart']['id_account'] = $this->store->id;
					if( !in_array($data_order['preview']['order_id'],$list_old)){
						$items[$data_order['preview']['order_id']] = $data_order;
					}s
				}
			}
			
			sleep(1);
			
		}*/

		
		return $items;

	}
	public static function changeStatus2(){
		$t = time() - 5400;
		if (!date_default_timezone_get()) {
				date_default_timezone_set('Europe/Helsinki');
		}
		$date = date('c', $t);

		$database = _obj('Database');
		$database->update('amazon_order',"id_marion=1",array("id_marion" => 1));


		//exit;


		//$carts = Cart::prepareQuery()->whereExpression('(shippingDate IS NOT NULL)')->orderBy('id','DESC')->get();
		$orders_data = $database->select('*','amazon_order',"sent=0");
		
		
		foreach($orders_data as $k => $ord){
			
		
			$order_rows = $database->select('*','amazon_order_item',"id_order='{$ord['id_amazon']}'");
			$ord['rows'] = $order_rows;
			$ordini_store[$ord['id_account']][] = $ord;
			
		}

		
		

		foreach($ordini_store as $id_store => $ordini){

				$store_obj = AmazonStore::withId($id_store);
					
				$store_obj->initMarkets();
				$order_obj = AmazonOrders::init($store_obj);
				$xml = '<?xml version="1.0" encoding="UTF-8"?><AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
					<Header>
						<DocumentVersion>1.01</DocumentVersion>
						<MerchantIdentifier>'.$store_obj->merchantId.'</MerchantIdentifier>
					</Header>
					<MessageType>OrderFulfillment</MessageType>';


				$corrieri = $store_obj->getCarriersExit();
					
				foreach($corrieri as $v){
					$map_corrieri_exit[$v['id_marion']][$v['market']] = $v['id_amazon'];
				}
				

				
				
				
				foreach($ordini as $v){
					$cart = Cart::withId($v['id_marion']);
					if( !$cart->shippingDate) continue;
					$da_aggiornare[] = $v['id_marion'];
					
					if( $map_corrieri_exit[$cart->shippingMethod][$v['market']] || $map_corrieri_exit[$cart->shippingMethod][0] ){
						if(  $map_corrieri_exit[$cart->shippingMethod][$v['market']] ){
							$v['CarrierName'] = $map_corrieri_exit[$cart->shippingMethod][$v['market']];
						}
						if(  $map_corrieri_exit[$cart->shippingMethod][0] ){
							$v['CarrierName'] = $map_corrieri_exit[$cart->shippingMethod][0];

						}

					}else{
						$v['CarrierName'] = 'standard';
					}

					$v['ShippingMethod'] = 'standard';
					$v['ShipperTrackingNumber'] = $cart->trackingCode;
					
					$xml .=  '<Message>
							<MessageID>'.$v['id_marion'].'</MessageID>
							<OperationType>Update</OperationType>
							<OrderFulfillment>
								<AmazonOrderID>'.$v['id_amazon'].'</AmazonOrderID>
								<FulfillmentDate>'.$date.'</FulfillmentDate>
								<FulfillmentData>
									<CarrierName>'.$v['CarrierName'].'</CarrierName>
									<ShippingMethod>'.$v['ShippingMethod'].'</ShippingMethod>';
									if( $v['ShipperTrackingNumber'] ){
										$xml .='<ShipperTrackingNumber>'.$v['ShipperTrackingNumber'].'</ShipperTrackingNumber>';
									}
								$xml .= '</FulfillmentData>';
							foreach($v['rows'] as $row){
								
								$xml .= '
								<Item>
								<AmazonOrderItemCode>'.$row['amazon_item_id'].'</AmazonOrderItemCode>
								<Quantity>'.$row['quantity'].'</Quantity>
								</Item>
								';
							}
							$xml .='</OrderFulfillment>
						</Message>';
				}
				$xml .= '</AmazonEnvelope>';
				if( !okArray($da_aggiornare) ) {
						echo 'NESSUN ORDINE DA AGGIORNARE';
						exit;
					}
					
					
				

					
				
					$amz=new AmazonFeed($store_obj->marketplace[0]); 
					
					$amz->setFeedType("_POST_ORDER_FULFILLMENT_DATA_"); 
					
					global $store;
						
					$marketplaces = array();
					foreach($store as $s){
						$marketplaces[] = $s['marketplaceId'];
					}
					$amz->setMarketplaceIds($marketplaces);
					
					$id_upload = 0;
					$amz->setFeedContent($xml);
					
					$amz->submitFeed(); 
					
					$res = $amz->getResponse();
					
					unset($res['SubmittedDate']);
					$res['marketplace'] = $store_obj->marketplace[0];
					$res['id_store'] = $_store->id;
					$res['id_upload'] = 0;
					$database->insert('amazon_feed',$res);
					$store_obj->saveXML($res['marketplace']."_POST_ORDER_FULFILLMENT_DATA_",$xml,$id_upload);
					
						
				
					foreach($da_aggiornare as $v){
						$database->update('amazon_order',"id_marion={$v}",array("sent" => 1));
					}
					

					echo "INVIATO";
					
		}
					
		/*$xml = '<?xml version="1.0" encoding="UTF-8"?><AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
			<Header>
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->store->merchantId.'</MerchantIdentifier>
			</Header>
			<MessageType>OrderFulfillment</MessageType>';*/

		//debugga($ordini_store);exit;
		

		//debugga($carts);exit;
	}

	public function changeStatus($data,$orders){
		
		$t = time() - 5400;
		if (!date_default_timezone_get()) {
				date_default_timezone_set('Europe/Helsinki');
		}
		$date = date('c', $t);
		
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?><AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
			<Header>
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->store->merchantId.'</MerchantIdentifier>
			</Header>
			<MessageType>OrderFulfillment</MessageType>
			<Message>
				<MessageID>1</MessageID>
				<OperationType>Update</OperationType>
				<OrderFulfillment>
					<AmazonOrderID>'.$data['id_amazon'].'</AmazonOrderID>
					<FulfillmentDate>'.$date.'</FulfillmentDate>
					<FulfillmentData>
						<CarrierName>'.$data['CarrierName'].'</CarrierName>
						<ShippingMethod>'.$data['ShippingMethod'].'</ShippingMethod>';
						if( $data['ShipperTrackingNumber'] ){
							$xml .='<ShipperTrackingNumber>'.$data['ShipperTrackingNumber'].'</ShipperTrackingNumber>';
						}
					$xml .= '</FulfillmentData>';
				foreach($orders as $row){
					$xml .= '
					<Item>
					<AmazonOrderItemCode>'.$row['amazon_item_id'].'</AmazonOrderItemCode>
					<Quantity>'.$row['quantity'].'</Quantity>
					</Item>
					';
				}
				$xml .='</OrderFulfillment>
			</Message>
		</AmazonEnvelope>
	';
		$this->store->initMarket($data['market']);
		
		
		
		$amz=new AmazonFeed($data['market']); 
		
		$amz->setFeedType("_POST_ORDER_FULFILLMENT_DATA_"); 

		
		
		$amz->setFeedContent($xml); 
		$amz->submitFeed(); 
		


	
	}
	public static function acks2(){
		
		$t = time() - 5400;
		if (!date_default_timezone_get()) {
				date_default_timezone_set('Europe/Helsinki');
		}
		$date = date('c', $t);
		$list = AmazonStore::prepareQuery()->get();
		$database = _obj('Database');
		$database->update('amazon_order',"id_marion=1",array('id_marion'=>1));
		
		foreach($list as $_store){

			
			$_store->initMarkets();
			$last_upload = $database->select('*','amazon_upload',"id_store={$_store->id} AND type='single' AND last_operation='_POST_ORDER_ACKNOWLEDGEMENT_DATA_' AND  finished = 0 ORDER BY id DESC limit 1");
			
			if( okArray($last_upload) ){
				$last_upload = $last_upload[0];

				$check = $database->select('*','amazon_feed',"id_upload={$last_upload['id']}");
				
				if( okArray($check) ){
					$check_status = $_store->getStatusFeed('_POST_ORDER_ACKNOWLEDGEMENT_DATA_',$last_upload['id']);
					
					if( $check_status ){

						
						$database->update('amazon_upload',"id={$last_upload['id']}",array('finished' => 1));
						echo "FINITO";
					
					}else{
						echo "IN CORSO";
						exit;
					}
				}


			}

		

			$orders_data = $database->select('*','amazon_order',"id_account={$_store->id} AND ack=0");
			foreach($orders_data as $k => $v){
					$orders_data[$k]['rows'] = $database->select('*','amazon_order_item',"id_order='{$v['id_amazon']}'");
			}
			
			if( !okArray($orders_data) ) {
				echo 'NESSUN ORDINE';
				exit;
			}
			
			
			$toinsert = array(
				'id_store' => $_store->id,
				'last_operation' => '_POST_ORDER_ACKNOWLEDGEMENT_DATA_',
				'finished' => 0,
				'type' => 'single',
			);
			$id_upload = $database->insert('amazon_upload',$toinsert);
				
	
			
			$obj = self::init($_store);
			
			$xml = '<?xml version="1.0" encoding="UTF-8"?><AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
			<Header>
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$obj->store->merchantId.'</MerchantIdentifier>
			</Header>
			<MessageType>OrderAcknowledgement</MessageType>';
			
			foreach($orders_data as $k => $v){
				$xml .= '<Message>
				<MessageID>'.($k+1).'</MessageID>
				<OrderAcknowledgement>
					<AmazonOrderID>'.$v['id_amazon'].'</AmazonOrderID>
					<MerchantOrderID>'.$v['id_marion'].'</MerchantOrderID>
					<StatusCode>Success</StatusCode>
					';
					foreach($v['rows'] as $v1){
						$xml .= 
							'<Item>
								<AmazonOrderItemCode>'.$v1['amazon_item_id'].'</AmazonOrderItemCode>
								<MerchantOrderItemID>'.$v1['product'].'</MerchantOrderItemID>
							</Item>';
					}
					
				$xml .='</OrderAcknowledgement>
			</Message>';
			}
			
			$xml .= '</AmazonEnvelope>';
			
			
			$obj->store->initMarkets();
			$amz=new AmazonFeed($obj->store->marketplace[0]); 
			global $store;
			
			$marketplaces = array();
			foreach($store as $s){
				$marketplaces[] = $s['marketplaceId'];
			}
			$amz->setMarketplaceIds($marketplaces);

			$amz->setFeedType("_POST_ORDER_ACKNOWLEDGEMENT_DATA_"); 

		
			
			$amz->setFeedContent($xml); 

				
			$amz->submitFeed(); 
			$res = $amz->getResponse();
			
			unset($res['SubmittedDate']);
			$res['marketplace'] = $obj->store->marketplace[0];
			$res['id_store'] = $_store->id;
			$res['id_upload'] = $id_upload;
			$database->insert('amazon_feed',$res);
			$_store->saveXML($res['marketplace']."_POST_ORDER_ACKNOWLEDGEMENT_DATA_",$xml,$id_upload);
			

			
			$database->update('amazon_order',"id_account={$_store->id} AND ack=0",array('ack'=>1));
			
			
		}


	}

	public static function acks(){
		
		$database = _obj('Database');
		$list = $database->select('*','amazon_order',"ack=0");
		foreach($list as $v){
			
				$amazonStore = AmazonStore::withId($v['id_account']);
				//$amazonStore->initMarket($v['markey']);
				$amz_orders = AmazonOrders::init($amazonStore);
				$orders = $database->select('*','amazon_order_item',"id_order='{$v['id_amazon']}'");
				
				$amz_orders->ack($v,$orders);
				$database->update('amazon_order',"id_amazon={$v['id_amazon']}",array('ack' => 1));
			
		}

	}

	public function ack($data,$orders){

		$amazon_order_ID = $data['id_amazon'];
		$marion_order_ID = $data['id_marion'];
		$market = $data['market'];
		
		/*$t = time() - 5400;
		if (!date_default_timezone_get()) {
				date_default_timezone_set('Europe/Helsinki');
		}
		$date = date('c', $t);
		*/
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
			<Header>
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->store->merchantId.'</MerchantIdentifier>
			</Header>
			<MessageType>OrderAcknowledgement</MessageType>
			<Message>
				<MessageID>1</MessageID>
				<OrderAcknowledgement>
					<AmazonOrderID>'.$amazon_order_ID.'</AmazonOrderID>
					<MerchantOrderID>'.$marion_order_ID.'</MerchantOrderID>
					<StatusCode>Success</StatusCode>
					';
					foreach($orders as $v){
						$xml .= 
							'<Item>
								<AmazonOrderItemCode>'.$v['amazon_item_id'].'</AmazonOrderItemCode>
								<MerchantOrderItemID>'.$v['product'].'</MerchantOrderItemID>
							</Item>';
					}
					
				$xml .= '
				</OrderAcknowledgement>
			</Message>
		</AmazonEnvelope>';
		
		$this->store->initMarket($market);
		
		
		
		$amz=new AmazonFeed($market); 
		
		$amz->setFeedType("_POST_ORDER_ACKNOWLEDGEMENT_DATA_"); 

		
		
		$amz->setFeedContent($xml); 
		$amz->submitFeed(); 
		

		

	
	}



	function parseOrder($amazon_order,$market_array){

		
		$order_data = $amazon_order->getData();
		$market = $market_array[$order_data['MarketplaceId']];
		
		$shipping_method = $order_data['ShipServiceLevel'];
		if( $order_data['PurchaseDate']){
			$date = preg_replace('/T/',' ',$order_data['PurchaseDate']);
			$date = preg_replace('/Z/','',$date);
		}

		$date = explode('.',$date)[0];
		

		$data['preview'] = array(
			'market_flag' => AmazonTool::getMarketplaceImage($market),
			'order_id' => $order_data['AmazonOrderId'],
			'buyer' => $order_data['BuyerName'],
			'total' => $order_data['OrderTotal']['Amount'],
			'date' => $date,
			'shipping_method' => $shipping_method,

		);

		$data['cart'] = array(
			'shipping_method' => $shipping_method,
			'market' => $market,
			'comesFrom' => 'AMAZON ('.$market.")",
			'name' => explode(' ',$order_data['BuyerName'])[0],
			'surname' => explode(' ',$order_data['BuyerName'])[1],
			'total' => $order_data['OrderTotal']['Amount'],
			'currency' => $order_data['OrderTotal']['CurrencyCode'],
			'evacuationDate' => $date,
			'paymentDate' => $date,
			'shippingAddress' => $order_data['ShippingAddress']['AddressLine1'],
			'shippingCity' => $order_data['ShippingAddress']['City'],
			'shippingPostalCode' =>  $order_data['ShippingAddress']['PostalCode'],
			'shippingCountry' =>  $order_data['ShippingAddress']['CountryCode'],
			'shippingPhone' => $order_data['ShippingAddress']['Phone'],
			'shippingProvince' => substr($order_data['ShippingAddress']['StateOrRegion'],0,3),
			'paymentMethod' => 'AMAZON',
			'email' => $order_data['BuyerEmail'],
			'shippingPrice' => 0,
			'paymentPrice' => 0
		);
		
		

		switch($order_data['OrderStatus']) {
				case 'Shipped':
					$data['cart']['status'] = $this->store->statusSent;
					break;
				case 'Unshipped':
					$data['cart']['status'] = $this->store->statusPaid;
					break;
			}
		$data['preview']['status'] = ($data['cart']['status'] == 'sent')?"<span class='label label-success'>SPEDITO</span>":"<span class='label label-warning'>NON SPEDITO</span>";
		
		$dettagli = $amazon_order->fetchItems();
		$rows = $dettagli->getItems();
		

		
		/*if( okArray($rows) ){
			$total = 0;
			$shipping = 0;
			foreach($rows as $v){
				$total += $v['ItemPrice']['Amount']*$v['QuantityOrdered'];
				$shipping += $v['ShippingPrice']['Amount']*$v['QuantityOrdered'];
				$data['orders'][] = array(
					'product' => $v['SellerSKU'],
					'quantity' => $v['QuantityOrdered'],
					'price' => $v['ItemPrice']['Amount'],
					'amazon_item_id' => $v['OrderItemId'],
				);
				$data['cart']['currency'] = $v['ItemPrice']['CurrencyCode'];
			}
			$data['cart']['total'] = $total;
			$data['cart']['shippingPrice'] = $shipping;
			
		}*/

		if( okArray($rows) ){
			$total = 0;
			$shipping = 0;
			foreach($rows as $v){
				$total += $v['ItemPrice']['Amount'];
				$shipping += $v['ShippingPrice']['Amount'];
				$data['orders'][] = array(
					'product' => $v['SellerSKU'],
					'quantity' => $v['QuantityOrdered'],
					'price' => $v['ItemPrice']['Amount']/$v['QuantityOrdered'],
					'amazon_item_id' => $v['OrderItemId'],
				);
				$data['cart']['currency'] = $v['ItemPrice']['CurrencyCode'];
			}
			$data['cart']['total'] = $total;
			$data['cart']['shippingPrice'] = $shipping;
			
		}
		
		return $data;
		
	}


	public static function getProduct($product){
		if( $product ){
			$explode = explode('_',$product);
			$id = $explode[0];
			
			if( $id ){
				$obj = Product::withId($id);
				
				if( is_object($obj) ){
					return $obj;
				}
			}
		}
		return false;
	}


	public static function import($order_id,$data,$orders,$map_corrieri_entrata=array()){
		$database = _obj('Database');
		

		$check = $database->select('*','amazon_order',"id_amazon='{$order_id}'");
		if( okArray($check) ){
			$errors[] = "<b>".$order_id."</b>: ordine già importato";
			return $errors;
		}
		if( !$map_corrieri_entrata[$data['shipping_method']] ){
			$errors[] = "Il corriere <b>".$data['shipping_method']."</b> non è stato associato a nessun corriere dello shop";
			return $errors;
		}else{
			$data['shippingMethod'] = $map_corrieri_entrata[$data['shipping_method']];
		}
		
		$cart = Cart::create();
		
		$cart->set($data);
		
		$cart->number = $order_id;
		
		
		$orders_tmp = $orders;
		foreach($orders as $order){
			
			$product = self::getProduct($order['product']);
			
			if( is_object($product) ){
				
				if( !self::$enable_download_product_out_of_stock ){
					if(  $order['quantity'] > $product->stock ){
						$errors[] = "<b>".$product->get('name')."</b>: la quantità ordinata supera quella presente nel magazzino.";
					}
				}else{

					$qty_new =  $product->stock-$order['quantity'];
					if( $qty_new < 0 ) $qty_new = 0;
					$product->updateStock($qty_new);
				}
				$order['product'] = $product->id;
			}else{
				if( !self::$enable_download_product_not_in_store ){
					$errors[] = "<b>".$order['product']."</b>: codice articolo non presente in magazzino.";
				}else{
					$order['custom2'] = $order['product'];
				}
			}

			
			
			
			if( !(int)$order['product'] ) unset($order['product']);
			$orders_check[] = $order;
			

		}
		
		
		if( okArray($errors) ){
			return $errors;
		}else{
			$cart->save();
			
			if( $cart->id ){

			
				foreach($orders_check as $order){

					

					$ord = Order::create();
					$order['cart'] = $cart->id;
					$ord->set($order);
						
					
					$ord->save();


				}
				
				$toinsert = array(
					'id_marion' => $cart->id,
					'id_amazon' => $order_id,
					'date' => $data['evacuationDate'],
					'market' => $data['market'],
					'id_account' => $data['id_account']
				);

				$database->insert('amazon_order',$toinsert);
				//$this->ack($toinsert,$orders_tmp);
				foreach($orders_tmp as $j => $v){
					$v['id_order'] = $order_id;
					$orders_tmp[$j]['id_order'] = $order_id;
					$database->insert('amazon_order_item',$v);
				}
				//$this->ack($toinsert,$orders_tmp);
			}

		}

		

		return $cart->id;

	}
}


?>