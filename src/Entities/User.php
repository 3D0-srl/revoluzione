<?php
namespace Marion\Entities;
use Marion\Core\Base;
use Marion\Core\Marion;
use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;
define('USER_TABLE', "user");
define('USER_USERNAME_DUPLICATE', 1);
define('USER_EMAIL_DUPLICATE', 2);
define('USER_USERNAME_OR_PASSWORD_INVALID', 3);
define('USER_EMAIL_OBBLIGATORY', 4);
define('USER_USERNAME_OBBLIGATORY', 5);
define('USER_NOT_ACTIVE', 6);
define('USER_EXPIRED_SESSION', 7);
define('USER_PWD_OBBLIGATORY', 8);




class User extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'user'; // nome della tabella a cui si riferisce la classe
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

	
	//variabile che stabilisce se l'utente ha inserito il blocco
	public $locked = false;
	public $auth_livelli = array();
	

	//funzione che blocca la sessione dell'utente
	public function lock(){
		$this->locked = true;	
	}
	
	//funzione che sblocca la sessione dell'utente
	public function unlock(){
		$this->locked = false;	
	}
	
	//funzione che verifica se la sessione è bloccata
	public function isLocked(){
		return $this->locked;
	}
	
/*	public static function permissions(){ 
		return self::$livelli_auth;
	}

	public function add_permission($code,$name){
		self::$livelli_auth[$code] = $name;
	}

	public static function set_permissions($array){
		self::$livelli_auth = $array;
	}
*/

	public static function loginWithJWT($jwt){
		try{
			$decoded = JWT::decode($jwt,_MARION_JWT_KEY_, array('HS256'));
			if( is_object($decoded) ){
				return self::withUsername($decoded->username);
			}
		}catch( ExpiredException $e ){
			return $e->getMessage();
		}
		
	}

	function generateJWT($expiretion_time=''){
		$payload = array(
			"id" => $this->id,
			"name" => $this->name,
			"surname" => $this->surname,
			"company" => $this->company,
			"username" => $this->username,
			"id_profile" => $this->id_profile,
			'iat' => time(),
			"nbf" => time(),
			'exp' => $expiretion_time?strtotime($expiretion_time):strtotime(_MARION_JWT_EXPIRATION_TIME_),
		);
		
		$jwt = JWT::encode($payload, _MARION_JWT_KEY_);
		
		return $jwt;
	}
	
	public static function loginWithToken($token){
		$token = Marion::decrypt($token);
		$token = base64_decode($token);
		list($username,$password,$time) = explode("||",$token);

		if( $time ){
			$diff = (time() - $time)/60;
			if( $diff > 180 ){
				return self::getError(USER_EXPIRED_SESSION);
			}
		}
		return self::login($username,$password);
	}

	
	public static function login($username_or_email,$password){
		if($username_or_email && $password ){
			
			
			Marion::do_action('user_credentials_login',array(&$username_or_email,&$password));
			
		
			$database = Marion::getDB();
			$query = $database->getQuerySelect('*',self::TABLE,"username=? AND deleted=0");
			$check = $database->prepare($query)
				->setParam($username_or_email,'string')
				->execute();
			
	
			if ( okArray($check) ){
				$type = 'username';
			}else{
				$query = $database->getQuerySelect('*',self::TABLE,"email=? AND deleted=0");
				$check = $database->prepare($query)
					->setParam($username_or_email,'string')
					->execute();
				$type = 'email';
			}

			

			if ( okArray($check) ){
				
				$hashedPassword = $check[0]['password'];
				
				if (!password_verify($password, $hashedPassword)) {
					return self::getError(USER_USERNAME_OR_PASSWORD_INVALID);
				}

				if($check[0]['active']){
					if( $type == 'username'){
						$utente = self::withUsername($username_or_email); 
					}
					if( $type == 'email'){
						$utente = self::withEmail($username_or_email); 
					}
					return $utente;
				}else{
					return self::getError(USER_NOT_ACTIVE);
				}
			}else{
				return self::getError(USER_USERNAME_OR_PASSWORD_INVALID);
			}
		}else{
			return self::getError(USER_USERNAME_OR_PASSWORD_INVALID);
		}
	}
	
	
	
	public static function withUsername($username){
		
		if( $username ){
			$database = _obj('Database');
			$data = $database->select('*',self::TABLE,"username='{$username}' AND deleted <> 1");
			if( okArray($data) ){
				$obj = self::withData($data[0]);
				return $obj;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public static function withEmail($email){
		
		if( $email ){
			$database = _obj('Database');
			$data = $database->select('*',self::TABLE,"email='{$email}' AND deleted <> 1");
			if( okArray($data) ){
				$obj = self::withData($data[0]);
				return $obj;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	
	//controlla se esiste un utente con l'email richiesta. Se valorizzato $id_user esclude quel codice utente nella verifica (utile per un update)
	public static function checkEmail($email,$id_user=NULL){
		//CONTROLLO CORRETTEZZA
		
		
		
		// CONTROLLO DUPLICATI
		$database = _obj('Database');
		$email = strtolower($email);
		if( $id_user ){
			$check = $database->select('*',self::TABLE,"email='{$email}' AND id_user <> {$id_user} AND deleted <> 1");
		}else{
			$check = $database->select('*',self::TABLE,"email='{$email}' AND deleted <> 1");
		}
		
		
		if( is_array($check) && count($check) > 0 ){
			return self::getError(USER_EMAIL_DUPLICATE);	
		}else{
			return 1;	
		}
	}
	
	
	//controlla se esiste un utente con l'username richiesta. Se valorizzato $id_user esclude quel codice utente nella verifica (valido per un update)
	public static function checkUsername($username,$id_user=NULL){
		
		//CONTROLLO CORRETTEZZA
		
		
		// CONTROLLO DUPLICATI
		$database = _obj('Database');
		$username = strtolower($username);
		
		if( $id_user ){
			$check = $database->select('*',self::TABLE,"username='{$username}' and id <> {$id_user} AND deleted <> 1");
		}else{
			$check = $database->select('*',self::TABLE,"username='{$username}' AND deleted <> 1");
		}
		
		if( is_array($check) && count($check) > 0 ){
			return self::getError(USER_USERNAME_DUPLICATE);	
		}else{
			return 1;	
		}
	}
	


	
	function loadPermissions(){
		$db = Marion::getDB();
		$list = $db->select('pe.*','profile_permission as pr join permission as pe on pe.id=pr.permission',"id_profile={$this->id_profile}");
		$this->auth_livelli = array('base');
		foreach($list as $v){
			$this->auth_livelli[] = $v['label'];
		}

		
		//debugga($list);exit;
	}


	

	public function afterLoad(){
		$this->loadPermissions();
	}

	//override del metodo della classe Base
	function beforeSave(){
		parent::beforeSave();
		if( !$this->auth ) $this->auth = 1;
		if( !$this->category ) $this->category = 1;
		if( !$this->id ){ 
			$this->deleted = 0;
			$this->dateInsert = date('Y-m-d H:i:s');
		}
		if( !$this->locale ){ 
			$this->locale = $GLOBALS['activelocale'];
		}
		$this->createToken();
		return $this;
	}




	//override del metodo della classe Base
	function checkSave(){
		$res = parent::checkSave();
		
		if( $res != 1 ){
			return $res;
		}
		//controllo dell'username ed eventualemnte di suoi duplicati
		if( $this->username ){
			$res = self::checkUsername($this->username,$this->id);
			
			if( $res !== 1 ) return $res;
		}else{
			return self::getError(USER_USERNAME_OBBLIGATORY);
			
		}
		
		//controllo dell'esistenza della mail ed eventualmente di suoi duplicati
		if( $this->email ){
			$res = self::checkEmail($this->email,$this->id);
			if( $res !== 1 ) return $res;
		}else{
			return self::getError(USER_EMAIL_OBBLIGATORY);
			
		}

		//controllo dell'esistenza della password
		if( !$this->password ){
			
			return self::getError(USER_PWD_OBBLIGATORY);
			
		}
		
		return $res;

	}

	//override del metodo della classe Base
	function afterSave(){
		parent::afterSave();
		//$this->getPermissions();
		
		return $this;
	}




	function checkPermission($livello){
		return in_array($livello,$this->auth_livelli);
	}

	function auth($livello){
		$livello = preg_replace('/( OR )/i'," || ",$livello);
		$livello = preg_replace('/( AND )/i'," && ",$livello);
		$condition = preg_replace_callback('/([a-zA-Z_-]+)/', 
			function ($matches) {
				if($this->checkPermission($matches[0])){
					return '1==1';
				}else{
					return '1==0';	
				}
			},$livello);

		$condition = "return (".$condition.");";
		
		return eval($condition);
		
	}


	//funzioni di autorizzazione
	function authUser(){
		return $this->auth('base');
	}


	//identifica un utente che non ha almeno qualche permesso oltre a quello base
	function authAdminUser(){
		
		if( count($this->auth_livelli) > 1){
			return true;
		}else{
			return false;
		}
	}

	function authSuperAdmin(){
		return $this->auth('superadmin');
	}

	//crea la chiave di attivazione dell'account
	function buildKeyActivation(){
		if( $this->id ){
			$serialize = array(
				'username' => trim($this->username),
				'password' => trim($this->password),
				'id'=> trim($this->id)
			);			
			return base64_encode(serialize($serialize));
		}
		return false;
	}
	
	//attiva un account a partire dalla chiave di ingresso
	public static function active($username,$password,$key){
		if($username && $password && $key){
			$database = _obj('Database');
			$check = $database->select('*',self::TABLE,"username='{$username}' AND password='{$password}' AND (deleted IS NULL OR deleted = 0 )");
			
			if( okArray($check) ){
				$user = self::withData($check[0]);
				
				$key_check = $user->buildKeyActivation();
				//debugga($key);
				//debugga($key_check);exit;
				
				if( $key == $key_check ){
					$user->active = 1;
					$user->save();
					return $user;
				}
			}
			
		}
		
		return false;
		


	}

	//metodo che crea il token dell'utente. Il token viene utilizzato per il login automatico all'applicativo
	function createToken($temporary = false){
		$username = $this->username;
		$password = $this->password;
		if( $temporary ){
			$time = time();
			$token = base64_encode($username."||".$password."||".$time);
		}else{
			$token = base64_encode($username."||".$password);
		}
		
		$this->token = Marion::encrypt($token);

	}


	//funzione che restituisce a partire da un codice di errore la sua etichetta
	public static function getError($error){
		if((int)$error > 0){
			switch($error){
				case USER_USERNAME_DUPLICATE:
					$messaggio = "username_duplicate";
					break;
				case USER_EMAIL_DUPLICATE:
					$messaggio = "email_duplicate";
					break;
				case USER_USERNAME_OR_PASSWORD_INVALID:
					$messaggio = "username_or_password_not_valid";
					break;
				case USER_EMAIL_OBBLIGATORY:
					$messaggio = "email_empty";
					break;
				case USER_USERNAME_OBBLIGATORY:
					$messaggio = "password_empty";
					break;
				case USER_NOT_ACTIVE:
					$messaggio = "user_not_active";
					break;
				case USER_EXPIRED_SESSION:
					$messaggio = "user_expired_session";
					break;
				case USER_PWD_OBBLIGATORY:
					$messaggio = "user_pwd_mandatory";
					break;
			}
			return $messaggio;
			
		}else{
			return false;
		}

	}

	//override metodo della classe Base
	public function prepareForm($locale='all'){
		$data = parent::prepareForm();
		return $data;
		
	}


	//restituisce i dati relativi ad un carrello
	function getDataCart(){
		foreach($this as $k => $v){
			$data[$k] = $v;
		}
		unset($data['id']);
		unset($data['auth_livelli']);
		unset($data['locked']);
		unset($data['_columns']);
		unset($data['active']);
		unset($data['deleted']);
		
		return $data;
	}

	

	

}







?>