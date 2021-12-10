<?php
class Base{
	
	/************************************* COSTANTI ***************************************************/

	const TABLE = 'tabella'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'tabella';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = 'parent'; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	
	/*************************************************************************************************/

	/********************************* VARIABILI DI CLASSE ********************************************/

	protected static $_tableColumns; //contiene i campi della tabella a cui si riferisce l'oggetto
	protected static $_tableColumnsLocale; //contiene i campi della tabella che contiene i dati locali

	protected $_columns = array(); //contiene i campi della tabella a cui si riferisce l'oggetto
	protected $_columnsLocale = array(); //contiene i campi della tabella che contiene i dati locali
	
	protected $_oldObject;
	/*************************************************************************************************/
	
	public $_localeData;
	public $_type_action='';
	// COSTRUTTORE
	function __construct(){
		
	
	}

	
	
	function showConstants(){
		$array = array(
			"Tabella" => static::TABLE,
			"Identificativo della Tabella" => static::TABLE_PRIMARY_KEY,
			"Campo padre della Tabella" => static::PARENT_FIELD_TABLE,
			"Tabella dati locali" => static::TABLE_LOCALE_DATA,
			"Chiave esterna Tabella dati locali" => static::TABLE_EXTERNAL_KEY,
			"Campo conente il locale" => static::LOCALE_FIELD_TABLE,
			"LOG ABILITATI" => static::LOG_ENABLED,
			"PATH dei log" => static::PATH_LOG,
			"Notifiche abilitate" => static::NOTIFY_ENABLED,
			"Email notifiche" => static::NOTIFY_ADMIN_EMAIL,
		);
		debugga($array);
	}
	
	
	//metodo che avvia i metodi di inizializzazione dell'oggetto dopo che i dati sono stati settati
	public function init()
	{
		$this->getDataInit();
		
	}



	//metodo richiamato quando l'oggetto viene creato per la prima volta
	public function afterLoad()
	{
		Marion::do_action('after_load_'.strtolower(get_class($this)),array($this));		
	}

	//funzione richiamata all'inizio di ogni metodo di tipo factory per la creazione di un nuovo oggetto. In questa funzione viene fatto un controllo
	//sulla configurazione dell'oggetto stesso
	protected static function initClass(){
		
		self::checkTable();
		//self::getTableColumns();
	}
	

	//crea un nuovo oggetto
	public static function create(){
		self::initClass();
		$object = _obj(get_called_class());
		$object->getColumns();
		$object->init();
		
		return $object;
	}

	//metodo che inizializza un oggetto a partire dal suo identificativo (ID)
	public static function withId($id)
	{
		
		self::initClass();
		if($id){
			$database = Marion::getDB();

			$query = $database->getQuerySelect('*',static::TABLE,static::TABLE_PRIMARY_KEY."=?");
			$data = $database->prepare($query)
					->setParam($id,'int')
					->execute();
			if(okArray($data)){
				return static::withData($data[0]);
			}else{
				static::writeLog("nessun dato trovato nel database per l'identificativo specificato nel metodo << withId >>");
				return null;
			}

		}else{
			static::writeLog("identificativo in input vuoto nel metodo << withId >>");
			return null;
		}


	}

	//inizializza un oggetto da un array
	public static function withData($data)
	{


		self::initClass();
		if(okArray($data)){
			
			$object = _obj(get_called_class());
			$object->getColumns();
			$object->set($data);
			
			$object->init();
			$object->afterLoad();
			//memorizzo i dati vecchi
			
			$object->setOldObject($object->copyWithId());

			
			return $object;
	
		}else{
			static::writeLog("array in input vuoto nel metodo << withData >>");
			return null;
		}
	}
	

	//controlla se un dato è serializzato
	public static function is_serialized($data)
    {
        return (@unserialize($data) !== false);
    }


	protected function existsColumn($field){
		if( !$field ) return false;
		return in_array($field,$this->_columns);
	}

	protected function existsColumnLocale($field){
		if( !$field ) return false;
		return in_array($field,$this->_columnsLocale);
	}

	
	function getOldObject(){
		if( is_object($this->_oldObject) ){
			return $this->_oldObject;
		}

		return false;
		
	}

	function setOldObject($obj){
		if( is_object($obj) ){
			$this->_oldObject = $obj;
		}
		
	}

	function hasId(){
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if( isset($this->$field_id) && $this->$field_id ){
			return true;
		}else{
			return false;
		}
	}

	//restituisce l'id dell'oggetto
	function  getId(){
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if($this->$field_id){
			return $this->$field_id;
		}else{
			static::writeLog("ID non presente per l'oggetto << getId >>");
			return false;
		}
	}

	//restituisce l'id del padre
	function  getParentId(){
		$parent_id = STATIC::PARENT_FIELD_TABLE;
		if($this->$parent_id){
			return $this->$parent_id;
		}else{
			static::writeLog("Padre non presente per l'oggetto << getParentId >>");
			return false;
		}
	}

