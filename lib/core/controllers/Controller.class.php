<?php
use Marion\Core\Marion;
class Controller{
	public $_auth = '';
	public $_required_access = true; //se valorizzato allora l'utente pu� accedere a questo controller solo se loggato
	public $_user;
	public $_ajax = false;
	public $_tmpl_obj;
	public $_ctrl;
	public $_action;

	public $_tool_buttons = [];
	public $_title;


	public $_root_directory; //variabile che contiene il path della root directory delll'applicazione
	
	// TWIG VARS
	public $_twig_global_vars = array();
	public $_twig_vars = array();
	public $_twig_functions = array();
	public $_twig_tepplates_dir = array();


	//VARIABILI PER IL FILE CSS E JS
	public $_files = array();
	public $_javascript = array();
	public $_css = array();


	public $errors = array(); //array contentente gli errori

	


	// COSTRUTTORE
	/* INPUT 
		$options = array(
			'auth_disabled'	 => 1 //disabilita il controllo dei permessi di accesso

		);
	*/
	function __construct($options=array()){
		$this->init($options);
		
		
		if( $this->_required_access && !$this->isLogged()){
			
			$this->notLogged();
		}
		
		
		if( isset($options['auth_disabled']) && $options['auth_disabled'] ){
			
		}else{
			if( !$this->checkAccess()){
				// non sei autorizzato

				$this->notAuth();
			}
		}
		
		if( $this->_ajax ){
			$this->ajax();
		}else{

			$this->setMedia();
			$this->display();
		}
	}
	
	// DISTRUTTORE
	function __destruct() {
        $this->closeDB();
	}


	function closeDB(){
		if( isset($GLOBALS['Database']) ){
			$obj = $GLOBALS['Database'];
			if( is_object($obj) ){
				$obj->close();
			}
		}
	}

	function setTitle($title){
		$this->_title = $title;
		return $this;
	}

	function addToolButton(UrlButton $button){
		$this->_tool_buttons[$button->getAction()] = $button;
		return $this;
	}

	function addToolButtons(array $buttons){
		foreach($buttons as $button){
			$this->addToolButton($button);
		}
		return $this;
	}
	function resetToolButtons(){
		$this->_tool_buttons = [];
		return $this;
	}

	function getToolButton($action){
		return $this->_tool_buttons[$action];
	}

	


	function getTemplateObj(){

		//funzione da eliminare	
	}

	
	function init($options=array()){
		if( defined('_MARION_DOCUMENT_ROOT_') ){
			$this->_root_directory = _MARION_DOCUMENT_ROOT_;
		}
		
		if( defined('_MARION_ROOT_DIR_') ){
			$this->_root_directory .= _MARION_ROOT_DIR_;
		}
		

		


		$this->getTemplateObj();
		
		$this->_user = Marion::getUser();
		if( _var('ajax') ){
			$this->_ajax = _var('ajax');
		}
		$this->_action = _var('action');
		
		if( array_key_exists('ctrl',$options) && $options['ctrl'] ){
			$this->setCtrl($options['ctrl']);
		}else{
			$this->setCtrl(preg_replace('/Controller/','',get_class($this)));
		}
		if( array_key_exists('url_script',$options) && $options['url_script'] ){
			$this->_url_script = $this->setUrlScript($options['url_script']);
		}else{
			$this->_url_script = $this->setUrlScript($_SERVER['PHP_SELF']."?ctrl=".$this->getCtrl());
		}

		//passo delle variabili twig in fase di costruzione del controller
		if( array_key_exists('twig_values',$options) && okArray($options['twig_values'])){
			foreach($options['twig_values'] as $k => $v){
				$this->setVar($k,$v);
			}
		}

		

		

		
		
	}
	/**
	* Metodo che restituisce il nome del controller
	*/
	function getCtrl(){
		return $this->_ctrl;
		
	}

	/**
	* Metodo che restituisce l'url corrente
	*/
	function getUrlCurrent(){
		return $_SERVER['REQUEST_URI'];
		
	}

