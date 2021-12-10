<?php
class PagerConfig extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'pager'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'pagerLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'pager';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 
	 // COSTANTI RELATIVE ALLA CLASSE PAGER 
	const LABEL_FIELD_TABLE = 'label'; //campo contenete l'etichetta dell'attributo

	//metodo che inizializza l'oggetto a partire dalla sua etichetta (LABEL)
	public static function withLabel($label)
	{
		
		
		self::initClass();
		
		if($label){
			$database = _obj('Database');
			$data = $database->select('*',static::TABLE,static::LABEL_FIELD_TABLE."='{$label}'");
			
			if(okArray($data)){
				return static::withData($data[0]);
			}else{
				static::writeLog("nessun dato trovato nel database per l'etichetta specificata nel metodo << withLabel >>");
				return false;
			}

		}else{
			static::writeLog("etichetta in input vuota nel metodo << withLabel >>");
			return false;
		}


	}
	

	function setList($list){
		$this->list = $list;
	}

	function build(){
		require_once 'Pager.php';
		$params = $this->getParams();
		$params['itemData'] = $this->list;
		
		$GLOBALS['_pager'] = &Pager::factory($params);
		
	}




	function getParams(){
		$res = array();
		
		foreach($this->_columns as $v){
			$obj = 
			$res[$v] = $this->get($v);
		
		}
		foreach($this->_columnsLocale as $v){
			$res[$v] = $this->get($v);
		
		}
		if(!$res['delta']){
			unset($res['delta']);
		}
		unset($res['id']);
		unset($res['label']);
		unset($res['locale']);
		unset($res['pager']);
		return $res;
	}




	// OVERRIDE DEI METODI DI PAGER
	
	function getLinks(){
		if( $GLOBALS['_pager'] ){
			return $GLOBALS['_pager']->getLinks();
		}
	}

	function getPageData(){
		if( $GLOBALS['_pager'] ){
			return $GLOBALS['_pager']->getPageData();
		}
	}
		
	function numItems(){
		if( $GLOBALS['_pager'] ){
			return $GLOBALS['_pager']->numItems();
		}
	}

	function numPages(){
		if( $GLOBALS['_pager'] ){
			return $GLOBALS['_pager']->numPages();
		}
	}
}

?>