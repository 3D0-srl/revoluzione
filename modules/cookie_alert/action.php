<?php
use \Marion\Core\Marion;

/*function alert_cookie_fields_multilocale(&$array){
	
	$database = _obj('Database');
	$field_page = $database->fields_table('cookieAlertLocale');
	foreach($field_page as $v){
		if( $v != 'cookieAlert' && $v != 'locale'){
			$list_fields[]= $v;
		}
	}
	$form = $database->select('*','form',"nome='module_cookie_alert'");
	foreach($list_fields as $campo){
		$campo_data = $database->select('codice','form_campo',"campo='{$campo}' and form={$form[0]['codice']}");
		$array[] = $campo_data[0]['codice'];
	}

	

}



function alert_cookie_authorization(&$list=array()){
	
	
	
	if( $_COOKIE['COOKIE_LAW_CONSENT'] ){
		
		$list[] = array(
			'module' => 'cookie_alert',
			'name' => "Consenso ai <b>cookie</b> per migliorare l'esperienza di navigazione"
		);
		
	}

	

}


Marion::add_action('authorization_user','alert_cookie_authorization');


function alert_cookie_del_authorization($module,$options){
	
	if( $module == 'cookie_alert'){
		
		
		setcookie('COOKIE_LAW_CONSENT', '', -1,'/');
		
	}

	

}


Marion::add_action('delete_authorization_user','alert_cookie_del_authorization');
*/
?>