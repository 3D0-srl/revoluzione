<?php
class Shoes extends AmazonCategory{


	
	public $_mapping_url = array(
		'Italy' => 'https://s3.amazonaws.com/category-custom-templates/ff/eu/it/Flat.File.Shoes.it.xlsm',
		'France' => 'https://s3.amazonaws.com/category-custom-templates/ff/eu/fr/Flat.File.Shoes.fr.xlsm',
		'UK' => 'https://s3.amazonaws.com/category-custom-templates/ff/eu/uk/Flat.File.Shoes.uk.xlsm',
		'Germany' => 'https://s3.amazonaws.com/category-custom-templates/ff/eu/de/Flat.File.Shoes.de.xlsm',
		'Spain' => 'https://s3.amazonaws.com/category-custom-templates/ff/eu/es/Flat.File.Shoes.es.xlsm',
	);


	public $_variations_theme = 
		array(
			'Size' => 'Taglia',
			'Color' => 'Colore',
			'SizeColor' => 'Taglia e Colore',
			'ColorName-MagnificationStrength' => 'Nome colore e Forza di ingrandimento',
			'ColorName-LensColor' => 'Nome colore e Colore lente',
			'ColorName-LensWidth' => 'Nome colore e Larghezza obiettivo',
		);
	public $_other_fields = array(
		'ClothingType' => array(
				'label' => 'Tipologia',
				'type_html' => 'select',
				'options' => array(
						'Accessory' => 'Accessory',
						'Bag' => 'Bag',
						'Pants' => 'Pants',
						'Shoes' => 'Shoes',
						'ShoeAccessory' => 'ShoeAccessory',
						'Eyewear' => 'Eyewear',
					),
				'required' => 1,
			),
		'Department' =>  array(
			'label' => 'Reparto',
			'type_html' => 'input',
			'required' => 1,
			'example' => 'Uomo',
			'default_values' => array(
					'Italy' => array('bambina','bambino','donna','unisex adulto','unisex bambino','uomo')

				),

		),
		'OuterMaterialType' =>  array(
			'label' => 'Materiale esterno',
			'type_html' => 'input',
			'required' => 1,
			'example' => 'Cotone',
			'default_values' => 
				array(
				'Italy' => array('Beaded','Caucciù','Feltro','Leather','Satin','Seta','Synthetic','Tela','Velvet','Wool','con perline','feltro','gomma','lana','pelle','raso','seta','sintetico','tela','velluto')
				),
		),
		'Size' =>  array(
			'label' => 'Taglia',
			'type_html' => 'input',
			//'required' => 1,
			'themes' => array(
				'Size','SizeColor'
			)
			//'example' => 'Uomo'
		),
		'Color' =>  array(
			'label' => 'Colore',
			'type_html' => 'input',
			//'required' => 1,
			'themes' => array(
				'Color','SizeColor'
			)
			//'example' => 'Uomo'
		),

	);
	

	function getName(){
		return 'Shoes';
	}


