<?php

class ArticleCategory extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'articleCategory'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'articleCategoryLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'articleCategory';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = 'parent'; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	

	// COSTANTI RELATIVE ALLA CLASSE ArticleCategory 
	const NAME_FIELD_TABLE = 'name'; //campo contenete il nome della sezione 

	public static function getAll($locale='it'){
		$database = _obj('Database');
		$sezioni = $database->select('*',STATIC::TABLE.' as s join '.STATIC::TABLE_LOCALE_DATA.' as l on s.'.STATIC::TABLE_PRIMARY_KEY.'=l.'.STATIC::TABLE_EXTERNAL_KEY,"locale='{$locale}'");
		
		if(okArray($sezioni)){
			$codici = array();
			foreach($sezioni as $v){
				$current = $v;
				$array_name = array($v[STATIC::NAME_FIELD_TABLE]);
				
				while(okArray($current) && $current[STATIC::PARENT_FIELD_TABLE] != 0){
					
					$current = $database->select('*',STATIC::TABLE.' as s join '.STATIC::TABLE_LOCALE_DATA.' as l on s.'.STATIC::TABLE_PRIMARY_KEY.'=l.'.STATIC::TABLE_EXTERNAL_KEY,STATIC::TABLE_PRIMARY_KEY."=".$current[STATIC::PARENT_FIELD_TABLE]." AND locale ='{$locale}'");
					if(okArray($current)){ 
						$current = $current[0]; 
						$array_name[] = $current[STATIC::NAME_FIELD_TABLE];
					}
					
					
				}
				
				$array_name = array_reverse($array_name);
				$name = '';
				foreach($array_name as $v1){
					$name .= "{$v1} / ";
				}
				$name = preg_replace('/\/ $/','',$name);
				
				$codici[$v['id']] = $name; 
			}
		}
		if(okArray($codici)){
			asort($codici);
			return $codici;
		}
		return false;
	}


	public function getFullName($locale='it'){
		
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if($this->$field_id){
			
			$database = _obj('Database');
			$filed_id = STATIC::TABLE_EXTERNAL_KEY;
			$sezione = $database->select('*',STATIC::TABLE.' as s join '.STATIC::TABLE_LOCALE_DATA.' as l on s.'.STATIC::TABLE_PRIMARY_KEY.'=l.'.STATIC::TABLE_EXTERNAL_KEY,"locale='{$locale}' and ".STATIC::TABLE_PRIMARY_KEY."={$this->$field_id}");
			
			if(okArray($sezione)){
				$sezione = $sezione[0];
				$array_name = array($sezione[STATIC::NAME_FIELD_TABLE]);
				$current = $sezione;
				while(okArray($current) && $current[STATIC::PARENT_FIELD_TABLE] != 0){
					
					$current = $database->select('*',STATIC::TABLE.' as s join '.STATIC::TABLE_LOCALE_DATA.' as l on s.'.STATIC::TABLE_PRIMARY_KEY.'=l.'.STATIC::TABLE_EXTERNAL_KEY,STATIC::TABLE_PRIMARY_KEY."=".$current[STATIC::PARENT_FIELD_TABLE]." AND locale ='{$locale}'");
					if(okArray($current)){ 
						$current = $current[0]; 
						$array_name[] = $current[STATIC::NAME_FIELD_TABLE];
					}
					
					
				}
				
				$array_name = array_reverse($array_name);
				$name = '';
				foreach($array_name as $v1){
					$name .= "{$v1} / ";
				}
				$name = trim(preg_replace('/\/\s$/','',$name));
				return $name;
				
			}
		}
		return false;
	}

	function beforeSave(){
		foreach($this->_localeData as $loc => $values){
			$this->_localeData[$loc]['prettyUrl'] = trim(strtolower($values['prettyUrl']));
		}
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

	

	function getNumPosts(){
		$database = _obj('Database');
		$codes = $database->select('*','articleCategoryComposition',"articleCategory={$this->id}");
		$tot = 0;
		if( okArray($codes) ){
			$where = "id in (";
			foreach($codes as $v){
				$where .= "{$v['article']},";
			}
			$where = preg_replace('/\,$/',')',$where);
			

			$tot = $database->select('count(*) as cont',"article","{$where} AND visibility = 1");
			$tot = $tot[0]['cont'];
		}

		return $tot;
	}


	function getPosts($limit=NULL,$offset=NULL){
		
		$database = _obj('Database');
		$codes = $database->select('*','articleCategoryComposition',"articleCategory={$this->id}");
		
		if( okArray($codes) ){
			$where = "id in (";
			foreach($codes as $v){
				$where .= "{$v['article']},";
			}

			$where = preg_replace('/\,$/',')',$where);
			$query = Article::prepareQuery()->whereExpression($where)->where('visibility',1)->orderBy('dateCreation','DESC');
			if( $limit ){
				$query->limit($limit);
			}
			if( $offset ){
				$query->offset($offset);
			}
			$posts = $query->get();
		}

		return $posts;


	}


	function getCountPosts(){
		$database = _obj('Database');
		$num = $database->select('count(*) as count','articleCategoryComposition',"articleCategory={$this->id}");
		//debugga($num);exit;
		return $num[0]['count'];
	}

	function getUrl(){
		if( isMultilocale()){
			return $GLOBALS['activelocale']."/posts/".$this->get('prettyUrl').".htm";
		}else{
			return "/posts/".$this->get('prettyUrl').".htm";
		}

	}

}


?>