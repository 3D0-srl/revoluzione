<?php
require ('../../../config.inc.php');

require_once('My_ModuleHelper.php');

$database = _obj('Database');

$path_module = __DIR__;


$action = _var('action');
$module = new My_ModuleHelper(basename(__DIR__));
$module->readXML();

switch($action){
	case 'install':
		$res = $module->install();
		break;
	case 'active':
		$res = $module->active();
		break;
	case 'disable':
		$res = $module->disable();
		break;
	case 'uninstall':
		$res = $module->uninstall();
		break;
	case 'export':
		$res = $module->exportZip();
		break;
}


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