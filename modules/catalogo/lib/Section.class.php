<?php
use Marion\Core\{BaseWithIMages,Marion};
class Section extends BaseWithIMages{
	
	// COSTANTI DI BASE
	const TABLE = 'section'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'sectionLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'section';// nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = 'parent'; //nome del campo padre 
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	

	// COSTANTI RELATIVE ALLA CLASSE SEZIONE 
	const NAME_FIELD_TABLE = 'name'; //campo contenete il nome della sezione 

	
	//TIPI DI URL
	public static $_typeUrl = array(
			0 => "index.php?ctrl=Catalogo&mod=catalogo&action=section&section=%s&lang=%s",
			1 => "catalog/section/%s/%s.htm",
			2 => "catalog-section-%s-%s.htm",
			3 => "cat/sec/%s/%s.htm",
			4 => "cat-sec-%s-%s.htm"
		);

	public static $_typeUrl_multilocale = array(
			0 => "index.php?ctrl=Catalogo&mod=catalogo&action=section&section=%s",
			1 => "%s/catalog/section/%s/%s.htm",
			2 => "%s-catalog-section-%s-%s.htm",
			3 => "%s/cat/sec/%s/%s.htm",
			4 => "%s-cat-sec-%s-%s.htm"
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
		
		$prettyUrl = $this->get('prettyUrl',$locale);
		if($prettyUrl){
			$name = $prettyUrl; 	
		}else{
			$name = $this->get('name',$locale);
		}
		$name = Marion::slugify($name);

		if( !isMultilocale() ){
			$typeUrls = self::$_typeUrl;
			if($this->urlType){
				$url = sprintf($typeUrls[$this->urlType],$id,$name);
			}else{
				$url = sprintf($typeUrls[0],$id,$name);
			}
		}else{
			$typeUrls = self::$_typeUrl_multilocale;
			if($this->urlType){
				$url = sprintf($typeUrls[$this->urlType],$locale,$id,$name);
			}else{
				$url = sprintf($typeUrls[0],$id,$locale,$name);
			}
		}
		$url = _MARION_BASE_URL_.$url;
		return $url;
	}

	//restituisce i vari tipi di url del prodotto
	public static function getTypeUrl(){
		if( isMultilocale() ){
			return self::$_typeUrl_multilocale;
		}else{
			return self::$_typeUrl;
		}
	}


	public static function getAll($locale='it'){
		$database = Marion::getDB();
		//$sezioni = $database->select('*',STATIC::TABLE.' as s join '.STATIC::TABLE_LOCALE_DATA.' as l on s.'.STATIC::TABLE_PRIMARY_KEY.'=l.'.STATIC::TABLE_EXTERNAL_KEY,"locale='{$locale}'");
		//if( isDev() ){
		$sezioni = self::prepareQuery()->get();
		$tree = self::buildTree($sezioni);
		
		
		foreach($tree as $level1){
			$toreturn[$level1->id] = $level1->get('name');
			if( okArray($level1->children ) ){
				foreach($level1->children as $level2){
					$toreturn[$level2->id] = $level1->get('name')." / ".$level2->get('name');
					if( okArray($level2->children ) ){
						foreach($level2->children as $level3){
							$toreturn[$level3->id] = $level1->get('name')." / ".$level2->get('name')." / ".$level3->get('name');
							if( okArray($level3->children ) ){
								foreach($level3->children as $level4){
									$toreturn[$level4->id] = $level1->get('name')." / ".$level2->get('name')." / ".$level3->get('name')." / ".$level4->get('name');
									if( okArray($level4->children ) ){
										foreach($level4->children as $level5){
											$toreturn[$level5->id] = $level1->get('name')." / ".$level2->get('name')." / ".$level3->get('name')." / ".$level4->get('name')." / ".$level5->get('name');
												
											if( okArray($level5->children ) ){
												foreach($level5->children as $level6){
													$toreturn[$level6->id] = $level1->get('name')." / ".$level2->get('name')." / ".$level3->get('name')." / ".$level4->get('name')." / ".$level5->get('name')." / ".$level6->get('name');
												}
											}
										}
									}
								}
							}
						}
					}
				}

			}
		}
		uasort($toreturn,function($a,$b){
			 if ($a == $b) {
				return 0;
			}
			return ($a < $b) ? -1 : 1;
		});
			
		return $toreturn;
		/*if(okArray($sezioni)){
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
		return false;*/
	}


