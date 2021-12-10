<?php
require_once('modules/paypal/lib/vendor/autoload.php');
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

use PayPalHttp\HttpException;
use Marion\Controllers\FrontendController;
use Marion\Core\Marion;
use Shop\Cart;
class PaypalbackController extends FrontendController{

	private $token;
	private $PayerID;

	private $error_code;
	private $error_message;

	private $transaction_id;
	private $buyer_email;
	private $status;

    function display(){

	   //prendo i parametri
       $this->getParameters();
        

	   //prendo la transazione


	   
	   $transaction = $this->getTransaction();
	  
	   if( okArray($transaction) ){
			$transaction = $transaction[0];
			
			
			$cart = Cart::withId($transaction['cartId']);
			if( $transaction['status'] == 'pending' ){
				
				
				if( $transaction['rapid_checkout'] && !$cart->virtual_cart && !_var('checked') ){
					//if( !$cart->email ){
						header('Location: index.php?mod=paypal&action=get_order&token='.$this->token."&PayerID=".$this->PayerID);
						exit;
					//}
				}

				
				$this->capture();
				if( !$this->error_code ){
					$this->success($cart);
					$this->successTransaction();
				}

				if(!authUser()){
					unset($_SESSION['sessionCart']['data']['id']);
					unset($_SESSION['sessionCart']['data']['paymentMethod']);
					unset($_SESSION['sessionCart']['orders']);
				}else{

				}

				header('Location: '._MARION_BASE_URL_.'cart-thanks/'.$cart->id.'.htm');
			}else{
				
				header('Location: '._MARION_BASE_URL_.'cart-thanks/'.$cart->id.'.htm');
				debugga($transaction);
			}

			
			

			
			
	   }
        
    }





	function success($cart){
		$status_confirmed = $this->options['status_confirmed'];
		$cart->changeStatus($status_confirmed);
		
		$automatic_stock_type = Marion::getConfig('eshop','automaticStockType');
		
		if( $automatic_stock_type == 'onConfirmed' ){
			$automatic_stock = Marion::getConfig('eshop','automaticStock');
			if($automatic_stock){
				$cart->decreaseInventory();
			}
		}
	}

	
	function getParameters(){
		
		$this->options = marion::getConfig('paypal_module');
		
		$this->token = _var('token');
		$this->PayerID = _var('PayerID');

	}

	function getTransaction(){
		

		$database = _obj('Database');
	    $transaction = $database->select('*','transactionPayPal',"token = '{$this->token}'");
	    return $transaction;
	}


	private function getClient(){
		
       if( $this->options['sandbox'] ){
			$clientId = $this->options['sandbox_client_id'];
			$clientSecret = $this->options['sandbox_client_secret'];
			$env = new SandboxEnvironment($clientId, $clientSecret);
		}else{
			$clientId = $this->options['production_client_id'];
			$clientSecret = $this->options['production_client_secret'];
			$env = new ProductionEnvironment($clientId, $clientSecret);
		}
		


        return new PayPalHttpClient($env);
    
	}

	public static function prettyPrint($jsonData, $pre="")
    {
        $pretty = "";
        foreach ($jsonData as $key => $val)
        {
            $pretty .= $pre . ucfirst($key) .": ";
            if (strcmp(gettype($val), "array") == 0){
                $pretty .= "\n";
                $sno = 1;
                foreach ($val as $value)
                {
                    $pretty .= $pre . "\t" . $sno++ . ":\n";
                    $pretty .= self::prettyPrint($value, $pre . "\t\t");
                }
            }
            else {
                $pretty .= $val . "\n";
            }
        }
        return $pretty;
    }
	

	function successTransaction(){
		$database = _obj('Database');
		$toupdate = array(
			'transactionId' => $this->transaction_id,
			'payerId' => $this->PayerID,
			'buyerEmail' => $this->buyer_email,
			'status' => 'completed',
			'checked' => 1
		);
		$database->update('transactionPayPal',"token = '{$this->token}'",$toupdate);
		return;
	}


	function capture(){
		 $request = new OrdersCaptureRequest($this->token);
		 $client = $this->getClient();
			
		 try{
			$response = $client->execute($request);
			//debugga($response);exit;
			$this->transaction_id = $response->result->id;
			$this->buyer_email = $response->result->payer->email_address;
			$this->status = $response->result->status;

			
			
		 }catch(HttpException $exception){
			$this->error_code = $exception->statusCode;
			$message = json_decode($exception->getMessage(), true);
			$this->error_message = self::prettyPrint($message);
			debugga($this->error_code);
			debugga($this->error_message);exit;
			
		 }
	}









	





	
}