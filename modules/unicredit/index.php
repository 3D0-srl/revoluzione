<?php
/*
require ('../../../config.inc.php');

$template = _obj('Template');


$database = _obj('Database');

$action = _var('action');
Marion::setMenu('manage_modules');
if( $action == 'conf_ok'){

	$formdata = _var('formdata');
	
	
	$array = check_form($formdata,'unicredit_conf',$campi_aggiuntivi);
	
	if($array[0] == 'ok'){
		unset($array[0]);
		foreach($array as $k => $v){
			Marion::setConfig('unicredit_module',$k,$v);
		}
	
		Marion::refresh_config();
		$template->link = "/admin/modules/unicredit/index.php";
		$template->output('continua.htm');
	}else{
		$template->errore = $array[1];
		get_form($elements,'unicredit_conf','conf_ok',$dati);

		$template->output_module(basename(__DIR__),'setting.htm',$elements);
	}

}else{


	$dati = Marion::getConfig('unicredit_module');
	
	//debugga($dati);exit;

	get_form($elements,'unicredit_conf','conf_ok',$dati);

	$template->output_module(basename(__DIR__),'setting.htm',$elements);	

}

function array_unicredit_status_confirmed(){
	$status_avaiables = CartStatus::prepareQuery()->where('active',1)->where('visibility',1)->orderBy('orderView')->where('label','active','<>')->where('deleted','active','<>')->get();
	foreach($status_avaiables as $v){
		$toreturn[$v->label] = $v->get('name');
	}

	return $toreturn;
}


*/

?>