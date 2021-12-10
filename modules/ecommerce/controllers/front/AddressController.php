<?php
use Marion\Controllers\BackendController;
use Marion\Core\Marion;
use Shop\Address;
use Marion\Entities\Country;
class AddressController extends BackendController{
		

		function setMedia(){
			parent::setMedia();
			$this->registerCSS('modules/ecommerce/css/backend_ecommerce.css');
			$this->registerCSS('modules/ecommerce/css/backend_shipping.css');
			$this->registerJS('modules/ecommerce/js/backend_shipping.js');
			$this->registerCSS('plugins/sweetalert/sweetalert.css');
			$this->registerJS('plugins/sweetalert/sweetalert.min.js');
			$this->registerJS('backend/js/function.js');
		}

		

		function index(){
			$this->setMenu('ecommerce_addresses');
			$user = Marion::getUser();
			$message = _translate('confirm_delete_addres','ecommerce');
			$list = Address::prepareQuery()->where('id_user',$user->id)->get();
			if( okArray($list) ){
				foreach($list as $v){
					$v->cofirm_delete_message = sprintf($message,$v->label);
				}
			}
			$this->setVar('list',$list);
		

			$this->output('address/list.htm');
		}

		function add($id=null){
			$this->setMenu('ecommerce_addresses');
				
			if( (int)$id ){
				$address = Address::withId($id);
				if( is_object($address) ){
					$data = $address->prepareForm();
				}
			}
			$dataform = $this->getDataForm('cart_address',$data);
			$this->setVar('dataform',$dataform);
			
			$this->output('address/form.htm');
		}

	


		function ajax(){
			switch($this->getAction()){
				case 'save':
					$formdata = $this->getFormdata();

					if( $formdata['country'] != 'IT' ){
						$campi_modificati['postalCode']['tipo'] = '';
						$campi_modificati['postalCode']['checklunghezza'] = 0;
						//unset($formdata['province']);
						$campi_modificati['province']['obbligatorio'] = 0;
					}
					$array = $this->checkdataForm('cart_address',$formdata,$campi_modificati);
					if( $array[0] == 'ok'){
						$user = Marion::getUser();
						$array['id_user'] = $user->id;
						$obj = Address::create();
						$obj->set($array);
						$obj->save();
						if( $formdata['default_address'] ){
							$user = Marion::getUser();
							$user->default_address = $obj->id;
							$user->save();
						}
						$risposta = array(
							'result' => 'ok',
						);
					}else{
						$risposta = array(
							'result' => 'nak',
							'error' => $array[1],
							'field' => $array[2]
						);
					}
					
					break;
				case 'delete':
					$id = _var('id');
	
					if( (int)$id ){
						
						$address = Address::withId($id);
						if( is_object($address) ){
							$address->delete();
							$risposta = array(
								'result' => 'ok'
							);
						}else{
							$risposta = array(
								'result' => 'nak',
								'error' => 'empty_address'
							);

						}
					}else{
						$risposta = array(
								'result' => 'nak',
								'error' => 'empty_address'
							);
					}
					break;
		}
		echo json_encode($risposta);
	}

		
	//FUNZIONI FORM
	function array_province(){
		$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$database = Marion::getDB();
		$prov = $database->select('sigla','provincia','','sigla ASC');
		if( okArray($prov) ){			
			foreach($prov as $v){
				$toreturn[$v['sigla']] = $v['sigla'];
			}
		}
		
		return $toreturn;
	}
	
	function array_nazioni_spedizione(){
		//$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$nazioni = Country::getAll();
		foreach($nazioni as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		return $toreturn;
	}
}