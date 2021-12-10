<?php
class AmazonSyncro{
	public $_id_account;
	public $_market;
	public $amz_store;


	//directories path 
	public $_dir_xml_feed_request = _MARION_MODULE_DIR_.'amazon/xml_upload';
	public $_dir_xml_feed_response = _MARION_MODULE_DIR_.'amazon/responses';
	public $_dir_report_response = _MARION_MODULE_DIR_.'amazon/reports';
	

	//costruttore
	function __construct($_id_account,$_market='Europe'){
		$this->_market = $_market;
		
		$this->_id_account = $_id_account;
		$this->amz_store = AmazonStore::withId($_id_account);
		if( is_object($this->amz_store) ){
			$this->amz_store->initMarket($_market);
		}
	}

	function getStore(){
		return $this->amz_store;
	}
	

	public static function init($_id_account,$_market='Europe'){

		$obj = new AmazonSyncro($_id_account,$_market);
		return $obj;
		
	}

	


	function reports($operation_tipes){
		global $store;
		$data_store = $store[$this->_market];
		$marketplaceId = $data_store['marketplaceId'];
		if( !okArray($operation_tipes) ){
			$operation_tipes = array($operation_tipes);
		}

		
		
		foreach($operation_tipes as $operation_type){
			$reportRequest = new AmazonReportRequest($this->_market);
			$reportRequest->setReportType($operation_type);
			if($operation_type == '_GET_FLAT_FILE_ACTIONABLE_ORDER_DATA_'){
				$reportRequest->setShowSalesChannel(true);
			}
			if( $marketplaceId ){
				$reportRequest->setMarketplaces(array($marketplaceId));
			}

			
			$reportRequest->setTimeLimits();
			
			$reportRequest->requestReport();
			
			$res = $reportRequest->getResponse();

			unset($res['SubmittedDate']);
			unset($res['Scheduled']);
			unset($res['StartDate']);
			unset($res['EndDate']);
			$res['marketplace'] = $this->_market;
			$res['id_store'] = $this->_id_account;
			$database = _obj('Database');
			$database->insert('amazon_report_sync',$res);

			
		}
	}

	function orders(){
		$order_obj = AmazonOrders::init($this->amz_store);
		$orders = $order_obj->download();

		
		if( okArray($orders) ){
			$corrieri = $this->amz_store->getCarriers();
			foreach($corrieri as $v){
				$map_corrieri_entrata[$v['id_amazon']] = $v['id_marion'];
			}
			
			foreach($orders as $item){
				$id = AmazonOrders::import($item['preview']['order_id'],$item['cart'],$item['orders'],$map_corrieri_entrata);
					
				if( okArray($id) ){
					$errors[] = $item['preview']['order_id'];
					$errors_messages[] = $id;
				}else{
					$toreturn[] = $item['preview']['order_id'];
				}
			}
		}

		$risposta = array(
			'success' => $toreturn,
			'error' => $errors,
			'error_messages' => $errors_messages
			
		);

		return $risposta;
	}


