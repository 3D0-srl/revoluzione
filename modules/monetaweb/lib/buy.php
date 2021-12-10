<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
?>
	<!DOCTYPE html>
	<html>
		<body>
			<form action="" method="POST">
				<input type="submit" value="Buy Now" name="button">
			</form>
		</body>
	</html>
<?php
} else {
  $parameters = array(
	  'id' => $terminalId,
	  'password' => $terminalPassword,
	  'operationType' => 'initialize',
	  'amount' => '1.00',
	  'currencyCode' => '978',
	  'language' => 'ITA',
	  'responseToMerchantUrl' => $merchantDomain.'/notify.php',
	  'recoveryUrl' => $merchantDomain.'/recovery.php',
	  'merchantOrderId' => 'TRCK0001',
      'cardHolderName' => 'Tom Smith',
      'cardHolderEmail'  => 'tom.smith@test.com',
	  'description' => 'Descrizione',
    'customField' => 'Custom Field'
  );




  $curlHandle = curl_init();
  curl_setopt($curlHandle, CURLOPT_URL, $setefiPaymentGatewayDomain.'/monetaweb/payment/2/xml');
  curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curlHandle, CURLOPT_POST, true);
  curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($parameters));
  curl_setopt($curlHandle, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
  $xmlResponse = curl_exec($curlHandle);
  curl_close($curlHandle);

  $response = new SimpleXMLElement($xmlResponse);

  //print_r($response);exit;
  $paymentId = $response->paymentid;
  $paymentUrl = $response->hostedpageurl;
 // ResultURL
  $securityToken = $response->securitytoken;

  $setefiPaymentPageUrl = "$paymentUrl?PaymentID=$paymentId";
  header("Location: $setefiPaymentPageUrl");
}
?>
