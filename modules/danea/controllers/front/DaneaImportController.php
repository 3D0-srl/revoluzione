<?php
class DaneaImportController extends FrontendController{
	public $id_shop = 1;
	public $id_inventory = 1;
	
	public $enable_import = true;  //abilita l'importazione
	public $create_image_on_finish = false;


	public $path_logs = _MARION_MODULE_DIR_.'danea/logs'; //cartella contentente i logs
	public $dir_upload = _MARION_MODULE_DIR_.'danea/immagini/'; //cartella in cui verranno memorizzate le immagini
	
	public $display_logs = false; // mostra a video i logs

	public $path_xml_products = _MARION_MODULE_DIR_.'danea/xml/articoli.xml'; //cartella contentente il file XML degli articoli. La cartella deve essere scrivibile
	public $type_import = 'full'; //tipologia importazione. 'full' sono stati trasmessi tutti i prodotti; 'update' se sono stati trasmessi solo i prodotti modificati

	public $enable_credentials = true; //abilita l'accesso all'endpoint dell'importazione dei prodotti con delle credenziali
	public $username = '';
	public $password = '';
	
	/* PREZZI E TASSE */
	public $default_tax = 31; //tassa di default da applicare ai prezzi se non presente nell'articolo. Se 0 non viene applicata alcuna tassa
	public $prices_with_tax = true; //stabilisce se i prezzi in fase di importazione sono già tassati

	//mappatura delle tasse di Marion con quelle di Danea, L'array ha come chiave l'id della tassa un Marion e come valore il codice della tassa su Danea
	public $mapping_taxes = array(
		31 => '22',
		2 => '10',
		3 => '4',
	);
	
	public $default_price_list = 1; //listino prezzo di default. I valori ammessi sono 1,2,3,4,5,6,7,8,9

	//Mappatura dei listini di danea in listini di Marion. L'array ha come chiave un valore comprezo tra 1 e 9 metre come valore la label del listino su Marion
	public $mapping_price_list = array(
		2 => 'specialprice',
		3 => 'saldi'
	);

	/* FINE PREZZI E TASSE */


	/* VARIAZIONI */

	public $manage_variations = 1; //abilita la gestione delle variazioni nell'importazione
	public $manage_variations_advanced = 1; //abilita la gestione avanzata delle variazioni. Richiede che il campo "manage_variations" sia abilitato


	//GESTIONE STANDARD
	// mappatura degli attributi dei prodotti con gli attributi Taglia Colore di Danea
	public $mapping_color_set = 'colore'; // Specifica quale è l'insieme attributi per la taglia. da specificare solo se manage_variations_advanced=0
	public $mapping_size_set = 'taglia'; // Specifica quale è l'insieme attributi per il colore. da specificare solo se manage_variations_advanced=0
	public $mapping_size_color_set = 'taglia_colore'; // Specifica quale è l'insieme attributi per la taglia colore. da specificare solo se manage_variations_advanced=0
	
	public $mapping_color = 'colore';
	public $mapping_size = 'size';


	/*
	Stabilisce quale insieme attributi utilizzare per il prodotto se ha variazioni. 
	Se l'insieme attributi ha due variazioni la prima variazione è Color e la seconda è Size. Se l'insieme attributi ha un'unica variazione viene presa la prima variazione non nulla nell'ordine Color, Size
	N.B. l'insieme attributi deve essere già creato sull'ecommerce e il prodotto su Danea deve essere un articolo con magazzino (taglie/colori)

	
	*/
	public $field_attribute_set = 1; // valore compreso tra 1 e 4 che corrispondono rispettivamente ai campi liberi su Danea relativi nella scheda del prodotto
	
	

	
	public $mapping_attributes = array(
		'taglia_colore' => array(
			'taglia' => 'size',
			'colore' => 'color',
		),
		'taglia' => array(
			'taglia' => 'size',
		),
		'lunghezza' => array(
			'lunghezza' => 'size',
		),
		'lettera' => array(
			'lettera' => 'size',
		),
		'colore' => array(
			'colore' => 'color',
		),
	);



	//stabilisce se il codice artcicolo del prodotto figlio deve essere generato in automatico o meno
	public $sku_child_dinamic = true;

	//stabilisce quale è il codice articolo (sku) del prodotto figlio. Viene gestiso solo nel caso in cui $sku_child_dinamic è false
	public $sku_child = 'parent_sku'; // valori ammessi: 'parent_sku', 'barcode'

	/* FINE VARIAZIONI */
	
