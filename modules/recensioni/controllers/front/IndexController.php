<?php
use Marion\Controllers\FrontendController;
use Illuminate\Database\Capsule\Manager as DB;
class IndexController extends FrontendController{	

		function display(){
			$recensioni = DB::table('recensioni')
					->where('confermato',true)
					->orderBy('data_inserimento','desc')
					->get()
					->toArray();
			//debugga($recensioni);exit;
			$this->setVar('recensioni',$recensioni);
			$this->output("recensioni.htm");
        }

		function setMedia(){
			parent::setMedia();
			$this->registerJS('modules/recensioni/js/script.js');
			$this->registerCSS('modules/recensioni/css/style.css');
		}
	}
?>