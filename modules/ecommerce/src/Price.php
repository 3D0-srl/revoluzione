<?php
namespace Shop;
use Marion\Core\Base;
use Marion\Core\Marion;
class Price extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'price'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica

	// COSTANTI RELATIVE ALLA CLASSE PRICE 
	const LABEL_FIELD_TABLE = 'label'; //campo contenete l'etichetta dell'attributo
	

	//ERRORI
	const ERROR_PRICE_DEFAULT_DUPLICATE = "default_price_duplicate";
	const ERROR_PRICE_BARRED_DUPLICATE = "barred_price_duplicate";
	const ERROR_PRICE_LIST_DUPLICATE = "list_price_duplicate";
	const ERROR_PRODUCT_EMPTY = "product_field_empty";
	
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
	


	//metodo che restituisce la label del prezzo
	function getLabel(){
		$field_label = STATIC::LABEL_FIELD_TABLE;
		return $this->$field_label;
	}
	

	//controlla se il prezzo è valido ovvero se la data corrente è maggiore della data di inizio e minore della data di fine
	function isActive(){
		$currentDate = strtotime(date('Y-m-d'));
		if($this->dateStart){
			if(strtotime($this->dateStart) > $currentDate){
				return false;
			}
		}
		if($this->dateEnd){
			if(strtotime($this->dateEnd) < $currentDate){
				return false;
			}
		}
		return true;
	}

	//prende il valore del prezzo
	function getValue(){
		$value = round($this->value,2);
		Marion::do_action('get_value_price',array(&$this,&$value));
		return $value;
	}


	//prende il valore del prezzo formattato
	function getFormattedValue(){
		return number_format($this->getValue(), 2, ',', '');
	}
	/***************************************************** OVERRIDE METODI DELLA CLASSE Base**************************************************************/
	public function checkSave(){
			//controllo l'esistenza del campo prodotto nel prezzo
			if( $this->product ){
				
				//controllo l'unicità del prezzo di default del prodotto
				$check = $this->checkUniquePriceDefault();
				
				if($check != 1){
					return $check;
				}

				//controllo l'unicità del prezzo di listino del prodotto
				$check = $this->checkUniqueBarredPrice();
				
				if($check != 1){
					return $check;
				}

				//controllo l'unicità dei prezzi di listino
				$check = $this->checkUniqueListPrice();
				
				if($check != 1){
					return $check;
				}
			
				return true;

			}else{
				return STATIC::ERROR_PRODUCT_EMPTY;	

			}
		
	}


	public function checkUniquePriceDefault(){
		//controllo unicità del prezzo DEFAULT e dell'esistenza del campo prodotto nel prezzo
		if( $this->getLabel() == 'default'){
			$query = self::prepareQuery()
					->where('label','default')
					->where('product',$this->product);
			
			if($this->hasId() ){
				$query->where('id',$this->getId(),'<>');

			}
			
			$check = $query->get();
				
			if( count($check) > 0) return STATIC::ERROR_PRICE_DEFAULT_DUPLICATE;
		}
		
		return true;
		
	}

	public function checkUniqueBarredPrice(){
		//controllo unicità del prezzo di LISTINO (o BARRATO) e dell'esistenza del campo prodotto nel prezzo
		if( $this->getLabel() == 'list'){
			$query = self::prepareQuery()
					->where('label','barred')
					->where('product',$this->product);
			
			if($this->hasId() ){
				$query->where('id',$this->getId(),'<>');

			}
			
			$check = $query->get();
				
			if( count($check) > 0) return STATIC::ERROR_PRICE_BARRED_DUPLICATE;
		}
		
		return true;
		
	}

	public function checkUniqueListPrice(){
		if( $this->getLabel() != 'default' && $this->getLabel() != 'barred'){
			$query = self::prepareQuery()
					->where('label', $this->label)
					->where('product',$this->product)
					->where('quantity',$this->quantity)
					->whereExpression("(userCategory = 0 OR userCategory = {$this->userCategory})");
			if($this->hasId() ){
				$query->where('id',$this->getId(),'<>');

			}

			
			
			$check = $query->getOne();
			//debugga($check);exit;
			if( $check->dateStart && $check->dateEnd ){
				if( $this->dateStart && $this->dateEnd ){
					
				}
			}elseif( $check->dateStart ){

			}elseif( $check->dateEnd ){

			}else{

			}
			
			return true;
				
			if( okArray($check) ) return STATIC::ERROR_PRICE_LIST_DUPLICATE;
		}
		

		return true;

	}



	public function beforeSave(){
		//controllo se la quantita è un intero positivo e il prezzo è maggiore o uguale di zero
		if($this->getLabel() != 'default'){
			$this->quantity = (int)$this->quantity;
			if(!$this->quantity) $this->quantity = 1;
			$this->userCategory = (int)$this->userCategory;
			//if(!$this->userCategory) $this->userCategory = 1;
		}else{
			$this->userCategory = 1;
			$this->quantity = 1;
		}

		$this->label = trim(strtolower($this->label));
		$this->value = (float)$this->value;
		if($this->value < 0) $this->value = 0;

		
	}






	 

}

?>