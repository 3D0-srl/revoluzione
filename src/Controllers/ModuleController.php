<?php
namespace Marion\Controllers;
use Marion\Controllers\Controller;
/*
La classe 'ModuleController' aggiunge alla classe 'Controller'

*/
class ModuleController extends Controller{
	public $_load_classes = true; //flag che stabilisce se caricare le classi definite nella cartella classes del modulo
	public $_module;
	function init($options=array()){
		
		if( isset($options['module']) ){
			$this->_module = $options['module'];
		}else{
			$this->_module = _var('mod');
		}
		
		
		//carico le classi del modulo
		$this->loadModuleClasses();
		parent::init($options);
		
		$this->_url_script .= "&mod={$this->_module}"; //aggiungo all'url il modulo
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