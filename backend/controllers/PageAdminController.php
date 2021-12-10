<?php
use Marion\Core\Marion;
use Marion\Entities\Cms\{Page,PageComposer};
use Marion\Utilities\PageComposerTools;
class PageAdminController extends \Marion\Controllers\AdminController{
	public $_auth = 'cms_page';

	
	function import(){
		if( okArray($_FILES) ){
			

			$dir = $this->tempdir();
			$file = $_FILES['file']['tmp_name'];
			$zip = new ZipArchive;
			if ($zip->open($file) === TRUE) {
				$zip->extractTo($dir);
				$zip->close();
			} 
			
			if( file_exists($dir."/list_pages.json")){
				$list = json_decode($dir."/list_pages.json",true);
				foreach($list as $v){
					if( file_exists($dir."/".$v.".zip")){
						$id = PageComposerTools::import($dir."/".$v.".zip");
					}
				}
				header('Location: index.php?ctrl=PageAdmin&action=list');
			}else{
				$id = PageComposerTools::import($_FILES['file']['tmp_name'],$dir);
				header('Location: index.php?ctrl=PageAdmin&action=edit&id='.$id);
			}
			exit;
		}
		
		$this->output('page/import.htm');
	}

	function displayContent(){
		$this->setMenu('cms_page');
		$action = $this->getAction();
		switch($action){
			case 'edit_page':
				$url = _var('url');
				$return_location = _var('return_location');
				$page = Page::prepareQuery()->where('url',$url)->getOne();
				
				if( is_object($page) ){
					header('Location: '.$this->getUrlScript()."&action=edit&id=".$page->id."&return_location=".$return_location);
					exit;
				}
				break;
			case 'import':
				$this->import();
				break;
			case 'export':
				$id = _var('id');
				PageComposerTools::export($id);
				exit;
				break;
		}
		
	}

	function displayForm(){
			$this->setMenu('cms_page');
			
			$type = _var('type');

			
			

			$url = _var('url');
			$action = $this->getAction();

			if( $action == 'add'){
				$this->setMenu('page_add');

			}


			if(	$this->isSubmitted()){
				


				$dati = $this->getFormdata();
				
				if( $dati['widget'] ){
					$campi_aggiuntivi['title']['obbligatorio'] = 0;
				}
				
				if( $dati['id'] ){
					$this->setVar('class_widget2',"hide_widget");
				}

				if( $dati['id'] && $dati['widget']){
					$this->setVar('class_widget2',"hide_widget");
					$this->setVar('widget',1);
					if( Marion::auth('superadmin') ){
						$this->setVar('superadmin',1);
					}
				}

				if( !Marion::auth('superadmin') ){
					$dati['theme'] = Marion::getConfig('SETTING_THEMES','theme');
				}
				
				$array = $this->checkDataForm('page',$dati,$campi_aggiuntivi);
				
				if( $array[0] == 'ok'){

					
					if( $action == 'edit'){
						$page = Page::withId($array['id']);
						if( !$page ) $this->error(203);
					}else{
						$page = Page::create();
					}
					$page->setLayout($array['layout']);

					$page->set($array);
					
					$res = $page->save();

					if( is_object($res) ){

						if( $action == 'duplicate'){

							if( $res->advanced ){
								Marion::do_action('pagecomposer_duplicate',array($array['id_old_adv_page'],$res->id_adv_page));
							}
							
						}

						if( $array['return_location'] ){
							header('Location:'.$array['return_location']);
						}else{
							$this->redirectToList(array('saved'=>1));
						}
						
						
					}else{
						if( __($res) ){
							$this->errors[] = __($res);
						}else{
							$this->errors[] = $res;
						}
					}
					
				}else{
					$this->errors[] = $array[1];
					

				}
				

			}else{
				if( $action == 'add' && !$type ){
					$this->output('page/new_page.htm');
					exit;
				}
				if( $action == 'edit' || $action == 'duplicate'){
					$id = $this->getID();
					
					
					if( !$id && !$url) $this->error(201);
					if( $id ){
						$pagina = Page::withId($id);
					}else{
						$pagina = Page::prepareQuery()->where('url',$url)->getOne();
					}
					if( !$pagina ) $this->error(202);
					
					$dati = $pagina->prepareForm2();

					
					if( $dati['id'] ){
						$this->setVar('class_widget2',"hide_widget");
					}
					
					if( $dati['widget'] ){
						$this->setVar('class_widget2',"hide_widget");
						$this->setVar('widget',1);
						if( Marion::auth('superadmin') ){
							$this->setVar('superadmin',1);
						}
					}
					
					if( $action == 'edit' && $dati['id_adv_page'] ){
						$database = _obj('Database');
						$check = $database->select('*','page_advanced',"id={$dati['id_adv_page']}");
						$dati['layout'] = $check[0]['id_layout'];
					}

					
					if( $action == 'duplicate'){
						$dati['id_old_adv_page'] = $dati['id_adv_page'];
						
						unset($dati['id']);
						unset($dati['locked']);
						//$action = 'add_page';
					}
				}else{
					
					switch($type){
						case 'content':
							$dati['widget'] = 1;
							break;
						case 'page_adv':
							$dati['advanced'] = 1;
							
							break;
						default:
							$dati['advanced'] = 0;
							break;

					}

					$theme = Marion::getConfig('SETTING_THEMES','theme');
					$dati['theme'] = $theme;
				}
				
				$dati['return_location'] = _var('return_location');
			}
			
			$dataform = $this->getDataForm('page',$dati);
			
			$this->setVar('dataform',$dataform);
			$this->output('page/form.htm');	

			
			
		

	}


