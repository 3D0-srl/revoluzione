<?php
namespace ProductFeatures;
use Catalogo\{Product,TagProduct};
use Marion\Core\Marion;
class SearchAction{

	function __construct($limit=null,$offset=null,$orderKey,$orderValue){
		$this->_formdata = _var('formdata');
		$this->_action = _var('action');
		$this->_tag = _var('tag');
		$this->_section = _var('section');
		$this->_limit = $limit;
		$this->_offset = $offset;
		$this->_orderKey = $orderKey;
		$this->_orderValue = $orderValue;

		$this->where = "parent = 0 AND deleted = 0 AND visibility = 1 ";
		
		$this->query = Product::prepareQuery()
						->where('parent',0)
						->where('deleted',0)
						->where('visibility',1);
		
	}
	


	function getList(){
		
		$query2 = clone $this->query;
		
		if( $this->_limit ){
			$this->query->limit($this->_limit);
		}
		if( $this->_offset ){
			$this->query->offset($this->_offset);
		}

		

		if( $this->_orderKey ){
			
			$this->query->orderBy($this->_orderKey,$this->_orderValue,array('price'));
		}
		
		$list= $this->query->get();

		//debugga($this->query->lastquery);exit;
		
		$tot = $query2->getCount();
		
		return array(
			'tot' => $tot,
			'list' => $list,
		);
	}

	public function get(){
		//Catalog::loadPrices();
		$this->getWhereBase();
		
		Marion::do_action('catalog_query_select',array($this->query));
		$this->getConditionTags();
		$this->getConditionManufactures();
		$this->getConditionFeatures();
		$this->getConditionAttributes();
		$this->getConditionPrice();
		

		return $this->getList();
	}

	function getConditionAttributes(){
		$attributes = $this->_formdata['filtri']['attributes'];
		if( okArray($attributes)){
			$cont2 = count($attributes);
			$where_attr = "value in (";
			foreach($attributes as $f){
				$where_attr .= "{$f}, ";
			}

			$where_attr = preg_replace('/\, $/',')',$where_attr);
			$this->query->whereExpression("1 <= (select count(distinct pa.value) from productAttribute as pa join product as p2 on p2.id=pa.product where {$where_attr} and p2.parent = t1.id)");
			$this->where .= "AND 1 <= (select count(distinct pa.value) from productAttribute as pa join product as p2 on p2.id=pa.product where {$where_attr} and p2.parent = t1.id) ";
		}
	}


	function getConditionTags(){
		$tags = $this->_formdata['filtri']['tags'];
		if( okArray($tags) ){
			
			$where_tags  = "id IN (select id_product from productTagComposition where id_tag IN (";
			foreach($tags as $f){
				$where_tags .= "{$f}, ";
			}
			
			$where_tags = preg_replace('/\, $/','))',$where_tags);
			
			$this->query->whereExpression("({$where_tags})");
			$this->where .= "AND ({$where_tags}) ";
						
					
		}
	}
	function getConditionManufactures(){
		$manufacturers = $this->_formdata['filtri']['manufacturers'];
		if( okArray($manufacturers) ){
			$where_manufacturer  = "manufacturer IN (";
			foreach($manufacturers as $f){
				$where_manufacturer .= "{$f}, ";
			}
			$where_manufacturer = preg_replace('/\, $/',')',$where_manufacturer);
			$this->query->whereExpression("({$where_manufacturer})");

			$this->where .= "AND ({$where_manufacturer})) ";
			
		}
	}


	function getConditionFeatures(){
		/*$features = $this->_formdata['filtri']['features'];
		$where_expression = '';
		if( $features ){
			$where_features = '(id_feature_value IN (';
			foreach($features as $f){
				$where_features.= "{$f},";
			}
			$where_features = preg_replace('/\,$/','))',$where_features);
			$where_expression = $where_features." OR ";
		}

		$where_expression = preg_replace('/OR $/','',$where_expression);
		if( $where_expression ){
			$this->query->whereExpression("({$where_expression})");

			$this->where .= "AND ({$where_expression})) ";
		}*/
		$features = $this->_formdata['filtri']['features'];
		if( $features ){
			$where_features = "(";
			foreach($features as $f){
				$where_features.= "{$f},";
			}
			$where_features = preg_replace('/\,$/',')',$where_features);
			$db = Marion::getDB();
			$filtri = $db->select('*','product_feature_value',"id IN {$where_features}");
			$assoc = [];
			foreach($filtri as $v){
				$assoc[$v['id_product_feature']][$v['id']] = $v['id'];
			}
			
			foreach($assoc as $k => $v){
				$assoc[$k] = array_values($v);
			}
		
			$assoc = array_values($assoc);
			if( count($assoc) > 1 ){
				$combinazioni = Marion::combinations($assoc);
			}else{
				
				foreach($assoc as $values){
					foreach($values as $v){
						$combinazioni[] = [$v];
					}
				}
			}
			
			
			$query = '';
			foreach($combinazioni as $comb){

				$_where_features = "(";
				foreach($comb as $f){
					$_where_features.= "{$f},";
				}
				$_where_features = preg_replace('/\,$/',')',$_where_features);
				$num_features = count($comb);
				$query .= "({$num_features} = (select count(distinct id_feature_value) from product_feature_association where t1.id=id_product AND id_feature_value IN {$_where_features})) OR ";

			}
			$query = "(".preg_replace('/\ OR $/',')',$query);
			
			$this->query->whereExpression($query);
		}
				
	}

