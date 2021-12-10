<?php
class Router{
	private $_admin_side = false;
	private $_ctrl;

	private static $redirections = [];

	function __construct(){
		
		$this->_ctrl = _var('ctrl') ? _var('ctrl') : 'Index';
		$this->_mod = _var('mod');
	
		if( defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_){
			$this->_admin_side = true;
		}
		
	}


	function getPaths(){
		$path = _MARION_ROOT_DIR_;
	
		if( $this->_mod ){
			$path_theme = _MARION_THEME_DIR_._MARION_THEME_;
			$path .= "modules/".$this->_mod;
			$path .= "/controllers";
			
			
			
			if( $this->_admin_side ){
				$files[] = $path. "/admin";
			}else{
				$path_theme .= "/modules/".$this->_mod."/controllers";
				$files[] = $path_theme."/front";
				$files[] = $path_theme;
				$files[] = $path. "/front";
			}
			$files[] = $path;
		}else{
			if( $this->_admin_side ){
				$path .= 'backend';

			}
			$files[] =$path. "/controllers";
		}
		
		
		return $files;
	}

	function resolveRoute(){
		$url = $_SERVER['REQUEST_URI'];
		foreach($GLOBALS['_routes'] as $pattern => $r){
			
			if (preg_match('#^' . $pattern . '$#', $url)) {
				

				$new_url = preg_replace('#^' . $pattern . '$#',$r,$url);
				$url = parse_url($new_url);
				debugga($url);exit;
				parse_str($url['query'], $get_array);
				debugga($get_array);exit;
				header('Location: '.$new_url);
				exit;
			}
		}
		debugga($url);exit;
	}


	function dispatch(){
		//$this->resolveRoute();
		$class = $this->_ctrl."Controller";
		
		$_paths = $this->getPaths();
		
		foreach($_paths as $path){
			$file = $path . "/" . $class.".php";
			if( file_exists($file) ){

				
				require_once($file);
				break;
			}
		}
		
		/*if( $this->_mod ){
			if( _MARION_ROOT_DIR_ ){
				$file = $_SERVER['DOCUMENT_ROOT']."/"._MARION_ROOT_DIR_."/modules/".$this->_mod."/controllers/".$class.".php";
			}else{
				$file = $_SERVER['DOCUMENT_ROOT']."/modules/".$this->_mod."/controllers/".$class.".php";
			}
			
			if( file_exists($file) ){
				
				require_once($file);
			}
		}else{
			if( $this->_admin_side ){
				$file = $_SERVER['DOCUMENT_ROOT']."/"._MARION_ROOT_DIR_."/backend/controllers/".$class.".php";
			}

			if( file_exists($file) ){
				
				require_once($file);
			}
			
			
		}*/

		$redirect = self::$redirections[$class];
		
		if($redirect){
			$class = $redirect;
		}
				
		
		if( class_exists($class) ){
			
			$ctrl = new $class();
			
		}else{
			echo "no controller class found";
		}
		

	}

	public static function redirect($ctrl,$redirectCtrl){
		self::$redirections[$ctrl] = $redirectCtrl;
	}


	//metodo che registra un url per il redirect
	public static function registerUrl($match,$redirect){
		
	}
}


?>