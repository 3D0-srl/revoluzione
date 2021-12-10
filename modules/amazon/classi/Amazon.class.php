<?php

require_once "MarketplaceWebService/Exception.php";
require_once "MarketplaceWebService/RequestType.php";
require_once "MarketplaceWebService/Interface.php";
require_once "MarketplaceWebService/Mock.php";
require_once "MarketplaceWebService/Model.php";
require_once "MarketplaceWebService/Client.php";


require_once "MarketplaceWebServiceProducts/Exception.php";
require_once "MarketplaceWebServiceProducts/Interface.php";
require_once "MarketplaceWebServiceProducts/Model.php";
require_once "MarketplaceWebServiceProducts/Client.php";



class Amazon{
	private $prefix = 'marion_';

	private $AWS_ACCESS_KEY_ID;
	private $AWS_SECRET_ACCESS_KEY;
	private $APPLICATION_NAME;
	private $APPLICATION_VERSION;
	private $MERCHANT_ID;
	private $MARKETPLACE_ID;
	private $maxErrorRetry = 3;



	function setKeyId($value){
		$this->AWS_ACCESS_KEY_ID = $value;
	}

	function setSecretKey($value){
		$this->AWS_SECRET_ACCESS_KEY = $value;
	}

	function setApplicationName($value){
		$this->APPLICATION_NAME = $value;
	}

	function setApplicationVersion($value){
		$this->APPLICATION_VERSION = $value;
	}

	function setMerchantId($value){
		$this->MERCHANT_ID = $value;
	
	}

	function setMarketPlaceId($value){
		$this->MARKETPLACE_ID = $value;
	}

	public static function create($AWS_ACCESS_KEY_ID,$AWS_SECRET_ACCESS_KEY,$MERCHANT_ID,$APPLICATION_NAME='Marion',$APPLICATION_VERSION="1.0"){
		self::loadClass('MarketplaceWebService_Model');
		self::loadClass('MarketplaceWebService_Model_ContentType');
		self::loadClass('MarketplaceWebService_Model_FeedSubmissionInfo');
		self::loadClass('MarketplaceWebService_Model_ResponseMetadata');
		self::loadClass('MarketplaceWebService_Model_IdList');
		self::loadClass('MarketplaceWebService_Model_ErrorResponse');
		
		
		$amazon = new Amazon();
		$amazon->setKeyId($AWS_ACCESS_KEY_ID);
		$amazon->setSecretKey($AWS_SECRET_ACCESS_KEY);
		$amazon->setApplicationName($APPLICATION_NAME);
		$amazon->setApplicationVersion($APPLICATION_VERSION);
		$amazon->setMerchantId($MERCHANT_ID);
		return $amazon;
	}


	public static function getMarketplaceId($locale){
		
		$array = array(
			'uk' => 'A1F83G8C2ARO7P',
			'de' =>'A1PA6795UKMFR9',
			'es' => 'A1RKKUPIHCS9HS',
			'fr' => 'A13V1IB3VIYZZH',
			'it' => 'APJ6JRA9NG5V4'
			'nl' => 'A1805IZSGTT6HS'
		);

		$id = $array[$locale];

		return $id;


	}


	public static function getServiceUrl($site='it',$type='feed'){
		switch($type){

			case 'feed':
				switch($site){
					case 'com':
						$serviceUrl = "https://mws.amazonservices.com";
						break;
					case 'uk':
						$serviceUrl = "https://mws.amazonservices.co.uk";
						break;
					case 'de':
						$serviceUrl = "https://mws.amazonservices.de";
						break;
					case 'fr':
						$serviceUrl = "https://mws.amazonservices.fr";
						break;
					case 'it':
						$serviceUrl = "https://mws.amazonservices.it";
						break;
					case 'jp':
						$serviceUrl = "https://mws.amazonservices.jp";
						break;
					case 'cn':
						$serviceUrl = "https://mws.amazonservices.com.cn";
						break;
					case 'ca':
						$serviceUrl = "https://mws.amazonservices.ca";
						break;

				}
				break;
			case 'product':
				switch($site){
					case 'com':
					case 'it':
					case 'fr':
					case 'de':
					case 'uk':
					case 'cn':
					case 'nl':
						$serviceUrl = "https://mws-eu.amazonservices.com/Products/2011-10-01";
						break;
					case 'jp':
						$serviceUrl = "https://mws.amazonservices.jp/Products/2011-10-01";
						break;

				}
				break;
		}
		return $serviceUrl;

	}

	


