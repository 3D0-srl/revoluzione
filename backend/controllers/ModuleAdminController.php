<?php
use Marion\Core\Marion;
use Marion\Core\Module;
class ModuleAdminController extends \Marion\Controllers\AdminController{
	public $_auth = 'config';

	public $_enable_market = false;
	public $todo = array(

	);
	
	

	function getAccountModules(){

		$list = array(
			'bonifico',
			'paypal',
			'gls'
		);
		return  $list;
	}

	function getMarketModules(){

		$list = array(
			'paypal' => array(
				'id' => '10',
				'price' => '10',
				'currency' => 'EUR'
			)
		);
		return  $list;
	}


	function displayForm(){
		$this->setMenu('add_module');
		
		$action = $this->getAction();

		if( $_FILES['file']['name'] ){
			if( $_FILES['file']['type'] == 'application/zip' || $_FILES['file']['type'] == 'application/x-zip-compressed'){
				
				$file = $_FILES['file']['tmp_name'];
				$name = preg_replace('/\.zip/','',$_FILES['file']['name']);
				
				$zip = new ZipArchive;
				$res = $zip->open($file);
				if ($res === TRUE) {
				  $zip->extractTo('../modules/');
				  $zip->close();
				  $chmod = "0777";
				  chmod('modules/'.$name, octdec($chmod));
				  
				   Marion::chmod_R('../modules/'.$name, 0777, 0777);

				  
				} else {
				 $this->errors[] ="Si è verificato un errore";
				 
				}
			}else{
				 $this->errors[] ="Il file da caricare deve essere .zip";
				 
			}
		}

		$this->output('module/form.htm');

	}


	function setMedia(){
		if( $this->getAction() == 'list'){
			$this->registerJS($this->getBaseUrlBackend().'js/modules.js?v=2','end');
		}
	}

