<?php
namespace Catalogo;
use Marion\Core\Marion;
use Shop\Eshop;
class Catalog{

	public static function createSearchTables(){
		$database = Marion::getDB();
		$database->execute("
			CREATE TABLE IF NOT EXISTS product_search (
			  id_product bigint(20) UNSIGNED NOT NULL,
			  product_key varchar(50) NOT NULL,
			  product_value varchar(200) NOT NULL,
			  lang varchar(3) NOT NULL DEFAULT 'it',
			  uid varchar(50) DEFAULT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
		$database->execute("ALTER TABLE product_search ADD UNIQUE KEY id_product (id_product,uid);");
		$database->execute("ALTER TABLE product_search ADD FULLTEXT KEY product_value (product_value);");
		$database->execute("
			ALTER TABLE IF NOT EXISTS product_search
			ADD CONSTRAINT FK_product_search
			FOREIGN KEY (id_product) REFERENCES product(id);");
		$database->execute("
			CREATE TABLE product_search_changed (
			  id_product bigint(20) NOT NULL,
			  timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
		$database->execute("ALTER TABLE product_search_changed ADD UNIQUE KEY id_product (id_product);");


	}

	public static function buildSearchIndexes(){
			$database = Marion::getDB();

			//elimino le info vecchie
			$database->delete('product_search',"id_product IN (select id_product from product_search_changed)");
			
			//prendo le sezioni
			$sections = $database->select('id,name,locale','section as s join sectionLocale as sl on sl.section=s.id');
			foreach($sections as $v){
				$dati_section[$v['id']][$v['locale']] = $v['name'];
			}

			//prendo le sezioni
			$brands = $database->select('id,name,locale','manufactures as m join manufacturerLocale as ml on ml.manufacturer=m.id');
			foreach($brands as $v){
				$dati_brand[$v['id']][$v['locale']] = $v['name'];
			}
			
			
			//prendo i tag
			$tags = $database->select('id,name,locale','tagProduct as t join tagProductLocale as tl on tl.id_tagProduct=t.id');
			
			foreach($tags as $v){
				$dati_tags[$v['id']][$v['locale']] = $v['name'];
			}
			
	
			$tag_composition = $database->select('*','productTagComposition',"id_product IN (select id_product from product_search_changed)");

			foreach($tag_composition as $v){
				if( $info_tag = $dati_tags[$v['id_tag']] ){
					foreach($info_tag as $lang => $name){
						$toinsert[] = array(
							'id_product' => $v['id_product'],
							'product_key' => 'tagn',
							'uid' => 'tag_'.$v['id_tag'],
							'product_value' => $name,
							'lang' => $lang,
						);
					}
				}
			}
			
		

			$list = $database->select('id,name,sku,locale,parent,section,manufacturer','product as p join productLocale as pl on pl.product=p.id',"visibility=1 AND deleted = 0 AND (id IN (select id_product from product_search_changed) OR (parent IN (select id_product from product_search_changed))");
			foreach($list as $v){
				if( $v['parent'] ){
					
					$id_product = $v['parent'];

					
				}else{
					$id_product = $v['id'];
					

				}
				$key_name = "product_name_".$v['id'];
				$key_sku = "product_sku_".$v['id'];
				$sezioni = $dati_section[$v['section']];
				if( okArray($sezioni) ){

					foreach($sezioni as $lang => $v1){
						$toinsert[] = array(
							'id_product' => $id_product,
							'product_key' => 'section',
							'uid' => 'section_name_'.$v['section'],
							'product_value' => $v1,
							'lang' => $lang,
						);
					}
					
				}
				$brands = $dati_brand[$v['manufacturer']];
				if( okArray($brands) ){

					foreach($brands as $lang => $v1){
						$toinsert[] = array(
							'id_product' => $id_product,
							'product_key' => 'manufacturer',
							'uid' => 'manufacturer_name_'.$v['manufacturer'],
							'product_value' => $v1,
							'lang' => $lang,
						);
					}
					
				}
				
				

				$toinsert[] = array(
					'id_product' => $id_product,
					'product_key' => 'name',
					'product_value' => $v['name'],
					'uid' => $key_name,
					'lang' => $v['locale'],
				);
				$toinsert[] = array(
					'id_product' => $id_product,
					'product_key' => 'sku',
					'uid' => $key_sku,
					'product_value' => $v['sku'],
					'lang' => $v['locale'],
				);
			}
			
			foreach($toinsert as $v){
				$database->insert('product_search',$v);

			}

			$database->delete('product_search_changed');


			return true;
	}

	

	/*public static function loadPrices($id_product=NULL){
		$database = Marion::getDB();
		if( $id_product ){
			$database->delete('priceValueDaily',"id_product={$id_product}");
		}else{
			$database->delete('priceValueDaily','1=1');
		}
		$userCategories = UserCategory::prepareQuery()->get();
		$categories = array();
		foreach($userCategories as $c){
			$categories[] = $c->id;
		}
		if(	$id_product ){
			$collection = Catalog::getProduct(array('id'=>$id_product));
		}else{
			$collection = Catalog::getProduct();
		}
		$products = $collection->toArray();
		if( okArray($products) ){
			foreach($products as $v){
				foreach($categories as $c){
					$price = $v->getPriceValue(1,$c);
					$toinsert[] = array(
						'id_product' => $v->id,
						'price' => $price,
						'userCategory' => $c
					);
				}
			}
		}
		if( okArray($toinsert) ){
			foreach($toinsert as $v){
				$database->insert('priceValueDaily',$toinsert);
			}
		}
		

	}*/

	public static function loadPrices($id_product=NULL){
		$qnt = 1;
		$database = Marion::getDB();
		//$database->delete('priceValueDaily','1=1');
		//prendo le categorie utente
		$userCategories = $database->select('id,label','userCategory');
		
		//prendo i prodotti
		if( $id_product ){
			$products = $database->select('id,parent,v.id_tax,v.parent_price','product as p join product_shop_values on v as v.id_product=p.id',"(id={$id_product} OR parent={$id_product}) AND deleted=0 order by parent");
			foreach($products as $p){
				$database->delete('priceValueDaily',"id_product={$p['id']}");
			}
			$products = $database->select('p.id,p.parent,v.id_tax,v.parent_price','product as p join product_shop_values as v on v.id_product=p.id',"((p.type=1 AND (p.parent IS NULL OR p.parent = 0)) OR p.type=2) AND p.id={$id_product} and p.deleted=0 order by parent");
		}else{
			$database->delete('priceValueDaily','1=1');
			$products = $database->select('id,parent,v.id_tax,v.parent_price','product as p join product_shop_values as v on v.id_product=p.id',"deleted=0");
			
		}
		
		
		//prendo le tasse
		$tasse = $database->select('*','tax');
		if( okArray($tasse) ){
			foreach($tasse as $v){
				$percentuale_tassa[$v['id']] = $v['percentage'];
			}
		}
		
		
		
		foreach($products as $k => $v){
			$id = $v['id']; //id del prodotto
			
			$taxCode = $v['id_tax']; // id della tassa
			if( $v['parent'] && $v['parent_price'] ){
				foreach($userCategories as $category) {
					$group = $category['id'];
					$prezzo_parent = $prezzo_tmp[$v['parent']][$group];
					$toinsert = array(
						'id_product' => $id,
						'price' => $prezzo_parent,
						'userCategory' => $group
					);
					$database->insert('priceValueDaily',$toinsert);

				}
				continue;
			}
			
			foreach($userCategories as $category) {
				$group = $category['id'];
				
				$prezzo_default = $database->select('*','price',"product={$id} AND label='default'");
				if( okArray($prezzo_default) ){
					$prezzo_valore = $prezzo_default[0]['value'];
				}
				//debugga($prezzo_default);exit;

				$listini = $database->select('p.id,p.dateStart,p.dateEnd','price as p join priceList as l on p.label=l.label',"product={$id} AND p.label <> 'default' AND p.label <> 'barred' and quantity <= {$qnt} AND (userCategory = {$group} OR userCategory = 0) and l.active=1 order by p.quantity DESC,userCategory DESC,l.priority DESC,p.quantity DESC");
				
				
				if( okArray($listini) ){
					$now = date('Y-m-d');
					
					foreach($listini as $k1 => $v1){
						if( $v1['dateStart'] ){
							
							if( strtotime( $v1['dateStart'] ) > strtotime($now) ){
							
								unset($listini[$k1]);
								continue;
							}
						}
						if( $v1['dateEnd'] ){
							if( strtotime( $v1['dateEnd'] ) < strtotime($now) ){
								unset($listini[$k1]);
								continue;
							}
						}
					}
					
					if( okArray($listini) ){
						$listino = array_values($listini)[0];
						

						$prezzo = $database->select('*','price',"id={$listino['id']}");

						
						if( okArray($prezzo) ){
							if( $prezzo[0]['type'] == 'price'){
								$prezzo_valore = $prezzo[0]['value'];
							}else{
								$prezzo_valore = $prezzo_valore - $prezzo_valore*$prezzo[0]['value']/100;
							}
						}
						
					}
				}
				
				//aggiungo la tassa 
				if( $taxCode ){
					$percentuale = $percentuale_tassa[$taxCode];
					if( $percentuale ){
						$prezzo_valore = Eshop::addVatToPrice($prezzo_valore,$percentuale);
					}
				}
				
		
				$toinsert = array(
					'id_product' => $id,
					'price' => $prezzo_valore,
					'userCategory' => $group
				);
				$prezzo_tmp[$id][$group] = $prezzo_valore;
				$database->insert('priceValueDaily',$toinsert);
			}
		}
	}




	

	//metodo che restituisce una collezione di oggetti di tipo prodotto a partire da una o più parole chiave di ricerca
		public static function searchProducts($words,$order=array(),$limit=NULL,$offset=NULL){
		$order_default = array(
			'orderView' => 'ASC',
		);

		if(okArray($order)){
			foreach($order_default as $k => $v){
				$order[$k] = $v;
			}
		}else{
			$order = $order_default;
		}
		$fields = "distinct p.id";
		$order_by = '';
		
		

		$table = "((product AS p JOIN productLocale as pl on pl.product=p.id) JOIN product_search AS s ON s.id_product=p.id) LEFT OUTER JOIN priceValueDaily as pr on pr.id_product=p.id";
		$where = "pl.locale = '{$GLOBALS['activelocale']}' AND s.lang = '{$GLOBALS['activelocale']}' AND p.visibility = 1 AND p.deleted = 0 AND (MATCH(s.product_value) AGAINST ('{$words}') OR ";

		$_words = explode(' ',$words);
		if( count($_words) > 1 ){

			
			/*$punteggio = count($_words)*10;


			$words = trim(preg_replace('/\s/',' +'," ".trim($words)));
			$order_by .= "rel DESC,";
			$fields .= ", MATCH(product_value) AGAINST ('{$words}' IN BOOLEAN MODE) * 10 as rel";
			*/
			/*$words_list = array();
			while(count($_words) > 0 ){
				unset($_words[count($_words)-1]);
				
				$new_word = '';
				foreach($_words as $w){
					$new_word .= $w." ";  
				}
				if( trim($new_word) ){
				
					$words_list[trim($new_word)] = count($_words);
				}
			}*/
			
			$num = count($_words);

			//The total number of possible combinations
			$total = $num * $num;

			//Loop through each possible combination
			for ($i = 0; $i < $total; $i++) {
				$new_word = '';
				$_tot = 0;
					//For each combination check if each bit is set
				for ($j = 0; $j < $total; $j++) {
					//Is bit $j set in $i?
					if (pow(2, $j) & $i){ 
						$_tot++;
						$new_word.= $_words[$j] . ' '; 	
					}
				}
				if( trim($new_word) ){
					$words_list[trim($new_word)] = $_tot;
				}
			}
			uasort($words_list,function($a,$b){
				if ($a==$b) return 0;
				return ($a>$b)?-1:1;
			});
			
			$ind = 1;
			foreach($words_list as $word => $pnt){
				
				$word = trim(preg_replace('/\s/',' +'," ".trim($word)));
				$order_by .= "rel{$ind} DESC,";
				$fields .= ", MATCH(product_value) AGAINST ('{$word}' IN BOOLEAN MODE) * {$pnt} as rel{$ind}";
				$ind++;
			}
			/*debugga($fields);exit;
			
			
			foreach($_words as $k => $v){
				if( $word = trim($v) ){
					$ind = $k+1;
					$order_by .= "rel{$ind} DESC,";
					$fields .= ", MATCH(product_value) AGAINST ('{$word}' IN BOOLEAN MODE) * 5 as rel{$ind}";
					$where .= "MATCH(s.product_value) AGAINST ('{$word}') OR ";
				}
			}*/
		}
		$where = preg_replace('/ OR $/',')',$where);


		foreach($order as $k => $v){
			if( in_array($k,array('sku','orderView')) ) {
				$k = "p.{$k}";
			}
			if( in_array($k,array('name')) ) {
				$k = "pl.{$k}";
			}

			if( in_array($k,array('price')) ) {
				$k = "pr.{$k}";
				
			}
			$fields .= ",{$k}";
			
			$order_type = strtoupper($v);
			$order_by .= "{$k} {$order_type},";
		}

		$order_by = preg_replace('/,$/','',$order_by);
		
		$database = Marion::getDB();
		if( $offset ){
			$limit .= " OFFSET {$offset}"; 
		}
		$select = $database->select($fields,$table,$where,$order_by,$limit);
		
		
		$products = array();
		if( okArray($select) ){
			foreach($select as $v){
				$product = Product::withId($v['id']);

				if( is_object($product) ){
					$products[] = $product;
				}
			}
		}
		
		
		return $products;
	}


	//metodo che restituisce il numero di oggetti di tipo prodotto a partire da una o più parole chiave di ricerca
	public static function getCountSearchProducts($words){
		$table = "product AS p  JOIN product_search AS s ON s.id_product=p.id";
		$where = "s.lang = '{$GLOBALS['activelocale']}' AND p.visibility = 1 AND p.deleted = 0 AND (s.product_value LIKE '%{$words}%' OR ";

		$words = explode(' ',$words);
		if( count($words) > 1 ){
			foreach($words as $v){
				if( $word = trim($v) ){
					$where .= "s.product_value LIKE '%{$word}%' OR ";
				}
			}
		}
		$where = preg_replace('/ OR $/',')',$where);
		
		$database = Marion::getDB();
		$select = $database->select('count(distinct p.id) as tot',$table,$where);
		
		return $select[0]['tot'];
		
	}


	public static function getCountProducts($filter=array(),$order=array(),$limit=NULL,$offset=NULL){
		//filtri di default
		$default = array(
			'visibility' => 1,
			'parent' => 0,
			'deleted' => 0
		);
		if( Marion::auth('catalog') ){
			unset($default['visibility']);
		}
		if(okArray($filter)){
			foreach($filter as $k => $v){
				if( okArray($v) ){
					$in_array[$k] = $v;
				}else{
					$default[$k] = $v;
				}
				
			}
		}

		

		$order_default = array(
			'orderView' => 'ASC',
		);

		if(okArray($order)){
			foreach($order as $k => $v){
				$order_default[$k] = $v;
			}
		}
		
		
		

		$query = Product::prepareQuery();

		
		//da verificare
		
		Marion::do_action('catalog_query_select',array($query));	
		//sezioni secondarie
		if( array_key_exists('section',$filter) ){
			
			$database = Marion::getDB();
			$others = $database->select('*','otherSectionsProduct',"section={$filter['section']}");
			if( okArray($others) ){
				unset($default['section']);
				$other_condition = "id in (";
				foreach($others as $v){
					$other_condition .= "{$v['product']},";
				}
				$other_condition = preg_replace('/\,$/',')',$other_condition);
				$query->whereExpression("(section = {$filter['section']} OR {$other_condition} )");
			}
			
		}
		if( okArray($in_array) ){
			foreach($in_array as $k => $set){
				$cond = '';
				foreach($set as $v){
					$cond .= "{$v},";
				}
				$cond = preg_replace("/,$/",'',$cond);
				$query->whereExpression("({$k} IN ({$cond}))");
			}
		}

		$query->whereMore($default);
		
		

	
		
		
		$query->orderByMore($order_default);
		if( $limit ){
			$query->limit($limit);
		}
		
		if( $offset ){
			$query->offset($offset);
		}
		
	

		$result = $query->getCount();
		return $result;
	}

	public static function getProducts($filter=array(),$order=array(),$limit=NULL,$offset=NULL){
		return self::getProduct($filter,$order,$limit,$offset);
	}

	public static function getProduct($filter=array(),$order=array(),$limit=NULL,$offset=NULL){
		//filtri di default
		$default = array(
			'visibility' => 1,
			'parent' => 0,
			'deleted' => 0
		);
		if( Marion::auth('catalog') ){
			unset($default['visibility']);
		}
		$in_array = array();
		if(okArray($filter)){
			foreach($filter as $k => $v){
				if( okArray($v) ){
					$in_array[$k] = $v;
				}else{
					$default[$k] = $v;
				}
				
			}
		}

		

		$order_default = array(
			'orderView' => 'ASC',
		);

		if(okArray($order)){
			foreach($order as $k => $v){
				$order_default_tmp[$k] = $v;
			}
			$order_default = array_merge($order_default_tmp,$order_default);
		}
		
		

		$query = Product::prepareQuery();

		$query->leftOuterJoin('priceValueDaily as dp',"t1.id=dp.id_product");
		if( authUser()){
			$current_user = Marion::getUser();
			$query->whereExpression("(dp.price is NULL OR dp.userCategory={$current_user->category})");
		}else{
			$query->whereExpression("(dp.price is NULL OR dp.userCategory=1)");
		}

		//da verificare
		
		Marion::do_action('catalog_query_select',array($query));	
		//$split_product = Marion::getConfig('catalog','attribute_split_product');
		
		//MODIFICA SPLIT
		/*if( isCiro()){
			$query->leftOuterJoin('split_product as t4',"t4.parent_product=t1.id");
			$query->setFieldSelect('t4.images as images_child');
		}*/
	
		/*if( $split_product ){
		
			$attribute = Attribute::withLabel($split_product);
			if( is_object($attribute) ){
				if( !$default['id'] ){
					unset($default['parent']);
					$database = Marion::getDB();
					$attributeSets = $database->select('distinct(attributeSet)','attributeSetComposition',"attribute={$attribute->id}");
					if( okArray($attributeSets) ){
						$cond = '';
						foreach($attributeSets as $set){
							$cond .= "{$set['attributeSet']},";
						}
						$cond = preg_replace("/,$/",'',$cond);
					}
					if( isMultilocale()){
						$query->setTable("((select DISTINCTROW t1.* from (product as t1 left outer join productLocale as t2 on t1.id = t2.product) where (t1.parent IS NULL OR t1.parent = 0) AND (t1.attributeSet IS NULL OR t1.attributeSet NOT IN ({$cond}))) UNION (select DISTINCTROW t1.* from (product as t1 left outer join productLocale as t2 on t1.id = t2.product) LEFT OUTER JOIN productAttribute as t3 on t1.id=t3.product where t1.attributeSet IN ({$cond}) AND t3.attribute='{$split_product}'  group by t1.parent,t3.value)) as t");
					}else{
						$query->setTable("((select DISTINCTROW t1.* from product as t1  where (t1.parent IS NULL OR t1.parent = 0) AND (t1.attributeSet IS NULL OR t1.attributeSet NOT IN ({$cond}))) UNION (select DISTINCTROW t1.* from product as t1  LEFT OUTER JOIN productAttribute as t3 on t1.id=t3.product where t1.attributeSet IN ({$cond}) AND t3.attribute='{$split_product}'  group by t1.parent,t3.value)) as t");
					}
					$query->setTableLocale(NULL);
				}
			}

		}*/
			
			
		
		

		//sezioni secondarie
		if( array_key_exists('section',$filter) ){
			
			$database = Marion::getDB();
			$others = $database->select('*','otherSectionsProduct',"section={$filter['section']}");
			if( okArray($others) ){
				unset($default['section']);
				$other_condition = "id in (";
				foreach($others as $v){
					$other_condition .= "{$v['product']},";
				}
				$other_condition = preg_replace('/\,$/',')',$other_condition);
				$query->whereExpression("(section = {$filter['section']} OR {$other_condition} )");
			}
			
		}
		if( okArray($in_array) ){
			foreach($in_array as $k => $set){
				$cond = '';
				foreach($set as $v){
					$cond .= "{$v},";
				}
				$cond = preg_replace("/,$/",'',$cond);
				$query->whereExpression("({$k} IN ({$cond}))");
			}
		}

		$query->whereMore($default);
		
		

	
		
		
		$query->orderByMore($order_default,array('price'));
		if( $limit ){
			$query->limit($limit);
		}
		
		if( $offset ){
			$query->offset($offset);
		}

		/*
		(select DISTINCTROW t1.* from (product as t1 left outer join productLocale as t2 on t1.id = t2.product) where (t1.parent IS NULL OR t1.parent = 0) AND (t1.attributeSet IS NULL OR t1.attributeSet NOT IN (26,27)) AND t1.deleted = 0 AND t1.section = 21)

		UNION

		(select DISTINCTROW t1.* from (product as t1 left outer join productLocale as t2 on t1.id = t2.product) LEFT OUTER JOIN productAttribute as t3 on t1.id=t3.product where t1.attributeSet IN (26,27) AND t3.attribute='colore' AND t1.deleted=0  AND t1.deleted = 0  AND t1.section = 21  group by t1.parent,t3.value)  order by orderView ASC limit 9
		


		
		select * from ((select DISTINCTROW t1.* from (product as t1 left outer join productLocale as t2 on t1.id = t2.product) where (t1.parent IS NULL OR t1.parent = 0) AND (t1.attributeSet IS NULL OR t1.attributeSet NOT IN (26,27)))

		UNION

		(select DISTINCTROW t1.* from (product as t1 left outer join productLocale as t2 on t1.id = t2.product) LEFT OUTER JOIN productAttribute as t3 on t1.id=t3.product where t1.attributeSet IN (26,27) AND t3.attribute='colore'  group by t1.parent,t3.value)) as t where  t.deleted=0  AND t.section = 21  order by orderView ASC limit 9


		*/
		
		if( array_key_exists('name',$order_default) ){
			$query->where('locale',$GLOBALS['activelocale']);
		}

		if( array_key_exists('price',$order_default) ){
			$query->setFieldSelect('price');
		}

		$result = $query->getCollection();
		if( isCiro()){
			//debugga($result);exit;
			//debugga($result);exit;
			/*foreach($result as $v){
				debugga($v->id);
				debugga($v->parent);
				debugga($v->getAttributes());
			}*/
			
			
			
		}
		//debugga($query);exit;
		//debugga($query->error);exit;
		return $result;
		
	}


	public static function getSection($filter=array(),$order=array()){
		//filtri di default
		$default = array(
			'visible' => 1,
			'parent' => 0,
			'deleted' => 0,
		);
		
		$order_default = array(
			'orderView' => 'ASC',
		);

		if(okArray($filter)){
			foreach($filter as $k => $v){
				$default[$k] = $v;
			}
		}

		if(okArray($order)){
			foreach($order as $k => $v){
				$order_default[$k] = $v;
			}
		}
		

		$query = Section::prepareQuery();
		$query->whereMore($default)->orderByMore($order_default);

		return $query->getCollection();
		
	}

	public static function getSectionTree($all=false){
		//filtri di default
		
		if( !$all ){
			$default = array(
				'visibility' => 1,
			);
		}
		
		$order_default = array(
			'orderView' => 'ASC',
		);

		$query = Section::prepareQuery();
		$query->whereMore($default)->orderByMore($order_default);
		$section = $query->get();
		if(okArray($section)){
			$tree = Section::buildtree($section);
			//ordino le sezioni di primo livello
			uasort($tree,function($a,$b){
				if ($a->orderView == $b->orderView) return 0;
				return ($a->orderView < $b->orderView)?-1:1;
			});
			//ordino le sezioni di secondo livello
			foreach($tree as $v){
				if( okArray($v->children) ){
					uasort($v->children,function($a,$b){
						if ($a->orderView == $b->orderView) return 0;
						return ($a->orderView < $b->orderView)?-1:1;
					});
					
					foreach($v->children as $v1){
						//ordino le sezioni di terzo livello
						if( okArray($v1->children) ){
							uasort($v1->children,function($a,$b){
								if ($a->orderView == $b->orderView) return 0;
								return ($a->orderView < $b->orderView)?-1:1;
							});
						}
					}

				}
			}
			return $tree;
		}
		return false;
		
	}

	

	public static function orderProductByPrice(&$products,$orderType='low'){
		
		if( $orderType == 'low'){

			uasort($products,function($a,$b){
				if ($a->getPriceValue() == $b->getPriceValue()) return 0;
				return ($a->getPriceValue() < $b->getPriceValue())?-1:1;
			});
			
		}else{
			
			$res = uasort($products,function($a,$b){
				if ($a->getPriceValue() == $b->getPriceValue()) return 0;
				return ($a->getPriceValue() > $b->getPriceValue())?-1:1;
			});
			
			
			

		}
	}


	public static function reset(){
		
		$products = Product::prepareQuery()->get();
		foreach($products as $prod){
			$prod->deleteChildren();
			$prod->delete();
		}
		$database = Marion::getDB();
		$database->execute("ALTER TABLE product AUTO_INCREMENT = 1");
		$database->execute("ALTER TABLE price AUTO_INCREMENT = 1");
	}


}





?>