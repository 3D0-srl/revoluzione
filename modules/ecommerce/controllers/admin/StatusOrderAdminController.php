<?php
use Marion\Controllers\AdminModuleController;
use Marion\Controllers\Interfaces\TabAdminInterface;
use Shop\{CartStatus};
use Marion\Core\Marion;
class StatusOrderAdminController extends AdminModuleController implements TabAdminInterface{
	public $_auth = '';
	public static function getTitleTab(){
		return _translate('order_states');
	}
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Stato ordine salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Stato ordine eliminato con successo','success');
		}
	}

	function displayList(){
		$this->showMessage();

		$list = CartStatus::prepareQuery()->get();
		$this->setVar('list',$list);
		$this->output('tabs/list_order_states.htm');
	}

	function displayFormMail(){
		$this->setVar('tabIndex',_var('tabIndex'));
		$id = $this->getID();
		if($id){
			$this->setVar('id',$id);
			$obj = CartStatus::withId($id);
			$this->setVar('status',$obj);
		}
		
		$database = Marion::getDB();
		if( $this->isSubmitted()){
			$dati = $this->getFormdata();

			if($dati['id_status']){
				$this->setVar('id',$dati['id_status']);
				$obj = CartStatus::withId($dati['id_status']);
				$this->setVar('status',$obj);
			}
			$array = $this->checkDataForm('cart_status_mail',$dati);
			if( $array[0] == 'ok' ){
				$locales = array_keys($array['subject']);
				

				foreach($locales as $lo){
					$toupdate = array(
						'subject' => $array['subject'][$lo],
						'message' => $array['message'][$lo],
						'locale' => $lo,
						'id_status' => $array['id_status']
					);
					
					$check = $database->select('*','cart_status_mail',"id_status={$array['id_status']} AND locale='{$lo}'");
					if( okArray($check) ){
						$database->update('cart_status_mail',"id_status={$array['id_status']} AND locale='{$lo}'",$toupdate);	
					}else{
						$database->insert('cart_status_mail',$toupdate);
					}
				
				}
				
				$this->displayMessage('Operazione effettuatta con successo!');
			}else{
				$this->errors[] = $array[1];
			}
		}else{
			$dati = array();
			$data = $database->select('*','cart_status_mail',"id_status={$id}");
			if( okArray($data) ){
				foreach($data as $v){
					$dati['subject'][$v['locale']] = $v['subject'];
					$dati['message'][$v['locale']] = $v['message'];
					
				}
				
			}
			$dati['id_status'] = $id;
			
		}
		
		

		$dataform = $this->getDataForm('cart_status_mail',$dati);
		$this->setVar('dataform',$dataform);
		$this->output('tabs/form_order_state_mail.htm');
	}


	function displayForm(){
		$action = $this->getAction();
		$id = $this->getID();
		if( $this->isSubmitted() ){
			/*$formdata = $this->getFormdata();
			$array = check_form2($formdata,'status_cart');*/
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('status_cart',$dati);
			if( $array[0] == 'nak'){
				$this->errors[] = $array[1];
			}else{

				if( $action == 'add' ){
					$obj = CartStatus::create();
				}else{
					$obj = CartStatus::withId($array['id']);
					
				}
				if(is_object($obj)){
					$obj->set($array);
					$obj->save();
					
				}
				$this->redirectToList(array('saved'=>1));
			}
			$dati = $array;
		}else{

			if( $action != 'add' ){
				$obj = CartStatus::withId($id);
				if( $obj ){
					$dati = $obj->prepareForm2();
					
				}
				if( $action == 'duplicate'){
					$action = 'add';
					unset($dati['id']);
				}
			}else{
				$dati = NULL;
			}

		}
		
		

		
		/*get_form2($elements,'status_cart',$action,$dati);
		if( $action == 'mod_stato_ordine'){
			$elements['formdata[label]']->attributes['readonly'] = 'readonly';
		}

		$this->output('form_order_state.htm',$elements);*/
		$dataform = $this->getDataForm('status_cart',$dati);
		$this->setVar('dataform',$dataform);
		
		$this->output('tabs/form_order_state.htm');
		
	}


	function displayContent(){
		
		$action = $this->getAction();
		if( $action == 'mail'){
			$this->displayFormMail();
		}else{
			$this->redirectToList();
		}
		
		
	}



	function setMedia(){
		$action = $this->getAction();
		switch($action){
			case 'edit':
			case 'add':
			case 'duplicate':
				$this->loadJS('spectrum');
				//debugga('qua');
				break;
			case 'mail':
				$this->registerJS($this->getBaseUrl().'modules/ecommerce/js/admin/mail_state_order.js','end');

				break;
			case 'list':
				$this->registerJS($this->getBaseUrl().'modules/ecommerce/js/admin/status_order.js','end');
				break;
			default:
				$this->registerJS($this->getBaseUrl().'modules/ecommerce/js/admin/status_order.js','end');
				break;

		}
		


	}




	function delete(){
		$id = $this->getID();

		$obj = CartStatus::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));
	}



	function ajax(){
		$action = $this->getAction();
		
		switch($action){
			case 'change_value':
				$field = _var('field');
				$id = $this->getID();
				$obj = CartStatus::withId($id);
				if( $obj ){
					$obj->$field = !$obj->$field;
					$obj->save();
					if( $obj->$field ){
						if( $field == 'active'){
							$text = _translate('active');
						}else{
							$text = _translate('yes');
						}
						$class = 'label label-success';
					}else{
						if( $field == 'active'){
							$text = _translate('inactive');
						}else{
							$text = _translate('no');
						}
						$class = 'label label-danger';
					}

					$risposta = array(
						'result' => 'ok',
						'text' => strtoupper($text),
						'class' => $class
						
					);
					
				}
				break;
			case 'data_mail_status':
				$database = Marion::getDB();
				$id_status = _var('id_status');
				$locale = _var('locale');
				$data = $database->select('*','cart_status_mail',"id_status={$id_status} AND locale='{$locale}'");
				
				ob_start();

				$dataform = $this->getDataForm('cart_status_mail',$data[0]);
				$this->setVar('dataform',$dataform);
				//get_form($elements,'cart_status_mail',$action,$data[0]);
				//$this->output('form_mail_state_order_fields.htm',$elements);
				$this->output('form_mail_state_order_fields.htm');
				$html = ob_get_contents();
				ob_end_clean();
				
				$risposta = array(
					'result' => 'ok',
					'html' => $html
				);


				break;
			case 'save_data_mail':
				$formdata = $this->getFormdata();
				

				//$array = check_form($formdata,'cart_status_mail');

				$array = $this->checkDataForm('cart_status_mail',$formdata);
				$database = Marion::getDB();
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
	
	}

	

}

?>