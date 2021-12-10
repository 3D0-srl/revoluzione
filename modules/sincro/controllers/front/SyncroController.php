<?php
class SyncroController extends FrontendController{
	
	public $dir_input_files = 'modules/sincro/ftp/'; //cartella in cui viene letto il file di importazione
	public $dir_images = 'modules/sincro/ftp/images/GEOX/'; //cartella in cui viene letto il file di importazione
	public $old_files_dir = 'modules/sincro/ftp/old/'; //cartella in cui vengono archiviati i file di importazione eseguiti
	public $path_csv = ''; //percorso del file elaborato
	public $delimiter_csv = ';'; //delimitatore dei campi del CSV
	public $path_logs = 'modules/sincro/logs/'; //cartella in cui vengono scritti i logs
	public $lang = 'it';
	public $display_logs = true; //mostra a video le operazioni effettuate

	public $move_file_after_read = false;

	
	public $count_import_images = 0; //contatore di prodotti per cui è stata inserita l'immagine
	public $limit_import_images = 50; //limite massimo di prodotti per cui deve essere importata l'immagine

	
	public $id_tax = 31;

	public $sku_field = 'sku';
	public $parent_sku_field = 'parent_sku';


	public $mapping_fields = array(); //mappatura dei campi del CSV
	public $attribute_fields = array('color','size'); // campi da considerare come attributi per le variazioni
	
	public $attribute_set = 'taglia_colore'; //insieme di attributi da assegnare al prodotto
	public $attribute_set_id = 0;
	public $mapping_attribute_set = array( //mappatura delle varizioni del CSV con quelle di Marion
			'color' => 'colore',
			'size' => 'taglia'
	);


	public $creation_products_flag = true; //questo flag stabilisce se occorre creare i prdootti non presenti su Marion

	public $features_fields = array('composition_material','gender');

	public $mapping_features = array(
		'composition_material' => 'Materiale',
		'gender' => 'Genere'
	);




	public $price_fields = array('price_without_vat','specialprice');
	public $mapping_prices = array(
		'price_without_vat' => array(
			'priceList' => 'default',
			'quantity' => 1,
			'type' => 'price'
		 ),
		 'specialprice' => array(
			'priceList' => 'specialprice',
			'quantity' => 1,
			'type' => 'price'
		 ),
	);

	public $reports = array(
		'type' => '',
		'status' => '',
		'start' => 0,
		'end' => 0,
		'duration' => 0,
		'manufacturers_created' => 0,
		'categories_created' => 0,
		'attribute_values_created' => 0,
		'feature_values_created' => 0,
		'products_created' => 0,
		'products_deleted' => 0,
		'images_created' => 0,
	);
	

	public $data_list = array();
	public $feature_values = array();
	public $categories_tree = array();
	public $manufacturers = array();
	public $products = array();
	public $mapping_manufacturers = array();
	public $mapping_categories = array();
	public $attribute_values = array();
	public $magazzini = array();
	public $mapping_magazzini = array();


	public $old_products = array();
	public $images = array();
	
	function checkParameters(){
		$check = 1;
		
		/*** CONTROLLO ATTRIBUTI ****/
		$set = AttributeSet::withLabel($this->attribute_set);
		if( !$set ){
			
			return 'ATTRIBUTE SET MISSING';
		}
		$this->attribute_set_id = $set->id;
		$composition = $set->getComposition();
		$labels = array();
		foreach($composition as $v){
			$attr = Attribute::withId($v['attribute']);
			if( is_object($attr) ){
				$labels[] = $attr->label;
			}
		}

		$check_attributes = array_intersect($labels,array_values($this->mapping_attribute_set));
		if( count($check_attributes) != count($this->mapping_attribute_set) ){
			return 'ATTRIBUTE SET COMPOSITION WRONG';
		}

		foreach($this->attribute_fields as $v){
			if( !array_key_exists($v,$this->mapping_attribute_set) ){
				return 'MISSING MAPPING FOR ATTRIBUTE '.$v;
			}
		}

		/*** FINE CONTROLLO  ATTRIBUTI ****/

		/*** CONTROLLO CARATTERISTICHE ****/
		require_once('modules/filtri_ricerca/classes/ProductFeature.class.php');
		require_once('modules/filtri_ricerca/classes/ProductFeatureValue.class.php');
		$labels = array();

		foreach($this->mapping_features as $v){
			$feature = ProductFeature::prepareQuery()
				->where('name',$v)
				->where('locale',$this->lang)
				->getOne();
			if( !is_object($feature) ){
				return 'MISSING FETAURE '.$v;
			}
		}

	
		foreach($this->features_fields as $v){
			if( !array_key_exists($v,$this->mapping_features) ){
				return 'MISSING MAPPING FOR FEATURE '.$v;
			}
		}
		

		/*** FINE CONTROLLO CARATTERISTICHE ****/

		
		
		return $check;
	}
	

