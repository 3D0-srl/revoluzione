<?php
class AmazonCategory{
	public $_market;
	public $_lang;
	public $_currency;
	public $_mapped_data;
	
	

	function init($market,$mapped_data){

		
		$this->_market = $market;
		
		$this->_currency = AmazonTool::$currency_markets[$market];
		
		$this->_lang = AmazonTool::$lang_markets[$market];
		$this->_mapped_data = $mapped_data;

		
		
	}

	function setData($data){
		$this->data = $data;
	}
	

	function getUrlMapping($market){
		return $this->_mapping_url[$market];
	}
	
	

	function getProductInfo($product = null){
		if( is_object($product) ){
			$database = _obj('Database');
			$dati = $database->select('*','amazon_product',"id_product={$product->id} AND marketplace='{$this->_market}'");
			if( okArray($dati) ){
				return $dati[0];
			}
			return false;
		}
	}

	

	function getMappedValues(){
		
		if( $this->_lang ){
			$lang = $this->_lang;
			$mapping = $this->_mapped_data;
			$_campi = array_keys($this->_other_fileds);

			
			foreach($_campi as $c){
				switch($this->data[$c]){

					case 'attribute':
						$id_attribute = $this->data[$c.'Attribute'];
						if( $id_attribute ){
							$dati_loc[$c]['nome'] = $this->_other_fileds[$c]['label'];
							$values = AttributeValue::preparequery()->where('attribute',$id_attribute)->get();
							
							foreach($values as $v){
								if( $mapping[$c][$v->get('value',$lang)] ){
									$valore = $mapping[$c][$v->get('value',$lang)];
								}else{
									$valore = $v->get('value',$lang);
								}
								
								$dati_loc[$c]['values'][$v->get('value',$lang)] = $valore;
								
							}
						}
						
						
						break;
					case 'feature':
						if( Marion::isActivedModule('filtri_ricerca') ){
							$id_feature = $this->data[$c.'Feature'];
							
							if( $id_feature ){
								$dati_loc[$c]['nome'] = $this->_other_fileds[$c]['label'];
								require_once('../filtri_ricerca/classes/ProductFeatureValue.class.php');
								$values = ProductFeatureValue::preparequery()->where('id_feature',$id_feature)->get();
								foreach($values as $v){
									if( $mapping[$c][$v->get('value',$lang)] ){
										$valore = $mapping[$c][$v->get('value',$lang)];
									}else{
										$valore = $v->get('value',$lang);
									}
									$dati_loc[$c]['values'][$v->get('value',$lang)] = $valore;
									
								}
							}
							
						}
						break;
					/*case 'value':
						$dati_loc[$c]['nome'] = $this->_other_fileds[$c]['label'];
						if( $mapping[$c][$this->data[$c."Value"]] ){
							$valore = $mapping[$c][$this->data[$c."Value"]];
						}else{
							$valore = $this->data[$c."Value"];
						}
						$dati_loc[$c]['values'][$this->data[$c."Value"]] = $valore;

						break;

					case 'default':
						$dati_loc[$c]['nome'] = $this->_other_fileds[$c]['label'];
						if( $mapping[$c][$this->data[$c."Default"]] ){
							$valore = $mapping[$c][$this->data[$c."Default"]];
						}else{
							$valore = $this->data[$c."Default"];
						}
						$dati_loc[$c]['values'][$this->data[$c."Default"]] = $valore;

						break;*/

				}

			}
			
		}

		return $dati_loc;

	}

	


	function getAttributesTheme($theme){

		foreach($this->_other_fileds as $k => $v){
			if( in_array($theme,$v['themes']) ){
				$attr[] = $k;
			}

		}
		return $attr;

	}

