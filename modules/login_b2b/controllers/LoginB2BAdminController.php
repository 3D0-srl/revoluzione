<?php
class LoginB2BAdminController extends ModuleController{
	public $_auth = 'cms';
	

	



	function display(){

		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			

			$array = check_form($formdata,'module_login_b2b');
			if( $array[0] == 'ok'){
				unset($array[0]);
				foreach($array as $k=>$v){
					Marion::setConfig('login_b2b',$k,$v);
				}
				$this->displayMessage('Impostazioni salvate con successo','success');
			}
			Marion::refresh_config();
		}else{
			$array = Marion::getConfig('login_b2b');
		}
		
		debugga('qua');exit;
		get_form($elements,'module_login_b2b','',$array);
		//$this->setMenu('filtri_ricerca_proprieta');
		//$this->setMenu('filtri_ricerca_proprieta');


		$this->output('conf.htm',$elements);
	}



	

	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Caratteristica salvata con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Caratteristica eliminata con successo','success');
		}
	}


}



?>