<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use Shop\PriceList;
use Catalogo\{Product,AttributeSet,Section,Manufacturer,TagProduct};
use Marion\Entities\UserCategory;
class ProductAdminController extends AdminModuleController{
	public $_auth = 'catalog';
	public $_module_ctrls = array();

	// CARICAMENTO DEI JS E CSS
	function setMedia(){
		
		//if( $this->getAction() != 'list'){
			
			$this->registerJS($this->getBaseUrl().'plugins/jnotify/jNotify.jquery.min.js','end');
			$this->registerJS($this->getBaseUrlBackend().'js/function.js','end');
			$this->registerJS('../modules/catalogo/js/admin/product.js','end');
			$this->registerJS('../modules/catalogo/js/admin/product_related.js','end');
			$this->registerJS($this->getBaseUrl().'plugins/inputmask/dist/jquery.inputmask.bundle.js','head');
			
			$js_head_files = array();
			$js_end_files = array();
			$css_files = array();
			Marion::do_action('product_form_javascript_head',array(&$js_head_files));
			Marion::do_action('product_form_javascript_end',array(&$js_end_files));
			Marion::do_action('product_form_css',array(&$css_files));
			
			if( okArray($js_end_files) ){
				foreach($js_end_files as $v){
					$this->registerJS($v,'end');
				}
			}

			
			if( okArray($js_head_files) ){
				foreach($js_head_files as $v){
					$this->registerJS($v,'head');
				}
			}

			if( okArray($css_files) ){
				foreach($css_files as $v){
					$this->registerCSS($v);
				}
			}
		//}

		$this->setMediaModules();
	}

	//OVERIDE	
	function init($options = Array()){
		parent::init();
		$this->loadModuleControllers();
	}
	function getUrlList(){
		$id = $this->getID();
		$action = $this->getAction();
		if($id){
			$prod = Product::withId($id);
		}

		if( ($id && $action == 'add') || ( isset($prod) && is_object($prod) && $prod->parent) ){
			//prodotto figlio
			if($id && $action == 'add'){
				//caso inserimento prodotto figlio
				$url = $this->getUrlEdit()."&tab=inventory";
			}
			if( is_object($prod) && $prod->parent ){
				//caso modifica prodotto figlio
				$url = $this->getUrlScript()."&action=edit&tab=inventory&id=".$prod->parent;
			}
		}else{
			$url = parent::getUrlList();
		}
		return $url;


	}

	

