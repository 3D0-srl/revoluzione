<?php
use Marion\Core\{Module,Marion,Form};
class FiltriRicerca extends Module{
	
	

	function install(){
		$res = parent::install();
		if( $res ){
			
			$this->db_import_sql_from_file(_MARION_MODULE_DIR_.'filtri_ricerca/sql/data.sql');
			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"product_feature_edit\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"536\",\"campo\":\"id\",\"etichetta\":\"codice\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"1\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"536\",\"campo\":\"name\",\"etichetta\":\"nome\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"2\",\"lunghezzamax\":\"80\",\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}}]}");


			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"product_feature_value_edit\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"537\",\"campo\":\"id\",\"etichetta\":\"codice\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"1\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"537\",\"campo\":\"value\",\"etichetta\":\"valore\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"2\",\"lunghezzamax\":\"80\",\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"3\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"537\",\"campo\":\"id_product_feature\",\"etichetta\":\"caratteristica\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}}]}");



			Form::import("{\"form\":{\"gruppo\":\"5\",\"nome\":\"filtri_ricerca_setting\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"851\",\"campo\":\"filtri\",\"etichetta\":\"filtri di ricerca\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"9\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"0\",\"function_template\":\"filtri_ricerca\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"851\",\"campo\":\"price_limit_max\",\"etichetta\":\"prezzo limite massimo\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"12\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"851\",\"campo\":\"escludi_tags\",\"etichetta\":\"Tag da escludere dalla ricerca\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"9\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"0\",\"function_template\":\"tags\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"851\",\"campo\":\"escludi_attributes\",\"etichetta\":\"Attributi da escludere dalla ricerca\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"9\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":\"attributes\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"851\",\"campo\":\"escludi_features\",\"etichetta\":\"Caratteristiche da escludere dalla ricerca\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"9\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":\"features\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");

			$database = Marion::getDB();
			$database->execute(
				"ALTER TABLE product_feature_association ADD FOREIGN KEY ( id_product) REFERENCES product(id);"	
			);

		}
		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();
		//debugga($res);
		if( $res ){
			$database = Marion::getDB();
			$database->execute('DROP TABLE product_feature');
			$database->execute('DROP TABLE product_feature_lang');
			$database->execute('DROP TABLE product_feature_value');
			$database->execute('DROP TABLE product_feature_value_lang');
			$database->execute('DROP TABLE product_feature_association');
			$conf = Marion::getConfig('filtri_ricerca');
			
			if( okArray($conf) ){
				foreach($conf as $k => $v){
					Marion::delConfig('filtri_ricerca',$k);
				}
			}
			Form::delete('product_feature_edit');
			Form::delete('product_feature_value_edit');
			Form::delete('filtri_ricerca_setting');
		}
		
		return $res;
	}

}



?>