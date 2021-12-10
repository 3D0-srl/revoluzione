<?php
class My_ModuleHelper extends ModuleHelper{

	

	function install(){
		$res = parent::install();
		if( $res ){
			$database = _obj('Database');
			$database->execute("
				CREATE TABLE bal_email_builder (
				  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				  UserId bigint(20),
				  name varchar(30) NOT NULL,
				  content longtext NOT NULL,
				  html longtext NOT NULL,
				  PRIMARY KEY (id),
				  UNIQUE KEY id (id)
				);
			");

			$data_form="{\"form\":{\"gruppo\":\"5\",\"nome\":\"mailman_list\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"507\",\"campo\":\"id\",\"etichetta\":\"codice\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"1\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"507\",\"campo\":\"domain\",\"etichetta\":\"dominio\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"4\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"507\",\"campo\":\"password\",\"etichetta\":\"password\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"5\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"507\",\"campo\":\"email\",\"etichetta\":\"email\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"10\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"6\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"507\",\"campo\":\"protocol\",\"etichetta\":\"protocollo\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"4\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"http\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"7\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":\"strtolower\",\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"507\",\"campo\":\"notifyuser\",\"etichetta\":\"notifica l'utente\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":\"0\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"9\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null},\"valori\":[{\"campo\":\"3519\",\"etichetta\":\"NO\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"3519\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"2\"}]},{\"campo\":{\"form\":\"507\",\"campo\":\"send_welcome_msg_to_this_batch\",\"etichetta\":\"invio messaggio di benvenuto\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":\"0\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"11\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null},\"valori\":[{\"campo\":\"3520\",\"etichetta\":\"NO\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"3520\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"2\"}]},{\"campo\":{\"form\":\"507\",\"campo\":\"notifyowner\",\"etichetta\":\"notifica al propetario\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":\"0\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"10\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null},\"valori\":[{\"campo\":\"3521\",\"etichetta\":\"NO\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"3521\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"2\"}]},{\"campo\":{\"form\":\"507\",\"campo\":\"debugg\",\"etichetta\":\"modalit\\u00e0 debugg\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":\"0\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"12\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null},\"valori\":[{\"campo\":\"3522\",\"etichetta\":\"NO\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"3522\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"2\"}]},{\"campo\":{\"form\":\"507\",\"campo\":\"list_name\",\"etichetta\":\"nome lista\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"507\",\"campo\":\"default_list\",\"etichetta\":\"lista principale\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"13\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"2\",\"value_ifisnull\":\"0\"},\"valori\":[{\"campo\":\"3524\",\"etichetta\":\"NO\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"3524\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"}]},{\"campo\":{\"form\":\"507\",\"campo\":\"locale\",\"etichetta\":\"linguaggio lista\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"it\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"8\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":\"2\",\"pre_function\":\"2\",\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"507\",\"campo\":\"visibility\",\"etichetta\":\"visibile\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"14\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"2\",\"value_ifisnull\":\"0\"},\"valori\":[{\"campo\":\"3526\",\"etichetta\":\"NO\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"3526\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"2\"}]},{\"campo\":{\"form\":\"507\",\"campo\":\"list_name_view\",\"etichetta\":\"nome lista\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}}]}";
		
		
		
			Form::import($data_form);

			$data_form = "{\"form\":{\"gruppo\":\"7\",\"nome\":\"mailman_action\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"165\",\"campo\":\"email\",\"etichetta\":\"email\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"10\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"165\",\"campo\":\"action\",\"etichetta\":\"action\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":\"subscribe\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null},\"valori\":[{\"campo\":\"1009\",\"etichetta\":\"subscribe\",\"valore\":\"subscribe\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"1009\",\"etichetta\":\"unsubscribe\",\"valore\":\"unsubscribe\",\"locale\":\"it\",\"ordine\":\"2\"}]}]}";

			Form::import($data_form);
			$data_form = "{\"form\":{\"gruppo\":\"7\",\"nome\":\"mailman_conf\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"163\",\"campo\":\"confirm_subscribe\",\"etichetta\":\"conferma iscrizione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"6\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null},\"valori\":[{\"campo\":\"989\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"989\",\"etichetta\":\"NO\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":\"2\"}]},{\"campo\":{\"form\":\"163\",\"campo\":\"confirm_unsubscribe\",\"etichetta\":\"conferma cancellazione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"6\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null},\"valori\":[{\"campo\":\"990\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"990\",\"etichetta\":\"NO\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":\"2\"}]},{\"campo\":{\"form\":\"163\",\"campo\":\"form_user\",\"etichetta\":\"opzione newsletter in form registrazione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"6\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"4\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null},\"valori\":[{\"campo\":\"991\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"991\",\"etichetta\":\"NO\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":\"2\"}]},{\"campo\":{\"form\":\"163\",\"campo\":\"form_user_subscribe_type\",\"etichetta\":\"tipo di iscrizione newsletter in registrazione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"1\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"5\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null},\"valori\":[{\"campo\":\"992\",\"etichetta\":\"Newsletter principale\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"992\",\"etichetta\":\"Tutte le newsletter online\",\"valore\":\"2\",\"locale\":\"it\",\"ordine\":\"2\"},{\"campo\":\"992\",\"etichetta\":\"Consenti la scelta all'utente\",\"valore\":\"3\",\"locale\":\"it\",\"ordine\":\"3\"}]},{\"campo\":{\"form\":\"163\",\"campo\":\"email\",\"etichetta\":\"email\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"10\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}}]}";
			
			Form::import($data_form);

			$database = _obj('Database');
			$database->execute(
			  "CREATE TABLE IF NOT EXISTS mailman_list (
			  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  domain varchar(200) DEFAULT NULL,
			  password varchar(50) DEFAULT NULL,
			  email varchar(100) DEFAULT NULL,
			  protocol varchar(10) DEFAULT 'http',
			  notifyuser tinyint(1) unsigned DEFAULT '0',
			  send_welcome_msg_to_this_batch tinyint(1) unsigned DEFAULT '0',
			  notifyowner tinyint(1) unsigned DEFAULT '0',
			  debugg tinyint(1) unsigned DEFAULT '0',
			  list_name varchar(100) DEFAULT NULL,
			  default_list tinyint(1) unsigned DEFAULT '0',
			  visibility tinyint(1) DEFAULT '1',
			  locale varchar(2) DEFAULT 'it',
			  PRIMARY KEY (id),
			  UNIQUE KEY id (id),
			  UNIQUE KEY id_2 (id))");

			$database->execute("
				CREATE TABLE IF NOT EXISTS mailman_listLocale (
					  mailman_list bigint(20) DEFAULT NULL,
					  list_name_view varchar(200) DEFAULT NULL,
					  locale varchar(3) DEFAULT NULL
					);"
			);

			$database->execute(
				  "CREATE TABLE IF NOT EXISTS mailman_subscribe (
				  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  auth varchar(10) DEFAULT NULL,
				  email varchar(100) DEFAULT NULL,
				  dateInsert datetime DEFAULT NULL,
				  ip varchar(20) DEFAULT NULL,
				  used tinyint(1) DEFAULT '0',
				  list bigint(20) DEFAULT NULL,
				  country varchar(5) DEFAULT NULL,
				  region varchar(200) DEFAULT NULL,
				  latitude double DEFAULT NULL,
				  longitude double DEFAULT NULL,
				  city varchar(200) DEFAULT NULL,
				  postalCode varchar(15) DEFAULT NULL
				  UNIQUE KEY id (id)
				)");

			$database->execute("
				CREATE TABLE mailMarketingCampaign (
				  id bigint(20) UNSIGNED NOT NULL,
				  name varchar(300) DEFAULT NULL,
				  name_view varchar(300) DEFAULT NULL,
				  list bigint(20) UNSIGNED DEFAULT NULL,
				  mail_template bigint(20) UNSIGNED DEFAULT NULL,
				  sent tinyint(1) DEFAULT '0',
				  content longtext,
				  cron tinyint(1) DEFAULT NULL,
				  dateStart date DEFAULT NULL,
				  hourStart time DEFAULT NULL,
				  tot_users int(11) DEFAULT NULL,
				  date_sent datetime DEFAULT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			");

			$database->execute("
				ALTER TABLE mailMarketingCampaign ADD UNIQUE KEY id (id);

			");

			$database->execute("
				ALTER TABLE mailMarketingCampaign MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
			");



			$database->execute("
				CREATE TABLE mail_marketing_link_mail (
				  id bigint(20) UNSIGNED NOT NULL,
				  id_campaign bigint(20) UNSIGNED NOT NULL,
				  link varchar(500) DEFAULT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			
			");


			$database->execute("
				ALTER TABLE mail_marketing_link_mail
				  ADD UNIQUE KEY id (id);
			");

			$database->execute("ALTER TABLE mail_marketing_link_mail
				  MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
			");


			$database->execute("
				CREATE TABLE mail_marketing_link_mail_click (
				  id_campaign bigint(20) UNSIGNED NOT NULL,
				  ip varchar(50) DEFAULT NULL,
				  id_link bigint(20) UNSIGNED DEFAULT NULL
				)");

			$database->eceute("
				ALTER TABLE mail_marketing_link_mail_click
				ADD UNIQUE KEY `id_campaign` (id_campaign,ip,id_link);
			");
			
			$settings = array(
					
					0 =>  array(
						'gruppo' => 'module_mailman',
						'etichetta' => 'mailman',
						'chiave' => 'email',
						'valore' =>  "",
						'descrizione' => '',
						'ordine' => '',
					),
					1 => array(
						'gruppo' => 'module_mailman',
						'etichetta' => 'mailman',
						'chiave' => 'confirm_subscribe',
						'valore' =>  "1",
						'descrizione' => '',
						'ordine' => '',
					),
					2 => array(
						'gruppo' => 'module_mailman',
						'etichetta' => 'mailman',
						'chiave' => 'confirm_unsubscribe',
						'valore' =>  "1",
						'descrizione' => '',
						'ordine' => '',
					),
					3 => array(
						'gruppo' => 'module_mailman',
						'etichetta' => 'mailman',
						'chiave' => 'form_user',
						'valore' =>  "1",
						'descrizione' => '',
						'ordine' => '',
					),
					4 => array(
						'gruppo' => 'module_mailman',
						'etichetta' => 'mailman',
						'chiave' => 'form_user_subscribe_type',
						'valore' =>  "1",
						'descrizione' => '',
						'ordine' => '',
					),
					
			);

			$database->update('permission',"label='newsletter'",array('active' => 1));
			foreach($settings as $k => $v){
				$database->insert('setting',$v);

			}
			$cache = _obj('Cache');
			if( $cache->isExisting("setting") ){
				$cache->delete('setting');
			}
		}
		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();
		if( $res ){
			$database = _obj('Database');
			$database->execute("DROP TABLE bal_email_builder");
			$database->execute("DROP TABLE mailMarketingCampaign");
			$database->execute("DROP TABLE mail_marketing_link_mail_click");
			$database->execute("DROP TABLE mail_marketing_link_mail");
			Form::delete('mailman_list');
			Form::delete('mailman_action');
			Form::delete('mailman_conf');
		
			$database->delete('setting',"gruppo='module_mailman'");
			$database->update('permission',"label='newsletter'",array('active' => 0));
			$cache = _obj('Cache');
			if( $cache->isExisting("setting") ){
				$cache->delete('setting');
			}
		}
		return $res;
	}

}



?>