<?php
use Marion\Controllers\AdminModuleController;
use Shop\ShippingArea;
use \Country;
class ShippingAreaAdminController extends AdminModuleController{
	public $_auth = 'ecommerce';
	
	


	function displayForm(){
		$this->setMenu('area_shippings');
		
		$action = $this->getAction();


		

		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			
			$array = $this->checkDataForm('shipping_area',$formdata);
			if( $array[0] == 'ok'){

				if( $action == 'add'){
				$obj = ShippingArea::create();
				}else{
					$obj = ShippingArea::withId($array['id']);
				}
				$obj->set($array);
				$obj->setCountries($array['countries']);
				
				

				$res = $obj->save();
				if( is_object($res) ){
					$this->saved();
				}else{
					$this->errors[] = $res;
				}


			}else{
				$this->errors[] = $array[1];
			}

			$dati = $array;
			

		}else{
		
			createIDform();
		
			$id = $this->getID();
			
			if($action != 'add'){
				$obj = ShippingArea::withId($id);
				
				$dati =  $obj->prepareForm2();
				

				if($action == 'duplicate'){
					unset($dati['id']);
					unset($dati['images']);
					$action = "add";
				}
			}else{
				$dati = NULL;
			}
		}
		
		$dataform = $this->getDataForm('shipping_area',$dati);
				
		$this->setVar('dataform',$dataform);
		$this->output('form_shipping_area.htm');

	}


	function setMedia(){
		$this->loadJS('multiselect');
	}

	function displayList(){
		$this->setMenu('area_shippings');
		$this->showMessage();

		
		
		$aree = ShippingArea::prepareQuery()->get();
		if( okArray($aree) ){
			$countries = Country::getAll();
			foreach($countries as $v){
				$list_countries[$v->id] = $v->get('name','it');
			}
			
			foreach($aree as $k => $v){
				$nazioni = '';
				foreach($v->countries as $country){
					$nazioni .= $list_countries[$country].", ";
				}
				$aree[$k]->nazioni = preg_replace('/\, $/','',$nazioni);
			}
		}
		



		$this->setVar('aree',$aree);
		$this->output('list_shipping_area.htm');
			
	}

	
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Area salvata con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Area eliminata con successo','success');
		}
	}

	function saved(){
		$this->redirectTolist(array('saved'=>1));
	}


	function delete(){
		$id = $this->getID();

		$obj = ShippingArea::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));
		

		
	}


	function array_nazioni(){
		//$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$nazioni = Country::getAll();
		foreach($nazioni as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		return $toreturn;
	}




	

}



?>