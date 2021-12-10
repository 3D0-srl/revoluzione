<?php

class SendMailTemplate extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'mail_log_template'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'amedeo@3d0.it'; // email a cui inviare la notifica
	

	
	static function get_template($id) {
		$database = _obj('Database');
		if ($id) {
	        $data = $database->select('*','template_newsletter',"id = {$id}");
		} else {
			$data = $database->select('*','template_newsletter');
		}

		if (okArray($data)) {
			return $data;
		} else {
			return false;
		}
	}




}



?>