	function buildData($product){

		
		$manufacturer = Manufacturer::withId($product->manufacturer);
		if( is_object($manufacturer)){
			$name_manufacturer = $manufacturer->get('name',$this->_lang);
		}
		$outer_material_type = $this->getValue('OuterMaterialType',$product);
		$department = $this->getValue('Department',$product);
		
		if( $this->getMappedValue('Department',$department) ){
			$department = $this->getMappedValue('Department',$department);
		}
	
		$size = $this->getValue('Size',$product);
		if( $size ){
			$size_map = $this->getMappedValue('Size',$size);
			if( !$size_map ) $size_map = $size;
		}
		$color = $this->getValue('Color',$product);
		if( $color ){
			$color_map = $this->getMappedValue('Color',$color);
			if( !$color_map ) $color_map = $color;
		}

		if( $product->upc ){
			$standard_product_id_value = $product->upc;
			$standard_product_id_type = 'UPC';
		}
		if( $product->ean ){
			$standard_product_id_value = $product->ean;
			$standard_product_id_type = 'EAN';
		}
		
		
		$data = array(
			'MessageID' => $product->id,
			'SKU' => 'MARION_'.$product->id,
			'standard_product_id_value' => $standard_product_id_value,
			'standard_product_id_type' => $standard_product_id_type,
			'VariationTheme' => $this->data['variationTheme'],
			'Brand' => $name_manufacturer,
			'Title' => $product->get('name',$this->_lang),
			'Description' => strip_tags($product->get('description',$this->_lang)),
			'ClothingType' => $this->data['ClothingType'],
			'Department' => $department,
			'OuterMaterialType' => $outer_material_type,
			'ProductTaxCode' => 'A_GEN_TAX',
			'LaunchDate' => date('Y-m-d').'T'.date('H:i:s'),
			'Size' => $size,
			'Color' => $color,
			'SizeMap' => $size_map,
			'ColorMap' => $color_map,
		);
		$children = $product->getChildren();

		if( !okArray($children) && !$data['standard_product_id_value']) return null;
		if( okArray($children) ){
			foreach( $children as $child){
				$outer_material_type = $this->getValue('OuterMaterialType',$child);
				$department = $this->getValue('Department',$child);
		
				if( $this->getMappedValue('Department',$department) ){
					$department = $this->getMappedValue('Department',$department);
				}
				$size = $this->getValue('Size',$child);
				if( $size ){
					$size_map = $this->getMappedValue('Size',$size);
					if( !$size_map ) $size_map = $size;
				}
				$color = $this->getValue('Color',$child);
				if( $color ){
					$color_map = $this->getMappedValue('Color',$color);
					if( !$color_map ) $color_map = $color;
				}
				if( $child->upc ){
					$standard_product_id_value = $child->upc;
					$standard_product_id_type = 'UPC';
				}
				if( $child->ean ){
					$standard_product_id_value = $child->ean;
					$standard_product_id_type = 'EAN';
				}
				$data2 = array(
					'MessageID' => $child->id,
					'SKU' => 'MARION_'.$child->id,
					'standard_product_id_value' => $standard_product_id_value,
					'standard_product_id_type' => $standard_product_id_type,
					'VariationTheme' => $this->data['variationTheme'],
					'Brand' => $name_manufacturer,
					'Title' => $child->get('name',$this->_lang),
					'Description' => strip_tags($child->get('description',$this->_lang)),
					'ClothingType' => $this->data['ClothingType'],
					'Department' => $department,
					'OuterMaterialType' => $outer_material_type,
					'ProductTaxCode' => 'A_GEN_TAX',
					'LaunchDate' => date('Y-m-d').'T'.date('H:i:s'),
					'Size' => $size,
					'Color' => $color,
					'SizeMap' => $size_map,
					'ColorMap' => $color_map,
				);
				$data['children'][] = $data2;
			}
		}

		return $data;
	}
	
	function getXmlProduct($product=NULL){
		if( is_object($product) ){
			$tmp = $product;
			unset($product);
			$product = array($tmp);
		}
		
		$feed = '';
		$template = _obj('Template');
		foreach($product as $prod){
			$data = $this->buildData($prod);
			if( okArray($data) ){
				$template->data = $data;
				ob_start();
				$template->output_module('amazon','shoes.xml');
				$xml = ob_get_contents();
				ob_end_clean();

				$feed .= $xml;
			}
		}
		return $feed;

	}
	