	function getFile(){
		$list = scandir($this->dir_input_files);
		foreach($list as $v){
			if( $v != '..' &&  $v != '.'){
				$explode = explode('.',$v);
				$ext = strtolower($explode[count($explode)-1]);
				if( $ext == 'csv'){
					$this->path_csv = $this->dir_input_files.$v;
					break;
				}
			}
		}
	}

	function display(){
		if( _var('display') ){
			$this->display_logs = true;
		}
		$this->getFile();
		


		if( !file_exists($this->path_csv) ){
			echo json_encode(
				array(
					'result' => 'ERROR',
					'message' => 'File not found'
				)
			);
			exit;
		}

		

		$this->reports['start'] = time();
		
		
		$check = $this->checkParameters();
		
		if( $check == 1 ) {

			$action = $this->getAction();
			switch($action){
				case 'delete_images':
					exit;
					$database = _obj('Database');
					$prod = $database->select('*','product');
					foreach($prod as $p){
						$images = unserialize($p['images']);
						if( okArray($images) ){
							foreach($images as $i){
								$img = ImageComposed::withId($i);
								if( is_object($img) ){
									$img->delete();
								}
								
							}
						}
						$ser = serialize(array());
						$database->update('product',"id={$p['id']}",array('images' =>$ser));
					}
					debugga('finito');
					exit;
					break;
				case 'images':
					$this->importImages();
					break;
				case 'quantities_and_prices':
					$this->updateQuantitiesAndPrices();
					break;
				default:
					$this->import();
					break;
			}
			
			$this->reports['status'] = 'SUCCESS';
		}else{
			$this->reports['status'] = 'ERROR';
			$this->scriviLog("ERROR: ".$check);
		}



		$this->reports['end'] = time();
		$this->reports['duration'] = $this->reports['end']-$this->reports['start'];


		$this->reportsLog();
		$this->saveReports();

		echo json_encode(
			array(
				'result' => $this->reports['status']
			)
		);
	}

	function importImages(){
		$this->reports['type'] = 'Import images';
		// LETTURA PARAMETRI DI MAPPING
		$this->getMappingFields();
		$required_fields = array(
			'sku','base_image',
		);
		foreach($this->mapping_fields as $k => $v){
			if( !in_array($v['field'],$required_fields) ){
				$this->mapping_fields[$k]['ignore'] = 1;
			}
		}
		//LETTURA DEL FILE CSV
		$this->readCSV();


		$this->prepareProducts();

		
		//LETTURA DELLA CARTELLA DELLE IMMAGINI
		$this->scanImgaesFolder();
		

		//AGGIORNAMENTO PRODOTTI
		$this->buildImages();
		

	}

	function updateQuantitiesAndPrices(){
		$this->reports['type'] = 'Update quantities and prices';
		// LETTURA PARAMETRI DI MAPPING
		$this->getMappingFields();
		$required_fields = array(
			'sku','quantity','price_without_vat','specialprice'
		);
		foreach($this->mapping_fields as $k => $v){
			if( !in_array($v['field'],$required_fields) ){
				$this->mapping_fields[$k]['ignore'] = 1;
			}
		}
		//LETTURA DEL FILE CSV
		$this->readCSV();

		//debugga($this);exit;
		$this->prepareProducts();

		$this->creation_products_flag = false; //disabilito la creazione dei prodotti

		//AGGIORNAMENTO PRODOTTI
		$this->buildProducts();
		

	}


	function import(){
		$this->reports['type'] = 'Full Import';

		// LETTURA PARAMETRI DI MAPPING
		$this->getMappingFields();
		
		//LETTURA DEL FILE CSV
		$this->readCSV();

	
		//CREAZIONE DEI MAGAZZINI
		$this->buildMagazzini();

		//CREAZIONI DELLE CATEGORIE
		$this->buildCategories();

		//CREAZIONI DELLE CATEGORIE
		$this->buildManufacturers();

		//CREAZIONI DEI VALORI DEGLI ATTRIBUTI
		$this->buildAttributeValues();

		//CREAZIONI DEI VALORI DELLE CARATTERISTICHE
		$this->buildFeatureValues();
	
		//PREPARAZIONE DEI DATI DEI PRODODTTI
		$this->prepareProducts();	
		
		//CREAZIONE PRODOTTI
		$this->buildProducts();

		//CANCELLAZIONE PRODOTTI NON SINCRONIZZATI
		$this->deleteProducts();
	}