	function getList(){
		$db  = Marion::getDB();
		$lang = _MARION_LANG_;
		$condizione = "locale = '{$lang}' AND ";
		
		
		$limit = $this->getListContainer()->getPerPage();
		
		if( $title = _var('title') ){
			$condizione .= "title LIKE '%{$title}%' AND ";
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		
		if( isset($_GET['visibility']) && $_GET['visibility'] != -1 ){
			$visibility = _var('visibility');
			$condizione .= "visibility = {$visibility} AND ";
		}
		
		if( $url = _var('url') ){
			$condizione .= "url LIKE '%{$url}%' AND ";
		}
		
		if( $type = _var('type') ){
			switch($type){
				case 'widget':
					$condizione .= "widget = 1 AND ";
					break;
				case 'advanced':
					$condizione .= "advanced = 1 AND ";
					break;
				case 'standard':
					$condizione .= "advanced = 0 AND ";
					break;
			}
			
		}
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $db->select('count(*) as tot','page as p join pageLocale as l on l.page=p.id',$condizione);
		
		
		//$condizione .= " AND locale = '{$GLOBALS['activelocale']}'";

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}else{
			
			$condizione .= " ORDER BY id DESC";
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		
		$list = $db->select('id,title,url,visibility,advanced,id_adv_page,locked,widget,page','page as p join pageLocale as l on l.page=p.id',$condizione);
	

		
		$total_items = $tot[0]['tot'];
		$container = $this->getListContainer();
		$container->setTotalItems($total_items);
		if( $total_items ){

				$container->setDataList($list);	
		}
		
	}

	function displayList(){
		$this->setMenu('cms_page');
		$this->setTitle('Pagine');

		$this->addToolButton((new \Marion\Controllers\Elements\UrlButton('import'))
			->setText(_translate('import'))
			->setIcon('fa fa-upload')
			->setUrl('index.php?ctrl=PageAdmin&action=import')
			->setClass('btn btn-principale btn-info')
		);

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
				'name' => 'url',
				'field_value' => 'url',
				'function_type' => 'value',
				'function' => '',
				'sortable' => true,
				'sort_id' => 'url',
				'searchable' => true,
				'search_name' => 'url',
				'search_value' => _var('url'),
				'search_type' => 'input',
			),
			2 => array(
				'name' => 'titolo',
				'field_value' => 'title',
				'function_type' => 'value',
				'function' => '',
				'sortable' => true,
				'sort_id' => 'title',
				'searchable' => true,
				'search_name' => 'title',
				'search_value' => _var('title'),
				'search_type' => 'input',
			),
			3 => array(
				'name' => 'tipologia',
				'field_value' => 'type',
				'function_type' => 'row',
				'function' => function($row){
					if( _var('export') ){
						if ($row['widget'] ){
							$html = strtoupper(_translate('widget'));
						}else{
							if ($row['advanced'] ){
								$html =strtoupper(_translate('advanced_page'));
							}else{
								$html = strtoupper(_translate('standard_page'));
							}
						}
					}else{
						if ($row['widget'] ){
							$html = "<span class='label label-warning'>".strtoupper(_translate('widget'))."</span>";
						}else{
							if ($row['advanced'] ){
								$html = "<span class='label label-info'>".strtoupper(_translate('advanced_page'))."</span>";
							}else{
								$html = "<span class='label label-success'>".strtoupper(_translate('standard_page'))."</span>";
							}
						}
					}
		
					return $html;
				},
				//'sortable' => true,
				//'sort_id' => 'type',
				'searchable' => true,
				'search_name' => 'type',
				'search_value' => _var('type'),
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					'widget' => 'widget',
					'advanced' => 'avanzata',
					'standard' => 'standard',
					
				),
			),
			4 => array(
				'name' => 'visibilità',
				'field_value' => 'visibility',
				'function_type' => 'row',
				'function' => function($row){
					if( _var('export') ){
						if ($row['visibility'] ){
							$html = strtoupper(_translate('online'));
						}else{
							$html = strtoupper(_translate('offline'));
						}
					}else{
						if ($row['visibility'] ){
							$html = "<span class='label label-success'>".strtoupper(_translate('online'))."</span>";
						}else{
							$html = "<span class='label label-danger'>".strtoupper(_translate('offline'))."</span>";
						}
					}
		
					return $html;
				},
				'searchable' => true,
				'search_name' => 'visibility',
				'search_value' =>(isset($_GET['visibility']))? _var('visibility'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => 'seleziona..',
					0 => 'offline',
					1 => 'online',
					
				),
			),
			5 => array(
				'name' => 'link',
				'function_type' => 'row',
				'function' => function($row){
					return "<a class='btn btn-sm btn-default' target='_blank' href='"._MARION_BASE_URL_."p/".$row['url'].".htm'><i class='fa fa-link'></i></a>";
				},
			),

		);
		

		$container = $this->getListContainer();
		$container->setFieldsFromArray($fields)
				->addEditActionRowButton()
				->addCopyActionRowButton()
				->addDeleteActionRowButton()
				->addActionRowButton(
					(new \Marion\Controllers\Elements\ListActionRowButton('setting'))
					->setEnableFunction(function($row){
						return $row['advanced'];
					})
					->setUrlFunction(function($row){
						return "index.php?ctrl=PageComposerAdmin&mod=pagecomposer&id={$row['id_adv_page']}";
					})
					->setText('composer')
					->setIconType('icon')
					->setIcon('fa fa-magic')
				)
				->addActionRowButton(
					(new \Marion\Controllers\Elements\ListActionRowButton('export'))
					->setEnableFunction(function($row){
						return $row['advanced'];
					})
					->setUrlFunction(function($row){
						return "index.php?ctrl=PageAdmin&action=export&id={$row['id']}";
					})
					->setText('esporta')
					->setIconType('icon')
					->setIcon('fa fa-download')
				)
				->addActionBulkButton(
					(new \Marion\Controllers\Elements\ListActionBulkButton('export'))
					->setConfirm(true)
					->setConfirmMessage("Sicuro di voler procedere con questa operazione?")
					->setText('esporta')
					->setIconType('icon')
					->setIcon('fa fa-download')
				)->build();
		
		
		$this->getList();


		parent::displayList();
	}


	function bulk(){
		$action = $this->getBulkAction();
		switch($action){
			case 'delete':
				$list = $this->getBulkIds();
				foreach($list as $v){
					$page = Page::withId($v);
					if( is_object($page) ){
						$page->delete();
					}
				}
				break;
				case 'export':
					$list = array();
					$list = $this->getBulkIds();
					$dir = $this->tempdir();
					mkdir($dir."/pages");
					foreach($list as $v){
						$list[] = $v;
						PageComposerTools::export($v,$dir."/pages/".$v.".zip");
					}
					file_put_contents($dir."/pages/list_pages.json",json_encode($list));
					$path_zip = $dir."/pages.zip";
					PageComposerTools::Zip($dir."/pages",$path_zip);
					$file_name = basename($path_zip);

					header("Content-Type: application/zip");
					header("Content-Disposition: attachment; filename=$file_name");
					header("Content-Length: " . filesize($path_zip));

					readfile($path_zip);
					unlink($path_zip);
					exit;
					
			break;
		}

		parent::bulk();
	}

	function tempdir() {
		$tempfile=tempnam(sys_get_temp_dir(),'');
		// you might want to reconsider this line when using this snippet.
		// it "could" clash with an existing directory and this line will
		// try to delete the existing one. Handle with caution.
		if (file_exists($tempfile)) { unlink($tempfile); }
		mkdir($tempfile);
		if (is_dir($tempfile)) { return $tempfile; }
	}
	

	function isAdvancedPage($row){
		return $row['advanced'];
	}


	function delete(){
		$id = $this->getID();

		$obj = Page::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));
		

		
	}

}



?>