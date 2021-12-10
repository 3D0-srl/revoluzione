<?php
use Marion\Controllers\ModuleController;
use Marion\Core\Marion;
class ConfController extends ModuleController{
	public $_auth = 'cms';


		function display(){
			

			if( $this->isSubmitted()){
				$dati = $this->getFormdata();

				$array = $this->checkDataForm('social_login_setting',$dati);
				if( $array[0] == 'ok' ){
					foreach($array as $k => $v){
						Marion::setConfig('social_login',$k,$v);
					}
					Marion::refresh_config();
					$this->displayMessage('Dati salvati con successo!');
				}else{
					$this->errors[] = $array[1];
				}
			}else{
				$dati = Marion::getConfig('social_login');
			}

			$dataform = $this->getDataForm('social_login_setting',$dati);
			$this->setVar('dataform',$dataform);
			$this->output('conf.htm');
		}
	}


?>