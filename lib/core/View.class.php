<?php
class View{
	private $_twig_obj;
	private $_twig_vars = array();
	private $_twig_functions = array();

	function __construct(){
		$this->_twig_obj = Marion::getTwig();

		$this->setVar('setting',$GLOBALS['setting']);
	}
	

	function setVar($key,$val){
		
		$this->_twig_vars[$key] = $val;
		
	}

	//associa una funzione di template a TWIG
	function addTemplateFunction($function=NULL){
			$this->_twig_functions[] = $function;
	}

	function output($tmpl){
		if( okArray($this->_twig_functions) ){
			foreach($this->_twig_functions as $func){
				$this->_twig_obj->addFunction($func);
			}
		}	
		echo $this->_twig_obj->render($tmpl, $this->_twig_vars);
	}
	


	


}


?>