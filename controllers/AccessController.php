<?php
use Marion\Core\Marion;
use Marion\Entities\{User,Country};
use Marion\Entities\Cms\Notification;
class AccessController extends \Marion\Controllers\FrontendController{
	function display(){
		$action = $this->getAction();
		
		if( $this->isLogged() && $action != 'notauth' && $action != 'logout' ){
			$this->redirectToHome();
		}
		
		switch($action){
			case 'login':
				$this->login();
				break;
			case 'logout':
				$this->logout();
				break;
			case 'resetpwd':
				$this->resetPassword();
				break;
			case 'lostpwd':
				$this->lostPassword();
				break;
			case 'signup':
				$this->signup();
				break;
			case 'activation':				
				$this->activation();
				break;
			case 'notauth':
				$this->notauth();
				break;
			case 'success_reset_pwd':
				$this->successResetPWd();
				break;
		}

	}

	function redirectToHome(){
		if( authUser() ){
			header('Location: '._MARION_BASE_URL_.'index.php?ctrl=Home');
		}
		
	}

	function successResetPwd(){
		$message = _translate('Password modificata con successo!');
		$this->setVar('message',$message);
		$this->output('access/success_page.htm');
	}

	
	function logout(){
		Marion::logout();
		$this->login();
	}

	function notauth(){
		$this->output('access/not_auth.htm');
	}


