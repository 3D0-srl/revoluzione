<?php

class Page extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'page'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'pageLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'page';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	
	function setLayout($id_layout){
		$this->layout = $id_layout;
	}

	function afterSave(){
		parent::afterSave();
		if( $this->advanced && $this->layout){

			
			$database = _obj('Database');
			
			$toinsert = array(
				'id_layout' => $this->layout,
			);
			if(  !$this->id_adv_page  ){
				$this->id_adv_page = $database->insert('page_advanced',$toinsert);
				$this->save();
			}else{
				$database->update('page_advanced',"id={$this->id_adv_page}",$toinsert);
			}
			
			
		}
	}
	function checkSave(){
		$res = parent::checkSave();
		

		if( $res == 1 ){
			foreach($this->_localeData as $loc => $values){
				$url = $values['url'];
				$query = self::prepareQuery()->where('url',$url)->where('theme',$this->theme)->where('locale',$loc);
				if( $this->id){
					$query->where('id',$this->id,'<>');
				}
				$check = $query->getOne();
				if( is_object($check) ){
					return "url_duplicate";
				}
			}

			return 1;
			

		}else{
			return $res;
		}
	}


	public static function getByUrl($url){
		$query = Page::prepareQuery()
			->where('url',$url)
			->where('locale',$GLOBALS['activelocale']);
		$theme = Marion::getConfig('SETTING_THEMES','theme');
		$query->whereExpression("(theme IS NULL OR theme='0' OR theme='{$theme}')");
		if( !auth('cms') ){
			$query->where('visibility',1);
		}
		$page = $query->getOne();
		return $page;
	}



	function getContent($locale=NULL){
		if( !$locale ){
			$locale = $GLOBALS['activelocale'];
		}

		$cont = $this->get('content');

		Marion::do_action('action_parse_html',array($cont));

		return $cont;


	}

	function getUrl($locale=null){
		return _MARION_BASE_URL_."p/".$this->get('url',$locale).".htm";
	}
}



class PageItemFrontend implements MenuItemFrontendInterface{
	
	public static function getGroupName(): string{
		 return 'Pagina';
	}


	public static function getUrl(array $params):string{
		$locale = $params['locale'];
		$id = $params['value'];
		
		$page = Page::withId($id);
		if( is_object($page) ){
			return $page->getUrl($locale);
		}
		
		return '';
	}
	
	public static function getPages():array{
		
		$list = Page::prepareQuery()->where('visibility',1)->where('widget',0)->get();
		$list_url = array();
		if( okArray($list) ){
			foreach($list as $v){
				$list_url[$v->id] = $v->get('title');
			}
		}

		return $list_url;
	}


}

LinkMenuFrontend::registerItem('PageItemFrontend');


?>