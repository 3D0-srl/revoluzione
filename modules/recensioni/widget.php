<?php
	
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
use Illuminate\Database\Capsule\Manager as DB;
class WidgetRecensioni extends  PageComposerComponent{
	
		
		
		function registerJS($data=null){
		

            //PageComposer::registerJS(_MARION_BASE_URL_."modules/recensioni/js/frontend.js");
		}
		function registerCSS($data=null){
			
			//PageComposer::registerCSS(_MARION_BASE_URL_."modules/recensioni/css/frontend.css");
		}

		function build($data=null){
            $recensioni = DB::table('recensioni')
                    ->where('confermato',true)
                    ->orderBy('data_inserimento','desc')
                    ->limit(5)
                    ->get()
                    ->toArray();
            //debugga($recensioni);exit;
            $this->setVar('recensioni',$recensioni);
            $this->output("last_recensioni.htm");
				
					
			
		}


	}

	
?>