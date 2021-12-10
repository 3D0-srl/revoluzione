<?php
use Marion\Controllers\AdminModuleController;
use Photogallery\{PhotoGallery,PhotoGalleryImage};
use Illuminate\Database\Capsule\Manager as DB;

class GalleryController extends AdminModuleController
{
	public $_auth = 'photogallery_manager';


	function displayForm()
	{
		$this->setMenu('photogallery_settings');
		$action = $this->getAction();

		if ($this->isSubmitted()) {

			$dati = $this->getFormdata();
			$array = $this->checkDataForm('photo_gallery', $dati);
			if ($array[0] == 'ok') {
				if ($action == 'edit') {
					$obj = PhotoGallery::withId($array['id']);
					$id = $array['id'];
				} else {
					$obj = PhotoGallery::create();
					//$id = _obj('Database')->execute("SELECT id FROM photoGallery ORDER BY id DESC")[0]['id'] + 1;
				}
				//$array['id'] = $id;
				
				//debugga($array);exit;
				$obj->set($array);
				$res = $obj->save();
				if( is_object($res) ){
					$id = $res->id;
					/*$database = Marion::getDB();
					$database->execute("DELETE FROM photoGalleryImage WHERE gallery = '$id'");
					$is_first = false;
					foreach(unserialize($array['images']) as $image) {
						if(!$is_first) {
							Marion::setConfig('photogallery_settings', $id, $image);
							$is_first = true;
						}
						$image_obj = PhotoGalleryImage::create();
						$image_obj->set([
							'image' => $image,
							'gallery' => $obj->id
						]);
						$image_obj->save();
					}
					*/
					
					if ($action == 'edit') {
						$this->redirectToList(array('updated' => $this->getId()));
					} else {
						$this->redirectToList(array('created' => $this->getId()));
					}
				}else{


					$this->errors[] = $res;
				}
				
				
			} else {
				$this->errors[] = $array[1];
			}
		} else {
			if ($action != 'add') {
				$id = $this->getId();
				$obj = PhotoGallery::withId($id);
				if (is_object($obj)) {
					$dati = $obj->prepareForm2();
					$list = PhotoGalleryImage::prepareQuery()->where('gallery',$obj->id)->get();
					foreach($list as $v){
						$dati['images'][] = $v->image;
					}
				}
			}
			if( $action == 'duplicate' ){
				unset($dati['id']);
			}
		}

		$dataform = $this->getDataForm('photo_gallery', $dati);
		$this->setVar('dataform', $dataform);

		$this->output('form_gallery.htm');
	}

	function displayList()
	{
		$this->setMenu('photogallery_settings');
		$this->setTitle('Gallerie immagini');
		$this->displayMessages();

		$fields = [
			0 => [
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',

			],
			1 => [

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

			2 => [

				'name' => '',
				'function_type' => 'row',
				'searchable' => false,
				'sortable' => false,
				'function' => function($row){
					$url = "index.php?mod=photogallery&ctrl=Image&id_gallery=".$row->id."&action=list";
					return "<a href='{$url}' class='btn btn-sm btn-default'><i class='fa fa-image'></i> immagini</a>";
				},
			
			],
		];


		$container = $this->getListContainer();


		$container->setFieldsFromArray($fields)
				->addEditActionRowButton()
				->addCopyActionRowButton()
				->addDeleteActionRowButton();
		$container->build();

		$this->getList();
		//$this->output('list_gallery.htm');
		parent::displayList();
	}


	function getList(){
		$limit = $this->getListContainer()->getPerPage();

		$query = DB::table('photo_gallery','p')
				->join('photo_gallery_lang as l','p.id','=','l.photo_gallery_id')
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

	function displayMessages()
	{
		if (_var('deleted')) {
			$this->displayMessage('Galleria eliminata con successo');
		}
		if (_var('created')) {
			$this->displayMessage('Galleria creata con successo');
		}
		if (_var('updated')) {
			$this->displayMessage('Galleria aggiornata con successo');
		}
	}

	function delete()
	{
		$id = $this->getId();
		$obj = PhotoGallery::withId($id);
		if (is_object($obj)) {
			$obj->delete();
		}
		$this->redirectToList(array('deleted' => $id));
	}
}
