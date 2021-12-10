<?php
class WidgetComponent{
	
	public $_twig;
	public $_twig_vars = array();
	public $_twig_tepplates_dir = array();

	public $module = '';
	
	function __construct($module=''){
		$this->module = $module;
	
	}
	

	function setModule($module){
		$this->module = $module;
	}


	function isMobile() {
		return _MARION_DEVICE_ == 'MOBILE';
	}

	function isTablet() {
		return _MARION_DEVICE_ == 'TABLET';
	}

	


	
	



	

	// TWIG INTEGRATIONS
	function output($tmpl){
		$this->initTemplateTwig();
		
		
		if( okArray($this->_twig_vars) ){
			echo $this->_twig->render($tmpl, $this->_twig_vars);
		}else{
			echo $this->_twig->render($tmpl);
		}
	}
	

	// imposta una variabile nel template
	function setVar($key,$val){
		
		$this->_twig_vars[$key] = $val;
		
	}


	function getTemplatesDirectories(){
		$path = array();
		if( $this->module ){
			$path_theme = _MARION_THEME_DIR_._MARION_THEME_;
			$path_theme .= "/modules/".$this->module;

			$path[] = $path_theme."/templates_twig/widgets";
			$path[] = $path_theme."/templates_twig";
			
			$path[] = _MARION_MODULE_DIR_.$this->module."/templates_twig/widgets";
			$path[] = _MARION_MODULE_DIR_.$this->module."/templates_twig";

			
		}else{
			$path[] = _MARION_THEME_DIR_._MARION_THEME_."/templates_twig";
		}
		//debugga($this->module);
		return $path;
	}


	
	function initTemplateTwig(){
		
		$paths = $this->getTemplatesDirectories();
		
		$loader = new \Twig\Loader\FilesystemLoader();
		foreach($paths as $path){
			if( file_exists( $path ) ){
				
				$loader->addPath($path);
			}

		}
		foreach($this->_twig_tepplates_dir as $dir){
			if( file_exists( $dir ) ){
				
				$loader->addPath($dir);
			}
		}
		$twig = new \Twig\Environment($loader, [
			//'cache' =>  ".."._MARION_TMP_DIR_,
		]);
		$this->loadTemplateVariables($twig);
		$this->loadTemplateFunctions();

		
		if( okArray($this->_twig_functions) ){
			foreach($this->_twig_functions as $func){
				$twig->addFunction($func);
			}
		}
		$this->_twig = $twig;
			


	}

	//carica le funzioni di template di base
	function loadTemplateFunctions(){
		
		$this->_twig_functions[] = new \Twig\TwigFunction('auth', function ($type) {
			return Marion::auth($type);
		});

		$this->_twig_functions[] = new \Twig\TwigFunction('okArray', function ($array) {
			return okArray($array);
		});

		$this->_twig_functions[] = new \Twig\TwigFunction('tr', function ($string,$module=null) {
			return _translate($string,$module);
		});

		$this->_twig_functions[] = new \Twig\TwigFunction('getHtmlCurrency', function ($code=NULL) {
				if( !$code ) $code = $GLOBALS['activecurrency'];
				return Marion::getHtmlCurrency($code);
		});

		$this->_twig_functions[] = new \Twig\TwigFunction('getConfig', function ($group=NULL,$key=null,$value=null) {
				
			return Marion::getConfig($group,$key,$value);
		});




		
	}



	//carica le variabili di template di base
	function loadTemplateVariables($twig){
		
		$_global_vars['activecurrency'] = _MARION_CURRENCY_;
		$_global_vars['activelocale'] = _MARION_LANG_;
		
		$_global_vars['currencyLabel'] = Marion::getHtmlCurrency(_MARION_CURRENCY_);
		if( $user = Marion::getUser() ){
			$_global_vars['userdata'] = $user;
		}
		$locales = Marion::getConfig('locale','supportati');
		$_global_vars['locales'] = array($_global_vars['activelocale']);
		
		foreach($locales as $loc){
			if( !in_array($loc,$_global_vars['locales'])){
				$_global_vars['locales'][] = $loc;
			}
		}
		
		
		if( isset($GLOBALS['gettext'])){
			$_global_vars['gettext'] = $GLOBALS['gettext'];
		}
		if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){
			$_protocollo = $_SERVER['HTTP_X_FORWARDED_PROTO'];
		}else{
			$_protocollo = !empty($_SERVER['HTTPS']) ? "https" : "http";
		}
		
		$_global_vars['baseurl'] = _MARION_BASE_URL_;
		if( isset($_SERVER['REDIRECT_QUERY_STRING']) && $_SERVER['REDIRECT_QUERY_STRING'] ){
			$_global_vars['return_location'] = $_SERVER['SCRIPT_NAME']."?".$_SERVER['REDIRECT_QUERY_STRING'];
		}elseif( $_SERVER['QUERY_STRING']){
			$_global_vars['return_location'] = $_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING'];
		}else{
			$_global_vars['return_location'] = $_SERVER['SCRIPT_NAME'];
		}

		foreach($_global_vars as $k => $v){
			$twig->addGlobal($k, $v);
		}
	}


	//associa una funzione di template a TWIG
	function addTemplateFunction($function=NULL){
			$this->_twig_functions[] = $function;
	}

	function addTwingTemplatesDir($dir){
		$this->_twig_tepplates_dir[] = $dir;
	}




	


}




?>