	//restituisce l'oggetto padre
	function  getParent(){
		$parent = $this->getParentId();
		if($parent){
				return self::withId($parent);
		}else{
			return false;
		}

	}

	//verifica se l'oggetto ha un padre
	public function hasParent(){
		$parent_id = STATIC::PARENT_FIELD_TABLE;
		if($this->$parent_id){
			return true;
		}else{
			return false;
		}
	}

	
	//setta i valori non locali di un oggetto
	public function set($data)
	{
	
		if(okArray($data)){
			foreach($data as $k => $v){
				
				if($this->existsColumn($k)){
					
					if( static::is_serialized($v) ){
						$this->$k = unserialize($v);
					}else{
						$this->$k = $v;
					}
				}else{
					if( static::is_serialized($v) ){
						$this->_other_data[$k] = unserialize($v);
					}else{
						$this->_other_data[$k] = $v;
					}
				}
			}

		}
		if( array_key_exists('_locale_data',$data) && okArray($data['_locale_data']) ){
			$this->setDataFromArray($data['_locale_data']);
		}else{
			$this->setData($data,getConfig('locale','default'));
		}
		//se i dati provegono da un prepara form allora setto i dati locali
		/*if( isMultilocale() ){
			$this->setDataFromArray($data['_locale_data']);
		}else{
			
			$this->setData($data,getConfig('locale','default'));
			
		}*/
		
		return $this;
	}

	//setta i dati locali dell'oggetto specidicando il locale
	public function setData($data,$locale=NULL)
	{	
		
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		if(okArray($data)){
			foreach($data as $k => $v)
			{
				if( $this->existsColumnLocale($k) ){
					
					if(is_array($v)){
						$this->_localeData[$locale][$k] = serialize($v);
					}else{
						$this->_localeData[$locale][$k] = $v;
					}
				}

			}
		}
		
		return $this;
		
	}

	//setta i dati locali dell'oggetto da un array
	public function setDataFromArray($dataArray)
	{	
		if( okArray($dataArray) ){
			foreach($dataArray as $locale => $data){
				if(okArray($data)){
					foreach($data as $k => $v)
					{
						if( $this->existsColumnLocale($k) ){
							if(is_array($v)){
								$this->_localeData[$locale][$k] = serialize($v);
							}else{
								$this->_localeData[$locale][$k] = $v;
							}
						}

					}
				}
			}
		}
		return $this;
		
	}
	
	//salva i dati locali di un oggetto
	/*protected function saveLocaleData(){
		
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		
		if($this->$field_id){
			$database = _obj('Database');
			
			$locale_select = $database->select(STATIC::LOCALE_FIELD_TABLE,static::TABLE_LOCALE_DATA,static::TABLE_EXTERNAL_KEY."={$this->$field_id}");
			
			if(okArray($locale_select)){
				foreach($locale_select as $v){
					$locale_old[$v[STATIC::LOCALE_FIELD_TABLE]] = $v[STATIC::LOCALE_FIELD_TABLE];
				}
			}
			
			if( okArray($this->_localeData) ){
				foreach($this->_localeData as $locale => $data){
					$toinsert = $data;
					$toinsert[STATIC::LOCALE_FIELD_TABLE] = $locale;
					$toinsert[STATIC::TABLE_EXTERNAL_KEY] = $this->$field_id;
					
					if( okArray($locale_old) ){

						if( in_array($locale,$locale_old)){
							$database->insert(static::TABLE_LOCALE_DATA,$toinsert);
							
						}else{
							unset($locale_old[$locale]);
							$database->update(static::TABLE_LOCALE_DATA,static::TABLE_EXTERNAL_KEY."={$this->$field_id} AND locale='{$locale}'",$toinsert);
							
						}
					}else{
						$database->insert(static::TABLE_LOCALE_DATA,$toinsert);
					
					}
					
					
				}
			}
			if(okArray($locale_old)){
				foreach($locale_old as $v){
					$database->delete(static::TABLE_LOCALE_DATA,STATIC::LOCALE_FIELD_TABLE."='{$v}' AND ".static::TABLE_EXTERNAL_KEY."={$this->$field_id}");
				}
			}

		}
		
		return $this;
	}*/
	//salva i dati locali di un oggetto
	protected function saveLocaleData(){
		
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		
		if($this->$field_id){
			$database = _obj('Database');
			
			$locale_select = $database->select(STATIC::LOCALE_FIELD_TABLE,static::TABLE_LOCALE_DATA,static::TABLE_EXTERNAL_KEY."={$this->$field_id}");
			if(okArray($locale_select)){
				foreach($locale_select as $v){
					$locale_old[$v[STATIC::LOCALE_FIELD_TABLE]] = $v[STATIC::LOCALE_FIELD_TABLE];
				}
			}

			
			if( okArray($this->_localeData) ){
				foreach($this->_localeData as $locale => $data){
					$toinsert = $data;
					$toinsert[STATIC::LOCALE_FIELD_TABLE] = $locale;
					$toinsert[STATIC::TABLE_EXTERNAL_KEY] = $this->$field_id;
					
					if(!in_array($locale,$locale_old)){
						$database->insert(static::TABLE_LOCALE_DATA,$toinsert);
					}else{
						unset($locale_old[$locale]);
						$database->update(static::TABLE_LOCALE_DATA,static::TABLE_EXTERNAL_KEY."={$this->$field_id} AND ".STATIC::LOCALE_FIELD_TABLE."='{$locale}'",$toinsert);
						
					}
					
					
				}
			}
			if(okArray($locale_old)){
				foreach($locale_old as $v){
					$database->delete(static::TABLE_LOCALE_DATA,STATIC::LOCALE_FIELD_TABLE."='{$v}' AND ".static::TABLE_EXTERNAL_KEY."={$this->$field_id}");
				}
			}

		}
		
		return $this;
	}

