<?php
use Marion\Controllers\FrontendController;
use Marion\Core\Marion;
class IndexController extends FrontEndController{

	

	function display(){
			
			$action = $this->getAction();

			switch($action){
				case 'login_facebook':
					$this->facebook();
					break;
				case 'login_google':
					$this->google();
					break;
			}
	}
	


	function showError($errore){
		$this->error = $errore;
		$this->output('errore.htm');
	}


	function facebook(){
		if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){
			$_protocollo = $_SERVER['HTTP_X_FORWARDED_PROTO'];
		}else{
			$_protocollo = !empty($_SERVER['HTTPS']) ? "https" : "http";
		}

		if( !Marion::getConfig('social_login','enable_facebook') ){
			$this->showError(308);
		}
		$redirect = _var('redirect');
		if( $redirect ){
			$_SESSION['redirect_fater_login_facebook'] = $redirect;
		}
		$facebook_id = Marion::getConfig('social_login','facebook_id');
		$facebook_secret = Marion::getConfig('social_login','facebook_secret');

		if( $facebook_id && $facebook_secret ){
			require_once( _MARION_MODULE_DIR_."social_login/hybridauth/hybridauth/Hybrid/Auth.php" );
			require_once(_MARION_MODULE_DIR_.'social_login/hybridauth/hybridauth/Hybrid/thirdparty/Facebook/autoload.php');
			
			$config = array(
			   // "base_url" the url that point to HybridAuth Endpoint (where the index.php and config.php are found)
			   "base_url" => $_protocollo."://".$_SERVER['SERVER_NAME']._MARION_BASE_URL_."modules/social_login/hybridauth/hybridauth/",
			 
			   "providers" => array (
					"Facebook" => array ( // 'id' is your facebook application id
					   "enabled" => true,
					   "keys" => array ( "id" => $facebook_id, "secret" => $facebook_secret ),
					   "scope" => "email, public_profile",//, user_birthday, user_hometown" // optional
					   "display" => "popup" // optional
					),
			   ),
			   //"debug_mode" => true ,
			   //"debug_file" => "/home/festivalvolontar/log/social.log",
			);

	



			
			try{
				$hybridauth = new Hybrid_Auth( $config );

				$adapter = $hybridauth->authenticate( "Facebook" );
				
				$user_profile = $adapter->getUserProfile();
			

				
				
				if( is_object($user_profile) ){
					$database = Marion::getDB();
					$check = $database->select('*','social_login_user',"id_facebook='{$user_profile->identifier}'");
					
					if( okArray($check) ){
						$id_user = $check[0]['id_user'];
						$user = User::withId($id_user);
						if( !is_object($user) || $user->deleted){
							$database->delete('social_login_user',"id_facebook='{$user_profile->identifier}'");
							unset($check);
						}
					}
					if( okArray($check) &&  is_object($user)){
						Marion::setUser($user);
						Marion::do_action('action_after_login');
					
						if( $_SESSION['redirect_fater_login_facebook'] ){
							header('Location: '.$_SESSION['redirect_fater_login_facebook']);
						}else{
							header('Location: '._MARION_BASE_URL_.'account/home.htm');
						}
					}else{
						$image = ImageComposed::fromUrl($user_profile->photoURL,'avatar.png');
						if( is_object($image) ){
							$image->save();
							$id_image = $image->getId();
						}

						$user = User::withEmail($user_profile->email);
						if( is_object($user) && !$user->deleted ){
							if( !$user->active){
								$user->active = 1;
								$user->save();
							}
							
							$check = $user;
						}else{
						
							$user = User::create();
							$user->set(
								array(
								'name' => $user_profile->firstName,
								'surname' => $user_profile->lastName,
								'email' => $user_profile->email,
								'username' => $user_profile->identifier,
								'password' => Marion::randomString(),
								'active' => 1,
								'image' => $id_image
								)	
							);
							$check = $user->save();
						}
						if( is_object($check) ){
							$toinsert = array(
								'id_user' => $check->id,
								'id_facebook' => $user_profile->identifier
							);
							$database->insert('social_login_user',$toinsert);
							Marion::setUser($check);
							Marion::do_action('action_after_login');
							if( $_SESSION['redirect_fater_login_facebook'] ){
							header('Location: '.$_SESSION['redirect_fater_login_facebook']);
						}else{
							header('Location: '._MARION_BASE_URL_.'account/home.htm');
						}
						}else{
							$this->showError($check);
							
						}
					}
				}
				
			}catch(Exception $e ){
				$message = $e->getMessage();
				$this->showError($message);
				
			}
			
			
		}else{
			$this->showError(209);
		}


	}





	function google(){
		if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){
			$_protocollo = $_SERVER['HTTP_X_FORWARDED_PROTO'];
		}else{
			$_protocollo = !empty($_SERVER['HTTPS']) ? "https" : "http";
		}

		if( !Marion::getConfig('social_login','enable_google') ){
			$this->showError(308);
		}
		$redirect = _var('redirect');
		if( $redirect ){
			$_SESSION['redirect_fater_login_google'] = $redirect;
		}
		$google_id = Marion::getConfig('social_login','google_id');
		$google_secret = Marion::getConfig('social_login','google_secret');

		if( $google_id && $google_secret ){
			//debugga($_SERVER);exit;
			require_once( _MARION_MODULE_DIR_."social_login/hybridauth/hybridauth/Hybrid/Auth.php" );
			
			$config = array(
			   // "base_url" the url that point to HybridAuth Endpoint (where the index.php and config.php are found)
			   "base_url" => $_protocollo."://".$_SERVER['SERVER_NAME']."/modules/social_login/hybridauth/hybridauth/",
			 
			   "providers" => array (
					"Google" => array ( // 'id' is your facebook application id
					   "enabled" => true,
					   "keys" => array ( "id" => $google_id, "secret" => $google_secret ),
					   "scope" => "https://www.googleapis.com/auth/userinfo.profile "."https://www.googleapis.com/auth/userinfo.email",//, user_birthday, user_hometown" // optional
						//"access_type"     => "offline",   // optional
						//"approval_prompt" => "force",     // optional
					   //"display" => "popup" // optional
					),
			   ),
			   //"debug_mode" => true ,
			   //"debug_file" => "/home/festivalvolontar/log/social.log",
			);

		
			
			try{
				$hybridauth = new Hybrid_Auth( $config );

				$adapter = $hybridauth->authenticate( "Google" );
				$user_profile = $adapter->getUserProfile();
				//controllo esistenza 

				
				//debugga($user_profile);exit;
				if( is_object($user_profile) ){
					$database = Marion::getDB();
					$check = $database->select('*','social_login_user',"id_google='{$user_profile->identifier}'");
					
					if( okArray($check) ){
						$id_user = $check[0]['id_user'];
						$user = User::withId($id_user);
						if( !is_object($user) || $user->deleted ){
							$database->delete('social_login_user',"id_google='{$user_profile->identifier}'");
							unset($check);
						}
					}
					if( okArray($check) &&  is_object($user)){
						Marion::setUser($user);
						Marion::do_action('action_after_login');
						

						if( $_SESSION['redirect_fater_login_google'] ){
							header('Location: '.$_SESSION['redirect_fater_login_google']);
						}else{
							header('Location: '._MARION_BASE_URL_.'account/home.htm');
						}
						
					}else{
						$image = ImageComposed::fromUrl($user_profile->photoURL,'avatar.png');
						if( is_object($image) ){
							$image->save();
							$id_image = $image->getId();
						}

						$user = User::withEmail($user_profile->email);
						if( is_object($user) && !$user->deleted ){
							if( !$user->active){
								$user->active = 1;
								$user->save();
							}
							
							$check = $user;
						}else{
							$user = User::create();
							$user->set(
								array(
								'name' => $user_profile->firstName,
								'surname' => $user_profile->lastName,
								'email' => $user_profile->email,
								'username' => $user_profile->identifier,
								'password' => Marion::randomString(),
								'active' => 1,
								'image' => $id_image
								)	
							);
							//debugga($user);exit;
							$check = $user->save();
						}
						
						if( is_object($check) ){
							$toinsert = array(
								'id_user' => $check->id,
								'id_google' => $user_profile->identifier
							);
							$database->insert('social_login_user',$toinsert);
							Marion::setUser($check);
							Marion::do_action('action_after_login');

							if( $_SESSION['redirect_fater_login_google'] ){
								header('Location: '.$_SESSION['redirect_fater_login_google']);
							}else{
								header('Location: '._MARION_BASE_URL_.'account/home.htm');
							}
						}else{
							$this->showError($check);
						}
					}
				}
				
			}catch(Exception $e ){
				header('Location: index.php');
			}
			exit;
			
		}else{
			$this->showError(209);
		}

	}
}


?>