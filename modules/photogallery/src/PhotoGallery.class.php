<?php
namespace Photogallery;
use Marion\Core\Base;
use Marion\Core\Marion;
class PhotoGallery extends Base{
	// COSTANTI DI BASE
	const TABLE = 'photo_gallery'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'photo_gallery_lang'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'photo_gallery_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 
	//TIPI DI URL
	public static $_typeUrl_multilocale = array(
			0 => _MARION_BASE_URL_."index.php?ctrl=Front&mod=photogallery&action=show_gallery&lang=%s&slug=%s",
			1 => _MARION_BASE_URL_."%s/album/%s.htm",
			2 => _MARION_BASE_URL_."%s-album-%s.htm",
			3 => _MARION_BASE_URL_."%s/gallery/%s.htm",
			4 => _MARION_BASE_URL_."%s-gallery-%s.htm"
		);
	
	public static $_typeUrl = array(
			
			0 => _MARION_BASE_URL_."index.php?ctrl=Front&mod=photogallery&action=show_gallery&slug=%s",
			1 => _MARION_BASE_URL_."album/%s.htm",
			2 => _MARION_BASE_URL_."album-%s.htm",
			3 => _MARION_BASE_URL_."gallery/%s.htm",
			4 => _MARION_BASE_URL_."gallery-%s.htm"
		);

	//restiuisce l'url della fotogallery
	function getUrl($locale){
		if(!$locale){ 
			$locale = _MARION_LANG_;
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		
		
		
		
		$id = $this->getId();
		
		
		$slug = $this->get('slug',$locale);
		
		
		if( isMultilocale() ){
			$typeUrls = self::$_typeUrl_multilocale;
		}else{
			$typeUrls = self::$_typeUrl;
		}
		if( isMultilocale() ){
			if($this->url_type){
				$url = sprintf($typeUrls[$this->urlType],$locale,$slug);
			}else{
				$url = sprintf($typeUrls[0],$locale,$slug);
			}
		}else{
			if($this->url_type){
				$url = sprintf($typeUrls[$this->urlType],$slug);
			}else{
				$url = sprintf($typeUrls[0],$slug);
			}
		}
		
		return $url;
	}

	//restituisce i vari tipi di url della photogallery
	public static function getTypeUrl(){
		if( isMultilocale() ){
			return self::$_typeUrl_multilocale;
		}else{
			return self::$_typeUrl;
		}	
		
	}


	//restituisce i vari tipi di url della photogallery
	public function deleteAllImages(){
		if( $this->hasId() ){
			$database = Marion::getDB();
			$images = PhotoGalleryImage::prepareQuery()->where('gallery',$this->id)->get();

			foreach( $images as $im){
				$im->delete();
			}
		}
		return $this;
	}



	function getImages($limit=NULL,$offset=NULL){

		$query = PhotoGalleryImage::prepareQuery()->where('gallery',$this->id);
		if( $limit ){
			$query->limit($limit);
		}
		if( $offset ){
			$query->offset($offset);
		}
		$images = $query->get();
		return $images;
	}


	function getPreview($type='original'){
		$image = PhotoGalleryImage::prepareQuery()->where('gallery',$this->id)->getOne();
		if( is_object($image) ){
			return $image->getUrlImage($type);
		}
		return false;
	}


	//restituisce una news a partire dal suo slug
	public static function withSlug($slug){
		$query = self::prepareQuery()->where('slug',$slug)->where('locale',$GLOBALS['activelocale']);
		
		$gallery = $query->getOne();
		//debugga($query->lastquery);exit;
		return $gallery;
	}

	public function checkSave(){
		$res = parent::checkSave();
		
		if( $res == 1 ){
			if( $this->hasId() ){
				
				foreach(Marion::getConfig('locale','supportati') as $loc){
					$query = self::prepareQuery()->where('id',$this->id,'<>');
					$query->where('slug',$this->_localeData[$loc]['slug']);
					$check = $query->getOne();
					if( is_object($check) ){
						return "slug_duplicate";
					}
				}
			}else{

				foreach(Marion::getConfig('locale','supportati') as $loc){
					$query = self::prepareQuery();
					$query->where('slug',$this->_localeData[$loc]['slug']);

					$check = $query->getOne();
					
					if( is_object($check) ){
						return "slug_duplicate";
					}
				}
			}
			return 1;
		}else{
			return $res;
		}
	}
}


?>