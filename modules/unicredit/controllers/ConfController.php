<?php
class ConfController extends ModuleController{
	public $_auth = 'ecommerce';
	public $_twig = true;

	

	function display(){
		$this->setMenu('manage_modules');
		

		
		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('unicredit_conf',$dati);


			if($array[0] == 'ok'){
				unset($array[0]);
				foreach($array as $k => $v){
					Marion::setConfig('unicredit_module',$k,$v);
				}
			
				Marion::refresh_config();
				$this->displayMessage('Impostazioni salvate con successo!');
			}else{
				$this->errors[] = $array[1];
			}

		}else{
			$dati = Marion::getConfig('unicredit_module');
		}

		$dataform = $this->getDataForm('unicredit_conf',$dati);
		$this->setVar('dataform',$dataform);


		$this->output('conf.htm');
	}


	function array_unicredit_status_confirmed(){
		$status_avaiables = CartStatus::prepareQuery()->where('active',1)->where('visibility',1)->orderBy('orderView')->where('label','active','<>')->where('deleted','active','<>')->get();
		foreach($status_avaiables as $v){
			$toreturn[$v->label] = $v->get('name');
		}

		return $toreturn;
	}


}



?>