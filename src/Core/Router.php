<?php
namespace Marion\Core;
class Router{
	public $_admin_side = false;
	public $_ctrl;

	public static $redirections = [];

	function __construct(){
		
		$this->_ctrl = _var('ctrl') ? _var('ctrl') : 'Index';
		$this->_mod = _var('mod');
		if( !$this->_ctrl ){
			$this->_ctrl = 'IndexAdmin';
		}
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
        
		foreach($GLOBALS['_routes'] as $pattern => $data){
			$pattern .= "(.*)"; 
			//if (preg_match('#^' . $pattern . '$#', $url, $matches)) {
			if (preg_match('#^' . $pattern . '#', $url, $matches)) {
                $class = $data['controller'];
                $scope = 'front';
                if( isset($data['admin']) ){
                    $scope = 'admin';
                }
                if( $data['module'] ){
					require_once(_MARION_MODULE_DIR_.$data['module']."/controllers/".$scope."/".$data['controller'].".php");
				}else{
					require_once(_MARION_ROOT_DIR_."/controllers/".$data['controller'].".php");
				}
                $options = [
                    'from_routing' => 1,
                    'module' => $data['module']
                ];
                $ctrl = new $class($options);
                $params = $matches;
                unset($params[0]);
                $method = isset($data['method'])?$data['method']:'display';
				call_user_func_array(array($ctrl, $method),array_values($params));
                exit;

			}
		}
		
	}


	function dispatch(){
		$this->resolveRoute();
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