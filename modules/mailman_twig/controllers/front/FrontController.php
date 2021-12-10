<?php

use Mailman\Mailman;
use Marion\Controllers\FrontendController;

class FrontController extends FrontendController{

	
	function display(){
		$key = _var('key');
		$dati = unserialize(base64_decode($key));
		require_once(_MARION_MODULE_DIR_."mailman_twig/classes/Mailman.class.php");
		switch($dati['action']){
			case 'subscribe':
				$list = Mailman::withId($dati['list']);
				$res = $list->subscribe($dati['email']);
				if( $res == 1 ){
					$message = "La tua mail è stata aggiunta alla nostra newsletter";
				}else{
					$message = "errore";
				}
				
				break;
			case 'unsubscribe':
				$list = Mailman::withId($dati['list']);
				$res = $list->unsubscribe($dati['email']);
				if( $res == 1 ){
					$message = "La tua mail è stata rimossa dalla nostra newsletter";
				}else{
					$message = "errore";
				}
				break;
		}


		$this->setVar('message',$message);
		$this->output('thanks.htm');
	}

}

?>