	function getClient($site = 'it',$type='feed'){
		
		
		$serviceUrl = self::getServiceUrl($site,$type);
		//debugga($serviceUrl);exit;
		if( $type == 'feed'){
			
			$config = array (
			  'ServiceURL' => $serviceUrl,
			  'ProxyHost' => null,
			  'ProxyPort' => -1,
			  'MaxErrorRetry' => $this->maxErrorRetry,
			);
		
			 $service = new MarketplaceWebService_Client(
				 $this->AWS_ACCESS_KEY_ID, 
				 $this->AWS_SECRET_ACCESS_KEY, 
				 $config,
				 $this->APPLICATION_NAME,
				 $this->APPLICATION_VERSION);
		}else{

			$config = array (
			  'ServiceURL' => $serviceUrl,
			  'ProxyHost' => null,
			  'ProxyPort' => -1,
			  'MaxErrorRetry' => $this->maxErrorRetry,
			);
			
			
			 $service = new MarketplaceWebServiceProducts_Client(
				 $this->AWS_ACCESS_KEY_ID, 
				 $this->AWS_SECRET_ACCESS_KEY, 
				 $this->APPLICATION_NAME,
				 $this->APPLICATION_VERSION,
				 $config);

		}
		

		return $service;
	}

	

	public static function loadClass($className){
		$filePath = "classes/".str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		if( file_exists($filePath)){
			require_once($filePath);
		}
		
	}
	
	
	public static function loadClassesSubmitFeed(){
		self::loadClass('MarketplaceWebService_Model_SubmitFeedResponse');
		self::loadClass('MarketplaceWebService_Model_SubmitFeedResult');
		self::loadClass('MarketplaceWebService_Model_SubmitFeedRequest');

	}

	public static function loadClassesCancelFeedSubmissions(){
		self::loadClass('MarketplaceWebService_Model_CancelFeedSubmissionsResponse');
		self::loadClass('MarketplaceWebService_Model_CancelFeedSubmissionsResult');
		self::loadClass('MarketplaceWebService_Model_CancelFeedSubmissionsRequest');

	}


	public static function loadClassesGetFeedSubmissionList(){
		self::loadClass('MarketplaceWebService_Model_GetFeedSubmissionListRequest');
		self::loadClass('MarketplaceWebService_Model_GetFeedSubmissionListResponse');
		self::loadClass('MarketplaceWebService_Model_GetFeedSubmissionListResult');		
	}


	public static function loadClassesGetFeedSubmissionResult(){
		self::loadClass('MarketplaceWebService_Model_GetFeedSubmissionResultRequest');
		self::loadClass('MarketplaceWebService_Model_GetFeedSubmissionResultResponse');
		self::loadClass('MarketplaceWebService_Model_GetFeedSubmissionResultResult');
	}


	public static function loadClassesGetReportList(){
		self::loadClass('MarketplaceWebService_Model_RequestReportRequest');
		self::loadClass('MarketplaceWebService_Model_RequestReportResponse');
		self::loadClass('MarketplaceWebService_Model_RequestReportResult');
	}

	/*public static function loadClassesReport(){
		self::loadClass('MarketplaceWebService_Model_GetReportRequest');
		/*self::loadClass('MarketplaceWebService_Model_GetReportCountRequest');
		self::loadClass('MarketplaceWebService_Model_GetReportCountResponse');	
		self::loadClass('MarketplaceWebService_Model_GetReportCountResult');

	}*/


	public function submitFeedRequest($xml,$feedType,$sites=array('it')){
		
		self::loadClassesSubmitFeed();

		$request = new MarketplaceWebService_Model_SubmitFeedRequest();
		$request->setMerchant($this->MERCHANT_ID);
		$request->setFeedType($feedType);
		
		//imposto i markeplaces
		if( okArray($sites) ){
			foreach( $sites as $site ){
				$marketplaceId = Amazon::getMarketplaceId($site);
				$request->setMarketplace($marketplaceId);
			}
		}
		$feedHandle = @fopen('php://temp', 'rw+');
		fwrite($feedHandle, $xml);
		rewind($feedHandle);

		$request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
		$request->setPurgeAndReplace(false);
		$request->setFeedContent($feedHandle);
		

		//prendo il client
		$client = $this->getClient();
		$response = $client->submitFeed($request);
		
		if ($response->isSetSubmitFeedResult()) { 

			$submitFeedResult = $response->getSubmitFeedResult();
			
			if ( $submitFeedResult->isSetFeedSubmissionInfo() ) { 
				
				$feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
				
				
				if ($feedSubmissionInfo->isSetFeedSubmissionId()) {
					 return $feedSubmissionInfo->getFeedSubmissionId();
				}
			}
		}

		return false;
	}


