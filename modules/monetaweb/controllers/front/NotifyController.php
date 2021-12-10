<?php
class NotifyController extends FrontendController{

	

	function display(){

		$id_cart = _var('id_cart');
		$paymentId = $_POST['paymentid'];
		$result = array();
		$result['result'] = $_POST['result'];
		$result['authorizationCode'] = $_POST['authorizationcode'];
		$result['rrn'] = $_POST['rrn'];
		$result['merchantOrderId'] = $_POST['merchantorderid'];
		$result['responsecode'] = $_POST['responsecode'];
		$result['threeDSecure'] = $_POST["threedsecure"];
		$result['maskedPan'] = $_POST["maskedpan"];
		$result['cardCountry'] = $_POST["cardcountry"];
		$result['customField'] = $_POST["customfield"];
		$result['securityToken'] = $_POST["securitytoken"];
		$result['id_cart'] = $id_cart;

		session_id($_GET['session_id']);
		session_start();
		$_SESSION['monetaweb-payment-result'][$paymentId] = $result;


		if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$protocol = 'https://';	
		}else{
			$protocol = 'http://';
		}

		$resultPageUrl = $protocol.$_SERVER['SERVER_NAME']._MARION_BASE_URL_.'index.php?mod=monetaweb&ctrl=Result&paymentid='.$paymentId."&id_cart=".$id_cart;

		echo $resultPageUrl;
	}
}	


?>