	function readCSV(){
		$this->scriviLog('inizio lettura CSV');
		
		$_data_list = array(); 
		
		if (($handle = fopen($this->path_csv, "r")) !== FALSE) {
		  $testata =  fgetcsv($handle, 5000, $this->delimiter_csv);
		   
		  while (($data = fgetcsv($handle, 5000, $this->delimiter_csv)) !== FALSE) {
			$row = array();
			foreach($data as $k => $v){
				if( !array_key_exists($k,$this->mapping_fields) ) continue;
				$field = $this->mapping_fields[$k];
				//debugga($data);exit;
				if( !$field || $field['ignore'] ) continue;
				if( $field['prefunction'] ){
					
					$function = $field['prefunction'];
					
					if( method_exists($this,$function) ){
						if( $field['row'] ){
							$v = $this->$function($v,$data);
						}else{
							$v = $this->$function($v);
						}
					}elseif( function_exists($function) ){
						if( $field['row'] ){
							$v = $function($v,$data);
						}else{
							$v = $function($v);
						}
					}
				}
				$row[$field['field']] = $v;
				if( in_array($field['field'],$this->attribute_fields )){
					if( $v ){
						$this->attribute_values[$this->mapping_attribute_set[$field['field']]][$v]['value'] = $v;
					}
				}

				if( in_array($field['field'],$this->features_fields )){
					if( $v ){
						$this->feature_values[$this->mapping_features[$field['field']]][$v]['value'] = $v;
					}
				}
			}

		
			$this->data_list[] = $row;
			
		  }
		  fclose($handle);
		}
		
		
		$this->scriviLog('fine lettura CSV');
		$this->moveFile();

	}

	//questo metodo crea i valori degli attributi se non presenti nel database
	function buildMagazzini(){
		$this->scriviLog('inizio creazione magazzini');
		
		foreach($this->magazzini as $v){
			require_once('modules/sincro/classes/Magazzino.class.php');
			$obj = Magazzino::prepareQuery()
				->where('etichetta',$v['name'])
				->getOne();
			if( !is_object($obj) ){
				$obj = Magazzino::create()->set(
					array(
						'etichetta' => $v['name'],
						'nome' => $v['name'],
					)
				)->save();
				
				$this->reports['magazzini_created'] += 1;
			}
			
			$this->mapping_magazzini[$v['name']] = $obj->id;
			
		

			
		}
		
		$this->scriviLog('fine creazione magazzini');
		return true;

	}


	//questo metodo crea i valori degli attributi se non presenti nel database
	function buildAttributeValues(){
		$this->scriviLog('inizio creazione attributi');
		
		foreach($this->mapping_attribute_set as $attribute){
			$attr = Attribute::withLabel($attribute);
			$values = AttributeValue::prepareQuery()->where('attribute',$attr->id)->get();
			foreach($values as $v){
				$this->attribute_values[$attr->label][$v->get('value',$this->lang)]['id'] = $v->id;
			}
			foreach($this->attribute_values[$attr->label] as $v){
				if( !array_key_exists('id',$v) ){
					$new = AttributeValue::create();
					$new->set(
						array(
							'attribute' => $attr->id,
							'value' => $v['value']
						)
					);
					$new->save();
					$this->reports['attribute_values_created'] += 1;
					$this->attribute_values[$attr->label][$v['value']]['id'] = $new->id;
				}
			}

			
		}
		$this->scriviLog('fine creazione attributi');
		return true;

	}

	//questo metodo crea i valori delle caratteristiche se non presenti nel database
	function buildFeatureValues(){
		foreach($this->mapping_features as $name){
			$feature = ProductFeature::prepareQuery()->where('name',$name)->getOne();
			if( is_object($feature) ){
				$values = $feature->getValues();
					
				if( okArray($values) ){
					foreach($values as $v){
						$this->feature_values[$name][$v->get('value',$this->lang)]['id'] = $v->id;
					}

				}
				
			}
			$feature_values = $this->feature_values[$name];
			foreach($feature_values as $k => $v){
				if( !$v['id'] ){
					$_val = ProductFeatureValue::create()
						->set(
							array(
								'value' => $v['value'],
								'id_product_feature' => $feature->id,
							)
						)->save();
					$this->feature_values[$name][$k]['id'] = $_val->id;
				}
			}

			
		}
		
	}


	function buildManufacturers(){
		$this->scriviLog('inizio creazione produttori');
		foreach($this->manufacturers as $v){
			$obj = Manufacturer::prepareQuery()
				->where('locale',$this->lang)
				->where('name',$v['name'])
				->getOne();
			if( !is_object($obj) ){
				$obj = Manufacturer::create()->set(
					array(
						'name' => $v['name'],
						'visibility' => 1
					)
				)->save();
				$this->reports['manufacturers_created'] += 1;
			}
			
			$this->mapping_manufacturers[$v['name']] = $obj->id;
			
		}
		$this->scriviLog('fine creazione produttori');
	}

