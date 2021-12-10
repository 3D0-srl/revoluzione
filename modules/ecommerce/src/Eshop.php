<?php
namespace Shop;
use Marion\Core\Marion;
class Eshop {
	

	public static function redirectSuccessPayment($id_cart){
		header('Location: '.self::getSuccessPaymentUrl($id_cart));
	}

	public static function redirectErrorPayment($id_cart,$params=array()){
		header('Location: '.self::getErrorPaymentUrl($id_cart,$params));
	}

	public static function getSuccessPaymentUrl($id_cart){
		return _MARION_BASE_URL_."index.php?ctrl=Gateway&mod=ecommerce&action=success&id=".$id_cart;
	}

	public static function getErrorPaymentUrl($id_cart,$params=array()){
		return _MARION_BASE_URL_."index.php?ctrl=Gateway&mod=ecommerce&action=error&id=".$id_cart;
	}

	//restituisce il valore dell'IVA (VAT)
	public static function getVAT(){

		if( okArray($GLOBALS['setting']['eshop']) ){
			return $GLOBALS['setting']['eshop']['vat'];
		}else{
			$database = Marion::getDB();
			$iva = $database->select('valore','setting',"gruppo='eshop' AND chiave= 'vat'");
			
			if(okArray($iva)){
				return $iva[0]['valore'];
			}
		}
		return false;
	}

	//controlla se nell'eshop i prezzi sono di default con IVA inclusa o no
	public static function hasIncludedVAT(){
		if( okArray($GLOBALS['setting']['eshop']) ){
			return $GLOBALS['setting']['eshop']['includedVAT'];

		}else{
			$database = Marion::getDB();
			$checkIva = $database->select('valore','setting',"gruppo='eshop' AND chiave= 'includedVAT'");
			if(okArray($checkIva)){
				return $checkIva[0]['valore'];
			}
		}
		return false;
	}

	
	//aggiunge l'IVA ad un prezzo
	public static function addVatToPrice($price,$codeVat=null){
		if( $codeVat === null){
			$vat = self::getVAT();
		}else{
			$vat = $codeVat;
		}
		
		$price = $price + (($price*$vat)/100);
		return $price;
	}
	
	//rimuove l'IVA da un prezzo
	public static function removeVatFromPrice($price,$codeVat=NULL){
		if( $codeVat === null ){
			$vat = self::getVAT();
		}else{
			$vat = $codeVat;
		}
		$price = $price/(1+($vat/100));
		return $price;
	}


	//estrae l'IVA da un prezzo
	public static function extractVatFromPrice($price,$codeVat=null){
		
		$priceWithoutIva = self::removeVatFromPrice($price,$codeVat);
		$priveVAT = $price-$priceWithoutIva;
		return $priveVAT;
	}
	

	//formatta un prezzo per il rendering a video
	public static function formatMoney($val){
		return number_format($val, 2, ',', '');
	}




	public static function priceValue($val,$currency=NULL){

		$rate = Marion::getExchangeRate($currency);
		
		if( $rate ){
			return $val*$rate;
		}else{
			return $val;
		}
	}
	
	public static function getCurrenciesRate($currency=NULL){
		$database = Marion::getDB();
		$currencies = $database->select('*','currency',"1=1");
		if($currency){
			$default = strtoupper($currency);		
		}else{
			$default = Marion::getConfig('eshop','defaultCurrency');
		}
		$query = " (";
		foreach($currencies as $k => $v){
			$query.='"'.$default.$v['code'].'",';
		}
		$query = preg_replace('/\,$/',')',$query);
		
		$path = "http://query.yahooapis.com/v1/public/yql?q=";
		$path .= urlencode('select * from yahoo.finance.xchange where pair in'.$query);
		$path .= "&env=store://datatables.org/alltableswithkeys";
		$path .= "&format=json";
		  // Get cURL resource
		  $curl = curl_init();
		  // Set some options - we are passing in a useragent too here
		  curl_setopt_array($curl, array(
		      CURLOPT_RETURNTRANSFER => 1,
		      CURLOPT_URL => $path,
		      CURLOPT_USERAGENT => 'Currency Rate'
		  ));
		  // Send the request & save response to $resp
		  $feed = curl_exec($curl);
		  // Close request to clear up some resources
		  curl_close($curl);
		 
		  $feed = json_decode($feed);
		  if( is_object($feed) ){
			  $result = (array)$feed->query->results;
			  
			  foreach($result['rate'] as $rate){
				   $dati = (array)$rate;
				   $code = preg_replace("/".$default."/",'',$dati['id']);
				   if( $code ){
					    $rate_value = $dati['Rate'];
					    $rates[$code] = $rate_value;
					    $toupdate['exchangeRate'] = $rate_value;
					    $database->update('currency',"code='{$code}'",$toupdate);
					    
					    
				   }
			  }
		  }
		  $rates[$default] = 1;
		  return $rates;
	}
	
	public static function downloadCurrencies($currency=NULL){
		$rates = self::getCurrenciesRate($currency);
		$database = Marion::getDB();
		foreach($rates as $code => $value){
			$toupdate['exchangeRate'] = $value;
			$database->update('currency',"code='{$code}'",$toupdate);
		}
		  
	}

}

?>