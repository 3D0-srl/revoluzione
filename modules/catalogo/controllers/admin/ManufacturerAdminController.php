<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use Catalogo\{Manufacturer,Product};
use Marion\Controllers\Elements\UrlButton;
class ManufacturerAdminController extends AdminModuleController{
	public $_auth = 'catalog';
	

	function displayContent(){
		
		$action = $this->getAction();
		switch($action){
			case 'products':
				
				$this->displayProductList();
				break;
			case 'add_product':
			
				$id_product = _var('id_product');
				$product = Product::withId($id_product);
				$id_manufacturer = _var('id_manufacturer');
				if( is_object($product) ){
					$product->manufacturer = $id_manufacturer;

					$product->save();
				}
				
				$url_back = base64_decode(_var('url_back'));
				
				header('Location: '.$url_back."&removed=1");
				exit;
				break;
			case 'remove_product':
				$id_product = _var('id_product');
				$product = Product::withId($id_product);
				
				if( is_object($product) ){
					$product->manufacturer = 0;

					$product->save();
				}
				$id_manucaturer = _var('id_manucaturer');
				$url_back = base64_decode(_var('url_back'));
				header('Location: '.$url_back."&removed=1");
				exit;
				break;
		}
		
	}


	function displayForm(){
		$this->setMenu('manufacturer');
		
		$action = $this->getAction();

		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			
			$array = $this->checkDataForm('manufacturer',$formdata);
			if( $array[0] == 'ok'){

				if( $action == 'add'){
				$obj = Manufacturer::create();
				}else{
					$obj = Manufacturer::withId($array['id']);
				}
				$obj->set($array);
				
				
				

				$res = $obj->save();
				if( is_object($res) ){
					$this->saved();
				}else{
					$this->errors[] = $res;
				}


			}else{
				$this->errors[] = $array[1];
			}

			$dati = $formdata;
			

		}else{
		
			createIDform();
		
			$id = $this->getID();
			
			if($action != 'add'){
				$obj = Manufacturer::withId($id);
				
				$dati =  $obj->prepareForm2();
				

				if($action == 'duplicate'){
					unset($dati['id']);
					unset($dati['images']);
					$action = "add";
				}
			}else{
				$dati = NULL;
			}
		}
		

		$dataform = $this->getDataForm('manufacturer',$dati);
			