	function displayForm(){
		$this->setMenu('manage_products');


		/****************INIZIO **************************/
			
		
		//prendo l'action
		$action = $this->getAction();
		
		//prendo l'id se specificato
		$id = $this->getID();
		
		
		
		//prendo la lista delle categorie
		$this->getUserCategories();

		//prendo i listini
		$this->getPriceLists();
		
		

		//di deault mostro la tab delle informazioni generali
		$this->setVar('tabActive','general');
		$tab = _var('tab');
		$new = _var('new');
		$add_children_message = _var('add_children_message'); // se valorizzata a 1 vuol dire che ho appena creato un prodotto padre 

		
		if( $add_children_message ){
			$this->displayMessage('Hai creato un prodotto <b>configurabile</b>! Ora non ti resta che creare le variazioni.');
		}

		$id = _var('id');
		if( $tab ){
			$this->setVar('tabActive',$tab);
		}
		
		
		// se il form non è sottomesso
		if( !$this->isSubmitted() || $new ){
			
			if( _var('new') ){
				$this->setVar('new_product_with_child',true);
			}
		
			$add_child = false;
			if( $action == 'add' && $id){
				$add_child = true;
			}
			
			if( $action == 'add' && !$add_child ){
				createIDform();
			
				if( $new ){
					$dati = $this->getFormdata();
					if( $dati['type'] == 2 ){
						$dati['parent'] = 0;
					}
				}else{
					$dati = array();
				
				}
				/** PARAMATRI DI DEFAULT */
				if(!isset($dati['weight'])) $dati['weight'] = 1000;
				if(!isset($dati['stock'])) $dati['stock'] = 1;
				if(!isset($dati['orderView'])) $dati['orderView'] = 10;
				if(!isset($dati['urlType'])) $dati['urlType'] = 1;
				

				
				
				
				/*$attributeSet = _var('attributeSet');
				$type = _var('type');
				$dati['attributeSet'] = $attributeSet;*/
				
				if( !$dati['type']  ){
					$dataform = $this->getDataForm('nuovo_prodotto',$dati,$this);
					$this->setVar('dataform',$dataform);
					$this->output('catalogo/product/new_product.htm');
					exit;
				}
				//$dati['type'] = $type;

				
				

			}else{
				/*if(!$id){
					//l'id non è stato specificato
					$template->errore_generico(256);
				}*/
				//prendo l'oggetto prodotto
				$prodotto = Product::withId($id);

				/*if(!$prodotto){ 
					//il prodotto non esiste
					$template->errore_generico(255);
				}*/
				
				
				
				//prendo i prodotti correlati
				$this->getRelatesProducts($prodotto);
				
				//prendo i dati del form
				$dati =  $prodotto->prepareForm2();
				
				//prendo la quantità
				$dati['stock'] = $prodotto->getInventory();
				
				//controlo se il prodotto ha un insieme di attributi
				if($dati['attributeSet']){
					$attributeSet = $dati['attributeSet'];
				}
				
				

				switch( $action ){
					case 'duplicate':
						//$this->getPrices($prodotto,$dati);
					case 'add':
						$action = 'add';
						unset($dati['images']);
						unset($dati['id']);
						break;
					case 'edit';
						// se il prodotto è configurabile
						if($prodotto->isConfigurable()){
							
							//prendo le quantità dei figli
							$form_stock = $prodotto->getInventoryChildren();
							if(okArray($form_stock)){
								$this->setVar('form_veloce_stock',$form_stock);
								
							}
							

						}
						break;
				}
			
				

			}	
			if( $add_child ){
				$dati['parent'] = $id;
				$dati['type'] = 1;
				$dati['parentPrice'] = 1;
				unset($dati['images']);
				unset($dati['id']);
			}
			
			if( $action == 'add' ){
				if( $dati['type'] == 2 && !$attributeSet ){
					
					$this->setVar('no_button_variations',true);
				}
			}else{
				if( $prodotto->isConfigurable() && !$attributeSet ){
					$this->setVar('no_button_variations',true);
				}
			}
			
			
			

			$this->getAttributesInput($dati,$prodotto);
			
			$dati['redirect'] = _var('redirect');
			
			
			
			
			
			
			if($action == 'add_child'){
				
				//$elements['formdata[sku]']->attributes['readonly'] = 'readonly';
				
			}

		}else{
			$dati = $this->getFormdata();

			
			$this->process($dati);
			//form sottomesso



		}
		

		if( $dati['parent'] || $action == 'add_child' ){
			
			//prendo le informazioni del prodotto padre
			$parent_product = Product::withId($dati['parent']);
			if( is_object($parent_product) ){
				$this->setVar('parent_product',$parent_product);
			}

			$parent_attributes = Product::getParentFields();
			$this->setVar('parent_attributes',$parent_attributes);
		}
		
			
		$this->addTemplateFunction(
			new \Twig\TwigFunction('tabActive', function ($val1,$val2) {
				if( $val1 == $val2){
					return "active in";
				}
				return '';
			})
		);
		
		
		$dataform = $this->getDataForm('product',$dati);

		$dataform['section']['other'] = array(
			'data-live-search' => "true"
		);
		$dataform['otherSections']['other'] = array(
			'data-live-search' => "true"
		);

		$dataform['related']['other']['onchange'] = 'add_section_related($(this).val()); return false;';
		




		$this->setVar('dataform',$dataform);
		
		



		//RICHIAMO TUTTE LE TAB AGGIUNTE DA MODULI
		$this->getTabModules();
		
		
		
		$this->output('catalogo/product/form.htm');

	}

	