	function send($operation_tipes=null){
		global $store;
		if( !okArray($operation_tipes) ){
			$operation_tipes = array($operation_tipes);
		}
		//prendo i dati necessari per il feed
		$_feeds_product = array('_POST_PRODUCT_DATA_','_POST_INVENTORY_AVAILABILITY_DATA_','_POST_PRODUCT_PRICING_DATA_');

		if( okArray(array_intersect($_feeds_product,$operation_tipes)) ){
			$products = $this->getProducts();
		}
		
		if( in_array('_POST_ORDER_ACKNOWLEDGEMENT_DATA_',$operation_tipes) ){
			$ack_orders = $this->getAckOrders();
		}


		if( in_array('_POST_ORDER_FULFILLMENT_DATA_',$operation_tipes) ){
			$status_orders = $this->getShippedOrders();
		}

		
		$data_store = $store[$this->_market];
		$marketplaceId = $data_store['marketplaceId'];
		$merchantId = $data_store['merchantId'];
		
		
		
		foreach($operation_tipes as $operation_type){
			switch($operation_type){
				case '_POST_PRODUCT_DATA_':
					$xml = $this->getXMLFeedProducts($merchantId,$products);
					break;
				case '_POST_INVENTORY_AVAILABILITY_DATA_':
					$xml = $this->getXMLFeedInventory($merchantId,$products);
					break;
				case '_POST_PRODUCT_PRICING_DATA_':
					$xml = $this->getXMLFeedPrice($merchantId,$products,$data_store['currency']);
					break;
				case '_POST_ORDER_ACKNOWLEDGEMENT_DATA_':
					$xml = $this->getXMLAckOrders($merchantId,$ack_orders);
					break;
				case '_POST_ORDER_FULFILLMENT_DATA_':
					$xml = $this->getXMLStatusOrders($merchantId,$status_orders);
					break;
				
			}
			
			if( $xml ){
				//debugga($xml);exit;
				//SALVO LA RICHIESTA XML
				$this->saveXMLRequest($this->_market.$operation_type,$xml);
				//debugga($xml);exit;
				//INVIO IL FEED
				$res = $this->submitFeed($operation_type,$marketplaceId,$xml);

				echo json_encode(array(
					'result' => 'ok',
					'FeedSubmissionId' => $res
				));
				exit;
			}

			
		}

		

	}

	function submitFeed($operation_type,$marketplaceId,$xml){
		$amz=new AmazonFeed($this->_market);
		$amz->setFeedType($operation_type);
		if( $marketplaceId ){
			$amz->setMarketplaceIds(array($marketplaceId));
		}
		$amz->setFeedContent($xml);
		$amz->submitFeed();
		$res = $amz->getResponse();

		$res = $amz->getResponse();
				
		unset($res['SubmittedDate']);
		$res['marketplace'] = $this->_market;
		$res['id_store'] = $this->_id_account;
		$database = _obj('Database');
		$database->insert('amazon_feed_sync',$res);
		
		return $res['FeedSubmissionId'];

	}


	function saveXMLRequest($name_file,$xml){
		
		if( !file_exists($this->_dir_xml_feed_request) ){
			mkdir($this->_dir_xml_feed_request,0755);
		}
		if( !file_exists($this->_dir_xml_feed_request."/".$this->_id_account)){
			mkdir($this->_dir_xml_feed_request."/".$this->_id_account,0755);
		}
		file_put_contents($this->_dir_xml_feed_request.'/'.$this->_id_account.'/'.$name_file.".xml",$xml);
	}

	function saveXMLResponse($name_file,$xml){
		if( !file_exists($this->_dir_xml_feed_response) ){
			mkdir($this->_dir_xml_feed_response,0755);
		}
		file_put_contents($this->_dir_xml_feed_response.'/'.$name_file.".xml",$xml);
	}
	function saveJSONResponse($name_file,$data){
		if( !file_exists($this->_dir_xml_feed_response) ){
			mkdir($this->_dir_xml_feed_response,0755);
		}
		file_put_contents($this->_dir_xml_feed_response.'/'.$name_file.".json",json_encode($data));
		
	}

	

	


	function getFeedsResponse(){
		$database = _obj('Database');
		$feeds = $database->select('*','amazon_feed_sync',"id_store={$this->_id_account} AND FeedProcessingStatus <> '_DONE_' order by timestamp DESC limit 10");
		$responses = [];
		if( okArray($feeds) ){
		
			foreach($feeds as $v){
				$feed_list[] = $v['FeedSubmissionId'];
			}

			global $store;
			
			
			$amz=new AmazonFeedList($this->_market);
		
			$amz->setTimeLimits('- 24 hours'); //limit time frame for feeds to any updated since the given time
			$amz->setFeedIds($feed_list);
			$amz->setFeedStatuses(array("_SUBMITTED_", "_IN_PROGRESS_", "_DONE_")); //exclude cancelled feeds
			$amz->fetchFeedSubmissions(); //this
			$res = $amz->getFeedList();
			
			if( okArray($res) ){
				
				foreach($res as $v){
					$responses[] = $v;
					$toupdate = array('FeedProcessingStatus'=>$v['FeedProcessingStatus']);
					if( $v['FeedProcessingStatus'] == '_DONE_' ){
						$_data = $this->getFeedResponse($v);
						$toupdate['successes'] = $_data['header']['MessagesSuccessful'];
						$toupdate['errors'] = $_data['header']['MessagesWithError'];
						$toupdate['warnings'] = $_data['header']['MessagesWithWarning'];
					}
					$database->update('amazon_feed_sync',"FeedSubmissionId='{$v['FeedSubmissionId']}'",$toupdate);
				}
			}
		}
		echo json_encode($responses);
		exit;
	}

