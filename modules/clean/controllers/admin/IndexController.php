<?php
class IndexController extends ModuleController{
	
	public $modules = array();


	function setMedia(){
		parent::setMedia();
		$this->registerJS('../modules/clean/js/script.js');
	}

	function display(){
		$this->getModules();
		
		$action = $this->getAction();
		switch($action){
			case 'delete':
				$this->displayMessage('Operazione effettuata con successo!');
				$this->deleteData();
				break;
		}

		$this->setVar('modules',$this->modules);
		$this->output('setting.htm');
	}



	function getModules(){
		
		$this->modules['cms'] = array(
			'name' => 'CMS',
			'entities' => array(
				'pages' => 'Pagine',
				'menu_links' => 'Link menu',
				'homepages' => 'Home Page',
				'footers' => 'Footers',
				'images' => 'Immagini (database)',
				'attachments' => 'Allegati (database)',
				'media' => 'Media',
				'users' => 'Utenti',
				'user_categories' => 'Categorie Utenti',
				'profiles' => 'Profili'
				)
			);
		
	

		Marion::do_action('action_clean_data',array(&$this->modules));
		
	}



	function deleteData(){
		$module = _var('module');
		$formdata = $this->getFormdata();
		$db = Marion::getDB();
		foreach($formdata['values'] as $v){
			switch($v){
				case 'pages':
					$db->delete('pageLocale');
					$db->delete('page');
					$db->delete('page_advanced');
					$db->delete('composition_page');
					$db->delete('composition_page_tmp');
					$db->execute("ALTER TABLE page AUTO_INCREMENT = 1");
					$db->execute("ALTER TABLE page_advanced AUTO_INCREMENT = 1");
					$db->execute("ALTER TABLE composition_page AUTO_INCREMENT = 1");
					$db->execute("ALTER TABLE composition_page_tmp AUTO_INCREMENT = 1");
					$dir_css = _MARION_MODULE_DIR_.'pagecomposer/media/css';
					$list = scandir($dir_css);
					foreach($list as $i){
						if( $i != '.' && $i != '..'){
							unlink($dir_css."/".$i);
						}
					}
					$dir_js = _MARION_MODULE_DIR_.'pagecomposer/media/js';
					$list = scandir($dir_js);
					foreach($list as $i){
						if( $i != '.' && $i != '..'){
							unlink($dir_js."/".$i);
						}
					}
				break;
				case 'homepages':
					$db->delete('homepage');
					$db->execute("ALTER TABLE homepage AUTO_INCREMENT = 1");
				break;
				case 'footers':
					$db->delete('footer');
					$db->execute("ALTER TABLE footer AUTO_INCREMENT = 1");
				break;
				case 'attachments':
					$dir = _MARION_ROOT_DIR_.'upload/attachments';
					$list = scandir($dir);
					foreach($list as $i){
						if( $i != '.' && $i != '..'){
							unlink($dir."/".$i);
						}
					}
					$db->delete('attachment');
					$db->execute("ALTER TABLE attachment AUTO_INCREMENT = 1");
				break;
				case 'images':
					$dir = _MARION_ROOT_DIR_.'upload/images/';
					$list = array('large','small','medium','thumbnail');

					foreach(scandir($dir) as $i){
						if( !in_array($i,$list) && $i != '.' && $i != '..'){
							unlink($dir.$i);
							
						}
					}
					foreach($list as $v){
						$images = scandir($dir.$v);
						foreach($images as $i){
							if( $i != '.' && $i != '..'){
								unlink($dir.$v."/".$i);
								
							}
						}
					}
					$db->delete('imageComposed');
					$db->delete('image');
					$db->execute("ALTER TABLE imageComposed AUTO_INCREMENT = 1");
					$db->execute("ALTER TABLE image AUTO_INCREMENT = 1");
					
				break;
				case 'media':
					$dir = _MARION_ROOT_DIR_.'media/';
					$list = array('files','images');
					$directories = scandir($dir);
					foreach($directories as $i){
						if( !in_array($i,$list) && $i != '.' && $i != '..' && $i != '.htaccess' ){
							unlink($dir.$v."/".$i);
							$this->rrmdir($dir.$i);
						}
					}
					
					
					
					foreach($list as $v){
						$images = scandir($dir.$v);
						
						foreach($images as $i){
							if( $i != '.' && $i != '..'){
								unlink($dir.$v."/".$i);
								$this->rrmdir($dir.$v."/".$i);
							}
						}
					}
					//exit;
				break;
				case 'menu_links':
					$db->delete('linkMenuFrontendLocale');
					$db->delete('linkMenuFrontend');
					$db->execute("ALTER TABLE linkMenuFrontend AUTO_INCREMENT = 1");
				break;
				case 'users':
					$db->delete('user','id>1');
					$db->execute("ALTER TABLE user AUTO_INCREMENT = 2");
				break;
				case 'user_categories':
					$db->delete('userCategoryLocale','usercategory > 1');
					$db->delete('userCategory','id > 1');
					$db->execute("ALTER TABLE userCategory AUTO_INCREMENT = 2");
				break;
				case 'profiles':
					$db->delete('profile','id>1');
					$db->delete('profile_permission','id_profile>1');
					$db->execute("ALTER TABLE profile AUTO_INCREMENT = 2");
				break;
			}
		}

		//debugga($formdata);exit;

		Marion::do_action('action_clean_delete_data',array($module,$formdata['values']));
		
		//Marion::do_action('clean_delete_data',)
	}


	function rrmdir($dir) { 
		if (is_dir($dir)) { 
		  $objects = scandir($dir);
		  foreach ($objects as $object) { 
			if ($object != "." && $object != "..") { 
			  if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
				$this->rrmdir($dir. DIRECTORY_SEPARATOR .$object);
			  else
				unlink($dir. DIRECTORY_SEPARATOR .$object); 
			} 
		  }
		  rmdir($dir); 
		} 
	  }


	
}


?>