	function process($formdata){
		

		$ajax = _var('ajax_request');
		
		
		

		$action = $this->getAction();
		
		
		
		
		if( okArray($formdata['stock_children']) ){
			foreach($formdata['stock_children'] as $k  => $v){
				if( $v['attributes'] ){
					$formdata['stock_children'][$k]['attributes'] = unserialize($v['attributes']);
				}else{
					$formdata['stock_children'][$k]['name'] = $v['name'];
				}
				
			}
		}
		$this->setVar('form_veloce_stock',$formdata['stock_children']);
		
		
		if( $formdata['type'] == 2 &&  !$formdata['attributeSet']){
			$this->setVar('no_button_variations',true);
		}

		

		
		$this->getAttributesInput($formdata,null,$campi_aggiuntivi);
		
		//boh??
		if( $formdata['type'] == 2){
			if( !$formdata['centralized_stock'] ){
				$campi_aggiuntivi['stock']['obbligatorio'] = 0;
			}
		}
		
		

		//se il prodotto è un prodotto figlio allora rendo non obbligatori dei campi
		if($formdata['parent']){
			$campi_aggiuntivi['offer']['obbligatorio'] = 0;
			$campi_aggiuntivi['sku']['obbligatorio'] = 0;
			$campi_aggiuntivi['ean']['obbligatorio'] = 0;
			$campi_aggiuntivi['section']['obbligatorio'] = 0;
			$campi_aggiuntivi['home']['obbligatorio'] = 0;
			$campi_aggiuntivi['orderView']['obbligatorio'] = 0;
		}else{
			$campi_aggiuntivi['parentPrice']['obbligatorio'] = 0;

			if( $formdata['type'] == 2 ){
				$campi_aggiuntivi['weight']['obbligatorio'] = 0;
			}
		}
			
		//se il prodotto è un prodotto figlio ed è stato impostato il prrezzo del prodotto padre allora
		if($formdata['parent'] && $formdata['parentPrice'] == 1){
			$campi_aggiuntivi['price_default']['obbligatorio'] = 0;
		}elseif($formdata['parent']){
			$campi_aggiuntivi['price_default']['obbligatorio'] = 0;
		}
		

	
		
		//aggiungo i campi di controllo relativi ai moduli
		Marion::do_action('product_form',array(&$campi_aggiuntivi));	

		

		//controllo i dati
		$array = $this->checkDataForm('product',$formdata,$campi_aggiuntivi);
		
		
		
		
		
		
		//controllo i dati sottomessi dai moduli
		$check_modules = $this->checkDataModules();
		
		if( $check_modules != 1 ){
			$array[0] = 'nak';
			$array[1] = $check_modules['error'];
			$array[3] = "tab_".$check_modules['tab'];
		}


		
		// il controllo del form è andato a buon fine
		if($array[0] == 'ok'){
			unset($array[0]);
		

			if($action == 'edit'){
				$product = Product::withId($array['id']);
				//if(!is_object($product)) $template->errore_generico(478);
			}else{
				
				//se il prodotto è un prodotto figlio allora lo copio dal padre
				if($array['parent']){
					$product = Product::withId($array['parent'])->copy();
					//elimino i campi del padre che non sono necessari o che prevedono delle dipendenze
					unset($product->images);
					unset($product->dateInsert);
				}else{
					$product = Product::create();
				}
			}
			
			//setto i dati del prodotto
			$product->set($array);
			
			$product->setAttributes($array);
			
			//setto le sezioni secondarie
			$product->setOtherSections($array['otherSections']);
			
			
			
			
			//salvo il prodotto
			$result = $product->save();
			
			//se il salvataggio non è andato a buon fine
			if(!is_object($result)){
				if( __($result) ){
					$errore = __($result);
				}else{
					$errore = $result;
				}
				if( $ajax ){
					$risposta = array(
						'result' => 'nak',
						'error' => $errore
					);
					echo json_encode($risposta);
					exit;
				}
				
			

				$this->errors[] = $errore;

			}else{
				
				$_parent_fields = Product::getParentFields();
				foreach($_parent_fields as $key_parent){
					$dati_parent[$key_parent] = $result->$key_parent;
				}

				
				//salvo i correlati
				$product->setRelatedSections($formdata['section_related']);
				$product->saveRelatedSections();
				//setto i tag del prodotto
				$product->saveTags($array['tags']);
				//aggiorno la quantità
				
				if( $product->isConfigurable() ){
					$tot = 0;
					//aggiorno le quantita dei figli
					if($formdata['stock_children']){
						foreach($formdata['stock_children'] as $k => $v){

							$child = Product::withId($k);
							if($child){
								if( $v['image'] ){
									$child->images[0] = $v['image'];
								}else{
									unset($child->images[0]);
								}
								$child->images = array_values($child->images);
								$tmp_data_child = $dati_parent;

								
								
								$tmp_data_child['sku'] = $v['sku'];
								$tmp_data_child['ean'] = $v['ean'];
								$tmp_data_child['upc'] = $v['upc'];
								$tmp_data_child['weight'] = (int)$v['weight'];
								$tmp_data_child['minOrder'] = (int)$v['minOrder'];
								$tmp_data_child['maxOrder'] = (int)$v['maxOrder'];
								
								$child->set( $tmp_data_child )->save();
								$child->updateInventory((int)$v['stock']);
							}
						}
					}
					$tot += (int)$v['stock'];
					$product->updateInventory($tot);
				}else{
					$product->updateInventory($array['stock']);
				}
				
					
				//$this->savePrices($result,$array,$prices_data);
				
				$link = '';
				if( $array['redirect'] ){
					$link = $product->getUrl();
				}else{
					if( $action == 'add' && $result->isConfigurable()){
						$link=$this->getUrlScript()."&action=edit&id=".$result->id."&tab=inventory&add_children_message=1";
					}else{
						if($result->hasParent()){
							$link=$this->getUrlScript()."&action=edit&id=".$result->getParentId()."&tab=inventory";
						}else{
							
						}

					}
				}

				//processo le procedure dei moduli
				$this->processModules($result);

				//controllo se occorre ricaricare la pagina in qualche modulo
				if( !$this->checkReloadPageInModules()){

					$module_contents = $this->reloadContentModules();
				}else{
					if( $action == 'edit'){
						$url_redirect = $this->getUrlEdit();
					}else{
						$url_redirect = $this->getUrlEdit()."&id=".$result->id;
					}
					
				}
				
				

				
				if( $ajax ){
					
					$risposta = array(
						'result' => 'ok',
						'redirect' => $url_redirect?$url_redirect:($link?$link:$this->getUrlList()."&saved=1"),
						'modules' => $module_contents,
						'force_redirect'=> $url_redirect?1:0
					);
					if( $array['redirect'] ){
						$risposta['redirect'] = $product->getUrl();
					}
					echo json_encode($risposta);
					exit;
				}
				

				if( $link ){
					header("Location: {$link}");
					exit;
				}else{
					$this->redirectToList(array('saved'=>1));
				}
				
				//$template->link = $link;
				

				
			
				//$template->output('continua.htm');
			}
		}else{
			if( $ajax ){
				$risposta = array(
					'result' => 'nak',
					'error' => $array[1],
					'field' => $array[2],
					'tab' =>  $array[3] ? $array[3] : '',
				);
				echo json_encode($risposta);
				exit;
			}else{
				$this->errors[] = $array[1];
			}
			
		}

	}

	