	//questo metodo crea le categorie se non presenti
	function buildCategories(){
		$this->scriviLog('inizio creazione categorie');
		foreach($this->categories_tree as $v){
			$this->buildCategory($v);
		}
		$this->scriviLog('fine creazione categorie');

	}
	
	function buildCategory($v,$parent_id=0,$key=''){
		$obj = Section::prepareQuery()->where('name',$v['name'])
			->where('locale',$this->lang)
			->where('parent',$parent_id)
			->getOne();
		if( !is_object($obj) ){
			$obj = Section::create()->set(
				array(
					'name' => $v['name'],
					'parent' => $parent_id,
					'urlType' => 1,
				)
			)->save();
			$this->reports['categories_created'] += 1;
		}
		
		if( array_key_exists('children',$v) ){
			$key .= $v['name'].">>"; 
			foreach($v['children'] as $v){
				$this->buildCategory($v,$obj->id,$key);
			}
		}else{
			$key .= $v['name']; 
			$this->mapping_categories[$key] = $obj->id;
		}
	}


	function prepareProducts(){
		$database = _obj('Database');
		$select = $database->select('id,sku,images','product',"deleted = 0");

		
		
		$products_id = array();
		$products_images = array();
		foreach($select as $v){
			$products_id[$v['sku']] = $v['id'];
			$products_images[$v['id']] = unserialize($v['images']);
		}

		$this->old_products = $products_id;

		foreach($this->data_list as $k => $v){
			
			$v['urlType'] = 1;

			$sku = $v['sku'];
			$explode_sku = explode('-',$sku);
			$v['parent_sku'] = $explode_sku[0];

			
			
			if( okarray($this->mapping_magazzini) ){
				$v['id_magazzino'] = $this->mapping_magazzini[$v['magazzino']];
			}else{
				$v['id_magazzino'] = 1;
			}
			unset($v['magazzino']);
			
			if( okArray($this->mapping_categories) ){
			
				$v['section'] = $this->mapping_categories[$v['categories']];
			}else{
				switch($v['gender']){
					case 'WOMEN':
						$v['section'] = 3;
						break;
					case 'MEN':
						$v['section'] = 2;
						break;
				}
			}

			
			
			if( okArray($this->attribute_values) ){
				unset($v['categories']);
				$v['attributeSet'] = $this->attribute_set_id;
				if( $v['parent_sku'] ){
					
					foreach($this->mapping_attribute_set as $k1 => $v1){
						$attr_value = $v[$k1];
						
						$attr_name = $v1;
						$v['attributes'][$attr_name] = $this->attribute_values[$attr_name][$attr_value]['id'];
						
						unset($v[$k1]);
					}
				}
				if( okArray($v['attributes']) ){
					$v['attributeSet'] = $this->attribute_set_id;
				}
			}

			if( okArray($this->feature_values) ){

			
			
				foreach($this->mapping_features as $k1 => $v1){

					$feature_value = $v[$k1];
					$id_feature_value = $this->feature_values[$v1][$feature_value]['id'];
					
					$v['feature_values'][] = $id_feature_value;
					unset($v[$k1]);
				}
			}
			if( $this->getAction() != 'images'){
				foreach( $this->mapping_prices as $k1 => $v1){
					$price_value = $v[$k1];
					$price_list = $v1;
					$price_list['value'] = $price_value;
					$v['prices'][] = $price_list;
					unset($v[$k1]);


				}
			}

			if( okArray($this->mapping_manufacturers) ){

				$v['manufacturer'] = $this->mapping_manufacturers[$v['manufacturer_name']];
				unset($v['manufacturer_name']);
			}
			if( $v['parent_sku'] ){
				
				$v['id'] = $products_id[$v['sku']];
				$v['old_images'] = $products_images[$v['id']];
				
				$this->products[$v['parent_sku']]['children'][] = $v;

				if( !isset($products[$v['parent_sku']]['parent']) ){
					unset($v['attributes']);
					
					$v['sku'] = $v['parent_sku'];
					
					unset($v['parent_sku']);
					unset($v['old_images']);
					$v['id'] = $products_id[$v['sku']];
					$v['old_images'] = $products_images[$v['id']];
					$this->products[$v['sku']]['parent'] = $v;
				}
			}else{
				$v['id'] = $products_id[$v['sku']];
				$v['old_images'] = $products_images[$v['id']];
				$this->products[$v['sku']]['parent'] = $v;
			}
			
			
			

			
			
			
		}

		

		

	}


