<?php

class Coupon extends Base {

	// COSTANTI DI BASE
	const TABLE = 'coupon'; // nome della tabella a cui si riferisce la classe
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


	public function checkSave() {
		$res = parent::checkSave();

		if( $res == 1 ){
			if( $this->name ) {
				$query = self::prepareQuery()->where('name',$this->name);

				if( $this->id ){
					$query->where('id',$this->id,'<>');
				}
				$check = $query->get();

				if( okArray($check) ) {

					return _translate('duplicate_name','manage_coupon');
				}
			}
			return $res;
		} else {
			return $res;
		}

	}

}

?>