	public $map_ean_field = ''; //valori ammessi Barcode, Code
	public $map_upc_field = ''; //valori ammessi Barcode, Code
	public $map_description_field = 'DescriptionHTML'; //valori ammessi DescriptionHTML, Notes
	public $map_description_short_field = '';  //valori ammessi DescriptionHTML, Notes

	
	public $create_variations_on_import = true; //crea i valori delle variazioni se non presenti su Marion
	public $create_categories_on_import = true; //crea le categorie se non  presenti su Marion
	public $create_manufacturers_on_import = true; //crea le categorie se non  presenti su Marion
	public $import_images = false; //abilita l'importazione delle immagini
	public $processing_images_in_the_end = false; //se impostato a true le immagini verranno prima inviate tutte e poi processate alla fine


	public $action_on_delete_product = 'disable'; //valori ammessi: '','disable','delete' 

	//campi non aggiornati quando il prodotto viene modificato
	public $disabled_fields_on_update = array(
		'section',
		'description',
		'name',
		//'manufacturer',
		//'immagini',
		//'ean',
		//'upc'

	);

	


	


	//TO DO
	public $mapping_features = array(
		'CustomField1' => 1, //id di una feature (o caratteristica)
		'CustomField2' => 2,
		'CustomField3' => 3,
	);
	



	//VARIABILI AUSILIARI PER MANTENERE IN CACHE ALCUNI VALORI
	public $map_tmp_attribute_values = array(); 
	public $map_tmp_attributes = array();
	public $map_tmp_attribute_sets = array();
	
	
	// Metodo che salva il file di importazione
	function saveFile(){
		$res = false;
		if( $_FILES['file']['tmp_name'] ){
			$res =  move_uploaded_file($_FILES['file']['tmp_name'], $this->path_xml_products);
		}
		return $res;
	}

	// Metodo che controlla le credenziali
	function checkCredentials(){
		if( !$this->enable_credentials ){ 
			return true;
		}else{
			list($username,$password) = explode(':',base64_decode($_SERVER['HTTP_X_AUTHORIZATION']));
			if( $username == $this->username && $this->password == $password ){
				return true;
			}else{
				return false;
			}
			
		}
	}

	// metodo che prende i parametri di importazione
	function getParametersImport(){
		
		$dati = Marion::getConfig('danea_setting');
		$this->enable_import = $dati['enable_import'];
		$this->enable_credentials = $dati['enable_credentials'];
		$this->username = $dati['username'];
		$this->password = $dati['password'];
		
		$this->mapping_attributes = array();
		$this->default_price_list = $dati['default_price_list'];  
		
		 
		$this->map_ean_field = $dati['map_ean_field'];
		$this->map_upc_field = $dati['map_upc_field'];
		$this->map_description_field = $dati['map_description_field'];
		$this->map_description_short_field = $dati['map_description_short_field'];
	


		$this->prices_with_tax = $dati['prices_with_tax'];


		$this->manage_variations = $dati['manage_variations_import'];
		$this->manage_variations_advanced = $dati['manage_variations_import_advanced'];
		
		
		$this->mapping_color_set =  $dati['mapping_color_set'];
		$this->mapping_size_set =  $dati['mapping_size_set'];
		$this->mapping_size_color_set =  $dati['mapping_size_color_set'];
		$this->mapping_color = $dati['mapping_color'];
		$this->mapping_size = $dati['mapping_size'];

		
		
		$this->mapping_price_list = array();
		$mapping_price_list = unserialize($dati['mapping_prices']);
		foreach($mapping_price_list as $k => $v){
			$this->mapping_price_list[$v] = $k;
		}
		$this->default_tax = $dati['default_tax'];
		$this->sku_child_dinamic = $dati['sku_child_dinamic'];
		$this->create_variations_on_import = $dati['create_variations_on_import'];
		$this->create_categories_on_import = $dati['create_categories_on_import'];
		$this->create_manufacturers_on_import = $dati['create_manufacturers_on_import'];
		

		$this->disabled_fields_on_update = unserialize($dati['disabled_fields_on_update']);

		
		$this->mapping_taxes = unserialize($dati['mapping_taxes']);
		$this->import_images = $dati['import_images'];
		$this->action_on_delete_product = $dati['action_on_delete_product'];

		
		$this->mapping_attributes = unserialize($dati['mapping_attribute_sets']);
		$this->field_attribute_set = $dati['field_attribute_set'];
				
		
	}
	
	function saveProductDanea($prod){
		if( is_object($prod) ){
			$database = _obj('Database');
			$toinsert = array(
				'sku' => $prod->sku,
				'id_product' => $prod->id
			);
			$database->insert('danea_product',$toinsert);
		}
	}


