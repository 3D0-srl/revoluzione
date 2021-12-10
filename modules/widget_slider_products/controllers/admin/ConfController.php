<?php
use Marion\Controllers\ModuleController;
use Catalogo\TagProduct;
use Catalogo\Section;
use Marion\Core\Marion;
class ConfController extends ModuleController{
	public $_auth = 'cms';
	
	public $_form_control = 'widget_slider_products';

	

	function display(){
		$database = Marion::getDB();
		$this->id_box = _var('id_box');
		$this->setVar('id_box',_var('id_box'));
		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			$update = array();
			switch( $formdata['slider_type'] ){
				case 'products_tas':
					$update['tag']['obbligatorio'] = 1;
					break;
				case 'products_category':
					$update['category']['obbligatorio'] = 1;
					break;
			}

			
			
			
			$array = $this->checkDataForm($this->_form_control,$formdata,$update);


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
			$dati = null;
			if( okArray($data) ){
				$dati = unserialize($data[0]['parameters']);
			}
			

			
		}

		$dataform = $this->getDataForm($this->_form_control,$dati);
		
		
		$this->setVar('dataform',$dataform);
		
		$this->output('setting.htm');
	}


	
	
	


	// FUNZIONI PER IL FORM
	function productTags(){
		$toreturn[0] = __('seleziona');
		$tag = TagProduct::prepareQuery()->get();
		foreach($tag as $v){
			$toreturn[$v->id] = $v->label;
		}
		return $toreturn;
	}


	// FUNZIONI PER IL FORM
	function sliderTypes(){

		$toreturn = array(
			'products_tag' => 'Products Tag',
			'best_sellers' => 'Best Sellers',
			'new_arrivals' => 'New Arrivals',
			'products_category' => 'Products Category'
		);
		
		return $toreturn;
	}

	// FUNZIONI PER IL FORM
	function categories(){
		//$toreturn[0] = __('seleziona');
		$sezioni = Section::getAll('it');
		
		
		foreach($sezioni as $k => $v){
			$select[$k] = $v;
		}
		return $select;
		
	}


}



?>