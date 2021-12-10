<?php
// Rinomimanre l'oggetto MyWidget e riportare lo stesso nel file config.xml nel campo 'function'
use Marion\Components\PageComposerComponent;

class WidgetFacebookChat extends  PageComposerComponent{
	
	public $template_html = 'render.htm'; //html del widget
	

	function registerJS($data=null){
		/*
			se il widget necessita di un file js allora occorre registralo in questo modo
			
			PageComposer::registerJS("url del file"); // viene caricato alla fine della pagina
			PageComposer::registerJS("url del file",'head'); // viene caricato nel head 
			

		*/
		//PageComposer::registerJS("/index.php?ctrl=",'head');
	}
	function registerCSS($data=null){
		/*
			se il widget necessita di un file css allora occorre registralo in questo modo
			
			PageComposer::registerCSS("url del file"); 
			

		*/
		//PageComposer::registerCSS("/modules/widget_starter/css/style.css");
	}

	function build($data=null){
			
			//$this->getTemplateTwig(basename(__DIR__)); //oggetto di tipo template che legge nei template del modulo
	
			
			
			/*$parameters: parametri di configurazione del widget
			  Questo array contiene i parametri di configurazione del widget
			*/
			$parameters = $this->getParameters();
			
			


			$logged_in_greeting = 'Hi!';
			$logged_out_greeting = 'Goodbye!';
			$theme_color = '#000000';
			$greeting_dialog_delay = 0;

			$greeting_dialog_display = 'show';
			$page_id = '123';
			if( $parameters['logged_in_greeting'][$GLOBALS['activelocale']] ){
				$logged_in_greeting = $parameters['logged_in_greeting'][$GLOBALS['activelocale']];
			}
			if( $parameters['logged_out_greeting'][$GLOBALS['activelocale']] ){
				$logged_out_greeting = $parameters['logged_out_greeting'][$GLOBALS['activelocale']];
			}

			if( $parameters['theme_color'] ){
				$theme_color = $parameters['theme_color'];
			}
			if( $parameters['greeting_dialog_delay'] ){
				$greeting_dialog_delay = $parameters['greeting_dialog_delay'];
			}

			if( $parameters['greeting_dialog_display'] ){
				$greeting_dialog_display = $parameters['greeting_dialog_display'];
			}

			if( $parameters['page_id'] ){
				$page_id = $parameters['page_id'];
			}

			$this->setVar('logged_in_greeting',$logged_in_greeting);
			$this->setVar('logged_out_greeting',$logged_out_greeting);
			$this->setVar('theme_color',$theme_color);
			$this->setVar('greeting_dialog_delay',$greeting_dialog_delay);
			$this->setVar('greeting_dialog_display',$greeting_dialog_display);
			$this->setVar('page_id',$page_id);

			/*
				INSERISCI IL CODICE DEL WIDGET




			*/

			//imposto una variabile nella pagina da mostrare
			//$this->setVar('nome_variabile','valore_variabile');

			
			$this->output($this->template_html);
				
		
	}

}






?>