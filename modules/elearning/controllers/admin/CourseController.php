<?php
use Marion\Controllers\AdminModuleController;
use Marion\Controllers\Elements\ListActionBulkButton;
use Illuminate\Database\Capsule\Manager as DB;
use Catalogo\{Product};
use Shop\Price;
class CourseController extends AdminModuleController{	
	public $auth="catalog"; //permesso per accedere al controller

	private function getList(){
		$limit = $this->getListContainer()->getPerPage();
		$query = DB::table('product','p')
			->join('productLocale as l','l.product','=','p.id')
			->where('deleted',0)
			->where('locale',_MARION_LANG_);
		
			if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$query->orderBy($order,$order_type);
		}
		if( $id = _var('id') ){
			$query->where('c.id',$id);
		}
		if( $section = _var('section') ){
			$query->where('section',$section);
		}
		if( $order_view = _var('orderView') ){
			$query->where('orderView',$order_view);
		}
		if( $name = _var('name') ){
			$query->where('name','like',"%{$name}%");
		}
		$visibility = _var('visibility');
		if( isset($_GET['visibility']) && $visibility != -1 ){
			$query->where('visibility',$visibility);
		}


		$total_items = $query->count();
		if( $total_items > 0 ){
			if( $page_id = _var('pageID') ){
				$offset = (($page_id-1)*$limit);
			}
			$query->limit($limit);
			if( isset($offset) ){
				$query->offset($offset);
			}
			$list = $query->get(
				[	'id',
					'images',
					'name',
					'visibility',
					'orderView',
					'section'
				]
			)->toArray();
			$this->getListContainer()
			->setDataList($list)
			->setTotalItems($total_items);

		}else{
			$this->getListContainer()
			->setTotalItems(0);
		}
	}

	public function displayList(){
		$this->setTitle('Corsi');
		$this->setMenu('elearning_corsi');

		if( _var('bulk_success') ){

		}else{
			if( _var('saved') ){
				$this->displayMessage('Corso inserito con successo');
			}
			if( _var('changed') ){
				$this->displayMessage('Corso aggiornato con successo');
			}
			if( _var('deleted') ){
				$this->displayMessage('Corso eliminato con successo');
			}
		}
		

		$categorie = $this->categorie();

		
		$bulkAction = (new ListActionBulkButton('delete'))
					->setText('elimina')
					->setIcon('fa fa-trash-o');

		$this->getListContainer()
			->addActionBulkButton(
				$bulkAction
			)
			->addEditActionRowButton()
			->addDeleteActionRowButton()
			->setFieldsFromArray(
				[
					[
						'name' => 'id',
						'field_value' => 'id',
						'searchable' => true,
						'sortable' => true,
						'sort_id' => 'id',
						'search_name' => 'id',
						'search_value' => _var('id'),
						'search_type' => 'input',
					],
					[
						'name' => '',
						'function_type' => 'row',
						'function' => function($row) use($categorie){
							if( $row->images ){
								$images = unserialize($row->images);
								if( okArray($images) && $images[0]){
									$url = _MARION_BASE_URL_.'img/'.$images[0]."/sm/product.png";
									return "<img src='{$url}' style='width:100px;'>";
								}
							}
							return '';
						}
					],
					[
						'name' => 'nome',
						'field_value' => 'name',
						'searchable' => true,
						'sortable' => true,
						'sort_id' => 'name',
						'search_name' => 'name',
						'search_value' => _var('name'),
						'search_type' => 'input',
					],
					[
						'name' => 'categoria',
						'function_type' => 'row',
						'function' => function($row) use($categorie){
							return $categorie[$row->section];
						},
						'sortable' => true,
						'sort_id' => 'section',
						'searchable' => true,
						'search_name' => 'section',
						'search_value' => _var('section'),
						'search_type' => 'select',
						'search_options' => $categorie
					],
					[
						'name' => 'ordine visual.',
						'field_value' => 'orderView',
						'searchable' => true,
						'sortable' => true,
						'sort_id' => 'orderView',
						'search_name' => 'orderView',
						'search_value' => _var('orderView'),
						'search_type' => 'input',
					],
					[
						'name' => 'online',
						'function_type' => 'row',
						'function' => function($row){
							if($row->visibility) return 'SI';
							return 'NO';
						},
						'sortable' => false,
						'sort_id' => 'visibility',
						'searchable' => true,
						'search_name' => 'visibility',
						'search_value' => (isset($_GET['visibility']))? _var('visibility'):-1,
						'search_type' => 'select',
						'search_options' => array(
							-1 => 'seleziona..',
							1 => 'SI',
							0 => 'NO'
						)
					],
					[
						'name' => '',
						'function_type' => 'row',
						'function' => function($row){
							return "<a class='btn btn-info' href='index.php?ctrl=Units&mod=elearning&action=list&id_course={$row->id}'><i class='fa fa-list'></i> unità</a>";
						}
					],
				]
		)->build();
		$this->getList();
		parent::displayList();
	}


	public function displayForm()
	{
		$this->setMenu('elearning_corsi');
		$id = _var('id');
		$action = $this->getAction();
		
		if( $this->isSubmitted() ){
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('elearning_corso',$dati);
			
			if( $array[0] == 'ok'){
				if( $action == 'edit' ){
					$product = Product::withId($array['id']);
				}else{
					$product = Product::create();
					$product->set(
						[
							'weight' => 1000,
							'virtual_product' => 1,
						]
					);
				}
				if( $array['image'] ){
					$array['images'] = [
						$array['image']
					];
				}else{
					$array['images'] = [];
				}
				if( !$array['visibility'] ) $array['visibility'] = 0;
				$product->set(
					$array
				);
				//debugga($array);exit;
				$product->save();
				$price = Price::prepareQuery()
					->where('product',$product->id)
					->where('label','default')
					->where('quantity',1)
					->getOne();
				if( !$price ){
					$price = Price::create();
				}
				//debugga($price);exit;
				$price->set(
					[
						'label' => 'default',
						'quantity' => 1,
						'value' => $dati['price'],
						'type' => 'price',
						'product' => $product->id
					]
				);
				$price->save();
				if( $action == 'edit' ){
					$this->redirectToList(['changed'=>true]);
				}else{
					$this->redirectToList(['saved'=>true]);
				}
				
			}else{
				$this->errors[] = $array[1];
			}
		}else{
			if( $id ){
				$product = Product::withId($id);
				if( is_object($product) ){
					$dati = $product->prepareForm2();
					if( okArray($dati['images'])){
						$dati['image'] = $dati['images'][0];
					}
	
					$prezzo = $product->getPriceValue();
					$dati['price'] = $prezzo;
					
				}
			}
		}

		
		$dataform = $this->getDataForm('elearning_corso',$dati);
		$this->setVar('dataform',$dataform);
		$this->output('form.htm');
	}

	function categorie(){
		
		$sezioni = Section::getAll('it');
		
		$select = array('seleziona...');
		foreach($sezioni as $k => $v){
			$select[$k] = $v;
		}
		return $select;
	}


	function delete(){
		$id = $this->getID();
		$product = Product::withId($id);
		if( is_object($product) ){
			$product->delete();
		}
		$this->redirectToList(['deleted'=>true]);
	}


	function bulk()
	{
		
		$action = $this->getBulkAction();
		switch($action){
			case 'delete':
				$ids = $this->getBulkIds();
				foreach($ids as $id){
					$product = Product::withId($id);
					if( is_object($product)){
						$product->delete();
					}
				}
				break;
		}
		parent::bulk();
	}
}
?>
