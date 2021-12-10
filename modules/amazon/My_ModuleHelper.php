<?php
class My_ModuleHelper extends ModuleHelper{
	
	public function rrmdir($dir) { 
	   if (is_dir($dir)) { 
		 $objects = scandir($dir); 
		 foreach ($objects as $object) { 
		   if ($object != "." && $object != "..") { 
			 if (is_dir($dir."/".$object))
			   $this->rrmdir($dir."/".$object);
			 else
			   unlink($dir."/".$object); 
		   } 
		 }
		 rmdir($dir); 
	   } 
	 }


	function createToken(){
		$length = 10;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}

		Marion::setConfig('amazon_module','token',$randomString);
		Marion::refresh_config();
		return false;
	}
	

	function install(){
		
		$res = parent::install();
		if( $res ){
			
			$database = _obj('Database');
			$database->execute("
				CREATE TABLE amazon_profile (
				  id bigint(20) UNSIGNED NOT NULL,
				  name varchar(100) DEFAULT NULL,
				  store bigint(20) DEFAULT NULL,
				  mapping text
				)
			");
			$database->execute("
				ALTER TABLE amazon_profile
				  ADD PRIMARY KEY (id),
				  ADD UNIQUE KEY id (id);
			");

			$database->execute("
				ALTER TABLE amazon_profile
				  MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
			
			");

			$database->execute("
				CREATE TABLE amazon_profile_marketplace (
				  id bigint(20) UNSIGNED NOT NULL,
				  id_profile bigint(20) UNSIGNED NOT NULL,
				  data text NOT NULL,
				  market varchar(100) NOT NULL
				);
			
			");

			$database->execute(
				"ALTER TABLE amazon_profile_marketplace ADD UNIQUE KEY id (id);"	
			);

			$database->execute("
				ALTER TABLE amazon_profile_marketplace
				  MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
			");

			////////////
			$database->execute("
				CREATE TABLE amazon_store (
				  name varchar(100) DEFAULT NULL,
				  merchantId varchar(200) DEFAULT NULL,
				  id bigint(20) UNSIGNED NOT NULL,
				  marketplace text,
				  token varchar(300) DEFAULT NULL,
				  statusPaid varchar(100) DEFAULT NULL,
				  statusSent varchar(100) DEFAULT NULL,
				  categories text,
				  mapping_profile text
				)
			
			");

			$database->execute("
				ALTER TABLE amazon_store
				  ADD PRIMARY KEY (id),
				  ADD UNIQUE KEY id (id);

			
			");

			$database->execute("
				ALTER TABLE amazon_store MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
			
			");

			////////////
			$database->execute("
				CREATE TABLE amazon_upload (
				  id bigint(20) UNSIGNED NOT NULL,
				  last_operation varchar(50) DEFAULT NULL,
				  id_store bigint(20) UNSIGNED DEFAULT NULL,
				  type varchar(100) DEFAULT NULL,
				  finished tinyint(1) DEFAULT '0'
				)
			
			");

			$database->execute("
				ALTER TABLE amazon_upload ADD UNIQUE KEY id (id);
			
			");

			$database->execute("
				ALTER TABLE amazon_upload MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
			
			");



			$database->execute("
				CREATE TABLE amazon_asin (
				  asin varchar(50) NOT NULL,
				  asin_parent varchar(50) NULL,
				  type varchar(50) NOT NULL,
				  value varchar(50) NOT NULL,
				  marketplace varchar(50) NOT NULL,
				  ProductTypeName varchar(50) DEFAULT NULL
				) 
			");

			$database->execute("ALTER TABLE amazon_asin ADD UNIQUE( asin, type, value, marketplace);");


			$database->execute("
				CREATE TABLE amazon_order (
				  id_marion bigint(20) UNSIGNED NOT NULL,
				  id_amazon varchar(100) NOT NULL,
				  date datetime DEFAULT NULL,
				  market varchar(100) DEFAULT NULL,
				  id_account bigint(20) UNSIGNED DEFAULT NULL
				);
			
			");


			/*$database->execute("
				CREATE TABLE amazon_product_info (
				  id_product bigint(20) UNSIGNED NOT NULL,
				  disable tinyint(1) NOT NULL DEFAULT '0'
				)		
			");*/


			$database->execute("
				CREATE TABLE amazon_carrier (
				  id_amazon varchar(100) NOT NULL,
				  id_marion bigint(20) UNSIGNED NOT NULL,
				  id_store bigint(20) UNSIGNED NOT NULL
				) 
			
			");

			$database->execute(
				"
				CREATE TABLE amazon_carrier_exit (
				  id_store bigint(20) UNSIGNED NOT NULL,
				  id_marion bigint(20) UNSIGNED NOT NULL,
				  id_amazon varchar(100) NOT NULL,
				  market varchar(100) DEFAULT NULL
				) 
				"
			);

			$database->execute(
				'CREATE TABLE amazon_feed (
				  FeedSubmissionId bigint(20) DEFAULT NULL,
				  FeedType varchar(50) DEFAULT NULL,
				  FeedProcessingStatus varchar(50) DEFAULT NULL,
				  timestamp timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				  marketplace varchar(50) DEFAULT NULL,
				  id_store bigint(20) UNSIGNED NOT NULL,
				  id_upload bigint(20) UNSIGNED NOT NULL
				)'	
			);
			
			$database->execute(
			"
			CREATE TABLE amazon_product (
			  id bigint(20) UNSIGNED NOT NULL,
			  id_account bigint(20) UNSIGNED DEFAULT NULL,
			  marketplace varchar(50) DEFAULT NULL,
			  id_product bigint(20) UNSIGNED NOT NULL,
			  parent_description tinyint(1) DEFAULT '0',
			  new_product tinyint(1) DEFAULT '0',
			  price double DEFAULT NULL,
			  disable_sync tinyint(1) DEFAULT '0',
			  bullet_1 varchar(200) DEFAULT NULL,
			  bullet_2 varchar(200) DEFAULT NULL,
			  bullet_3 varchar(200) DEFAULT NULL
			);
			"	
			);

			$database->execute("
				ALTER TABLE amazon_product ADD UNIQUE KEY id (id);
				
			");
			
			$database->execute("
				ALTER TABLE amazon_product MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
			");


			$database->execute(
				"
					CREATE TABLE amazon_report (
					  ReportRequestId varchar(100) DEFAULT NULL,
					  ReportType varchar(100) DEFAULT NULL,
					  timestamp timestamp NULL DEFAULT CURRENT_TIMESTAMP,
					  marketplace varchar(50) DEFAULT NULL,
					  id_store bigint(20) UNSIGNED DEFAULT NULL,
					  id_upload bigint(20) UNSIGNED DEFAULT NULL,
					  ReportProcessingStatus varchar(50) DEFAULT NULL
					) ;
				"	
			);

			$database->execute(
				"CREATE TABLE amazon_order_item (
					  id_order varchar(100) DEFAULT NULL,
					  product varchar(100) DEFAULT NULL,
					  quantity int(11) DEFAULT NULL,
					  price double DEFAULT NULL,
					  amazon_item_id varchar(100) DEFAULT NULL
					);"
			);

			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"amazon_store\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"457\",\"campo\":\"name\",\"etichetta\":\"form_buy_name\",\"gettext\":\"1\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"2\",\"lunghezzamax\":\"80\",\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"457\",\"campo\":\"marketplace\",\"etichetta\":\"marketplace\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"4\",\"tipo_valori\":\"0\",\"function_template\":\"array_amazon_marketplace\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"457\",\"campo\":\"id\",\"etichetta\":\"codice\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"1\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"457\",\"campo\":\"merchantId\",\"etichetta\":\"merchantId\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"457\",\"campo\":\"token\",\"etichetta\":\"token\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"5\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"457\",\"campo\":\"statusPaid\",\"etichetta\":\"mappatura stato ordine pagato\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"6\",\"tipo_valori\":\"0\",\"function_template\":\"array_status_mapping_amazon\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"457\",\"campo\":\"statusSent\",\"etichetta\":\"mappatura stato ordine spedito\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"7\",\"tipo_valori\":\"0\",\"function_template\":\"array_status_mapping_amazon\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"457\",\"campo\":\"categories\",\"etichetta\":\"categorie\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"8\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"8\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}}]}");
			
			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"amazon_profile\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"569\",\"campo\":\"id\",\"etichetta\":\"codice\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"1\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"569\",\"campo\":\"name\",\"etichetta\":\"nome\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"2\",\"lunghezzamax\":\"80\",\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"569\",\"campo\":\"category\",\"etichetta\":\"categoria amazon\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"0\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"0\",\"function_template\":\"array_amazon_categories\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"569\",\"campo\":\"store\",\"etichetta\":\"store\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"0\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"0\",\"function_template\":\"array_amazon_stores\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}}]}");



			Form::import("{\"form\":{\"gruppo\":\"0\",\"nome\":\"amazon_profile_clothing\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"568\",\"campo\":\"variationTheme\",\"etichetta\":\"tema variazioni\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_variationTheme\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"ClothingType\",\"etichetta\":\"tipologia di abbigliamento\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_ClothingType\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"Department\",\"etichetta\":\"Reparto\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_choice\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"OuterMaterial\",\"etichetta\":\"Materiale esterno\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"4\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_choice\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"ColorAttribute\",\"etichetta\":\"colore\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"11\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_attribute\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"SizeAttribute\",\"etichetta\":\"Attributo taglia\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"13\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_attribute\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"MaterialCompositionAttribute\",\"etichetta\":\"composizione materiale attributo\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"15\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_attribute\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"OuterMaterialAttribute\",\"etichetta\":\"materiale esterno attributo\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"17\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_attribute\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"OuterMaterialDefault\",\"etichetta\":\"materiale esterno default\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"19\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_OuterMaterialDefault\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"DepartmentDefault\",\"etichetta\":\"materiale esterno default\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"20\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_Department\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"Size\",\"etichetta\":\"Taglia\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"5\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_choice2\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"Color\",\"etichetta\":\"Colore\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"6\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_choice2\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"DepartmentAttribute\",\"etichetta\":\"reparto attributo\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"21\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_attribute\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"MaterialComposition\",\"etichetta\":\"Composizione Materiale\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"8\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_choice\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"MaterialCompositionFeature\",\"etichetta\":\"composizione materiale caratteristica\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"16\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_feature\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"OuterMaterialFeature\",\"etichetta\":\"materiale esterno caratteristica\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"18\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_feature\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"DepartmentFeature\",\"etichetta\":\"reparto caratteristica\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"22\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_feature\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"SizeFeature\",\"etichetta\":\"caratteristica taglia\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"14\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_feature\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"ColorFeature\",\"etichetta\":\"colore caratteristica\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"12\",\"tipo_valori\":\"0\",\"function_template\":\"array_profile_clothing_feature\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"OuterMaterialValue\",\"etichetta\":\"Materiale esterno valore fisso\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"10\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"MaterialCompositionValue\",\"etichetta\":\"Composizione materiale valore fisso\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"9\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"DepartmentValue\",\"etichetta\":\"Reparto valore fisso\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"7\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"SizeValue\",\"etichetta\":\"Taglia valore fisso\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"7\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}},{\"campo\":{\"form\":\"568\",\"campo\":\"ColorValue\",\"etichetta\":\"Colore valore fisso\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"7\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}}]}");
			$this->rrmdir('xml_upload');
			$this->rrmdir('reports');
			$this->rrmdir('responses');
			mkdir('xml_upload');
			mkdir('reports');
			mkdir('responses');

			$chmod = "0777";
			chmod('xml_upload', octdec($chmod));
			chmod('reports', octdec($chmod));
			chmod('responses', octdec($chmod));
			

			$this->createToken();

			$obj = PaymentMethod::withCode('AMAZON');
			if( !is_object($obj) ){
				$payment = PaymentMethod::create();
				$payment->code = 'AMAZON';

				$data = array(
					'name' => 'AMAZON',
					'visibility' => 0,
					'price' => 0,
					'pecentage' => 0,
					'orderView' => 10,
				);
				$payment->set($data);
				foreach(Marion::getConfig('locale','supportati') as $lo){
					$payment->setData($data,$lo);
				}
				$payment->save();
			}

		}
		return $res;
	}





	function uninstall(){
		$res = parent::uninstall();
		if( $res ){

			Form::delete('amazon_profile');
			Form::delete('amazon_profile_clothing');
			Form::delete('amazon_store');

			$obj = PaymentMethod::withCode('AMAZON');
			if( is_object($obj) ){
				$obj->delete();
			}	
			$tables = array('amazon_profile','amazon_store','amazon_upload','amazon_asin','amazon_order','amazon_order','amazon_carrier','amazon_feed','amazon_product','amazon_report','amazon_carrier_exit','amazon_order_item');
			
			$database = _obj('Database');
			foreach($tables as $table){
				$database->execute("DROP TABLE {$table}");
			}

			$this->rrmdir('xml_upload');
			$this->rrmdir('responses');
			$this->rrmdir('reports');
		}
		
		return $res;
	}

}



?>