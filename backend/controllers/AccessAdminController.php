<?php
use Marion\Core\Marion;
use Marion\Entities\User;
class AccessAdminController extends \Marion\Controllers\Controller{
    public $_auth = '';
	public $_required_access = false;



	
	function display()
	{
		$action = $this->getAction();
		switch($action){
			case 'logout':
				$this->logout();
				break;
		}
	}


	function logout(){
		Marion::logout();
		header('Location: index.php');
		//$this->login();
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
					'error' => $GLOBALS['gettext']->strings[$res]
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