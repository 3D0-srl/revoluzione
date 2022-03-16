<?php
use Marion\Controllers\AdminModuleController;
use Illuminate\Database\Capsule\Manager as DB;
use Elearning\CourseUnit;
use Marion\Controllers\Elements\UrlButton;
use Marion\Controllers\Elements\ListActionBulkButton;

class UnitsController extends AdminModuleController{	
	public $auth=""; //permesso per accedere al controller

	function setMedia()
	{
		
		parent::setMedia();
		$this->registerJS(_MARION_BASE_URL_.'modules/elearning/js/plupload-3.1.5/js/plupload.full.min.js');
	}


	function upload(){
		 // (B) INVALID UPLOAD
		 if (empty($_FILES) || $_FILES["file"]["error"]) {
			$this->verbose(0, 'Errore');
		  }
		  
		  // (C) UPLOAD DESTINATION - CHANGE FOLDER IF REQUIRED!
		  $filePath = _MARION_MODULE_DIR_.'elearning' . DIRECTORY_SEPARATOR . "uploads";
		  if (!file_exists($filePath)) { if (!mkdir($filePath, 0777, true)) {
			$this->verbose(0, "Failed to create $filePath");
		  }}
		  
		  $fileName_tmp = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
		  $filename_pretty = $this->makeFriendly($fileName_tmp);
		  //$fileName = $this->makeFriendly(basename($this->verifica_duplicati($fileName_tmp,$filePath)));
		  $fileName = basename($this->verifica_duplicati($filename_pretty,$filePath));
		  
		  
		  //$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
		  
		  $filePath = $filePath . DIRECTORY_SEPARATOR . $fileName;
		  
		  // (D) DEAL WITH CHUNKS
		  $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		  $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		  $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
		  if ($out) {
			$in = @fopen($_FILES["file"]["tmp_name"], "rb");
			if ($in) { while ($buff = fread($in, 4096)) { fwrite($out, $buff); } }
			else { $this->verbose(0, "Failed to open input stream"); }
			@fclose($in);
			@fclose($out);
			@unlink($_FILES["file"]["tmp_name"]);
		  } else { $this->verbose(0, "Failed to open output stream"); }
		  
		  // (E) CHECK IF FILE HAS BEEN UPLOADED
		  if (!$chunks || $chunk == $chunks - 1) { 
			  $id = DB::table('course_video')->insertGetId(
				  [
					  'path' => $fileName,
					  'name' => $fileName_tmp,
				  ]
			  );
			  rename("{$filePath}.part", $filePath); 
		  }
		  if( isset($id) ){
			$this->verbose(1, $id);
		  }else{
			$this->verbose(1, 0);
		  }
		  
	}


	function displayContent()
	{
		
		  $action = $this->getAction();
		  switch($action){
			  case 'upload':
				$this->upload();
				break;
		  }
		 
		  
		  
		  
	}

	/*
	*	displayForm
	*	Metodo richiamato per mostrare il form
	*/
	public function displayForm(){
		$this->setMenu("elearning_corsi"); //attiva la voce di menu del backend.
		$action = $this->getAction(); // valori ammessi "add","edit"
		
		if( $this->isSubmitted() ){
			//il form è stato sottomesso

			//prendo i dati del POST
			$data = $this->getFormdata();
			
			//controllo i dati con un form di controllo opportunamente creato
			$check = $this->checkDataForm("elearning_unita",$data);

			
			if( $check[0] == "nak"){
				//se ci sono errori passo l errore al template
				$this->errors[] = $check[1];
			}else{
				
				//salvataggio dati
				if( $action == "add" ){
					$unit = CourseUnit::create();
				}else{
					$unit = CourseUnit::withId($check['id']);
				}
				//debugga($unit);exit;
				$unit->set($check);
				$unit->save();
				if( $action == 'edit' ){
					$this->redirectToList(['changed'=>true,'id_course'=> $unit->course_id]);
				}else{
					$this->redirectToList(['saved'=>true,'id_course'=> $unit->course_id]);
				}

			}
		}else{

			if( $action != "add" ){
				// popolo il form con i dati presenti nel db
				$data = [];
				$id = $this->getID();
				if( $id ){
					$unit = CourseUnit::withId($id);
					if( $unit) {
						$data = $unit->prepareForm2();
					}
					if( $data['video_id'] ){
						$video = DB::table('course_video')->where('id',$data['video_id'])->first();
						if( $video ){
							$this->setVar('video',$video);
						}
						
					}
				}
				
			}else{
				$data = [
					'course_id' => _var('id_course')
				];
			}
		}
		
		$this->setVar('id_course',$data['course_id']);
		
		$dataform = $this->getDataForm("elearning_unita",$data);
		$this->setVar("dataform",$dataform);
		$this->output("form_unit.htm"); 
	}