	/**
	* Metodo che restituisce l'url base del controller
	*/
	function getUrlScript(){
		return $this->_url_script;
	}
	

	
	/**
	* Metodo che restituisce il valore di una variabile di template
	*
	*@param string $var nome della variabile
	*/
	function getVar($var){
		return $this->_tmpl_obj->$var;
	}
	
	/**
	* Metodo che restituisce i dati del form sottomesso
	*/
	function getFormdata($num=null){
		if(	$num ){
			if( $this->_ajax ){
				$formdata = _formdata($num);
			}else{
				$formdata = _var('formdata'.$num);
			}
		}else{
			if( $this->_ajax ){
				$formdata = _formdata();
			}else{
				$formdata = _var('formdata');
			}
		}		
		
		return $formdata;
	}

	/**
	* Metodo che restituisce l'action del controller
	*/
	function getAction(){
		return $this->_action;
	}

	/**
	* Metodo che restituisce l'url base del backend del sito
	*/
	function getBaseUrlBackend(){
		return _MARION_BASE_URL_.'backend/';
	}

	/**
	* Metodo che restituisce l'url base del sito
	*/
	function getBaseUrl(){
		return _MARION_BASE_URL_;
	}
	
	
	/**
	* Metodo che imposta il nome del controller
	*
	*@param string $ctrl nome del controller
	*/
	function setCtrl($ctrl){
		$this->_ctrl = $ctrl;
		
	}

	/**
	* Metodo che imposta l'url dello script
	*
	*@param string $url url
	*/
	function setUrlScript($url){
		return $this->_url_script = $url;
	}


	
	
	/**
	* Metodo che verifica se il form è stato sottomesso
	*
	*@return boolean
	*/
	function isSubmitted(){
		
		return okArray($this->getFormdata());
	}
	


	/**
	* Metodo che verifica se l'utente è abilitato ad accedere al controller
	*
	*@return boolean
	*/
	function checkAccess(){
		if( !$this->_auth ) return true;
		return $this->_user->auth($this->_auth);
	}
	//Stabilisce se un utente � loggato o meno
	function isLogged(){
		
		return authUser();
	}

	function outputString($string){
		
		$this->setTemplateVariables();

		
		$this->displayErrors();
		
		$this->initTwingTemplate();
		$tmpl = twig_template_from_string($this->_tmpl_obj, $string);
		echo $this->_tmpl_obj->render($tmpl, $this->_twig_vars);
		//resetto l'oggetto twig
		$this->_tmpl_obj = null;
	}
	
	// stampa una pagina di template
	function output($tmpl){
		$this->setTemplateVariables();
	
		
		$this->displayErrors();
		
		$this->initTwingTemplate();
		echo $this->_tmpl_obj->render($tmpl, $this->_twig_vars);
		//resetto l'oggetto twig
		$this->_tmpl_obj = null;
		
	}

	// imposta una variabile nel template
	function setVar($key,$val){
		$this->_twig_vars[$key] = $val;
	}


	
	

	function setMenu($tag){
		
		$item = MenuItem::prepareQuery()->where('tag',$tag)->getOne();
		
		if( is_object($item) ){
			
			
			if( $item->parent ){
				$parent = $item->getParent();
				$this->setVar('current_admin',$parent->tag);
				$this->setVar('current_admin_child',$item->tag);
			}else{
				$this->setVar('current_admin',$item->tag);
			}

		}

	}
	
	

	function notLogged(){
		$this->output('access/login.htm');
		exit;
	}

	function notAuth(){
		$this->output('access/not_auth.htm');
		exit;
	}

	function error($message=''){
		$this->setVar('message',$message);
		$this->output('common/error.htm');
		exit;
	}

