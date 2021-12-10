<?php

use Marion\Core\Marion;
function advanced_catalog_query_select($query){
	$query->leftOuterJoin('advancedCatalogSplitProduct as t4',"t4.parent_product=t1.id");
	$query->setFieldSelect('t4.images as images_child');
	$query->setFieldSelect('t4.id_product as id_child');
	$query->setFieldSelect('t4.value as attribute_child');
}

Marion::add_action('catalog_query_select','advanced_catalog_query_select');



/*function advanced_catalog_preview_attribute($prodotto,$type_img='small'){
	
	//if( Marion::isActivedModule('advanced_catalog') ){
		
		if( is_object($prodotto) ){
			
			$database = _obj('Database');
			$select = $database->select('*','advancedCatalogAttributePreview',"id_product={$prodotto->id}");
			if( okArray($select) ){
				$id_attribute = $select[0]['id_attribute'];
				$attribute = Attribute::withId($id_attribute);
				if( is_object($attribute) ){
					$label = $attribute->label;
				}
				if( $prodotto->isConfigurable()){
					$attr = $prodotto->getAttributes();
					if( array_key_exists($label,$attr) ){
						$widget = Marion::widget('advanced_catalog');
						


						//creo una stringa random
						 $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						$charactersLength = strlen($characters);
						$randomString = '';
						for ($i = 0; $i < 5; $i++) {
							$randomString .= $characters[rand(0, $charactersLength - 1)];
						}




						$children  = $prodotto->getChildren();
						if( okArray($children) ){
							foreach($children as $child){
								$attr1 = $child->getAttributes();
								if( okArray($attr1) ){
									$value = $attr1[$label];
									
									$list_tmp[$value] = array(
										'product' => $child->id,
										'img_product' => $child->getUrlImage(0,$type_img),
									);
									
								}
							}
						}
						
						$select = $prodotto->getAttributesView();
						$values = $select[$label];
						if( okArray($values['values']) ){
							foreach( $values['values'] as $k => $v ){
								$values['values'][$k]['product'] = $list_tmp[$k]['product'];
								$values['values'][$k]['img_product'] = $list_tmp[$k]['img_product'];
							}
						}
						$widget->product_id = $prodotto->id;
						
						$widget->option = $values;
						$widget->random = $randomString;
						
						$widget->output('preview_attribute.htm');

					}
					
				}

			}
			
		}
	//}
	
	


}
*/

function advanced_catalog_after_load($product){
	if( $product->_other_data['images_child'] ){
		$product->images = $product->_other_data['images_child'];
	}
	
	
	if( $product->_other_data['id_child'] ){
		$product->id_child = $product->_other_data['id_child'];
		$product->attribute_child = $product->_other_data['attribute_child'];

		$database = Marion::getDB();
		$qnt = $database->select('*','product_inventory',"id_product={$product->id_child}");
		if( okArray($qnt) ){
			$product->stock = $qnt[0]['quantity'];
		}
		
		
	}
	
}

Marion::add_action('after_load_product','advanced_catalog_after_load');






class SplitProductTabAdmin extends ProductTabAdminController{
	


	public function getTitle(): string{
		return 'Split Product';
	}

	public function getTag():string{
		
		return 'advanced_catalog';
	}


	function getContent(){
		
		$formdata = $this->getFormdata();
		if($formdata['type']){
			$this->setVar('product_type',$formdata['type']);
		}
		
		
		$id = $this->getID();

		if( !$id ){
			$this->setVar('no_saved',1);
			$this->output('tab_product.htm');
			return false;
		}

		$product = Product::withId($id);

		if( $product->parent ){
			$this->setVar('has_parent',1);
			$this->output('tab_product.htm');
			return false;
		}
		$action = $this->getAction();

		$database = Marion::getDB();
		$dati = [];
		$select = $database->select('*','advancedCatalogAttributeExplode',"id_product={$product->id}");
		if( okArray($select) ){
			$dati['explode_product_catalog_attribute'] = $select[0]['id_attribute'];
		}
		$select = $database->select('*','advancedCatalogAttributePreview',"id_product={$product->id}");
		if( okArray($select) ){
			$dati['preview_product_catalog_attribute'] = $select[0]['id_attribute'];
		}
		
		
		

		
		
		

		$dataform = $this->getDataForm('advanced_catalog_split_form',$dati);
		$this->setVar('dataform',$dataform);

		
	
		

	
		$this->output('tab_product.htm');

		




	}
	

