<?php
class Privalia extends Module{

	

	function install(){
		$res = parent::install();
		if( $res ){
			$database = _obj('Database');
			

			$database->execute("
				CREATE TABLE privalia_taxonomy (
				  code bigint(20) NOT NULL,
				  parent_code bigint(20) DEFAULT NULL,
				  name text,
				  path text,
				  level int(11) DEFAULT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			");
			$database->execute("
				ALTER TABLE privalia_taxonomy
				  ADD PRIMARY KEY (code);
			");


			$database->execute("
				CREATE TABLE privalia_taxonomy_attribute (
				  code varchar(100) NOT NULL,
				  description text,
				  category_code bigint(20) DEFAULT NULL,
				  label text,
				  required tinyint(1) DEFAULT '0',
				  data_type varchar(50) DEFAULT NULL,
				  type varchar(50) DEFAULT NULL,
				  recommended tinyint(1) DEFAULT '0',
				  min_value varchar(100) DEFAULT NULL,
				  max_value varchar(100) DEFAULT NULL,
				  entity_type varchar(100) DEFAULT NULL,
				  variant text,
				  values_list text
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
				
			");
			$database->execute("ALTER TABLE privalia_taxonomy_attribute ADD FOREIGN KEY (category_code) REFERENCES privalia_taxonomy (code);");


			$database->execute("
			CREATE TABLE privalia_taxonomy_attribute_value (
			  code varchar(100) NOT NULL,
			  label text,
			  valori text
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

			
			$database->execute("
				CREATE TABLE privalia_carrier (
				  id bigint(20) NOT NULL,
				  name varchar(50) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			");

			$database->execute("
			CREATE TABLE privalia_channel (
			  id bigint(20) NOT NULL,
			  name varchar(100) NOT NULL,
			  marketplaceCode varchar(100) NOT NULL,
			  marketplaceName varchar(100) NOT NULL,
			  enabled tinyint(1) NOT NULL DEFAULT '0',
			  sellerType varchar(50) NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			");

			$database->execute("
			CREATE TABLE privalia_profile (
			  id bigint(20) UNSIGNED NOT NULL,
			  taxonomy bigint(20) UNSIGNED DEFAULT NULL,
			  path_taxonomy varchar(500) DEFAULT NULL,
			  name varchar(300) DEFAULT NULL,
			  lang varchar(3) DEFAULT NULL,
			  configuration longtext
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			");

			$database->execute("ALTER TABLE privalia_profile ADD UNIQUE KEY id (id);");
			$database->execute("ALTER TABLE privalia_profile MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;");


			$database->execute("
			CREATE TABLE privalia_feed (
			  id bigint(20) UNSIGNED NOT NULL,
			  timestamp timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			  type varchar(50) DEFAULT NULL,
			  input varchar(100) DEFAULT NULL,
			  output varchar(100) DEFAULT NULL,
			  status varchar(50) DEFAULT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			");

			$database->execute("ALTER TABLE privalia_feed ADD UNIQUE KEY id (id);");

			$database->execute("ALTER TABLE privalia_feed MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;");
			

			$database->execute("
				CREATE TABLE privalia_order (
				  id_cart bigint(20) UNSIGNED NOT NULL,
				  id_privalia bigint(20) UNSIGNED NOT NULL,
				  marketplaceCode varchar(50) NOT NULL,
				  marketplaceName varchar(100) NOT NULL,
				  shopChannelId bigint(20) NOT NULL,
				  shopChannelName varchar(100) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			");


			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"privalia_shop_list\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":null,\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"935\",\"campo\":\"name\",\"etichetta\":\"Nome\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"935\",\"campo\":\"id_profile\",\"etichetta\":\"Profilo di vendita\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"1\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"4\",\"tipo_valori\":\"0\",\"function_template\":\"profiles\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"935\",\"campo\":\"id_channel\",\"etichetta\":\"Privalia channel\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"1\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"0\",\"function_template\":\"channels\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"935\",\"campo\":\"categories\",\"etichetta\":\"Categorie\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"9\",\"tipo\":\"1\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"0\",\"function_template\":\"categories\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"935\",\"campo\":\"id\",\"etichetta\":\"id\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"1\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");



		}
	}

	function uninstall(){
		$res = parent::uninstall();
		
		if( $res ){
			$database = _obj('Database');
			$database->execute('DROP TABLE privalia_profile');
			$database->execute('DROP TABLE privalia_carrier');
			$database->execute('DROP TABLE privalia_channel');
			$database->execute('DROP TABLE privalia_feed');
			$database->execute('DROP TABLE privalia_taxonomy');
			$database->execute('DROP TABLE privalia_taxonomy_attribute');
			$database->execute('DROP TABLE privalia_taxonomy_attribute_value');
			$database->execute('DROP TABLE privalia_order');
			

			Form::delete('privalia_shop_list');
		}
	}

}