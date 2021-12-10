<?php
use \Marion\Core\Marion;
use ProductFeatures\{ProductFeature,SearchAction,SearchView,ProductFeatureValue};
use Catalogo\Product;
use Marion\Components\WidgetComponent;
function filtri_ricerca_action_delete_product($product){
		$database = Marion::getDB();
		$database->delete('product_feature_association',"id_product={$product->id}");
		
}

function filtri_ricerca_action(&$list_products,&$tot_products,$limit,$offset,$orderKey,$orderValue){
		$formdata = _var('formdata');
		if( !okArray($formdata) ){
			$formdata = _formdata();
		}
	

		$search = new SearchAction($limit,$offset,$orderKey,$orderValue);
		
		$data = $search->get();
		
		$tot_products = $data['tot'];			
		$list_products = $data['list'];

		return;
		
}

Marion::add_action('acion_filtri_ricerca','filtri_ricerca_action');


function filtri_ricerca_query_select($query){
	//$query->leftOuterJoin('product_feature_association as t5',"t5.id_product=t1.id");
}

Marion::add_action('catalog_query_select','filtri_ricerca_query_select');


function filtri_ricerca_set_media_ctrl($ctrl){
	if( get_class($ctrl) == 'CatalogoController' ){ 
		if( $ctrl->getAction() == 'section' ){
			$ctrl->registerCSS('//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
			$ctrl->registerJS('plugins/jquery-ui/jquery-ui-1.10.4.min.js','end');

		}
	}
}

Marion::add_action('action_register_media_front','filtri_ricerca_set_media_ctrl');


function filtri_ricerca_sidebar(){

	
	
	$action = _var('action');
	$features = ProductFeature::prepareQuery()->orderBy('orderView')->get();

	$formdata = _var('formdata');


	

	$search = new SearchView();
	$filtri = $search->get();


	

	$widget = new WidgetComponent('filtri_ricerca');



	$config = Marion::getConfig('filtri_ricerca');
	$max_price = $config['price_limit_max'];
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
		$widget->setVar($k,$v);
	}

	$widget->output('features_search_side.htm');

}
Marion::add_action('display_catalog_list_sidebar','filtri_ricerca_sidebar');



class FeaturesTabAdmin extends ProductTabAdminController{
	
	function isEnabled(): bool{
		$id = $this->getID();
		$action = $this->getAction();
		$formdata = $this->getFormdata();		
		if( (!$id && !$formdata['id']) || ( $action == 'add' && $id ) ){
			
			
		}else{
			if( $formdata['parent'] ){
				return false;
			}else{
				$product = Product::withId($id);
				if( is_object($product) && $product->hasParent() ){
					return false;
				}
			}
		}
		return true;
	}

	public function getTitle(): string{
		
		return 'Caratterisitche';
	}

	public function getTag(): string{
		
		return 'feature';
	}


	function setMedia(){
		$this->registerJS('../modules/filtri_ricerca/js/product.js');
	}

	function getContent(){
		
		
		
		
		$id = $this->getID();
		$action = $this->getAction();
		$formdata = $this->getFormdata();
	
		if( $this->isSubmitted() && $this->reloadContent()){
			$this->setVar('reload_js',1);
		}
		
		
		if( (!$id && !$formdata['id']) || ( $action == 'add' && $id ) ){
			$this->setVar('new_product',1);
			
		}else{
			if( $formdata['parent'] ){
				return false;
			}else{
				$product = Product::withId($id);
				if( is_object($product) && $product->hasParent() ){
					return false;
				}
			}
	
			$database = Marion::getDB();
			
			$tosend['features'] = ProductFeature::prepareQuery()->orderBy('orderView')->get();
			
			
			
			if( okArray($formdata) ){
				foreach($formdata['features'] as $k => $v){
					
					if( $v['value'] && (int)$v['value'] != -1){
						$selezionati[] = $v['value'];
					}elseif( (int)$v['value'] == -1 ){

						$selezionati_custom[$k] = $v['other'];
					}
					
				}
				
			}else{

				$select = $database->select('*','product_feature_association',"id_product={$id}");
				if( okArray($select) ){
					foreach($select as $v){
						$selezionati[] = $v['id_feature_value'];
					}
				}
			}
			$tosend['selezionati_custom'] = $selezionati_custom;
			$tosend['selezionati'] = $selezionati;
			

			foreach($tosend as $k => $v){
				$this->setVar($k,$v);

			}
		}
		
		$this->output('tab_product_admin.htm');

		




	}
	

