<?php
use Marion\Core\Module;
class Catalogo extends Module{
	
	

	function install(){
		$res = parent::install();
		if( $res ){
			
			
		}
		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();
		if( $res ){
			
		}
		
		return $res;
	}


	function loadClasses(){
		$path = _MARION_MODULE_DIR_.'catalogo/lib';
		if( file_exists( $path ) ){
			foreach (scandir($path) as $filename) {
				
				$path_file = $path."/".$filename;
				
				
				if (is_file($path_file)) {
					
					require_once ($path_file);
				}
			}
		}
		
	}

}



?>