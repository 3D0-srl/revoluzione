<?php
namespace Mailman;
use Marion\Core\Marion;
use FormBuilder\{FormBuilderElement};
class MailmanFormBuilderElement extends FormBuilderElement{
	

	public static function getID():string{
		return 'mailman_newsletter';
}


	public static function getName():string{
		return 'Email newsletter';
	}


	public static function buildHtml(array $field,array $params):string{
		$mailman_conf = Marion::getConfig('module_mailman');

		require_once(_MARION_MODULE_DIR_.'mailman_twig/classes/Mailman.class.php');
		if( $mailman_conf['form_user_subscribe_type'] == 1 ){
			$lists =  Mailman::prepareQuery()->where('default_list',1)->get();
			
		}elseif( $mailman_conf['form_user_subscribe_type'] == 3 ){
			$template->selezionabile =  true;
			$lists = Mailman::prepareQuery()->where('visibility',1)->get();
		}else{
			
			$lists = Mailman::prepareQuery()->where('visibility',1)->get();
		}
		$input = '';
		
		if( okArray($params) ){
			$_params = '';
			foreach($params as $k=> $v){
				if( in_array(strtolower(trim($k)),array('type','name','multiple')) ){
					continue;
				}
				$_params .= "{$k}='{$v}' ";
			}
		}
		
		foreach($lists as $v){

			$input .= "<input type='hidden' name='mailman_list_subscribe[]' value='".$v->id."'>";
		}
		return "<input type='text' name='{$field['name']}' {$_params}>".$input;
	}


	//in questo metodo viene effettuato il controllo sul campo
	public static function check($value=null){
		if( $value ){
			if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
				return "email non valida";
			}
		}
		/*
			// se ci sono errori occorre restituire il messaggio di errore
			return $error_string;

		*/
			
		// se non ci sono errori
		return true;
	}




	


	
}