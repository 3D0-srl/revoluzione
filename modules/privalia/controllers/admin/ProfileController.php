<?php
class ProfileController extends AdminModuleController{
		private $product_fields = array(
			'name' => 'Nome',
			'description' => 'Descrizione',
			'descriptionShort' => 'Descrizione breve',
			'category_name' => 'Nome categoria',
			'ean' => 'EAN',
			'upc' => 'UPC',
			'sku' => 'SKU',
			'parent_sku' => 'SKU (padre)',
			'weight' => 'Peso',
			'stock' => "Quanità",
			'price' => 'Prezzo base con Iva',
			'price_whitout_vat' => 'Prezzo base senza Iva',
			'price_offer' => 'Prezzo con sconti',
			'manufacturer' => 'Brand',
			//'is_child' => 'Flag variazione (stabilisce se è un prodotto figlio)',
			'image1' => 'Immagine 1',
			'image2' => 'Immagine 2',
			'image3' => 'Immagine 3',
			'image4' => 'Immagine 4',
			'image5' => 'Immagine 5',
			'image6' => 'Immagine 6',
			'image7' => 'Immagine 7',
			'image8' => 'Immagine 8',
			'image9' => 'Immagine 9',

		);
		private $manage_route = false;
		private $route = 'Outlet';


		private $lang = '';

		//tracciato del file
		private $privalia_fields = array();
	
		
		function getList(){

			$database = _obj('Database');
		
			
			$condizione = "1=1 AND ";
			$limit = $this->getListOption('per_page');
			
			if( $name = _var('name') ){
				$condizione .= "name LIKE '%{$name}%' AND ";
			}

			if( $id = _var('id') ){
				$condizione .= "id = {$id} AND ";
			}

			if( $lang = _var('language') ){
				$condizione .= "lang = '{$lang}' AND ";
			}
			$condizione = preg_replace('/AND $/','',$condizione);
			

			$tot = $database->select('count(*) as tot','privalia_profile',$condizione);

			
			

			if( $order = _var('orderBy') ){
				$order_type = _var('orderType');
				$condizione .= " ORDER BY {$order} {$order_type}";
			}


			$condizione .= " LIMIT {$limit}";
			if( $page_id = _var('pageID') ){
				$condizione .= " OFFSET ".(($page_id-1)*$limit);
				
			}

			
			

			$list = $database->select('id,name,lang','privalia_profile',$condizione);
			
			
			$this->setListOption('total_items',$tot[0]['tot']);
			$this->setDataList($list);

		}

		function displayList(){
			$langs = Marion::getConfig('locale','supportati');
			$languages = array('' => '--select--');
			foreach($langs as $v){
				$languages[$v] = $v;
			}
		
			$fields = array(
				0 => array(
					'name' => 'ID',
					'field_value' => 'id',
					'searchable' => true,
					'sortable' => true,
					'sort_id' => 'id',
					'search_name' => 'id',
					'search_value' => '',
					'search_type' => 'input',
				),
				1 => array(
					'name' => 'Nome',
					'field_value' => 'name',
					'function_type' => 'value',
					'function' => 'strtoupper',
					'sortable' => true,
					'sort_id' => 'name',
					'searchable' => true,
					'search_name' => 'name',
					'search_value' => _var('name'),
					'search_type' => 'input',
				),
				2 => array(
					'name' => 'Lingua',
					'field_value' => 'lang',
					'function_type' => 'value',
					'function' => 'strtoupper',
					'sortable' => true,
					'sort_id' => 'lang',
					'searchable' => true,
					'search_name' => 'language',
					'search_value' => _var('language'),
					'search_type' => 'select',
					'search_options' => $languages,
				),
				

			);
			$buttons = $this->getListOption('buttons');
			$tmp = $buttons['right_side']['add'];
			unset($buttons['right_side']);
			$buttons['right_side']['home'] = array(
				
					'text' => 'Torna alla Home',
                    'icon_type' => 'icon',
                    'icon' => 'fa fa-home',
                    'url' => 'index.php?mod=privalia',
                    'class' => 'btn btn-info',
			);
			$buttons['right_side']['add']  = $tmp;

			
			
			$this->setListOption('buttons',$buttons);
			
			$this->setListOption('title','Profili di vendita');
			$this->setListOption('fields',$fields);
			$this->getList();
			parent::displayList();
		}