	/*function getXmlProduct($product=NULL){
		
		$currency = $this->_currency;
		$lang = $this->_lang;
		
		
		if( is_object($product) ){
			$tmp = $product;
			unset($product);
			$product = array($tmp);
		}

		debugga($product);exit;
		

		$OuterMaterialType = $this->getValue('OuterMaterialType',$product);
		


		
		$materialComposition = $this->getValue('MaterialComposition',$product);
		$department = $this->getValue('Department',$product);
		$feed = '';
		
		
		foreach($product as $prod){
			if( $product->deleted ) continue;
			$hasChildren = $prod->hasChildren();
			$manufacturer = Manufacturer::withId($prod->manufacturer);
			if( is_object($manufacturer)){
				$name_manufacturer = $manufacturer->get('name',$lang);
			}
			if($hasChildren){
				$feed .= '<Message>
						<MessageID>'.$prod->id.'</MessageID>
						<OperationType>Update</OperationType>
						<Product>
							<SKU>MARION_'.$prod->id.'</SKU>
							<ProductTaxCode>A_GEN_TAX</ProductTaxCode>
							<LaunchDate>'.date('Y-m-d').'T'.date('H:i:s').'</LaunchDate>
							<DescriptionData>
								<Title>'.$prod->get('name',$lang).'</Title>
								<Brand>'.$name_manufacturer.'</Brand>
								<Description>'.htmlentities($prod->get('name',$lang)).'</Description>
							</DescriptionData>';
							if($hasChildren){
								$feed.='<ProductData>
									<Shoes>
										<ClothingType>'.$this->data['ClothingType'].'</ClothingType>
										<VariationData>
											<Parentage>parent</Parentage>
											<VariationTheme>'.$this->data['variationTheme'].'</VariationTheme>
										</VariationData>
										<ClassificationData>
											<Department>'.$department.'</Department>
											<OuterMaterialType>'.$OuterMaterialType.'</OuterMaterialType>
										</ClassificationData>
									</Shoes>
								</ProductData>';
							}
							$feed.='</Product>
				</Message>';
			
			 
				
				foreach($prod->getChildren() as $variant){
					if( $variant->deleted ) continue;
					if(  !$variant->upc  && !$variant->ean ) continue; 
					if( $variant->upc ){
						$_id_code = $variant->upc;
						$_id_type = 'UPC';
					}
					if( $variant->ean ){
						$_id_code = $variant->ean;
						$_id_type = 'EAN';
					}
					
					$feed.='<Message>
							<MessageID>'.$variant->id.'</MessageID>
							<OperationType>Update</OperationType>
							<Product>
								<SKU>MARION_'.$variant->id.'</SKU>
								<StandardProductID>
									<Type>'.$_id_type.'</Type>
									<Value>'.$_id_code.'</Value>
								</StandardProductID>
								<ProductTaxCode>A_GEN_TAX</ProductTaxCode>
								<LaunchDate>'.date('Y-m-d').'T'.date('H:i:s').'</LaunchDate>
								<DescriptionData>
									<Title>'.$variant->get('name',$lang).'</Title>
									<Brand>'.$name_manufacturer.'</Brand>
									<Description>'.htmlentities($variant->get('name',$lang)).'</Description>
								</DescriptionData>
								<ProductData>
									<Shoes>
									<ClothingType>'.$this->data['ClothingType'].'</ClothingType>
									';
										
									$feed .= $this->getVariationDataXML($variant,$lang);
									$feed.= '<ClassificationData>
									<Department>'.$department.'</Department>';
									$feed .= $this->getColorMap($variant,$lang,$mapping);
									//if( $variant['ColorMap'] ){
									//	$feed.='<ColorMap>'.$variant['ColorMap'].'</ColorMap>';
									//}
									$feed.='
									<OuterMaterialType>'.$OuterMaterialType.'</OuterMaterialType>';
									//if( $variant['SizeMap'] ){
									//	$feed.='<SizeMap>'.$variant['SizeMap'].'</SizeMap>';
									//}
									$feed .= $this->getSizeMap($variant,$lang,$mapping);
									$feed.='</ClassificationData>
									</Shoes>
								</ProductData>
							</Product>
					</Message>';
				}
			}else{

					
					if(  !$prod->upc  && !$prod->ean ) continue; 

					if( $prod->upc ){
						$_id_code = $product->upc;
						$_id_type = 'UPC';
					}
					if( $prod->ean ){
						$_id_code = $prod->ean;
						$_id_type = 'EAN';
					}
					
					$feed.='<Message>
							<MessageID>'.$prod->id.'</MessageID>
							<OperationType>Update</OperationType>
							<Product>
								<SKU>MARION_'.$prod->id.'</SKU>
								<StandardProductID>
									<Type>'.$_id_type.'</Type>
									<Value>'.$_id_code.'</Value>
								</StandardProductID>
								<ProductTaxCode>A_GEN_TAX</ProductTaxCode>
								<LaunchDate>'.date('Y-m-d').'T'.date('H:i:s').'</LaunchDate>
								<DescriptionData>
									<Title>'.$prod->get('name',$lang).'</Title>
									<Brand>'.$name_manufacturer.'</Brand>
									<Description>'.htmlentities($prod->get('name',$lang)).'</Description>
								</DescriptionData>
								<ProductData>
									<Shoes>
									<ClothingType>'.$this->data['ClothingType'].'</ClothingType>
									
									';
										
									$feed .= $this->getVariationDataXML($prod,$lang);
									$feed.= '<ClassificationData>
									
									<Department>'.$department.'</Department>';
									$feed .= $this->getColorMap($prod,$lang,$mapping);
									$feed.='
									<OuterMaterialType>'.$OuterMaterialType.'</OuterMaterialType>';
									$feed .= $this->getSizeMap($prod,$lang,$mapping);
									$feed.='</ClassificationData>
									</Shoes>
								</ProductData>
							</Product>
					</Message>';

			}
		}

		return $feed;
		
	}*/


	function getColorMap($product,$lang,$mapping){
		$xml = '';

		$variationTheme = $this->data['variationTheme'];
		if( $variationTheme == 'Color' || $variationTheme == 'SizeColor'){
			$value = $this->getValue('Color',$product,$lang);
			$map = $mapping['Color'][$value];
			if( $map ){
				$xml = '<ColorMap>'.$map.'</ColorMap>';
			}
			
		}
		
		return $xml;

	}

	function getSizeMap($product,$lang,$mapping){
		$xml = '';
		$variationTheme = $this->data['variationTheme'];
		if( $variationTheme == 'Size' || $variationTheme == 'SizeColor'){
			$value = $this->getValue('Size',$product,$lang);
			$map = $mapping['Size'][$value];
			if( $map ){
				$xml = '<SizeMap>'.$map.'</SizeMap>';
			}
		}
		return $xml;
	}
	
}


?>