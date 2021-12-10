<?php
namespace ProductFeatures;
use Catalogo\{TagProduct,Manufacturer,AttributeValue,Attribute};
use Marion\Core\Marion;
class SearchView{

	public $_attribute_images = array('colore');
	//public $_order_filters = array('manufacturers','tags','features','attributes');

	function __construct($limit=null,$offset=null,$orderKey=null,$orderValue=null){
		$this->_formdata = _var('formdata');
		$this->_action = _var('action');
		$this->_tag = _var('tag');
		$this->_section = _var('section');
		
		
	}
	
	function getWhereBase(){
			$where = '';
			if( $this->_section ){
				$sezioni = $this->filtri_ricerca_get_section_children($this->_section);
				
				foreach($sezioni as $t){
					$where .= "{$t},";
				}
				$where = "IN (".preg_replace('/\,$/',')',$where);
				$this->where = "((p.section {$where}) OR p.id IN (select product from otherSectionsProduct where section {$where}))";
				$this->where_attributes = $this->where;
			}

			
			
			if( $this->_tag ){
				$tag = TagProduct::prepareQuery()->where('label',$this->_tag)->getOne();
				$this->where = "p.id IN (select id_product from productTagComposition where id_tag={$tag->id})";
				$this->where_attributes = "p.parent IN (select id_product from productTagComposition where id_tag={$tag->id})";
			}

			$this->where .= " AND visibility=1 AND deleted=0";

	}

	function get(){

		$conf = Marion::getConfig('filtri_ricerca');
		$filtri_ok = unserialize($conf['filtri']);
		$escludi_tags = unserialize($conf['escludi_tags']);
		$escludi_attributes = unserialize($conf['escludi_attributes']);
		$escludi_features = unserialize($conf['escludi_features']);
		
		$this->getWhereBase();
		

		foreach($filtri_ok as $_filtro){
			
			
			switch($_filtro){
				case 'tags':

					if( !$this->_tag ){
						$this->getFilterTags($escludi_tags);
					}

					break;
				case 'manufacturers':
					if( $this->_action != 'brand' ){
						$this->getFilterManufacturers();
					}

					break;
				case 'attributes':
					$this->getFilterAttributes($escludi_attributes);
					break;

				case 'features':
					$this->getFilterFeatures($escludi_features);
					break;
				

			}
		}
		

		//$this->getFilterFeatures();
		

		if( okArray($this->filtri) ){
			/*foreach($this->filtri as $v){
				$key = array_search($v['type'],$this->_order_filters);
				$temp[$key][] = $v;
			}
			ksort($temp);
			foreach($temp as $v){
				foreach($v as $v1){
					$toreturn[] = $v1;
				}
			}
			$toreturn = 
			*/
			return $this->filtri;
		}else{
			return false;
		}
		
		
		
		
	}

	function getFilterTags($escludi_tags = array()){
		$database = Marion::getDB();;
		$where = '';
		if( okArray($escludi_tags) ){
			foreach($escludi_tags as $v){
				$where .= "{$v},";
			}
			$where = preg_replace('/\,$/','',$where);
			$tags = $database->select('distinct t.id_tag','productTagComposition as t join product as p on p.id=t.id_product',"{$this->where} AND parent=0 AND id_tag NOT IN ({$where})");
		}else{
			$tags = $database->select('distinct t.id_tag','productTagComposition as t join product as p on p.id=t.id_product',"{$this->where} AND parent=0");
		}
		
		
		if( okArray($tags) ){

			foreach($tags as $v){
				
				$tag_obj = TagProduct::withId($v['id_tag']);
				if( is_object($tag_obj) ){
					
					$option = array(
						'value' => $tag_obj->id,
						'text' => $tag_obj->get('name'),
					);
					
					if( in_array($tag_obj->id,$this->_formdata['filtri']['tags']) ){
						$option['selected'] = 1;
					}
					$options[] = $option;
				}
			}
			uasort($options,function($a, $b){
				if ($a['text'] == $b['text']) {
					return 0;
				}
				return ($a['text'] < $b['text']) ? -1 : 1;
			});
			
			$this->filtri[] = array(
				'type' => 'tags',
				'name' => _translate('tags','filtri_ricerca'),
				'values' => $options,
				
			);

		}
	}