		function displayForm(){
				
				$action = $this->getAction();
				

				if( $this->isSubmitted()){
					$formdata = $this->getFormdata();
					$database = _obj('Database');
					$id = $database->insert('privalia_profile',$formdata);
					header('Location: index.php?ctrl=Profile&mod=privalia&action=edit&new=1&id='.$id);
					exit;
				}
				$this->setVar('manage_route',$this->manage_route);
				

				$id = _var('id');
				$database = _obj('Database');
				$dati = $database->select('*','privalia_profile',"id={$id}");
				
				if( okArray($dati) ){
					if( $action == 'duplicate' ){
						$data = $dati[0];

						unset($data['id']);
						$data['name'] = $data['name']." copy";

						
						$id = $database->insert('privalia_profile',$data);
						
						header('Location: index.php?ctrl=Profile&mod=privalia&action=edit&new=1&id='.$id);
						exit;
					}


					$conf = unserialize($dati[0]['configuration']);
					$lang = $dati[0]['lang'];
					$this->lang = $lang;
					$this->getPrivaliaCategories($lang);
					$this->setVar('conf',json_encode($conf));
					$this->setVar('name',$dati[0]['name']);
					$this->setVar('taxonomy',$dati[0]['taxonomy']);
				}
				
				$this->setVar('id',$id);
				if( $this->manage_route ){
					$this->getRouteValues();
				}else{
					
					$this->getPrivaliaTaxonomies();
					if( $dati[0]['taxonomy'] ){
						
						$this->setVar('mappatura',$this->getTaxonomyAttributes($dati[0]['taxonomy']));
					}

				}
				$this->setVar('market',$lang);
				$this->loadMarionValues();
				
				
				if( $action == 'add'){
					$langs = Marion::getConfig('locale','supportati');
					$this->setVar('langs',$langs);
					
					$this->output('form_new_profile.htm');
				}else{
					if( $this->manage_route ){
						$this->output('form_profile.htm');
					}else{
						$this->output('form_dinamic_profile.htm');
					}
				}

		}


		function loadMarionValues(){
			require_once(_MARION_MODULE_DIR_."filtri_ricerca/classes/ProductFeature.class.php");
			require_once(_MARION_MODULE_DIR_."filtri_ricerca/classes/ProductFeatureValue.class.php");
			
			$filtri = ProductFeature::prepareQuery()->get();
			$dati_filtri = array();
			foreach($filtri as $k => $v){
				$values = $v->getValues();
				//$filtri[$k]->values = $values;
				foreach($values as $v1){
					$dati_filtri[$v->id]['id'] = $v->id;
					$dati_filtri[$v->id]['values'][$v1->id] = $v1->get('value',$this->lang);
				}
			}
			$dati_attributi = array();
			$attributi = Attribute::prepareQuery()->get();
			foreach($attributi as $v){
				$values = $v->getValues();
				foreach($values as $v1){
					$dati_attributi[$v->id]['id'] = $v->id;
					$dati_attributi[$v->id]['values'][$v1->id] = $v1->get('value',$this->lang);
				}
			}
			
			$this->setVar('dati_attributi',json_encode($dati_attributi));
			$this->setVar('dati_filtri',json_encode($dati_filtri));
			
			$this->setVar('privalia_fields',$this->privalia_fields);
			$this->setVar('filtri',$filtri);
			$this->setVar('attributi',$attributi);
			$this->setVar('product_fields',$this->product_fields);
		}

		function getPrivaliaCategories($lang){
			$database = _obj('Database');
			$list = $database->select('*','privalia_taxonomy');
			$privalia_categories = array();
			foreach($list as $item){
				$path = unserialize($item['path']);
				$name = unserialize($item['name']);
				$privalia_categories[$item['code']] = $path[$lang];
			}
			
			$this->setVar('privalia_categories',$privalia_categories);
			

			
			
		}