	function getReportsResponse(){
		
		$database = _obj('Database');
		$reports = $database->select('*','amazon_report_sync',"id_store={$this->_id_account} AND ReportProcessingStatus  <> '_DONE_' AND ReportProcessingStatus  <> '_DONE_NO_DATA_' order by timestamp DESC limit 10");
		$responses = array();
		if( okArray($reports) ){
		
			foreach($reports as $v){
				$report_list[] = $v['ReportRequestId'];
				$market_report[$v['ReportRequestId']] = $v['marketplace'];
			}

			global $store;
			
			
			$amz=new AmazonReportRequestList($this->_market);


			$amz->setRequestIds($report_list);
			$amz->fetchRequestList();
			$res = $amz->getList();
			
			
			if( okArray($res) ){
				
				foreach($res as $v1){
					$responses[] = $v1;
					if( $v1['ReportProcessingStatus'] == '_DONE_' ){
						$this->getReportResponse($v1,$market_report[$v1['ReportRequestId']]);
						
					}
					
					$database->update('amazon_report_sync',"ReportRequestId='{$v1['ReportRequestId']}'",array('ReportProcessingStatus'=>$v1['ReportProcessingStatus']));
				}
			}
			
		}
		echo json_encode($responses);
		exit;
	}




	function getResponses(){
		$this->getReportsResponse();
		$this->getFeedsResponse();
		

		
	}


	function getFeedResponse($v){
		global $store;
		$amz = new AmazonFeedResult($this->_market,$v['FeedSubmissionId']); //feed ID can be quickly set by passing it to the constructor
		$amz->fetchFeedResult();
		$response = $amz->getRawFeed();
		
		//SALVO LA RISPOSTA 
		$this->saveXMLResponse($v['FeedSubmissionId'],$response);
		

		$data = $this->parseFeedResponse($response);
		
		$this->saveJSONResponse($v['FeedSubmissionId'],$data);
		
		/*switch($v['FeedType']){
			case '_POST_PRODUCT_DATA_':

				break;
			case '_POST_PRODUCT_RELATIONSHIP_DATA_':

				break;
			case '_POST_PRODUCT_PRICING_DATA_':

				break;
			case '_POST_INVENTORY_AVAILABILITY_DATA_':

				break;
			case '_POST_ORDER_ACKNOWLEDGEMENT_DATA_':

				break;
			case '_POST_ORDER_FULFILLMENT_DATA_':

				break;
		}*/
		return $data;
		
	}

	function getReportResponse($v,$market){
		
		global $store;
		$report = new AmazonReport($this->_market);
		$report->setReportId($v['GeneratedReportId']);
		$report->fetchReport();
		$result = $report->getRawReport();
		//debugga($v);exit;
		if( !file_exists($this->_dir_report_response."/responses") ){
			mkdir($this->_dir_report_response."/responses",0755,true);
		}
		
		switch($v['ReportType']){
			case '_GET_MERCHANT_LISTINGS_DATA_':
				$path = $this->_dir_report_response."/".$this->_id_account."_".$market.".csv" ;
				$path_response = $this->_dir_report_response."/responses/".$v['ReportRequestId'].".csv";
				file_put_contents($path_response,$result);
				file_put_contents($path,$result);
				
				
				
				break;
			default:
				if( $market ){
					$path = $this->_dir_report_response."/".$this->_id_account."_".$market."_".$v['ReportType'].".csv" ;
				}else{
					$path = $this->_dir_report_response."/".$this->_id_account."_".$v['ReportType'].".csv" ;
				}
				$path_response = $this->_dir_report_response."/responses/".$v['ReportRequestId'].".csv";
				file_put_contents($path_response,$result);
				file_put_contents($path,$result);

				
				break;

		
		}
	}


