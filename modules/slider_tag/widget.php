<?php
	
function slider_tag(){
	$module_dir = 'slider_tag';
	$widget = Marion::widget($module_dir);
	require_once('modules/slider_tag/classes/ImageMap.class.php');


	
	$list = ImageMap::prepareQuery()->get();
	
	foreach($list as $v){
		$widget->images[] =$v->getDivWithTags();
	}
	
	

	ob_start();
	$widget->output('slider.htm');
	$html = ob_get_contents();
	ob_end_clean();
	
	
	return $html;
	
}

Marion::add_widget('index.htm','slider_tag','sliderfull','frontend',2,'append');

?>