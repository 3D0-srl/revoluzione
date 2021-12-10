<?php
use \Marion\Core\Marion;

function gdpr_log_action($obj){
	if( authUser()){
		$user = Marion::getUser();
		if( !is_object($user) || !$user->auth('admin')) {
			return;		
		}

		if( method_exists($obj,'getOldObject') ){
			$old = $obj->getOldObject();
			if( $old->id){
				$type = 'edit';
				
			}else{
				$type = 'new';
			}
		}else{
			$type = 'edit';
		}
		$id = $obj->id;
			
		$name = strtolower(get_class($obj));
		$name = preg_replace('/my_/','',$name);
		$key = $type."_".$name;
		$current_user = marion::getUser();
		$user = $current_user->name." ".$current_user->surname;
		$string = __module('gdpr',$type."_".$name,NULL,array($id,$user),TRUE);

		if( !$string ){
			$string = __module('gdpr',$type."_object",NULL,array($name,$id,$user),TRUE);
		}
		
		$database = _obj('Database');
		$current_user = Marion::getUser();
		$toinsert = array(
			'id_user' => $current_user->id,
			'log' => $string,
			'type' => $name
		);
		$database->insert('gdpr_log',$toinsert);
	}
	
	
}


$children  = array();
$_list_obj_register_action_gdpr = [];
foreach(get_declared_classes() as $class){
	if( is_subclass_of( $class, 'Base' ) ){
		$_list_obj_register_action_gdpr[] = $class;
	}
}


//$_list_obj_register_action_gdpr = array('product','section','user','usercategory');
foreach($_list_obj_register_action_gdpr as $v){
	Marion::add_action('after_save_'.$v,'gdpr_log_action');
	Marion::add_action('after_save_my_'.$v,'gdpr_log_action');
}


Marion::add_action('form_registration_user','gdpr_add_fields_user');


function gdpr_add_fields_user(&$array){

	if( _var('action') == 'add_ok'){
		//CREO I CAMPI DEL FORM
		$array['gdpr'] =  array(
			'campo'=>'gdpr',
			'type'=>'checkbox',
			'options' => array('1'),
			'obbligatorio'=>'t',
			'default'=>'0',
			'etichetta'=>'Privacy'
		);
		

	}
	
	
	return $array;
}

function gdpr_link_area($id=null){
			

		$twig = Marion::getTwig('gdpr');
		
		$database = _obj('Database');
		
		
		$options_db = Marion::getConfig('database');
		
		$check = $database->select("*","information_schema.tables","table_schema = '{$options_db['options']['nome']}' AND table_name = 'address'");
		if( okArray($check) ){
			$params['address'] = true;
		}
		echo $twig->render('link_account.htm',$params);
		

		
			
}

Marion::add_action('display_home_blocks','gdpr_link_area');



?>