	function setTemplateVariables(){
		
		$this->setVar('formID',_var('formID'));
		$this->setVar('action',$this->getAction());
		$this->setVar('ctrl',$this->getCtrl());
		$this->setVar('script_url',$this->getUrlScript());
		$this->setVar('url_current',$this->getUrlCurrent());
		$this->setVar('locales',Marion::getConfig('locale','supportati'));
		$this->setVar('tool_buttons',$this->_tool_buttons);
		$this->setVar('title',$this->_title);


		
	
		//debugga($this->_javascript);exit;
		
		if( array_key_exists('head',$this->_javascript) && okArray($this->_javascript['head']) ){
			$js_list_head = array();
			ksort($this->_javascript['head']);
			foreach($this->_javascript['head'] as $list_files){
				foreach($list_files as $v){
					$js_list_head[] = $v;
				}
			}
			
			$this->setVar('javascript_head',$js_list_head);
			
		}

		if( array_key_exists('end',$this->_javascript) && okArray($this->_javascript['end']) ){
			$js_list_end = array();
			ksort($this->_javascript['end']);
			foreach($this->_javascript['end'] as $list_files){
				foreach($list_files as $v){
					$js_list_end[] = $v;
				}
			}
			$this->setVar('javascript_end',$js_list_end);
			
		}

		
		
		
		$this->setVar('css',$this->_css);


		
		
	}
	/**
	* Permette di registrare uno script js al controller
	*
	* @param string $url url (relativa o assoluta) del file js
	* @param string $position posizione dove verrà caricato lo script. Valori ammessi 'head','end'
	* @param integer $priority ordine di caricamento dello script
	*/
	function registerJS($url,$position='head',$priority=99){
		$position = strtolower($position);
		
		if( !in_array($position,array('head','end')) ) return;
		if( okArray($this->_files) ){
			if( !in_array($url,$this->_files) ){
				$this->_files[] = $url;
				$this->_javascript[$position][$priority][] = $url;
			}
		}else{
			$this->_files[] = $url;
			$this->_javascript[$position][$priority][] = $url;
		}
	}
	/**
	* Permette di rimuovere da un controller un file js registrato
	*
	* @param string $url url (relativa o assoluta) del file js
	* @param string $position posizione dove si trova lo script registrato. Valori ammessi 'head','end'
	*/
	function unregisterJS($url,$position='head'){
		$position = strtolower($position);
		
		if( !in_array($position,array('head','end')) ) return;
		if( okArray($this->_files) ){
			
			if( in_array($url,$this->_files) ){
				$pos = array_search($url,$this->_files);
				if( $pos === false ) return;
				unset($this->_files[$pos]);
				foreach($this->_javascript[$position] as $priority => $values){
					$pos = array_search($url,$values);
					if( $pos ) {
						unset($this->_javascript[$position][$priority][$pos]);
						break;
					}
				}
				
			}
		}
	}
	/**
	* Permette di registrare uno foglio di stile (css) al controller
	*
	* @param string $url url del file css
	*/
	function registerCSS($url){
		if( okArray($this->_files) ){
			if( !in_array($url,$this->_files) ){
				$this->_files[] = $url;
				$this->_css[] = $url;
			}
		}else{
			$this->_files[] = $url;
			$this->_css[] = $url;
		}
	}

	/**
	* Permette di rimuovere dal controller un foglio di stile (css) registrato
	*
	* @param string $url url del file css
	*/
	function unregisterCSS($url){
		if( okArray($this->_files) ){
			if( in_array($url,$this->_files) ){
				$pos = array_search($url,$this->_files);
				if( $pos === false ) return;
				unset($this->_files[$pos]);
				$pos = array_search($url,$this->_css);
				unset($this->_css[$pos]);
			}
		}
	}
	



	/**
	* Metodo in cui vengono reindirizzate tutte le chiamate ajax
	*/
	function ajax(){


	}
	
	/**
	* Metodo a cui vengono reindirizzate tutte le chiamate del controller ad eccezione di quelle ajax
	*
	*/
	function display(){

	}