	private function getList(){
		$limit = $this->getListContainer()->getPerPage();
		

		$query = DB::table('course_unit','c')
			->join('course_unit_lang as l','l.course_unit_id','=','c.id')
			->leftJoin('course_video as v','c.video_id','=','v.id')
			->where('lang',_MARION_LANG_)
			->where('course_id',_var('id_course'));
		
		
		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$query->orderBy($order,$order_type);
		}
		if( $id = _var('id') ){
			$query->where('c.id',$id);
		}
		if( $order_view = _var('order_view') ){
			$query->where('order_view',$order_view);
		}
		if( $name = _var('name') ){
			$query->where('name','like',"%{$name}%");
		}
		if( $title = _var('title') ){
			$query->where('title','like',"%{$title}%");
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
				[	'c.id',
					'title',
					'order_view',
					'name'
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

	/*
	*	displayList
	*	Metodo richiamato per mosstrare la lista
	*/
	public function displayList(){
		$this->setMenu("elearning_corsi"); //attiva la voce di menu del backend.


		$this->resetToolButtons();
	
		$this->addToolButtons(
			[
				(new UrlButton('add'))
				->setText('Indietro')
				->setIcon('fa fa-arrow-left')
				->setClass('btn btn-secondario')
				->setUrl('index.php?mod=elearning&ctrl=Course&action=list'),
				(new UrlButton('back'))
				->setText('Aggiungi')
				->setIcon('fa fa-plus')
				->setClass('btn btn-principale m-t-10')
				->setUrl('index.php?mod=elearning&ctrl=Units&action=add&id_course='._var('id_course'))

			]
			
		);

		if( _var('bulk_success') ){

		}else{
			if( _var('saved') ){
				$this->displayMessage('Unità inserita con successo');
			}
			if( _var('changed') ){
				$this->displayMessage('Unità aggiornata con successo');
			}
			if( _var('deleted') ){
				$this->displayMessage('Unità eliminata con successo');
			}
		}

		$id_corso = _var('id_course');
		$product = Product::withId($id_corso);
		if( is_object($product)){
			$this->setTitle('Unità del corso <b>'.$product->get('name')."</b>");
		}
		
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
						'name' => 'title',
						'field_value' => 'title',
						'searchable' => true,
						'sortable' => true,
						'sort_id' => 'title',
						'search_name' => 'title',
						'search_value' => _var('title'),
						'search_type' => 'input',
					],
					[
						'name' => 'video',
						'field_value' => 'name',
						'searchable' => true,
						'sortable' => true,
						'sort_id' => 'name',
						'search_name' => 'name',
						'search_value' => _var('name'),
						'search_type' => 'input',
					],
					
					[
						'name' => 'ordine visual.',
						'field_value' => 'order_view',
						'searchable' => true,
						'sortable' => true,
						'sort_id' => 'order_view',
						'search_name' => 'order_view',
						'search_value' => _var('order_view'),
						'search_type' => 'input',
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
	function bulk()
	{
		
		$action = $this->getBulkAction();
		switch($action){
			case 'delete':
				$ids = $this->getBulkIds();
				foreach($ids as $id){
					$unit = CourseUnit::withId($id);
					if( is_object($unit)){
						$unit->delete();
					}
				}
				break;
		}
		parent::bulk();
	}

	function verifica_duplicati($file, $basedir) {
		$nomefile = $basedir . '/'. $file;
		if (file_exists($nomefile)) {
			$pf = $this->pathinfo_filename($nomefile);
			if (empty($pf['extension'])) $pf['extension'] = 'bin';
	
			if (preg_match('/([[:print:]]+)\_\((\d+)\)$/', $pf['filename'], $matches)) {
				$pf['filename'] = $matches[1] . '_('. ($matches[2]+1) .')';
			} else {
				$pf['filename'] .= '_(1)';
			}
	
			$pf['filename'] .= '.'.$pf['extension'];
	
			return $this->verifica_duplicati($pf['filename'], $basedir);
		}
		return $nomefile;
	}
	
	
	function pathinfo_filename($path) {
		$temp = pathinfo($path);
		if ($temp['extension']) {
			$temp['filename'] = substr($temp['basename'],0 ,strlen($temp['basename'])-strlen($temp['extension'])-1);
		}
		return $temp;
	}

	function verbose ($ok=1, $info="") {
		if ($ok==0) { http_response_code(400); }
		exit(json_encode(["ok"=>$ok, "id"=>$info]));
	}


	function makeFriendly($string)
	{
		$explode = explode('.',$string);
		$string = $explode[0];
		$string = strtolower(trim($string));
		$string = str_replace("'", '', $string);
		$string = preg_replace('#[^a-z\-]+#', '_', $string);
		$string = preg_replace('#_{2,}#', '_', $string);
		$string = preg_replace('#_-_#', '-', $string);
		return preg_replace('#(^_+|_+$)#D', '', $string).".".$explode[1];
	}
	  


}
?>