	//rimuove i dati locali per un fissato locale
	public function removeData($locale){
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		unset($this->_localeData[$locale]);
	}

	//metodo che verifica se l'oggetto ha figli
	public function hasChildren(){
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if($this->$field_id){
			$database = _obj('Database');
			$check = $database->select(STATIC::TABLE_PRIMARY_KEY,STATIC::TABLE,STATIC::PARENT_FIELD_TABLE."={$this->$field_id}");
			return okArray($check);
		}else{
			return false;
		}
	}
	//metodo che prende i figli di un oggetto
	public function getChildren($where=NULL){
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if( !$where ) $where = "1=1";
		if($this->$field_id){
			$database = _obj('Database');
			$data = $database->select('*',STATIC::TABLE,STATIC::PARENT_FIELD_TABLE."={$this->$field_id} AND {$where}");
			
			$toreturn = array();
			if(okArray($data)){
				foreach($data as $v){
					$toreturn[] = self::withData($v);
				}
				return $toreturn;
			}else{
				return false;
			}
			
		}else{
			return false;
		}
	}
	
	
	

	//salva l'oggetto nel database
	// se $force_id è true allora è possibile salvare un oggetto specificando in fase di creazione il suo id
	public function save($force_id=false){
		
		$this->beforeSave();
		
		$check = $this->checkSave();
		
		
		

		$flag = (int)$check;
		if($check == 1){
			$database = _obj('Database');
		
			foreach($this as $k => $v){
				if( $this->existsColumn($k) ){
					if( is_array($v) ){
						$data[$k] = serialize($v);
					}else{
						$data[$k] = $v;
					}
				}
			}
			if( $this->existsColumn('dateLastUpdate') ){
				$data['dateLastUpdate'] = date('Y-m-d H:i:s');
			}
			
			$field_id = STATIC::TABLE_PRIMARY_KEY;
			
			if($this->$field_id){
				if( $force_id ){
					$res = $database->insert(STATIC::TABLE,$data);
				}else{
					$res = $database->update(STATIC::TABLE,STATIC::TABLE_PRIMARY_KEY."={$this->$field_id}",$data);
				}
				$this->last_query = $database->lastquery;
				
				if( !$res ){
					$this->error_query = $database->error;
				}else{
					unset($this->error_query);
				}
				
			}else{
				$res = $database->insert(STATIC::TABLE,$data);
				
				$this->last_query = $database->lastquery;
				if( !$res ){
					$this->error_query = $database->error;
				}else{
					$this->id = $res;
					unset($this->error_query);
				}		
			}
			
			$this->afterSave();
			return $this;
		}else{
			return $check;
		}

	}
	
	//funzione chiamata prima del salvataggio dell'oggetto. In questa funzione avviene il controllo dei dati (dopo beforeSave()).
	//restituisce true (oppure 1) se va tutto bene altrimenti una stringa contentente l'eticehtta dell'errore
	protected function checkSave(){
		return true;
	}


	//funzione chiamata dopo il salvataggio dell'oggetto
	protected function afterSave(){
		
		$this->saveLocaleData();
		//eseguo le eventuali azioni
		Marion::do_action('after_save_'.strtolower(get_class($this)),array($this));		
	}

	//funzione chiamata prima del salvataggio dell'oggetto. In questa funzione si effettuano delle operazioni preliminari prima del salvataggio
	public function beforeSave(){
		if( $this->getId() ){
			$this->_type_action = 'UPDATE';
		}else{
			$this->_type_action = 'INSERT';
		}
	}


	//metodo che viene richiamato prima di eliminare l'oggetto dal database
	function beforeDelete(){
		Marion::do_action('before_delete_'.strtolower(get_class($this)),array($this));
	}


	//metodo che elimina dal database l'oggetto
	public function delete(){
		$this->beforeDelete();
		if($this->getId()){
			$database = _obj('Database');
			$field_id = STATIC::TABLE_PRIMARY_KEY;
			$database->delete(static::TABLE_LOCALE_DATA,static::TABLE_EXTERNAL_KEY."={$this->$field_id}");
			$database->delete(static::TABLE,static::TABLE_PRIMARY_KEY."={$this->$field_id}");
		}

	}
	