	/**
	* In questa metodo è possibile registrare tutti i file js e css da utilizzare nel controller
	*/
	function setMedia(){
		//qui puoi inserire i js e i css
	}



	
	/**
	* Metodo che si occupa di mostrare a video gli errori memorizzati nell'attributo $errors
	*/
	function displayErrors(){
		if( isset($this->errors) && okArray($this->errors) ){
			$message = '';
			if( count($this->errors) > 1 ){
				$message = "<ul>";
				foreach($this->errors as $v){
					$message .= "<li>{$v}</li>";
				}
				$message .= "</ul>";
			}else{
				$message = $this->errors[0];
			}
			
			$this->displayMessage($message,'danger');
		}

	}

	/**
	* Metodo che si occupa di mostrare a video un messaggio di alert
	*
	* @param string $message contenuto del messaggio da mostrare
	* @param string $type tipologia di visualizzazione del messaggio. [danger, success, warning]
	*/
	function displayMessage($message,$type='success'){

		$message_old = $this->getVar('message');
		$message = $message;
		$div = "<div class='alert alert-{$type}' style='width:100%; position:relative;color:#000000;'>";
		$div .= "<a style='position:absolute; color:#000000; right:5px;top:0;cursor:pointer;' onclick='$(this).parent().remove()'><i class='fa fa-times'></i></a>";
		$div .= $message;
		$div .= "</div>";

		$div = $message_old.$div;
		$this->setVar('messages',$div);
	}





	// TWIG TEMPLATE INTEGRATION
	
	function checkDataForm($nameform=null,$data=null,$override=null,$ctrl=null){
		$dataform = null;
		if( $nameform ){
			$form = new Form($nameform);
			
			if( is_object($form) ){
				if( okArray($override)){
					$form->addElements($override);
				}
				if(!$ctrl){
					$dataform = $form->checkData($data,$this);
				}else{
					$dataform = $form->checkData($data,$ctrl);
				}
			}
		}

		return $dataform;
	}


	function getDataForm($nameform=null,$data=null,$ctrl=null,$override=null){
		$dataform = null;
		if( $nameform ){
			$form = new Form($nameform);
			if( is_object($form) ){
				if( okArray($override)){
					
					$form->addElements($override);
				}
				if(!$ctrl){
					$dataform = $form->prepareData($data,$this);
				}else{
					$dataform = $form->prepareData($data,$ctrl);
				}
			}
		}

		return $dataform;
	}
	function initTwingTemplate(){
		
		$loader = new \Twig\Loader\FilesystemLoader('templates_twig');
		if( okArray($this->_twig_tepplates_dir) ){
			foreach($this->_twig_tepplates_dir as $dir ){
				$loader->addPath($dir);
			}
		}
		
		$options_twig = array(
			'debug' => _MARION_DISPLAY_ERROR_,
		);
		global $_MARION_ENV;
		if( $_MARION_ENV['TWIG']['cache'] ){
			//$options_twig['cache'] = ".."._MARION_TMP_DIR_;
		}
		

		$twig = new \Twig\Environment($loader,$options_twig);
		
		$twig->addExtension(new \Twig\Extension\StringLoaderExtension());
		
		$this->loadTemplateVariables($twig);
		$this->loadTemplateFunctions();


		
		
		if( okArray($this->_twig_functions) ){
			foreach($this->_twig_functions as $func){
				$twig->addFunction($func);
			}
		}

		if( okArray($this->_twig_global_vars) ){
			foreach($this->_twig_global_vars as $k => $v){
				$twig->addGlobal($k,$v);
			}
		}

		
		
		$filter = new \Twig\TwigFilter('serialize', function ($array) {
			return serialize($array);
		});

		$twig->addFilter($filter);
		/*putenv('LC_ALL=it_IT');
		setlocale(LC_ALL, 'it_IT');

		// Specify the location of the translation tables
		bindtextdomain('marion', 'translate_twig');
		bind_textdomain_codeset('marion', 'UTF-8');

		// Choose domain
		textdomain('marion');
		*/
		$this->getSideMenu();
		$this->getNotifications();
		$this->_tmpl_obj = $twig;
		
	}