	function parseFeedResponse($response){
	
		$xml=simplexml_load_string($response);
		if( is_object($xml) ){
			$header = (array)$xml->Message->ProcessingReport->ProcessingSummary;
			$result = $xml->Message->ProcessingReport->Result;
			foreach($result as $v){

				$messages[] = array(
					'message_id'=> (string)$v->MessageID,
					'sku'=> (string)$v->AdditionalInfo->SKU,
					'message'=> (string)$v->ResultDescription,
					'result'=> (string)$v->ResultCode,
					'error_code'=> (string)$v->ResultMessageCode,
		
				);

			}
		}
		return array(
			'header' => $header,
			'messages' => $messages
		);

	}
	/******************************************************* DATA SOURCE ********************************************************************************/	
	function getAckOrders(){
		$database = _obj('Database');
		$orders_data = $database->select('*','amazon_order',"id_account={$this->amz_store->id} AND ack=0");
		foreach($orders_data as $k => $v){
			$orders_data[$k]['rows'] = $database->select('*','amazon_order_item',"id_order='{$v['id_amazon']}'");
		}
		$database->update('amazon_order',"id_account={$this->amz_store->id} AND ack=0",array('ack'=>1));
		return $orders_data;

	}

	function getShippedOrders(){
		$database = _obj('Database');
		$database->update('amazon_order',"id_marion=1",array('id_marion'=>1));
		$orders_data = $database->select('*','amazon_order',"sent=0");
		

		$corrieri = $this->amz_store->getCarriersExit();
					
		foreach($corrieri as $v){
			$map_corrieri_exit[$v['id_marion']][$v['market']] = $v['id_amazon'];
		}

		foreach($orders_data as $k => $v){
			
			$cart = Cart::withId($v['id_marion']);
			if( !$cart->shippingDate){ 
				unset($orders_data[$k]);
				continue;
			}
			$order_rows = $database->select('*','amazon_order_item',"id_order='{$v['id_amazon']}'");
			$orders_data[$k]['rows'] = $order_rows;
			$da_aggiornare[] = $v['id_marion'];

			if( $map_corrieri_exit[$cart->shippingMethod][$v['market']] || $map_corrieri_exit[$cart->shippingMethod][0] ){
				if(  $map_corrieri_exit[$cart->shippingMethod][$v['market']] ){
					$orders_data[$k]['CarrierName'] = $map_corrieri_exit[$cart->shippingMethod][$v['market']];
				}
				if(  $map_corrieri_exit[$cart->shippingMethod][0] ){
					$orders_data[$k]['CarrierName'] = $map_corrieri_exit[$cart->shippingMethod][0];

				}
				

			}else{
				$orders_data[$k]['CarrierName'] = 'standard';
			}

			$orders_data[$k]['ShippingMethod'] = 'standard';
			$orders_data[$k]['ShipperTrackingNumber'] = $cart->trackingCode;
		}
		
		foreach($da_aggiornare as $v){
			$database->update('amazon_order',"id_marion={$v}",array("sent" => 1));
		}
		
		return $orders_data;
		

	}

	function getProducts(){
		$this->amz_store->getProducts($this->_market);
		return $this->amz_store->products;
	}



