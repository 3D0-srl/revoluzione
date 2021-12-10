<?php
use Marion\Core\Marion;
function widget_slider_brands_refresh_cache(){
	
	$database = Marion::getDB();
	$select = $database->select('*','composition_page_tmp as h join module as m on m.id=h.module',"m.directory='widget_slider_brands'");
	if( okArray($select) ){
		$cache = _obj('Cache');
		foreach($select as $v){
			$dati = unserialize($v['parameters']);
			if( $dati['id_slider'] == $res->id_slider ){
				$key = 'sliderbrands_'.$dati['id_box'];
				

				if( $cache->isExisting($key) ){
					$cache->delete($key);
				}
			}
			
		}
	}
	//debugga($_POST);exit;
}

Marion::add_action('after_save_manufacturer','widget_slider_brands_refresh_cache');
Marion::add_action('my_after_save_manufacturer','widget_slider_brands_refresh_cache');

?>