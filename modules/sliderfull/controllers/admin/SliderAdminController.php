<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use SliderFull\{SliderFull,SlideFull};
class SliderAdminController extends AdminModuleController{
	public $_auth = 'cms';
	
	function displayList(){
		
		$this->setMenu('sliderfull');
		$this->showMessage();

		$sliders = SliderFull::prepareQuery()->get();
	
		$this->setVar('list',$sliders);
		$this->output('list_slider.htm');

	}

	function resetCache($id_slider){
		$database = Marion::getDB();
		$select = $database->select('*','composition_page_tmp as h join module as m on m.id=h.module',"m.directory='sliderfull'");
		if( okArray($select) ){
			$cache = _obj('Cache');
			foreach($select as $v){
				$dati = unserialize($v['parameters']);
				if( $dati['id_slider'] == $id_slider ){
					$key = 'sliderfull_'.$dati['id_box'];
					

					if( $cache->isExisting($key) ){
						$cache->delete($key);
					}
				}
				
			}
		}
		$select = $database->select('*','composition_page as h join module as m on m.id=h.module',"m.directory='sliderfull'");
		if( okArray($select) ){
			$cache = _obj('Cache');
			foreach($select as $v){
				$dati = unserialize($v['parameters']);
				if( $dati['id_slider'] == $id_slider ){
					$key = 'sliderfull_'.$dati['id_box'];
					

					if( $cache->isExisting($key) ){
						$cache->delete($key);
					}
				}
				
			}
		}
	}

	function displayForm(){
		
		$this->setMenu('sliderfull');
		$action = $this->getAction();
		$id = _var('id');
		if( $this->isSubmitted() ){
			$dati = $this->getFormdata();

			//$array = check_form2($formdata,'module_sliderfull_slider');
			$array = $this->checkDataForm('module_sliderfull_slider',$dati);

			
			if( $array[0] == 'ok' ){

				if(	$action == 'add'){
					$obj = SliderFull::create();
				}else{
					$obj = SliderFull::withId($array['id']);
				}
				$obj->set($array);
				$res = $obj->save();
				$this->resetCache($res->id);
				
				if(is_object($res)){
					$this->redirectToList(array('saved'=>1));
				}else{
					$this->errors[] = $res;
				}
				
			}else{
				
				$this->errors[] = $array[1];
				
				
			}
		}else{


			$dati = NULL;
			if( $action != 'add'){
				$obj = SliderFull::withId($id);
				if(is_object($obj) ){
					$dati = $obj->prepareForm2();
					
				}
				if( $action == 'duplicate'){
					unset($dati['id']);
					$action = 'add';
				}
			}


			
			
			

		}
		$dataform = $this->getDataForm('module_sliderfull_slider',$dati,$this);
		$this->setVar('dataform',$dataform);
		$this->output('form_slider.htm');

	}
	


	function delete(){
		$id = $this->getID();

		$obj = SliderFull::withId($id);
		if( is_object($obj) ){
			$slides = SlideFull::prepareQuery()->where('id_slider',$obj->id)->get();
			foreach($slides as $slide){
				$slide->delete();
			}
			$obj->delete();
		}
		$this->resetCache($obj->id);
		$this->redirectToList(array('deleted'=>1));
		

		
	}

	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Slider salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Slider eliminato con successo','success');
		}
	}

	
	/*function display(){
		$action = $this->getAction();
		$this->setMenu('cms_page');
		


		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			$array = check_form2($formdata,'module_cookie_alert');
			if( $array[0] == 'ok'){ 
				$cookieAlert = CookieAlert::prepareQuery()->getOne();
				if( !is_object($cookieAlert) ){
					$cookieAlert = CookieAlert::create();
				}
				$cookieAlert->set($array);
				$cookieAlert->save();
				$this->displayMessage('Configurazione salavata con successo');
				
			}else{
				$this->errors[] = $array[1];
			}
			$dati = $formdata;
		}else{
			$obj = CookieAlert::prepareQuery()->getOne();
			if( is_object($obj) ){
				$dati = $obj->prepareForm2();
			}
		}

		get_form2($elements,'module_cookie_alert','',$dati);
		$this->output('conf.htm',$elements);

		
	}*/

	


	


}



?>