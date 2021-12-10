<?php
class Sincro extends Module{

	

	function install(){
		$res = parent::install();
		if( $res ){
			$database = _obj('Database');
			

			$database->execute("
				CREATE TABLE sincro_reports (
				  id bigint(20) UNSIGNED NOT NULL,
				  type varchar(100) NOT NULL,
				  start timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  end timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  duration float NOT NULL,
				  reports text NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			");
			$database->execute("
				ALTER TABLE sincro_reports ADD UNIQUE KEY id (id);
			");
			$database->execute("ALTER TABLE sincro_reports MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1; ");

			$database->execute("
				CREATE TABLE sincro_magazzino_prodotto (
				  id_magazzino bigint(20) UNSIGNED NOT NULL,
				  id_product bigint(20) UNSIGNED NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
				");
			$database->execute("
			CREATE TABLE sincro_magazzino (
				  id bigint(20) UNSIGNED NOT NULL,
				  nome varchar(150) NOT NULL,
				  etichetta varchar(100) NOT NULL,
				  email varchar(100) DEFAULT NULL,
				  telefono varchar(100) DEFAULT NULL,
				  luogo varchar(100) DEFAULT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			");
			 $database->execute(" ALTER TABLE sincro_magazzino ADD UNIQUE KEY id (id);");


			$database->execute("ALTER TABLE sincro_magazzino MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;");

			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"sincro_magazzino\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":null,\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"903\",\"campo\":\"id\",\"etichetta\":\"id\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"903\",\"campo\":\"nome\",\"etichetta\":\"nome\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"903\",\"campo\":\"etichetta\",\"etichetta\":\"etichetta\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"903\",\"campo\":\"email\",\"etichetta\":\"email\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"903\",\"campo\":\"telefono\",\"etichetta\":\"telefono\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"4\",\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"903\",\"campo\":\"luogo\",\"etichetta\":\"luogo\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"5\",\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");
			

		}
		return $res;
	}



	function uninstall(){
		
		$res = parent::uninstall();
		if( $res ){
			$database = _obj('Database');
			$database->execute('DROP TABLE sincro_reports');
			$database->execute('DROP TABLE sincro_magazzino_prodotto');
			$database->execute('DROP TABLE sincro_magazzino');

			Form::delete('sincro_magazzino');
		}
		return $res;
	}





}



?>