<?php
use Marion\Core\Marion;
use Marion\Controllers\Interfaces\TabAdminInterface;
use Marion\Controllers\ModuleController;
class CurrencyAdminController extends ModuleController implements TabAdminInterface{
	public $_auth = '';
	
	public static function getTitleTab(){
		return _translate('currencies');
	}
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Tassa salvata con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Tassa eliminata con successo','success');
		}
	}


	function display(){
		$this->setVar('tabIndex',_var('tabIndex'));
		$database = Marion::getDB();
		
		if( $this->isSubmitted()){
			
			//$formdata = $this->getFormdata();;
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('currency',$dati);
	

			//$array = check_form($formdata,'currency');
			if( $array[0] == 'ok'){
				foreach($dati['currencies'] as $k => $curr){
					if( $k == $array['defaultCurrency'] && !$curr['active'] ){
						$array[0] = 'nak';
						$array[1] = "Attivare e specificare per la valuta di default il tasso di cambio";
						$array[2] = "currencies";
						
						break;
					}
					if( $curr['active'] && !$curr['exchangeRate']){
						$array[0] = 'nak';
						$array[1] = "Specificare un tasso di conversione per le valute attive";
						$array[2] = "currencies";
						$this->setVar('code_curency', $k);
						break;
					}
				}

			}
			

			if( $array[0] == 'ok'){
				foreach($dati['currencies'] as $k => $v){

					if( $v['active'] ){
						$toupdate = array(
							'exchangeRate' => $v['exchangeRate'],
							'active' => 1,
							'defaultValue' => 0,
						);
					}else{
						$toupdate = array(
							'exchangeRate' => $v['exchangeRate'],
							'active' => 0,
							'defaultValue' => 0,
						);
					}
					if( $k == $array['defaultCurrency'] ){
						$toupdate['defaultValue'] = 1;
					}
					$database->update('currency',"code='{$k}'",$toupdate);
					
				}

				
				
				Marion::refresh_config();
				$this->displayMessage('Dati salvati con successo');
			}

		}

		$currencies = $database->select('*','currency',"1=1");
		
		if(okArray($currencies)){
			foreach($currencies as $v){
				$currencies_array[$v['code']] = $v;
				if( $v['defaultValue'] ){
					$dati['defaultCurrency'] = $v['code'];
				}
			}
		}
		
		$this->setVar('currencies',$currencies_array);
		
		
		$dataform = $this->getDataForm('currency',$dati);
		//get_form($elements,'currency','',$dati);
		$this->setVar('dataform',$dataform);
		
		$this->output('currencies.htm');

	}



	/*function ajax(){
		$action = $this->getAction();
		
		switch($action){
			case 'data_mail_status':
				$database = _obj('Database');
				$id_status = _var('id_status');
				$locale = _var('locale');
				$data = $database->select('*','cart_status_mail',"id_status={$id_status} AND locale='{$locale}'");
				
				ob_start();
				get_form($elements,'cart_status_mail',$action,$data[0]);
				$this->output('form_mail_state_order_fields.htm',$elements);
				$html = ob_get_contents();
				ob_end_clean();
				
				$risposta = array(
					'result' => 'ok',
					'html' => $html
				);


				break;
			case 'save_data_mail':
				$formdata = $this->getFormdata();
				

				$array = check_form($formdata,'cart_status_mail');
				$database = _obj('Database');
				if( $array[0] == 'ok'){
					unset($array[0]);
					$check = $database->select('*','cart_status_mail',"id_status={$array['id_status']} AND locale='{$array['locale']}'");
					if( okArray($check) ){
						$database->update('cart_status_mail',"id_status={$array['id_status']} AND locale='{$array['locale']}'",$array);	
					}else{
						$database->insert('cart_status_mail',$array);
					}
					//debugga($database->error);
					$risposta = array(
						'result' => 'ok',
						'message' => _translate('data_saved_successfully')
					);
				}else{
					$risposta = array(
						'result' => 'nak',
						'error' => $array[1]
					);
				}	
				
				break;
		}

		echo json_encode($risposta);
	
	}*/
	

	function currencies(){
		$database = Marion::getDB();
		$currencies = $database->select('*','currency',"1=1");
		foreach($currencies as $v){
			$toreturn[$v['code']] = $v['code'];
		}
		return $toreturn;
	}
	

}

?>