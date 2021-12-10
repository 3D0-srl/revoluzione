<?php
use Marion\Components\PageComposerComponent;
$GLOBALS['use_image_product_for_filter_attribute'] = array('colore');
/*function product_feature_tab(){
	
	$action = _var('action');
	$formdata = _var('formdata');
	if( !okArray($formdata) ){
		$formdata = _formdata();
	}
	$id = _var('id');
	if( $action == 'add_child' || $formdata['parent'] ){
		return false;
	}else{
		$product = Product::withId($id);
		if( is_object($product) && $product->hasParent() ){
			return false;
		}
	}
	$html = "<li><a href='#product_features' data-toggle='tab'>Features</a></li>";
	return $html;
	
}
Marion::add_widget('form_prodotto.htm','product_feature_tab','tab_product','admin',1,'append');
Marion::add_widget('form_prodotto_multilocale.htm','product_feature_tab','tab_product','admin',1,'append');
*/

/*function product_feature_tab_content(){
	

	$action = _var('action');
	$formdata = _var('formdata');

	if( !okArray($formdata) ){
		$formdata = _formdata();
	}
	
	$id = _var('id');
	if( $action == 'add_child' || $formdata['parent'] ){
		return false;
	}else{
		$product = Product::withId($id);
		if( is_object($product) && $product->hasParent() ){
			return false;
		}
	}

	require_once('classes/ProductFeature.class.php');
	require_once('classes/ProductFeatureValue.class.php');

	
	$database = _obj('Database');
	
	$features = ProductFeature::prepareQuery()->orderBy('orderView')->get();
	$module_dir = 'filtri_ricerca';
	$widget = Marion::widget($module_dir);
	$widget->features = $features;
	
	
	$formdata = _var('formdata');
	if( okArray($formdata) ){
		foreach($formdata['features'] as $k => $v){
			
			if( $v['value'] && (int)$v['value'] != -1){
				$widget->selezionati[] = $v['value'];
			}elseif( (int)$v['value'] == -1 ){

				$widget->selezionati_custom[$k] = $v['other'];
			}
			
		}
		
	}else{

		$select = $database->select('*','product_feature_association',"id_product={$id}");
		if( okArray($select) ){
			foreach($select as $v){
				$widget->selezionati[] = $v['id_feature_value'];
			}
		}
	}
	ob_start();
	$widget->output('tab_product.htm');
	$html = ob_get_contents();
	ob_end_clean();
	//debugga($html);exit;
	return $html;
	
}

Marion::add_widget('form_prodotto.htm','product_feature_tab_content','tab_product_content','admin',1,'append');
Marion::add_widget('form_prodotto_multilocale.htm','product_feature_tab_content','tab_product_content','admin',1,'append');

*/


