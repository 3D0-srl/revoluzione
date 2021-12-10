<?php
namespace Shop;
use Marion\Core\Base;
class PriceList extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'priceList'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'priceListLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'priceList';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 
	


	function checkSave(){
		$check = parent::checkSave();
		if( $check == 1 ) {
			
			//controllo se l'etichetta è diversa dai valori non consentiti "default" e "barred"
			if( $this->label == 'default' || $this->label == 'barred' ){
				return "label_not_aviable";
			}
			


			$query = self::prepareQuery()->where('label',$this->label);
			if( $this->hasId() ){
				$query->where('id',$this->id,'<>');
			}
			$obj = $query->getOne();

			if( is_object($obj) ){
				return "label_duplicate";
			}else{
				$query = self::prepareQuery()->where('priority',$this->priority);
				if( $this->hasId() ){
					$query->where('id',$this->id,'<>');
				}
				$obj = $query->getOne();

				if( is_object($obj) ){
					return "priority_duplicate";
				}else{
					return 1;
				}
			}
		}else{
			return $check;
		}

	}
}

?>