	//rimuove tutti i figli (a tutti i livelli) di un oggetto
	public function deleteChildren(){
		$parent_field = STATIC::PARENT_FIELD_TABLE;
		if($parent_field && $this->getId()){
			$allChildren = self::prepareQuery()->where($parent_field,$this->getId())->get();
			if( okArray($allChildren) ){
				foreach($allChildren as $v){
					$list[$v->id] = $v; 	
				}
			}
			$cont_old = 0;

			while( count($list) != $cont_old  ){
				foreach($list as $val){	
					$cont_old = count($list);
					if($val->hasChildren()){
						
						$figli = $val->getChildren();
						
						foreach($figli as $f){
							$list[$f->id] = $f;	
						}
					}	
				}	
			}
			foreach($list as $v){
				$v->delete();	
			}
		}
		return $this;
	}
	
	//restituisce il valore di un attributo. Se il valore è locale specificando il locale restituisce il valore apportuno altrimenti restituisce quello relativo al locale di default
	/*
		$field : campo da mostrare
		$locale: lingua in cui si vuole visualizzare il valore
		$truncate; lunghezza massima del valore da mostrare

	*/
	function get($field,$locale=NULL,$truncate=NULL){
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		if( property_exists($this,$field) ){
			$value =  $this->$field;
		}else{
			if(okArray($this->_localeData[$locale]) && array_key_exists($field,$this->_localeData[$locale])){
				$value =  $this->_localeData[$locale][$field];
			}
		}
		if( $truncate ){
			$value = strip_tags($value);

			if (strlen($value) > $truncate) {

				// truncate string
				$value = substr($value, 0, $truncate);

				// make sure it ends in a word so assassinate doesn't become ass...
				$value = substr($value, 0, strrpos($value, ' ')).'...'; 
			}
		}
		return $value;
	}
	

	//metodo che restituisce i valori locali dell'oggetto
	function valuesData($locale='all'){
		if(okArray($this->_localeData)){
			if($locale != 'all'){
				if(array_key_exists($locale,$this->_localeData)){
					return $this->_localeData[$locale];
				}else{
					static::writeLog("dati locali non presenti per '{$locale}' << getData >>");
					return false;
				}
				
			}else{
				return $this->_localeData;
			}
		}else{
			static::writeLog("nessun dato locale presente << getData >>");
			return false;
		}
	}

	//metodo che che scrive nei log e/o invia messaggi all'admin
	public static function writeLog($message,$type="ERROR")
	{
		
		$class_name = get_called_class();
		$message_log = "{$type} ({$class_name}): {$message}";
		
		if( static::LOG_ENABLED ){
			if(static::PATH_LOG){
				error_log($message_log,0,static::PATH_LOG);
			}else{
				error_log($message_log,0);
			}

		}
		if( ( static::NOTIFY_ENABLED || Marion::getConfig('log','notify') ) &&  ( static::NOTIFY_ADMIN_EMAIL || Marion::getConfig('log','mail') )){
			if( Marion::getConfig('generale','mail') ){
				if( filter_var(Marion::getConfig('generale','mail'), FILTER_VALIDATE_EMAIL) ){
					error_log($message_log,1,Marion::getConfig('generale','mail'));
				}
			}else{
				if(filter_var(static::NOTIFY_ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)){
					error_log($message_log,1,static::NOTIFY_ADMIN_EMAIL);
				}	
			}
		}
	}

	//effettua la copia di un oggetto
	public function copyWithId(){
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		$obj = clone $this;
		return $obj;
	}
	
	//effettua la copia di un oggetto
	public function copy(){
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		$obj = clone $this;
		unset($obj->$field_id);
		return $obj;
	}

	/******************************* METODI DI INIZIALIZZAIONE *****************************/
	
	//metodo che inizializza l'array dei dati locali della tabella
	protected function getDataInit(){
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if(isset($this->$field_id) && $this->$field_id ){
			$database = _obj('Database');
			$data = $database->select('*',static::TABLE_LOCALE_DATA,static::TABLE_EXTERNAL_KEY."={$this->$field_id}");
			if( okArray($data) ){
				foreach($data as $v){
					if(okArray($v)){
						unset($v[static::TABLE_EXTERNAL_KEY]);
						$app = $v[STATIC::LOCALE_FIELD_TABLE];
						unset($v[STATIC::LOCALE_FIELD_TABLE]);
						$this->_localeData[$app] = $v;
					}
				}
			}

		}
	}



	//prende le colonne delle tabelle interessate nella classe e le memorizza in variabili di classe
	function getColumns(){
		$database = _obj('Database');
		$columns = $database->fields_table(STATIC::TABLE);

		$this->_columns = $columns;
		
		if(STATIC::TABLE_LOCALE_DATA){
			$columns = $database->fields_table(STATIC::TABLE_LOCALE_DATA);
			$this->_columnsLocale = $columns;
		}	
	}


