<?php
use Marion\Components\PageComposerComponent;
use Marion\Core\Marion;
use Marion\Entities\Cms\PageComposer;
use Marion\Utilities\PageComposerTools;
class WidgetRevSliderComponent extends  PageComposerComponent{
	
	public $template_html = 'miotemplate.htm'; //html del widget
	
	
	function getDataSlider($id){
		if( !(int)$id ) return false;
		if( isset($GLOBALS['widget_revslider']) && okArray($GLOBALS['widget_revslider']) && array_key_exists($id,$GLOBALS['widget_revslider'])) return $GLOBALS['widget_revslider'][$id];
		$database = Marion::getDB();
		
		$res = $database->select('*','revolution_slider',"id={$id}");
		
		if( okArray($res) ){
			$res = $res[0];
			$GLOBALS['widget_revslider'][$id]['js'] = unserialize($res['js']);
			$GLOBALS['widget_revslider'][$id]['css'] = unserialize($res['css']);
			$GLOBALS['widget_revslider'][$id]['content'] = $res['content'];


			//debugga($res);exit;
			
		}else{
			$GLOBALS['widget_revslider'][$id] = null;
		}


		


		
	}

	function registerJS($data=null){
		/*
			se il widget necessita di un file js allora occorre registralo in questo modo
			
			PageComposer::registerJS("url del file"); // viene caricato alla fine della pagina
			PageComposer::registerJS("url del file",'head'); // viene caricato nel head 
			

		*/

		$parameters = unserialize($data['parameters']);
		

		$id  = $parameters['id_slider'][$GLOBALS['activelocale']];
		
		if( (int)$id ){
			$this->getDataSlider($id);
			
			
			if( okArray($GLOBALS['widget_revslider'][$id]['js']) ){
				foreach( $GLOBALS['widget_revslider'][$id]['js'] as $v ){
					if( preg_match('/\/jquery\//',$v['url']) ) continue;
					
					PageComposer::registerJS("modules/widget_revslider/sliders/slider_{$id}/".$v['url'],'head');
				}
			}
		}
		
		
	}
	function registerCSS($data=null){
		/*
			se il widget necessita di un file css allora occorre registralo in questo modo
			
			PageComposer::registerCSS("url del file"); 
			

		*/
		$parameters = unserialize($data['parameters']);
		
		$id  = $parameters['id_slider'][$GLOBALS['activelocale']];
		if( (int)$id ){
			$this->getDataSlider($id);
			if( okArray($GLOBALS['widget_revslider'][$id]['css']) ){
				foreach( $GLOBALS['widget_revslider'][$id]['css'] as $v ){
					if( preg_match('/http/',$v['url']) ){
						PageComposer::registerCSS($v['url']);
					}else{
						PageComposer::registerCSS("modules/widget_revslider/sliders/slider_{$id}/".$v['url']);
					}
				}
			}
		}
	}

	function build($data=null){
			
			//$template = Marion::widget(basename(__DIR__)); //oggetto di tipo template che legge nei template del modulo
	
			
			
			/*$parameters: parametri di configurazione del widget
			  Questo array contiene i parametri di configurazione del widget
			*/
			$parameters = $this->getParameters();

			
			

			/*
				INSERISCI IL CODICE DEL WIDGET




			*/
			//debugga($parameters);exit;
			$id  = $parameters['id_slider'][$GLOBALS['activelocale']];
			

			
			$this->getDataSlider($id);
			echo $GLOBALS['widget_revslider'][$id]['content'];
			
			
				
		
	}





	function export($directory){
		$parameters = $this->getParameters();
		$database = Marion::getDB();
		$dati = array();
		
		$sliders = array();


		if(okArray($parameters)){
			foreach($parameters['id_slider'] as $lang => $id){
				$res = $database->select('*','revolution_slider',"id={$id}");
				
				if( okArray($res) ){
					$dati[] = $res[0];
					
					$path = _MARION_MODULE_DIR_."widget_revslider/sliders/slider_".$res[0]['id'];
					if(file_exists($path)){
						$sliders[] = array(
							'relative' => "slider_".$res[0]['id'],
							'absolute' => $path,

						);
					}
				}
			}
		}
		
		foreach($sliders as $slider){
			$dest = $directory."/".$slider['relative'].".zip";
			
			PageComposerTools::Zip($slider['absolute'],$dest);
		}

		file_put_contents($directory."/dati.json",json_encode($dati));
		

	}


	function import($directory){
		$database = Marion::getDB();


		


		$parameters = $this->getParameters();
		//debugga($parameters);
		$dati = json_decode(file_get_contents($directory."/dati.json"),true);
		
		$associa = array();
		
		foreach($dati as $v){
			$id_old = $v['id'];
			unset($v['id']);
			$id = $database->insert('revolution_slider',$v);
			$content = $v['content'];
			
			$content = preg_replace("/slider_{$id_old}/","slider_{$id}",$content);
			$database->update('revolution_slider',"id={$id}",array('content' => $content));
			$associa[$id_old] = $id;
		
		}
		

		foreach($parameters['id_slider'] as $lang => $v){
			$parameters['id_slider'][$lang] = $associa[$v];
		}

		
		$list = scandir($directory);
		foreach($list as $file){
			if( preg_match('/zip/',$file) ){
				$path_zip = $directory."/".$file;
				$name = explode('.',$file);
				$zip = new \ZipArchive;
				if ($zip->open($path_zip) === TRUE) {
					$zip->extractTo($directory."/".$name[0]);
					$zip->close();
				
				}
			}
		}
		

		foreach($associa as $id_old => $id){
			$path_old = $directory."/slider_".$id_old;
			$path_new = _MARION_MODULE_DIR_."widget_revslider/sliders/slider_".$id;
			rename($path_old,$path_new);
		}
		//debugga($parameters);exit;
		$this->setParameters($parameters);
		
		
		return true;

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