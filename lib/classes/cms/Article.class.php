<?php

class Article extends BaseWithImages{
	
	// COSTANTI DI BASE
	const TABLE = 'article'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'articleLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'article';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	
	//metodo richiamato quando l'oggetto viene creato per la prima volta
	public function afterLoad()
	{
		parent::afterLoad();
		$this->categories = array();
		if( $this->id){
			$database = _obj('Database');
			$categories = $database->select('*','articleCategoryComposition',"article={$this->id}");
			foreach($categories as $k => $v){
				$this->categories[] = $v['articleCategory'];
			}
		}

		$this->tags = array();
		if( $this->id){
			$database = _obj('Database');
			$tags = $database->select('*','articleTagComposition',"article={$this->id}");
			foreach($tags as $k => $v){
				$this->tags[] = $v['tag'];
			}
		}
		
	}

	//funzione chiamata prima del salvataggio dell'oggetto. In questa funzione si effettuano delle operazioni preliminari prima del salvataggio
	public function beforeSave(){
		parent::beforeSave();
		if( !$this->id ){
			$user = Marion::getUser();
			$this->author = $user->id;
			$this->dateCreation = date('Y-m-d H:i:s');
		}
		$this->dateLastUpdate = date('Y-m-d H:i:s');
	}


	function checkSave(){
		

		
		foreach($this->_localeData as $loc => $values){
			$prettyurl = $values['prettyUrl'];
			if(!$prettyurl) return 'missing_pretty_url';
		}
		
		
		
	
		//controllo se il prettyurl è univovo
		foreach($this->_localeData as $loc => $values){
			$prettyurl = $values['prettyUrl'];
			$query = self::prepareQuery()->where('prettyUrl',$prettyurl)->where('locale',$loc);
			if( $this->id){
				$query->where('id',$this->id,'<>');
			}
			$check = $query->get();
			if( okArray($check) ) return 'duplicate_pretty_url';

		}
		
		return true;
		
	}


	//funzione chiamata dopo il salvataggio dell'oggetto
	public function afterSave()
	{
		parent::afterSave();
		$this->saveCategories();
		$this->saveTags();


	}


	function delete(){
		parent::delete();
		$database = _obj('Database');
		$database->delete('articleCategoryComposition',"article={$this->id}");
		$comments = ArticleComment::prepareQuery()->where('article',$this->id)->get();
		if(okArray($comments) ){
			foreach($comments as $comment){
				$comment->delete();
			}
		}
		
	}
	
	function setCategories($array){
		$this->categories = $array;
	}

	function setTags($array){
		$this->tags = $array;
	}


	function saveCategories(){
		$database = _obj('Database');
		$database->delete('articleCategoryComposition',"article={$this->id}");
		
		if( okArray($this->categories) ){

			
			foreach($this->categories as $v){
				$toinsert = array(
						'article'=>$this->id,
						'articleCategory'=>$v
					);
				$database->insert('articleCategoryComposition',$toinsert);
				
			}
		}
		
	}


	function saveTags(){
		$database = _obj('Database');
		$database->delete('articleTagComposition',"article={$this->id}");
		
		if( okArray($this->tags) ){

			
			foreach($this->tags as $v){
				$toinsert = array(
						'article'=>$this->id,
						'tag'=>$v
					);
				$database->insert('articleTagComposition',$toinsert);
				
			}
		}
		
	}


	function getCategoriesString(){
		$database = _obj('Database');
		if( okArray($this->categories) ){
			
			foreach($this->categories as $v){
				$obj = ArticleCategory::withId($v);
				if( is_object($obj) ){
					$cat .= $obj->getFullName()."<br>";
				}
			}
		}

		return $cat;
	}


	function getAuthorName(){
		if( $this->id && $this->author){
			$user = User::prepareQuery()->where('id',$this->author)->getOne();
			
			if( is_object($user) ){
				return $user->name." ".$user->surname;
			}else{
				return false;
			}
		}
	}

	public function getComments(){
		if( $this->id){
			return ArticleComment::prepareQuery()->where('article',$this->id)->orderBy('id','ASC')->get();
		}else{
			return false;
		}
	}

	public function getTags(){
		if( okArray($this->tags) ){
			foreach($this->tags as $cod){
				$tag = ArticleTag::withId($cod);
				if( is_object($tag) ){
					$tags[] = $tag;
				}
			}
			return $tags;
			
		}else{
			return false;
		}
	}

	public static function withSlug($slug,$locale='it'){
		$query = self::prepareQuery()
			->where('prettyUrl',$slug)
			->where('locale',$locale);
		$res = $query->getOne();
		return $res;

	}


	function getCountComments(){
		
		$database = _obj('Database');
		$num = $database->select('count(*) as count','articleComment',"article={$this->id}");
		//debugga($num);exit;
		return $num[0]['count'];
	}




	function getContentShort($length=100){
		$template = _obj('Template');
		return $template->limit_text($this->get('content'),$length);

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


	function getUrl(){
		if( isMultilocale()){
			return $GLOBALS['activelocale']."/post/".$this->get('prettyUrl').".htm";
		}else{
			return "/post/".$this->get('prettyUrl').".htm";
		}

	}
}


?>