	public function cancelFeedSubmissions($FeedSubmissionId){
		self::loadClassesCancelFeedSubmissions();
		// Or the request can be constructed like
		$request = new MarketplaceWebService_Model_CancelFeedSubmissionsRequest();
		$request->setMerchant($this->MERCHANT_ID);
		
		$idList = new MarketplaceWebService_Model_IdList();
		$request->setFeedSubmissionIdList($idList->withId($FeedSubmissionId));
		
		
		$client = $this->getClient();
		
		$response = $client->cancelFeedSubmissions($request);
		if ($response->isSetCancelFeedSubmissionsResult()) { 
			$cancelFeedSubmissionsResult = $response->getCancelFeedSubmissionsResult();
            if ($cancelFeedSubmissionsResult->isSetCount()) {


			}

			$feedSubmissionInfoList = $cancelFeedSubmissionsResult->getFeedSubmissionInfoList();
			foreach ($feedSubmissionInfoList as $feedSubmissionInfo) {

				
				if ($feedSubmissionInfo->isSetFeedSubmissionId()) 
				{
					$item['id'] = $feedSubmissionInfo->getFeedSubmissionId();
				}
				if ($feedSubmissionInfo->isSetFeedType()) 
				{
					
					$item['type'] = $feedSubmissionInfo->getFeedType();
				}
				if ($feedSubmissionInfo->isSetSubmittedDate()) 
				{
					
					$item['submittedDate'] = $feedSubmissionInfo->getSubmittedDate()->format("Y-m-d H:i:s");
				}
				if ($feedSubmissionInfo->isSetFeedProcessingStatus()) 
				{
					$item['status'] = $feedSubmissionInfo->getFeedProcessingStatus();
				}
				if ($feedSubmissionInfo->isSetStartedProcessingDate()) 
				{
					$item['processingDate'] = $feedSubmissionInfo->getStartedProcessingDate()->format("Y-m-d H:i:s");
				}
				if ($feedSubmissionInfo->isSetCompletedProcessingDate()) 
				{
					$item['completedProcessingDate'] = $feedSubmissionInfo->getCompletedProcessingDate()->format("Y-m-d H:i:s");
				}
				$list[] = $item;

			}
	
		}
		return $list;
	}
	

	function getFeedSubmissionList(){
		self::loadClassesGetFeedSubmissionList();

		$request = new MarketplaceWebService_Model_GetFeedSubmissionListRequest();
		
		$request->setMerchant($this->MERCHANT_ID);
		
		$client = $this->getClient();
		$response = $client->getFeedSubmissionList($request);
		if ($response->isSetGetFeedSubmissionListResult()) { 
			$getFeedSubmissionListResult = $response->getGetFeedSubmissionListResult();
			  if ($getFeedSubmissionListResult->isSetNextToken()) 
				
				$feedSubmissionInfoList = $getFeedSubmissionListResult->getFeedSubmissionInfoList();
				foreach ($feedSubmissionInfoList as $feedSubmissionInfo) {
				
					if ($feedSubmissionInfo->isSetFeedSubmissionId()) 
					{
						$item['id'] = $feedSubmissionInfo->getFeedSubmissionId(); 
						
					}
					if ($feedSubmissionInfo->isSetFeedType()) 
					{
						$item['type'] = $feedSubmissionInfo->getFeedType();
					}
					if ($feedSubmissionInfo->isSetSubmittedDate()) 
					{
						
						$item['submittedDate'] = $feedSubmissionInfo->getSubmittedDate()->format("Y-m-d H:i:s");
					}
					if ($feedSubmissionInfo->isSetFeedProcessingStatus()) 
					{
						$item['status'] = $feedSubmissionInfo->getFeedProcessingStatus();
					}
					if ($feedSubmissionInfo->isSetStartedProcessingDate()) 
					{
						$item['processingDate'] = $feedSubmissionInfo->getStartedProcessingDate()->format("Y-m-d H:i:s");
					}
					if ($feedSubmissionInfo->isSetCompletedProcessingDate()) 
					{
						$item['completedProcessingDate'] = $feedSubmissionInfo->getCompletedProcessingDate()->format("Y-m-d H:i:s");
					}
					$list[] = $item;
				}
		}
		return $list;

	}


	


