<?php
namespace Shop;
use Marion\Core\Base;
use Marion\Core\Marion;
class ShippingArea extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'shippingArea'; // nome della tabella a cui si riferisce la classe
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
	 

	public $countries = array();



	function afterSave(){
		parent::afterSave();
		if( $this->id ){
			$database = Marion::getDB();
			$database->delete('shippingAreaComposition',"area={$this->id}");
			if( okArray($this->countries) ){
				foreach($this->countries as $v){
					$toinsert = array(
						'country' => $v,
						'area' => $this->id
					);
					$database->insert('shippingAreaComposition',$toinsert);
				}
			}
		}
	}

	function getCountries(){
		
		if( $this->id ){
			$this->countries = array();
			$database = Marion::getDB();
			$select = $database->select('*','shippingAreaComposition',"area={$this->id}");
			
			foreach($select as $v){
				$this->countries[] = $v['country'];
			}
			
		}
	}


	function afterLoad(){
		parent::afterLoad();
		$this->getCountries();
	}


	function setCountries($array){
		$this->countries = $array;
	}



	public static function fromCountry($country,$limit=NULL){
		$query = self::prepareQuery()->whereExpression("id in (select area from shippingAreaComposition where country='{$country}')");
		if( $limit ){
			$query->limit($limit);
		}
		$res = $query->get();
		return $res;
	}

}

?>