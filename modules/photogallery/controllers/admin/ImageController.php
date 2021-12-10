<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use Photogallery\{PhotoGalleryImage,PhotoGallery};
use Illuminate\Database\Capsule\Manager as DB;
class ImageController extends AdminModuleController
{
    public $_auth = 'photogallery_manager';
    
	

	function displayList(){
		$this->setMenu('photogallery_settings');
        
		$id_gallery = _var('id_gallery');
		$gallery = PhotoGallery::withId($id_gallery);

        $this->setTitle("Immagini di <b>{$gallery->get('name')}</b>");
        
        $this->getToolButton('add')->setUrl('index.php?ctrl=Image&mod=photogallery&action=add&id_gallery='.$id_gallery);

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
				'name' => '',
				'function_type' => 'row',
                'function' => function($row){
                    $url = _MARION_BASE_URL_."img/".$row->image."/th/image.png";
                    return "<img src='{$url}'>";
                }

			],
			2 => [

				'name' => 'Nome',
				'field_value' => 'name',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'name',
				'search_name' => 'name',
				'search_value' => '',
				'search_type' => 'input',

			],

	
		];


		$container = $this->getListContainer();


		$container->setFieldsFromArray($fields)
				->addEditActionRowButton()
				->addCopyActionRowButton()
				->addDeleteActionRowButton();
		$container->build();

		//$this->setVar('images', $images);

        $this->getList($id_gallery);
		//$this->output('list_image.htm');
       
        parent::displayList();
	}


	function displayForm(){
		$this->setMenu('photogallery_settings');

		$action = $this->getAction();
		if ($this->isSubmitted()) {
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('photo_gallery_image', $dati);
			if ($array[0] == 'ok') {
				if( $action == 'edit' ){
                    $obj = PhotoGalleryImage::withId($array['id']);
                }else{
                    $obj = PhotoGalleryImage::create();
                }
               
                
				
				$obj->set($array);
                $obj->save();
                
                $this->redirectToList(['id_gallery'=>$obj->photo_gallery_id]);
			} else {
				$this->errors[] = $array[1];
			}
			
		} else {
            if( $action == 'edit'){
                $id = $this->getID();
                $obj = PhotoGalleryImage::withId($id);
			
			    $dati = $obj->prepareForm2();
            }else{

                $dati['photo_gallery_id'] = _var('id_gallery');
            }
			
		}
		$this->setTitle('Immagine gallery');
        

		$dataform = $this->getDataForm('photo_gallery_image', $dati);
		$this->setVar('dataform', $dataform);
		$this->output('form_image.htm');
	}



    function getList($id_gallery){
		$limit = $this->getListContainer()->getPerPage();

		$query = DB::table('photo_gallery_image','p')
				->join('photo_gallery_image_lang as l','p.id','=','l.photo_gallery_image_id')
				->where('lang','=',_MARION_LANG_);

        $query->where('photo_gallery_id','=',$id_gallery);
		
        if( $id = _var('id')){
			$query->where('id','=',$id);
		}
		if( $name = _var('name')){
			$query->where('name','LIKE',"%{$name}%");
		}
		
		$tot = $query->count();
		
		if( $tot > 0){
			$query->limit($limit);
			if( $page_id = _var('pageID') ){
				$query->offset((($page_id-1)*$limit));
				
			}
			$list = $query->select(['id','name','image'])->get()->toArray();
			$this->getListContainer()->setDataList($list);
		}
		$this->getListContainer()->setTotalItems($tot);
		

	}


    function delete()
	{
		$id = $this->getId();
		$obj = PhotoGalleryImage::withId($id);
		if (is_object($obj)) {
			$obj->delete();
		}
		$this->redirectToList(array('deleted' => $id,'id_gallery'=>$obj->photo_gallery_id));
	}
}
