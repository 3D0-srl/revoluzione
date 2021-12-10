<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use Illuminate\Database\Capsule\Manager as DB;
use ListActionBulkButton as GlobalListActionBulkButton;
use Marion\Controllers\Elements\ListActionBulkButton;

class SliderController extends AdminModuleController{
	public $_auth = 'cms';
	
	private $path = _MARION_MODULE_DIR_."widget_revslider/sliders/";
	

	function displayList(){
        $this->setMenu('revolution_sliders');
        $this->showMessage();
        
        $this->checkFunctions();
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
				'field_value' => 'title',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'title',
				'search_name' => 'title',
				'search_value' => '',
				'search_type' => 'input',

			],

		];

        $bulkDeleteAction = (new ListActionBulkButton('delete'))->setText('elimina')->setIcon('fa fa-trash-o');
        
		
		$this->setTitle('Revolution sliders');
		$this->getListContainer()
			->setFieldsFromArray($fields)
            ->addActionBulkButton($bulkDeleteAction)
            ->addDeleteActionRowButton()
			->build();
		$this->getList();
		parent::displayList();
	}


	function checkFunctions(){
		if( !class_exists('ZipArchive') ){
			$this->errors[] = "<b>ZipArchive</b> non è installato. Occorre installare questa estensione di php per poter caricare nuovi slider.";

		}
        if( file_exists($this->path) ){
            if ( !is_writable($this->path)) {

                $this->errors[] = "La cartella <b>sliders</b> presente all'interno della root del modulo deve essere scrivibile";
            } 
        }else{
            $this->errors[] = "Occorre create una cartella <b>sliders</b> nella root del modulo";
        }
	}

	function displayForm(){
		$this->setMenu('revolution_sliders');
		
		$database = Marion::getDB();

		
		

		$this->checkFunctions();
		if( $this->isSubmitted()){

			
			$id = $database->insert('revolution_slider',array('content'=>''));
			$this->saveSlider('slider_'.$id);
			$dati = $this->parseSlider('slider_'.$id);

			foreach($dati as $k => $v){
				if( is_array($v) ){
					$update[$k] = serialize($v);
				}else{
					$update[$k] = $v;
				}

				$database->update('revolution_slider',"id={$id}",$update);
			}
			$this->redirectToList(['saved'=>1]);
		}
        $this->setTitle('Revolution Slider');
		$this->output('form.htm');
	}
	

	function object_to_array($data)
	{
		if (is_array($data) || is_object($data))
		{
			$result = array();
			foreach ($data as $key => $value)
			{
				$result[$key] = $this->object_to_array($value);
			}
			return $result;
		}
		return $data;
	}

	function rrmdir($dir) { 
	   if (is_dir($dir)) { 
		 $objects = scandir($dir); 
		 foreach ($objects as $object) { 
		   if ($object != "." && $object != "..") { 
			 if (is_dir($dir."/".$object))
			   $this->rrmdir($dir."/".$object);
			 else
			   unlink($dir."/".$object); 
		   } 
		 }
		 rmdir($dir); 
	   } 
	 }

	 function parseSlider($path){
		
		//debugga($_SERVER);exit;

		
		$file = $this->path.$path.'/slider.html';
		if( !file_exists($file) ) return false;
		$doc = new \DOMDocument();
		$doc->loadHTMLFile($file);
		
		
		$title = $doc->getElementsByTagName('title')[0]->textContent;
		
		
		/*foreach ($doc->childNodes as $item){
			debugga($item);
		}*/
		$head = $doc->getElementsByTagName('head')[0];

		$scripts = $head->getElementsByTagName('script');
		
		for ($i = 0; $i < $scripts->length; $i++)
		{
			$script = $scripts->item($i);
			
			if( $script->textContent){
				$js[$i]['content'] = $script->textContent;
			}else{
				$js[$i]['url'] = $script->getAttribute('src');
			}
			//echo $i . ": " . $script->getAttribute('src') . "<br />";
		}

		$scripts = $head->getElementsByTagName('link');
		
		for ($i = 0; $i < $scripts->length; $i++)
		{
			$script = $scripts->item($i);
			
			
			$css[]['url'] = $script->getAttribute('href');
			
			//echo $i . ": " . $script->getAttribute('src') . "<br />";
		}
		
		$scripts = $head->getElementsByTagName('style');
		
		for ($i = 0; $i < $scripts->length; $i++)
		{
			$script = $scripts->item($i);
			
			if( trim($script->textContent) ){
				$css[]['content'] = $script->textContent;
			}
			
			//echo $i . ": " . $script->getAttribute('src') . "<br />";
		}

		$body = $doc->getElementsByTagName('body')[0];
		
		$content = $doc->saveHTML($body);
		
		$head_path_css =  $this->path.'/'.$path."/head_css";
		$head_path_js =  $this->path.'/'.$path."/head_js";
		mkdir($head_path_js,0777,true);
		mkdir($head_path_css,0777,true);
		foreach($js as $ind =>$v){
			
			if( $v['content'] ){
				
				$script_file_path =  $this->path.'/'.$path."/head_js/script".$ind.".js";
				$script_file = "head_js/script".$ind.".js";
				$myfile = fopen($script_file_path, "w");
				
				fwrite($myfile, $v['content']);
				
				fclose($myfile);
				unset($js[$ind]['content']);
				$js[$ind]['url'] = $script_file;
			}
			
			
		}

		foreach($css as $ind =>$v){
			
			if( $v['content'] ){
				
				$script_file_path =  $this->path.'/'.$path."/head_css/style".$ind.".css";
				$script_file = "head_css/style".$ind.".css";
				$myfile = fopen($script_file_path, "w");
				
				fwrite($myfile, $v['content']);
				
				fclose($myfile);
				
				unset($css[$ind]['content']);
				$css[$ind]['url'] = $script_file;
			}
			
			
		}

		
		//$content =$this->relToAbs($content,'/modules/widget_revslider/sliders/'.$path."/");

		$content = preg_replace('/assets/',_MARION_BASE_URL_.'modules/widget_revslider/sliders/'.$path."/assets",$content);
		
		$dati = array(
			'title' => $title,
			'js' => $js,
			'css' => $css,
			'content' => $content
		);

		
		

		return $dati;


		
	}



	function saveSlider($dest_base){
		
		//debugga($_FILES);exit;
		
		if( okArray($_FILES['file']) ){
				
				$file = $_FILES['file'];
				
				
					
				$this->rrmdir($this->path.$dest_base);
				mkdir($this->path.$dest_base,0777,true);

				
				$zip = new ZipArchive;
				
				if ($zip->open($file['tmp_name']) === TRUE) {
					$zip->extractTo($this->path.$dest_base);
					$zip->close();
					
				} else {
				}
				
				
		}


	}


	
	
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Slider salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Slider eliminato con successo','success');
		}
	}

	function saved(){
		$this->redirectTolist(array('saved'=>1));
	}


	function delete(){
		
		$id = $this->getID();
		
	    $this->deleteSlider($id);
		

		$this->redirectToList();
		

		
	}

    function deleteSlider($id){

		$this->rrmdir($this->path.'slider_'.$id);
		DB::table('revolution_slider')->delete($id);
    }


	function getList(){
		$limit = $this->getListContainer()->getPerPage();

		$query = DB::table('revolution_slider');

		if( $id = _var('id')){
			$query->where('id','=',$id);
		}
		if( $title = _var('title')){
			$query->where('title','LIKE',"%{$title}%");
		}
		
		$tot = $query->count();
		
		if( $tot > 0){
			$query->limit($limit);
			if( $page_id = _var('pageID') ){
				$query->offset((($page_id-1)*$limit));
				
			}
			$list = $query->select(['id','title'])->get()->toArray();
			$this->getListContainer()->setDataList($list);
		}
		$this->getListContainer()->setTotalItems($tot);
		
		
				
		
		
	}

	function sliders(){
		$database = Marion::getDB();
		$list = $database->select('*','revolution_slider');
		if( okArray($list) ){
			foreach($list as $v){
				$toreturn[$v['id']] = $v['title'];
			}
		}
		return $toreturn;
	}



    function bulk(){
        $action = $this->getBulkAction();
        $ids = $this->getBulkIds();
        switch($action){
            case 'delete':
                foreach($ids as $id){
                    $this->deleteSlider($id);
                }
                break;
        }

        parent::bulk();
    }

}



?>