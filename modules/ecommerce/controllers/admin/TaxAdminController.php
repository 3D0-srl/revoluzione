<?php
use Marion\Controllers\AdminModuleController;
use Marion\Controllers\Interfaces\TabAdminInterface;
use Shop\{Tax};

class TaxAdminController extends AdminModuleController implements TabAdminInterface{
	public $_auth = '';
	
	public static function getTitleTab(){
		return _translate('taxes');
	}
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Tassa salvata con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Tassa eliminata con successo','success');
		}
	}

	function displayList(){
		$this->showMessage();

		$list = Tax::prepareQuery()->get();
		$this->setVar('list',$list);
		
		$this->output('tabs/list_tax.htm');
	}

	
	function displayForm(){
		//$this->setVar('tabIndex',_var('tabIndex'));
		$action = $this->getAction();
		$id = $this->getID();
		if( $this->isSubmitted() ){
			
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('tax',$dati);
			if( $array[0] == 'nak'){
				$this->errors[] = $array[1];
			}else{

				if( $action == 'add' ){
					$obj = Tax::create();
				}else{
					$obj = Tax::withId($array['id']);
					
				}
				if(is_object($obj)){
					$obj->set($array);
					$obj->save();
					
				}
				$this->redirectToList(array('saved'=>1));
			}
			
		}else{

			if( $action != 'add' ){
				$obj = Tax::withId($id);
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
		
		

		
		//get_form2($elements,'tax',$action,$dati);
		$dataform = $this->getDataForm('tax',$dati);
		$this->setVar('dataform',$dataform);
		
		$this->output('tabs/form_tax.htm');//,$elements);
	}


	function displayContent(){
		
		$action = $this->getAction();
		if( !$action ){
			$this->redirectToList();

		}
		
		
		
	}






	function delete(){
		$id = $this->getID();

		$obj = Tax::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));
	}



	

}

?>