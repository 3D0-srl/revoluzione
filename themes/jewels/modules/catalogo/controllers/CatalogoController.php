<?php
require_once(_MARION_MODULE_DIR_.'catalogo/controllers/front/CatalogoController.php');
Marion\Core\Router::redirect('CatalogoController','NewCatalogoController');


class NewCatalogoController extends CatalogoController{
	
	

	function setMedia(){
		parent::setMedia();	
        
	}


	
}


?>