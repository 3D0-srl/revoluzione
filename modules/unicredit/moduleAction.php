<?php
require ('../../../config.inc.php');

require_once('My_ModuleHelper.php');
$database = _obj('Database');

$path_module = __DIR__;

require_once('My_ModuleHelper.php');

$action = _var('action');
$module = new My_ModuleHelper(basename(__DIR__));
$module->readXML();

switch($action){
	case 'install':
		$res = $module->install();
		break;
	case 'active':
		$obj = PaymentMethod::prepareQuery()->where('code','QUIPAGO')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 1;
			$obj->save();
		}
		$res = $module->active();
		break;
	case 'disable':
		$obj = PaymentMethod::prepareQuery()->where('code','QUIPAGO')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 0;
			$obj->save();
		}
		$res = $module->disable();
		break;
	case 'uninstall':
		$res = $module->uninstall();
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