	/******************************************************* XML FEED ********************************************************************************/	
	function getXMLFeedProducts($merchantId,$products){
		$date_now = AmazonTool::getDateNow();
		
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>Product</MessageType>
			<PurgeAndReplace>false</PurgeAndReplace>';
			foreach($products as $row){
				$disable = $row['disable']==1?'Delete':'PartialUpdate';
				$parent_flag_disable = $row['disable'];
				$tmp = array();
				if(trim($row['bullet_1'])){
					$tmp[]= $row['bullet_1'];
				}
				if(trim($row['bullet_2'])){
					$tmp[]= $row['bullet_2'];
				}
				if(trim($row['bullet_3'])){
					$tmp[]= $row['bullet_3'];
				}
				if( okArray($tmp) ){
					
					$row['bullets'] = $tmp;
				}
				if( $row['upc'] || $row['ean'] || $row['asin']){
					if( $row['upc'] ){
						$_type = 'UPC';
						$_valore = $row['upc'];
					}elseif($row['ean']){
						$_type = 'EAN';
						$_valore = $row['ean'];
					}else{
						$_type = 'ASIN';
						$_valore = $row['asin'];
					}
					if( !okArray($row['children']) ){
						$xml .= '<Message> 
									<MessageID>'.$row['id'].'</MessageID>
									<OperationType>'.$disable.'</OperationType> 
									<Product> 
										<SKU>'.$row['sku'].'</SKU> 
										<StandardProductID> 
											<Type>'.$_type.'</Type> 
											<Value>'.$_valore.'</Value> 
										</StandardProductID> 
										<ProductTaxCode>A_GEN_TAX</ProductTaxCode> 
										<LaunchDate>'.$date_now.'</LaunchDate> 
										<Condition> 
											<ConditionType>New</ConditionType> 
										</Condition> 
										<DescriptionData> 
											<Title>'.htmlspecialchars($row['name']).'</Title>
											<Description>
												'.htmlspecialchars($row['name']).'
											</Description>
											';
											if( okArray($row['bullets'])){
												foreach($row['bullets'] as $bullet){
													$xml .='<BulletPoint>'.$bullet.'</BulletPoint>';
												}
											}
										$xml .='</DescriptionData>';

									$xml .= '</Product>
								</Message>';
						}
					}
					if( okArray($row['children']) ){
						foreach($row['children'] as $row2){
							if( $row['parent_description'] ){
								$row2['bullets'] = $row['bullets']; 
							}else{
								$tmp = array();
								if(trim($row2['bullet_1'])){
									$tmp[]= $row2['bullet_1'];
								}
								if(trim($row2['bullet_2'])){
									$tmp[]= $row2['bullet_2'];
								}
								if(trim($row2['bullet_3'])){
									$tmp[]= $row2['bullet_3'];
								}
								if( okArray($tmp) ){
									
									$row2['bullets'] = $tmp;
								}
							}
							if( $parent_flag_disable ){
								$disable = 'Delete';
							}else{
								$disable = $row2['disable']==1?'Delete':'PartialUpdate';
							}
							if( $row2['upc'] || $row2['ean'] ){
								if( $row2['upc'] ){
									$_type = 'UPC';
									$_valore = $row2['upc'];
								}else{
									$_type = 'EAN';
									$_valore = $row2['ean'];
								}
								$xml .= '<Message> 
									<MessageID>'.$row2['id'].'</MessageID>
									<OperationType>'.$disable.'</OperationType> 
									<Product> 
										<SKU>'.$row2['sku'].'</SKU> 
										<StandardProductID> 
											<Type>'.$_type.'</Type> 
											<Value>'.$_valore.'</Value> 
										</StandardProductID> 
										<ProductTaxCode>A_GEN_TAX</ProductTaxCode> 
										<LaunchDate>'.$date_now.'</LaunchDate> 
										<Condition> 
											<ConditionType>New</ConditionType> 
										</Condition> 
										<DescriptionData> 
											<Title>'.htmlspecialchars($row2['name']).'</Title>
											<MerchantShippingGroupName>SHIPPINGTEMPLATENAME</MerchantShippingGroupName>
											<Description>
												'.htmlspecialchars($row2['name']).'
											</Description>
											';
										if( okArray($row2['bullets'])){
											foreach($row2['bullets'] as $bullet){
												$xml .='<BulletPoint>'.$bullet.'</BulletPoint>';
											}
										}
										$xml .='</DescriptionData>
										</Product>
									</Message>';
								} 
						}
					}
			}
			$xml .= '</AmazonEnvelope>';

			
		return $xml;
	}


