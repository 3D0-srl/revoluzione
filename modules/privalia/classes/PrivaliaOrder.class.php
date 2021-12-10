<?php

class PrivaliaOrder{
		public $order_data = array();
		public $cart;
		private $mapping_carriers = [];
		private $mapping_status = array(
			'PENDING' => '',
			'PROCESSING' => '',
			'SHIPPED' => '',
			'CANCELLED' => '',
		
		);	
		private $enabled_channels = [];

		function __construct(){
			
		}


		public static function import($data,$params){
			
			$obj = new PrivaliaOrder();
			$obj->getParams($params);
			$obj->order_data = $data;
			$parsed_data = $obj->parse();
			$check = $obj->check($parsed_data);
			
			if( $check == 1 ){
				$obj->store($parsed_data);	
			}else{
				return $check;
			}
		}

		function getParams($params){
			
			$database = _obj('Database');
			$channels = unserialize($params['channels']);
			$carriers = unserialize($params['mapping_shipping']);
			if( okArray($channels) ){
				foreach($channels as $c){
					$channel = $database->select('*','privalia_channel',"id={$c}");
					if( okArray($channel) ){
						$this->enabled_channels[] = $channel[0]['marketplaceCode'];
					}
				}
			}
			if( okArray($carriers) ){
				$this->mapping_carriers = $carriers;
			}
			$this->mapping_status['PENDING'] = $params['mapping_pending'];
			$this->mapping_status['PROCESSING'] = $params['mapping_processing'];
			$this->mapping_status['SHIPPED'] = $params['mapping_shipped'];
			$this->mapping_status['CANCELLED'] = $params['mapping_cancelled'];
			
			
			
		}

		function check($data){
			if( !in_array($data['info']['marketplaceCode'],$this->enabled_channels)){
				return "CHANNEL_NOT_ENABLED";
			}
			if( !$data['data']['status'] ){
				return "MISSING_MAPPING_STATUS";
			}

			if( $data['data']['shipped'] && !$data['data']['shippingMethod']){
				return "MISSING_MAPPING_CARRIER";
			}

			return 1;
		}


		function parse(){
			
			$data = $this->order_data;
			$cart = Cart::create();
			$cart = array();
			$cart['data'] = array(
				'number' => $data['orderId'],
				'paymentMethod' => 'PRIVALIA',
				'comesFrom' => $data['marketplaceName'],
				'status' => $this->mapping_status[$data['status']],
				'total' => $data['totalPrice'],
				'shippingPrice' => $data['shippingCosts'],
				'currency' => $data['currency'],
				'name' => $data['billingInformation']['name'],
				'company' => $data['billingInformation']['company'],
				'address' => $data['billingInformation']['address'],
				'city' => $data['billingInformation']['city'],
				'postalCode' => $data['billingInformation']['zipCode'],
				'country' => $data['billingInformation']['countryIsoCode'],
				'email' => $data['billingInformation']['email'],
				'phone' => $data['billingInformation']['phone'],
				'province' => $data['billingInformation']['state'],
				'shippingName' => $data['shippingInformation']['name'],
				'shippingAddress' => $data['billingInformation']['address'],
				'shippingCity' => $data['shippingInformation']['city'],
				'shippingPostalCode' => $data['shippingInformation']['zipCode'],
				'shippingCountry' => $data['shippingInformation']['countryIsoCode'],
				'shippingEmail' => $data['shippingInformation']['email'],
				'shippingPhone' => $data['shippingInformation']['phone'],
				'shippingProvince' => $data['shippingInformation']['state'],
				'shipped' => ($data['status']=='SHIPPED')?1:0,
				'shippingDate' => $data['shippedOrderDate'],
				'paymentDate' => $data['createOrderDate']

			);

			
			$cart['orders'] = array();
			foreach($data['orderLines'] as $v){
				$cart['orders'][] = array(
					'price' => $v['price'],
					'quantity' => $v['quantity'],
					'sku' => $v['sku'],
					'refund' => $v['status']['Returned']
	
				);
			}

			$cart['info'] = array(
				'shopChannelName' => $data['shopChannelName'],
				'shopChannelId' => $data['shopChannelId'],
				'marketplaceCode' => $data['marketplaceCode'],
				'marketplaceName' => $data['marketplaceName'],
				'id_privalia' => $data['orderId'],
			);
			if( okArray($data['deliveryDetails']) ){
				$deliveryDetails = $data['deliveryDetails'][0];
				$carrierID = $deliveryDetails['carrierId'];
				$cart['data']['shippingMethod'] = $this->mapping_carriers[$carrierID];
			}
			

			return $cart;
			

			
			
		}


		function store($data){
			$cart = Cart::create();
			$cart->set($data['data']);
			$cart->save();

			$database = _obj('Database');
			$info = $data['info'];
			$info['id_cart'] = $cart->id;
			$database->insert('privalia_order',$info);
			debugga($cart);exit;

		}

}