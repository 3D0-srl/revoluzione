<?php
use Marion\Controllers\ModuleController;
use Marion\Core\Marion;
class ConfController extends ModuleController{
	public $_auth = 'cms';

	

	function display(){
		$database = Marion::getDB();
		$this->id_box = _var('id_box');
		$this->setVar('id_box',_var('id_box'));
		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			$array = $this->checkDataForm('widget_box_image',$formdata);
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

		$dataform = $this->getDataForm('widget_box_image',$dati);
		
		$this->setVar('dataform',$dataform);
		
		$this->output('impostazioni.htm');
	}


	
	
	


	// FUNZIONI PER IL FORM
	function array_hover_box_image(){
		$toreturn = array(
			'' => '------',
			'hover01' => 'Zoom In',
			//'hover02' => 'Zoom In #2',
			'hover03' => 'Zoom Out',
			//'hover04' => 'Zoom Out #2',
			'hover05' => 'Slide',
			'hover06' => 'Rotate',
			'hover07' => 'Blur',
			'hover08' => 'Gray Scale',
			'hover09' => 'Sepia',
			'hover10' => 'Blur + Gray Scale',
			'hover11' => 'Opacity',
			'hover16' => 'Overlay',
			//'hover12' => 'Opacity #2',
			'hover13' => 'Flashing',
			//'hover14' => 'Shine',
			//'hover15' => 'Circle',
		);

		return $toreturn;
	}


}



?>