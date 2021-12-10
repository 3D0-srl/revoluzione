<?php
use Marion\Core\Marion;
use Marion\Controllers\ModuleController;
class SettingController extends ModuleController{
	public $_auth = 'cms';

	

	function display(){
		$action = $this->getAction();
		$this->setMenu('manage_modules');
		
		$database = Marion::getDB(); 

		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();

			
	
	
			$type = 'bonifico';
			foreach($formdata as $k => $v){
				$database->update('setting',"gruppo='{$type}' AND chiave = '{$k}'",array('valore'=>$v));
			}
			$cache = _obj('Cache');
			if( $cache->isExisting("setting") ){
				$cache->delete('setting');
			}

			$this->displayMessage('Dati slavati con successo!');
			
		}

		
		$select = $database->select('*','setting',"gruppo='bonifico' order by ordine");
		
		foreach($select as $v){
			$toreturn[$v['chiave']] = $v; 
		}

		
		$this->setVar('dati',$toreturn);

		$this->output('conf.htm');

		
	}

	


	


}



?>