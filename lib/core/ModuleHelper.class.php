<?php
/*********************************************************
Classe che si occupa di installare e disinstallare un modulo
*********************************************************/


class ModuleHelper{
	public $version=1;

	function __construct($path=null){
		$this->directory_module = $path;
	}


	function readXML(){
		$xml_file = "config.xml";
		
		if( file_exists($xml_file) ){
			$data = simplexml_load_file($xml_file);
			
			if( is_object($data->info) ){
				if(is_object($data->info->description)){
					$data->info->description = $data->info->description->__toString();
				}
				/*if(is_object($data->info->url)){
					$data->info->url = $data->info->url->__toString();
				}*/
				
			}
			$this->config = $this->object_to_array($data);
			
			
		}
	}

	
	
	function checkConflits(){
		$dependencies = $this->config['conflicts'];
		if( okArray($dependencies) ){
			$where .= 'id in (';
			foreach($dependencies['conflict'] as $d){
				$where .= "{$d},";
			}
			$where = preg_replace("/\,$/",")",$where);
			$database = _obj('Database');
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



	function checkDependencies(){
		$dependencies = $this->config['dependencies'];
		
		if( okArray($dependencies) ){
			$database = _obj('Database');

			if( !okArray($dependencies['dependence']) ){
				$dependencies['dependence'] = array($dependencies['dependence']);
			}
			
			foreach($dependencies['dependence'] as $d){
				if( (int)$d ){
					$check = $database->select('count(*) as count',"module","id={$d} and active=1");
				
				}
				/*else{
					$check = $database->select('count(*) as count',"module","id={$d['id']} and active=1");
				}*/

				
				
				if( $check[0]['count'] == 0  ){
					$this->error = "error dependencies";
					if( $d['errorMessage'] ){
						$this->errorMessage = (string)$d['errorMessage'];
						return false;
					}
				}
			}

		}
		return true;
		
	}



	function saveWidgets(){
		$database = _obj('Database');
		$info = $this->config['info'];
		
		if( !okArray( $this->config['widget'][0] ) ){
			$this->config['widget'] = array($this->config['widget']);
		}
		
		foreach($this->config['widget'] as $v){
			$v['module'] = $info['id'];
			if( $v['url_conf'] ){
				if( !preg_match('/backend/',$v['url_conf']) ){
					$v['url_conf'] = "/admin/modules/".$this->directory_module."/".$v['url_conf'];
				}
			}
			$database->insert('widget',$v);
			
		}
	}

	function deleteWidgets(){
		$database = _obj('Database');
		$info = $this->config['info'];
		$database->delete('widget',"module={$info['id']}");
	}

	function createMenus(){
		
		$this->deleteMenus();
		
		$database = _obj('Database');
		$menu = $this->config['menu'];
		$info = $this->config['info'];

		if( !array_key_exists('0',$menu) ){
			$tmp = $menu;
			unset($menu);
			$menu[0] = $tmp;
		}
		foreach($menu as $menu_data){
			
			
			
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
				$parent = MenuItem::create()
						->set(array(
							'module'=>$info['id'],
							'tag'=>$parent_data['tag']?$parent_data['tag']:$info['tag'],
							'permission' => $parent_data['permission'],
							'scope' => $scope_module,
							'priority' => $parent_data['priority'],
							'icon' => $parent_data['icon'],
							'icon_img' => $parent_data['iconImg']?"../modules/".$info['tag']."/".$parent_data['iconImg']:'',
						))->setDataFromArray($data_locale_parent)
						->save();
			}else{
				$parent = MenuItem::prepareQuery()->where('tag',$parent_data['parent'])->getOne();
			}

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
				
				if( $this->version && $this->version == 2 ){

				}else{
					if( $scope_module == 'admin'){
						$item['url'] = "/admin/modules/".$this->directory_module."/".$item['url'];
					}else{
						$item['url'] = "/modules/".$this->directory_module."/".$item['url'];
					}
				}
				
				
				
				$menu = MenuItem::create()
					->set($item)
					->setDataFromArray($data_locale)
					->save();
			}
		}
		//debugga($parent);exit;


		

	}


	function deleteMenus(){
		$database = _obj('Database');
		$menu_old = MenuItem::prepareQuery()->where('module',$this->config['info']['id'])->get();
		foreach($menu_old as $v){
			$v->delete();
		}
	}


	function install(){

		//inserisco il modulo nella tabella dei moduli
		$database = _obj('Database');
		$data = $this->config;

		if( !$this->checkConflits() || !$this->checkDependencies()) return false;
		

		
		$info = $data['info'];
		
		
		$info['active'] = 1;
		$info['default_module'] = 0;
		$info['directory'] = $this->directory_module;
		
		if( !okArray($database->select('*','module',"id={$info['id']}")) ){
			$database->insert('module',$info);
		}
		

		//creo i menu
		$this->createMenus();
		$this->saveWidgets();

		$cache = _obj('Cache');
		if( $cache->isExisting("setting") ){
			$cache->delete('setting');
		}
		Marion::read_config();
		return true;


	}


	function uninstall(){
		$database = _obj('Database');
		
		//elimino il modulo dalla tabella dei moduli
		$data = $this->config;
		$info = $data['info'];
		$database->delete('module',"id={$info['id']}");

		
		//elimino i menu
		$this->deleteMenus();
		$this->deleteWidgets();

		$cache = _obj('Cache');
		if( $cache->isExisting("setting") ){
			$cache->delete('setting');
		}
		Marion::read_config();
		return true;
	}


	function active(){
		$database = _obj('Database');
		$data = $this->config;
		$info = $data['info'];
		$database->update('module',"id={$info['id']}",array('active'=>1));
		$database->update('menuItem',"module={$info['id']}",array('active'=>1));
		
		Marion::refresh_config();
		return true;

	}

	function disable(){
		$database = _obj('Database');
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
			$database = _obj('Database');
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
				$zip = new ZipArchive();
				if ($zip->open($destination, ZIPARCHIVE::CREATE) === true) {
					
					$source = realpath($source);
					if (is_dir($source) === true) {
						$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
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
		
	







}



?>