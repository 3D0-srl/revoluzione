<?php
namespace Marion\Entities;
use Marion\Core\Base;
use Marion\Core\Marion;
class UserCategory extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'userCategory'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'userCategoryLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'userCategory';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica

	// COSTANTI RELATIVE ALLA CLASSE ATTRIBUTO 
	const LABEL_FIELD_TABLE = 'label'; //campo contenete l'etichetta dell'attributo
	
	
	//ERRORI
	const ERROR_LABEL_DUPLICATE = "label_duplicate";
	const ERROR_LABEL_EMPTY = "label_empty";
	const ERROR_CATEGORY_LOCKED = "category_locked";

	

	//metodo che inizializza l'oggetto a partire dalla sua etichetta (LABEL)
	public static function withLabel($label)
	{
		
		
		self::initClass();
		
		if($label){
			$database = Marion::getDB();
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
	
	


	function getLabel(){
		$field_label = STATIC::LABEL_FIELD_TABLE;
		return $this->$field_label;
	}

	/***************************************************** OVERRIDE METODI DELLA CLASSE Base**************************************************************/
	public function checkSave(){
		return true;
		//controllo se la label è settata e che non ci siano duplicati
		if( $this->label ){
			
			$query = self::prepareQuery()
					->where('label', $this->getLabel());
			if( $this->hasId() ){
				$query = $query->where('id', $this->getId(),'<>');
			}
			$check = $query->get();
				
			if( count($check) > 0) return STATIC::ERROR_LABEL_DUPLICATE;
			
			return true;
		}else{
			return STATIC::ERROR_LABEL_EMPTY;	
		}
	} 

	public function beforeSave(){
		if( !$this->locked) $this->locked = 0;
	}


	public function delete(){
		if($this->locked){
			return STATIC::ERROR_CATEGORY_LOCKED;
		}
		return parent::delete();
	}

	public function isLocked(){
		if($this->locked){
			return true;
		}
		return false;

	}
	


}

?>