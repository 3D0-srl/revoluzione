<?php
class Clothing extends AmazonCategory{
	
	public $_mapping_url = array(
		'Italy' => 'https://s3.amazonaws.com/category-custom-templates/ff/eu/it/Flat.File.Clothing.it.xlsm',
		'France' => 'https://s3.amazonaws.com/category-custom-templates/ff/eu/fr/Flat.File.Clothing.fr.xlsm',
		'UK' => 'https://s3.amazonaws.com/category-custom-templates/ff/eu/uk/Flat.File.Clothing.uk.xlsm',
		'Germany' => 'https://s3.amazonaws.com/category-custom-templates/ff/eu/de/Flat.File.Clothing.de.xlsm',
		'Spain' => 'https://s3.amazonaws.com/category-custom-templates/ff/eu/es/Flat.File.Clothing.es.xlsm',
	);
	
	public $_variations_theme = 
		array(
			'Size' => 'Taglia',
			'Color' => 'Colore',
			'SizeColor' => 'Taglia e Colore',
		);
	public $_other_fields = array(
		'ClothingType' => array(
				'label' => 'Tipologia',
				'type_html' => 'select',
				'options' => array(
						'Shirt' => 'Shirt',
						'Sweater' => 'Sweater',
						'Pants' => 'Pants',
						'Shorts' => 'Shorts',
						'Skirt' => 'Skirt',
						'Dress' => 'Dress',
						'Suit' => 'Suit',
						'Blazer' => 'Blazer',
						'Outerwear' => 'Outerwear',
						'SocksHosiery' => 'SocksHosiery',
						'Underwear' => 'Underwear',
						'Bra' => 'Bra',
						'Shoes' => 'Shoes',
						'Hat' => 'Hat',
						'Bag' => 'Bag',
						'Accessory' => 'Accessory',
						'Jewelry' => 'Jewelry',
						'Sleepwear' => 'Sleepwear',
						'Swimwear' => 'Swimwear',
						'PersonalBodyCare' => 'PersonalBodyCare',
						'HomeAccessory' => 'HomeAccessory',
						'NonApparelMisc' => 'NonApparelMisc',
						'Kimono' => 'Kimono',
						'Obi' => 'Obi',
						'Chanchanko' => 'Chanchanko',
						'Jinbei' => 'Jinbei',
						'Yukata' => 'Yukata',
					),
				'required' => 1,
			),
		'Department' =>  array(
			'label' => 'Reparto',
			'type_html' => 'input',
			'required' => 1,
			'example' => 'Uomo',
			'default_values' => array(
					'Italy' => array('Bambini e ragazzi','Bimba','Bimbo','Uomo','Donna')

				),
			'mapping' => true, //questo valore pu� essere mappato

		),
		'InnerMaterial' =>  array(
			'label' => 'Materiale interno',
			'type_html' => 'textarea',
			'required' => 0,
			'example' => '60% Cotone, 40% Poliestere',
			/*'default_values' => 
				array(
				'Italy' => array('canapa','con paillettes','denim','feltro','gomma','lana','lino','pelle','pelliccia','pelliccia sintentica','piumino','pile','satin','seta','sintetico','latex','vernice')
				),*/
			'mapping' => false,
		),
		
		'OuterMaterial' =>  array(
			'label' => 'Materiale esterno',
			'type_html' => 'input',
			'required' => 1,
			'example' => 'Cotone',
			'default_values' => 
				array(
				'Italy' => array('canapa','con paillettes','denim','feltro','gomma','lana','lino','pelle','pelliccia','pelliccia sintentica','piumino','pile','satin','seta','sintetico','latex','vernice')
				),
			'mapping' => true,
		),
		'MaterialComposition' =>  array(
			'label' => 'Composizione Materiale',
			'type_html' => 'textarea',
			'required' => 1,
			'example' => '100% cotone'
		),
		'Size' =>  array(
			'label' => 'Taglia',
			'type_html' => 'input',
			//'required' => 1,
			'themes' => array(
				'Size','SizeColor'
			),
			'mapping' => true,
			//'example' => 'Uomo'
		),
		'Color' =>  array(
			'label' => 'Colore',
			'type_html' => 'input',
			//'required' => 1,
			'themes' => array(
				'Color','SizeColor'
			),
			'mapping' => true,
			//'example' => 'Uomo'
		),

	);




