<?php
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
class WidgetBoxImageComponent extends  PageComposerComponent{
	
	public $template_html = 'render.htm'; //html del widget
	
	function registerJS($data=null){
		/*
			se il widget necessita di un file js allora occorre registralo in questo modo
			
			PageComposer::registerJS("url del file"); // viene caricato alla fine della pagina
			PageComposer::registerJS("url del file",'head'); // viene caricato nel head 
			

		*/
		//PageComposer::registerJS("modules/widget_box_image/js/script.js");
	}
	function registerCSS($data=null){
		/*
			se il widget necessita di un file css allora occorre registralo in questo modo
			
			PageComposer::registerCSS("url del file"); 
			

		*/
		PageComposer::registerCSS("modules/widget_box_image/css/style.css");
	}

	function build($data=null){
			
			//$widget = Marion::widget(basename(__DIR__));

			//parametri di configurazione del widget
			$parameters = $this->getParameters();
			
			//box contenitore
			//$box = $this->getBoxContainer();
				
			
			$this->setVar('hover',$parameters['hover']);
			$this->setVar('target_blank',$parameters['target_blank']);


			if( _MARION_ENABLE_WEBP_ && file_exists("media/images/".$parameters['image_webp'][$GLOBALS['activelocale']])){
				$image = "media/images/".$parameters['image_webp'][$GLOBALS['activelocale']];
			}else{
				$image = "media/images/".$parameters['image'][$GLOBALS['activelocale']];
			}


			$this->setVar('image',$image);

			$this->setVar('url',$parameters['url'][$GLOBALS['activelocale']]);
			
		
			

			//$widget->titolo;
			if( $parameters['title'][$GLOBALS['activelocale']] ){
				$this->setVar('titolo',$parameters['title'][$GLOBALS['activelocale']]);
			}
			if( $parameters['label'][$GLOBALS['activelocale']] ){
				$this->setVar('label',$parameters['label'][$GLOBALS['activelocale']]);
			}
			if( $parameters['testo_bottone'][$GLOBALS['activelocale']] ){
				$this->setVar('testo_bottone',$parameters['testo_bottone'][$GLOBALS['activelocale']]);
			}
			$this->output($this->template_html);
	}



	function isEditable(){
		

		return true;
	}



	function export($directory){
		$parameters = $this->getParameters();
		$paths = array();
		if(okArray($parameters)){
			foreach($parameters['image'] as $v){
				$paths[] = $v;
			}
			foreach($parameters['image_webp'] as $v){
				$paths[] = $v;
			}
		}

		$zip = new \ZipArchive;
		if ($zip->open($directory.'/images.zip', \ZipArchive::CREATE) === TRUE)
		{
			
			foreach($paths as $path){
				if(file_exists(_MARION_ROOT_DIR_."media/images/".$path)){
					//debugga($path);exit;
					$zip->addFile(_MARION_ROOT_DIR_."media/images/".$path,$path);
				}
				
			}
			// All files are added, so close the zip file.
			$zip->close();
		}

	}


	function import($directory){
		$parameters = $this->getParameters();
		if( file_exists($directory) ){
			$path_zip = $directory."/images.zip";
			if( file_exists($path_zip) ){
				$zip = new \ZipArchive;
				if ($zip->open($path_zip) === TRUE) {
					$zip->extractTo($directory."/images");
					$zip->close();
				
				} else {
				
				}
			}
			$this->rcopy($directory."/images",_MARION_ROOT_DIR_."media/images");
		}
		

	}

	function rcopy($src, $dest){

		// If source is not a directory stop processing
		if(!is_dir($src)) return false;
	
		// If the destination directory does not exist create it
		if(!is_dir($dest)) { 
			if(!mkdir($dest)) {
				// If the destination directory could not be created stop processing
				return false;
			}    
		}
	
		// Open the source directory to read in files
		$i = new \DirectoryIterator($src);
		foreach($i as $f) {
			if($f->isFile()) {
				copy($f->getRealPath(), "$dest/" . $f->getFilename());
			} else if(!$f->isDot() && $f->isDir()) {
				$this->rcopy($f->getRealPath(), "$dest/$f");
			}
		}
	}
}






?>