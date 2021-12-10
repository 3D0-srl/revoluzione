<?php

class PrivaliaList extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'privalia_list'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = ''; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	

	
	public $categories = array();


	function setCategories($list = array()){
		$this->categories = $list;
	}






	function afterSave(){
		parent::afterSave();
		$database = _obj('Database');
		$database->delete('privalia_list_composition',"id_list = {$this->id}");
		if( okArray($this->categories) ){
			foreach($this->categories as $v){
				$toinsert = array(
					'id_list' => $this->id,
					'id_category' => $v,
				);
				$database->insert('privalia_list_composition',$toinsert);
			}
		}
	}

	function afterLoad(){
		parent::afterLoad();
		$database = _obj('Database');
		$sel = $database->select('*','privalia_list_composition',"id_list = {$this->id}");
		if( okArray($sel) ){
			foreach($sel as $v){
				$this->categories[] = $v['id_category'];
			}
		}
	}


	function delete(){
		$database = _obj('Database');
		$id = $this->id;
		$database->delete('privalia_list',"id={$id}");
		$database->delete('privalia_list_composition',"id_list={$id}");
	}




}

