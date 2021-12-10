<?php
use Marion\Core\Marion;
class CmsController extends \Marion\Controllers\Controller{
	public $_auth = 'cms_page';
	
	
	

	function display(){
		
		$action = $this->getAction();
		switch($action){
			case 'logo':
				$this->displayFormLogo();
				break;

		}
		

		
	}

	function displayFormLogo(){
			$this->setMenu('logo');
			
			$type = _var('type');

			
			
			$campi_aggiuntivi = array();
			
			$action = $this->getAction();




			if(	$this->isSubmitted()){
				


				$dati = $this->getFormdata();
				
				$array = $this->checkDataForm('cms_logo',$dati,$campi_aggiuntivi);
				
				if( $array[0] == 'ok'){
					
					Marion::setConfig('cms_setting','logo',$array['image']);
					$this->displayMessage('Logo salvato con successo!');
				}else{
					$this->errors[] = $array[1];
					

				}
				

			}else{
				
				$dati['image'] = Marion::getConfig('cms_setting','logo');
			}
			
			$dataform = $this->getDataForm('cms_logo',$dati);
			
			$this->setVar('dataform',$dataform);
			$this->output('form_logo.htm');	

			
			
		

	}


}



?>