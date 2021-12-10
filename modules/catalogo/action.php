<?php
use \Marion\Core\Marion;
use Marion\Entities\Cms\Interfaces\MenuItemFrontendInterface;
use Marion\Entities\Cms\LinkMenuFrontend;

function catalogo_register_twig_templates_dir(&$direcories=array()){
	$direcories[] = _MARION_MODULE_DIR_."catalogo/templates_twig";
	return;
}

Marion::add_action('action_register_twig_templates_dir','catalogo_register_twig_templates_dir');



function catalogo_clean_data(&$list){
	
	
	
	$list['catalogo'] =
		array(
		'name' => 'Catalogo',
		'entities' => array(
			'products' => 'Prodotti',
			'attribute_sets' => 'Insieme Attributi',
			'attributes' => 'Attributi',
			'attribute_values' => 'Valori attributi',
			'categories' => 'Categorie',
			'manufactures' => 'Produttori/Brands',
			'tags' => 'Tag prodotti',
			),
		);
	return;
}


function catalogo_clean_delete_data($module,$values){
	
	if( $module != 'catalogo'){
		return;
	}
	$database = _obj('Database');
	foreach($values as $v){
		switch($v){
			case 'products':
				$database = _obj('Database');
				$select_images = $database->select('images','product');
				foreach($select_images as $v){
					$list = unserialize($v['images']);
					if( okArray($list) ){
						foreach($list as $v1){
							$images[] = $v1;
						}
					}
					
				}
				
				foreach($images as $v){
					$image = ImageComposed::withId($v);
					if( is_object($image) ){
						
						$image->delete();
						
					}


					
				}
				
				

				$database->delete('productLocale');
				$database->delete('productAttribute');
				$database->delete('product_inventory');
				$database->delete('productRelated');
				$database->delete('productRelatedSection');
				$database->delete('productTagComposition');
				$database->delete('product_shop_values');
				$database->delete('otherSectionsProduct');
				$database->delete('product');
				$database->execute("ALTER TABLE product AUTO_INCREMENT = 1");

				break;
			case 'attribute_values':
				$database->delete('attributeValueLocale');
				$database->delete('attributeValue');
				break;
			case 'attribute':
				$database->delete('attributeValueLocale');
				$database->delete('attributeValue');
				$database->execute("ALTER TABLE attributeValue AUTO_INCREMENT = 1");
				break;
			case 'attribute_sets':
				$database->delete('attributeSet');
				$database->delete('attributeSetComposition');
				$database->execute("ALTER TABLE attributeSet AUTO_INCREMENT = 1");
				
				break;
			case 'categories':
				//codice per eliminare le categorie
				$database->delete('sectionRelated');
				$database->delete('sectionLocale');
				$database->delete('productRelatedSection');
				$database->delete('otherSectionsProduct');
				$database->delete('section');
				$database->execute("ALTER TABLE section AUTO_INCREMENT = 1");
				break;
			case 'manufactures':
				//codice per eliminare i produttori
				$database->delete('productTagComposition');
				$database->delete('manufacturerLocale');
				$database->delete('manufacturer');
				$database->execute("ALTER TABLE manufacturer AUTO_INCREMENT = 1");
				

				break;
			case 'tags':
				//codice per eliminare i tags
				$database->delete('tagProductLocale');
				$database->delete('tagProduct');
				$database->execute("ALTER TABLE tagProduct AUTO_INCREMENT = 1");
				break;
		}
	}

	return;
}


Marion::add_action('action_clean_data','catalogo_clean_data');
Marion::add_action('action_clean_delete_data','catalogo_clean_delete_data');



class CatalogoCategoryMenuFrontend implements MenuItemFrontendInterface{
	
	public static function getGroupName(): string{
		 return 'Catalogo/Categorie';
	}


	public static function getUrl(array $params):string{
		$locale = $params['locale'];
		$id = $params['value'];
		
		$section = Section::withId($id);
		
		if( is_object($section) ){
			return $section->getUrl($locale);
		}
		return '';
	}
	
	public static function getPages():array{
		
		$sezioni = Section::getAll('it');

		
		
		return $sezioni;
	}


}

LinkMenuFrontend::registerItem('CatalogoCategoryMenuFrontend');

class CatalogoTagMenuFrontend implements MenuItemFrontendInterface{
	
	public static function getGroupName(): string{
		 return 'Catalogo/Tag prodotti';
	}


	public static function getUrl(array $params):string{
		$locale = $params['locale'];
		$id = $params['value'];
		
		$tag = TagProduct::withId($id);
		
		if( is_object($tag) ){
			return _MARION_BASE_URL_.'catalog/tag/'.$tag->get('label').".htm";
		}
		return '';
	}
	
	public static function getPages():array{
		
		$toreturn = array();
		$list = TagProduct::prepareQuery()->get();
		foreach($list as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		
		
		return $toreturn;
	}


}

LinkMenuFrontend::registerItem('CatalogoTagMenuFrontend');
?>