<?php
include "config.php";


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

session_id($paymentId);
session_start();
$_SESSION['payment-result'] = $result;

$resultPageUrl = $merchantDomain . "/result.php?paymentid=" . $paymentId;

echo $resultPageUrl;
?>
