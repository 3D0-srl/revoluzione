
<?php
class GoogleTagManagerSnippet extends Module{

	function install(){
		$res = parent::install();
		if( $res ){

			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"google_tag_manager_snippet_setting\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":null,\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"1009\",\"campo\":\"header\",\"etichetta\":\"Snippet header\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"8\",\"tipo\":null,\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1009\",\"campo\":\"body\",\"etichetta\":\"Snippet body\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"8\",\"tipo\":null,\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");
			
		}


		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();
		if( $res ){
			
			Form::delete('google_tag_manager_snippet_setting');


			Marion::delConfig('google_tag_manager_snippet','header');
			Marion::delConfig('google_tag_manager_snippet','body');
		}	
		return $res;
	}

}
?>
