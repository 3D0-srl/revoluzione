<?php
class FMResponsiveAdminController extends ModuleController{
	public $_auth = 'cms';
	public $_twig = true;
	

	function display(){
		$this->setMenu('filemanager_responsive');
		

		
		$this->output('filemanager.htm');
	}

	


}



?>