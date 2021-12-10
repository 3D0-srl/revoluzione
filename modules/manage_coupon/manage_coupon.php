<?php
class ManageCoupon extends Module{
	
	

	function install(){
		$res = parent::install();
		if( $res ){
			
			$database = _obj('Database');

			//creo la tabella dei coupon
			$database->execute("
					CREATE TABLE IF NOT EXISTS coupon (
					  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					  name varchar(100) NOT NULL,
					  discount_type varchar(10) NOT NULL,
					  discount_value double NOT NULL,
					  multiple_use tinyint(1) NOT NULL,
					  expiry_date date,
					  min_level double,
					  user_category text,
					  used int(11),
					  use_limit varchar(100) DEFAULT NULL,
					  users longtext,
					  num_repeat int(11) DEFAULT NULL,
					  UNIQUE KEY id (id),
					  UNIQUE KEY name (name)
					)");

			//creo la tabella dei coupon_cart
			$database->execute("
					CREATE TABLE IF NOT EXISTS coupon_cart (
					  coupon_id bigint(20) NOT NULL,
					  coupon_name varchar(100) NOT NULL,
					  carrello bigint(20) NOT NULL,
					  id_user bigint(20) UNSIGNED DEFAULT NULL
					)");
					

			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"manage_coupon_data\",\"commenti\":\"Modulo per la gestione dei coupon\",\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"682\",\"campo\":\"name\",\"etichetta\":\"Inserisci un nome al coupon altrimenti verr\\u00e0 generato in automatico\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"0\",\"lunghezzamax\":\"100\",\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"682\",\"campo\":\"discount_type\",\"etichetta\":\"Tipo coupon\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"fixed\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"},\"valori\":[{\"campo\":\"5123\",\"etichetta\":\"Percentuale\",\"valore\":\"percentage\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"5123\",\"etichetta\":\"Importo fisso\",\"valore\":\"fixed\",\"locale\":\"it\",\"ordine\":\"2\"}]},{\"campo\":{\"form\":\"682\",\"campo\":\"discount_value\",\"etichetta\":\"Valore (importo o percentuale)\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"2\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"4\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"682\",\"campo\":\"expiry_date\",\"etichetta\":\"Data di scadenza\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"7\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"5\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"1\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"682\",\"campo\":\"multiple_use\",\"etichetta\":\"Utilizzabile pi\\u00f9 volte?\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"6\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"0\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"6\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"},\"valori\":[{\"campo\":\"5126\",\"etichetta\":\"Si\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"5126\",\"etichetta\":\"No\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":\"2\"}]},{\"campo\":{\"form\":\"682\",\"campo\":\"min_level\",\"etichetta\":\"Soglia minima di spesa\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"2\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"8\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"682\",\"campo\":\"user_category\",\"etichetta\":\"Categoria utente\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"9\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"1\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"9\",\"tipo_valori\":\"0\",\"function_template\":\"manage_coupon_array_usercategory\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"682\",\"campo\":\"id\",\"etichetta\":\"id\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"682\",\"campo\":\"use_limit\",\"etichetta\":\"limite applicazione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"1\",\"default_value\":\"0\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"10\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"},\"valori\":[{\"campo\":\"5130\",\"etichetta\":\"categoria utente\",\"valore\":\"category_users\",\"locale\":\"it\",\"ordine\":\"2\"},{\"campo\":\"5130\",\"etichetta\":\"nessuno\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"5130\",\"etichetta\":\"utenti specifici\",\"valore\":\"specific_users\",\"locale\":\"it\",\"ordine\":\"3\"}]},{\"campo\":{\"form\":\"682\",\"campo\":\"users\",\"etichetta\":\"utenti\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"8\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"11\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"682\",\"campo\":\"num_repeat\",\"etichetta\":\"numero di utilizzi per utente\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"1\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"7\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}}]}");
		}
		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();

		if( $res ){
			Form::delete('manage_coupon_data');
			$database = _obj('Database');
			$database->execute('DROP TABLE coupon');
			$database->execute('DROP TABLE coupon_cart');
		}

		return $res;
	}

}



?>