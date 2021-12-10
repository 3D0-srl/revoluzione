<?php
class MagazzinoController extends AdminModuleController{
	public $_auth = 'ecommerce';
	

	function displayForm(){
		$this->setMenu('sincro_magazzino');
		$action = $this->getAction();
		if( $this->isSubmitted()){
			$dati = $this->getFormData();
			$array = $this->checkDataForm('sincro_magazzino',$dati);
			if( $array[0] == 'ok'){
				if( $action == 'edit'){
					$obj = Magazzino::withId($array['id']);
				}else{
					$obj = Magazzino::create();
				}
				$obj->set($array)->save();
				$this->redirectToList(array('created' => 1));
			}else{
				$this->errors[] = $array[1];
			}	
		}else{
			if( $action == 'edit'){
				$id = $this->getID();
				$obj = Magazzino::withId($id);
				if( is_object($obj) ){
					$dati = $obj->prepareForm2();
				}
			}

		}
		$dataform = $this->getDataForm('sincro_magazzino',$dati);
		$this->setVar('dataform',$dataform);
		$this->output('magazzino/form.htm');
	}


	function displayList(){
		$this->setMenu('sincro_magazzino');
		if( _var('created') ){
			$this->displayMessage("Magazzino creato con successo");
		}
		if( _var('deleted') ){
			$this->displayMessage("Magazzino eliminato con successo");
		}
		$list = Magazzino::prepareQuery()->get();
		$this->setVar('list',$list);
		$this->output('magazzino/list.htm');
	}


	function delete(){
		$id = $this->getID();
		$obj = Magazzino::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted' => 1));
	}
	
}