	function getFilterManufacturers(){
		$database = Marion::getDB();;
		$list = $database->select('distinct manufacturer','product as p',"{$this->where} AND parent=0");
		
		if( okArray($list) ){

			foreach($list as $v){
				
				$obj = Manufacturer::withId($v['manufacturer']);
				if( is_object($obj) ){
					
					$option = array(
						'value' => $obj->id,
						'text' => $obj->get('name'),
					);
					
					if( in_array($obj->id,$this->_formdata['filtri']['manufacturers']) ){
						$option['selected'] = 1;
					}
					$options[] = $option;
				}
			}
			if( okArray($options) ){
				uasort($options,function($a, $b){
					if ($a['text'] == $b['text']) {
						return 0;
					}
					return ($a['text'] < $b['text']) ? -1 : 1;
				});
			}
			
			$this->filtri[] = array(
				'type' => 'manufacturers',
				'name' => _translate('manufacturers','filtri_ricerca'),
				'values' => $options,
				
			);



		}
	}


	function getFilterAttributes($escludi_attributes=array()){
		$database = Marion::getDB();;
		
		$attributi = $database->select('distinct a.value,att.id as attribute_id,p.images','(product as p join productAttribute as a on a.product=p.id) join attribute as att on att.label = a.attribute',"{$this->where_attributes}");
		
			//debugga($this->where);
			if( okArray($attributi) ){
				$attributi2 = array();
				foreach($attributi as $k => $v){
					if( in_array($v['attribute_id'],$escludi_attributes )) continue;
					$attributi2[$v['attribute_id']][] = $v['value'];
					
					//$images = unserialize($v['images']);
					//$images_child[$v['value']] = $images[0];
					/*if( $v['img'] ){
						$images_child[$v['value']] = $v['img'];
					}*/
					
				}
				
				$lista_attr = array();
				foreach( $attributi2 as $attr_id => $valori ){
					$attributo = Attribute::withId($attr_id);
					
					$array_attr[$attr_id]['type'] = 'attributes';
					$array_attr[$attr_id]['id'] = $attr_id;
					$array_attr[$attr_id]['name'] = $attributo->get('name');
					
					foreach($valori as $v){
						
						$attr_value = AttributeValue::withId($v);
						
						if( is_object($attr_value) ){
							$option =  array(
								'value' => $v,
								'text' => $attr_value->get('value'),
								'order' => $attr_value->orderView,
								'img' => $attr_value->img,
								'resize' => 'or'
							);

							/*if( in_array($attributo->label,$this->_attribute_images)){
								$option['img'] = $images_child[$v];
								$option['resize'] = 'th';
							}*/

							if( $option['img'] ){
								$array_attr[$attr_id]['images'] = true;
							}
							if( in_array($v,$this->_formdata['filtri']['attributes']) ){
								$option['selected'] = 1;
							}
							$array_attr[$attr_id]['values'][$v] = $option;
						}
						
					}
					if( okArray($array_attr) ){
						foreach( $array_attr as $k => $v){
							if( okArray($array_attr[$k]['valori']) ){
								uasort($array_attr[$k]['valori'],function($a, $b){
									if ($a['order'] == $b['order']) {
										return 0;
									}
									return ($a['order'] < $b['order']) ? -1 : 1;
								});
							}
						}
					}
					
					
				}
				
			}
			if( okArray($array_attr) ){
				foreach($array_attr as $k => $v){
					$this->filtri[] = $v;
				}
			}

	}



	function getFilterFeatures($escludi_features=array()){
		$database = Marion::getDB();;

	
		$features = $database->select('pfv.*,l.value','(product_feature_association as pfc join product as p on p.id=pfc.id_product) join (product_feature_value as pfv join product_feature_value_lang as l on l.id_product_feature_value=pfv.id) on pfv.id=pfc.id_feature_value',"{$this->where} AND parent=0 AND l.lang='it' order by pfv.orderView");
			
			if( okArray($features) ){
				foreach($features as $v){
					if( in_array($v['id_product_feature'],$escludi_features )) continue;
					
					 $option = array(
						'value' => $v['id'],
						'text' => $v['value'],
					);
					if( okArray($this->_formdata) ){
						if( in_array($v['id'],$this->_formdata['filtri']['features']) ){
							$option['selected'] = 1;
						}

					}

					$values[$v['id_product_feature']][$v['id']] = $option;
				}

				

				foreach($values as $id_feature => $values){
					$feature = ProductFeature::withId($id_feature);
					
					if( is_object($feature) ){
						
						$this->filtri[] = array(
							'type' => 'features',
							'name' => $feature->get('name'),
							'values' => $values,
							'order' => $feature->orderView,
						);

						
					
					
					}
				}
				uasort($this->filtri,function($a, $b){
					if ($a['order'] == $b['order']) {
						return 0;
					}
					return ($a['order'] < $b['order']) ? -1 : 1;
				});
			}

	}

	function filtri_ricerca_get_section_children($section_id){
		$database = Marion::getDB();;
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