		function getTaxonomyAttributes($id){

			$ignore_fields = array('tax_rate_percentage','is_variation','category');
			$database = _obj('Database');
			$list = $database->select('*','privalia_taxonomy_attribute',"category_code={$id}");

			
			$fields = array();
			foreach($list as $k => $v){
				if( in_array($v['code'],$ignore_fields) ) $fields[$v['code']]['ignore'] = 1;

				$fields[$v['code']]['id'] = preg_replace('/\s/','',$v['code']);
				$fields[$v['code']]['description'] = unserialize($v['description'])[$this->lang];
				$fields[$v['code']]['name'] = unserialize($v['label'])[$this->lang];
				$fields[$v['code']]['required'] = $v['required'];
				$fields[$v['code']]['recommended'] = $v['recommended'];
				
				if( $v['values_list'] ){
					$dati = $database->select('*','privalia_taxonomy_attribute_value',"code='{$v['values_list']}'");
					if( okArray($dati) ){
					
						$valori = unserialize($dati[0]['valori'])	;
						foreach($valori as $k1=> $v1){
							$fields[$v['code']]['values'][$v1['id']] = $v1['value_'.$this->lang];
						}
						
					}
				}

				
			}
			
			
			
			function cmp($a, $b)
			{
				return strcmp($a["name"], $b["name"]);
			}

			uasort($fields, "cmp");

			function cmp2($a, $b)
			{
				 if ($a['required'] == $b['required']) {
					return 0;
				}
				return ($a['required'] > $b['required']) ? -1 : 1;
			}

			uasort($fields, "cmp2");

			
			$this->privalia_fields = $fields;
			$this->loadMarionValues();
			$this->setVar('privalia_fields',$this->privalia_fields);
			ob_start();
			
			$this->output('mapping.htm');
			$html = ob_get_contents();
			ob_end_clean();
			
			return $html;
			
		}
		
		function getPrivaliaTaxonomies(){
			$database = _obj('Database');
			$list = $database->select('*','privalia_taxonomy');
			$cat = array();
			foreach($list as $v){
				$name = unserialize($v['path']);
				$cat[$v['code']] = $name[$this->lang];
			}
			$this->setvar('privalia_categories',$cat);
			
		}


		

		/* ALTR FUNZIONI */
		function getRouteValues(){

			if( $route = $this->route ){
				require_once(_MARION_MODULE_DIR_."privalia/tracciati/".$this->route.'.php');
				$obj = new $route();
				$this->privalia_fields = $obj->getFields();
				
			}
			
			foreach($this->privalia_fields as $k => $v){
				$this->privalia_fields[$k]['id'] = preg_replace('/\s/','',$k);
				if( array_key_exists('function_values',$v) && $v['function_values'] && method_exists($obj,$v['function_values']) ){
					$function = $v['function_values'];
					$this->privalia_fields[$k]['values'] = $obj->$function($this->lang);
				}
			}
			
		}


		



		function ajax(){
			
			$action = $this->getAction();
			switch($action){
				case 'get_attributes':
					$code = _var('code');
					$this->lang = _var('language');
					$html = $this->getTaxonomyAttributes($code);
					$risposta = array(
						'result' => 'ok',
						'html' => $html
					);
					break;
					

				case 'save_profile':
					$id = _var('id');
					$formdata = $this->getFormdata();
					//debugga($formdata);exit;
					//$this->getTaxonomyAttributes($formdata['taxonomy']);
					$database = _obj('Database');

					$path = '';
					if( $taxonomy = $formdata['taxonomy'] ){
						$dati_taxonomy = $database->select('*','privalia_taxonomy',"code={$formdata['taxonomy']}");
						if( okArray($dati_taxonomy) ){
							$path = unserialize($dati_taxonomy[0]['path'])[_var('language')];
						}
					}

					unset($formdata['taxonomy']);


					$toinsert = array(
						'taxonomy' => $taxonomy,
						'path_taxonomy' => $path,
						'lang' => _var('language'),
						'configuration' => serialize($formdata),
						'name' => _var('name')
					);
					if( $id ){
						$database->update('privalia_profile',"id={$id}",$toinsert);
					}else{
						$id = $database->insert('privalia_profile',$toinsert);
					}
					$risposta = array(
						'result' => 'ok',
						'id' => $id
					);
					break;
			}
			echo json_encode($risposta);
		}




		function delete(){
			$id = $this->getId();
			$database = _obj('Database');
			$database->delete('privalia_profile',"id={$id}");
			parent::delete();
		}
}