	function getConditionPrice(){
		$formdata = $this->_formdata;
		$limite_min = $formdata['price_min'];
		$limite_max = $formdata['price_max'];
		$where_price = '';
		if( $limite_min && $limite_max ){
			$where_price = "price >={$limite_min} AND price <= {$limite_max}";
		}elseif( $limite_min ){
			$where_price = "price >={$limite_min}";
		}elseif( $limite_max ){
			$where_price = "price <= {$limite_max}";
		}
		//debugga($this);exit;
		if( $this->_orderKey == 'price' || $where_price ){
			$this->query->leftOuterJoin('priceValueDaily as rp',"t1.id=rp.id_product");
		}
			
		if( $where_price ){
			if( authUser()){
				$this->query->whereExpression("({$where_price})");
				$this->where .= "AND ({$where_price}) ";
			}else{
				$this->query->whereExpression("({$where_price} AND userCategory = 1)");
				$this->where .= "AND ({$where_price}) AND userCategory = 1) ";
			}

		}
	}
	


	function getWhereBase(){
		
		if( $this->_section ){
			$this->getWhereSection();
		}

		if( $this->_tag ){
			$this->getWhereTag();
		}

		if( $this->_action == 'brand' ){
			$this->_manufacturer = _var('id');
			$this->query->where('manufacturer',$this->_manufacturer);
			$this->where .= "AND manufacturer = {$this->_manufacturer} ";
		}
	}


	function getWhereSection(){
		
			
		$section_children = $this->filtri_ricerca_get_section_children($this->_section);
		
		$database = Marion::getDB();
		
		$where_children_section = '';
		if( count($section_children) > 1 ){
			$where_children_section = 'IN (';
			foreach($section_children as $t){
				$where_children_section .= $t.",";
			}
			$where_children_section = preg_replace('/\,$/',')',$where_children_section);
		}
		if( $where_children_section ){
			$others = $database->select('*','otherSectionsProduct',"section {$where_children_section}");
		}else{
			$others = $database->select('*','otherSectionsProduct',"section={$this->_section}");
		}
		$other_condition = '';
		if( okArray($others) ){
			unset($default['section']);
			$other_condition = "id in (";
			foreach($others as $v){
				$other_condition .= "{$v['product']},";
			}
		}
		
		if( $where_children_section ){
			if( $other_condition){
				$other_condition = preg_replace('/\,$/',')',$other_condition);
				$this->query->whereExpression("(section {$where_children_section} OR {$other_condition} )");

				$this->where .= "AND (section {$where_children_section} OR {$other_condition} ) ";
			}else{
				$this->query->whereExpression("(section {$where_children_section} )");
				$this->where .= "AND (section {$where_children_section} ) ";
			}
		}else{
			if( $other_condition){
				$other_condition = preg_replace('/\,$/',')',$other_condition);
				$this->query->whereExpression("(section = {$this->_section} OR {$other_condition} )");
				$this->where .= "AND (section = {$this->_section} OR {$other_condition} ) ";
			}else{
				$this->query->where('section',$this->_section);
				$this->where .= "AND section = {$this->_section} ";
			}
		}

		
		

	}

	function getWhereTag(){
		
		$database = Marion::getDB();
		$tag = TagProduct::prepareQuery()->where('label',$this->_tag)->getOne();
		$tags = array($tag->id);
		$where_tags  = "id IN (select id_product from productTagComposition where id_tag IN (";
		foreach($tags as $f){
			$where_tags .= "{$f}, ";
		}
		
		$where_tags = preg_replace('/\, $/','))',$where_tags);
		
		$this->query->whereExpression("({$where_tags})");

		$this->where .= "AND ({$where_tags}) ";
		
	}

	function filtri_ricerca_get_section_children($section_id){
		$database = Marion::getDB();
		$tmp = array($section_id);
		$iter = 0;
		$check = true;
		$visti  = array();
		while($iter < 1000 && $check) {
			$tmp1 = $tmp;
			
			foreach($tmp as $v1){
				if( !in_array($v1,$visti) ){

					$sezioni = $database->select('id','section',"parent={$v1}");
					if( okArray($sezioni) ){
						foreach($sezioni as $t){
							$tmp[$t['id']] = $t['id'];
							
						}
					}
					$visti[] = $v1;
				}
			}
			if( count($tmp) == count($tmp1) ){
				$check = false;
			}
			$iter++;
		}

		return array_values($tmp);

	}
}


?>