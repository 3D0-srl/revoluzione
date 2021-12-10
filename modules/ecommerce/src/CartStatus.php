<?php
namespace Shop;
use Marion\Core\Base;
class CartStatus extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'cartStatus'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'cartStatusLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'id_cartStatus';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica




	public static function withLabel($label){
		
		if( $label ){
			$obj = CartStatus::prepareQuery()->where('label',$label)->getOne();
			
			return $obj;
		}
		return false;
	}

}
?>