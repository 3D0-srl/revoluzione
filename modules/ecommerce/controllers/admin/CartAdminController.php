<?php
use Marion\Core\Marion;
use Marion\Controllers\Interfaces\TabAdminInterface;
use Marion\Controllers\ModuleController;
class CartAdminController extends ModuleController implements TabAdminInterface{
	public $_auth = '';
	public static function getTitleTab(){
		return _translate('cart','ecommerce');
	}
	

	function display(){
		if( $this->isSubmitted()){
			$dati = $this->getFormdata();

			$array = $this->checkDataForm('cart_setting',$dati);
			if( $array[0] == 'ok'){
				unset($array[0]);
				
				foreach($array as $k => $v){
					Marion::setConfig('cart_setting',$k,$v);
				}
				Marion::refresh_config();
				$this->displayMessage('Configurazione salvata con successo!');
			}else{
				$this->errors[] = $array[1];	
			}
			
		}else{
			$dati = Marion::getConfig('cart_setting');
		}
		

		$dataform = $this->getDataForm('cart_setting',$dati);
		$this->setVar('dataform',$dataform);

		$this->output('tabs/setting_cart.htm');
	}

	

}

?>