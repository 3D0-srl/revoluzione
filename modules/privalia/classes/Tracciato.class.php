<?php
class Tracciato{
	public $fields = array(
		'FIEL_CSV_HEADER' => array(
			'name' => 'EAN', //nome in visualizzazione nella fase di configurazione del mapping
			'ignore' => 0, //ignorato in fase di configurazione del mapping
			'required' => 1, //campo obbligatorio,
			'fixed' => 1, //il valore del campo del tracciato è fisso. Qualora il campo è 0 o assente allora il campo è abilitato per la mappatura
			'function_values' => 'nameFunction', //funzione che genera i valori ammesso dal campo
		),
		
	);
	
	public $limit_products = 0;
	
	private $errors = array();

	
	private $manufacurers = array(); //memorizzo i brand (cache)
	private $profiles = array(); //memorizzo i profili di mapping (cache)
	private $prices = array();//memorizzo i prezzi dei prodotti (cache)
	private $taxes = array(); //memorizzo le percentuali delle tasse (cache)
	private $attribute_values = array(); //memorizzo i valori degli attributi (cache)
	private $feature_values = array();




	private $mapping = array();
	private $category = array();


	function __construct(){
		$this->init();
	}

	function init(){
		$this->getTaxes();
		$this->getManufacurers();
		$this->getAttributes();
		require_once(_MARION_MODULE_DIR_."filtri_ricerca/classes/ProductFeatureValue.class.php");
	}

	function getFields(){
		return $this->fields;
	}

	function getTaxes(){
		$list = Tax::prepareQuery()->get();
		foreach($list as $tax){
			$this->taxes[$tax->id] = $tax->percentage;
		}
	}

	function getManufacurers(){
		$list = Manufacturer::prepareQuery()->get();
		foreach($list as $man){
			$this->manufactures[$man->id] = $man->get('name');
		}
	}

	function getAttributes(){
		$list = Attribute::prepareQuery()->get();
		foreach($list as $man){
			$this->attributes[$man->label] = $man->id;
		}
	}

	function addProduct($product,$id_profile){
		
		if( $id_profile ){
			
			$this->getMapping($id_profile);
			
			if( $product->isConfigurable()){
				$children = $product->getChildren();
				if( okArray($children) ){
					foreach($children as $child){
						$this->data_csv[] = $this->getDataRow($child);
					}
				}
			}else{
				$this->data_csv[] = $this->getDataRow($product);
			}
		}
	}

	function getMapping($id_profile){
		if( okArray($this->mapping) ) return false;
		//if( array_key_exists($id_profile,$this->profiles) ) return $this->profiles[$id_profile];
		$database = _obj('Database');
		$dati = $database->select('*','privalia_profile',"id={$id_profile}");
		if( okArray($dati) ){
			$this->mapping = unserialize($dati[0]['configuration']);
			$this->path_category = $dati[0]['path_taxonomy'];
			$this->category = $dati[0]['taxonomy'];
			//$mapping['C16-PRODUCT_TEMPLATE']['fixed'] = $dati[0]['path_taxonomy'];
			
		}else{
			echo "nessun profilo esistente";
			exit;
		}
		
		
	}

	function getDataRow($product){
		$database = _obj('Database');
		$mapping = $this->mapping;
		
		$category = $this->path_category;
		$images = $product->images;
		$shop = $database->select('*','product_shop_values',"id_product={$product->id}");
		if( okArray($shop) ){
			$shop_values = $shop[0];
			if( !$shop_values['parent_price'] ){
				$prices = $this->getPrices($product->id,$shop_values['id_tax']);
			}else{
				$prices = $this->getPrices($product->parent,$shop_values['id_tax']);
			}
			
		}else{
			return false;
		}
		
		$data = array();

		

		$attributes = $product->getAttributes();
		$data_attributes = array();
		foreach($attributes as $k => $v){
			$data_attributes[$this->attributes[$k]] = $v;
		}

		if( $product->parent ){
			$filtri = $database->select('pf.*,v.id_product_feature','product_feature_association as pf join product_feature_value as v on v.id=pf.id_feature_value',"id_product={$product->parent}");
		}else{
			$filtri = $database->select('pf.*,v.id_product_feature','product_feature_association as pf join product_feature_value as v on v.id=pf.id_feature_value',"id_product={$product->id}");
		}
		
		$data_filtri = array();
		foreach($filtri as $k => $v){
			$data_filtri[$v['id_product_feature']] = $v['id_feature_value'];
		}


		
		foreach($mapping as $k => $v){
			
			if( $v['fixed'] ){
				$data[$k] = $v['fixed'];
			}else{
				if( $v['mapping'] == 'other' ){
					$data[$k] = $v['static'];
					continue;
				}
				if( preg_match('/field/',$v['mapping'])) {
					$key = preg_replace('/field_/','',$v['mapping']);
					if( preg_match("/image[0-9]/",$key) ){
						$num = preg_replace('/image/','',$key);
						
						if( $images[$num-1] ){
							$data[$k] = "http://" . $_SERVER['SERVER_NAME'].$product->getUrlImage($num-1);
						}else{
							$data[$k] = '';
						}
					}elseif(preg_match("/price/",$key)){
						$data[$k] = $prices[$key];
					}elseif($key == 'manufacturer'){
						
						$data[$k] = $this->manufactures[$product->manufacturer];
						//devo prendere il brand
					}else{
						$data[$k] = $product->get($key);
					}
					continue;
				}
				if( preg_match('/feature/',$v['mapping'])) {
					$key = preg_replace('/feature_/','',$v['mapping']);
					if( $id_feature_value = $data_filtri[$key] ){
						
						if( okArray($v['association']) && $v['association'][$id_feature_value] ){
							$data[$k] = $v['association'][$id_feature_value];
						}else{
							$data[$k] = $this->getFeatureValue($id_feature_value);
						}
					}
					continue;
				}
				if( preg_match('/attribute/',$v['mapping'])) {
					$key = preg_replace('/attribute_/','',$v['mapping']);
					
					if( $id_attribute_value = $data_attributes[$key] ){
						
						if( okArray($v['association']) && $v['association'][$id_attribute_value] ){
							$data[$k] = $v['association'][$id_attribute_value];
						}else{
							$data[$k] = $this->getAttributeValue($id_attribute_value);
						}
					}
					continue;
				}
			}
			switch($k){
				case 'category':
					$data[$k] = $category;
					break;
				case 'tax_rate_percentage':
					$data[$k] = $this->taxes[$shop_values['id_tax']];
					break;
				case 'is_variation':
					$data[$k] = $product->parent?1:0;
					break;
				default:
					$data[$k] = '';
					break;
			}
			
			
		}
		debugga($data);exit;
		return $data;

		
	}


