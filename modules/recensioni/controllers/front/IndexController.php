<?php
use Marion\Controllers\FrontendController;
class IndexController extends FrontendController{	

		function display(){
            //$this->output("recensioni.htm");
			$this->output("scrivi-recensione.htm");
        }

		function setMedia(){
			parent::setMedia();
			$this->registerJS('modules/recensioni/js/script.js');
			$this->registerCSS('modules/recensioni/css/style.css');
		}
	}
?>