	 function getColumnsArray($tipo=NULL){
		if($tipo == 'locale'){
			return $this->_columnsLocale;
		}else{
			return $this->_columns;
		}	
	}


	
	//controlla se esistono le tabelle relative all'oggetto 
	protected static function checkTable(){
		
		$database = _obj('Database');
		$db = $GLOBALS['setting']['default']['DATABASE']['options']['nome'];
		
		$table = STATIC::TABLE;
		$check = $database->select('*','information_schema.tables',"table_schema = '{$db}' AND table_name = '{$table}'");
		
		if(!okArray($check)){
			self::writeLog("Tabella {$table} non presente nel database {$db}");
			
			throw new Exception("Tabella {$table} non presente nel database {$db}");
		}
		
		if(STATIC::TABLE_LOCALE_DATA){
			$table = STATIC::TABLE_LOCALE_DATA;
			$check = $database->select('*','information_schema.tables',"table_schema = '{$db}' AND table_name = '{$table}'");
			
			if(!okArray($check)){
				self::writeLog("Tabella {$table} non presente nel database {$db}");
				throw new Exception("Tabella {$table} non presente nel database {$db}");
				
			}
		}
		
	}
	

	function prepareForm2(){
		$locales = Marion::getConfig('locale','supportati');
		foreach($this as $k => $v){
			if($k == '_localeData'){
				if(okArray($v)){
						//debugga($this->_localeData);exit;
						foreach($locales as $lo){
							if( !$this->_localeData[$lo]){
								
								foreach($this->_columnsLocale as $field){
									if($field != STATIC::LOCALE_FIELD_TABLE && $field != STATIC::TABLE_EXTERNAL_KEY){
										$this->_localeData[$lo][$field] = null;
									}
								}
							}
						}
						
						foreach($this->_localeData as $loc => $values){
							foreach($values as $k1 => $v1){
								if($k1 != STATIC::LOCALE_FIELD_TABLE){
									$data[$k1][$loc] = $v1;
								}
							}
						}
				}

			}else{
				$data[$k] = $v;
				
			}
		}
		unset($data['_columns']);
		unset($data['_columnsLocale']);
		unset($data['_oldObject']);
		
		return $data;
	}


	//prepara i dati di un form. Riceve in input un parametro che se è uguale ad 'all' prende tutti i valori locali altrimenti solo quelli di un locale assegnato
	function prepareForm($locale='all'){
		foreach($this as $k => $v){
			if($k == '_localeData'){
				if(okArray($v)){
					if($locale == 'all'){
						foreach($this->_localeData as $loc => $values){
							foreach($values as $k1 => $v1){
								if($k1 != STATIC::LOCALE_FIELD_TABLE){
									$data[$k1."_{$loc}"] = $v1;
								}
							}
						}
					}else{
						foreach($this->_localeData as $loc => $values){
							foreach($values as $k1 => $v1){
								if( $locale == $loc){
									if($k1 != STATIC::LOCALE_FIELD_TABLE){
										$data[$k1] = $v1;
									}
								}
							}
						}
					}

				}

			}else{
				$data[$k] = $v;
				
			}
		}
		return $data;
	}

	
	//crea l'albero dei figli a partire da un array
	public static function buildtree($src_arr, $parent_id = 0, $tree = array())
	{
		if(STATIC::PARENT_FIELD_TABLE){
			$parent = STATIC::PARENT_FIELD_TABLE;
			$id = STATIC::TABLE_PRIMARY_KEY;
			
			foreach($src_arr as $idx => $row)
			{
				$parent = STATIC::PARENT_FIELD_TABLE;
				if($row->$parent == $parent_id)
				{
					$tree[$row->$id] = $row;
					unset($src_arr[$idx]);
					$tree[$row->id]->children = self::buildtree($src_arr, $row->$id);
				}
			}
			
			ksort($tree);
			return $tree;
		}else{
			static::writeLog("L'oggetto non possiede un campo parent << buildtree >>");
			return false;
		}
	}
	
	
	
	public static function prepareQuery(){
		self::initClass();
		$object = self::create();
		$object->getColumns();
		
		$data = array(
			'table' => STATIC::TABLE,
			'table_locale' => STATIC::TABLE_LOCALE_DATA,
			'primary_key' => STATIC::TABLE_PRIMARY_KEY,
			'key_external' => STATIC::TABLE_EXTERNAL_KEY,
			'parent' => STATIC::PARENT_FIELD_TABLE,
			//'locale_column'=> STATIC::LOCALE_FIELD_TABLE,
			'columns' => $object->getColumnsArray(),
			'columns_locale' => $object->getColumnsArray('locale'),
			'obj' => get_called_class()
		);
		
		$query = PrepareQuery::create($data);
		return $query;
		
		
	}

	
	
	

}


