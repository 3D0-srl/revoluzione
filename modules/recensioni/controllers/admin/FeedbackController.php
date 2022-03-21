<?php
use Marion\Controllers\AdminModuleController;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Controllers\Elements\ListActionBulkButton;
class FeedbackController extends AdminModuleController{	
	public $auth="catalog"; //permesso per accedere al controller



	private function getList(){
		$limit = $this->getListContainer()->getPerPage();
		$query = DB::table('recensioni');
		
		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$query->orderBy($order,$order_type);
		}
		if( $id = _var('id') ){
			$query->where('id',$id);
		}
	
		if( $nickname = _var('nickname') ){
			$query->where('nickname','like',"%{$nickname}%");
		}
		if( $message = _var('message') ){
			$query->where('message','like',"%{$message}%");
		}
		$confermato = _var('confermato');
		if( isset($_GET['confermato']) && $confermato != -1 ){
			$query->where('confermato',$confermato);
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
				['*']
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
		$this->setTitle('Recensioni');
		$this->setMenu('recensioni_admin');

		if( _var('bulk_success') ){

		}
		

		//$categorie = $this->categorie();

		
		$bulkAction = (new ListActionBulkButton('delete'))
					->setText('elimina')
					->setIcon('fa fa-trash-o');
		$bulkAction2 = (new ListActionBulkButton('conferma'))
					->setText('conferma')
					->setIcon('fa fa-check');

		$this->getListContainer()
			->addActionBulkButtons(
				[$bulkAction2,$bulkAction]
				
			)
			//->addEditActionRowButton()
			//->addDeleteActionRowButton()
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
						'name' => 'nickname',
						'field_value' => 'nickname',
						'searchable' => true,
						'sortable' => true,
						'sort_id' => 'nickname',
						'search_name' => 'nickname',
						'search_value' => _var('nickname'),
						'search_type' => 'input',
					],
					
					[
						'name' => 'Messaggio',
						'field_value' => 'message',
						'searchable' => true,
						'sortable' => true,
						'sort_id' => 'message',
						'search_name' => 'message',
						'search_value' => _var('message'),
						'search_type' => 'input',
					],
					[
						'name' => 'confermato',
						'function_type' => 'row',
						'function' => function($row){
							if($row->confermato) return 'SI';
							return 'NO';
						},
						'sortable' => false,
						'sort_id' => 'confermato',
						'searchable' => true,
						'search_name' => 'confermato',
						'search_value' => (isset($_GET['confermato']))? _var('confermato'):-1,
						'search_type' => 'select',
						'search_options' => array(
							-1 => 'seleziona..',
							1 => 'SI',
							0 => 'NO'
						)
					]
				]
		)->build();
		$this->getList();
		parent::displayList();
	}

	/*
	*	ajax
	*	Metodo richiamato per gestire le chiamate ajax
	*/
	public function ajax(){
		
	}


	/*
	*	delete
	*	Metodo richiamato per eliminare un elemento
	*/
	public function delete(){
		$id = $this->getId();

		$toReturn = [
			"deleted" => 1

		];
		$this->redirectToList($toReturn);
		
	}

	/*
	*	bulk
	*	Metodo richiamato per eseguire un azione bulk sugli elementi della lista selezionati
	*/
	public function bulk(){
		$action = $this->getBulkAction(); //prendo il valore selezionato per la bulk action
		$ids = $this->getBulkIds(); // prendo gli id selezionati 


		switch($action){
			case "delete":
				$ids = $this->getBulkIds();
				foreach($ids as $id){
					DB::table('recensioni')->where('id',$id)->delete();
				}
				break;
			case "conferma":
				$ids = $this->getBulkIds();
				foreach($ids as $id){
					DB::table('recensioni')->where('id',$id)->update(['confermato' => true]);
				}
				break;
		}
		parent::bulk();
		
	}


}
?>
