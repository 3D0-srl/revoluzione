<?php
// Rinomimanre l'oggetto MyWidget e riportare lo stesso nel file config.xml nel campo 'function'
use Marion\Components\PageComposerComponent;

class WidgetCodeJS extends  PageComposerComponent{
	
	public $template_html = 'miotemplate.htm'; //html del widget

	
	function build($data=null){
			
			
			/*$parameters: parametri di configurazione del widget
			  Questo array contiene i parametri di configurazione del widget
			*/
			$parameters = $this->getParameters();



			echo "<script>".$parameters['js_box']."</script>";
			

				
		
	}



}






?>