function filtri_ricerca_top(){

	
	require_once('classes/ProductFeature.class.php');
	require_once('classes/ProductFeatureValue.class.php');
	
	$action = _var('action');
	$features = ProductFeature::prepareQuery()->orderBy('orderView')->get();

	$formdata = _var('formdata');
	switch($action){
		case 'section':
			$database = _obj('Database');
			$section_id = _var('section');
			
			$sezioni = filtri_ricerca_get_section_children($section_id);
			
			foreach($sezioni as $t){
				$where .= "{$t},";
			}
			$where = "(".preg_replace('/\,$/',')',$where);

			$features = $database->select('pfv.*,l.value','(product_feature_association as pfc join product as p on p.id=pfc.id_product) join (product_feature_value as pfv join product_feature_value_lang as l on l.id_product_feature_value=pfv.id) on pfv.id=pfc.id_feature_value',"p.section IN {$where} AND l.lang='it' order by pfv.orderView");
			
			if( okArray($features) ){
				foreach($features as $v){

					
					 $option = array(
						'value' => $v['id'],
						'text' => $v['value'],
					);
					if( okArray($formdata) ){
						if( in_array($v['id'],$formdata['filtri']['feature']) ){
							$option['selected'] = 1;
						}

					}

					$values[$v['id_product_feature']][$v['id']] = $option;
				}

				

				foreach($values as $id_feature => $values){
					$feature = ProductFeature::withId($id_feature);
					
					if( is_object($feature) ){
						
						$filtri[] = array(
							'type' => 'feature',
							'name' => $feature->get('name'),
							'values' => $values,
							'order' => $feature->orderView,
						);

						
					
					
					}
				}
				uasort($filtri,function($a, $b){
					if ($a['order'] == $b['order']) {
						return 0;
					}
					return ($a['order'] < $b['order']) ? -1 : 1;
				});
			}
			
			

			$attributi = $database->select('distinct a.value,att.id as attribute_id,p.images','(product as p join productAttribute as a on a.product=p.id) join attribute as att on att.label = a.attribute',"(p.section IN {$where} OR p.id IN (select product from otherSectionsProduct where section IN {$where})) and p.deleted=0 and p.visibility=1");
			
			
			if( okArray($attributi) ){
				foreach($attributi as $k => $v){
					$attributi2[$v['attribute_id']][] = $v['value'];
					
					$images = unserialize($v['images']);
					$images_child[$v['value']] = $images[0];
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

							if( in_array($attributo->label,$GLOBALS['use_image_product_for_filter_attribute'])){
								$option['img'] = $images_child[$v];
								$option['resize'] = 'th';
							}

							if( $option['img'] ){
								$array_attr[$attr_id]['images'] = true;
							}
							if( in_array($v,$formdata['filtri']['attributes']) ){
								$option['selected'] = 1;
							}
							$array_attr[$attr_id]['values'][$v] = $option;
						}
						
					}
					foreach( $array_attr as $k => $v){
						uasort($array_attr[$k]['valori'],function($a, $b){
							if ($a['order'] == $b['order']) {
								return 0;
							}
							return ($a['order'] < $b['order']) ? -1 : 1;
						});
					}
					
					
				}
				
			}

			foreach($array_attr as $k => $v){
				$filtri[] = $v;
			}




			
			break;
	}
	


	
	$module_dir = 'filtri_ricerca';
	$widget = Marion::widget($module_dir);
	$widget->filtri = $filtri;
	$widget->action = $action;
	$widget->section = _var('section');
	$widget->tag = _var('tag');
	

	
	$widget->output('features_search_top.htm');
}


/*
function filtri_ricerca_side(){

	
	require_once('classes/ProductFeature.class.php');
	require_once('classes/ProductFeatureValue.class.php');
	
	$action = _var('action');
	$features = ProductFeature::prepareQuery()->orderBy('orderView')->get();

	$formdata = _var('formdata');
	switch($action){
		case 'section':
			$database = _obj('Database');
			$section_id = _var('section');
			
			$sezioni = filtri_ricerca_get_section_children($section_id);
			
			foreach($sezioni as $t){
				$where .= "{$t},";
			}
			$where = "(".preg_replace('/\,$/',')',$where);

			$features = $database->select('pfv.*,l.value','(product_feature_association as pfc join product as p on p.id=pfc.id_product) join (product_feature_value as pfv join product_feature_value_lang as l on l.id_product_feature_value=pfv.id) on pfv.id=pfc.id_feature_value',"p.section IN {$where} AND l.lang='it' order by pfv.orderView");
			
			if( okArray($features) ){
				foreach($features as $v){

					
					 $option = array(
						'value' => $v['id'],
						'text' => $v['value'],
					);
					if( okArray($formdata) ){
						if( in_array($v['id'],$formdata['filtri']['feature']) ){
							$option['selected'] = 1;
						}

					}

					$values[$v['id_product_feature']][$v['id']] = $option;
				}

				

				foreach($values as $id_feature => $values){
					$feature = ProductFeature::withId($id_feature);
					
					if( is_object($feature) ){
						
						$filtri[] = array(
							'type' => 'feature',
							'name' => $feature->get('name'),
							'values' => $values,
							'order' => $feature->orderView,
						);

						
					
					
					}
				}
				uasort($filtri,function($a, $b){
					if ($a['order'] == $b['order']) {
						return 0;
					}
					return ($a['order'] < $b['order']) ? -1 : 1;
				});
			}
			
			

			$attributi = $database->select('distinct a.value,att.id as attribute_id,p.images','(product as p join productAttribute as a on a.product=p.id) join attribute as att on att.label = a.attribute',"(p.section IN {$where} OR p.id IN (select product from otherSectionsProduct where section IN {$where})) and p.deleted=0 and p.visibility=1");
			
			
			if( okArray($attributi) ){
				foreach($attributi as $k => $v){
					$attributi2[$v['attribute_id']][] = $v['value'];
					
					$images = unserialize($v['images']);
					$images_child[$v['value']] = $images[0];
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

							if( in_array($attributo->label,$GLOBALS['use_image_product_for_filter_attribute'])){
								$option['img'] = $images_child[$v];
								$option['resize'] = 'th';
							}

							if( $option['img'] ){
								$array_attr[$attr_id]['images'] = true;
							}
							if( in_array($v,$formdata['filtri']['attributes']) ){
								$option['selected'] = 1;
							}
							$array_attr[$attr_id]['values'][$v] = $option;
						}
						
					}
					foreach( $array_attr as $k => $v){
						uasort($array_attr[$k]['valori'],function($a, $b){
							if ($a['order'] == $b['order']) {
								return 0;
							}
							return ($a['order'] < $b['order']) ? -1 : 1;
						});
					}
					
					
				}
				
			}

			foreach($array_attr as $k => $v){
				$filtri[] = $v;
			}




			
			break;
	}
	


	
	$module_dir = 'filtri_ricerca';
	$widget = Marion::widget($module_dir);
	$widget->filtri = $filtri;
	$widget->action = $action;
	$widget->section = _var('section');
	$widget->tag = _var('tag');
	

	
	$widget->output('features_search_side.htm');
}
*/


