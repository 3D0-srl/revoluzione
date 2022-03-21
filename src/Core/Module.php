<?php
namespace Marion\Core;
use Marion\Entities\Cms\{MenuItem,HomeButton};
use Marion\Entities\Permission;

/*********************************************************
Classe che si occupa di installare e disinstallare un modulo
*********************************************************/


class Module{
	public static $latest = 3;
	private $_version;
	protected $directory_module;
	
	function __construct($path=null){
		$this->directory_module = $path;
		
	}



	/**
	 * metodo che legge il file di configurazione XML
	 */

	function readXML(){
		
		$xml_file = "config.xml";
		if( !file_exists($xml_file) ){
			//per la nuova gestione dei moduli
			$xml_file = _MARION_MODULE_DIR_.$this->directory_module."/config.xml";
		}
		
		if( file_exists($xml_file) ){
			$data = simplexml_load_file($xml_file);
			$this->config_xml = $data;
			if( is_object($data->info) ){
				$this->_version = $this->info['version'];
				if(is_object($data->info->description)){
					$data->info->description = $data->info->description->__toString();
				}
			}
			$this->config = $this->object_to_array($data);
			
		}
	}

	/**
	 * metodo che controlla se ci sono conflitti tra il modulo che si sta installando con quelli già presenti nel CMS
	 */
	
	function checkConflits(){
		$dependencies = $this->config['conflicts'];
		$where = '';
		if( okArray($dependencies) ){
			$where .= 'id in (';
			foreach($dependencies['conflict'] as $d){
				$where .= "{$d},";
			}
			$where = preg_replace("/\,$/",")",$where);
			$database = Marion::getDB();
			$check = $database->select('*',"module","{$where} and active=1");
			if( okArray($check) ){
				$this->error = "error conflicts";
				return false;
			}else{
				return true;
			}
		
		}
		return true;
		
	}

	/**
	 * metodo che controlla le dipendenze
	 */

	function checkDependencies(){
		$dependencies = $this->config['dependencies'];
		
		if( okArray($dependencies) ){
			$installed_modules = Marion::getConfig('modules','installed');
			if( !okArray($dependencies['dependence']) ){
				$dependencies['dependence'] = array($dependencies['dependence']);
			}
			
			
			foreach($dependencies['dependence'] as $d){
				
					if(!in_array($d,$installed_modules)){
						
						$this->errorMessage = $d." not installed or not active";
						return false;
						
					}
			}

		}
		return true;
		
	}


	function checkUninstall(){
		$installed_modules = Marion::getConfig('modules','installed');

		if( okArray($installed_modules) ){
			


			$module_dependencies = array();
			foreach($installed_modules as $module){

					$xml_file = _MARION_MODULE_DIR_.$module."/config.xml";
					if( file_exists($xml_file) ){
						$data = simplexml_load_file($xml_file);
						$array = $this->object_to_array($data);
						
						if( array_key_exists('dependencies',$array) ){
							
							$dependencies = $array['dependencies'];
							
							if( !okArray($dependencies['dependence']) ){
								$dependencies['dependence'] = array($dependencies['dependence']);
							}
							
							
							if( in_array($this->directory_module,$dependencies['dependence']) ){
								
								$module_dependencies[] = $module;
							}
						}
					
						
					}
			}
			if( okArray($module_dependencies) ){
				$modules_string = '';
				foreach($module_dependencies as $module){
					$modules_string .= $module.",";
				}
				$modules_string = preg_replace('/\,$/','',$modules_string);
				$this->errorMessage = "Action invalid: ".$modules_string." depends from ".$this->directory_module;
				return false;
			}
			

		}
		
		return true;
		
	}
	
	



	function install(){
		$data = $this->config;
		$info = $data['info'];
		if( !$info['version'] ) $info['version'] = self::$latest;
		if( $info['version'] == 3 ){
			return $this->install_v3();

		}
		//inserisco il modulo nella tabella dei moduli
		$database = Marion::getDB();
		

		if( !$this->checkConflits() || !$this->checkDependencies()) return false;

		
		
		
		$info['active'] = 1;
		$info['default_module'] = 0;
		$info['directory'] = $this->directory_module;
		
		if( !okArray($database->select('*','module',"id={$info['id']}")) ){
			$database->insert('module',$info);
		}
		

		//creo i menu
		$this->createMenus();

		$this->createHomeButtons();
		$this->saveWidgets();

		$cache = _obj('Cache');
		if( $cache->isExisting("setting") ){
			$cache->delete('setting');
		}
		Marion::read_config();
		return true;


	}


