<?php
use Marion\Controllers\ModuleController;
use Marion\Core\Marion;
use Illuminate\Database\Capsule\Manager as DB;

class WidgetController extends ModuleController{
	public $_auth = 'cms';
	function getPath(){
		$this->path = _MARION_MODULE_DIR_."widget_revslider/sliders/";
	}


	function display(){


		$database = Marion::getDB();
		
		
		
		$this->id_box = _var('id_box');
		$this->setVar('id_box',_var('id_box'));

		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			$array = $this->checkDataForm('widget_revslider',$formdata);
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


		

		$dataform = $this->getDataForm('widget_revslider',$dati);

		
		$this->setVar('dataform',$dataform);
		$this->output('conf.htm');
	}



	function sliders(){
		$database = Marion::getDB();
		$list = $database->select('*','revolution_slider');
		if( okArray($list) ){
			foreach($list as $v){
				$toreturn[$v['id']] = $v['title'];
			}
		}
		return $toreturn;
	}







}



?>