	function getDaneaProducts($sku_array = array()){
		$database = _obj('Database');
		$list = $database->select('*','danea_product');
		
		if( okArray($sku_array) ){
			foreach($list as $v){
				if( in_array($v['sku'],$sku_array) ){
					$products[$v['sku']] = $v['id_product'];
				}
			}
		}else{

			foreach($list as $v){
				
				$products[$v['sku']] = $v['id_product'];
			}
		}

		
		
		return $products;
	}
	

	function uploadImage(){

		/*$type_import = _var('type_import');
		$session_id = _var('session_id');
		session_destroy();
		session_id($session_id);
		session_start();
		
		$sku = $_SESSION['danea_images'][$_POST['fileName']];*/
		$file = $this->dir_upload.$_POST['fileName'];
		//debugga($file);exit;
		if (move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
			
			
			echo "OK";
		} else {
		   echo "ERROR";
		}
		exit;
	}

	function createImages(){
		$xml = simplexml_load_file($this->path_xml_products) or die("Error: Cannot create object");
		if( is_object($xml) ){
			$this->type_import = _var('type_import');
			if( $this->type_import == 'full'){
				$products = $xml->Products->Product;
			}else{
				$products = $xml->UpdatedProducts->Product;
			}
			$database = _obj('Database');
			
			$danea_products = $this->getDaneaProducts();
			$where_prod = '';
			foreach($danea_products as $id_prod){
				$where_prod .= "{$id_prod},";
			}
			$where_prod = preg_replace('/\,$/','',$where_prod);
			$list_images = $database->select('id,images','product',"id IN ({$where_prod})");
			
			foreach($list_images as $v ){
				$prod_images[$v['id']] = unserialize($v['images']);
			}

			

			foreach($products as $p){
				$sku = (string)$p->Code;
				$id_prod = $danea_products[$sku];

				if( in_array('immagini', $this->disabled_fields_on_update) && okArray($prod_images[$id_prod])) continue;
				
				$image_name = (string)$p->ImageFileName;
				if( $image_name ){
					$file = $this->dir_upload.$image_name;
					if( file_exists($file) ){
						
						$img = ImageComposed::withFile($file);
						$img->save();
						$id_image = $img->getId();
					}else{
						$id_image = 0;
					}
				}else{
					$id_image = -1; //cancellare l'immagine
				}

				
				if( $id_prod ){
					
					
					
					
					if( $id_image ){
						if( $id_image == -1 ){
							$images = array();
						}else{
							$images = array($id_image);
						}
						$database->update('product',"id={$id_prod}",array('images'=>serialize($images)));
					}

				}
			}
		}

		echo 'OK';
		exit;

	}



	function saveInventory($id_prod,$qnt=0){
		$database = _obj('Database');
		$inventory_shop = $database->select('*','product_inventory',"id_product={$id_prod} AND id_inventory={$this->id_inventory}");
		$toupdate = array(
			'id_product' => $id_prod,
			'id_inventory' => $this->id_inventory,
			'quantity' => $qnt

		);
		if( okArray($inventory_shop) ){
			$database->update('product_inventory',"id_product={$id_prod} AND id_inventory={$this->id_inventory}",$toupdate);
		}else{
			$database->insert('product_inventory',$toupdate);
		}
		return true;
	}