	function uninstall(){
		$data = $this->config;
		$info = $data['info'];
		if( !$info['version'] ) $info['version'] = self::$latest;
		if( $info['version'] == 3 ){
			return $this->uninstall_v3();

		}
		if( !$this->checkUninstall() ){
			return false;
		}
		$database = Marion::getDB();
		
		//elimino il modulo dalla tabella dei moduli
		$data = $this->config;
		$info = $data['info'];
		$database->delete('module',"id={$info['id']}");
		
		
		//elimino i menu
		$this->deleteMenus();
		$this->deleteHomeButtons();
		$this->deleteWidgets();

		$cache = _obj('Cache');
		if( $cache->isExisting("setting") ){
			$cache->delete('setting');
		}
		Marion::read_config();
		return true;
	}


	function active(){
		$database = Marion::getDB();
		$data = $this->config;
		$info = $data['info'];
		$database->update('module',"id={$info['id']}",array('active'=>1));
		$database->update('menuItem',"module={$info['id']}",array('active'=>1));
		
		Marion::refresh_config();
		return true;

	}

	function disable(){
		$database = Marion::getDB();
		$data = $this->config;
		$info = $data['info'];
		$database->update('module',"id={$info['id']}",array('active'=>0));
		$database->update('menuItem',"module={$info['id']}",array('active'=>0));
		
		Marion::refresh_config();
		return true;

	}


	function object_to_array($obj) {
		if(is_object($obj)) $obj = (array) $obj;
		if(is_array($obj)) {
			$new = array();
			foreach($obj as $key => $val) {
				$new[$key] = $this->object_to_array($val);
			}
		}
		else $new = $obj;
		return $new;       
	}


	function db_import_sql_from_file($filename){
		if ( @file_exists($filename) ) {
			$database = Marion::getDB();
			$templine = '';
			// Read in entire file
			$fp = fopen($filename, 'r');
			// Loop through each line
			while (($line = fgets($fp)) !== false) {
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || $line == '')
					continue;
				// Add this line to the current segment
				$templine .= $line;
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';') {
					// Perform the query

					if ( $database->execute($templine) === false ) {
						debugga($templine);
						return false;
					}
					
					// Reset temp variable to empty
					$templine = '';
				}
			}
			
