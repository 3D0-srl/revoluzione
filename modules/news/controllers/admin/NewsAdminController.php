<?php
use Marion\Controllers\AdminModuleController;
use News\{News,NewsType};
use Marion\Controllers\Elements\UrlButton;
use Illuminate\Database\Capsule\Manager as DB;
class NewsAdminController extends AdminModuleController
{
	public $_auth = 'cms';


	function displayForm()
	{
		$this->setMenu('news_settings');
		$categoryCount = count(NewsType::create()->prepareQuery()->get());
		if($categoryCount == 0) {
			$this->displayMessage('Devi creare almeno una categoria per poter inserire una news!', 'danger');
		}

		$action = $this->getAction();

		if ($this->isSubmitted()) {
			$formdata = $this->getFormdata();

			$array = $this->checkDataForm('news', $formdata);
			if ($array[0] == 'ok') {

				if ($action != 'edit') {
					$obj = News::create();
				} else {
					$obj = News::withId($array['id']);
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
				$obj = News::withId($id);

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


		$dataform = $this->getDataForm('news', $dati);
		
		$this->setVar('dataform', $dataform);
		$this->output('form_news.htm');
	}

	function setMedia()
	{
		if ($this->getAction() == 'list') {
			$this->registerJS($this->getBaseUrlBackend() . 'js/news.js', 'end');
		}
	}


	/*function displayList()
	{
		$this->setMenu('news_settings');
		$this->showMessage();

		$query = News::prepareQuery();

		$limit = $this->getLimitList();

		$offset = $this->getOffsetList();

		$query2 = clone $query;

		if ($limit) {
			$query->limit($limit);
		}
		if ($offset) {
			$query->offset($offset);
		}
		$list = $query->get();
		$tot = $query2->getCount();

		$pager_links = $this->getPagerList($tot);



		


		$this->setVar('links', $pager_links);

		$this->setVar('list', $list);
		$this->output('list_news.htm');
	}*/

	function displayList()
	{
		$this->setMenu('news_settings');
		$this->setTitle('News');

		$this->showMessage();


		$this->addToolButton(
			(new UrlButton('add_type'))
			->setUrl('index.php?mod=news&ctrl=NewsCategoryAdmin&action=list')
			->setText('Gestione categorie')
			->setIcon('fa fa-list')
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

				'name' => 'Titolo',
				'field_value' => 'title',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'title',
				'search_name' => 'title',
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

		$query = DB::table('news','n')
				->join('news_lang as l','n.id','=','l.news_id')
				->where('lang','=',_MARION_LANG_);

		if( $id = _var('id')){
			$query->where('id','=',$id);
		}
		if( $title = _var('title')){
			$query->where('title','LIKE',"%{$title}%");
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
			$list = $query->select(['id','title','visibility'])->get()->toArray();
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

		$obj = News::withId($id);
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
				$obj = News::withId($id);
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


	function array_type_url_news()
	{

		$tipi = News::getTypeUrl();

		unset($tipi[0]);
		
		foreach ($tipi as $k => $v) {
			$tipi_select[$k] = "www.mysite.com" . sprintf($v, "<b>id</b>", "<b>slug</b>");
		}
		
		$this->array_type_url_news = $tipi_select;
		return $tipi_select;
	}

	function array_type_news()
	{
		$toreturn = array($GLOBALS['gettext']->strings['seleziona']);
		$select = NewsType::prepareQuery()->get();
		//debugga($select);exit;
		if (okArray($select)) {
			foreach ($select as $v) {
				$toreturn[$v->id] = $v->get('name');
			}
		}


		return $toreturn;
	}
}
