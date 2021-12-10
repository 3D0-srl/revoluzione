<?php
use Marion\Controllers\ModuleController;
use Marion\Core\Marion;
use Shop\UserCategory;
use Shop\ShippingMethod;
use Shop\ShippingArea;
class ShippingSettingAdminController extends ModuleController{
	public $_auth = 'ecommerce';
	
	function setMedia(){
		$this->loadJS('multiselect');
	}

	function display(){
		$this->setMenu('conf_shipping');
		
		
		if( $this->isSubmitted()){
				
			
			$formdata = $this->getFormdata();
			if( $formdata['enableFreeShipping'] ){
				$campi_aggiuntivi['thresholdFreeShipping']['obbligatorio'] = 1;
			}
			//$array = check_form($formdata,'config_shipping',$campi_aggiuntivi);
			$array = $this->checkDataForm('config_shipping',$formdata,$campi_aggiuntivi);
			if($array[0] == 'ok'){
				  foreach($array as $k => $v){
					Marion::setConfig('eshop',$k,$v);
				  }
				  Marion::refresh_config();

				 $this->displayMessage('Configurazione salvata con successo');
			}else{
				$this->error_fields[] = $array[2];
				$this->errors[] = $array[1];
				
			}
			$dati = $array;



		}else{
			$dati = Marion::getConfig('eshop');
		}
		
		
		
		$dataform = $this->getDataForm('config_shipping',$dati);
				
		$this->setVar('dataform',$dataform);
		
		
		$this->output('shipping_config.htm');
			
	}

	function array_userCategory(){
		$categorie = UserCategory::prepareQuery()->get();
		$select = array();
		foreach($categorie as $v){
			$select[$v->getId()] = $v->get('name');
		}
		return $select;
	}

	function array_shippingMethods(){
		$list = ShippingMethod::prepareQuery()->get();
		//$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		foreach($list as $v){
			$toreturn[$v->getId()] = $v->get('name');
		}
		return $toreturn;

	}


	
	function array_shippingAreas(){
		$list = ShippingArea::prepareQuery()->get();
		//$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		foreach($list as $v){
			$toreturn[$v->getId()] = $v->get('name');
		}
		return $toreturn;

	}

	



	

}



?>