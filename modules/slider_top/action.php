<?php
	use \Marion\Core\Marion;
	function slider_top_widget(){
		
		$theme_text_short = false;
		
		$widget = new WidgetComponent('slider_top');
		
		require_once(_MARION_MODULE_DIR_.'slider_top/classes/SliderTopItem.class.php');
		$query = SliderTopItem::prepareQuery()
		->where('online',1)
		->whereExpression("((time_range IS NULL OR time_range = 0) OR (time_range = 1 AND date_start <= NOW() AND date_end >= NOW()))")
		->orderBy('order_view','ASC');


		
		
		$items = $query->get();
		//debugga($items);
		
		//$format = "d " + hours + "h "+ minutes + "m " + seconds + "s ""
		foreach($items as $item){
			$text = $item->get('text');
			
			if( $item->enable_countdown ){
				$date = $item->countdown;
				if( !$theme_text_short ){
					
					$d = _translate('days','slider_top');
					$h = _translate('hours','slider_top');
					$m = _translate('minutes','slider_top');
					$s = _translate('seconds','slider_top');

				}else{
					$d = _translate('d','slider_top');
					$h = _translate('h','slider_top');
					$m = _translate('m','slider_top');
					$s = _translate('s','slider_top');
				}
				$text = preg_replace('/\[countdown\]/',"<span class='slider_top_countdown' color='{$item->color}' background='{$item->background_color}' compact='{$item->compact_countdown}' date='{$date}' d='{$d}' h='{$h}' m='{$m}' s='{$s}'></span>",$text);
			}
			$text = preg_replace('/\[\[/',"<b>",$text);
			$text = preg_replace('/\]\]/',"</b>",$text);
			$item->text = $text;
		}
		$widget->setVar('items',$items);

		$widget->output('widget.htm');
			
	}

	Marion::add_action('display_start_top','slider_top_widget');






	function slider_top_set_media_ctrl( $ctrl){
		$ctrl->registerCSS('modules/slider_top/css/style.css');
		$ctrl->registerJS('plugins/slick/slick.min.js','end');
		$ctrl->registerCSS('plugins/slick/slick.css','end');
		$ctrl->registerCSS('plugins/slick/slick-theme.css','end');
		$ctrl->registerJS('modules/slider_top/js/script.js','end');
	}

	Marion::add_action('action_register_media_front','slider_top_set_media_ctrl');
?>