	function insertProductData($data){
		$database = _obj('Database');
		$database->insert('product_shop_values',
				array(
					'id_product' => $data['id'],
					'min_order' => 1,
					'parent_price' => 0,
					'id_tax' => $this->id_tax
					)
				);
		$database->insert('sincro_magazzino_prodotto',
					array(
						'id_product' => $data['id'],
						'id_magazzino' => $data['id_magazzino']
						)
					);
				
	}

	function updateParentPrices($id_product,$prices = array()){

		
		foreach($prices as $label => $value){

			$query = Price::prepareQuery()
							->where('label', $label)
							->where('quantity', 1)
							->where('product',$id_product);
			$price = $query->getOne();
			
			if( !is_object($price) ){
				if( $label != 'default' && $value == 0) continue; // se il prezzo non è quello di default ed è null non lo creo
				$price = Price::create()->set(
								array(
								'label' => $label,
								'quantity' => 1,
								'product' => $id_product,
								'value' => $value,
								'type' => 'price'
								)
							)->save();
			}else{
				if( $label != 'default' && $value == 0){
					$price->delete();
				}else{
					$price->set(
						array(
							'value' => $value
							)
					)->save();
				}

			}

		}
		
	}


	function buildProducts(){
		$database = _obj('Database');
		$this->scriviLog('inizio creazione prodotti');
		
		$flag_insert_parent = false;

		$old_quantity = array();
		$inventory = $database->select('*','product_inventory',"id_inventory=1");
		foreach($inventory as $v){
			$old_quantity[$v['id_product']] = $v['quantity'];
		}
		
		
		foreach($this->products as $sku => $data){

			
			$parent_prices = array();
			$quantity_parent = 0;
			$parent = $data['parent'];
			
			unset($this->old_products[$sku]);
			
			//CREAZIONE PRODOTTO
			if( !$parent['id'] && $this->creation_products_flag ){
				$flag_insert_parent = true;
				
				$parent['type'] = 2;
				$parent_obj = Product::create()->set($parent);
				
				$parent_obj->save();
				
				$parent['id'] = $parent_obj->id;
				if( $parent['id'] ){
					$this->insertProductData($parent);
					
					if( okArray($parent['feature_values']) ){
						foreach($parent['feature_values'] as $f){
							$database->insert('product_feature_association',
								array(
									'id_product' => $parent_obj->id,
									'id_feature_value' => $f
								)
							);
						}
					}
					$this->reports['products_created'] += 1;
				}
			}
			
			if( $parent['id'] ){
				$children = $data['children'];
				foreach($children as $child){
					$flag_insert_child = false;
					unset($this->old_products[$child['sku']]);
					$quantity_parent += (int)$child['quantity'];
					//CREAZIONE VARIAZIONE PRODOTTO
					if( !$child['id'] && $this->creation_products_flag ){
						$flag_insert_child = true;
						$child['type'] = 1;
						$child['parent'] = $parent['id'];
						$obj = Product::create()
							->set($child);
						$obj->setAttributes($child['attributes']);

						
						$obj->save();
						$child['id'] = $obj->id;
						if( $child['id'] ){
							$this->insertProductData($child);
							$this->reports['products_created'] += 1;
						}
					}
					if( $child['id'] ){
						if( !$flag_insert_child ){
							if( (int)$child['quantity'] != $old_quantity[$obj->id] ){

								$database->update('product_inventory',
									"id_product={$child['id']}",
									array(
										'quantity' => (int)$child['quantity'],
										)
									);
							}
							
						}else{
							
							$database->insert('product_inventory',
							array(
								'id_product' => $obj->id,
								'quantity' => (int)$child['quantity'],
								'id_inventory' => 1
								)
							);
							
						}
						//salvo i prezzi dei prodotti 
						$this->saveProductChildrenPrices($child['id'],$child['prices'],$parent_prices);
					}
					
					
				}
				if( $flag_insert_parent ){
					$database->insert('product_inventory',
						array(
							'id_product' => $parent['id'],
							'quantity' => (int)$quantity_parent,
							'id_inventory' => 1
							)
						);
				}else{
					if( $quantity_parent != $old_quantity[$parent['id']] ){
						$database->update('product_inventory',
							"id_product={$parent['id']}",
							array(
								'quantity' => (int)$quantity_parent,
								)
							);
					}

				}
				
				
				$this->updateParentPrices($parent['id'],$parent_prices);

				/*$query = Price::prepareQuery()
							->where('label', 'default')
							->where('quantity', 1)
							->where('product',$parent['id']);
				$parent_price_obj = $query->getOne();
				
				if( !is_object($parent_price_obj) ){
					$parent_price_obj = Price::create()->set(
									array(
									'label' => 'default',
									'quantity' => 1,
									'product' => $parent['id'],
									'value' => $price_parent_default
									)
								)->save();
				}else{
					$parent_price_obj->set(
						array(
							'value' => $price_parent_default
							)
					)->save();

				}
				

				$query = Price::prepareQuery()
							->where('label', 'specialprice')
							->where('quantity', 1)
							->where('product',$parent['id']);
				$parent_special_price_obj = $query->getOne();
				
				if( !is_object($parent_special_price_obj) ){
					$parent_special_price_obj = Price::create()->set(
									array(
									'label' => 'specialprice',
									'quantity' => 1,
									'product' => $parent['id'],
									'value' => $special_price_parent
									)
								)->save();
				}else{
					$parent_special_price_obj->set(
						array(
							'value' => $special_price_parent
							)
					)->save();

				}*/

	
			}

			

			
			//debugga($parent_price_obj);exit;
		

			
			
		}
		

		Catalog::loadPrices();
		
		$this->scriviLog('fine creazione prodotti');
	}

