<?php
// Rinominare l'oggetto MyWidget e riportare lo stesso nel file config.xml nel campo 'function'
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
class WidgetPhotogallery extends  PageComposerComponent
{

    public $template_html = 'render.htm'; //html del widget


    function registerJS($data = null)
    {
        /*
			se il widget necessita di un file js allora occorre registralo in questo modo
			
			PageComposer::registerJS("url del file"); // viene caricato alla fine della pagina
			PageComposer::registerJS("url del file",'head'); // viene caricato nel head 
			

		*/

		PageComposer::registerJS(_MARION_BASE_URL_."plugins/fancybox/dist/jquery.fancybox.min.js");
        PageComposer::registerJS("//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.js");
        PageComposer::registerJS(_MARION_BASE_URL_."modules/photogallery/js/script.js");
    }

    function registerCSS($data = null)
    {
        /*
			se il widget necessita di un file css allora occorre registralo in questo modo
			
			PageComposer::registerCSS("url del file"); 
			

		*/
		PageComposer::registerCSS(_MARION_BASE_URL_."plugins/fancybox/dist/jquery.fancybox.min.css");
        PageComposer::registerCSS(_MARION_BASE_URL_."modules/photogallery/css/style.css");
        PageComposer::registerCSS("//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css");
        PageComposer::registerCSS("https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css");
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


        //imposto una variabile nella pagina da mostrare

        $this->setVar('box_id', $this->id_box);
        $this->setVar('active_gallery', $parameters['active_gallery']);

        $this->output($this->template_html);
    }
}