	function process(){
		  $dati = $this->parseXML();
		 
		  $_products = $dati['products'];
		  $_deleted_products = $dati['deleted_products'];
		 
		  $check_info_shop = $this->checkExistsTableInfoShop(); //controllo se esiste la tabella relativo alle informazioni dello shop. (Nuova versione Marion)
		  

		  $database = _obj('Database');
		  
		  $danea_products = array(); // variabile che tiene traccia dei prodotti sincronizzati con danea. Restituisce un array chiave-valore dove la chiave è il codice Danea e il valore è l'ID del prodotto

		  if( $this->type_import == 'full'){
			$danea_products = $this->getDaneaProducts();
		  }
		 
		  if( okArray($_products) ){

			  
			  foreach($_products as $data){
				 
				
				 $product = $this->getProduct($data);
				 
				 if( $this->type_import == 'full'){
					unset($danea_products[$data['sku']]);
				 }
				 
				 $child_ids = array();
				 $data_parent = $data;
				 if( $product->id ){
					//update
					
					foreach($this->disabled_fields_on_update as $field){
						unset($data[$field]);
					}
					$children = $product->getChildren();
					
					if( okArray($children) ){
						
						foreach($children as $c){
							$attributes = $c->getAttributes();
							
							ksort($attributes);
							$child_ids[serialize($attributes)] = $c;
						}
							

					
					}
				 }
				 
				 $data['visibility'] = 1;
				 $product->set(
					$data
				 );
				
				
				 $parent = $product->save();
				 $this->saveProductDanea($parent);
				 

				 if( $parent->id ){
					 if( $check_info_shop ){
						$data_shop = array(
							'id_product' => $parent->id,
							'parent_price' => 0,
							'id_tax' => $data['taxCode'],
							'min_order' => $data['minOrder'],
							'id_shop' => 1
						);
						$this->saveInfoShop($data_shop);
					 }

					$this->savePrices($parent->id,$data['prices']);
					$this->saveInventory($parent->id,$data['stock']);
					
					foreach($data['_children'] as $child_data){
						ksort($child_data['attributes']);
						
						$key_child = serialize($child_data['attributes']);
						
						if( $child_ids[$key_child] ){
							$child = $child_ids[$key_child];
							
							
							foreach($this->disabled_fields_on_update as $field){
								unset($child_data[$field]);
							}
							$child->set($child_data);

							$child->setAttributes($child_data['attributes']);
							unset($child_ids[$key_child]);
						}else{
							$child = Product::create();
							$child->set($data_parent);
							$child->parent = $parent->id;
							$child->manufacturer = $parent->manufacturer;
							$child->section = $parent->section;
							$child->type = 1;
							$child->parentPrice = 1;
							$child->set($child_data);
							$child->setAttributes($child_data['attributes']);
							unset($child_data['attributes']);
						}

						
						
						
						$azione = 'insert';
						if( $child->id ){
							$azione = 'update';
						}
						$res2 = $child->save();
						
						
						
						
						if( $res2 && $res2->id ){
							
							if( $azione == 'insert'){
								$this->writeLog($data['sku']." inserta variazione ".$child_data['variation_name']);
							}
							if( $check_info_shop ){
								$data_shop = array(
									'id_product' => $res2->id,
									'parent_price' => 1,
									'id_tax' => $data['taxCode'],
									'min_order' => $data['minOrder'],
									'id_shop' => 1
								);
								$this->saveInfoShop($data_shop);
								$this->saveInventory($res2->id,$child_data['stock']);
							 }
						}else{
							if( is_object($res2) ){
								if( $res2->error_query ){
									$this->writeLog($data['sku']."  ".$res2->error_query);
								}
							}else{
								$this->writeLog($data['sku']."  ".$res2);
							}
							
						}
					}

					foreach($child_ids as $_child){
						$_child->delete();
						$this->writeLog($data['sku']." eliminata variazione");
					}
					
					$this->writeLog($data['sku']." aggiornato");
				 }
			  }
		  }
		  if( $this->type_import == 'update'){
			if( okArray($_deleted_products) ){
				$danea_products = $this->getDaneaProducts($_deleted_products);
			}
		  }
		 
		  if( okArray($danea_products) ){
				foreach($danea_products as $k => $v){
					switch($this->action_on_delete_product){ 
						case 'delete':
							$database->delete('danea_product',"sku='{$k}'");
							$prod = Product::withId($v);
							if( is_object($prod) ){
								$prod->delete();
								
								$this->writeLog($prod->sku." eliminato");
							}
							break;
						case 'disable':
							
							$database->update('product',"id={$v}",array('visibility'=>0));
							
								
							$this->writeLog($prod->sku." messo offline");
							

							break;

					}
					
					
				}
		  }
		  
		  
		  echo "OK\n";
		  if( okArray($_products) && $this->import_images){
			  $_session_id = session_id();
			  echo "ImageSendURL="."http://" . $_SERVER['SERVER_NAME'] ."/index.php?ctrl=DaneaImport&mod=danea&action=upload_image&type_import=".$this->type_import."&session_id=".$_session_id."\n";
			  echo "ImageSendFinishURL=http://". $_SERVER['SERVER_NAME'] ."/index.php?ctrl=DaneaImport&mod=danea&action=create_images&type_import=".$this->type_import;
			 
		  }
		  exit;
	}
	
	function getConfigVariations(){
		
		if( $this->manage_variations ){
			if( !$this->manage_variations_advanced ){
				$this->mapping_attributes = array();
				if( $this->mapping_color_set ){
					
					$this->mapping_attributes[$this->mapping_color_set ][$this->mapping_color] = 'color';
				}
				if( $this->mapping_size_set ){
					$this->mapping_attributes[$this->mapping_size_set][$this->mapping_size] = 'size';
				}

				if( $this->mapping_size_color_set ){
					$this->mapping_attributes[$this->mapping_size_color_set][$this->mapping_color] = 'color';
					$this->mapping_attributes[$this->mapping_size_color_set][$this->mapping_size] = 'size';
				}
			}
		}
	}