class PrepareQuery{
	public $obj;
	public $table;
	public $table_locale;
	public $primary_key;
	public $key_external;
	public $groupBy;
	public $offset;
	public $columns = array();
	public $columns_locale = array();
	public $join_array = array();
	public $left_join_array = array();
	public $right_join_array = array();
	public $field_select = array();


	public function init(){
		$this->obj='';
		$this->table = '';
		$this->table_locale = '';
		$this->primary_key = '';
		$this->key_external = '';
		$this->columns = array();
		$this->columns_locale = array();
		$this->join_array = array();
		$this->left_join_array = array();
		$this->right_join_array = array();
		$this->field_select = array();
	}
	
	public static function	create($array=array()){
		$query = new prepareQuery();
		foreach($array as $k => $v){
			$query->$k = $v;
		}
		$database = _obj('Database');
		$type_table = $database->type_fields_table($query->table);
		if($query->table_locale){
			$type_table_locale = $database->type_fields_table($query->table_locale);
		
			if(okArray($type_table_locale)){
				$type_table = array_merge($type_table,$type_table_locale);
			}
		}
		$query->type_field = $type_table;
		$query->reset();
		return $query;
	}
	//$operetor può assumere valore  '=','<>','IN','NOT IN','LIKE','ILIKE'
	function where($key,$value,$operator='='){
		$operator = trim(strtoupper($operator));
		$alias = '';
		if($key){
			if( $operator == '=' || $operator == '<>' || $operator == '>' || $operator == '<' || $operator == '>=' || $operator == '<='){
				if(in_array($key,$this->columns) || in_array($key,$this->columns_locale)){
					if( okArray($this->columns_locale) ){
						if( in_array($key,$this->columns) ){
							$alias = 't1.';
						}else{
							$alias = 't2.';
						}
					}
					
					//unset($alias);
					$database = _obj('Database');
					$value = $database->formatta_campo($value,$this->type_field[$key]);
					if( $this->condition ){
						$this->condition .= " AND {$alias}{$key} {$operator} {$value} AND ";
					}else{
						$this->condition .= "{$alias}{$key} {$operator} {$value} AND ";
					}	
				}
			}else{
				if(in_array($key,$this->columns) || in_array($key,$this->columns_locale)){
					if( okArray($this->columns_locale) ){
						if( in_array($key,$this->columns) ){
							$alias = 't1.';
						}else{
							$alias = 't2.';
						}
					}
					//unset($alias);
					if( $this->condition ){
						$this->condition .= " AND {$alias}{$key} {$operator} {$value} AND ";
					}else{
						$this->condition .= "{$alias}{$key} {$operator} {$value} AND ";
					}
				}
			}
			$this->condition = preg_replace('/AND $/','',$this->condition);
		}
		return $this;
	}


	function whereExpression($condition=NULL){
		if($condition){
			if( $this->condition ){
				$this->condition .= " AND {$condition} ";
			}else{
				$this->condition .= "{$condition} ";
			}
				
		}
		return $this;
	}
	
	//$operetor può assumere valore  '=','<>','IN','NOT IN','LIKE','ILIKE'
	function whereMore($where=array(),$operator="="){
		$operator = trim(strtoupper($operator));
		if(okArray($where)){
			foreach($where as $k => $v){
				$this->where($k,$v,$operator);	
			}
		}
		return $this;
		
	}

	//$operetor può assumere valore  '=','<>','IN','NOT IN','LIKE'
	function orWhere($key,$value,$operator='='){
		$operator = trim(strtoupper($operator));
		$alias = '';
		if($key){
			if( $operator == '=' || $operator == '<>' || $operator == '>' || $operator == '<' || $operator == '>=' || $operator == '<='){
				if(in_array($key,$this->columns) || in_array($key,$this->columns_locale)){
					if( in_array($key,$this->columns) ){
						$alias = 't1.';
					}else{
						$alias = 't2.';
					}
					unset($alias);
					$database = _obj('Database');
					$value = $database->formatta_campo($value,$this->type_field[$key]);
					if( $this->condition ){
						$this->condition .= " OR {$alias}{$key} {$operator} {$value} AND ";
					}else{
						$this->condition .= "{$alias}{$key} {$operator} {$value} AND ";
					}	
				}
			}else{
				unset($alias);
				if( $this->condition ){
					$this->condition .= " OR {$alias}{$key} {$operator} {$value} AND ";
				}else{
					$this->condition .= "{$alias}{$key} {$operator} {$value} AND ";
				}
			}
			$this->condition = preg_replace('/AND $/','',$this->condition);
		}
		return $this;
	}

	function orWhereExpression($condition=NULL){
		if($condition){
			if( $this->condition ){
				$this->condition .= " OR {$condition} ";
			}else{
				$this->condition .= "{$condition} ";
			}
				
		}
		return $this;
	}
	
	//$operetor può assumere valore  '=','<>','IN','NOT IN','LIKE','ILIKE'
	//$operetor può assumere valore  '=','<>','IN','NOT IN','LIKE','ILIKE'
	function orWhereMore($where=array(),$operator="="){
		$operator = trim(strtoupper($operator));
		if(okArray($where)){
			
			foreach($where as $k => $v){
				$this->orWhere($k,$v,$operator);
			}

		}
		return $this;
		
	}

