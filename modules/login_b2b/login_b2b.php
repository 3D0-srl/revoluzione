<?php
class LoginB2b extends Module{
	

	function install(){
		$res = parent::install();
		if( $res ){
			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"module_login_b2b\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"693\",\"campo\":\"return_url\",\"etichetta\":\"url di atterraggio\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"693\",\"campo\":\"show_lost_pass\",\"etichetta\":\"mostra link di recupero password\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"2\",\"value_ifisnull\":\"0\"},\"valori\":[{\"campo\":\"5299\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"}]}]}");
		}
		return $res;
	}



	function uninstall(){
		
		$res = parent::uninstall();
		if( $res ){
			Form::delete('module_login_b2b');

			
			
		}
		return $res;
	}

}



?>