	function display(){

		  $this->getParametersImport();
			
		  $this->getConfigVariations();
		
		  if( !$this->enable_import ){
			 echo "IMPORT DISABLED";
			 exit;
		  }
		 


		

		  if( !$this->checkCredentials() ){
				echo 'WRONG CREDENTIALS';
				exit;
		  }
		  
		   $action = $this->getAction();
		

		  switch($action){
			case 'create_images':
				$this->createImages();
				break;
			case 'upload_image':
				$this->uploadImage();
				break;
			default:
				//salvataggio del file
				$this->saveFile();
				//$list = $this->getTypesImport();
				//debugga('qua');exit;
				$this->process();
				break;
		  }
			
		  
		  
		  

		  

	}


	function checkExistsTableInfoShop(){
		$database = _obj('Database');
		$sel = $database->execute("SHOW TABLES LIKE 'product_shop_values'");
		return okArray($sel);
	}

	function saveInfoShop($data){
		$database = _obj('Database');
		$database->delete('product_shop_values',"id_product={$data['id_product']}");
		$database->insert('product_shop_values',$data);

		return true;
		
	}


	function savePrices($id_product,$prices){
		$database = _obj('Database');
		$list = $database->select('*','price',"product={$id_product} AND ((quantity=1 AND userCategory=0) OR label = 'default')");
		
		$old = array();
		if( okArray($list) ){
			foreach($list as $v){
				$old[$v['label']] = $v['id'];
			}
		}
		
		foreach($prices as $k => $v){
			if( $k == 'default' || $v > 0 ){
				$toinsert = array(
					'product' => $id_product,
					'quantity' => 1,
					'userCategory' => 0,
					'label' => $k,
					'value' => $v
				);
				
				$id = $old[$k];
			
				if( $id ){
					$database->update('price',"id={$id}",$toinsert);
					unset($old[$k]);
				}else{
					$database->insert('price',$toinsert);
				}
				
				
			}
			
			
		}
		if( okarray($old) ){
			
			foreach($old as $v){
				$database->delete('price',"id={$v}");
				
			}
	
		}
		

		
	}

	function getProduct($v){
		$product = Product::prepareQuery()
			->where('sku',$v['sku'])
			->where('parent',0)
			->where('deleted',0)
			->getOne();
		if( is_object($product) ){
			return $product;
		}else{
			return Product::create();
		}
	}
	
	

	function getManufacturers(){
		$this->manufacturer_mapping = array();
		$list = Manufacturer::prepareQuery()->get();
		foreach($list as $v){
			$this->manufacturer_mapping[$v->get('name')] = $v->id;
		}
		
	}

	function buildManufacturer(&$dati){
		if( $dati['manufacturer_name'] ){
			if( $this->manufacturer_mapping[$dati['manufacturer_name']] ){
				$dati['manufacturer'] = $this->manufacturer_mapping[$dati['manufacturer_name']];

			}else{
				if( $this->create_manufacturers_on_import ){
					$man = Manufacturer::create()->set(
						array(
							'name' => $dati['manufacturer_name'],
							'visibility' => 1,
						)
					)->save();
					if( is_object($man) ){
						$this->manufacturer_mapping[$dati['manufacturer_name']] = $man->id;
						$dati['manufacturer'] =  $man->id;
					}
				}
			}
		}
		unset($dati['manufacturer_name']);
	}
	

