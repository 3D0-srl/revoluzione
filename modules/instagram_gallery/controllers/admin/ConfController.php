<?php
class ConfController extends ModuleController{
	public $_auth = 'cms';
	public $_twig = true;
	public $_form_control = 'instagram_gallery_setting';



	

	function display(){
		$this->setMenu('manage_modules');
		$database = _obj('Database');

		$action = $this->getAction();

		if( $action == 'reset_token' ){
			Marion::delConfig('instagram_gallery','access_token');
			Marion::refresh_config();
		}

		$url_redirect = "https://".Marion::getConfig('generale','baseurl').'/instagram-gallery';
		$this->setVar('url_redirect',$url_redirect);
		
		$settings = Marion::getConfig('instagram_gallery', 'access_token');

		//debugga($settings);exit;

		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			$array = $this->checkDataForm($this->_form_control,$dati);
			if( $array[0] == 'ok'){
				unset($array[0]);
				
				unset($array[0]);
				foreach($array as $k => $v){
					Marion::setConfig('instagram_gallery',$k,$v);
				}
				Marion::refresh_config();
				
				$this->getToken($array['client_id'],$url_redirect,$array['client_secret']);
			}else{
				$this->errors[]= $array[1];
			}
			
			
		}else{
			$dati = Marion::getConfig('instagram_gallery');
			
		}

		
		if( $dati['access_token'] ){
			$this->setVar('access_token',$dati['access_token']);
		}

		if( $dati['client_id'] ){
			$this->setVar('client_id',$dati['client_id']);
		}

		if( $dati['user_id'] ){
			$this->setVar('user_id',$dati['user_id']);
		}

		if( $dati['client_secret'] ){
			$this->setVar('client_secret',$dati['client_secret']);
		}

		$dataform = $this->getDataForm($this->_form_control,$dati);
		
		$this->setVar('dataform',$dataform);

		$this->output('conf.htm');
	}


	

	function getToken($client_id,$url_redirect,$client_secret){
		$client_id = trim($client_id);
		$_SESSION['instagram_config']['client_id'] = $client_id;
		$_SESSION['instagram_config']['url_redirect'] = $url_redirect;
		$_SESSION['instagram_config']['client_secret'] = $client_secret;

		$url = "https://api.instagram.com/oauth/authorize?client_id={$client_id}&redirect_uri={$url_redirect}&scope=user_profile,user_media&response_type=code";
		//$url = "http://instagram.com/oauth/authorize/?client_id={$client_id}&redirect_uri={$url_redirect}&response_type=token&scope=basic";

		header('Location:'.$url);
	}

}



?>