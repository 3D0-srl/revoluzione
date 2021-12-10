<?php
class IndexController extends ModuleController{
	public $alexa_db = array(
		"host" => "localhost",
		"nome" => "outletbr_db",
		"password" => "EpC]Fc1rW^3)",
		"user" => "outletbr_dbuser",
		"port" => '3306',
	);
	

	function display(){

		$this->output('conf.htm');

	}

	
}

?>