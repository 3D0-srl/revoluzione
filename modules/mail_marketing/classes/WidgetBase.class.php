<?php

class WidgetBase extends Base implements Widget{
	
	// COSTANTI DI BASE
	const TABLE = ''; // nome della tabella a cui si riferisce la classe
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
	
	function getBaseUrl(){
		$protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';


		return $protocol."://".$_SERVER['SERVER_NAME'];
	}
	
	function getUrlEdit(){
		
	}
	

	function getIcon(){
		
	}

	function getLogoUrl(){
		
	}
	public function saveWidget($obj,$name,$id){
		if( is_object($obj) ){
			
			$old = $obj->getOldObject();
			
			if( is_object($old) && $old->id ){
				$_obj = MailWidget::prepareQuery()->where('id_object',$id)->getOne();
				if( is_object($_obj) ){
					$_obj->set(
						array(
							'name' => $name,
							'object' => get_class($obj),
							'id_object' => $id,
							'date_last_update' => date('Y-m-d H:i:s'),
						)
					)->save();
				}
			}else{
				$now =  date('Y-m-d H:i:s');
				MailWidget::create()->set(
					array(
						'name' => $name,
						'object' => get_class($obj),
						'id_object' => $id,
						'data_insert' => $now,
						'data_last_update' => $now,
					)
				)->save();


			}
		}
	}
	
	function getContent(){

	}
	function afterSave(){
		parent::afterSave();

		
		$this->saveWidget($this,$this->name,$this->id);
	}
	

	function setConf($array=array()){
		$this->conf = serialize($array);
	}
	


}



?>