	function parseXML(){
		$xml = simplexml_load_file($this->path_xml_products) or die("Error: Cannot create object");
		
		$this->getManufacturers();

		$list_products = array();
		$list_deleted_products = array();
		if( is_object($xml) ){


			$products = $xml->Products->Product;
			if( !$products ){
				$products = $xml->UpdatedProducts->Product;
				$this->type_import = 'update';
			}

			$deleted_products = $xml->DeletedProducts->Product;

			

			if( $deleted_products ){
				foreach($deleted_products as $p ){
					$list_deleted_products[(string)$p->Code] = (string)$p->Code;
				}
			}
			
			if( $this->prices_with_tax ){
				$percentage_taxes = [];
				$database = _obj('Database');
				$list_taxes = $database = $database->select('*','tax');
				foreach($list_taxes as $v){
					$percentage_taxes[$v['id']] = $v['percentage'];
				}
			}
			
			$mapping_taxes = [];
			foreach($this->mapping_taxes as $id => $code_danea){
				$mapping_taxes[$code_danea] = $id;
			}
			if( !array_key_exists('danea_images',$_SESSION) ) $_SESSION['danea_images'] = array();

			foreach($products as $p ){


				//memorizzo in sessione l'associazione tra codice articolo e immagine
				$_SESSION['danea_images'][(string)$p->Code] = (string)$p->ImageFileName;;
				





				
				$error = '';
				$dati = array(
					'sku' => (string)$p->Code,
					'name' => (string)$p->Description,
					'manufacturer_name' => trim((string)$p->ProducerName),
					'stock' => (int)$p->AvailableQty,
					'minOrder' => (int)$p->MinStock?(int)$p->MinStock:1,
					'virtual_product' => (bool)$p->ManageWarehouse?0:1,
					//'weight' => (int)$p->NetWeight,
					'taxCode' => (int)$mapping_taxes[(string)$p->Vat]?(int)$mapping_taxes[(string)$p->Vat]:$this->default_tax
					
				);
				$categories =  array(trim((string)$p->Category));
				$categories[] = trim((string)$p->Subcategory);
				for( $k = 2; $k <= 9; $k++ ){
					$key_category = "Subcategory".$k;
					$categories[] = trim((string)$p->$key_category);
				}

				$dati['categories'] = $categories;
				
				
				$prezzi = array(
					'price1' => (float)$p->NetPrice1,
					'price2' => (float)$p->NetPrice2,
					'price3' => (float)$p->NetPrice3,
					'price4' => (float)$p->NetPrice4,
					'price5' => (float)$p->NetPrice5,
					'price6' => (float)$p->NetPrice6,
					'price7' => (float)$p->NetPrice7,
					'price8' => (float)$p->NetPrice8,
					'price9' => (float)$p->NetPrice9,
				);
				
				

				$custom = array(
					'CustomField1' => (string)$p->CustomField1,
					'CustomField2' => (string)$p->CustomField2,
					'CustomField3' => (string)$p->CustomField3,
					'CustomField4' => (string)$p->CustomField4,
				);

				

				/*if( $this->prices_with_tax && $dati['taxCode'] ){
					$percentage = (float)$percentage_taxes[$dati['taxCode']];
					if( $percentage ){
						
						foreach($prezzi as $k => $v){
							$price_no_tax = $v - ($v*$percentage/100);
							
							$prezzi[$k] = $price_no_tax;
						}
					}
	
				}*/
				
				
				
				$dati['prices'] = array(
					'default' => $prezzi["price".$this->default_price_list]?$prezzi["price".$this->default_price_list]:0
				);
				
				foreach($this->mapping_price_list as $num => $label_price){
					$dati['prices'][$label_price] = $prezzi["price".$num]?$prezzi["price".$num]:0;
					
				}
				
				if( $this->map_ean_field ){
					$key = $this->map_ean_field;
					$dati['ean'] = (string)$p->$key;
				}
				if( $this->map_upc_field ){
					$key = $this->map_upc_field;
					$dati['upc'] = (string)$p->$key;
				}
				if( $this->map_description_field ){
					$key = $this->map_description_field;
					if( $key == 'Notes' ){
						$dati['description'] = nl2br((string)$p->$key);
					}else{
						$dati['description'] = (string)$p->$key;
					}
				}
				if( $this->map_description_short_field ){
					$key = $this->map_description_short_field;
					if( $key == 'Notes' ){
						$dati['descriptionShort'] = nl2br((string)$p->$key);
					}else{
						$dati['descriptionShort'] = (string)$p->$key;
					}
				}

				if( $this->manage_variations ){
					if( $custom['CustomField'.$this->field_attribute_set] ){
						$dati['attribute_set_label'] = trim($custom['CustomField'.$this->field_attribute_set]);
					}

					
					$type_product = '';
					if( is_object($p->Variants) ){
						foreach($p->Variants->Variant as $v){
							$child_name = '';
							$child = array(
								'size' => (string)$v->Size,
								'color' => (string)$v->Color,
								'quantity' => (int)$v->AvailableQty
							);
							if( $child['color'] == '-' ){
								unset($child['color']);
							}else{
								$child_name .= "color: ".$child['color']." ";
							}
							if( $child['size'] == '-' ){
								unset($child['size']);
							}else{
								
								$child_name .= "size: ".$child['size'];
							}
							$child['variation_name'] = trim($child_name);

							if( $this->map_barcode_field ){
								$child[$this->map_barcode_field] = (string)$v->Barcode;
							}

						

							if( $this->sku_child_dinamic ){
								$child['sku'] = $dati['sku'];
								if( $child['color'] ){
									$child['sku'] .= '-'.$child['color'];
								}
								if($child['size'] ){
									$child['sku'] .= '-'.$child['size'];
								}
							}else{
								switch($this->sku_child){
									case 'parent_sku':
										$child['sku'] = $dati['sku'];
										break;
									case 'barcode':
										$child['sku'] = (string)$v->Barcode;
										break;
								}
							}

							if( $this->map_ean_field ){
								$key = $this->map_ean_field;
								if( $key == 'Code' ){
									$child['ean'] = $child['sku'];
								}else{
									$child['ean'] = (string)$v->$key;
								}
								
							}
							if( $this->map_upc_field ){
								$key = $this->map_upc_field;
								if( $key == 'Code' ){
									$child['upc'] = $child['sku'];
								}else{
									$child['upc'] = (string)$v->$key;
								}
							}
							if( !$this->manage_variations_advanced ){
								if( $child['color'] && $child['size'] ){
									$dati['attribute_set_label'] = $this->mapping_size_color_set;
								}elseif( $child['color'] ){
									$dati['attribute_set_label'] = $this->mapping_color_set;
								}elseif( $child['size'] ){
									$dati['attribute_set_label'] = $this->mapping_size_set;
								}

							}
							$dati['variations'][] = $child;
						}
					}

					
					if( $dati['variations'] ){
						$data_attributes = $this->buildVariations($dati);
						//debugga($data_attributes);exit;
						if( !okArray($data_attributes) ){
							
							echo $dati['sku']." ".$data_attributes;
							$this->writeLog($dati['sku']." ".$data_attributes);
							exit;
					
						}
					
					
						/*if( !okArray($data_attributes) ){
							$error = $data_attributes;
						}*/
						unset($dati['attribute_set_label']);
						unset($dati['variations']);
						if( $data_attributes['attrubute_set_id'] ){
							$dati['attributeSet'] = $data_attributes['attrubute_set_id'];
							if( okArray($data_attributes['children']) ){
								$dati['type'] = 2;
								$dati['_children'] = $data_attributes['children'];
							}
							
						}
					}
				}
				$this->buildCategories($dati);

				
				$this->buildManufacturer($dati);
				$list_products[] = $dati;
				
				
			}
		}
		
		return array(
			'products' => $list_products,
			'deleted_products' => $list_deleted_products
		);
	}
	

