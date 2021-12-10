<?php
require ('../../../include.inc.php');

$database = _obj('Database');

$path_module = __DIR__;

require_once('My_ModuleHelper.php');


$module = new My_ModuleHelper(basename(__DIR__));
$module->readXML();
$res = $module->install();




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