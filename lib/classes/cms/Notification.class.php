<?php

class Notification extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'notification'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'notificationLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'notification';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 

	const LIMIT_TRUNCATE = 100; // limite troncamento notifica

	 function getTimeFromNow(){
		$time = strtotime($this->date);
		$now = time();
		$diff = $now -$time;
		$interval  = abs($diff);
		$minutes   = round($interval / 60);

		if( $minutes > 60 ){
			$hours = (int)($minutes / 60);
			if( $hours > 24 ){
				$day = (int)($hours / 24);
				return $day." ".__('days_notification');
			}else{
				return $hours." ".__('hours_notification');
			}	
		}else{
			return $minutes." ".__('minutes_notification');
		}
	 }


	 function getPriorityHtml(){
		switch($this->priority){
			case 1:
				$color= 'red';
				break;
			case 2:
				$color= 'orange';
				break;
			case 3:
				$color= 'black';
				break;
			case 4:
				$color= 'green';
				break;
			

		}

		return $color;
	 }

	 function getIconHtml(){
		switch($this->type){
			case 'new_order':
				$icon= 'fa-shopping-cart';
				break;
			case 'new_user':
				$icon= 'fa-user';
				break;
			default:
				$icon= 'fa-envelope';
		}

		return $icon;
	 }

	 function getTruncateText($locale=NULL,$limit=NULL){
		$string = $this->get('text',$locale);
		
		$string = strip_tags($string);
		if( !$limit ) $limit = STATIC::LIMIT_TRUNCATE;
		if (strlen($string) > $limit) {

			// truncate string
			$stringCut = substr($string, 0,$limit);

			// make sure it ends in a word so assassinate doesn't become ass...
			$string = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
		}
		 return $string;

	 }
	

	function getDetailUrl(){
		if( $this->custom ){
			switch($this->type){
				case 'new_order':
					$url= "index.php?ctrl=OrderAdmin&mod=ecommerce&action=edit&id{$this->custom}";
					break;
				case 'new_user':
					$url= "index.php?ctrl=UserAdmin&action=edit&id={$this->custom}";
					break;
				case 'new_email':
					$url= "index.php?ctrl=NotificationAdmin&action=view_mail&id={$this->custom}";
					break;
				default:
					$url= "/admin/admin.php?action=notifications&id={$this->id}";
			}
		}else{
			$url= "/admin/admin.php?action=notifications&id={$this->id}";
		}

		return $url;
	}



	 function beforeSave(){
		if( !$this->id ){
			$this->view = 0;
			$this->date = date('Y-m-d H:i:s');
		}
	 }


	 public static function newOrder($cart){
		if(!is_object($cart)) return false;
		$locales = getConfig('locale','supportati');
		$total = $cart->getTotalFinalFormatted();
			
		$localeData = array();
		foreach($locales as $loc){
			$parameter = array(
				$total,
				$cart->getNamePaymentMethod($loc),
				$cart->name,
				$cart->surname,
			);
			$localeData[$loc]['text'] = __('new_order_notification',$loc,$parameter);
		}
		$priority = 2;
		if( $cart->status == 'confirmed' || $cart->status == 'sent'){
			$priority = 4;
		}
		$users = User::prepareQuery()->where('auth',1,'<>')->get();

		$current_user = Marion::getUser();
		foreach($users as $u){
			
			if( $u->auth('admin') || $u->auth('ecommerce') ){
				self::create()->set(
					array( 
						'receiver' => $u->id,
						'sender' => $current_user->id,
						'type' => 'new_order',
						'priority' => $priority,
						'custom' => $cart->id,
					)
				)->setDataFromArray($localeData)
				->save();
			}
		}
		return true;
		
	 }


	 public static function newUser($user){
		if(!is_object($user)) return false;
		$locales = getConfig('locale','supportati');
		
		
		$localeData = array();
		$parameter = array(
				$user->name,
				$user->surname,
			);
		foreach($locales as $loc){
			$localeData[$loc]['text'] = __('new_user_notification',$loc,$parameter);
		}
		
		$users = User::prepareQuery()->where('auth',1,'<>')->get();
		$current_user = Marion::getUser();
		foreach($users as $u){
			
			if( $u->auth('admin') || $u->auth('user_management') ){
				$notification = self::create()->set(
					array( 
						'receiver' => $u->id,
						'sender' => $current_user->id,
						'type' => 'new_user',
						'priority' => 4,
						'custom' => $user->id,
					)
				)->setDataFromArray($localeData)
				->save();
			}
		}
		return true;
		
	 }
	

	public static function getCount($user_id=NULL){
		
		if( !$user_id ) {
			$user = Marion::getUser();
			if($user){
				$user_id = $user->id;
			}
		}
		$database = _obj('Database');
		$count = $database->select('count(*) as cont','notification',"receiver={$user_id} AND view=0");
		return $count[0]['cont'];
	}

	 

	
	
}


?>