	function scanImgaesFolder(){
		$list = scandir($this->dir_images );
		foreach($list as $v){
			
			if( preg_match('/\(/',$v) ){
				continue;
			}
			if( preg_match_all('/(jpeg)/',strtolower($v),$matches) ){
				if(count($matches) > 1){
					//debugga($v);
				}
			}
			if( $v != '.' && $v != '..' && !is_dir($this->dir_images.$v)){

				$explode = explode('.',$v);
				$name = $explode[0];
				$ext = $explode[1];
				

				if( preg_match('/_/',$name) ){
					$explode = explode('_',$name);
					$base = $explode[0];
					$num = $explode[1];

				}else{
					$base = $name;
					$num = 0;
				}

				$this->images[$base][$num] = array(
					'fullname' => $v,
					'name' => $name,
					'ext' => $ext,
					'path' => $this->dir_images.$v,
				);
				
			}
		}
		

		
	}

	function getImages($key){
		return $this->images[$key];
	}


	function createImages($list = array()){

		$toreturn = array();
		if( okArray($list) ){
			foreach($list as $v){
				$image = ImageComposed::withFile($v['path']);
				$image->save();
				$id = $image->getId();
				if( $id ){
					$toreturn[] = $id;
					$this->reports['images_created'] += 1;
					
				}
			}
		}
		return $toreturn;
	}

	function processImages($data=array()){
		$images_exists = false;
		if( !okarray($data['old_images']) ){
			
			$images = $this->getImages($data['base_image']);
			if( okArray($images) ){
				$created_images = $this->createImages($images);
				if( okArray($created_images) ){
					$database = _obj('Database');
					$database->update('product',"id={$data['id']}",array('images'=>serialize($created_images)));
					$images_exists = true;

					$this->scriviLog('Importate immagini di '.$data['sku']);

					$this->count_import_images++;
				}
				
			}
		}else{
			$images_exists = true;
		}

		return $images_exists;

	}

	function buildImages(){
		$this->scriviLog('inizio creazione immagini');
		$database = _obj('Database');

		
		foreach($this->products as $p){
			
			$parent = $p['parent'];
			$children = $p['children'];
			foreach($children as $v){
				if( $v['id'] ){
					if( $this->limit_import_images && $this->count_import_images > $this->limit_import_images ) break;
					$exists_images = $this->processImages($v);
					if( $exists_images ){
						//se esistono le immagini per questo prodotto imposto l'immagine base per il padre
						$parent['base_image'] = $v['base_image'];
						
					}
				}
				
			}
			if(  $this->limit_import_images && $this->count_import_images > $this->limit_import_images ) break;
			if( $parent['id'] ){
				$this->processImages($parent);
			}
		}

		$this->scriviLog('fine creazione immagini');
	}



	function deleteProducts(){
		$this->scriviLog('inizio eliminazione prodotti');
		$database = _obj('Database');
		if( okArray($this->old_products) ){
			$where_deleted = '';
			foreach($this->old_products as $v){
				$this->reports['products_deleted'] += 1;
				$where_deleted .= $v.",";
			}
			$where_deleted = preg_replace('/,$/',"",$where_deleted);
			$database->update('product',"id IN ({$where})",array('deleted' => 1));
		}
		$this->scriviLog('inizio eliminazione prodotti');
	}


