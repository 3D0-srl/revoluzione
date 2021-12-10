<?php
use Marion\Controllers\AdminModuleController;
use News\NewsType;
use Marion\Controllers\Elements\UrlButton;
use Illuminate\Database\Capsule\Manager as DB;
class NewsCategoryAdminController extends AdminModuleController
{
	public $_auth = 'cms';





	function displayForm()
	{
		$this->setMenu('news_settings');

		$action = $this->getAction();

		if ($this->isSubmitted()) {
			$formdata = $this->getFormdata();

			$array = $this->checkDataForm('type_news', $formdata);
			if ($array[0] == 'ok') {

				if ($action == 'add') {
					$obj = NewsType::create();
				} else {
					$obj = NewsType::withId($array['id']);
				}
				$obj->set($array);




				$res = $obj->save();
				if (is_object($res)) {
					$this->saved();
				} else {
					$this->errors[] = $res;
				}
			} else {
				$this->errors[] = $array[1];
			}

			$dati = $formdata;
		} else {

			createIDform();

			$id = $this->getID();

			if ($action != 'add') {
				$obj = NewsType::withId($id);

				$dati =  $obj->prepareForm2();


				if ($action == 'duplicate') {
					unset($dati['id']);
					unset($dati['images']);
					$action = "add";
				}
			} else {
				$dati = NULL;
			}
		}


		$dataform = $this->getDataForm('type_news', $dati);

		$this->setVar('dataform', $dataform);
		$this->output('form_type_news.htm');
	}



	
	function displayList()
	{
		$this->setMenu('news_settings');
		$this->setTitle('Categorie News');

		$this->showMessage();
		
		$this->addToolButton(
			(new UrlButton('back'))
			->setUrl('index.php?mod=news&ctrl=NewsAdmin&action=list')
			->setText('Torna alle news')
			->setIcon('fa fa-arrow-left')
			->setIconType('icon')	
		);

		$fields = [
			'id' => [
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',

			],
			'title' => [

				'name' => 'Nome',
				'field_value' => 'name',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'name',
				'search_name' => 'name',
				'search_value' => '',
				'search_type' => 'input',

			],
			'visibility' => [
				'name' => 'online',
				'function_type' => 'row',
				'function' => function($row){
					if( $row->visibility){
						return '<span class="label label-success">ONLINE</span>';
					}else{
						return '<span class="label label-danger">OFFLINE</span>';
					}
				},
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'visibility',
				'search_name' => 'visibility',
				'search_value' => isset($_GET['visibility'])?$_GET['visibility']:-1,
				'search_type' => 'select',
				'search_options' => [
					'-1' => '--SELECT--',
					'0' => 'OFFLINE',
					'1' => 'ONLINE'
				]

			],
		];

		$container = $this->getListContainer()
			->setFieldsFromArray($fields)
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->addDeleteActionRowButton();
			
		$container->build();
		$this->getList();
		parent::displayList();
	}


	function getList(){
		$limit = $this->getListContainer()->getPerPage();

		$query = DB::table('news_type','n')
				->join('news_type_lang as l','n.id','=','l.news_type_id')
				->where('lang','=',_MARION_LANG_);

		if( $id = _var('id')){
			$query->where('id','=',$id);
		}
		if( $name = _var('name')){
			$query->where('name','LIKE',"%{$name}%");
		}
		if( isset($_GET['visibility'])){
			$visibility = $_GET['visibility'];
			if( $visibility != -1){
				$query->where('visibility','=',$visibility);
			}
	
		}
		$tot = $query->count();
		
		if( $tot > 0){
			$query->limit($limit);
			if( $page_id = _var('pageID') ){
				$query->offset((($page_id-1)*$limit));
				
			}
			$list = $query->select(['id','name','visibility'])->get()->toArray();
			$this->getListContainer()->setDataList($list);
		}
		$this->getListContainer()->setTotalItems($tot);
		
		
				
		
		
	}



	function showMessage()
	{
		if (_var('saved')) {
			$this->displayMessage('News salvata con successo', 'success');
		}
		if (_var('deleted')) {
			$this->displayMessage('News eliminata con successo', 'success');
		}
	}

	function saved()
	{
		$this->redirectTolist(array('saved' => 1));
	}


	function delete()
	{
		$id = $this->getID();

		$obj = NewsType::withId($id);
		if (is_object($obj)) {
			$obj->delete();
		}
		$this->redirectToList(array('deleted' => 1));
	}





	function ajax()
	{

		$action = $this->getAction();
		$id = $this->getID();
		switch ($action) {
			case 'change_visibility':
				$obj = NewsType::withId($id);
				if (is_object($obj)) {
					if ($obj->visibility) {
						$obj->visibility = 0;
					} else {
						$obj->visibility = 1;
					}

					$obj->save();
					$risposta = array(
						'result' => 'ok',
						'status' => $obj->visibility
					);
				} else {
					$risposta = array(
						'result' => 'nak'
					);
				}
				break;
		}

		echo json_encode($risposta);
	}


	function array_type_url_news_type()
	{

		$tipi = NewsType::getTypeUrl();

		unset($tipi[0]);
		if (isMultilocale()) {
			foreach ($tipi as $k => $v) {
				$tipi_select[$k] = "www.mysite.com" . sprintf($v, "<b>slug</b>", 'it');
			}
		} else {
			foreach ($tipi as $k => $v) {
				$tipi_select[$k] = "www.mysite.com" . sprintf($v, "<b>slug</b>");
			}
		}
		$this->array_type_url_news = $tipi_select;
		return $tipi_select;
	}
}
