<?php

class MailWidget extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'mail_widget'; // nome della tabella a cui si riferisce la classe
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
	




	function getObject(){
		if( !is_object($this->obj_cache) ){
			$obj_name = $this->object;
			$file = 'widgets/'.$obj_name."/".$obj_name.".php";
			
			require_once('widgets/'.$obj_name."/".$obj_name.".php");
			
			$obj = $obj_name::withId($this->id_object);
			$this->obj_cache = $obj;
			return $obj;
		}else{
			return $this->obj_cache;
		}
	}
	

	function getIcon(){
		$obj = $this->getObject();
		return $obj->getIcon();
	}






	function getUrlEdit(){
		$obj = $this->getObject();
		return $obj->getUrlEdit();
	}


	function getLogoUrl(){
		$obj = $this->getObject();
		return $obj->getLogoUrl();
	}


	function delete(){
		$obj = $this->getObject();
		$obj->delete();
		parent::delete();
	}

}



?>