	/*function displayList(){
		$this->setMenu('manage_products');
		$this->showMessage();


		

		$reset = _var('reset');
	
		

		
		if( $reset ){
			unset($_SESSION['filter_search_products_admin']);
			unset($_SESSION['order_filter_search_products_admin']);
		}
		$dati = _var('formdata');
		
		

		
		
		if( okArray($dati) ){
			$this->setVar('filtering',true);
			$array = $this->checkdataForm('search_product',$dati);
			
			if( $array[0] == 'ok' ){
				$where = $this->getWhereList($dati);
			}
		}
		

		$limit = $this->getLimitList();
		$offset = $this->getOffsetList();

		$order = _var('orderBy');
		$order_value = _var('orderByValue');

		$this->setVar('limit',$limit);
		
		
		
		

		$query = Product::prepareQuery()->where('parent',0)->where('deleted',0);
		if( $where ){
			$query->whereExpression($where);
		}
		if( $limit ){
			$query->limit($limit);
		}

		if( $order ){
			$query->orderBy($order,$order_value);
			
		}
		$query->orderBy('dateInsert','DESC');
		

		if( $offset ){
			$query->offset($offset);
		}
		$database = Marion::getDB();;
		if( $where ){
			$tot = $database->select('count(*) as cont','product',"parent=0 AND deleted = 0 AND {$where}");
		}else{
			$tot = $database->select('count(*) as cont','product',"parent=0 AND deleted = 0");
		}

		$tot = $tot[0]['cont'];
		$pager_links = $this->getPagerList($tot);

		$prodotti = $query->get();
		$sections = Section::getAll();
		$this->setVar('sections',$sections);
		$this->setVar('prodotti',$prodotti);
		$this->setVar('links',$pager_links);
		
		$dataform = $this->getDataForm('search_product',$dati,$this);
		$this->setVar('dataform',$dataform);
		
		
		
		
		
		$this->output('catalogo/product/list.htm');




	}*/