	function checkData(){
		$formdata = $this->getFormdata();
		
		$error = 1;
		
	
		
	
		$array = $this->checkDataForm('advanced_catalog_split_form',$formdata);
		
		if( $array[0] == 'ok'){
			$this->checked_data = $array;
			
		}else{
			$error = $array[1];
		}
		
		return $error;
	}

	function reloadContent():bool{
		return false;
	}

	function reloadPage():bool{
		return false;
		
	}


	function process($product=null){
		
		$dati = $this->checked_data;
		$database = Marion::getDB();
		
		
		
		$attribute_explode = $dati['explode_product_catalog_attribute'];
		$attribute_preview = $dati['preview_product_catalog_attribute'];
		$database->delete('advancedCatalogAttributeExplode',"id_product={$product->id}");
		$database->delete('advancedCatalogAttributePreview',"id_product={$product->id}");
		if( !$product->parent ){
			$database->delete('advancedCatalogSplitProduct',"parent_product={$product->id}");
			if( $attribute_explode ){
				$toinsert = array(
					'id_product' => $product->id,
					'id_attribute' => $attribute_explode,
				);
				$database->insert("advancedCatalogAttributeExplode",$toinsert);
		
				$attribute = Attribute::withId($attribute_explode);
				if( is_object($attribute) ){
					$label = $attribute->label;
					
					$children = $product->getChildren();
					if( okArray($children) ){
						foreach( $children as $child ){
							if( !$child->visibility ) continue;
							$attributes = $child->getAttributes();
							if( okArray($attributes) && $attributes[$label] ){
								$unique[$attributes[$label]]['id'] = $child->id;
								$unique[$attributes[$label]]['images'] = $child->images;
							}
						}
					}
					
					
					if( okArray($unique) ){
						foreach($unique as $k => $u){
							$toinsert = array(
								'id_product' => $u['id'],
								'parent_product' => $product->id,
								'value' => $k,
								'images' => serialize($u['images'])
							);
							
							$database->insert('advancedCatalogSplitProduct',$toinsert);
						}
					}
				}
				
			}

			if( $attribute_preview ){
				$toinsert = array(
					'id_product' => $product->id,
					'id_attribute' => $attribute_preview,
				);
				$database->insert("advancedCatalogAttributePreview",$toinsert);
			}
			
			
		}

	
		
	}


	//FUNZIONI FORM
	function attributes(){

		$id = _var('id');
		if( !$id ){
			$formdata = _formdata();
			$id = $formdata['id'];
		}
		



		$product = Product::withId($id);
		
		
		if( is_object($product)){
			$attributeSet = $product->getAttributeSet();
		}else{
			$id_attribute_set = _var('attributeSet');
			if( !$id_attribute_set ){
				$id_attribute_set = $formdata['attributeSet'];
			}
			$attributeSet = AttributeSet::withId($id_attribute_set);
		}
		$select[0] = __('seleziona');
		if( is_object($attributeSet) ){
			$attributes = $attributeSet->getAttributes();
			if( okArray($attributes) ){
				foreach($attributes as $v){
					$attribute = Attribute::withId($v['attribute']);
					if( is_object($attribute) ){
						$select[$attribute->id] = $attribute->get('name');
					}
				}
				
			}
		}
		
		
		return $select;

	}


}


Product::registerAdminTab('SplitProductTabAdmin');


?>