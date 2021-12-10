<?php
	
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
class WidgetInstagramGallery extends  PageComposerComponent{
	
		
		
		function registerJS($data=null){
			/*
				se il widget necessita di un file js allora occorre registralo in questo modo
				
				PageComposer::registerJS("url del file"); // viene caricato alla fine della pagina
				PageComposer::registerJS("url del file",'head'); // viene caricato nel head 
				

			*/
			$options = Marion::getConfig('instagram_gallery');
			if( $options['show_slider'] ){
				PageComposer::registerJS('plugins/bxslider-4/dist/jquery.bxslider.min.js','end');
							
				PageComposer::registerJS("/modules/instagram_gallery/js/frontend.js");
			}
		}
		function registerCSS($data=null){
			/*
				se il widget necessita di un file css allora occorre registralo in questo modo
				
				PageComposer::registerCSS("url del file"); 
				

			*/
			PageComposer::registerCSS('plugins/bxslider-4/dist/jquery.bxslider.min.css');
			PageComposer::registerCSS("/modules/instagram_gallery/css/frontend.css");
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

				require_once('classes/Instagram.class.php');
				
				$options = Marion::getConfig('instagram_gallery');

				
				
				$this->setVar('show_data',$options['show_info_images']);
				$this->setVar('show_slider',$options['show_slider']);
				
				if( !$options['widget_layout'] ){
					$list = InstagramImage::prepareQuery()->where('visibility',1)->orderBy('created_time','DESC')->limit(10)->get();
					if( $options['date_format'] ){
						$date_format = $options['date_format'];
						foreach($list as $v){
							$v->created_time = strftime($date_format,strtotime($v->created_time));
							
						}
					}
					$this->setVar('list',$list);
					$this->output('widget.htm');
				}else{

					$list = InstagramImage::prepareQuery()->where('visibility',1)->orderBy('created_time','DESC')->limit(8)->get();
					if( $options['date_format'] ){
						$date_format = $options['date_format'];
						foreach($list as $v){
							$v->created_time = strftime($date_format,strtotime($v->created_time));
							
						}
					}
					
					$immagine = Marion::getConfig('instagram_gallery','image');
					if( $immagine ){
						$data['image'] = "/img/".$immagine."/or/instagram.jpg";
					}
					foreach($list as $k => $v){
						$v->ordine = $k;
						if( $k < 4 ){
							$dati1[] = $v;
						}else{
							$dati2[] = $v;
						}
					}
					if( count($dati1)  < 4 ){
						$tot = count($dati1);
						for( $i =0; $i < 4-$tot; $i++ ){
							$dati1[] = 'ciao'; 
						}
					}
				
					if( count($dati2)  < 4 ){
						$tot = count($dati2);
						for( $i =0; $i < 4-$tot; $i++ ){
							$dati2[] = 'ciao'; 
						}
					}
					$data['dati1'] = $dati1;
					$data['dati2'] = $dati2;
					$data['list'] = $list;
					
					
					foreach($data as $k => $v){
						$this->setVar($k,$v);
					}
					
					$this->setVar('text',$options['widget_text']);
					$this->output('widget_with_image.htm');

				}
				//$template->output($this->template_html);
					
			
		}


		//questo metodo stabilisce se per un determiato box della pagina il widget è disponibile
		function isAvailable($box){
			$available = true;
			switch($box){
				case 'col-100':
					//$available = false;
					break;
				case 'col-75':
					//$available = false;
					break;
				case 'col-33':
					//$available = false;
					break;
				case 'col-25':
					//$available = false;
					break;
				default:
					//$available = false;
					break;

			}
			
			
			return $available;
		}
	}

	
?>