	function getSelectAttributes(){
		

		$attributes = Attribute::prepareQuery()->get();
		foreach($attributes as $v){
			$array[$v->id] = $v->get('name');
		}
		

		return $array;

	}

		
	function getSelectFeatures(){
		if( Marion::isActivedModule('filtri_ricerca') ){
			require_once('../filtri_ricerca/classes/ProductFeature.class.php');

			$attributes = ProductFeature::prepareQuery()->get();
			foreach($attributes as $v){
				$array[$v->id] = $v->get('name');
			}
		}

		return $array;

	}
	//crea il form per la configurazione del profilo
	function getForm($market){
		$template = _obj('Template');
		$template->url_mapping = $this->getUrlMapping($market);
		$template->variations_theme = $this->_variations_theme;
		
		$attributes = $this->getSelectAttributes();
		$template->attributes = $attributes;

		$features = $this->getSelectFeatures();
		$template->features = $features;
		$template->data = $this->data;
		
		foreach($this->_other_fileds as $k => $v){
			$this->_other_fileds[$k]['selected'] = $this->data[$k];
			$this->_other_fileds[$k]['selected_value'] = $this->data[$k."Value"];
			$this->_other_fileds[$k]['selected_attribute'] = $this->data[$k."Attribute"];
			$this->_other_fileds[$k]['selected_feature'] = $this->data[$k."Feature"];

			if( $v['default_values'] ){
				
				$this->_other_fileds[$k]['default_values'] = $v['default_values'][$market];
			}
		}

		

		

		
		$template->other_fileds = $this->_other_fileds;
	
		//debugga($template->other_fileds);exit;
		ob_start();
		//get_form($elements,'amazon_profile_clothing','action',$this->data);
		$template->output_module('amazon','template_category.htm');
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	//controlla i dati del form per la configurazione del profilo
	function checkForm($data){

		$fields = $this->_other_fileds;
		foreach($fields as $k => $v){
			if( $v['required'] && !$data[$k]){
				$errore['errore'] = "Il campo ".$v['label']." è obbligatorio";
				$errore['campo'] = $k;
				break;
			}
			if( in_array($data['variationTheme'],$v['themes']) && !$data[$k]){
				$errore['errore'] = "Il campo ".$v['label']." è obbligatorio";
				$errore['campo'] = $k;
				break;
			}
		}	
		return $errore;
		//debugga($data);exit;

	}

	function getVariationDataXML($child,$lang){
			
			$xml = '';
			$variationTheme = $this->data['variationTheme'];
			switch($variationTheme){
				case 'SizeColor':
					$size = $this->getValue('Size',$child,$lang);
					$color = $this->getValue('Color',$child,$lang);
					$xml_data = "<Size>{$size}</Size>";
					$xml_data .= "<Color>{$color}</Color>";
					break;
				case 'Size':
					$size = $this->getValue('Size',$child,$lang);
					$xml_data = "<Size>{$size}</Size>";
					break;
				case 'Color':
					$color = $this->getValue('Color',$child,$lang);
					$xml_data = "<Color>{$color}</Color>";
					break;
			}
			if( $xml_data ){
				$xml .= "<VariationData>";
				if( $child->parent){
					$xml .="<Parentage>child</Parentage>";
				}
				$xml .=	"{$xml_data}
						<VariationTheme>{$variationTheme}</VariationTheme>
						</VariationData>
				";
			}
			
			return $xml;

	}
	//restituisce il valore mappato di una variabile 
	function getMappedValue($name_attribute,$value){
		if( $this->_mapped_data[$name_attribute][$value] ){
			return $this->_mapped_data[$name_attribute][$value] ;
		}
		return false;
	}

	//restituisce il valore di una variabile
	function getValue($name_attribute,$product){
		$lang = $this->_lang;
		$value = '';
	
		switch($this->data[$name_attribute]){
			case 'value':
				return $this->data[$name_attribute.'Value'];
				break;
			case 'default':
				return $this->data[$name_attribute.'Default'];
				break;
			case 'attribute':
					
				if( is_object($product) ){
					$attributes = $product->getAttributes();
					if( okArray($attributes) ){

						$id_attribute = $this->data[$name_attribute."Attribute"];
						
						if( $id_attribute ){
							$attribute = Attribute::withId($id_attribute);
							
							if( is_object($attribute) ){
								
								
								$id_value = $attributes[$attribute->label];
								
								if( $id_value ){
									$value_obj = AttributeValue::withId($id_value);
									
									if( is_object($value_obj) ){
										$value = $value_obj->get('value',$lang);
									}
								}	

							}

						}
					}
				}
				
				break;
			case 'feature':
				
				if( Marion::isActivedModule('filtri_ricerca') ){
					$id_feature = $this->data[$name_attribute.'Feature'];
					
					if( $id_feature ){
						
						require_once('../filtri_ricerca/classes/ProductFeatureValue.class.php');
						
						$feature = ProductFeatureValue::withId($id_feature);
						
						
					}
					
				}

				break;
		}
		
		return $value;
	}
	

	/*
	function prepareData($products){
		
		if( is_object($products) ){
			$tmp = $products;
			unset($products);
			$products[] = $tmp;
		}

		foreach($products as $product){

			if( is_object($product) ){
					
				// id product is online
				if( $product->visibility ){
					
					
					$data = array(
						'id' => $product->id,
						'name' => $product->get('name',$code_lang),
						'description' => $product->get('description',$code_lang),
						'sku' => "MARION_".$product->id, //product reference
						'ean' => $product->ean, //ean code
						'quantity' => $product->stock, //quantity
						'weight' => $product->weight //weight in gr.
					);

					if( $product->manufacturer ){
						$brand = Manufacturer::withId($product->manufacturer);
						if( is_object($brand) ){
							$brand_name = $brand->get('name');
							$data['brand'] = $brand_name;
						}
						
					}

			
					
					foreach($product->images as $k => $v){
							
						$data['images'][] = $base_url.$product->getUrlImage($k);

					}
					
					//if product has variations
					if( $product->isConfigurable()){
						$data['quantity'] = 0;
						unset($data['weight']);
						//attribute set
						$attributeSet = $product->getAttributeSet();
						if( is_object($attributeSet) ){
							$attributes = $attributeSet->getAttributes();
							foreach($attributes as $v){
								$attribute = Attribute::withId($v['attribute']);
								if( is_object($attribute) ){
									//attribute
									$_name_attribute[$attribute->label] = $attribute->get('name',$code_lang); //se in inglese devo mettere 'en' al posto di 'it'
								}
							}
						}
						$data['variation_theme'] = $_profile['amazon_variation_theme'];
						//array containing the attribute tag as a key and the attribute name as the value
						//debugga($_name_attribute);exit;



						//I take the children of the father product in such a way as to take the variations
						$children = $product->getChildren();
						if( okArray($children) ){
							foreach($children as $v){
								


								$variations_child = array();
								$mapAmazon = array();
								$attributes = $v->getAttributes();
								foreach($attributes as $label => $attribute_value_id){
									$attribute_value = AttributeValue::withid($attribute_value_id);
									if( is_object($attribute_value) ){
										$variations_child[] = array(
											'name' => $_name_attribute[$label],
											'value' => $attribute_value->get('value')
										);
										
										//$mapAmazon[$_name_attribute[$label]] = get_mapping_variation($attribute_value->get('value'));

									}
					
								}
								list($price_final_child,$price_final_child_without_discount) = $this->getPrices($v,$product->taxCode);
								$child_data = array(
									'id' => $v->id,
									'sku' =>"MARION_".$v->id,
									'ean' => $v->ean,
									'quantity' => $v->stock,
									'weight' => $v->weight, //weight in gr.
									'price' => $price_final_child,
									'attributes' => $variations_child,
									
								);
								foreach($mapAmazon as $x => $b){
									$child_data[$x."Map"] = $b;
								}
								if( $data['price'] ){
									$data['price'] = min($data['price'],$child_data['price']);
								}else{
									$data['price'] = $child_data['price'];
								}
								if( $data['brand'] ){
									$child_data['brand'] = $data['brand'];
								}


								$data['quantity'] += $child_data['quantity'];

								//get shipping price for specific country
								//foreach($countries as $country){
								//	$child_data['shipping_price'][$country] = $shippingMethod->getPrice($country,$v->weight);
								//}
								$child_data['shipping_price'] = 6;
								foreach($v->images as $k1 => $v1){
						
									$child_data['images'][] = $base_url.$v->getUrlImage($k1);

								}

								$data['variations'][] = $child_data;
								
					
							}
						}

					}else{
		
						//get shipping price for specific country
						//foreach($countries as $country){
						//	$data['shipping_price'][$country] = $shippingMethod->getPrice($country,$product->weight);
						//}

						$data['shipping_price'] = 6;
						//or to get information on the offer, see the function below
						list($price_final_product,$price_final_product_without_discount) = $this->getPrices($product);
						$data['price'] = $price_final_product;
					}
					
					$list[] = $data;


				}
			}
		}
		$this->data_products = $list;
	}

	function getPrices($product,$taxCode){
		if( is_object($product) ){

			
			$price = $product->getPrice();

			if( $price->defaultValue){
				$product_price = $price->value; //price value without tax
				$product_price_without_discount = $price->defaultValue; //prezzo di listino senza tassa
			}else{
				$product_price = $price->value; //price value without tax
			}
			if( !$taxCode ){
				$taxCode = $product->taxCode;
			}
			if( $taxCode ){
				$tax = Tax::withId($taxCode);
				if( is_object($tax) ){
					$tax_percentage = $tax->percentage;
					if( $tax_percentage ){
						$product_price += $product_price*$tax_percentage/100;
						if( $product_price_without_discount ){
							$product_price_without_discount += $product_price_without_discount*$tax_percentage/100;
						}
					}
				}
			}

		}
		
		return array($product_price,$product_price_without_discount);
	}*/

}




?>