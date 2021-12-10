<?php

class Profile extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'profile'; // nome della tabella a cui si riferisce la classe
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
    
    


    function setPermissions($array = null){
        $this->permissions = $array;
    }


    function getPermissions(){
        $database = _obj('Database');
        $select = $database->select('*','profile_permission',"id_profile={$this->id}");
        if( okArray($select) ){
            foreach($select as $v){
                $this->permissions[] =  $v['permission'];
            }
        }
    }

    function afterLoad(){
        parent::afterLoad();
        $this->getPermissions();
    }


    function afterSave(){
        parent::afterSave();
        $this->saveComposition();
    }



    function saveComposition(){
        $database = _obj('Database');
        $database->delete('profile_permission',"id_profile={$this->id}");
        if( okArray($this->permissions) ){
            foreach($this->permissions as $v){
                $toinsert = array(
                    'permission' => $v,
                    'id_profile' => $this->id
                );
                $database->insert('profile_permission',$toinsert);
            }
        }
    }

	function removeUser($id_user=NULL){
		if( $id_user ){
			$database = _obj('Database');
			$database->update('user',"id_profile={$this->id} AND id={$id_user}",array('id_profile'=>0));
		}
	}


	function delete(){
	    $database = _obj('Database');
        $database->delete('profile_permission',"id_profile={$this->id}");
		parent::delete();
		
	}

}


?>