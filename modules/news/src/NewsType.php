<?php
namespace News;
use Marion\Core\{Marion,Base};
class NewsType extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'news_type'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'news_type_lang'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'news_type_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 


	public static $_typeUrl = array(
			1 => "/news/%s",
			2 => "/n/%s",
		);
	

	//restiuisce l'url del prodotto 
	function getUrl($locale=null){
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
			$name = $this->get('name',$locale);
		}
		$name = Marion::slugify($name);
		$type_url = $this->urlType;
		
		$typeUrls = self::$_typeUrl;
		if( !$type_url ) $type_url = 1;
		$url = sprintf($typeUrls[$type_url],$name);
		

		return $url;
	}

	//restituisce i vari tipi di url del prodotto
	public static function getTypeUrl(){
		return self::$_typeUrl;
	
	}


	//restituisce una news a partire dal suo slug
	public static function withSlug($slug){
		$news = self::prepareQuery()->where('slug',$slug)->where('locale',_MARION_LANG_)->getOne();
		return $news;
	}



	




	//override metodi

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



	function beforeSave(){
		parent::beforeSave();

		if( $this->default_news ){
			$database = Marion::getDB();
			$database->update('newsType',"1=1",array('default_news' => 0));
		}
	}

}


?>