	function getFeatureValue($id_feature_value){
		if( array_key_exists($id_feature_value,$this->feature_values) ){
			return $this->feature_values[$id_feature_value];
		}else{
			$obj = ProductFeatureValue::withId($id_feature_value);
			if( is_object($obj) ){
				$value = $obj->get('value');
				$this->feature_values[$id_feature_value] = $value;
				return $value;
			}
			return false;
		}
	}


	function getAttributeValue($id_attribute_value){
		if( array_key_exists($id_attribute_value,$this->attribute_values) ){
			return $this->attribute_values[$id_attribute_value];
		}else{
			$obj = AttributeValue::withId($id_attribute_value);
			if( is_object($obj) ){
				$value = $obj->get('value');
				$this->attribute_values[$id_attribute_value] = $value;
				return $value;
			}
			return false;
		}
	}



	function getPrices($id_product,$id_tax=0){
		
		if( !array_key_exists($id_product,$this->prices) ){
			$prices = array(
				'price_whitout_vat' => 0,
				'price' => 0,
				'price_offer' => 0,
			);
			$database = _obj('Database');
			$sel = $database->select('value','price',"product={$id_product} AND label = 'default'");
			$default_price = 0;
			if( okArray($sel) ){
				$default_price = $sel[0]['value'];
				$prices['price_whitout_vat'] = $default_price;
				$prices['price'] = $default_price;
			}
			
			$listini = $database->select('p.*','price as p join priceList as l on p.label=l.label',"product={$id_product} AND p.label <> 'default' AND p.label <> 'barred' and quantity <= 1 AND (userCategory = 1 OR userCategory = 0) and l.active=1 order by p.quantity DESC,userCategory DESC,l.priority DESC,p.quantity DESC");
					
			
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
					
					if( $listino['type'] == 'price'){
						$price_offer = $listino['value'];
					}else{
						$price_offer = $default_price - $default_price*$listino['value']/100;
					}
					
					
				}
			}
			$prices['price_offer'] = $price_offer;
			$this->prices[$id_product] = $prices;
		}else{	
			$prices = $this->prices[$id_product];
		}
		$percentage = 0;
		if( array_key_exists($id_tax,$this->taxes)){
			$percentage = $this->taxes[$id_tax];
		}
	
		if( $percentage ){
			$prices['price'] = $prices['price']+$prices['price']*$percentage/100;
			$prices['price_offer'] = $prices['price_offer']+$prices['price_offer']*$percentage/100;
		}
		return $prices;

	}

	function check(){
		

			
			$database = _obj('Database');
			$list = $database->select('*','privalia_taxonomy_attribute',"category_code={$this->category}");

			
			$fields = array();
			foreach($list as $k => $v){
				$fields[$v['code']] = $v;
				
				if( $v['values_list'] ){
					$dati = $database->select('*','privalia_taxonomy_attribute_value',"code='{$v['values_list']}'");
					if( okArray($dati) ){
					
						$valori = unserialize($dati[0]['valori'])	;
						foreach($valori as $k1=> $v1){
							$fields[$v['code']]['values'][$v1['id']] = $v1;
						}
						
					}
				}

				
			}
			$errors = array();
			foreach($this->data_csv as $ind => $data){
				foreach($fields as $field => $params){
					
					if( $params['required'] && !$data[$field] ){
						$errors[$ind][] = "{$field} is required";
					}
				}
			}

			
			
			
			
			
			return $errors;
			
		
	}
	

	function prepareCSV(){
		$errors = $this->check();
		$csv = array();
		$tot = 0;
		foreach($this->data_csv as $ind => $data){
			
			if( array_key_exists($ind,$errors) ) continue;
			$tot++;
			if( $this->limit_products >0 && $tot <= $this->limit_products ){
				$csv[] = $data;
			}else{
				$csv[] = $data;
			}
		}
		
		
		return $csv;
		
	}


	/*function prepareCSV(){
		$this->check();
		$csv = array();
		$tot = 0;
		foreach($this->data_csv as $data){
			$row = array();
			foreach($this->fields as $field => $params){
				$row[$field] = $data[$field];
			}
			$tot++;
			if( $this->limit_products && $tot <= 10 ){
				$csv[] = $row;
			}else{
				$csv[] = $row;
			}
		}
		
		return $csv;
		
	}*/
}


?>