			fclose($fp);
			return true;
		}

		return false;
	}

	

	function exportZip(){
			
		
			$name_file = $this->directory_module.".zip";
			$path_zip = "/tmp/".$name_file;
			

			$dir = getcwd();
			

			$res = $this->zipData($dir,$path_zip);
			
			// Get real path for our folder

			/*$rootPath = realpath($dir);

			// Initialize archive object
			$zip = new ZipArchive();
			$zip->open($path_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

			// Create recursive directory iterator
			
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($rootPath),
				RecursiveIteratorIterator::LEAVES_ONLY
			);
			debugga($zip);exit;

			foreach ($files as $name => $file)
			{
				// Skip directories (they would be added automatically)
				if (!$file->isDir())
				{
					// Get real and relative path for current file
					$filePath = $file->getRealPath();
					$relativePath = substr($filePath, strlen($rootPath) + 1);

					// Add current file to archive
					$zip->addFile($filePath, $relativePath);
				}
			}

			// Zip archive will be created only after closing object
			$zip->close();*/

			header('Content-Type: application/zip');
			header("Content-Disposition: attachment; filename=".$name_file);
			header('Content-Length: ' . filesize($path_zip));
			readfile($path_zip);
			

	

	}



	function zipData($source, $destination) {
		if (extension_loaded('zip') === true) {
			
			if (file_exists($source) === true) {
				$zip = new \ZipArchive();
				if ($zip->open($destination, \ZIPARCHIVE::CREATE) === true) {
					
					$source = realpath($source);
					if (is_dir($source) === true) {
						$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
						debugga($source);
						foreach ($files as $file) {
							$file = realpath($file);
							if (is_dir($file) === true) {
								$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
							} else if (is_file($file) === true) {
								$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
							}
						}
					} else if (is_file($source) === true) {
						$zip->addFromString(basename($source), file_get_contents($source));
					}
				}
				exit;
				return $zip->close();
			}
		}
		return false;
	}
		
	/** VERSIONE 3 **/
	
	function uninstallWidgets(){
		$database = Marion::getDB();
		$info = $this->config['info'];
		$database->delete('widget',"directory='{$this->directory_module}'");
	}

	function installWidgets(){
		$database = Marion::getDB();
		$info = $this->config['info'];
		if( $this->config['widget'] ){
			$widgets = array();
			if( !okArray( $this->config['widget']['widgets'][0] ) ){
				$widgets = array($this->config['widget']['widgets']);
			}else{
				$widgets = $this->config['widget']['widgets'];
			}
			
			foreach($widgets as $v){
				$v['restrictions'] = serialize(explode(',',$v['restrictions']));
				$v['module'] = $info['id'];
				
				$database->insert('widget',$v);
				
			}
		}
	}

	
 


	/** VERSIONE 2 **/
	function saveWidgets(){
		$database = Marion::getDB();
		$info = $this->config['info'];
		
		if( !okArray( $this->config['widget'][0] ) ){
			$this->config['widget'] = array($this->config['widget']);
		}
		
		foreach($this->config['widget'] as $v){
			$v['module'] = $info['id'];

			
			$v['restrictions'] = serialize(explode(',',$v['restrictions']));
			
			$database->insert('widget',$v);
			
		}
	}

	function deleteWidgets(){
		$database = Marion::getDB();
		$info = $this->config['info'];
		$database->delete('widget',"module={$info['id']}");
	}
	

	function deleteHomeButtons(){
		$old_buttons = HomeButton::prepareQuery()->where('module',$this->directory_module)->get();
		foreach($old_buttons as $v){
			$v->delete();
		}
		return true;
	}
	
	function createHomeButtons(){
		$this->deleteHomeButtons();
		$home = $this->config['home'];
		$buttons = $home['buttons']['button'];
		if( $buttons ){
		
			if( !array_key_exists('0',$buttons) ){
				$tmp = $buttons;
				unset($buttons);
				$buttons[0] = $tmp;
			}

			if(okArray($buttons)){
				foreach($buttons as $data){
					$data['module'] = $this->directory_module;
					$data['icon_image'] = $data['iconImg'];
					foreach($data['locale'] as $k => $v){
						foreach($v as $lo => $val){
							$data_locale[$lo][$k] = $val;
						}
			
					}

					$obj = HomeButton::create()
						->set($data)->setDataFromArray($data_locale)->save();

					
				}
			}
		}



		return true;

	}

	function createMenus(){
		
		$this->deleteMenus();
		
		$database = Marion::getDB();
		
		$info = $this->config['info'];
		


		$menu = $this->config['menu'];
		if( !array_key_exists('0',$menu) ){
			$tmp = $menu;
			unset($menu);
			$menu[0] = $tmp;
		}
		
		foreach($menu as $menu_data ){
			$scope_module = $menu_data['scope'];
			
			
			
			//inserisco la voce padre del menu
			$parent_data = $menu_data['header'];
			if( !$parent_data['parent'] ){
				if( $parent_data['locale'] ){
						foreach($parent_data['locale'] as $k => $v){
							foreach($v as $lo => $val){
								$data_locale_parent[$lo][$k] = $val;
							}
				
						}
					}
				$data_item = array(
					'module'=>$info['id'],
					'tag'=>$parent_data['tag']?$parent_data['tag']:$info['tag'],
					'permission' => $parent_data['permission'],
					'scope' => $scope_module,
					'priority' => $parent_data['priority'],
					'icon' => $parent_data['icon'],
					'icon_image' => $parent_data['iconImg'],
					'url' => $parent_data['url']
				);
				
				$parent = MenuItem::create()
						->set($data_item)->setDataFromArray($data_locale_parent)
						->save();
				
			}else{
				$parent = MenuItem::prepareQuery()->where('tag',$parent_data['parent'])->getOne();
			}
			//debugga($parent);exit;
			//debugga($menu_data['items']);exit;
			
			if( isset($menu_data['items']) ){
				if( !okArray( $menu_data['items'][0] ) ){
					$menu_data['items'] = array($menu_data['items']);
				}
				
				foreach($menu_data['items'] as $item){
					if( $item['locale'] ){
						foreach($item['locale'] as $k => $v){
							foreach($v as $lo => $val){
								$data_locale[$lo][$k] = $val;
							}
				
						}
					}
					unset($item['locale']);
				
					$item['module'] = $info['id'];
					$item['parent'] = $parent->id;
					$item['scope'] = $scope_module;
					//$item['tag'] = $info['tag'];
					
					
					
					
					
					$menu = MenuItem::create()
						->set($item)
						->setDataFromArray($data_locale)
						->save();
				}
			}
		}

	}


	function deleteMenus(){
	
		$menu_old = MenuItem::prepareQuery()->where('module',$this->config['info']['id'])->get();
		foreach($menu_old as $v){
			$v->delete();
		}
	}








	/******************************************************** VERSIONE 3 ****************************************************/

	function removeHookActions(){
		$database = Marion::getDB();
		$id = $this->config_xml->info->id;
		$database->delete('hook_action',"id_module={$id}");
	}


	function addHookActions(){
		$id_module = (int)$this->config_xml->info->id;
		if( $this->config_xml->actions ){
			foreach($this->config_xml->actions->action as $item){
				//debugga($item);
				Marion::create_hook(
						$item->hook,
						$item->hookDescription?$item->hookDescription:$item->hook,
						$item->hookType?$item->hookType:'display',
						$id_module
				);
				Marion::register_action($item->hook,$item->function,$id_module);
			}
		}
	}



	function insertModule(){
		$database = Marion::getDB();
		$data = $this->config;
		$info = $data['info'];
	
		$info['active'] = 1;
		$info['default_module'] = 0;
		$info['directory'] = $this->directory_module;
		
		if( !okArray($database->select('*','module',"id={$info['id']}")) ){
			$id = $database->insert('module',$info);
			$this->config_xml->info->id = $id;
		}
		
		
		
	}

	function install_v3(){
		//controllo se ci sono dipendenze o conflitti
		if( !$this->checkConflits() || !$this->checkDependencies()) return false;
		$sql_file =_MARION_MODULE_DIR_.$this->directory_module."/sql/install.sql";
		if( file_exists($sql_file) ){
			//per la nuova gestione dei moduli
			$this->db_import_sql_from_file($sql_file);
		}

		

		//inserisco il modulo della tabella module
		$this->insertModule();
		
		$this->addHookActions();

		//creo le voci del menu
		$this->createMenus_v3();

		$this->createHomeButtons_v3();

		$this->saveWidgets_v3();

		$this->savePermissions();
		return 1;
	}

	function createMenus_v3(){
		$this->deleteMenus_v3();
		$info = $this->config_xml->info;
		
		//CREAZIONI DELLE VOCI ADMIN
		if( $this->config_xml->admin->menu->items ){
		foreach($this->config_xml->admin->menu->items->item as $item){
			
			$id_parent = 0;
			
			if( $parent_tag = trim($item->parent) ){
				$parent_item = MenuItem::prepareQuery()
				->where('tag',$parent_tag)
				->where('scope','admin')
				->getOne();
				
				if( is_object($parent_item) ){
					$id_parent = $parent_item->id;
				}else{
					continue;
				}
			}

			$data_locale = (array)$item->locale;
			$data_lang = array();
			foreach($data_locale as $k => $v){
				foreach($v as $lo => $val){
					$data_lang[$lo][$k] = (string)$val;
				}
	
			}
			
			$data_item = array(
				'module'=> (int)$info->id,
				'tag'=> (string)$item->tag,
				'permission' => (string)$item->permission,
				'scope' => 'admin',
				'priority' => (int)$item->priority,
				'icon' => (string)$item->icon,
				'icon_image' => (string)$item->iconImg,
				'url' => (string)$item->url,
				'parent' => $id_parent
			);
			//debugga($data_item);
				
			$item = MenuItem::create()
					->set($data_item)->setDataFromArray($data_lang)
					->save();

		}
		}

		//CREAZIONI DELLE VOCI BACKEND
		if( $this->config_xml->backend->menu->items ){
			foreach($this->config_xml->backend->menu->items->item as $item){
				
				$id_parent = 0;
				if( $parent_tag = trim($item->parentTag) ){
					$parent_item = MenuItem::prepareQuery()->where('tag',$parent_tag)->where('scope','frontend')->getOne();
					if( is_object($parent_item) ){
						$id_parent = $parent_item->id;
					}else{
						continue;
					}
				}

				$data_locale = (array)$item->locale;
				$data_lang = array();
				foreach($data_locale as $k => $v){
					foreach($v as $lo => $val){
						$data_lang[$lo][$k] = (string)$val;
					}
		
				}
				
				$data_item = array(
					'module'=> (int)$info->id,
					'tag'=> (string)$item->tag,
					'permission' => (string)$item->permission,
					'scope' => 'frontend',
					'priority' => (int)$item->priority,
					'icon' => (string)$item->icon,
					'icon_image' => (string)$item->iconImg,
					'url' => (string)$item->url,
					'parent' => $id_parent
				);
					
				MenuItem::create()
						->set($data_item)->setDataFromArray($data_lang)
						->save();

			}
		}
	}

	function deleteMenus_v3(){
		
		$menu_old = MenuItem::prepareQuery()->where('module',(int)$this->config_xml->info->id)->get();
		foreach($menu_old as $v){
			$v->delete();
		}
	}


	function savePermissions(){
		$this->deletePermissions();
		if( $this->config_xml->permissions ){
			foreach($this->config_xml->permissions->permission as $item){
			
				$data = (array)$item;
				$id_module = $this->config_xml->info->id;
				$dati = array();
				$dati['id_module'] = $id_module;
				$dati['label'] = $data['tag'];
				$dati['orderView'] = 10;
				$dati['active'] = 1;
				foreach($data['locale'] as $k => $v){
					foreach($v as $lo => $val){
						$data_locale[$lo][$k] = $val;
					}
		
				}
	
				$obj = Permission::create()
					->set($dati)->setDataFromArray($data_locale)->save();
		
				
	
			}
		}
		
		
		



		return true;
		

	}
	function deletePermissions(){
		$id_module = $this->config_xml->info->id;
		$old = Permission::prepareQuery()->where('id_module',$id_module)->get();
		foreach($old as $v){
			$v->delete();
		}
		return true;
	}

	function createHomeButtons_v3(){
		$this->deleteHomeButtons();
		
		if( $this->config_xml->backend->homeButtons ){
			foreach($this->config_xml->backend->homeButtons->button as $button){
				$data = (array)$button;
				$data['module'] = $this->directory_module;
				$data['icon_image'] = $data['iconImg'];
				foreach($data['locale'] as $k => $v){
					foreach($v as $lo => $val){
						$data_locale[$lo][$k] = $val;
					}
		
				}

				$obj = HomeButton::create()
					->set($data)->setDataFromArray($data_locale)->save();

			}
		}
		
		



		return true;

	}
	

	function saveWidgets_v3(){
		$database = Marion::getDB();
		$id_module = $this->config_xml->info->id;
		if( $this->config_xml->widgets ){
			foreach($this->config_xml->widgets->widget as $w){
				$data = (array)$w;
				$data['module'] = $id_module;
	
				
				$data['restrictions'] = serialize(explode(',',$data['restrictions']));
				
				$database->insert('widget',$data);
	
			}
		}
		
		return true;
	}

	

	function deleteWidgets_v3(){
		$database = Marion::getDB();
		$id_module = (int)$this->config_xml->info->id;
		$database->delete('widget',"module={$id_module}");
	}


	function uninstall_v3(){
		
		if( !$this->checkUninstall() ){
			return false;
		}
		$sql_file = "../modules/".$this->directory_module."/sql/uninstall.sql";
		if( file_exists($sql_file) ){
			
			$this->db_import_sql_from_file($sql_file);
		}

		$database = Marion::getDB();
		
		//elimino il modulo dalla tabella dei moduli
		$data = $this->config;

		$tag = (string)$this->config_xml->info->tag;
		$select = $database->select('*','module',"directory='{$tag}'");
		$info_module = $select[0];
		$this->config_xml->info->id = $info_module['id'];
		
		$id_module = (int)$this->config_xml->info->id;
	
		
		$database->delete('module',"id={$id_module}");
		
		
		//elimino i menu
		$this->deleteMenus_v3();
		$this->deleteHomeButtons();
		$this->deleteWidgets_v3();


		$this->removeHookActions();


		$this->deletePermissions();

		$cache = _obj('Cache');
		if( $cache->isExisting("setting") ){
			$cache->delete('setting');
		}
		Marion::read_config();
		return true;
	}


	public function getFaker():\Faker\Generator{
		return \Faker\Factory::create();
	}

	public function seeder(){

	}


}



?>