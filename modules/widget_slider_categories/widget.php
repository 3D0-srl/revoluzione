<?php
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
class WidgetSliderCategoriesComponent extends  PageComposerComponent{
	


	function registerJS($data=null){
		PageComposer::registerJS("https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.2/js/swiper.js");
		PageComposer::registerJS("modules/widget_slider_categories/js/script.js");
	}

	function registerCSS($data=null){
		PageComposer::registerCSS("https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.5/css/swiper.min.css");
		PageComposer::registerCSS("modules/widget_slider_categories/css/frontend.css");
	}

	function build($data=null){
			
		//$widget = Marion::widget('widget_slider_brands');

		
		
		$dati = $this->getParameters();
		

		if( $dati ){
			if( $dati['title'][$GLOBALS['activelocale']] ){
				$params['titolo'] = $dati['title'][$GLOBALS['activelocale']];
			}
			$where = '';
			foreach($dati['categories'] as $v){
				$where .= "{$v},";
			}
			$where = preg_replace('/,$/','',$where);
			$sections = Section::prepareQuery()
				->where('visibility',1)
				->whereExpression("(id in ({$where}))")
				->get();
			$params['id_box']= $this->id_box;
			foreach($params as $k => $v){
				$this->setVar($k,$v);
			}
			$this->setVar('list', $sections);
			$this->output('render.htm');
			
		}
	}
}




?>