class WidgetFiltriRicercaSide extends  PageComposerComponent{
	
	public $template_html = 'filtri_ricerca_side.htm'; //html del widget
	

	function registerJS($data = null){
		/*
			se il widget necessita di un file js allora occorre registralo in questo modo
			
			PageComposer::registerJS("url del file"); // viene caricato alla fine della pagina
			PageComposer::registerJS("url del file",'head'); // viene caricato nel head 
			

		*/
		PageComposer::registerJS("plugins/jquery-ui/jquery-ui-1.10.4.min.js",'head');
		PageComposer::registerJS("modules/filtri_ricerca/js/filtri_ricerca_side.js",'end');
	}
	function registerCSS($data = null){
		/*
			se il widget necessita di un file css allora occorre registralo in questo modo
			
			PageComposer::registerCSS("url del file"); 
			

		*/
		PageComposer::registerCSS("//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css");
		
		PageComposer::registerCSS("modules/filtri_ricerca/css/filtri_ricerca_side.css");
	}

	function build($data = null){
			
			/*$parameters: parametri di configurazione del widget
			  Questo array contiene i parametri di configurazione del widget
			*/
			$parameters = $this->getParameters();


			/*
				INSERISCI IL CODICE DEL WIDGET



		
			*/
			

		

			require_once('classes/ProductFeature.class.php');
			require_once('classes/ProductFeatureValue.class.php');
				
			$action = _var('action');
			$features = ProductFeature::prepareQuery()->orderBy('orderView')->get();

			$formdata = _var('formdata');
			

			require_once('classes/SearchView.class.php');

			$search = new SearchView();
			$filtri = $search->get();
			$max_price = 500;
			$step_price = 10;
			$params = array(
				'maxprice' => $max_price,
				'filtri' => $filtri,
				'action' => $action,
				'section' => _var('section'),
				'tag' => _var('tag'),
				'price_min' => 0,
				'price_max' => $max_price,
				'step_price' => $step_price


			);

			if( $formdata['price_min'] ){
				$params['price_min'] = $formdata['price_min'];
			}

			if( $formdata['price_max'] ){
				$params['price_max']= $formdata['price_max'];
			}

			foreach($params as $k => $v){
				$this->setVar($k,$v);
			}
			$this->output($this->template_html);

	}

}

?>