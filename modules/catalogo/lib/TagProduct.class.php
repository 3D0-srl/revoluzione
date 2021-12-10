<?php
use Marion\Core\{Base,Marion};
class TagProduct extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'tagProduct'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'tagProductLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'id_tagProduct';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 



	function getUrl(){
		if( isMultilocale()){
			return _MARION_BASE_URL_.$GLOBALS['activelocale']."/catalog/tag/".$this->label.".htm";
		}else{
			return _MARION_BASE_URL_."catalog/tag/".$this->label.".htm";
		}
		
	}



	function getProductIds(){
		$toreturn = array();
		$database = Marion::getDB();
		$ids = $database->select('*','productTagComposition',"id_tag = {$this->id}");
		
		if( okArray($ids) ){
			foreach($ids as $v){
				$toreturn[] = $v['id_product'];
			}
		}
		return $toreturn;
	}

	function afterSave(){
		parent::afterSave();
		
		//prendo tutti i prodotti che hanno questo tag e svuoto la tabella di ricerca
		$list = $this->getProductIds();
		if( okArray($list) ){
			$database = Marion::getDB();
			foreach($list as $v){
				$database->insert('product_search_changed',array('id_product' => $v));
			}
		}
		
	}
		
}

?>