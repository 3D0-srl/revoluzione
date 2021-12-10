<?php
require ('../../../config.inc.php');

require_once('My_ModuleHelper.php');
$database = _obj('Database');

$module = new My_ModuleHelper();

$module->readXML();
$res = $module->uninstall();


if( $res == 1 ){
	$risposta = array(
			'result'=>'ok'
	);
}else{
	$risposta = array(
			'result'=>'nak'
	);
}
echo json_encode($risposta);
exit;

?>