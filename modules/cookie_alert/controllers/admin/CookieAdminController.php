<?php
use Marion\Controllers\ModuleController;
use CookieAlert\Cookie;
class CookieAdminController extends ModuleController{
	public $_auth = 'cms';

	

	function display(){
		$action = $this->getAction();
		$this->setMenu('manage_modules');
		


		if( $this->isSubmitted()){
			$dati = $this->getFormdata();


			//$array = check_form2($formdata,'module_cookie_alert');
			$array = $this->checkDataForm('module_cookie_alert',$dati);
			if( $array[0] == 'ok'){ 
				$cookieAlert = Cookie::prepareQuery()->getOne();
				if( !is_object($cookieAlert) ){
					$cookieAlert = Cookie::create();
				}
				$cookieAlert->set($array);
				$cookieAlert->save();
				$this->displayMessage('Configurazione salavata con successo');
				
			}else{
				$this->errors[] = $array[1];
			}
			
		}else{
			$obj = Cookie::prepareQuery()->getOne();
			if( is_object($obj) ){
				$dati = $obj->prepareForm2();
			}
		}
		

		$dataform = $this->getDataform('module_cookie_alert',$dati);
		//get_form2($elements,'module_cookie_alert','',$dati);
		
		$this->setVar('dataform',$dataform);
		$this->output('conf.htm');

		
	}

	


	


}



?>