		$this->setVar('dataform',$dataform);
		$this->output('catalogo/manufacturer/form.htm');
		

	}


	function setMedia(){
		if( $this->getAction() == 'list'){
			$this->registerJS('../modules/catalogo/js/admin/manufacturer.js','end');
			
		}
	}

	function getList(){
		$database = Marion::getDB();;
		
		$condizione = "locale = '{$GLOBALS['activelocale']}' AND ";
		
		$limit = $this->getListOption('per_page');
		
		if( $name = _var('name') ){
			$condizione .= "name LIKE '%{$name}%' AND ";
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','manufacturer as m join manufacturerLocale as l on l.manufacturer=m.id',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('m.id,l.name','manufacturer as m join manufacturerLocale as l on l.manufacturer=m.id',$condizione);
		
		
		$this->setListOption('total_items',$tot[0]['tot']);
		$this->setDataList($list);
		
	}

	function displayList(){
		$this->setMenu('manufacturer');
		$this->showMessage();

	
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
					'name' => '',
					'function_type' => 'row',
					'function' => 'getNumProducts',
				),

			);

			
			$this->setTitle(_translate('Manufacturers','catalogo'));
			$this->setListOption('fields',$fields);
			$this->getList();
			parent::displayList();
			
	}


	function getNumProducts($row){
		
		$id = $row['id'];
		$database = Marion::getDB();;
		$sel = $database->select('count(*) as tot','product',"(deleted IS NULL OR deleted=0) AND manufacturer={$id}");
		$url = $this->getUrlScript()."&action=products&id_manufacturer=".$id."&url_back=".urlencode($this->getUrlCurrent());
		
		return "<a href='{$url}' class='btn btn-default btn-sm'>".$sel[0]['tot']." prodotti</a>";
	}

	function bulk(){
		$action = $this->getBulkAction();
		$ids = $this->getBulkIds();
		$database = Marion::getDB();;

		switch($action){
			
			case 'delete':
				foreach($ids as $id){
					$obj = Manufacturer::withId($id);
					if( is_object($obj) ){
						$obj->delete();
					}
				}
				break;
		}
		parent::bulk();
	}

	
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Brand salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Brand eliminato con successo','success');
		}
	}

	function saved(){
		$this->redirectTolist(array('saved'=>1));
	}


	function delete(){
		$id = $this->getID();

		$obj = Manufacturer::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));
		

		
	}




	
	function ajax(){
		
		$action = $this->getAction();
		$id = $this->getID();
		switch($action){
			case 'change_visibility':
				$obj = Manufacturer::withId($id);
				if( is_object($obj) ){
					if( $obj->visibility ){
						$obj->visibility = 0;
					}else{
						$obj->visibility = 1;
					}
					
					$obj->save();
					$risposta = array(
						'result' => 'ok',
						'status' => $obj->visibility
					);
				}else{
					$risposta = array(
						'result' => 'nak'	
					);
				}
				break;
				
		}

		echo json_encode($risposta);
		
	}





	function getProductList(){
		$database = Marion::getDB();;
		$id_manucaturer = _var('id_manufacturer');
		$condizione = "parent = 0 AND (deleted is NULL OR deleted= 0) AND (locale is NULL OR locale = '{$GLOBALS['activelocale']}') AND ";


		$add_product = _var('add_products');
		if( $add_product ){
			$condizione .=  "manufacturer <> {$id_manucaturer} AND ";
		}else{
			$condizione .=  "manufacturer = {$id_manucaturer} AND ";
		}
		
		
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

	function displayProductList(){
		$this->setMenu('manufacturer');
		$id_manucaturer = _var('id_manufacturer');
		$add_product = _var('add_products');
		
		$this->showMessage();
		$this->categories = $this->array_sezioni();
		$fields = array(
			0 => array(
				'name' => 'Immagine',
				'field_value' => 'images',
				'function' => 'getProductImage',
				'function_type' => 'value',
				/*'searchable' => true,
				'search_name' => 'image',
				'search_value' => (isset($_GET['image']))? _var('image'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					1 => 'ha immagine',
					0 => 'non ha immagine'
				)*/
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
			/*6 => array(
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
	
			),*/

		);

		$man = Manufacturer::withId($id_manucaturer);

		

		$bulk_actions = $this->getListOption('bulk_actions');
		$row_actions = $this->getListOption('row_actions');
		//$buttons = $this->getListOption('buttons');
		$bulk_actions = array();
		if( $add_product ){
			$row_actions['actions'] = array(
				'add' =>	array(
					'text' => 'associa a <b>'.$man->get('name')."</b>",
					'icon_type' => 'icon',
					'icon' => 'fa fa-plus',
					'url_function' => 'addProductUrl',
				)
			);
			$bulk_actions['actions']['add_to_manucaturer'] = array(
				'text' => 'aggiungi associazione',
				'icon_type' => 'icon',
				'icon' => 'fa fa-plus',
				'img' => '',
				'confirm' => true,
				'confirm_message' => "Sicuro di voler associare i prodotti selezionati al producttore <b>".$man->get('name')."</b>?",
				

			);
			$this->setTitle(_translate(['Add products to <b>%s</b>',$man->get('name')],'catalogo'));
			

		

			$this->addToolButton(
				(new UrlButton('back'))
				->setText(_translate('back'))
				->setUrl($this->getUrlScript()."&action=products&id_manufacturer="._var('id_manufacturer')."&url_back=".urlencode(_var('url_back')))
				->setIconType('icon')
				->setClass('btn btn-secondario')
				->setIcon('fa fa-arrow-left')
			);
			
			
		}else{
			$row_actions['actions'] = array(
				'delete' =>	array(
					'text' => 'rimuovi associazione',
					'icon_type' => 'icon',
					'icon' => 'fa fa-trash-o',
					'url_function' => 'removeProductUrl',
				)
			);
			$bulk_actions['actions']['remove_from_manucaturer'] = array(
				'text' => 'rimuovi associazione',
				'icon_type' => 'icon',
				'icon' => 'fa fa-trash-o',
				'img' => '',
				'confirm' => true,
				'confirm_message' =>  "Sicuro di voler rimuovere l'associazione dei prodotti selezionati con il producttore <b>".$man->get('name')."</b>?",
				

			);
			$this->setTitle(_translate(['Products of <b>%s</b>',$man->get('name')],'catalogo'));
			
		

			$this->addToolButton(
				(new UrlButton('back'))
				->setText(_translate('back'))
				->setUrl(_var('url_back'))
				->setIconType('icon')
				->setClass('btn btn-secondario')
				->setIcon('fa fa-arrow-left')
			)->addToolButton(
				(new UrlButton('add'))
				->setText(_translate('add'))
				->setUrl($this->getUrlScript()."&action=products&add_products=1&id_manufacturer="._var('id_manufacturer')."&url_back=".urlencode(_var('url_back')))
				->setIconType('icon')
				->setClass('btn btn-principale')
				->setIcon('fa fa-plus')
			);
			
			
		}

		
		
		$this->setListOption('row_actions',$row_actions);
		$this->setListOption('bulk_actions',$bulk_actions);
		
		
		$this->setListOption('fields',$fields);
		$this->getProductList();


		parent::displayList();

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
		return $this->categories[$val];
	}

	function removeProductUrl($row){
		$id = $row['id'];
		$url_back = base64_encode($this->getUrlCurrent());
		$url = $this->getUrlScript()."&action=remove_product&id_product=".$id."&id_manufacturer="._var('id_manufacturer')."&url_back=".$url_back;
		return $url;
		
	}
	function addProductUrl($row){
		$id = $row['id'];
		$url_back = base64_encode($this->getUrlCurrent());
		$url = $this->getUrlScript()."&action=add_product&id_product=".$id."&id_manufacturer="._var('id_manufacturer')."&url_back=".$url_back;
		return $url;
		
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

	function array_sezioni(){
		
		$sezioni = Section::getAll('it');
		
		$select = array('seleziona...');
		foreach($sezioni as $k => $v){
			$select[$k] = $v;
		}
		return $select;
	}

}



?>