	function getList(){
		$database = Marion::getDB();;
		
		$condizione = "parent = 0 AND (deleted is NULL OR deleted= 0) AND (locale is NULL OR locale = '{$GLOBALS['activelocale']}') AND ";
		
		
		$limit = $this->getListOption('per_page');
		
		if( $sku = _var('sku') ){
			$condizione .= "sku LIKE '%{$sku}%' AND ";
		}

		if( $name = _var('name') ){
			$condizione .= "name LIKE '%{$name}%' AND ";
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		if( $type = _var('type') ){
			$condizione .= "type = {$type} AND ";
		}
		if( $section = _var('section') ){
			$condizione .= "section = {$section} AND ";
		}

		$visibility = _var('visibility');
		if( isset($_GET['visibility']) && $visibility != -1 ){
			$condizione .= "visibility = {$visibility} AND ";
		}

		$image = _var('image');
		if( isset($_GET['image']) && $image != -1 ){
			$images = serialize(array());
			
			if( $image ){
				$condizione .= "images <> '{$images}' AND ";
			}else{
				$condizione .= "images = '{$images}' AND ";
			}
			
		}
		
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','product as p left outer join productLocale as l on l.product=p.id',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('id,name,sku,visibility,section,type,images','product as p left outer join productLocale as l on l.product=p.id',$condizione);
		//debugga($database->lastquery);exit;
		$total_items = $tot[0]['tot'];

		$this->setListOption('html_template','catalogo/product/list.htm');
		$this->setListOption('total_items',$total_items);
		$this->setDataList($list);
		
	}

	function displayList(){
		$this->setMenu('manage_products');
		$this->showMessage();
		$this->categories = $this->array_sezioni();
		$fields = array(
			0 => array(
				'name' => 'Immagine',
				'field_value' => 'images',
				'function' => 'getProductImage',
				'function_type' => 'value',
				'searchable' => true,
				'search_name' => 'image',
				'search_value' => (isset($_GET['image']))? _var('image'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					1 => 'ha immagine',
					0 => 'non ha immagine'
				)
			),
			1 => array(
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',
			),
			2 => array(
				'name' => 'cod. articolo',
				'field_value' => 'sku',
				'sortable' => true,
				'sort_id' => 'sku',
				'searchable' => true,
				'search_name' => 'sku',
				'search_value' => _var('sku'),
				'search_type' => 'input',
			),
			3 => array(
				'name' => 'Nome articolo',
				'field_value' => 'name',
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			),
			4 => array(
				'name' => 'Categoria',
				'field_value' => 'section',
				'function' => 'getCategoryName',
				'function_type' => 'value',
				'sortable' => true,
				'sort_id' => 'section',
				'searchable' => true,
				'search_name' => 'section',
				'search_value' => _var('section'),
				'search_type' => 'select',
				'search_options' => $this->categories
			),
			5 => array(
				'name' => 'visibilità',
				'function' => 'onlineOffline',
				'function_type' => 'row',
				'sortable' => true,
				'sort_id' => 'visibility',
				'searchable' => true,
				'search_name' => 'visibility',
				'search_value' => (isset($_GET['visibility']))? _var('visibility'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					1 => 'online',
					0 => 'offline'
				)
			),
			6 => array(
				'name' => 'Tipo',
				'field_value' => 'type',
				'function' => 'productType',
				'function_type' => 'value',
				'sortable' => true,
				'sort_id' => 'type',
				'searchable' => true,
				'search_name' => 'type',
				'search_value' => _var('type'),
				'search_type' => 'select',
				'search_options' => array(
					'' => 'seleziona..',
					1 => 'semplice',
					2 => 'configurabile'
				)
			),
			7 => array(
				'name' => '',
				'field_value' => 'id',
				'function_type' => 'value',
				'function' => 'getProductLink'
	
			),

		);

		$bulk_actions = $this->getListOption('bulk_actions');

		$bulk_actions['actions']['active'] = array(
				'text' => 'rendi online',
				'icon_type' => 'icon',
				'icon' => 'fa fa-eye',
				'img' => '',
				'confirm' => true,
				'confirm_message' => 'Sicuro di voler rendere <b>online</b> i prodotti selezionati?',
				

		);
		$bulk_actions['actions']['inactive'] = array(
				'text' => 'rendi offline',
				'icon_type' => 'icon',
				'icon' => 'fa fa-eye-slash',
				'img' => '',
				'confirm' => true,
				'confirm_message' => 'Sicuro di voler rendere <b>offline</b> i prodotti selezionati?',
				

		);
		$bulk_actions['actions']['change_section'] = array(
				'text' => 'cambia categoria',
				'icon_type' => 'icon',
				'icon' => 'fa fa-move',
				'img' => '',
				'confirm' => true,
				//'confirm_message' => 'Sicuro di voler rendere offline i prodotti selezionati?',
				'ajax_content' => 'displayFormCategoryBulk',

		);
		$this->setListOption('bulk_actions',$bulk_actions);

		$this->setTitle('Prodotti');
		$this->setListOption('fields',$fields);
		$this->getList();


		parent::displayList();

	}
	
	function displayFormCategoryBulk(){
		$dataform = $this->getDataForm('product_bulk_action_category');
		$this->setVar('dataform',$dataform);
		$this->output('catalogo/product/form_bulk_action.htm');
		
	}

	function getProductImage($val){
		$html = '';
		$images = unserialize($val);
		if( okArray($images) ){
			$id_image = $images[0];
			if( $id_image ){
				$html = "<img class='imgprodlist' src='/img/{$id_image}/th/img.png' alt=''>";
			}
		}
		return $html;
	}
	function getCategoryName($val){
		if( isset($this->categories[$val]) && $this->categories[$val]) return $this->categories[$val];
		return '';
	}

	function getProductLink($val){
		$url = _MARION_BASE_URL_."index.php?mod=catalogo&ctrl=Catalogo&action=product&product=".$val;

		$html = "<a href='{$url}' target='_blank' class='edit btn btn-sm btn-default'><i class='fa fa-link'></i></a>";
		return $html;
	}

	function onlineOffline($row){
		if( _var('export') ){
			if ($row['visibility'] ){
				$html = strtoupper(_translate('online'));
			}else{
				$html = strtoupper(_translate('offline'));
			}
		}else{
			if ($row['visibility'] ){
				$html = "<span class='label label-success'  id='status_{$row['id']}' style='cursor:pointer;' onclick='change_visibility({$row['id']}); return false;'>".strtoupper(_translate('online'))."</span>";
			}else{
				$html = "<span class='label label-danger' id='status_{$row['id']}' style='cursor:pointer;' onclick='change_visibility({$row['id']}); return false;'>".strtoupper(_translate('offline'))."</span>";
			}
		}

		return $html;
	}

	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Prodotto salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Prodotto eliminato con successo','success');
		}

