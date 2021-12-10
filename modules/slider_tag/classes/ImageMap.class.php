<?php


class ImageMap extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'image_map'; // nome della tabella a cui si riferisce la classe
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
	
	

	function getUrlImage(){
		if( $this->url_image){
			return $this->url_image;
		}else{
			return "/img/".$this->image."/or/slide-{$this->id}.png";
		}
	}


	function getNumTags(){
		$database = _obj('Database');
		$res = $database->select('count(*) as cont','image_map_tag',"pic_id=" . $this->id);
		return $res[0]['cont'];
	}
	

	function getDivWithTags(){
		$database = _obj('Database');
		$res = $database->select('*','image_map_tag',"pic_id=" . $this->id);
		
		$html = "<div class='img_instagram instagram_photo' id='instagram_{$this->id}'>";
		$html .= "<img src='{$this->getUrlImage()}'/>";
		$this->products = array();
		foreach($res as $k => $v){
			$top = ($v['pic_y'])*100/$v['height'];
			$top2 = ($v['pic_y'])*100/$v['height'];
			$left = ($v['pic_x']*100/$v['width'])-11.5;
			$left2 = ($v['pic_x'])*100/$v['width'];
			$url = '';
			$class = '';
			$template = _obj('Template');
			$activecurrency = $template->activecurrency;
			if( $v['product'] ){
				
				$product = Product::withId($v['product']);
				
				if( is_object($product) ){

					$v['url'] = $product->getUrl();
					$v['prezzo'] = $product->getPriceFormatted(1);
					
					/*$limit = 6;
					
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
					}*/

					if( !trim($v['name']) ){
						$v['name'] = $product->get('name');
					}
					
					if( $product->images[0]){
						$class = 'product_tag';

						$v['name'] = "<img src='".$product->getUrlImage(0,'thumbnail')."'/><p class='nome_prodotto_slider_tag'>{$v['name']}</p><p class='prezzo_prodotto_slider_tag'>{$activecurrency} {$v['prezzo']}</p>";
					}
					
				}
				

			}
			$ind = $k+1;
			
			if( $v['url'] ){
				$html .= "<span><a href='{$v[url]}'><div class='pallina_tag' style='left:{$left2}%; top:{$top}%;'><img src='/modules/slider_tag/".$v['icon']."'/></div></a><div class='tag_instagram {$class}' style='left:{$left2}%; top:{$top2}%;'>{$v['name']}</div></span>";
			}else{
				$html .= "<span><a><div class='pallina_tag' style='left:{$left2}%; top:{$top}%;'><img src='/modules/slider_tag/".$v['icon']."'/></div></a><div class='tag_instagram' style='left:{$left2}%; top:{$top2}%;'>{$v['name']}</div></span>";
			}
			
			
		
			
		}
		
		$html .= "</div>";

		
		return $html;
	
	}



}


?>