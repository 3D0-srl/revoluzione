<?php
class ConfController extends ModuleController{
	public $_auth = 'ecommerce';
	public $_twig = true;

	function setMedia(){
		$this->loadJS('multiselect');
	}

	function display(){
		$this->setMenu('manage_modules');

		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('module_cod',$dati);


			if($array[0] == 'ok'){
				unset($array[0]);
				foreach($array as $k => $v){
					Marion::setConfig('cod',$k,$v);
				}
			
				Marion::refresh_config();
				$this->displayMessage('Impostazioni salvate con successo!');
			}else{
				$this->errors[] = $array[1];
			}

		}else{
			$dati = Marion::getConfig('cod');
		}

		$dataform = $this->getDataForm('module_cod',$dati);
		$this->setVar('dataform',$dataform);


		$this->output('conf.htm');
	}


	function array_shippingMethods(){
		$list = ShippingMethod::prepareQuery()->get();
		//$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		foreach($list as $v){
			$toreturn[$v->getId()] = $v->get('name');
		}
		return $toreturn;

	}


}



?>