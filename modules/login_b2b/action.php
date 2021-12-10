<?php
use \Marion\Core\Marion;

function login_b2b_init(&$cart=NULL){
		
		$scludi_url = array(
			
		);

		if( !authUser() && !preg_match('/modules\/login_b2b/',$_SERVER['REQUEST_URI'])  && !preg_match('/backend\//',$_SERVER['REQUEST_URI']) ){
			$dati = Marion::getConfig('login_b2b');
			

			$widget = new WidgetComponent('login_b2b');
			//$widget = Marion::widget('login_b2b');
			
			
			$action = _var('action');

			$widget->setVar('return_url',$dati['return_url']);
			$widget->setVar('show_lost_pass', $dati['show_lost_pass']);
			
			switch($action){
				case 'lost_pwd_ok':
					$widget->output('lost_pwd_ok.htm');
					break;
				case 'lost_pwd':
					$widget->output('lost_pwd.htm');
					break;
				case 'register':
					//$elements = get_form($elements,'user');
					//$widget->output('register.htm',$elements);
					break;
				default:
					$widget->output('login.htm');
					break;
			}
			exit;
		}
	
}



Marion::add_action('init','login_b2b_init');







?>