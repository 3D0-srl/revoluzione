<?php
/*
La classe 'ModuleController' aggiunge alla classe 'Controller'

*/
class ModuleController extends Controller{
	public $_load_classes = true; //flag che stabilisce se caricare le classi definite nella cartella classes del modulo
	public $_module;
	function init($options=array()){
		
		$this->_module = _var('mod');
		
		//carico le classi del modulo
		$this->loadModuleClasses();
		parent::init($options);
		
		$this->_url_script .= "&mod={$this->_module}"; //aggiungo all'url il modulo
		//$this->_url_script = $_SERVER['PHP_SELF']."?ctrl=".$this->getCtrl()."&mod={$this->_module}";
		/*if( $options['url_script'] ){
			$this->_url_script = $this->setUrlScript($options['url_script']);
		}else{
			$this->_url_script = $this->setUrlScript($_SERVER['PHP_SELF']."?ctrl=".$this->getCtrl()."&mod={$this->_module}");
		}*/
	}

	function getTemplateObj(){
		$this->addTwingTemplatesDir("../modules/{$this->_module}/templates_twig/admin");		
	}



	function loadModuleClasses(){
		if( $this->_load_classes ){
			$directory = "../modules/{$this->_module}/classes";
			
			if( is_dir($directory) ){
				$classes = scandir($directory);
				foreach($classes as $v){
					$file = $directory."/".$v;
					if( is_file($file) ){
						require_once($file);
					}
				}
			}
		}
		
	}

	
	function setTemplateVariables(){
		parent::setTemplateVariables();
		$this->setVar('module',$this->_module);
	}

	
}
?>