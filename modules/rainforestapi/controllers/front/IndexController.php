<?php
class IndexController extends FrontendController{
	public $path_logs = 'modules/rainforestapi/logs/'; //cartella in cui vengono scritti i logs
	public $type_search = 'asin';

	function display(){
		$ean = 'B07VCDLX9V';
		$data_product = $this->getProductByAsin($ean);
		$dati = $this->parseResponse($data_product);
	
		//debugga($data_prodcut);exit;
	}



	function parseResponse($data){

		//debugga($data);
		$dati = array(
			'name' => $data['product']['title'],
			'description' => $data['product']['description'],
			'manufacturer_name' => $data['product']['brand'],
		);
		foreach($data['product']['images'] as $v){

			$dati['images'][$v['variant']] = $v['link'];
		}


		foreach($data['product']['feature_bullets'] as $v){
			$info = explode(':',$v);
			$dati['features'][$info[0]] = $info[1];
		}


		return $dati;
		
	}


	function getProductByEan($ean){

		$data = $this->getStoredData($ean);
		//if( $data ) return $data;
		# set up the request parameters
		$queryString = http_build_query([
		  'api_key' => '277FD0EADD1848CFAD6A883B6CD73294',
		  'type' => 'product',
		  'amazon_domain' => 'amazon.it',
		  'include_summarization_attributes' => 'true',
		  'output' => 'json',
		  'device' => 'desktop',
		  'include_a_plus_body' => 'true',
		  'language' => 'it_IT',
		  'gtin' => $ean,
		  'include_html' => 'true'
		]);

		# make the http GET request to Rainforest API
		$ch = curl_init(sprintf('%s?%s', 'https://api.rainforestapi.com/request', $queryString));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$api_result = curl_exec($ch);
		curl_close($ch);
		$this->scriviLog($ean,$api_result);
		# print the JSON response from Rainforest API
		return json_decode($api_result, true);
	}


	function getProductByAsin($ean){
		
		//$data = $this->getStoredData($ean);
		
		//if( $data ) return $data;

		
		# set up the request parameters
		$queryString = http_build_query([
		  'api_key' => '277FD0EADD1848CFAD6A883B6CD73294',
		  'type' => 'product',
		  'amazon_domain' => 'amazon.it',
		  'include_summarization_attributes' => 'true',
		  'output' => 'json',
		  'device' => 'desktop',
		  'include_a_plus_body' => 'true',
		  'language' => 'it_IT',
		  'asin' => $ean,
		  'include_html' => 'true'
		]);

		# make the http GET request to Rainforest API
		$ch = curl_init(sprintf('%s?%s', 'https://api.rainforestapi.com/request', $queryString));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$api_result = curl_exec($ch);
		curl_close($ch);
		
		$this->scriviLog($ean,$api_result);
		# print the JSON response from Rainforest API

		
		return json_decode($api_result, true);

	}
	


	function getStoredData($asin){
		if( file_exists($this->path_logs.$asin.".json") ){
			$data = file_get_contents($this->path_logs.$asin.".json");
			
			return json_decode($data, true);
		}
		
	}


	function scriviLog($asin,$data){
		
		file_put_contents($data,3,$this->path_logs.$asin.".json");
		
	}
}


?>