	function buildVariations($v){
		if( $v['attribute_set_label'] ){
			$variations = $v['variations'];
			
			$mapping_attributes_tmp = $this->mapping_attributes[$v['attribute_set_label']];
			foreach($mapping_attributes_tmp as $k1 => $v1){
				$mapping_attributes[$v1] = $k1;
			}

			



			if( !$this->map_tmp_attribute_sets[$v['attribute_set_label']] ){
				
				$attributeSet = AttributeSet::withLabel($v['attribute_set_label']);
				if( !is_object($attributeSet) ){ 
					return 'ATTRIBUTE SET NOT EXISTS';
				}
				$composition = $attributeSet->getComposition();
				$this->map_tmp_attribute_sets[$v['attribute_set_label']]['id'] = $attributeSet->id;
				$this->map_tmp_attribute_sets[$v['attribute_set_label']]['composition'] = $composition;
				
			}

			$attributeSet_id = $this->map_tmp_attribute_sets[$v['attribute_set_label']]['id'];
			$composition = $this->map_tmp_attribute_sets[$v['attribute_set_label']]['composition'];
			
			
			
			
			//$this->mappping_attributes
			
			
			
			if( okArray($variations) ){
				

				
				
				$id_attributes = [];

				

				$toreturn['attrubute_set_id'] = $attributeSet_id;

				foreach($variations as $var){
					
					if( $mapping_attributes['size'] ){
						if( !$attribute_size ){
							if( $this->map_tmp_attributes[$mapping_attributes['size']] ){
								$attribute_size = $this->map_tmp_attributes[$mapping_attributes['size']];
							}else{
								$attribute_size = Attribute::withLabel($mapping_attributes['size']);
								$this->map_tmp_attributes[$mapping_attributes['size']] = $attribute_size;
							}
						}
						if( !is_object($attribute_size) ){
							return 'ATTRIBUTE NOT EXISTS';
						}else{
							if( !$id_attributes[$attribute_size->id] ){
								$id_attributes[$attribute_size->id] = $attribute_size->id;
								$_attribute_data[$mapping_attributes['size']]['variation'] = 'size';
								$_attribute_data[$mapping_attributes['size']]['attribute_id'] = $attribute_size->id;
								$_attribute_data[$mapping_attributes['size']]['attribute_label'] = $mapping_attributes['size'];
							}
							$_attribute_data[$mapping_attributes['size']]['values'][$var['size']] = $var['size'];
						}
						
						
						
					}
					if( $mapping_attributes['color'] ){

						if( $this->map_tmp_attributes[$mapping_attributes['color']] ){
							$attribute_color = $this->map_tmp_attributes[$mapping_attributes['color']];
						}else{
							$attribute_color = Attribute::withLabel($mapping_attributes['color']);
							$this->map_tmp_attributes[$mapping_attributes['color']] = $attribute_color;
						}

						
						if( !is_object($attribute_color) ){
							return 'ATTRIBUTE NOT EXISTS';
						}else{
							if( !$id_attributes[$attribute_color->id] ){
								$id_attributes[$attribute_color->id] = $attribute_color->id;
								$_attribute_data[$mapping_attributes['color']]['variation'] = 'color';
								$_attribute_data[$mapping_attributes['color']]['attribute_id'] = $attribute_color->id;
								$_attribute_data[$mapping_attributes['color']]['attribute_label'] = $mapping_attributes['color'];
							}
							$_attribute_data[$mapping_attributes['color']]['values'][$var['color']] = $var['color'];
						}
					}
					
					
					
				}
			}

			
			
			$_check_attribute_set_composition = array_intersect(array_keys($composition),$id_attributes);
			
			if( count($composition) != count($_check_attribute_set_composition) ){ 
				return 'ATTRIBUTE SET COMPOSITION WRONG';
			}

			
			

			//debugga($_attribute_data);exit;
			
			
			
			

	
			
			
			
			foreach($_attribute_data as $k => $v){
				$v['values'] = array_values($v['values']);
				$_attribute_data[$k]['values'] = $v['values'];
				
				if( $this->map_tmp_attributes[$v['attribute_id']] ){
					$_value_id = $this->map_tmp_attributes[$v['attribute_id']];
				}else{
					$_values = AttributeValue::prepareQuery()->where('attribute',$v['attribute_id'])->get();
					$_value_id = array();
					foreach($_values as $_v){
						$_value_id[$_v->get('value')] = $_v->id;
					}
					$this->map_tmp_attributes[$v['attribute_id']] = $_value_id;
				}

				
				

				

				foreach($v['values'] as $_v){
					
					$id_value = $_value_id[$_v];
					if( !$id_value ){
						if( $this->create_variations_on_import ){
							
							$attribute_value = AttributeValue::create();

							$attribute_value->set(
								array(
									'attribute' => $v['attribute_id'],
									'value' => $_v
								)
							);
							$res = $attribute_value->save();
							if( !is_object($res) ){
								return "PROBLEM ON CREATE VARIATION VALUE";
							}
							$this->map_tmp_attributes[$v['attribute_id']][$_v] = $res->id; //metto in cache
							$_attribute_data[$k]['mapping_values'][$_v] = $res->id;
							
							
						}else{
							return 'ATTRIBUTE VALUE NOT EXISTS: '.$_v;
						}
					}else{
						$_attribute_data[$k]['mapping_values'][$_v] = $id_value;
					}
					
				}
			}

			//foreach($_attribute_data)
			
			foreach($_attribute_data as $v){
				$tmp[$v['variation']] =$v;
			}

			$children = array();
			foreach($variations as $v){
				
				$child = array(
					'sku' => $v['sku'],
					'variation_name' => $v['variation_name'],
					'ean' => $v['ean'],
					'upc' => $v['upc'],
					'stock' => $v['quantity'],
					'type' => 1,
					'attributeSet' => $toreturn['attrubute_set_id']
				);
				if( $this->map_barcode_field ){
					$child[$this->map_barcode_field] = $v[$this->map_barcode_field];
				}
				foreach($tmp as $var => $data){
					if( $v[$var] ){
						$child['attributes'][$data['attribute_label']] = $data['mapping_values'][$v[$var]];
					}
				}
				$children[] = $child;
				
			}
			
			$toreturn['children'] = $children;
			

			//debugga($toreturn);exit;
			
			
		}

	
		return $toreturn;
			
	}
	

