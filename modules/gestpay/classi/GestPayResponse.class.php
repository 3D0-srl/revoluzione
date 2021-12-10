<?php
class GestPayResponse{
	public $sandbox = false;
	public $status_confirmed = '';
	public $wsdl='';
	public $response = null;
	public $shopLogin;
	public $crypted_string;
	public $id_cart;
	public $attempt;
	public $number;
	public $error_code='';
	public $error_description='';
	

	function __construct(){

		$this->init();
		
		$this->getResponse();
		
		$this->getCart();
		$this->updateAttempt();

		
		
		if( $this->error_code ){
			switch($this->error_code){
				

				case 1143:
					
					$this->cart->changeStatus('payment_gestpay_canceled');
					break;
				default:
					$this->cart->changeStatus('payment_gestpay_nak');
					break;
			}
		
			if( authUser() ) {
				
				$this->cart->changeStatus('active');
			}else{
				$cart->changeStatus('canceled');
				unset($_SESSION['sessionCart']['data']['id']);
				unset($_SESSION['sessionCart']['data']['paymentMethod']);
			}

			$this->redirectToPayment();
		}else{
			
			$this->cart->changeStatus($this->status_confirmed);
			header( "Location: /payment.php?action=payment_success&id={$this->id_cart}");

		}

		
	}


	function redirectToPayment(){
		//$url = '/index.php?action=cart_payment&ctrl=Cart&mod=cart&lang='.$GLOBALS['activelocale']."&error_gestpay=". urlencode($this->error_description);
		
		$url = '/index.php?ctrl=Cart&mod=cart&action=cart_payment&lang='.$GLOBALS['activelocale']."&error_gestpay=". urlencode($this->error_description);
		header( "Location: {$url}");
		
	}



	function getCart(){
		$this->cart = Cart::withId($this->id_cart);
	}



	function updateAttempt(){
		
		$database = _obj('Database');
		$toupdate = array(
			'checked' => 1,
			'status' => $this->error_code?'NAK':'SUCCESS',
			'error_code' => $this->error_code,
			'error_text' => $this->error_description,
		);
		$database->update('transazione_gestpay',"id_cart={$this->id_cart} AND num={$this->attempt}",$toupdate);
	}


	function init(){
		$this->getConf();
		$this->getUrl();
		$this->shopLogin = $_GET["a"];
		$this->crypted_string = $_GET["b"];
	}



	function getResponse(){
		
		$params = array('shopLogin' => $this->shopLogin, 'CryptedString' => $this->crypted_string);
		


		$client = new SoapClient($this->wsdl);
		

		try {
			$this->response = $client->Decrypt($params);

			if( $this->response ){
				$result = simplexml_load_string($this->response->DecryptResult->any);
				list($this->id_cart,$this->attempt,$this->number) = explode('_',(string)$result->ShopTransactionID);
				

				$this->error_code = (string) $result->ErrorCode;
				$this->error_description = (string) $result->ErrorDescription;
			}

		} catch(SoapFault $fault) {
			trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
		}
	}



	function getUrl(){
		if ($this->sandbox) {
			//$shopLogin = $gestpay['shopLoginTest'];
			$this->wsdl = "https://sandbox.gestpay.net/gestpay/gestpayws/WSCryptDecrypt.asmx?WSDL"; //TESTCODES 
		} else {
			//$shopLogin = $gestpay['shopLogin'];
			$this->wsdl = "https://ecomms2s.sella.it/gestpay/gestpayws/WSCryptDecrypt.asmx?WSDL"; //PRODUCTION
		}

	}



	function getConf(){
		$gestpay = Marion::getConfig('gestpay_module');
		$this->sandbox = $gestpay['sandbox'];
		$this->status_confirmed = $gestpay['status_confirmed'];
	}
}


?>