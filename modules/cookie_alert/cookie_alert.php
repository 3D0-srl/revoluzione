<?php
use Marion\Core\Module;
use Marion\Core\Form;
use Marion\Core\Marion;
class CookieAlert extends Module{
	

	function install(){
		$res = parent::install();

		$data_form="{\"form\":{\"gruppo\":\"7\",\"nome\":\"module_cookie_alert\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"685\",\"campo\":\"autoAcceptCookiePolicy\",\"etichetta\":\"auto accettazione policy\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"8\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"2\",\"value_ifisnull\":\"0\",\"dropzone\":\"0\"},\"valori\":[{\"campo\":\"5138\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"}]},{\"campo\":{\"form\":\"685\",\"campo\":\"popupPosition\",\"etichetta\":\"posizione alert\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"bottom\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"7\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"},\"valori\":[{\"campo\":\"5139\",\"etichetta\":\"TOP\",\"valore\":\"top\",\"locale\":\"it\",\"ordine\":\"1\"},{\"campo\":\"5139\",\"etichetta\":\"BOTTOM\",\"valore\":\"bottom\",\"locale\":\"it\",\"ordine\":\"2\"},{\"campo\":\"5139\",\"etichetta\":\"BOTTOM-RIGHT\",\"valore\":\"bottomright\",\"locale\":\"it\",\"ordine\":\"3\"},{\"campo\":\"5139\",\"etichetta\":\"BOTTOM-LEFT\",\"valore\":\"bottomleft\",\"locale\":\"it\",\"ordine\":\"4\"},{\"campo\":\"5139\",\"etichetta\":\"BLOCK\",\"valore\":\"block\",\"locale\":\"it\",\"ordine\":\"5\"},{\"campo\":\"5139\",\"etichetta\":\"FIXED-TOP\",\"valore\":\"fixedtop\",\"locale\":\"it\",\"ordine\":\"6\"}]},{\"campo\":{\"form\":\"685\",\"campo\":\"popupTitle\",\"etichetta\":\"titolo\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"685\",\"campo\":\"popupText\",\"etichetta\":\"testo alert\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"8\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"4\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"685\",\"campo\":\"buttonLearnmoreTitle\",\"etichetta\":\"testo button leggi altro\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"3\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"685\",\"campo\":\"buttonContinueTitle\",\"etichetta\":\"testo button continua\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"4\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"685\",\"campo\":\"buttonLearnmoreOpenInNewWindow\",\"etichetta\":\"apri policy in un'altra finestra\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"9\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"2\",\"value_ifisnull\":\"0\",\"dropzone\":\"0\"},\"valori\":[{\"campo\":\"5144\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"}]},{\"campo\":{\"form\":\"685\",\"campo\":\"agreementExpiresInDays\",\"etichetta\":\"numero giorni scadenza\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"1\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"6\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"685\",\"campo\":\"urlPolicy\",\"etichetta\":\"url informativa\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"5\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}},{\"campo\":{\"form\":\"685\",\"campo\":\"styleCompact\",\"etichetta\":\"stile compatto\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"8\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"2\",\"value_ifisnull\":\"0\",\"dropzone\":\"0\"},\"valori\":[{\"campo\":\"5147\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"}]}]}";
		Form::import($data_form);

		$database = Marion::getDB();
		$database->execute("
			CREATE TABLE IF NOT EXISTS cookieAlert (
			  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  agreementExpiresInDays int(11) DEFAULT NULL,
			  popupPosition varchar(20) DEFAULT NULL,
			  styleCompact tinyint(1) DEFAULT NULL,
			  autoAcceptCookiePolicy tinyint(1) DEFAULT NULL,
			  buttonLearnmoreOpenInNewWindow tinyint(1) DEFAULT NULL,
			  UNIQUE KEY id (id)
			);");

		$database->execute("
			CREATE TABLE IF NOT EXISTS cookieAlertLocale (
			  cookieAlert bigint(20) NOT NULL,
			  popupTitle varchar(100) DEFAULT NULL,
			  popupText tinytext,
			  buttonContinueTitle varchar(100) DEFAULT NULL,
			  buttonLearnmoreTitle text,
			  urlPolicy varchar(200) DEFAULT NULL,
			  locale varchar(10) NOT NULL
			);");

		$database->execute("
			INSERT INTO cookieAlert (id, agreementExpiresInDays, popupPosition, styleCompact, autoAcceptCookiePolicy, buttonLearnmoreOpenInNewWindow) VALUES (1, 360, 'bottom', 0, 0, 0);
		");

		$database->execute("
			INSERT INTO cookieAlertLocale (cookieAlert, popupTitle, popupText, buttonContinueTitle, buttonLearnmoreTitle, urlPolicy, locale) VALUES (1, 'Accettazione cookie', '<p>Utilizziamo i cookie per migliorare l\'esperienza di navigazione e dei nostri servizi. Continuando con la navigazione se ne accetta l\'uso.</p>\r\n', 'continua', 'leggi altro', '/p/info_cookie.htm', 'it');
		");

		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();
		$database = Marion::getDB();
		$database->execute("DROP table cookieAlert");
		$database->execute("DROP table cookieAlertLocale");
		Form::delete('module_cookie_alert');
		return $res;
	}

}



?>