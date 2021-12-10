<?php
use Marion\Controllers\ModuleController;
use News\{NewsType};
use Marion\Core\Marion;
class ConfController extends ModuleController{
	public $_auth = 'cms';
	public $_form_control = 'widget_box_news';

	function setMedia(){
		$this->loadJS('multiselect');
	}

	

	function display(){
	
		$database = Marion::getDB();
		$this->id_box = _var('id_box');
		$this->setVar('id_box',_var('id_box'));
		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			$array = $this->checkDataForm($this->_form_control,$formdata);
			if( $array[0] == 'ok'){
				unset($array[0]);
				
				$data = array();
				foreach($array as $k => $v){
					if( $k != '_locale_data'){
						$data[$k] = $v;
					}
				}
				foreach($array['_locale_data'] as $k =>$v){
					foreach($v as $k1 => $v1){
						$data[$k1][$k] = $v1;
					}
				}
		
				
				$dati = serialize($data);
				
				$database->update('composition_page_tmp',"id={$this->id_box}",array('parameters'=>$dati));
				
				$this->displayMessage('Dati salati con successo!','success');
			}else{
				$this->errors[]= $array[1];
			}
			$dati = $formdata;
			
		}else{
			$data = $database->select('*','composition_page_tmp',"id={$this->id_box}");
			
			if( okArray($data) ){
				$dati = unserialize($data[0]['parameters']);
			}
			
		}

		$dataform = $this->getDataForm($this->_form_control,$dati);
		
		$this->setVar('dataform',$dataform);

		$this->output('setting.htm');
	}


	
	
	


	// FUNZIONI PER IL FORM
	function getCategories(){
		$categories = NewsType::prepareQuery()->get();
		$toReturn = [];

		foreach($categories as $category) {
			$name = $category->get('name');
			$id = $category->id;
			$toReturn[$id] = $name;
		}

		$toReturn[-1] = 'tutte';

		return $toReturn;
		
	}


}