	function displayList(){
		$this->setMenu('manage_modules');
		$this->showMessage();
		

		

		$database = Marion::getDB();
		$type=_var('type');

		if( !$type ){
			$type = 'all';
		}		
		$search = _var('search');
		$this->setVar('search',$search);
		$user = Marion::getUser();
		
		if( $user->auth('superadmin')){
			if( $type == 'all'){
				$modules_default = $database->select('*','module',"default_module=1");
				
				foreach($modules_default as $k => $v){
					$modules[] = $v;
				}
			}
		}
		$this->setVar('gruppo',$type);
		switch($type){
			case 'payment':
				$this->setVar('titolo',"Metodi di pagamento");
				break;
			case 'cms':
				$this->setVar('titolo',"CMS");
				break;
			case 'ecommerce':
				$this->setVar('titolo',"Ecommerce");
				break;
			case 'catalog':
				$this->setVar('titolo',"Catalogo");
				break;
			case 'newsletter':
				$this->setVar('titolo',"Newsletter");
				break;
			default:

				break;

		}
		
		$modules_db = $database->select('*','module',"default_module=0");
		//debugga($modules_db);exit;
		foreach($modules_db as $v){
			$list_module[$v['id']] = $v;
			$id_module_v3[$v['directory']] = $v['id'];
		}
		
		$list = scandir('../modules'); 
		
		foreach($list as $k => $v){
			if( $v != '.' && $v!= '..'){
				if($v == 'module_starter') continue;
				if($v == 'widget_starter') continue;
				$file = '../modules/'.$v."/config.xml"; 
				if( file_exists($file) ){
					$data_xml = simplexml_load_file($file);


					
					$data = (array)$data_xml->info;
					$compatibility = $this->checkCompatibility($data);
					
					if( !isset($data['version']) || (isset($data['version']) && !$data['version']) ) $data['version'] = Module::$latest;
					if( $data['version'] == 3 ){
						$data['id'] = $id_module_v3[$data['tag']];
					}
					if( (int)$data['id'] == 0 && (int)$data['version'] < 3 ) continue;
					if( $search ){
						
						if ( !preg_match("/{$search}/i",$data['name']) ){
							continue;
						}
					}
					if( is_object($data['description']) ){
						$data['description'] = $data['description']->__toString();
						
					}
					if( $type != 'all' ){
						
						if(!$data['kind'] || $data['kind'] != $type ){
							//debugga($data);exit;
							continue;
						}
					}

					$data['img'] = "../modules/".$data['tag']."/img/logo.png";
					if(isset($data_xml->linkSetting)){
						$link_setting = (array)$data_xml->linkSetting;
						if( okArray($link_setting) ){
							if( preg_match('/mod=/',$link_setting[0])){
								$data['link_setting'] = trim($link_setting[0]);
							}else{
								$data['link_setting'] = "/admin/modules/".$v."/".trim($link_setting[0]);
							}
						}
					}
					
					if( array_key_exists($data['id'],$list_module) ){
						$list_module[$data['id']]['installed'] = 1;
						$list_module[$data['id']]['dir_module'] = $v;
						$list_module[$data['id']]['version'] = $data['version'];
						$list_module[$data['id']]['compatibility'] = $compatibility;
						$list_module[$data['id']]['img'] ="../modules/".$v."/img/logo.png";
						if( isset($data['link_setting']) ){
							$list_module[$data['id']]['link_setting'] = $data['link_setting'];
						}
						$modules[] = $list_module[$data['id']];
					}else{
						$data['dir_module'] = $v;
						$data['compatibility'] = $compatibility;
						$data['img'] = "../modules/".$data['dir_module']."/img/logo.png";
						$modules[] = $data;
					}

					
				}
			}
		}
		$this->setVar('enable_market',$this->_enable_market);
		if($this->_enable_market ){

			$modules_account = $this->getAccountModules();
			$info_modules = $this->getMarketModules();
			$this->setVar('info_modules',$info_modules);
			$this->setVar('modules_account',$modules_account);
		}
		
		
		
		$this->setVar('modules',$modules);
		$this->output('module/list.htm');
			
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





	function ajax(){
		
		$action = $this->getAction();
		$id = $this->getID();
		switch($action){
			case 'enable_module_default':
				$database = Marion::getDB();
				$module = $database->select('*','module',"id={$id}");
				if( okArray($module) ){
					$module = $module[0];
					$tag = $module['tag'];
					if( $tag == 'cms_page' || $tag == 'cms_news' || $tag == 'cms_gallery' ){
						$database->update('menuItem',"tag='{$tag}'",array('active'=>1));
						$database->update('menuItem',"tag='page'",array('active'=>1));
						$database->update('permission',"label='cms'",array('active'=>1));
						
					}else{
						$menu = $database->select('*','menuItem',"tag='{$tag}' and (parent=0 or parent is null)");
						if( okArray($menu) ){
							$id_menu = $menu[0]['id'];
							$database->update('menuItem',"id={$id_menu} OR parent = {$id_menu}",array('active'=>1));
						}
						if( $tag == 'catalogo'){
							$tag = 'attribute';
							$menu = $database->select('*','menuItem',"tag='{$tag}' and (parent=0 or parent is null)");
							if( okArray($menu) ){
								$id_menu = $menu[0]['id'];
								$database->update('menuItem',"id={$id_menu} OR parent = {$id_menu}",array('active'=>1));
							}
						}
						
					}
					if( $tag == 'article'){
						$database->update('permission',"label='post'",array('active'=>1));
						$database->update('permission',"label='post_admin'",array('active'=>1));
					} elseif( $tag == 'catalogo' || $tag == 'attribute'){
						$database->update('permission',"label='catalog'",array('active'=>1));
					}else{
						$database->update('permission',"label='{$tag}'",array('active'=>1));
					}
					$database->update('module',"id={$id}",array('active'=>1));
					$risposta = array('result'=>'ok');
				}else{
					$risposta = array('result'=>'nak');
				}
				break;
			case 'disable_module_default':
				$database = Marion::getDB();
				$module = $database->select('*','module',"id={$id}");
				if( okArray($module) ){
					$module = $module[0];
					$tag = $module['tag'];
					if( $tag == 'cms_page' || $tag == 'cms_news' || $tag == 'cms_gallery' ){
						$database->update('menuItem',"tag='{$tag}'",array('active'=>0));
						
						if( !okArray($database->select('*','module',"(tag='cms_page' OR tag='cms_gallery' OR tag='cms_news') AND tag <> '{$tag}' AND active=1") ) ){
							$database->update('menuItem',"tag='page'",array('active'=>0));
							$database->update('permission',"label='cms'",array('active'=>0));
						}
						
					}else{
						$menu = $database->select('*','menuItem',"tag='{$tag}' and (parent=0 or parent is null)");
						//debugga($database->lastquery);
						if( okArray($menu) ){
							$id_menu = $menu[0]['id'];
							$database->update('menuItem',"id={$id_menu} OR parent = {$id_menu}",array('active'=>0));
						}
						if( $tag == 'catalog'){
							$tag = 'attribute';
							$menu = $database->select('*','menuItem',"tag='{$tag}' and (parent=0 or parent is null)");
							if( okArray($menu) ){
								$id_menu = $menu[0]['id'];
								$database->update('menuItem',"id={$id_menu} OR parent = {$id_menu}",array('active'=>0));
							}
						}
						
					}
					
					if( $tag == 'article'){
						$database->update('permission',"label='post'",array('active'=>0));
						$database->update('permission',"label='post_admin'",array('active'=>0));
					} elseif( $tag == 'catalog' || $tag == 'attribute'){
						$database->update('permission',"label='catalog'",array('active'=>0));
					}else{
						$database->update('permission',"label='{$tag}'",array('active'=>0));
					}
					$database->update('module',"id={$id}",array('active'=>0));
					
					$risposta = array('result'=>'ok');
				}else{
					$risposta = array('result'=>'nak');
				}

				break;
			
			case 'active':
			case 'enable':
				$action = 'active';
			case 'disable':
			case 'uninstall':
			case 'install':
				$module = _var('module');
				$file = "../modules/".$module."/".$module.".php";
				
				if( file_exists($file) ){
					
					require_once($file);
					
					if( $module == 'pagecomposer'){
						$class = 'PagecomposerInstaller';
					}else{
						$class = $this->getModuleClassName($module);
					}
					
					//debugga($class);exit;
					if( class_exists($class) ){
						$obj = new $class($module);
						
					
						if( is_object($obj) ){
							$obj->readXML();
							$obj->$action();
							
							
							if( $obj->errorMessage ){
								$risposta = array('result'=>'nak','errore'=>$obj->errorMessage);
							}else{
								$risposta = array('result'=>'ok');
							}
						}else{
							$risposta = array('result'=>'nak','Error');
						}
						
						
					}else{
						$risposta = array('result'=>'nak','errore'=>"classe non trovata");
					}
					
				}else{
					$risposta = array(
						'result'=>'nak',
						'errore'=>"Modulo non trovato"
					);
				}
				
				break;
				
		}

		echo json_encode($risposta);
		
	}


	function getModuleClassName(string $string):string{
		

		$str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

	
    	return $str;

	}

	function checkCompatibility($data){
		$check = true;
		if(isset($data['compatibility'])){
			
			$current = (int)preg_replace('/\./','',_MARION_VERSION_);
			
			$comp = (array)$data['compatibility'];
			//sdebugga($comp);exit;
			$from = $comp['from'];
			$to = $comp['to'];
			if($from){
				$from = preg_replace('/\./','',$from);
				if(strlen($from) < 3){
					$diff = 3-strlen($from);
					for($k =1; $k<=$diff; $k++){
						$from .= '0';
					}
				}
				$from = (int)$from;
				if( $current < $from ){
					$check = false;
				}
			}

			if($to){
				$to = preg_replace('/\./','',$to);
				if(strlen($to) < 3){
					$diff = 3-strlen($to);
					for($k =1; $k<=$diff; $k++){
						$to .= '0';
					}
				}
				$to = (int)$to;
				if( $current > $to ){
					$check = false;
				}
			}

			
			

		}
		return $check;
	}

}



?>