		if( _var('deleted_multiple') ){
			$this->displayMessage('I prodotti selezionati sono stati eliminati con successo','success');
		}

		
	}
	

	function productType($val){
		$type = '';
		switch($val){
			case 1:
				$type = 'semplice';
				break;
			case 2:
				$type = 'configurabile';
				break;
		}
		return $type;
	}

	function saved(){
		$this->redirectTolist(array('saved'=>1));
	}

	function bulk(){
		$action = $this->getBulkAction();
		$ids = $this->getBulkIds();
		$database = Marion::getDB();;

		switch($action){
			case 'active':
				
				foreach($ids as $id){

					$obj = Product::withId($id);
					if( is_object($obj) ){
						$obj->set(array('visibility' => 1));
						$obj->save();
					}
				}
				break;
			case 'inactive':
				foreach($ids as $id){
					$obj = Product::withId($id);
					if( is_object($obj) ){
						$obj->set(array('visibility' => 0));
						$obj->save();
					}
					
				}
				break;
			case 'delete':
				foreach($ids as $id){
					$obj = Product::withId($id);
					if( is_object($obj) ){
						$obj->delete();
					}
				}
				break;
			case 'change_section':
				$data = $this->getBulkForm();
				if( $data['section'] ){
					foreach($ids as $id){
						$obj = Product::withId($id);
						if( is_object($obj) ){
							$obj->set(array('section' => $data['section']));
							$obj->save();
						}
					}
				}
				break;
		}
		parent::bulk();
	}


	function delete(){
		$id = $this->getID();
		if( (int)$id ){
			$list[] = $id; 
		}else{
			$list = (array)json_decode($id);
		}
		
		foreach($list as $v){
			$obj = Product::withId($v);
			
			$obj->delete();
		}
		if( count($list) > 1 ){
			$this->redirectToList(array('deleted_multiple'=>1));
		}else{
			if( $obj->parent ){
				header('Location: '.$this->getUrlScript()."&action=edit&id=".$obj->parent."&tab=inventory");
			}else{
				$this->redirectToList(array('deleted'=>1));
			}



		}

		
	}


	function ajax(){
		
		$action = $this->getAction();
		$id = $this->getID();
		switch($action){
			case 'get_product_section':
				$section = _var('section');
				$name = _var('name');
				$query = Product::prepareQuery()
						->whereExpression("(name like '%{$name}%' OR sku like '%{$name}%')")
						->where('section',$section)
						->where('parent',0);
				
				$prodotti = $query->get();
				$toreturn = array();
				
				if( okArray($prodotti) ){
					foreach($prodotti as $k => $v){
						$item = array(
							'name' => $v->get('name'),
							'id' => $v->id,
							'img' => $v->getUrlImage(0,'small')
						);
						$toreturn[] = $item;
					}
				}
				$risposta = array(
					'result' => 'ok',
					'data' => $toreturn
				);
				
				

				break;

			case 'change_visibility':
				$obj = Product::withId($id);
				if( is_object($obj) ){
					if( $obj->visibility ){
						$obj->visibility = 0;
					}else{
						$obj->visibility = 1;
					}
					
					$obj->save();
					$risposta = array(
						'result' => 'ok',
						'status' => $obj->visibility,
						'text' => $obj->visibility? strtoupper(_translate('online')):strtoupper(_translate('offline')),
					);
				}else{
					$risposta = array(
						'result' => 'nak'	
					);
				}
				break;
			case 'add_child_rapid_ok':
				$formdata = $this->getFormdata();
				
				
				$combinazioni = $formdata['combinazioni'];
				$parent = $formdata['parent'];
				
				unset($_SESSION['last_child_product_'.$parent]);

				$product = Product::withId($parent);
				$attributes = $product->getAttributes();
				$num_var = count($attributes);
				$tmp = $product;
				unset($tmp->id);
				unset($tmp->images);
				unset($tmp->_old_images);
				unset($tmp->dateInsert);
				unset($tmp->dateLastUpdate);
				unset($tmp->relatedSections);
				$tmp->type = 1;
				$tmp->parent = $parent;
				$database = Marion::getDB();;
				foreach($combinazioni as $comb){
					
					$check = 0;
					//debugga($comb['attributi']);
					foreach($comb['attributi'] as $v){
						if( $v ){
							$check++;
						}
					}
					if( $check != $num_var ){
						$errore = "Selezionare un valore per ciascuna variazione";
					}
					
					if( !$errore ){
						if( $comb['checked'] ){
							$child = clone $tmp;
							//$child->parentPrice = 1;
							$child->stock = (int)$comb['stock'];
							$child->visibility = 1;
							//$child->minOrder = 1;
							$child->setAttributes($comb['attributi']);
							$res = $child->save();


							
							
							
							if( is_object($res) ){
								$database->insert('product_shop_values',
									array(
										'min_order' => 1,
										'parent_price' => 1,
										'id_tax' => $parent->taxCode,
										'id_product' => $res->id
									)
								);
								$_SESSION['last_child_product_'.$parent][$res->id] = $res->id;
							}else{
								$errore = __($res);
							}
							
						}
					}
				}
				
				if( !$errore ){
					$risposta = array(
						'result' => 'ok',
						'id' => $parent,
					);
				}else{
					$risposta = array(
						'result' => 'nak',
						'errore' => $errore
					);
				}
				
				
		
				break;
			case 'get_children_stock':


				
				$product = Product::withId($id);
				//prendo le quantità dei figli
				//$form_stock = $product->getStockChildren();
				$form_stock = $product->getInventoryChildren();
				if( okArray($form_stock) ){
					foreach($form_stock as $k => $v){
						if( !$_SESSION['last_child_product_'.$id][$k] ){
							unset($form_stock[$k]);
						}
					}
					unset($_SESSION['last_child_product_'.$id]);


					$this->setVar('form_veloce_stock',$form_stock); 
				}
				
				ob_start();
				$this->output('catalogo/product/form_stock_children.htm');
				$html = ob_get_contents();
				ob_end_clean();
			   
				$risposta = array(
					'result' => 'ok',
					'html' => $html,
				);

				

				break;
		
				
		}

		echo json_encode($risposta);
		
	}



	// METODI 


	function getWhereList($formdata){

		if( okArray($formdata) ){
			$_SESSION['filter_search_products_admin'] = $formdata;
			$array = $this->checkDataForm('search_product',$formdata);
			$where = '';
			if( $array[0] == 'ok'){
			
				if( $array['visibility'] ){
					if( $array['visibility'] == 1 ){
						$where .= "visibility = 1 AND ";
					}else{
						$where .= "visibility = 0 AND ";
					}
				}

				if( $array['section'] ){
					$where .= "section = {$array['section']} AND ";
				}

				if( $array['id'] ){
					$where .= "id = {$array['id']} AND ";
				}

				if( $array['name'] ){
					$where .= "name LIKE '%{$array['name']}%' AND ";
				}

				if( $array['sku'] ){
					$where .= "sku LIKE '%{$array['sku']}%' AND ";
				}

				if( $array['type'] ){
					$where .= "type = {$array['type']} AND ";
				}
			}
			
		}

		if( $where ){
			$where = preg_replace('/ AND $/','',$where);
		}
		

		return $where;


	}
	function getUserCategories(){
		$categorie = UserCategory::prepareQuery()->get();
		$this->setVar('categorie',$categorie);
	}

	function getPriceLists(){
		$pricelist = PriceList::prepareQuery()->orderBy('priority')->get();
		$this->setVar('pricelist',$pricelist);
	}
	



	function getAttributesInput($dati,$prodotto=NULL,&$campi_aggiuntivi=null){
		$action = $this->getAction();
		$attributeSet = $dati['attributeSet'];
		
		
		
		if($attributeSet){

			$insieme_Attributi = AttributeSet::withId($attributeSet);
			if($insieme_Attributi){
				if( $dati['type'] == 1 ){
					$attributeSelect = $insieme_Attributi->getAttributeWithValues(); 
				}
				if( $dati['type'] == 2 ){
					$attributeSelect = $insieme_Attributi->getAttributeWithValuesAndImages(); 
					//debugga($attributeSelect);exit;
				}
			}
		}

		if( $this->isSubmitted()){

			
			if( $dati['type'] == 1 ){
				foreach($attributeSelect as $k => $v){
					$campi_aggiuntivi[$k] = array(
							'campo'=>$k,
							'type'=>'select',
							'options' => $v,
							'obbligatorio'=>'t',
							'default'=>'0',
							'etichetta'=>$k
						);
					$attributi_selezionati[$k] = $dati[$k];

				}
			}
		
		}else{

			
			if($action != 'add'){
				//prelevo i valori degli attributi per il prodotto in esame
				$attributi_selezionati = $prodotto->getAttributes();
				
			}
		}
		if(okArray($attributeSelect)){

			$this->setVar('attributes',$attributeSelect);
			$this->setVar('select_variazione_prodotto',$attributeSelect);
		}
		if(okArray($attributi_selezionati)){
			$this->setVar('attributiSelezionati',$attributi_selezionati);
		}
		
		

	}

	function getRelatesProducts($prodotto){
		if( !$prodotto->parent ){

			//PRENDO I PRODOTTI CORRELATI
			$sections_related = $prodotto->relatedSections;
			$num_products_related = 0;
			if( okArray($sections_related) ){
				foreach($sections_related as $k => $v){
					$sectionRel = Section::withId($v['section']);
					if( is_object($sectionRel) ){
						$v['section_name'] = $sectionRel->get('name');
						if( $v['type'] == 'specific' ){
							if( okArray($v['products']) ){
								$where = '(id in (';
								foreach($v['products'] as $_id){
									$where .= "{$_id}, ";
								}
								$where = preg_replace('/\, $/','))',$where);
								$v['products'] = Product::prepareQuery()->where('visibility',1)->whereExpression($where)->get();
								foreach($v['products'] as $v2){
									$list_products_related[] = $v2->id;
								}
							}
						}
						$num_products_related += count($v['products']);
						$sections_related[$k] = $v;
						$list_sections_related[] = $v['section'];
					}else{
						unset($sections_related[$k]);
					}
					
				}
			}
			$this->setVar('list_products_related',$list_products_related);
			$this->setVar('list_sections_related',$list_sections_related);
			if( okArray($sections_related) ){
				$num_sections_related = count($sections_related);
			}else{
				$num_sections_related = 0;
			}
			
			$this->setVar('num_products_related',$num_products_related);
			$this->setVar('num_sections_related',$num_sections_related);
			$this->setVar('relatedSections',$sections_related);
			
			
		}
	}


	//FORM
	function array_sezioni_prodotto(){
		return $this->array_sezioni();
	}
	function array_sezioni(){
		
		$sezioni = Section::getAll('it');
		
		$select = array('seleziona...');
		foreach($sezioni as $k => $v){
			$select[$k] = $v;
		}
		return $select;
	}

	function array_produttori(){
		
		$produttori = Manufacturer::prepareQuery()->get();
		
		$select = array('seleziona...');
		foreach($produttori as $v){
			$select[$v->id] = $v->get('name');
		}
		return $select;

	}

	function array_tag_product(){
		
		$tag = TagProduct::prepareQuery()->get();
		
		
		foreach($tag as $v){
			$select[$v->id] = $v->label;
		}
		return $select;

	}

	function array_type_url(){
		
		$tipi = Product::getTypeUrl();
		
		unset($tipi[0]);
		foreach($tipi as $k => $v){
			if( isMultilocale() ){
				$tipi_select[$k] = "www.mysite.com".sprintf($v,"<b>it</b>","<b>codice</b>","<b>nome_prodotto</b>");
			}else{
				
				$tipi_select[$k] = "www.mysite.com".sprintf($v,"<b>codice</b>","<b>nome_prodotto</b>");
			}
			
		}
		$this->array_type_url_product = $tipi_select;
		return $tipi_select;
		

	}



	function array_insieme_attributi(){
		
		$insiemi = AttributeSet::getList();
		
		$select = array('nessuno');
		foreach($insiemi as $k => $v){
			$select[$v->getId()] = $v->getLabel();
		}
		return $select;

	}


	
	/*  MODULI */
	//metodo che carica i moduli controllers
	function loadModuleControllers(){
		if(okArray(Product::$_registred_classes)){
			foreach(Product::$_registred_classes as $v){
				
				$mod_ctrl = new $v($this);
				if($mod_ctrl->isEnabled()){
					$this->_module_ctrls[] = $mod_ctrl;
				}
				
			}
			
		}
	}

	//metodo che aggiunge al form le tab dei moduli
	function getTabModules(){
		
		if(okArray($this->_module_ctrls)){
			$this->setVar('admin_tab_classes',$this->_module_ctrls);
		}
	}
	//metodo che controlla i dati passati dal form dei moduli
	function checkDataModules(){
		
		$check = true;
		if( okArray($this->_module_ctrls) ){
			foreach( $this->_module_ctrls as $obj){
				$_check = $obj->checkData();
				if( $_check != 1 ){
					
					$check = array();
					$check['error'] = $_check;
					$check['tab'] = $obj->getTag();
					break;
				}
			}
		}

		

		return $check;
	}
	//carica i file js e css dei moduli
	function setMediaModules(){
		
		if(okArray($this->_module_ctrls)){
			foreach( $this->_module_ctrls as $obj){
				$obj->setMedia();
			}
		}
	}
	

	//metodo che processa i moduli dopo il salvataggio del prodotto
	function processModules($product){
		
		
		if(okArray($this->_module_ctrls)){
			foreach($this->_module_ctrls as $obj){
				
				
				$obj->process($product);

			}
		}

		
	}

	//metodo che ricarica il contenuto delle tab dei moduli
	function reloadContentModules(){
		
		if(okArray($this->_module_ctrls)){
			foreach($this->_module_ctrls as $obj){
				
				
				
				if( $obj->reloadContent()){
					ob_start();
					
					$obj->getContent();
					$html = ob_get_contents();
					ob_end_clean();
					$content[$obj->getTag()] = $html; 
				}
			}
		}

		return $content;
	}

	//metodo che controlla se occorre ricaricare la pagina
	function checkReloadPageInModules(){
		$check = false;
		if(okArray($this->_module_ctrls)){
			foreach($this->_module_ctrls as $obj){
				
				
				
				if( $obj->reloadPage()){
					$check = true;
					break;
				}
			}
		}

		return $check;
	}

	/*  FINE MODULI */




	//override

	function getFormdata($num=null){
		if(	$num ){
			if( $this->_ajax || _var('ajax_request') ){
				$formdata = _formdata($num);
			}else{
				$formdata = _var('formdata'.$num);
			}
		}else{
			if( $this->_ajax  || _var('ajax_request')){
				$formdata = _formdata();
			}else{
				$formdata = _var('formdata');
			}
		}		
		
		return $formdata;
	}

}



?>