<?php
class AmazonCategory{
	public $_market;
	public $_lang;
	public $_currency;
    public $_mapped_data;
    public $_mapping_url = [];
    public $_variations_theme = [];
    public $_other_fields = [];
	
	function getName(){


    }

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
								require_once(_MARION_MODULE_DIR_.'filtri_ricerca/classes/ProductFeatureValue.class.php');
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

				}

			}
			
		}

		return $dati_loc;

	}

	


	function getAttributesTheme($theme){

		foreach($this->_other_fields as $k => $v){
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
            require_once(_MARION_MODULE_DIR_.'filtri_ricerca/classes/ProductFeature.class.php');

			$attributes = ProductFeature::prepareQuery()->get();
			foreach($attributes as $v){
				$array[$v->id] = $v->get('name');
			}
		}

		return $array;

	}
	//crea il form per la configurazione del profilo
	function getForm($id_store,$market){

		$widget = new WidgetComponent('amazon');
        
        $widget->setVar('id_store',$id_store);
        $widget->setVar('market',$market);
        $widget->setVar('category',get_class($this));
        
        
        $widget->setVar('url_mapping',$this->getUrlMapping($market));
		$widget->setVar('variations_theme',$this->_variations_theme);
		
		$attributes = $this->getSelectAttributes();
		$widget->setVar('attributes',$attributes);

		$features = $this->getSelectFeatures();
		$widget->setVar('features',$features);
		$widget->setVar('data',$this->data);
		
		foreach($this->_other_fields as $k => $v){
			$this->_other_fields[$k]['selected'] = $this->data[$k];
			$this->_other_fields[$k]['selected_value'] = $this->data[$k."Value"];
			$this->_other_fields[$k]['selected_attribute'] = $this->data[$k."Attribute"];
			$this->_other_fields[$k]['selected_feature'] = $this->data[$k."Feature"];

			if( $v['default_values'] ){
				
				$this->_other_fields[$k]['default_values'] = $v['default_values'][$market];
			}
        }
        
        //debugga($this->_other_fields);exit;

		

		

		
		$widget->setVar('other_fields',$this->_other_fields);
	
		//debugga($template->other_fileds);exit;
		ob_start();
		//get_form($elements,'amazon_profile_clothing','action',$this->data);
		$widget->output('form.htm');
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
	


}




?>