	public function getFullName($locale='it'){
		
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if($this->$field_id){
			
			$database = Marion::getDB();
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
				$name = trim(preg_replace('/\/ $/','',$name));
				return $name;
				
			}
		}
		return false;
	}

	//restituisce il numero di prodotti nella sezione
	public function getCountProduct(){
		$product = Product::prepareQuery()
			->where("section",$this->id)
			->where('visibility',1)
			->where('parent',0)
			->where('deleted',0)
			->get();
		return count($product);
	}
	


	//metodo che restituisce il percorso di un prodotto
	function breadCrumbs($template_html=NULL){
		
		
		$options_default = array(
			"before_html" => "<span>",
			"after_html" => "</span>",
			"divider_html" => " > ",

		);

		/*foreach($options as $k => $v){
			if( $options_default[$k] ){
				$options_default[$k] = $v;
			}
		}*/
		if( $this->id ){
			
			$section = self::withId($this->id);
			
			$list[] = array(
					'name' => $section->get('name'),
					'id' => $section->id,
					'parent' => $section->parent,
					'url' => $section->getUrl()
				);
			while( $section->parent ){
				$section = $section->getParent();
				$list[] = array(
					'name' => $section->get('name'),
					'id' => $section->id,
					'parent' => $section->parent,
					'url' => $section->getUrl()
				);

			}
			
			krsort($list);
			$list = array_values($list);
			$list[0]['first'] = 1;
			$list[count($list)-1]['last'] = 1;
			$breadCrumbs = '';
			if( !$template_html ){
			foreach($list as $v){
					$breadCrumbs .= $options_default['before_html'].$v['name'].$options_default['after_html'].$options_default['divider_html'];
				}
				$divider_html = $options_default['divider_html'];
				
				$breadCrumbs = preg_replace("/{$divider_html}$/",'',$breadCrumbs);
			}else{
				
				$template = _obj('Template');
				$template->breadCrumbs_list = $list; 
				
				ob_start();
				$template->output($template_html);
				$breadCrumbs = ob_get_contents();
				ob_end_clean();
				
			}
			
			return $breadCrumbs;
		}
		return false;
	}



	function setRelatedSections($array){
		$this->relatedSections = $array;

	}



	function afterSave(){
		parent::afterSave();
		
		$database = Marion::getDB();
		$database->delete('sectionRelated',"section={$this->id}");
		
		if( okarray($this->relatedSections) ){
			foreach($this->relatedSections as $v){
				$toinsert = array(
					'section' => $this->id,
					'related' => $v,
				);
				$database->insert('sectionRelated',$toinsert);

			}

		}
		$list = $database->select('id','product',"parent=0 AND deleted = 0 AND section = {$this->id}");

		
		if( okArray($list) ){
			foreach($list as $v){
				$database->insert('product_search_changed',array('id_product' => $v['id']));
			}
		}

	}

	function afterLoad(){
		parent::afterLoad();
		$database = Marion::getDB();
		$sections = $database->select('*','sectionRelated',"section={$this->id}");
		if( okArray($sections) ){
			foreach($sections as $v){
				$this->relatedSections[] = $v['related'];
			}
		}
	}



	function delete(){
		parent::delete();
		$database = Marion::getDB();
		$database->delete('sectionRealated',"section={$this->id}");
	}


	
	function getRelatedProducts($limit = 6){
		if( okarray($this->relatedSections) ){
			$where = "(";
			foreach($this->relatedSections as $v ){
				$where .= "{$v},";
			}
			$where = preg_replace('/\,/',')',$where);
			$query = Product::prepareQuery()
				->where('visibility',1)
				->where('deleted',0)
				->where('parent',0)
				->where('section',$where,"IN")
				->limit($limit);
			$products = $query->get();
			
			return $products;
		}

	}

	
	/*public static updateProductNumberSection($id){
		$database = Marion::getDB();
		
		//dati della sezione
		$section = $database->select('parent','product',"id={$id}");

		
		$sum = $database->select('count(*) as cont','product',"section={$id}");
		$sum = $sum[0]['cont'];
	
		if( $section[0]['parent'] ){
			$parent = $section[0]['parent'];
			while($parent){
				$section = $database->select('parent','product',"id={$parent}");
				$parent = 0;
				if( okArray($section) ){
					$parent = $section[0]['parent'];
				}
			}
		}
		$database->update('product',"id={$is}",array('numProducts'=>$sum));
		while(okArray($database->select('id','section',"") ))
	}*/

}





?>