	function getFeedSubmissionResult($FeedSubmissionId){
		self::loadClassesGetFeedSubmissionResult();
		$request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
		$request->setMerchant($this->MERCHANT_ID);
		$request->setFeedSubmissionId($FeedSubmissionId);
		$fileHandle = fopen('php://memory', 'rw+');
		$request->setFeedSubmissionResult($fileHandle);
		
		$client = $this->getClient();
		$response = $client->getFeedSubmissionResult($request);
		
		rewind($fileHandle);
		$responseStr = stream_get_contents($fileHandle);
		$responseXML = new SimpleXMLElement($responseStr);
		debugga($responseXML);exit;
		if ($response->isSetGetFeedSubmissionResultResult()) {
			$getFeedSubmissionResultResult = $response->getGetFeedSubmissionResultResult();
			
			if ($getFeedSubmissionResultResult->isSetContentMd5()) {
                    
                   $result['contentMd5'] = $getFeedSubmissionResultResult->getContentMd5();
			}

			if ($response->isSetResponseMetadata()) { 
				$responseMetadata = $response->getResponseMetadata();
				if ($responseMetadata->isSetRequestId()) 
				{
					
					$result['requestId'] = $responseMetadata->getRequestId();
				}
			}
		}
		

		return  $result;
	}





	function getReportList(){
		self::loadClassesGetReportList();

	}




	function createXML($id_products,$type='product',$loc='it'){
		if( !is_array($id_products) ){
			$id_products = array($id_products);
		}
		
		$xml = '';
		$template = _obj('Template');
		$template->loc_data = $loc;
		$template->merchantIdentifier = $this->MERCHANT_ID;
		$template->prefix = $this->prefix;
		switch($type){
			case 'product':
				$template->timestamp = date('Y-m-d'); 
				
				foreach($id_products as $id_product){
					$product = Product::withId($id_product);
					if( is_object($product) ){

						$product->description = strip_tags($product->get('description',$loc));
						if( $product->manufacturer ){
							$manufacturer = Manufacturer::withId($product->manufacturer);
							if( is_object($manufacturer) ){
								$product->manufacturer_name = $manufacturer->get('name',$loc);
							}
						}
						if( !$product->width || !$product->depth || !$product->height || !$product->weight ){
							$product->no_dimensioni = true;

						}
						$template->products[] = $product;
						if( $product->hasChildren()){
							foreach($product->getChildren() as $product1){
								$attributes = $product1->getAttributes();
								if( okArray($attributes) ){
									foreach($attributes as $label =>$value){
										//$attr = Attribute::withLabel($label);
										//debugga($attr);exit;
										$attrValue = AttributeValue::withId($value);
										$product1->size = $attrValue->get('value',$loc);
									}
								}
								$product1->description = strip_tags($product1->get('description',$loc));
								if( $product1->manufacturer ){
									if( is_object($manufacturer) ){
										$product1->manufacturer_name = $manufacturer->get('name',$loc);
									}
								}
								if( !$product1->width || !$product1->depth || !$product1->height || !$product1->weight ){
									$product1->no_dimensioni = true;

								}
								$template->products[] = $product1;
							}
						}
					}
				}
				
				ob_start();
				$template->output_module('amazon','AmazonFeedProduct.xml');
				$xml = ob_get_contents();
				ob_end_clean();
				//debugga($xml);exit;
				break;
			case 'relationship':
				$database = _obj('Database');
				foreach($id_products as $id_product){
					//$product = Product::withId($id_product);
					$children = $database->select('id','product',"parent={$id_product} AND deleted = 0");
					if( okArray($children) ){
						$dati = array();
						$dati['product'] = $id_product;
						foreach($children as $v){
							$dati['children'][] = $v['id'];
						}
						$toreturn[] = $dati;
					}
					/*$attributeSet = $product->getAttributeSet();
					if( is_object($attributeSet) ){
						$attributi = $attributeSet->getAttributes();
						
						$select = $database->select('a.product,v.value,attrL.name,attr.id','(((product as p join productAttribute as a on a.product=p.id) join attributeValueLocale as v on v.attributeValue = a.value) join attribute as attr on attr.label=a.attribute) join attributeLocale as attrL on attrL.attribute=attr.id',"p.parent={$id_product} AND v.locale='{$loc}' AND attrL.locale='{$loc}'");
						
						if( okArray($select) ){
							$gruppo = array();
							foreach($select as $v){
								$gruppo[$v['product']][$v['id']]['name'] = $v['name'];
								$gruppo[$v['product']][$v['id']]['value'] = $v['value'];
							}
						}
					}
					$dati = array();
					$dati['product'] = $id_product;
					$dati['children'] = $gruppo;
					$toreturn[] = $dati;*/
				}
				
				$template->list = $toreturn;
				ob_start();
				$template->output_module('amazon','AmazonFeedRelationship.xml');
				$xml = ob_get_contents();
				ob_end_clean();

				//debugga($xml);exit;
			
				break;
			case 'price':
				$database = _obj('Database');
				$where = "p.id in (";
				foreach($id_products as $v){
					$where .= "{$v},";
				}
				$where = preg_replace('/\,$/',')',$where);
				$select = $database->select('p.id,p1.value as price','product as p join price as p1 on p1.product=p.id',$where." AND label='default'");
				
				$template->list = $select;
				ob_start();
				$template->output_module('amazon','AmazonFeedPrice.xml');
				$xml = ob_get_contents();
				ob_end_clean();
			
				break;
			case 'image':
				$database = _obj('Database');
				$where = "id in (";
				foreach($id_products as $v){
					$where .= "{$v},";
				}
				$where = preg_replace('/\,$/',')',$where);
				$select = $database->select('id,images','product',$where);
				
				if( okArray($select) ){
					foreach($select as $k => $v){
						$images = unserialize($v['images']);
						if( count($images) > 9 ){
							$select[$k]['images'] = array_slice($images, 0, 9);
						}else{
							$select[$k]['images'] = $images;
						}
						
					}
				}
				
				$template->list = $select;
				ob_start();
				$template->output_module('amazon','AmazonFeedImage.xml');
				$xml = ob_get_contents();
				ob_end_clean();

				//debugga($xml);exit;

				break;
			case 'inventory':
				$database = _obj('Database');
				$where = "id in (";
				foreach($id_products as $v){
					$where .= "{$v},";
				}
				$where = preg_replace('/\,$/',')',$where);
				$select = $database->select('id,stock','product',$where);
				$template->list = $select;
				ob_start();
				$template->output_module('amazon','AmazonFeedInventory.xml');
				$xml = ob_get_contents();
				ob_end_clean();
				break;


		}

		
		
		
		return $xml;

	}




