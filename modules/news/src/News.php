<?php
namespace News;
use Marion\Core\{Marion,BaseWithImages};
class News extends BaseWithImages{
	
	// COSTANTI DI BASE
	const TABLE = 'news'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'news_lang'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'news_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 
	//TIPI DI URL
	public static $_typeUrl = array(
			1 => _MARION_BASE_URL_."news/%s-%s",
			2 => _MARION_BASE_URL_."n/%s-%s",
		);




	//restiuisce l'url del prodotto 
	function getUrl($locale=NULL){

		
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
	
		
	
		$id = $this->getId();
		
		$prettyUrl = $this->get('slug',$locale);
		if($prettyUrl){
			$name = $prettyUrl; 	
		}else{
			$name = $this->get('title',$locale);
			$name = Marion::slugify($name);
		}
		

	
		$typeUrls = self::$_typeUrl;
		

		$type_url = $this->urlType;
		
	
		if( !$type_url) $type_url = 1;
		$url = sprintf($typeUrls[$type_url],$id,$name);
		
		

		

		return $url;
	}

	//restituisce i vari tipi di url del prodotto
	public static function getTypeUrl(){
		
		return self::$_typeUrl;
		
		
	}


	//restituisce una news a partire dal suo slug
	public static function withSlug($slug){
		$news = self::prepareQuery()->where('slug',$slug)->where('locale',$GLOBALS['activelocale'])->getOne();
		return $news;
	}




	//override metodi
	

	public function beforeSave(){
		parent::beforeSave();
		foreach($this->_localeData as $loc => $v){
			if( !$v['slug'] ) $this->_localeData[$loc]['slug'] = Marion::slugify($v['title']);
		}
	}


	public function checkSave(){
		$res = parent::checkSave();
		
		if( $res == 1 ){
			if( $this->hasId() ){
				
				foreach(getConfig('locale','supportati') as $loc){
					$query = self::prepareQuery()->where('id',$this->id,'<>');
					$query->where('slug',$this->_localeData[$loc]['slug']);
					$check = $query->getOne();
					if( is_object($check) ){
						return "slug_duplicate";
					}
				}
			}else{

				foreach(getConfig('locale','supportati') as $loc){
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



	function getTruncateContent($limit){
		$string = $this->get('content');
		
		$string = strip_tags($string);

		if (strlen($string) > $limit) {

			// truncate string
			$stringCut = substr($string, 0, $limit);

			// make sure it ends in a word so assassinate doesn't become ass...
			$string = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
		}
		 return $string;
	
	}

	function getTruncateTitle($limit){
		$string = $this->get('title');
		
		$string = strip_tags($string);

		if (strlen($string) > $limit) {

			// truncate string
			$stringCut = substr($string, 0, $limit);

			// make sure it ends in a word so assassinate doesn't become ass...
			$string = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
		}
		 return $string;
	
	}

	//restituisce il nome della categoria
	function getCategoryName(){
		if( $this->type_news){
			$type = NewsType::withId($this->type_news);
			if( is_object($type) ){
				return $type->get('name');
			}
		}
		return false;
		
	}

}


?>