	function saveProductChildrenPrices($id_product,$prices=array(),&$parent_prices=array()){
		foreach($prices as $price){
			$price_obj = Price::prepareQuery()
				->where('label', $price['priceList'])
				->where('quantity', $price['quantity'])
				->where('product',$id_product)
				->getOne();
			if( array_key_exists($price['priceList'],$parent_prices) ) {
				$parent_prices[$price['priceList']] = 0;
			}
			if( $price['value'] > 0 ){

				if( $parent_prices[$price['priceList']] == 0 ){
					$parent_prices[$price['priceList']] = $price['value'];
				}else{
					$parent_prices[$price['priceList']] = min($parent_prices[$price['priceList']],$price['value']);
				}
				
				
				
				
				if( !is_object($price_obj) ){
					$price_obj = Price::create()->set(
						array(
						'label' => $price['priceList'],
						'quantity' => $price['quantity'],
						'product' => $id_product,
						'value' => $price['value']
						)
					)->save();
				}else{
					$price_obj->set(
						array(
							'value' => $price['value']
							)
					)->save();
				}
			}else{

				if( is_object($price_obj) ){
					$price_obj->delete();
				}

			}
		}
					
	}

	/***** FUNZIONI PER IL PARSING DEL FILE CSV *****/
	function getMappingFields(){
		$this->mapping_fields = array(
			0 => array(
				'field' => 'ean',
				'prefunction' => 'cleanData',
				'ignore' => 1
			),
			1 => array(
				'field' => 'sku',
				'prefunction' => 'cleanData',
				'ignore' => 0
			),
			2 => array(
				'field' => 'base_image',
				'prefunction' => 'cleanData',
				'ignore' => 0
			),
			3 => array(
				'field' => 'manufacturer_name',
				'prefunction' => 'cleanManucaturer',
				'ignore' => 0
			),
			4 => array(
				'field' => 'color',
				'prefunction' => 'cleanVariation',
				'ignore' => 0
			),
			5 => array(
				'field' => 'name',
				'prefunction' => 'cleanData',
				'ignore' => 0
			),
			6 => array(
				'field' => 'description',
				'prefunction' => 'cleanData',
				'ignore' => 0
			),
			7 => array(
				'field' => 'composition_material',
				'prefunction' => 'cleanVariation',
				'ignore' => 0
			),
			8 => array(
				'field' => 'dimension',
				'prefunction' => 'cleanVariation',
				'ignore' => 0
			),
			/*9 => array(
				'field' => 'categories',
				'prefunction' => 'splitCategories',
				'ignore' => 0,
				'row' => 1
			),*/
			9 => array(
				'field' => 'gender',
				'prefunction' => 'cleanGender',
				'ignore' => 0
			),
			10 => array(
				'field' => 'size',
				'prefunction' => 'cleanVariation',
				'ignore' => 0
			),
			11 => array(
				'field' => 'quantity',
				'prefunction' => 'cleanData',
				'ignore' => 0
			),
			12 => array(
				'field' => 'C20-WEB_SIZE',
				'prefunction' => 'cleanData',
				'ignore' => 1
			),
			13 => array(
				'field' => 'price_without_vat',
				'prefunction' => 'formatPrice',
				'ignore' => 0
			),
			14 => array(
				'field' => 'price',
				'prefunction' => 'formatPrice',
				'ignore' => 0
			),
			/*15 => array(
				'field' => 'image1',
				'prefunction' => 'cleanData',
				'ignore' => 0
			),
			17 => array(
				'field' => 'image2',
				'prefunction' => 'cleanData',
				'ignore' => 0
			),
			18 => array(
				'field' => 'image3',
				'prefunction' => 'cleanData',
				'ignore' => 0
			),
			19 => array(
				'field' => 'image4',
				'prefunction' => 'cleanData',
				'ignore' => 0
			),
			20 => array(
				'field' => 'image5',
				'prefunction' => 'cleanData',
				'ignore' => 0
			),*/
			15 => array(
				'field' => 'specialprice',
				'prefunction' => 'formatPrice',
				'ignore' => 0
			),
			16 => array(
				'field' => 'magazzino',
				'prefunction' => 'cleanMagazzino',
				'ignore' => 0
			),
		);
	}

	function splitCategories($data,$row){
	
		$parent = $this->cleanData($row[10]);
		if( $parent == 'WOMEN'){
			$parent = 'DONNA';
		}else{
			$parent = 'UOMO';
		}
		$list = array('-1' => $parent);
		$explode = explode('>>',$data);
		
		$key_category = $parent.'>>';
		foreach($explode as $k => $v){
			if( $k <= 0 ){ 
				unset($explode[$k]);
				continue;
			}
			$val = strtoupper($this->cleanData($v));
			$list[$k] = $val;
			$key_category .= $val.">>";
		}
		$key_category = preg_replace('/>>$/','',$key_category);
		
		$list = array_values($list);
		
		
		$this->setCategories($list);
		return $key_category;
	}

