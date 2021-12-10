<?php

class ArticleComment extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'articleComment'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = 'parent'; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 




	 function beforeSave(){
		parent::beforeSave();
		if( !$this->id ){
			$this->deleted = 0;
			$this->dateCreation = date('Y-m-d H:i:s');
		}
	 }
	

	function getAuthorName(){
		if( $this->id && $this->user){
			$user = User::prepareQuery()->where('id',$this->user)->getOne();
			
			if( is_object($user) ){
				return $user->name." ".$user->surname;
			}else{
				return false;
			}
		}
	}
}


?>