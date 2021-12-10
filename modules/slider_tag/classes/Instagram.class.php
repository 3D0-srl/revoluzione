<?php


class InstagramImage extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'instagram_image'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = ''; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	

	

	function getDivWithTags(){
		$database = _obj('Database');
		$res = $database->select('*','image_map_tag',"pic_id=" . $this->id);
		
		$html = "<div class='img_instagram instagram_photo' id='instagram_{$this->id}'>";
		$html .= "<img src='{$this->url_image}'/>";
		$this->products = array();
		foreach($res as $k => $v){
			$top = ($v['pic_y']+50)*100/640;
			$top2 = ($v['pic_y']+50)*100/640;
			$left = ($v['pic_x']*100/640)-11.5;
			$left2 = ($v['pic_x']+100)*100/640;
			$url = '';

			
			if( $v['id_product'] ){
				$product = Product::withId($v['id_product']);
				if( is_object($product) ){
					$limit = 6;
					
					if( count($this->products) < $limit ){
						$dati = array(
								'id' => $product->id,
								'url' => $product->getUrl(),
								'image' => $product->getUrlImage(0),
								'price' => $product->getPriceFormatted(),
								'name' => $product->get('name'),
						);
						$this->products[] = $dati;
						$url = $dati['url'];
						$id_prodotto = $v['id_product'];
					}else{
						$url = $product->getUrl();
						$id_prodotto = $product->id;
					}

					if( !trim($v['name']) ){
						$v['name'] = $product->get('name');
					}
					
				}
				

			}
			$ind = $k+1;
			$color = $v['color'];
			if( $url ){
				$html .= "<span><a href='{$url}'><div class='pallina_tag pallinatag_{$id_prodotto}' style='left:{$left2}%; top:{$top}%; background-color:{$color}'>&nbsp;</div></a><div class='tag_instagram' style='left:{$left}%; top:{$top2}%;'>{$v['name']}</div></span>";
			}else{
				$html .= "<span><a><div class='pallina_tag' style='left:{$left2}%; top:{$top}%;background-color:{$color}'>&nbsp;</div></a><div class='tag_instagram' style='left:{$left}%; top:{$top2}%;'>{$v['name']}</div></span>";
			}
			
		}
		
		$html .= "</div>";

		
		return $html;
	
	}



}


?>