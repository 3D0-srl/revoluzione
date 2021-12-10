<?php

/** 
 * 
 * example code for using the phpList API Client
 * 
 * For more information, visit https://github.com/michield/phplist-restapi-client
 * 
 * 
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);


require ('../../../config.inc.php');
include_once 'classes/phpListRESTApiClient.php';

$apiURL = 'http://phplist.3d0.it/admin/?page=call&pi=restapi';
$login = 'abuse_gs2wpc1m';
$password = 'Hc2e5O6qF_';

$phpList = new phpListRESTApiClient($apiURL, $login, $password);
$phpList->tmpPath = '/var/tmp';

$subscriberEmail = 'phplistTest@mailinator.com';

if ($phpList->login()) {
	echo "qua";
	
	
	$list = $phpList->getConnection();
	debugga($list);exit;

}
