<?php
class AmazonTool{
	static $markets = array(
		'Canada' => 'A2EUQ1WTGCTBG2',
		'US' => 'ATVPDKIKX0DER',
		'Mexico' => 'A1AM78C64UM0Y8',
		'Spain' => 'A1RKKUPIHCS9HS',
		'UK' => 'A1F83G8C2ARO7P',
		'France' => 'A13V1IB3VIYZZH',
		'Germany' => 'A1PA6795UKMFR9',
		'Italy' => 'APJ6JRA9NG5V4',
		'Brazil' => 'A2Q3Y263D00KWC',
		'India' => 'A21TJRUUN4KGV',
		'China' => 'AAHKV2X7AFYLW',
		'Japan' => 'A1VC38T7YXB528',
		'Australia' => 'A39IBJ37TRP1C6',
		'Netherlands' => 'A1805IZSGTT6HS',
		'Sweden' => 'A2NODRKZP88ZB9'

	);

	static $img_markets = array(
		'Canada' => 'ca.png',
		'US' => 'us.png',
		'Mexico' => 'mx.png',
		'Spain' => 'es.png',
		'UK' => 'gb.png',
		'France' => 'fr.png',
		'Germany' => 'de.png',
		'Italy' => 'it.png',
		'Brazil' => 'br.png',
		'India' => 'in.png',
		'China' => 'cn.png',
		'Japan' => 'jp.png',
		'Australia' => 'au.png',
		'Europe' => 'europe.png',
		'Netherlands' => 'nl.png',
		'Sweden' => 'se.png',

	);

	static $currency_markets = array(
		'Canada' => 'CND',
		'US' => 'USD',
		'Mexico' => 'MXN',
		'Spain' => 'EUR',
		'UK' => 'GBP',
		'France' => 'EUR',
		'Germany' => 'EUR',
		'Italy' => 'EUR',
		'Brazil' => 'br.png',
		'India' => 'INR',
		'China' => 'RMB',
		'Japan' => 'JPY',
		'Australia' => 'USD',
		'Netherlands' => 'EUR',
		'Sweden' => 'EUR',

	);

	public static $lang_markets = array(
		'Canada' => 'en',
		'US' => 'en',
		'Mexico' => 'es',
		'Spain' => 'es',
		'UK' => 'en',
		'France' => 'fr',
		'Germany' => 'de',
		'Italy' => 'it',
		'Brazil' => 'pr',
		'India' => 'in',
		'China' => 'en',
		'Japan' => 'en',
		'Australia' => 'en',
		'Netherlands' => 'en',
		'Sweden' => 'en',

	);

	public function __construct(){
		
	}

	public static function getMarketplaceLang($state){
		$loc = self::$lang_markets[$state];

		$locales = Marion::getConfig('locale','supportati');
		if( in_array($loc,$locales)) return $loc;
		else return 'it';

	}


	public static function getMarketplaceId($state){
		return self::$markets[$state];
	}

	public static function getMarketplaceCurrency($state){
		return self::$currency_markets[$state];
	}

	public static function getMarketplaceImage($state){
		return "images/".self::$img_markets[$state];
	}

	public static function getEndpoint($state){
		switch($state){
			case 'Canada':
			case 'US':
			case 'Mexico':
				$endpoint = 'https://mws.amazonservices.com';
				break;
			case 'Europe':
			case 'Spain':
			case 'UK':
			case 'France':
			case 'Germany':
			case 'Netherlands':
			case 'Sweden':
			case 'Italy':
				$endpoint = 'https://mws-eu.amazonservices.com';
				break;
			case 'Brazil':
				$endpoint = 'https://mws.amazonservices.com';
				break;
			case 'Japan':
				$endpoint = 'https://mws.amazonservices.jp';
				break;
			case 'China':
				$endpoint = 'https://mws.amazonservices.com.cn';
				break;
			case 'India':
				$endpoint = 'https://mws.amazonservices.in';
				break;
			case 'Australia':
				$endpoint = 'https://mws.amazonservices.com.au';
				break;

		}

		return $endpoint;
	}


	public static function getDateNow(){
		return date('Y-m-d').'T'.date('H:i:s');
	}




	public static function getCarriers(){
		$handle = fopen(_MARION_MODULE_DIR_."amazon/carriers/standard.ini", "r");
		if ($handle) {
			while (($line = fgets($handle)) !== false) {

				$list[] = trim($line);
				// process the line read.
			}

			fclose($handle);
		} else {
			// error opening the file.
		}

		sort($list);
		return $list;
	}

	public static function getCarriersExit(){
		$handle = fopen(_MARION_MODULE_DIR_."amazon/carriers/codes.ini", "r");
		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				
				if( !preg_match('/#/',$line)){
					$list[] = trim($line);
				}
				// process the line read.
			}

			fclose($handle);
		} else {
			// error opening the file.
		}

		sort($list);
		return $list;
	}

	public static function getMarionCarriers(){
		$list = ShippingMethod::prepareQuery()->get();
		foreach($list as $v){
			$carr[$v->id] = $v->get('name');
		}


		return $carr;
	}


	public static function getMarkets(){

		return array_keys(self::$markets);
	}
	
}


?>