	function buildCategory($category,$id_parent){
		
		if( $this->category_mapping[$id_parent][$category] ) return $this->category_mapping[$id_parent][$category];
		
		$query = Section::prepareQuery()
				->where('name',$category)
				->where('parent',$id_parent);
		
		$section = $query->getOne();
		//debugga($section);
		
		if( !is_object($section) ){
			if( !$this->create_categories_on_import ) return 0;
			$section = Section::create()
						->set(
							array(
								'name' => $category,
								'prettyUrl' => Marion::slugify($category),
								'parent' => $id_parent
							)
						)->save();
			
		}
		$this->category_mapping[$id_parent][$category] = $section->id;
		return $section->id;
	}

	function buildCategories(&$v){
		
		
		$categories = $v['categories'];
		$id_category = 0;
		foreach($categories as $category){
			if( trim($category) ){
				$id_category = $this->buildCategory($category,$id_category);
				if(!$id_category) break;
				
			}else{
				break;
			}
		}
		unset($v['categories']);
		$v['section'] = $id_category;

		


		return true;
			
	}



	function writeLog($message){
		$date = date('Y-m-d').".log";
		if( $this->display_logs ){
			echo $message."\n\r";
		}
		error_log(date('Y-m-d_H:i')." :: ".print_r($message,true)."\n\r",3,$this->path_logs."/".$date);
		
	}

	
	
	



}



?>