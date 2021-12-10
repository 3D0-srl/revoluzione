<?php
class AmazonOrders{
	
	private  $enable_download_product_not_in_store = false;
	private  $enable_download_product_out_of_stock = false;
	private $carriers = [];
	private $store;
	private $limit = "- 2 days"; // 48 hours
	private $status_array =  array("Unshipped", "PartiallyShipped", "Unfulfillable",'Shipped');
	function setStore($store){
		$this->store = $store;
		$carriers = $store->getCarriers();
		foreach($carriers as $c){
			$this->carriers[$c['id_amazon']] = $c['id_marion'];
		}
		

		
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
		$items = [];
		if( okArray($list) ){
			foreach($list as $v){
				$data_tmp = $v->getData();
				
				
				

				if( !okArray($list_old) || !in_array($data_tmp['AmazonOrderId'],$list_old)){
					
					
					$data_order= $this->parseOrder($v,$name_market_by_id);
					
					$data_order['cart']['id_account'] = $this->store->id;
					//if( !in_array($data_order['preview']['order_id'],$list_old)){
					$items[$data_order['preview']['order_id']] = $data_order;
					//}
				}
			}
		}


		
		return $items;

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


	public function getProduct($sku):?Product{
		if( $sku ){
			
			$obj = Product::prepareQuery()->where('sku',$sku)->getOne();
			
			if( is_object($obj) ){
				return $obj;
			}
			
		}
		return null;
	}


	public function import($order_id,$data,$orders){
		$database = _obj('Database');
		

		$check = $database->select('*','amazon_order',"id_amazon='{$order_id}'");
		
		if( okArray($check) ){
			$errors[] = "<b>".$order_id."</b>: ordine già importato";
			return $errors;
		}
		if( !$this->carriers[$data['shipping_method']] ){
			$errors[] = "Il corriere <b>".$data['shipping_method']."</b> non è stato associato a nessun corriere dello shop";
			return $errors;
		}else{
			$data['shippingMethod'] = $this->cariers[$data['shipping_method']];
		}
		
		$cart = Cart::create();
		
		$cart->set($data);
		
		$cart->number = $order_id;
		
		
		$orders_tmp = $orders;
		foreach($orders as $order){
			
			$product = $this->getProduct($order['product']);
			
			if( is_object($product) ){
				
				if( !$this->enable_download_product_out_of_stock ){
					if(  $order['quantity'] > $product->getInventory() ){
						$errors[] = "<b>".$product->get('name')."</b>: la quantità ordinata supera quella presente nel magazzino.";
					}
				}else{

					$qty_new =  $product->getInventory()-$order['quantity'];
					if( $qty_new < 0 ) $qty_new = 0;
					$product->updateStock($qty_new);
				}
				$order['product'] = $product->id;
			}else{
				if( !$this->enable_download_product_not_in_store ){
					$errors[] = "<b>".$order['product']."</b>: codice articolo non presente in magazzino.";
				}else{
					$order['custom2'] = $order['product'];
				}
			}
			if( okArray($errors) ){
				return $errors;
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