	function checkData(){
		$formdata = $this->getFormdata();
		$features = $formdata['features'];
		$error = 1;
		if( okArray($features) ){
				foreach($features as $k => $v){
					if( (int)$v['value'] == -1 && !trim($v['other']) ){
						
						$obj = ProductFeature::withId($k);
						$error = "Nessun valore specificato per la feature <b>".$obj->get('name')."</b>";
						break;
					}
				}
		}
		return $error;
	}

	function reloadContent():bool{
		return true;
	}


	function process($product=null){
		

		$formdata = $this->getFormdata();
		
		if( $formdata ){
			$features = $formdata['features'];
			
			$database = Marion::getDB();
			$database->delete('product_feature_association',"id_product={$product->id}");
			if( !$product->parent ){
				
				if( okArray($features) ){
					foreach($features as $k => $v){
						if( (int)$v['value'] == -1 && trim($v['other']) ){
							//creo il valore
							
							
							
							$obj = ProductFeatureValue::create();
							$obj->id_product_feature = $k;
							if( isMultilocale()){
								$_locales = Marion::getConfig('locale','supportati');
								foreach($_locales as $lo){
									$obj->setData(array('value' => trim($v['other'])),$lo);
								}
							}else{
								$obj->setData(array('value' => trim($v['other'])),$GLOBALS['activelocale']);
							}
							$res = $obj->save();
							$id_value = $res->getId();
						}else{
							$id_value = $v['value'];
						}
						if($id_value ){
							$toinsert = array(
								'id_feature_value' => $id_value,
								'id_product' => $product->id
							);
							$database->insert('product_feature_association',$toinsert);
						}
					}

				
				}
			}
		}


		
	}
}

Product::registerAdminTab('FeaturesTabAdmin');





function filtri_ricerca_clean_data(&$list){
	
	
	$list['filtri_ricerca'] =
		array(
			'name' => 'Filtri ricerca',
			'entities' => array(
				'features' => 'caratteristiche',
				'feature_values' => 'valori caratteristiche',
			
			),

		);
	
	
	return;
}


function filtri_ricerca_clean_delete_data($module,$values){
	
	if( $module != 'filtri_ricerca'){
		return;
	}
	
	foreach($values as $v){
		switch($v){
			case 'features':
				//codice per eliminare le caratteristiche
				$database = Marion::getDB();
				$database->delete('product_feature_lang');
			
				$database->delete('product_feature');
				$database->delete('product_feature_value_lang');
				$database->delete('product_feature_value');
				$database->execute("ALTER TABLE product_feature AUTO_INCREMENT = 1");
				$database->execute("ALTER TABLE product_feature_value AUTO_INCREMENT = 1");
				$database->delete('product_feature_association');
				break;
			case 'feature_values':
				//codice per eliminare le caratteristiche
				$database = Marion::getDB();
				
				$database->delete('product_feature_value_lang');
				$database->delete('product_feature_value');
				$database->execute("ALTER TABLE product_feature_value AUTO_INCREMENT = 1");
				$database->delete('product_feature_association');
				break;


		}
	}
	
	return;
}


Marion::add_action('action_clean_data','filtri_ricerca_clean_data');
Marion::add_action('action_clean_delete_data','filtri_ricerca_clean_delete_data');



?>