	function orderByMore($orderBy=array(),$other_fields=array()){
		if(okArray($orderBy)){
			foreach($orderBy as $k => $v){
				if( strtolower($k) == 'rand()' ){
					$this->order .= "{$k}, ";
				}else{
					if(in_array($k,$this->columns) || in_array($k,$this->columns_locale) || in_array($k,$other_fields)){
						if( in_array($k,$this->columns_locale) || in_array($k,$other_fields) ){
							$this->field_select[] = $k;
						}
						$this->order .= "{$k} {$v}, ";
					}
				}
			}
		}
		return $this;
	}
	function groupBy($condiction = NULL){
		$this->groupBy = $condiction;
	}

	function orderBy($column,$type="ASC",$other_fields=array()){
		if( strtolower($column) == 'rand()' ){
			$this->order .= "{$column}, ";
		}else{
			if(in_array($column,$this->columns) || in_array($column,$this->columns_locale) || in_array($column,$other_fields)){
				if( in_array($column,$this->columns_locale) || in_array($column,$other_fields) ){
					$this->field_select[] = $column;
				}
				$this->order .= "{$column} {$type}, ";
			}
		}
		return $this;
	}
	
	function setFieldSelect($field){
		if( $field ){
			$this->field_select[] = $field;
		}

	}


	function join($table='',$condition=''){
		if( $table && $condition ){
			$this->join_array[] = array(
				'table' => $table,
				'condition' => $condition,
			);
		}
	}

	function leftOuterJoin($table='',$condition=''){
		if( $table && $condition ){
			$this->left_join_array[] = array(
				'table' => $table,
				'condition' => $condition,
			);
		}
	}

	function rightOuterJoin($table='',$condition=''){
		if( $table && $condition ){
			$this->right_join_array[] = array(
				'table' => $table,
				'condition' => $condition,
			);
		}
	}

	function limit($limit=0){
		$this->limit = $limit;
		return $this;
	}

	function offset($offset=0){
		$this->offset = $offset;
		return $this;
	}
	

	function setTable($table = NULL){
		$this->table = $table;
	}

	function setTableLocale($table = NULL){
		$this->table_locale = $table;
	}


	function custom($custom=NULL){
		$this->customQuery = $custom;
	}