	function cleanManucaturer($data){
		$data = $this->cleanData($data);
		if( $data ){
			$this->manufacturers[$data]['name'] = $data;
		}
		return $data;
	}

	function cleanMagazzino($data){
		$data = $this->cleanData($data);
		if( $data ){
			$this->magazzini[$data]['name'] = $data;
		}
		return $data;
	}

	function setCategories($list){
		$tmp = array();
		foreach($list as $level => $v){
			if( !$v) break;
			switch($level){
				case 0:
					$this->categories_tree[$v]['name'] = $v;
					break;
				case 1:
					$this->categories_tree[$tmp[0]]['children'][$v]['name'] = $v;
					break;
				case 2:
					$this->categories_tree[$tmp[0]]['children'][$tmp[1]]['children'][$v]['name'] = $v;
					break;
				case 3:
					$this->categories_tree[$tmp[0]]['children'][$tmp[1]]['children'][$tmp[2]]['children'][$v]['name'] = $v;
					break;

				case 3:
					$this->categories_tree[$tmp[0]]['children'][$tmp[1]]['children'][$tmp[2]]['children'][$tmp[3]]['children'][$v]['name'] = $v;
					break;
			}


			$tmp[] = $v;
		}
	
		

	}
	

	function cleanData($data){
		return utf8_encode(trim($data));
	}

	function cleanVariation($data){
		return utf8_encode(strtoupper(trim($data)));
	}


	function formatPrice($data){
		$data = $this->cleanData($data);
		if( $data ){
			$data = preg_replace('/\./','',$data);
			$data = preg_replace('/\,/','.',$data);
		}
		return (float)$data;
	}



	function scriviLog($message){
		$date = date('Y-m-d').".log";
		$hour = strftime('%H:%M:%S',time());
		
		error_log("[{$hour}]:: ".$message."\n",3,$this->path_logs.$date);
		if( $this->display_logs ){
			debugga("[{$hour}]:: ".$message);
		}
	}


	function reportsLog(){
		$date = date('Y-m-d').".log";
		$table = '';
		$table .= str_pad('REPORTS',102,"*",STR_PAD_BOTH )."<br>";
		$table .= "<br>";
		$table .= "------------------------------------------------------------------------------------------------------<br>";

		error_log("\n",3,$this->path_logs.$date);
		error_log(str_pad('REPORTS',102,"*",STR_PAD_BOTH )."\n",3,$this->path_logs.$date);
		error_log("\n",3,$this->path_logs.$date);
		error_log("------------------------------------------------------------------------------------------------------\n",3,$this->path_logs.$date);

		$db_data = array();
		foreach($this->reports as $k => $v){
			switch($k){
				case 'end':
				case 'start':
					$v = strftime('%H:%M:%S',$v);
					break;
				case 'duration':
					$v = round(abs($v) / 60,2). " minuti";
					break;
			}
			
			
			$key = strtoupper(preg_replace('/_/',' ',$k));
			$table .= "|".str_pad($key,50," ",STR_PAD_RIGHT)."| ".str_pad($v,50," ",STR_PAD_RIGHT)."|<br>";
			error_log("|".str_pad($key,50," ",STR_PAD_RIGHT)."| ".str_pad($v,50," ",STR_PAD_RIGHT)."|\n",3,$this->path_logs.$date);
		}
		error_log("------------------------------------------------------------------------------------------------------\n",3,$this->path_logs.$date);

		error_log("\n",3,$this->path_logs.$date);
		error_log(str_pad('REPORTS',102,"*",STR_PAD_BOTH )."\n",3,$this->path_logs.$date);
		error_log("\n",3,$this->path_logs.$date);

		$table .= "------------------------------------------------------------------------------------------------------<br>";
		$table .= "<br>";
		$table .= str_pad('REPORTS',102,"*",STR_PAD_BOTH )."<br>";
		if( $this->display_logs ){
			debugga($table);
		}
		
	}


	function saveReports(){
		$database = _obj('Database');
		$toinsert = array(
			'type' => $this->reports['type'],
			'start' => date('Y-m-d H:i:s',$this->reports['start']),
			'end' => date('Y-m-d H:i:s',$this->reports['end']),
			'duration' => round(abs($this->reports['duration']) / 60,2),
			'reports' => serialize($this->reports)
		);
		$database->insert('sincro_reports',$toinsert);
		
	}


	function moveFile(){
		if( $this->move_file_after_read ){
			$file = strftime('%Y-%m-%d_%H-%M',time()).".csv";
			
			rename($this->path_csv,$this->old_files_dir.$file);
		}
	}

}

?>