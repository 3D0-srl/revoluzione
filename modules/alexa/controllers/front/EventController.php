<?php
// Include the library
require 'modules/alexa/alexa-endpoint/autoload.php';
use MayBeTall\Alexa\Endpoint\Alexa;
use MayBeTall\Alexa\Endpoint\User;
use MayBeTall\Alexa\Endpoint\Profile;
use MayBeTall\Alexa\Endpoint\Intent;
use MayBeTall\Alexa\Endpoint\Request;

function logga($var){
	error_log(print_r($var,true), 3, "modules/alexa/event.log");
}
class EventController extends FrontendController{
	public $path_logs = 'modules/alexa/event.log';

	
	function logga($var){
		error_log(print_r($var,true), 3, $this->path_logs);
	}
	
	function display(){

		Alexa::init();
		
		
		Alexa::messages(function(){
			$payload = Request::getPayload();
			$message = (string)$payload->request->message->sampleMessage;
			Alexa::say($message);
			logga($payload);
			
			$response = json_encode(array());
			//logga(self::$body);
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json;charset=UTF-8');
			header('Content-Length:' . strlen($response));
			echo $response;
			exit;
		});


		/*Request::init();
		$type = Request::getType();
		$payload = Request::getPayload();

		$this->logga($payload);*/
		
	}


	
		
}

?>