	function getCount(){
		$database = _obj('Database');
		$group_by = '';
		$_paramters_select = '';
		if(!$this->condition) $this->condition = "1=1";
		

		
		$campi_raggrupppamento[] = 't1.id';
		if( okArray($this->field_select) ){
			foreach( $this->field_select as $v ){
				$explode = explode(' as ',$v);
				
				if( $explode[0] ){
					$campi_raggrupppamento[] = trim($explode[0]);
				}else{
					$explode = explode(' AS ',$v);

					if( $explode[1] ){
						$campi_raggrupppamento[] = trim($explode[1]);
					}else{
						$campi_raggrupppamento[] = trim($v);
					}
				}
			}
			
		}
		if( count($campi_raggrupppamento) > 1 ){
			foreach($campi_raggrupppamento as $v){
				$group_by .=  $v.",";
			}
			$group_by = preg_replace('/\,$/','',$group_by);
			$this->groupBy = $group_by;
			
		}

		
		
		if( $this->groupBy ){
			$this->condition .= " group by ".$this->groupBy;
		}
		
		if($this->table_locale && $this->key_external){
			$table_select = "{$this->table} as t1 left outer join {$this->table_locale} as t2 on t1.{$this->primary_key} = t2.{$this->key_external}";
		}else{
			$table_select = "{$this->table}";
		}
		if( okArray($this->join_array) ){
			foreach($this->join_array as $v){
				$table_select = "(".$table_select.") JOIN {$v['table']} on {$v['condition']}";
			}
		}

		

		if( okArray($this->left_join_array) ){
			foreach($this->left_join_array as $v){
				$table_select = "(".$table_select.") LEFT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}

		if( okArray($this->right_join_array) ){
			foreach($this->right_join_array as $v){
				$table_select = "(".$table_select.") RIGHT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}

		if( okArray($this->field_select) ){
			foreach( $this->field_select as $v ){
				$_paramters_select .= ", ".$v;
			}
			
		}

		
		if( $this->groupBy ){
			$_paramters_select = 'count(*)';
		}else{
		
		
			if($this->table_locale && $this->key_external){
				$_paramters_select = 'count(distinct t1.id)';
			}else{
				$_paramters_select = 'count(*)';
			}
		}
		
		$cont = $database->select("{$_paramters_select} as cont",$table_select,$this->condition);
		
		if( $this->groupBy ){
			
			
			$cont = count($cont);
		}else{
			$cont = $cont[0]['cont'];
		}
		
		
		return $cont;
	}
	
	/*function getCount(){
		$toreturn = array();
		$database = _obj('Database');
		
		if(!$this->condition) $this->condition = "1=1";

		if( $this->groupBy ){
			$this->condition .= " group by ".$this->groupBy;
		}

		if($this->order){ 
			$this->order = preg_replace('/, $/','',$this->order);
			$this->condition .= " order by ".$this->order;
		}
		if($this->limit){ 
			$this->condition .= " limit ".$this->limit;
		}

		if($this->offset){ 
			$this->condition .= " offset ".$this->offset;
		}
		
		if($this->table_locale && $this->key_external){
			$table_select = "{$this->table} as t1 left outer join {$this->table_locale} as t2 on t1.{$this->primary_key} = t2.{$this->key_external}";
		}else{
			$table_select = "{$this->table}";
		}
		if( okArray($this->join_array) ){
			foreach($this->join_array as $v){
				$table_select = "(".$table_select.") JOIN {$v['table']} on {$v['condition']}";
			}
		}

		

		if( okArray($this->left_join_array) ){
			foreach($this->left_join_array as $v){
				$table_select = "(".$table_select.") LEFT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}

		if( okArray($this->right_join_array) ){
			foreach($this->right_join_array as $v){
				$table_select = "(".$table_select.") RIGHT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}
		

		if($this->table_locale && $this->key_external){
			$_paramters_select = 'count(DISTINCTROW t1.id)';
		}else{
			$_paramters_select = 'count(*)';
		}
		
		
		$cont = $database->select("{$_paramters_select} as cont",$table_select,$this->condition);
	
		
		return $cont[0]['cont'];
	}*/


	function get(){
		$toreturn = array();
		$database = Marion::getDB();
		
		if(!$this->condition) $this->condition = "1=1";

		if( $this->groupBy ){
			$this->condition .= " group by ".$this->groupBy;
		}

		if($this->order){ 
			$this->order = preg_replace('/, $/','',$this->order);
			$this->condition .= " order by ".$this->order;
		}
		if($this->limit){ 
			$this->condition .= " limit ".$this->limit;
		}

		if($this->offset){ 
			$this->condition .= " offset ".$this->offset;
		}
		
		if($this->table_locale && $this->key_external){
			$table_select = "{$this->table} as t1 left outer join {$this->table_locale} as t2 on t1.{$this->primary_key} = t2.{$this->key_external}";
		}else{
			$table_select = "{$this->table}";
		}
		if( okArray($this->join_array) ){
			foreach($this->join_array as $v){
				$table_select = "(".$table_select.") JOIN {$v['table']} on {$v['condition']}";
			}
		}

		

		if( okArray($this->left_join_array) ){
			foreach($this->left_join_array as $v){
				$table_select = "(".$table_select.") LEFT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}

		if( okArray($this->right_join_array) ){
			foreach($this->right_join_array as $v){
				$table_select = "(".$table_select.") RIGHT OUTER JOIN {$v['table']} on {$v['condition']}";
			}
		}
		if($this->table_locale && $this->key_external){
			$_paramters_select = 'DISTINCTROW t1.*';
		}else{
			$_paramters_select = '*';
		}
		
		if( okArray($this->field_select) ){
			foreach( $this->field_select as $v ){
				$_paramters_select .= ", ".$v;
			}
			
		}

		
		
		$data = $database->select($_paramters_select,$table_select,$this->condition);
	
		$this->lastquery = $database->lastquery;
		$this->error = $database->error;
		
		if( okArray($data) ){
			foreach($data as $v){
				
				if(okArray($v)){
					
					$object = _obj($this->obj);
					
					$object->getColumns();
					$object->set($v);
					if(method_exists($object,'init')){
						$object->init();
					}

					if(method_exists($object,'afterLoad')){
						$object->afterLoad();
						$object->setOldObject($object->copyWithId()); 
					}
					
					$toreturn[] = $object;
				}else{
					Base::writeLog("array in input vuoto nel metodo << get >> dell'oggetto PrepareQuery");
				}
			}
		}
		
		return $toreturn;
	}

	function getOne(){
		$this->limit = 1;
		$result = $this->get();
		if(okArray($result)){
			return $result[0];
		}
	}
	// vedi https://github.com/danielgsims/php-collections
	function getCollection(){
		$data = $this->get();
		$collection = new Collection($this->obj);
		$collection->addRange($data);
				
		return $collection;

	}
	
	protected function formatta(){
            $key=$key.$k.", ";
            if( empty($v) ){
	            if( is_numeric($v) && intval($v) === 0 ){
	            	$values=$values.$this->formatta_campo($this->injectionPrevent($v),$tipi[$k]).", ";
	        	}else{
		        	$values=$values."null, ";
	        	}
            }else{
	            $values=$values.$this->formatta_campo($this->injectionPrevent($v),$tipi[$k]).", ";
            }
            
        }

	function reset(){
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		$this->lastquery = '';
		$this->error = '';
	}


}




?>