	function getNotifications(){

		$user = Marion::getUser();
		//NOTIFICATIONS
		$notifications = Notification::prepareQuery()
				->where('view',0)
				->where('receiver',$user->id)
				->orderBy('id','DESC')->limit(10)->get();
		$this->setVar('num_notifications', Notification::getCount());
		$this->setVar('notifications',$notifications);
	}

	function getSideMenu(){

		$query = MenuItem::prepareQuery()->where('scope','admin')->where('active',1)->orderby('priority','ASC');		
		$list = $query->get();
		//$template = _obj('Template');
		foreach($list as $k => $v){
			if( $v->showLabel ){
				$function = $v->labelFunction;
				
				if(function_exists($function) ){
					$v->labelText = $function();
				}
				
			}
		}
		$toreturn = Base::buildtree($list);
		foreach($toreturn as $k => $v){
			if( $v->children ){
				uasort($toreturn[$k]->children,function($a,$b){
					if ($a->priority==$b->priority) return 0;
					return ($a->priority<$b->priority)?-1:1;
				});
			}
		}
		uasort($toreturn,function($a,$b){
			if ($a->priority==$b->priority) return 0;
			return ($a->priority<$b->priority)?-1:1;
		});
		
		//debugga($toreturn);exit;
		$this->setVar('menu_admin_items',$toreturn);
		
	}

	//carica le funzioni di template di base
	function loadTemplateFunctions(){
		
		$_twig_functions = array();
		

		$_twig_functions[] = new \Twig\TwigFunction('current_menu_admin', function ($a,$b) {
			if( $a == $b){
				return "active current hasSub";
			}
			return '';
		});
		
		$_twig_functions[] = new \Twig\TwigFunction('display_widget', function ($url) {
			$query = Page::prepareQuery()->where('url',$url);

			if( !auth('cms_page') ){
				$query->where('visibility',1);
			}

			$page = $query->getOne();
			if( is_object($page) ){
				$locale = $GLOBALS['activelocale'];
				return $page->get('content',$locale);
			}

			return false;
			
		});

		$_twig_functions[] = new \Twig\TwigFunction('auth', function ($type) {
			return Marion::auth($type);
		});

		$_twig_functions[] = new \Twig\TwigFunction('getConfig', function ($group,$key) {
			$val = Marion::getConfig($group,$key);
			if( $val ) return $val;
			return '';
		});


		$_twig_functions[] = new \Twig\TwigFunction('okArray', function ($array) {
			return okArray($array);
		});

		$_twig_functions[] = new \Twig\TwigFunction('tr', function ($string,$module=null) {
			return _translate($string,$module);
		});

		$_twig_functions[] = new \Twig\TwigFunction('getHtmlCurrency', function ($code=NULL) {
			if( !$code ) $code = $GLOBALS['activecurrency'];
			return Marion::getHtmlCurrency($code);
		});

		$_twig_functions[] = new \Twig\TwigFunction('formattanumero', function ($val=NULL) {
			return number_format($val, 2, ',', '');
		});

		$_twig_functions[] = new \Twig\TwigFunction('dataIta', function ($val=NULL) {
			return strftime('%d/%m/%Y',strtotime($val));
		});

		$_twig_functions[] = new \Twig\TwigFunction('dataOraIta', function ($val=NULL) {
			return strftime('%d/%m/%Y %H:%M',strtotime($val));
		});

		
		$_twig_functions[] = new \Twig\TwigFunction('do_action', function ($hook,$par1=NULL,$par2=NULL,$par3=NULL,$par4=NULL) {
			$params = array();
			if( $par1 ){
				$params[] = $par1;
			}
			if( $par2 ){
				$params[] = $par2;
			}
			if( $par3 ){
				$params[] = $par3;
			}
			if( $par4 ){
				$params[] = $par4;
			}
			if( !okArray($params) ){
				unset($params);
			}
			if( isset($params) ){
				Marion::do_action($hook,$params);
			}else{
				Marion::do_action($hook);
			}
		});

		$_twig_functions[] = new \Twig\TwigFunction('isActivedModule', function ($module=NULL) {
			return Marion::isActivedModule($module);
		});

		
		foreach($_twig_functions as $func){
			$this->addTemplateFunction($func);
		}

		

	   

		
  

		
	}

