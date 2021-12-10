<?php

/*require ('../../../config.inc.php');

$template = _obj('Template');


$database = _obj('Database');

$action = _var('action');
Marion::setMenu('manage_modules');



$template->server_ip = $_SERVER['SERVER_ADDR'];
if( $action == 'conf_ok'){
	
	$formdata = _var('formdata');
	
	
	$array = check_form($formdata,'gestpay_conf',$campi_aggiuntivi);
	
	if($array[0] == 'ok'){
		unset($array[0]);
		foreach($array as $k => $v){
			Marion::setConfig('gestpay_module',$k,$v);
		}
	
		Marion::refresh_config();
		$template->link = "/admin/modules/gestpay/index.php";
		$template->output('continua.htm');
	}else{
		$template->errore = $array[1];
		get_form($elements,'gestpay_conf','conf_ok',$dati);

		$template->output_module(basename(__DIR__),'setting.htm',$elements);
	}

}else{


	$dati = Marion::getConfig('gestpay_module');
	
	//debugga($dati);exit;

	get_form($elements,'gestpay_conf','conf_ok',$dati);
	
	$template->output_module(basename(__DIR__),'setting.htm',$elements);	

}

function array_gestpay_status_confirmed(){
	$status_avaiables = CartStatus::prepareQuery()->where('active',1)->where('visibility',1)->orderBy('orderView')->where('label','active','<>')->where('deleted','active','<>')->get();
	foreach($status_avaiables as $v){
		$toreturn[$v->label] = $v->get('name');
	}

	return $toreturn;
}


*/s

?>