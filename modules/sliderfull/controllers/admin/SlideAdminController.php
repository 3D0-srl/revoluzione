<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use SliderFull\SlideFull;
class SlideAdminController extends AdminModuleController{
	public $_auth = 'cms';

	function setMedia(){
		$action = $this->getAction();
		switch($action){
			case 'edit':
			case 'add':
			case 'duplicate':
				$this->registerJS($this->getBaseUrl().'plugins/spectrum/spectrum.js','head');
				$this->registerCSS($this->getBaseUrl().'plugins/spectrum/spectrum.css');
				break;
			
			default:
				
				break;

		}
		


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
	
	
	function displayList(){
		

		$this->setMenu('sliderfull');
		$this->showMessage();

		$id_slider = _var('id_slider');
		$this->setVar('id_slider',$id_slider);
		$slides = SlideFull::prepareQuery()->orderBy('orderView')->where('id_slider',$id_slider)->get();
	
		$this->setVar('list',$slides);
		$this->output('list_slide.htm');

	}

	function displayForm(){
		
		$this->setMenu('sliderfull');
		$action = $this->getAction();
		$id = $this->getID();
		$id_slider = _var('id_slider');
		if( $this->isSubmitted() ){
			$dati = $this->getFormdata();
			
			$array = $this->checkDataForm('module_sliderfull_slide',$dati);
			if( $array[0] == 'ok' ){

				if(	$action == 'add'){
					$obj = SlideFull::create();
				}else{
					$obj = SlideFull::withId($array['id']);
				}
				$obj->set($array);
				$res = $obj->save();
				
				$this->resetCache($res->id_slider);
				if(is_object($res)){
					$this->redirectToList(array('saved'=>1,'id_slider'=>$array['id_slider']));
				}else{
					$this->errors[] = $res;
				}
				
				
			}else{
				
				$this->errors[] = $array[1];
				
				
			}
		}else{


			$dati = NULL;
			if( $action != 'add'){
				$obj = SlideFull::withId($id);
				if(is_object($obj) ){
					$dati = $obj->prepareForm2();
					
				}
				if( $action == 'duplicate'){
					unset($dati['id']);
					$action = 'add';
				}
			}else{
				$dati['id_slider'] = $id_slider;
			}
			

			

		}
		$dataform = $this->getDataForm('module_sliderfull_slide',$dati,$this);
		$this->setVar('dataform',$dataform);
		$this->output('form_slide.htm');

	}
	


	function delete(){
		$id = $this->getID();

		$obj = SlideFull::withId($id);
		
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->resetCache($obj->id_slider);
		$this->redirectToList(array('deleted'=>1,'id_slider'=>$obj->id_slider));
		

		
	}

	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Slide salvata con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Slide eliminata con successo','success');
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

function array_locales_sliderfull(){
	$locales = Marion::getConfig('locale','supportati');
	
	foreach($locales as $loc){
		$toreturn[$loc] = $loc;
	}
	return $toreturn;

}



?>