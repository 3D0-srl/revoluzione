<?php
// Rinomimanre l'oggetto MyWidget e riportare lo stesso nel file config.xml nel campo 'function'
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
class WidgetVideoComponent extends  PageComposerComponent
{

	public $template_html = 'youtube.htm'; //html del widget

	function registerJS($data=null)
	{

		PageComposer::registerJS(_MARION_BASE_URL_."plugins/video-js/src/js/video.js");
	}

	function registerCSS($data=null)
	{
		PageComposer::registerCSS(_MARION_BASE_URL_."plugins/video-js/src/css/video-js.scss");
	}

	function build($data = null)
	{
		//parametri di configurazione del widget
		$parameters = $this->getParameters();

	
		$tipo = $parameters['tipo_video'];

	
		$this->setVar('url_video', $parameters['url']);
		if ($tipo == 'youtube') {
			$this->output($this->template_html);
		} elseif ($tipo == 'vimeo') {
			$this->output('vimeo.htm');
		} else {
			$ext = explode('.',$parameters['url']);
			$this->setVar('video_ext',$ext[count($ext)-1]);
			$this->output('upload.htm');
		}
	}

	function isAvailable($box)
	{
		return true;
	}
}
