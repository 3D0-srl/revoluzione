<?php

use Marion\Core\Marion;
use Marion\Controllers\Interfaces\TabAdminInterface;
use Marion\Controllers\ModuleController;
class EcommerceSettingAdminController extends ModuleController implements TabAdminInterface{
	public $_auth = 'ecommerce';
	
	
	public static function getTitleTab(){
		return _translate('general');
	}

	

	function display(){
		
		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			
			if( $dati['enableFreeShipping'] ){
				$campi_aggiuntivi['thresholdFreeShipping']['obbligatorio'] = 1;
			}
			if( $dati['enableInvoice'] ){
				$campi_aggiuntivi['nameInvoice']['obbligatorio'] = 1;
				$campi_aggiuntivi['logoInvoice']['obbligatorio'] = 1;
			}

			$array = $this->checkDataForm('config_eshop',$dati);
			//$array = check_form($formdata,'config_eshop',$campi_aggiuntivi);
			
			if( $array[0] == 'ok'){
				if( $array['startFreeShipping'] && $array['endFreeShipping'] && strtotime($array['startFreeShipping']) > strtotime($array['endFreeShipping']) ){
					$array[0] = 'nak';
					$array[1] = "La data di inizio periodo è successiva  a quella di fine";
				}
			}

			

			$campi_azienda = array('nome_azienda','ragione_sociale','partita_iva','capitale_sociale','indirizzo_azienda','telefono','fax','mail_contatti','banca_appoggio');
			
			if($array[0] == 'ok'){
				unset($array[0]);
				
				foreach($array as $k => $v){
					if( in_array($k,$campi_azienda) ){
						Marion::setConfig('azienda',$k,$v);
					}else{
						Marion::setConfig('eshop',$k,$v);
					}
				}

				Marion::refresh_config();

				$this->displayMessage(_translate('data_saved_successfully'),'success');
			}else{
				$this->errore[] = $array[1];
			}






			
			
		}else{

			$dati = Marion::getConfig('eshop');
			$dati_azienda = Marion::getConfig('azienda');
			$dati = array_merge($dati,$dati_azienda);
		}


		
		$dataform = $this->getDataForm('config_eshop',$dati);
		$this->setVar('dataform',$dataform);
		
		$this->output('tabs/setting_ecommerce.htm');
		
		
	}

}

?>