<?php
require_once ('php_mailman.php'); 
class Mailman extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'mailman_list'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'mailman_listLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'mailman_list';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	

	public function checkSave(){
		$res = parent::checkSave();

		if( $res == 1 ){
			if( $this->default_list ){
				$query = self::prepareQuery()->where('default_list',1);
				if( $this->id ){
					$query->where('id',$this->id,'<>');
				}
				$check = $query->get();
				if( okArray($check) ){
					return "duplicate_list_default";
				}
			}
			return $res;
		}else{
			return $res;
		}


	}

	//metodo che prende i parametri da POST e GET
	public static function getData(){
		$data = $_GET;
		
		if( okArray($data) ){
			foreach($data as $k => $v){
				Marion::sessionize($k,$v);
			}
		}
		$data = $_POST;
		if( okArray($data) ){
			foreach($data as $k => $v){
				Marion::sessionize($k,$v);
			}
		}

	}
	
	public function getCountSubscribe(){
		$database = _obj('Database');
		$res = $database->select('count(*) as count', 'mailman_subscribe', "used = 1 and list={$this->id}");
		return $res[0]['count'];
	}

	
	
	//iscrizione della mail alla lista
	function subscribe($email){
		if( !$email ) return "email_empty";
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  return  "email_not_valid";
		}
		if( !$email ) return false;
		if( ! $this->isSubscibed($email) ){
			if( !is_object($this->mailman) ){
				$this->connect();
			}
			$database = _obj('Database');
			$toinsert = array(
				'email' => $email	
			);
			$database->insert('mail_subscribe_report',$toinsert);
			
			$res = $this->mailman->subscribe($email);
			if( $this->debugg ){
				debugga($res);
				exit;
			}
		}
		return true;
	}

	//iscrizione della mail alla lista
	function unsubscribe($email){
		if( !$email ) return "email_empty";
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  return  "email_not_valid";
		}
		if( !$email ) return false;
		if( $this->isSubscibed($email) ){
			if( !is_object($this->mailman) ){
				$this->connect();
			}
			$database = _obj('Database');
			$toinsert = array(
				'email' => $email	
			);
			$database->insert('mail_unsubscribe_report',$toinsert);
			
			$res = $this->mailman->unsubscribe($email);
			if( $this->debugg ){
				debugga($res);
				exit;
			}
		}
		return true;
	}
	

	//eliminazione della mail dalla lista
	function unsubscribe_admin($email){
		if( !$email ) return "email_empty";
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  return  "email_not_valid";
		}
		if( $this->isSubscibed($email) ){
			if( !is_object($this->mailman) ){
				$this->connect();
			}
			$res = $this->mailman->unsubscribe($email);
			$database = _obj('Database');
			$database->delete('mailman_subscribe',"email = '{$email}'");
			if( $this->debugg ){
				debugga($res);
				exit;
			}
			return true;
		}else{
			return "email_not_in_archive";
		}
		
		
		
	}


	//controlla se l'email è presente nel database
	function isSubscibed($email){
		$database = _obj('Database');
		$check = $database->select('*', 'mailman_subscribe', "email = '{$email}' AND used = 1 and list={$this->id}");
		return okArray($check);
	}

	//conferma una mail di iscrizione
	function confirmEmail($email,$auth){
		$database = _obj('Database');
		$check = $database->select('*', 'mailman_subscribe', "email = '{$email}' AND used = 0 and list={$this->id} and auth='{$auth}'");
		
		if( okArray($check) ){
			$check = $check[0];
			$this->subscribe($email);
			
			$database->update('mailman_subscribe',"email = '{$email}' AND used = 0 and list={$this->id} and auth='{$auth}'",array('used'=>1));
			//debugga('qua');exit;
			$database->delete('mailman_subscribe',"email = '{$email}' AND used = 0 and list={$this->id}");
			return true;
		}else{
			return false;
		}

		
	}



	function subscribe_admin($email){
		if( !$email ) return "email_empty";
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  return  "email_not_valid";
		}
		if( !$this->isSubscibed($email) ){
			$toinsert = array();
			$toinsert['used'] = 0;
			$toinsert['email'] = $email;
			$toinsert['dateInsert'] = date('Y-m-d H:i:s');
			$toinsert['list'] = $this->id;
			$toinsert['auth'] = substr(md5(time().$email),0,10);
			$toinsert['ip'] = $_SERVER['REMOTE_ADDR'];
			$database = _obj('Database');
			
			$database->insert('mailman_subscribe',$toinsert);
			
			$this->confirmEmail($email,$toinsert['auth']);
			return true;
		}else{
			return "email_in_archive";
		}
	}

	function getLocationFromIP(){
		 $location = json_decode(file_get_contents('http://ipinfo.io/'.$_SERVER['REMOTE_ADDR']));
		 //debugga($location);exit;
		 if( is_object($location) ){
			
			
			$data = array(
				'city' => $location->city,
				'region' => $location->region,
				'country' => $location->country,
				'postalCode' => $location->postal
			);
			$coord = preg_split('/,/',$location->loc);
			if( count($coord) == 2 ){
				$data['latitude'] = $coord[0];
				$data['longitude'] = $coord[1];
			}
			

			
		 }
		
		 return $data;
		 
	}

	function saveConfirmEmail($email,$registration=0){
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  return  "email_not_valid";
		}
		if( $this->isSubscibed($email) ){
			return 'email_in_archive';
		}
		$toinsert = array();
		$toinsert['used'] = 0;
		$toinsert['email'] = $email;
		$toinsert['dateInsert'] = date('Y-m-d H:i:s');
		$toinsert['list'] = $this->id;
		//$toinsert['registration'] = $registration;
		$toinsert['auth'] = substr(md5(time().$email),0,10);
		
		$toinsert['ip'] = $_SERVER['REMOTE_ADDR'];
		$location = $this->getLocationFromIP();
		if( okArray($location) ){
			$toinsert = array_merge($toinsert,$location);
		}

		
		
		$database = _obj('Database');
		$database->insert('mailman_subscribe',$toinsert);

		return $toinsert;
	}



	//invia una mail di conferma di iscrizione
	function sendConfirmEmail($email,$out_module=false){
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  return  "email_not_valid";
		}
		if( $this->isSubscibed($email) ){
			return 'email_in_archive';
		}
		$toinsert = $this->saveConfirmEmail($email);
		if( okArray($toinsert) ){
			$mail = _obj('Mail');
					
			//imposto i dati da stampare a video
			$tosend = array(
				'auth' => $toinsert['auth'],
				'email' => $toinsert['email'],
				'action' => 'subscribe',
				'list' => $this->id
				);
			$mail->dati['serialized'] = base64_encode(serialize($tosend));
			$mail->dati['subscribe'] = 1;
			$mail->dati['list_name_view'] = $this->list_name_view;
			//imposto i destinatari della mail
			$mail->setTo($email);
			
			//imposto il mittente
			$from = Marion::getConfig('module_mailman','email');
			$mail->setFrom($from);

			$nomesito = getConfig('generale','nomesito');
			
			$mail->setTemplateHtml('mail_newsletter.htm','mailman',$out_module);
			
			$subject = __module('mailman','confirm_subscribe_newsletter',NULL,array($this->list_name_view,$nomesito),$out_module);
			

			$mail->setSubject($subject);
			//debugga($mail);exit;
			$mail->send();
		}

		

		return true;
	}



	//invia una mail di conferma di iscrizione
	function sendConfirmRemove($email,$out_module=false){
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  return  "email_not_valid";
		}
		if( $this->isSubscibed($email) ){
			$database = _obj('Database');
			$data_subscribe = $database->select('*','mailman_subscribe',"email='{$email}' and list={$this->id}");
			$data_subscribe = $data_subscribe[0];
			
			$mail = _obj('Mail');
			
			//imposto i dati da stampare a video
			$tosend = array(
				'auth' => $data_subscribe['auth'],
				'email' => $data_subscribe['email'],
				'action' => 'unsubscribe',
				'list' => $this->id
				);
			$mail->dati['serialized'] = base64_encode(serialize($tosend));
			$mail->dati['unsubscribe'] = 1;
			$mail->dati['list_name_view'] = $this->list_name_view;
			//imposto i destinatari della mail
			$mail->setTo($email);
			
			//imposto il mittente
			$from = Marion::getConfig('module_mailman','email');
			$mail->setFrom($from);

			$nomesito = getConfig('generale','nomesito');
			
			$mail->setTemplateHtml('mail_newsletter.htm','mailman',$out_module);
			
			$subject = __module('mailman','confirm_unsubscribe_newsletter',NULL,array($this->list_name_view,$nomesito),$out_module);
			

			$mail->setSubject($subject);
			//debugga($mail);exit;
			$mail->send();

		}else{
			return "email_in_archive";
		}

		return true;
		
	}

	//conferma una mail di cancellazione
	function confirmEmailRemove($email,$auth){
		$database = _obj('Database');
		$check = $database->select('*', 'mailman_subscribe', "email = '{$email}' AND used = 1 and list={$this->id} and auth='{$auth}'");
		
		if( okArray($check) ){
			$check = $check[0];
			$this->unsubscribe($email);
			
			$database->delete('mailman_subscribe',"email = '{$email}' AND used = 1 and list={$this->id} and auth='{$auth}'");
			return true;
		}else{
			return false;
		}

		
	}

	//oggetto che si connette al servizio mailman
	function connect(){
		$mailman = new php_mailman($this->domain, $this->password);
		$mailman->set_list($this->name_list);
		$mailman->set_protocol($this->protocol);
		$mailman->set_language($GLOBALS['activelocale']);

		$this->mailman = $mailman;

	}



	function getSubscribers(){
		$database = _obj('Database');
		$emails = $database->select('*', 'mailman_subscribe', "list={$this->id} and used=1");
		
		return $emails;
	
	}









}



?>