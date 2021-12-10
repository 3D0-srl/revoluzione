<?php
use Marion\Core\Module;
use Marion\Core\Marion;
use Marion\Core\Form;
class AdvancedCatalog extends Module{

	

	function install(){
			
		$res = parent::install();
		
		if( $res ){
			$database = Marion::getDB();
			$database->execute("
				CREATE TABLE advancedCatalogAttributeExplode (
				  id_product bigint(20) UNSIGNED NOT NULL,
				  id_attribute bigint(20) UNSIGNED NOT NULL
				);
			");
			$database->execute("
				CREATE TABLE advancedCatalogAttributePreview (
				  id_product bigint(20) UNSIGNED NOT NULL,
				  id_attribute bigint(20) UNSIGNED NOT NULL
				);
			");

			$database->execute("
				CREATE TABLE advancedCatalogSplitProduct (
				  id_product bigint(20) UNSIGNED DEFAULT NULL,
				  parent_product bigint(20) UNSIGNED DEFAULT NULL,
				  value bigint(20) UNSIGNED DEFAULT NULL,
				  images varchar(200) DEFAULT NULL
				);
			");

			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"advanced_catalog_split_form\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":null,\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"990\",\"campo\":\"explode_product_catalog_attribute\",\"etichetta\":\"attributo di esplosione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":\"attributes\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"990\",\"campo\":\"preview_product_catalog_attribute\",\"etichetta\":\"attributo di anteprima\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":\"attributes\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");
		}
		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();
		if( $res ){
			$database = Marion::getDB();
			$database->execute("DROP TABLE advancedCatalogAttributeExplode");
			$database->execute("DROP TABLE advancedCatalogAttributePreview");
			$database->execute("DROP TABLE advancedCatalogSplitProduct");
			Form::delete('advanced_catalog_split_form');
		}
		return $res;
	}

}



?>