	function getXMLFeedInventory($merchantId,$products){
		
		
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>Inventory</MessageType>';
			foreach($products as $row){
				if( $this->_market == 'UK'){
					$row['stock'] = 0;
				}
				if( ($row['ean'] || $row['upc']) && !$row['disable']){
					$xml .= '<Message> 
						<MessageID>'.$row['id'].'</MessageID>
						<OperationType>Update</OperationType> 
						<Inventory> 
							<SKU>'.$row['sku'].'</SKU> 
							<Quantity>'.$row['stock'].'</Quantity>
						</Inventory>
						</Message>';
				}
				if( okArray($row['children']) ){
					foreach($row['children'] as $row2){
						if( $this->_market == 'UK'){
							//$row2['stock'] = 0;
						}
						//$row2['stock'] = 0;
						if( ($row2['ean'] || $row2['upc']) && !$row2['disable'] ){
							$xml .= '<Message> 
								<MessageID>'.$row2['id'].'</MessageID>
								<OperationType>Update</OperationType> 
								<Inventory> 
									<SKU>'.$row2['sku'].'</SKU> 
									<Quantity>'.$row2['stock'].'</Quantity>
								</Inventory>
								</Message>';
						}
					}
				}
			}
			$xml .= '</AmazonEnvelope>';
			
		return $xml;
	}


	function getXMLFeedPrice($merchantId,$products,$currency){
		
		
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>Price</MessageType>';
			foreach($products as $row){
				if( ($row['ean'] || $row['upc']) && !$row['disable']){
					$price = $row['price'];
					if( $row['price_override'] ){
						$price = $row['price_override'];
					}
					$price = round($price,2);
					$price = $price + $price*0.15;
					$price = round($price,2);
					$xml .= '<Message> 
						<MessageID>'.$row['id'].'</MessageID>
						<OperationType>Update</OperationType> 
						<Price>
							<SKU>'.$row['sku'].'</SKU> 
							<StandardPrice currency="'.$currency.'">'.$price.'</StandardPrice>
						</Price>
						</Message>';
				}
				if( okArray($row['children']) ){
					foreach($row['children'] as $row2){
						$price_child = 0;
						if( $row['parent_description'] ){

							$price_parent = $row['price'];
							if( $row['price_override'] ){
								$price_parent = $row['price_override'];
							}
							$price_child = $price_parent;
						}else{
							$price_child = $row2['price'];
							if( $row2['price_override'] ){
								$price_child = $row2['price_override'];
							}
							$price_child = round($price_child,2);
						}
						$price_child = $price_child + $price_child*0.15;
						$price_child = round($price_child,2);
						if(  ($row2['ean'] || $row2['upc']) && !$row2['disable']){

							$xml .= '<Message> 
								<MessageID>'.$row2['id'].'</MessageID>
								<OperationType>Update</OperationType> 
								<Price>
									<SKU>'.$row2['sku'].'</SKU> 
									<StandardPrice currency="'.$currency.'">'.$price_child.'</StandardPrice>
								</Price>
								</Message>';
						}
					}
				}
			}
			$xml .= '</AmazonEnvelope>';
			
		return $xml;
	}
	


	function getXMLStatusOrders($merchantId,$orders_data ){
			if( !okArray($orders_data) ) return false;
			$t = time() - 5400;
			if (!date_default_timezone_get()) {
					date_default_timezone_set('Europe/Helsinki');
			}
			$date = date('c', $t);
			
			$xml = '<?xml version="1.0" encoding="UTF-8"?><AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
					<Header>
						<DocumentVersion>1.01</DocumentVersion>
						<MerchantIdentifier>'.$merchantId.'</MerchantIdentifier>
					</Header>
					<MessageType>OrderFulfillment</MessageType>';
			foreach($orders_data as $v){
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
			


			

			return $xml;
	}


	function getXMLAckOrders($merchantId,$orders_data){
			if( !okArray($orders_data) ) return false;

			$xml = '<?xml version="1.0" encoding="UTF-8"?><AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
			<Header>
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$merchantId.'</MerchantIdentifier>
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

		return $xml;
	}



	


}


?>