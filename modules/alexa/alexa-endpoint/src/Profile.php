<?php
namespace MayBeTall\Alexa\Endpoint;

class Profile{
	private $access_token;
	private $endpoint;
	private $deviceId;
	public $userId;

	function __construct(){
		$res = json_decode(file_get_contents('php://input'));
		$this->access_token = $res->context->System->apiAccessToken;
		$this->deviceId = $res->context->System->device->deviceId;
		$this->userId = $res->context->System->user->userId;
		$this->endpoint = $res->context->System->apiEndpoint;
		
	}
	

	function getUserId(){
		return $this->userId;
	}
	
	public function getName(){
		//return $this->endpoint;
		$endpoint = $this->endpoint.'/v2/accounts/~current/settings/Profile.name';
		return $this->request($endpoint);
	}

	public function getEmail(){
		$endpoint = $this->endpoint.'/v2/accounts/~current/settings/Profile.email';
		return $this->request($endpoint);
	}


	public function request($endpoint){
		$authorization = "Authorization: Bearer {$this->access_token}";
		$ch = curl_init($endpoint);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: api.eu.amazonalexa.com','Accept: application/json', $authorization ));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		return (string)json_decode($result);
	}

}

?>