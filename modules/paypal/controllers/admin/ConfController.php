<?php
use Marion\Controllers\ModuleController;
use Marion\Core\Marion;
use Shop\{ShippingMethod,CartStatus};
class ConfController extends ModuleController{
	public $_auth = 'ecommerce';
	

	

	function display(){
		$this->setMenu('manage_modules');

		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			$campi_modificati = array();
			if( $dati['sandbox'] ){
				$campi_modificati['sandbox_client_id']['obbligatorio'] = 1;
				$campi_modificati['sandbox_client_secret']['obbligatorio'] = 1;
			}else{
				$campi_modificati['production_client_id']['obbligatorio'] = 1;
				$campi_modificati['production_client_secret']['obbligatorio'] = 1;
			}
			$array = $this->checkDataForm('paypal_conf',$dati,$campi_modificati);
			

			if($array[0] == 'ok'){
				unset($array[0]);
				foreach($array as $k => $v){
					if( is_array($v) ) $v = serialize($v);
					Marion::setConfig('paypal_module',$k,$v);
				}
			
				Marion::refresh_config();
				$this->displayMessage('Impostazioni salvate con successo!');
			}else{
				$this->errors[] = $array[1];
			}

		}else{
			$dati = Marion::getConfig('paypal_module');
		}
		

		$dataform = $this->getDataForm('paypal_conf',$dati);
		$this->setVar('dataform',$dataform);


		$this->output('conf.htm');
	}


	function array_paypal_status_confirmed(){
		$status_avaiables = CartStatus::prepareQuery()->where('active',1)->where('visibility',1)->orderBy('orderView')->where('label','active','<>')->where('deleted','active','<>')->get();
		foreach($status_avaiables as $v){
			$toreturn[$v->label] = $v->get('name');
		}

		return $toreturn;
	}


	function couriers(){
		$list = ShippingMethod::prepareQuery()->get();
		foreach($list as $v){
			$select[$v->id] = $v->get('name');
		}

		return $select;
	}

	function mandadoryAddressFields(){
		

		return array(
			'shippingPhone' => 'Telefono',
			'shippingCellular' => 'Cellulare',
			'shippingEmail' => 'Email'

		);
	}


}



?>