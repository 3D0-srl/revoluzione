<?php
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
class WidgetSliderBrandsComponent extends  PageComposerComponent{
	

	function registerCSS($data=null){
		PageComposer::registerCSS("modules/widget_slider_brands/css/style.css");
	}
	function registerJS($data=null){
		PageComposer::loadJS('bxslider');
		PageComposer::registerJS("modules/widget_slider_brands/js/script.js",'end');
	}

	function build($data=null){
			
			//$widget = Marion::widget('widget_slider_brands');
	
			$html = '';
			$cache = _obj('Cache');
			$key_cache = 'sliderbrands_'.$this->id_box;
			
			//$html = $cache->get($key_cache);
			
			if( !$html ){
				$dati = $this->getParameters();
				
				if( $dati ){
					$database = _obj('Database');
					$list =Manufacturer::prepareQuery()->where('visibility',1)->get();
					
					//$widget->list_brands = $list;
					$this->setVar('list_brands',$list);
					//$widget->titolo = 'Best Sellers';
					

					if( $dati['title'][$GLOBALS['activelocale']] ){
						$params['titolo'] = $dati['title'][$GLOBALS['activelocale']];
					}
					
					
					$escludi = array('num_products','tag','id_box');
					foreach($dati as $k => $v){
						if( !okArray($v) && !in_array($k,$escludi)){
							$_tmp = explode('_',$k);
							if( !array_key_exists(1,$_tmp) ){
							//if( !$_tmp[1] ){
								$key = 'desktop';
							}else{
								$key = $_tmp[1];
							}
							$opzioni[$key][$_tmp[0]] = $v;
						}
					}
					
					$params['opzioni'] = json_encode($opzioni);
					
					$params['id_box'] = $this->id_box;
					foreach($params as $k => $v){
						$this->setVar($k,$v);
					}
					ob_start();
					$this->output('slider.htm');
					$html = ob_get_contents();
					ob_end_clean();
					
					$time_cache = $GLOBALS['setting']['default']['CACHE']['time'];
					$cache->set($key_cache,$html,$time_cache);
					
				}
			}
			echo $html;	
		
	}
}






?>