	function getListMatchingProducts($query,$loc = 'it'){
			

			self::loadClass('MarketplaceWebServiceProducts_Model_ListMatchingProductsRequest');
			self::loadClass('MarketplaceWebServiceProducts_Model_ListMatchingProductsResponse');
			self::loadClass('MarketplaceWebServiceProducts_Model_ListMatchingProductstResult');

			
			$request = new MarketplaceWebServiceProducts_Model_ListMatchingProductsRequest();
			
			$request->setSellerId($this->MERCHANT_ID);
			$request->setQuery($query);
			$request->setMarketPlaceId(self::getMarketplaceId($loc));
			$client = $this->getClient($loc,'product');

			
			
			

			try {
				$response = $client->ListMatchingProducts($request);
				
				
				

				$dom = new DOMDocument();
				$dom->loadXML($response->toXML());
				$dom->preserveWhiteSpace = false;
				$dom->formatOutput = true;
				$responseXML = new SimpleXMLElement($dom->saveXML());
				

				
				$products = $response->getListMatchingProducts();
				debugga($products);exit;
				debugga($products);exit;
				//echo("ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");

			 } catch (MarketplaceWebServiceProducts_Exception $ex) {
				echo("Caught Exception: " . $ex->getMessage() . "\n");
				echo("Response Status Code: " . $ex->getStatusCode() . "\n");
				echo("Error Code: " . $ex->getErrorCode() . "\n");
				echo("Error Type: " . $ex->getErrorType() . "\n");
				echo("Request ID: " . $ex->getRequestId() . "\n");
				echo("XML: " . $ex->getXML() . "\n");
				echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
			 }
			debugga($response);exit;
			
	}

	function getLMatchingProduct($query,$loc = 'it'){
			

			self::loadClass('MarketplaceWebServiceProducts_Model_GetMatchingProductRequest');
			self::loadClass('MarketplaceWebServiceProducts_Model_GetMatchingProductResponse');
			self::loadClass('MarketplaceWebServiceProducts_Model_GetMatchingProductResult');
			$request = new MarketplaceWebServiceProducts_Model_GetMatchingProductRequest();
			
			$request->setSellerId($this->MERCHANT_ID);
			
			$request->setASINList(array('ASIN.1'=>$query));
			
			$request->setMarketPlaceId(self::getMarketplaceId($loc));
			$client = $this->getClient($loc,'product');
			

			
			$response = $client->GetMatchingProduct($request);
			debugga($response);exit;
			
	}
	

	






}









?>