	function login(){
		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('login',$dati);
			if($array[0] == 'ok'){
				$res = User::login($array['username'],$array['password']);
				
				if(is_object($res) ){
					Marion::setUser($res);

					Marion::do_action('action_after_login');
					
					
					$return_location = _var('return_location');
					if( Marion::getConfig('generale','redirect_admin_side') == 1 && authAdminUser()){
						header("Location: "._MARION_BASE_URL_."backend/index.php");
					}else{
						header("Location: "._MARION_BASE_URL_."{$GLOBALS['activelocale']}/account/home.htm");
					}
					
				}else{
					$this->errors[] = $GLOBALS['']->strings[$res];
					
				}
			}else{
				$this->errors[] = $array[1];
				
			}
		}
		$this->output('access/login.htm');
	}

	function lostPassword(){
		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('lostpass',$dati);
			if($array[0] == 'ok'){
				$user = User::prepareQuery()->where('email',$array['email'])->getOne();
				if(is_object($user)){
					$this->sendMailLostPassword($user);
					$this->displayMessage("Email di recupero inviata all'indirizzo ".$array['email'],'success');
				}else{
					
					$this->errors[] = __('no_user');
				}
			}else{
				$this->errors[] = $array[1];
				
			}


		}
		
		$dataform = $this->getDataForm('lostpass',$dati);
		$this->setVar('dataform',$dataform);
		$this->output('access/lostpwd.htm');
		
	}


	function checkTokenResetPassword($token){
		$dati = unserialize(base64_decode($token));
		if( okArray($dati) ){
			$time = $dati['time'];
			if($time){
				//controllo se è passata un'ora dalla richiesta di recupero password
				$diff = round(abs(time() - $time) / 60,2);
				if( $diff > 60 ){
					$this->error(_translate('TOKEN SCADUTO'));
				}
				
			}else{
				$this->error(_translate('ERRORE OPERAZIONE'));
			}
		}else{
			$this->error(_translate('ERRORE OPERAZIONE'));
		}
		return $dati;
	}


	function resetPassword(){


		if( $this->isSubmitted() ){
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('new_password',$dati);
		
			if($array[0] == 'ok'){
				$token = $dati['key'];
				$dati_token = $this->checkTokenResetPassword($token);
				if($array['password'] != $array['password_confirm'] ){
					$this->errors[] = _translate('Le password non coincidono');
				}else{
					$user = User::withId($dati_token['id_user']);
					if( is_object($user) ){
						$user->password = password_hash($array['password'], PASSWORD_DEFAULT); 
						$user->save();
						header('Location: '._MARION_BASE_URL_.'index.php?ctrl=Access&action=success_reset_pwd');
						
					}else{
						$this->errors[] = _translate('Utente non presente in archivio');
					}
				}
			}else{
				$this->errors[] = $array[1];
			}
			
		}else{

			$token = _var('key');
			$dati_token = $this->checkTokenResetPassword($token);



			
			$dati = array(
				'key' => $token
			);
		}

		

		$dataform = $this->getDataForm('new_password',$dati);
		
		$this->setVar('dataform',$dataform);
		$this->output('access/new_pwd.htm');
		
	}

	function signup(){
		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('user',$dati);

			
			if($array[0] == 'ok'){
				$user = User::create();
				$array['active'] = 0;
				$array['deleted'] = 0;

				$array['password'] = password_hash($array['password'], PASSWORD_DEFAULT); 

				$user->set($array);
				
				$res = $user->save();
				if(is_object($res)){
					Marion::do_action('action_user_registration',$user);
					$this->sendConfirmRegistration($res);
					$this->setVar('email',$res->email);
					$this->output('access/registration_successful.htm');
					exit;

				}else{
					$this->errors[] = $GLOBALS['']->strings[$res];
				}

			}else{
				$this->errors[] = $array[1];
				
			}


		}
		$dataform = $this->getDataForm('user',$dati);
		$this->setVar('dataform',$dataform);
		$this->output('access/signup.htm');
		
	}

	function activation(){

		$key = _var('key');

		if($key){ 
			$dati = unserialize(base64_decode($key));
			$dati['key'] = $key;
		}else{
			$this->error(_translate('INVALID_TOKEN'));
		}

		
		
		if( okArray($dati)){
			
			
			$array = $this->checkDataForm('activation',$dati);
			
			if($array[0] == 'ok'){

				$user = User::active($array['username'],$array['password'],$array['key']);
				
				if( is_object($user)){
						Marion::do_action('action_user_activation',$user);
						Marion::setUser($user);
						Marion::do_action('action_after_login');
						Notification::newUser($user);
						$this->sendMailNewUser($user);
						//debugga('qui');exit;
						$message = _translate("ACCOUNT_SUCCESSFULLY_ACTIVATED");
						$this->setVar('message',$message);
						$this->output('access/success_page.htm');
					//}
				}else{
					$this->error(_translate('USER_NOT_EXISTS'));
				}
				

			}else{
				$this->errors[] = $array[1];
				
			}


		}else{
			$this->error(_translate('INVALID_TOKEN'));
		}

		
		
	}



	// funzione che invia la mail di recupero password
	function sendMailLostPassword($user){
		
		$data = array(
			'id_user' => $user->id,
			'email' => $user->email,
			'time' => time()
		);
		$this->setVar('serialized', base64_encode(serialize($data)));
		$this->setVar('user',$user);


		//preparo l'html
		ob_start();
		$this->output('mail/mail_forgot_pwd.htm');
		$html = ob_get_contents();
		ob_end_clean();


		$generale = Marion::getConfig('generale');

		$subject = sprintf($GLOBALS['']->strings['subject_lostpass'],$generale['nomesito']);

		$mail = _obj('Mail');
		
		$mail->setHtml($html);
		$mail->setSubject($subject);
		
		
		$mail->setTo($user->email);
		$mail->setFrom($generale['mail']);
		
		$res = $mail->send();

		//debugga($res);exit;

		return $res;
	}

	// funzione che invia la mail di conferma iscrizione
	function sendConfirmRegistration($user){
		
		
		$generale = Marion::getConfig('generale');

		

		$array_activation = array(
				'username' => $user->username,
				'password' => $user->password,
				'key' => $user->buildKeyActivation(),

		);

		
		$this->setVar('user',$user);
		$this->setVar('serialized',base64_encode(serialize($array_activation)));
		
		//preparo l'html
		ob_start();
		$this->output('mail/mail_activation.htm');
		$html = ob_get_contents();
		ob_end_clean();

		
		$mail = _obj('Mail');
		$mail->setHtml($html);

		$subject = sprintf(_translate('registrazione_subject'),$generale['nomesito']);
		$mail->setSubject($subject);
		
		
		$mail->setTo($user->email);
		$mail->setFrom($generale['mail']);
		$res = $mail->send();

		return $res;

	}


	function sendMailNewUser($user){

	}


	



	//FUNZIONI FORM
	function array_province(){
		$toreturn = array( $GLOBALS['']->strings['seleziona'] );
		$database = Marion::getDB();
		$prov = $database->select('sigla','provincia','','sigla ASC');
		if( okArray($prov) ){			
			foreach($prov as $v){
				$toreturn[$v['sigla']] = $v['sigla'];
			}
		}
		
		return $toreturn;
	}
	function array_type_buyer(){
		$labels = array('private','company');
		foreach($labels as $label){
			$toreturn[$label] = __("type_buyer_".$label);
		}
		return $toreturn;
	}
	function array_nazioni(){
		//$toreturn = array( $GLOBALS['']->strings['seleziona'] );
		
		//getCountries
		$nazioni = Country::getAll();
		foreach($nazioni as $v){
		
			$toreturn[$v->id] = $v->get('name');
			
		}
		return $toreturn;
	}



	function ajax(){

		$action = $this->getAction();
		switch($action){
			case 'login':
				$response = $this->login_ajax();
			break;
		}

		echo json_encode($response);
	}


	function login_ajax(){
		$dati = $this->getFormdata();
		$array = $this->checkDataForm('login',$dati);
		if($array[0] == 'ok'){
			$res = User::login($array['username'],$array['password']);
			
			if(is_object($res) ){
				Marion::setUser($res);
				Marion::do_action('action_after_login');
				
				$response = array(
					'result' => 'ok'
				);
				
			}else{
				$response = array(
					'result' => 'nak',
					'error' => $GLOBALS['']->strings[$res]
				);
				
			}
		}else{
			$response = array(
				'result' => 'nak',
				'error' => $array[1]
			);
			
		}
		return $response;
	}
}


?>