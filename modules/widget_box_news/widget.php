<?php
// Rinominare l'oggetto MyWidget e riportare lo stesso nel file config.xml nel campo 'function'
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
use News\{News,NewsType};
class WidgetBoxNewsComponent extends  PageComposerComponent
{

	public $template_html = 'render.htm'; //html del widget


	function registerJS($data = null)
	{
		PageComposer::registerJS(_MARION_BASE_URL_."plugins/slick/slick.min.js");
		PageComposer::registerJS(_MARION_BASE_URL_."modules/widget_box_news/js/script.js");
	}

	function registerCSS($data = null)
	{
		/*
			se il widget necessita di un file css allora occorre registralo in questo modo
			
			PageComposer::registerCSS("url del file"); 
			

		*/
		PageComposer::registerCSS(_MARION_BASE_URL_."modules/widget_box_news/css/style.css");
		PageComposer::registerCSS(_MARION_BASE_URL_."plugins/slick/slick.css");
					
	}

	function build($data = null)
	{

	

		//$this->getTemplateTwig(basename(__DIR__)); //oggetto di tipo template che legge nei template del modulo

		/*$parameters: parametri di configurazione del widget
			  Questo array contiene i parametri di configurazione del widget
			*/

		$parameters = $this->getParameters();


		/*
				INSERISCI IL CODICE DEL WIDGET




			*/



		$this->setVar('box_id', 'widget_box_news_' . $this->id_box);
		if (okArray($parameters['categories'])) {
			foreach ($parameters['categories'] as $v) {
				$news_count = count(News::prepareQuery()->where('type_news', $v)->get());
				$obj = NewsType::withId($v);
				$obj->news_count = $news_count;
				if ($v == -1) {
					$allCategories = true;
					$allNews = count(News::prepareQuery()->get());
				} else {
					if (is_object($obj)) {
						$categories['info'][] = $obj;
					}
				}
			}
		}


		//imposto una variabile nella pagina da mostrare

		$this->setVar('allCategories', $allCategories);

		$this->setVar('title', $parameters['title']);
		$this->setVar('categories', $categories);
		$this->setVar('cols', $parameters['cols']);
		$this->setVar('rows', $parameters['rows']);
		$this->setVar('position', $parameters['position']);
		$this->setVar('first_news', $parameters['first_news']);
		$this->setVar('hidemenu', $parameters['hidemenu']);
		$this->setVar('cols_netbook', $parameters['cols_netbook']);
		$this->setVar('rows_netbook', $parameters['rows_netbook']);
		$this->setVar('cols_tablet', $parameters['cols_tablet']);
		$this->setVar('rows_tablet', $parameters['rows_tablet']);
		$this->setVar('cols_smartphone', $parameters['cols_smartphone']);
		$this->setVar('rows_smartphone', $parameters['rows_smartphone']);
		$this->setVar('allNews', $allNews);

		$this->output($this->template_html);
	}
}