	function getName(){
		return 'Clothing';
	}
	

	function buildData(&$product){

		
		$manufacturer = Manufacturer::withId($product->manufacturer);
		if( is_object($manufacturer)){
			$name_manufacturer = $manufacturer->get('name',$this->_lang);
		}
		$material_composition = $this->getValue('MaterialComposition',$product);
		$department = $this->getValue('Department',$product);
		$outer_material = $this->getValue('OuterMaterial',$product);
		
		if( $this->getMappedValue('Department',$department) ){
			$department = $this->getMappedValue('Department',$department);
		}
		if( $this->getMappedValue('OuterMaterial',$outer_material) ){
			$outer_material = $this->getMappedValue('OuterMaterial',$outer_material);
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
		

		$_data_prod_info = $this->getProductInfo($product);
		if( $_data_prod_info['price'] ) $product->amazon_price = $_data_prod_info['price'];
		
		$data = array(
			'MessageID' => $product->id,
			'SKU' => $product->id."_".$product->sku,
			'standard_product_id_value' => $standard_product_id_value,
			'standard_product_id_type' => $standard_product_id_type,
			'VariationTheme' => $this->data['variationTheme'],
			'Brand' => $name_manufacturer,
			'Title' => $product->get('name',$this->_lang),
			'Description' => strip_tags($product->get('description',$this->_lang)),
			'ClothingType' => $this->data['ClothingType'],
			'Department' => $department,
			'MaterialComposition' => $material_composition,
			'OuterMaterial' => $outer_material,
			'ProductTaxCode' => 'A_GEN_TAX',
			'LaunchDate' => date('Y-m-d').'T'.date('H:i:s'),
			'Size' => $size,
			'Color' => $color,
			'SizeMap' => $size_map,
			'ColorMap' => $color_map,
		);
		$children = $product->getChildren();
		//debugga($children);exit;
		if( !okArray($children) && !$data['standard_product_id_value']) return null;
		if( okArray($children) ){
			foreach( $children as $child){
				if( $_data_prod_info ){
					
					if( $_data_prod_info['parent_description'] ){

					}
				}
				if( !$_data_prod_info['parent_description'] ){
					
					$_data_prod_info_child = $this->getProductInfo($child);
					
					if( $_data_prod_info_child['disable_sync'] ) continue;
					if( $_data_prod_info_child['price'] ) $child->amazon_price = $_data_prod_info_child['price'];
				}

				$material_composition = $this->getValue('MaterialComposition',$child);
				$department = $this->getValue('Department',$child);
				$outer_material = $this->getValue('OuterMaterial',$child);
				if( $this->getMappedValue('Department',$department) ){
					$department = $this->getMappedValue('Department',$department);
				}
				if( $this->getMappedValue('OuterMaterial',$outer_material) ){
					$outer_material = $this->getMappedValue('OuterMaterial',$outer_material);
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
					'SKU' => $child->id."_".$child->sku,
					'standard_product_id_value' => $standard_product_id_value,
					'standard_product_id_type' => $standard_product_id_type,
					'VariationTheme' => $this->data['variationTheme'],
					'Brand' => $name_manufacturer,
					'Title' => $child->get('name',$this->_lang),
					'Description' => strip_tags($child->get('description',$this->_lang)),
					'ClothingType' => $this->data['ClothingType'],
					'Department' => $department,
					'MaterialComposition' => $material_composition,
					'OuterMaterial' => $outer_material,
					'ProductTaxCode' => 'A_GEN_TAX',
					'LaunchDate' => date('Y-m-d').'T'.date('H:i:s'),
					'Size' => $size,
					'Color' => $color,
					'SizeMap' => $size_map,
					'ColorMap' => $color_map,
				);
				$data['children'][] = $data2;
			}
			$product->children = $children;
		}
		
		return $data;
	}
	
	function getXmlProduct(&$product=NULL){
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
				$template->output_module('amazon','clothing.xml');
				$xml = ob_get_contents();
				ob_end_clean();
				$feed .= $xml;
			}
		}
		return $feed;

	}



	



	
}




?>