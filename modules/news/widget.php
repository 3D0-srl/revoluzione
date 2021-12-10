<?php
// Rinomimanre l'oggetto MyWidget e riportare lo stesso nel file config.xml nel campo 'function'
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
use News\News;
class WidgetUltimeNews extends  PageComposerComponent{
	
	public $template_html = 'render.htm'; //html del widget
	

	function registerJS($data=null){
		/*
			se il widget necessita di un file js allora occorre registralo in questo modo
			
			PageComposer::registerJS("url del file"); // viene caricato alla fine della pagina
			PageComposer::registerJS("url del file",'head'); // viene caricato nel head 
			

		*/
		//PageComposer::registerJS("/modules/news/js/script.js");
	}
	function registerCSS($data=null){
		/*
			se il widget necessita di un file css allora occorre registralo in questo modo
			
			PageComposer::registerCSS("url del file"); 
			

		*/
		PageComposer::registerCSS(_MARION_BASE_URL_."modules/news/css/widget.css");
	}

	function build($data=null){
			
			//$this->getTemplateTwig(basename(__DIR__)); //oggetto di tipo template che legge nei template del modulo
	
			
			
			/*$parameters: parametri di configurazione del widget
			  Questo array contiene i parametri di configurazione del widget
			*/
			$parameters = $this->getParameters();
			
			$id_cat = $parameters['id_category_news'];


			$news = [];
			if( $id_cat ){
				$query = News::prepareQuery()
					->where('type_news',$id_cat)
					->orderBy('date','DESC')->limit(3);
				$list = $query->get();
				foreach($list as $v){
					$news[] = [
						'id' => $v->id,
						'title' => $v->get('title'),
						'image' => $v->getUrlImage(0)
					];
				}
			}
			

			/*
				INSERISCI IL CODICE DEL WIDGET




			*/

			//imposto una variabile nella pagina da mostrare
			$this->setVar('list',$list);

			
			$this->output($this->template_html);
				
		
	}

}






?>