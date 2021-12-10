<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'includes/db.class.php';
session_start();
//
// if (strlen($_POST['name'])<1) {
//   echo 'error';
//   return ;
// }isset,is_null

// if (isset($_POST['name']) || is_null($_POST['name'])
// || isset($_POST['content']) || is_null($_POST['content'])) {
//    echo 'value not be null';
//    return;
// }

$db = new Db();

//
//$UserId=$_SESSION["userdata"]->id;

$UserId = 1;

$name = $_POST['name'];
$content =htmlentities($_POST['content']);
$html =htmlentities($_POST['html']);



$result = $db->insert( $name, $content,$html,$UserId);

if ($result) {
  echo 'ok';
}else {
   echo 'error';
}


?>