	function addTwingTemplatesDir($dir){
		$this->_twig_tepplates_dir[] = $dir;
	}

	//carica le variabili di template di base
	function loadTemplateVariables($twig){
		if( isset($GLOBALS['activelocale']) ){
			$_global_vars['activelocale'] = $GLOBALS['activelocale'];
		}elseif( isset($GLOBALS['setting']['default']['LOCALE']['default']) && $GLOBALS['setting']['default']['LOCALE']['default'] ){
			$_global_vars['activelocale'] = $GLOBALS['setting']['default']['LOCALE']['default'];
		}else{
			$_global_vars['activelocale'] = 'it';
		}
		if( $GLOBALS['activecurrency'] ){
			$_global_vars['activecurrency'] = $GLOBALS['activecurrency'];
		}else{
			$_global_vars['activecurrency'] = 'EUR';
		}
		
		$_global_vars['currencyLabel'] = Marion::getHtmlCurrency($_global_vars['activecurrency']);
		
		$user = Marion::getUser();
		if( $user ){
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
		$_global_vars['abslolute_baseurl'] = $_protocollo."://".$GLOBALS['setting']['default']['GENERALE']['baseurl']._MARION_BASE_URL_;
		
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
			if($function && is_object($function) && ($function instanceof \Twig\TwigFunction)){
				if( !array_key_exists($function->getName(),$this->_twig_functions) ){
					$this->_twig_functions[$function->getName()] = $function;
				}
			}
	}


	//associa una variabile globale a TWIG
	function addGlobalVar($key,$value=NULL){
			$this->_twig_global_vars[$key] = $value;
	}

	





	// funzioni che permette di caricare le librerie js
	function loadJS($library){
		if( is_string($library) ){
			$libraries = array($library);
		}else{
			$libraries = $library;
		}
		
		if( okArray($libraries) ){
			foreach($libraries as $lib){

				switch($lib){
					case 'slick':
						$this->registerJS($this->getBaseUrl().'plugins/slick/slick.min.js','end',10);
						$this->registerCSS($this->getBaseUrl().'plugins/slick/slick.css');
						
						break;
					case 'bxslider':
						$this->registerJS($this->getBaseUrl().'plugins/bxslider-4/dist/jquery.bxslider.min.js','end',10);
						$this->registerCSS($this->getBaseUrl().'plugins/bxslider-4/dist/jquery.bxslider.min.css');
						break;
					case 'fancybox':
						$this->registerCSS($this->getBaseUrl().'plugins/fancybox/dist/jquery.fancybox.min.css');	
						$this->registerJS($this->getBaseUrl().'plugins/fancybox/dist/jquery.fancybox.min.js','head',10);
						break;
					case 'multiselect':
						$this->registerJS($this->getBaseUrl().'plugins/lou-multi-select/js/jquery.multi-select.js','end',10);
						$this->registerCSS($this->getBaseUrl().'plugins/lou-multi-select/css/multi-select.css');
						break;
					case 'spectrum':

						$this->registerJS($this->getBaseUrl().'plugins/spectrum/spectrum.js','head',10);
						$this->registerCSS($this->getBaseUrl().'plugins/spectrum/spectrum.css');
						break;
					case 'jstree':
						$this->registerJS($this->getBaseUrl().'plugins/jstree-vakata/dist/jstree.min.js','end',10);
						$this->registerCSS($this->getBaseUrl().'plugins/jstree-vakata/dist/themes/default/style.min.css');
						break;
				}

			}
		}
		

	}

}




?>