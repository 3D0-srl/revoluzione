<?php
class PageComposer{
	public $template_page;
	public $preview_page;


	public $custom_css;
	public $custom_css_tmp;
	
	public $custom_js_head;
	public $custom_js_head_tmp;

	public $custom_js_end;
	public $custom_js_end_tmp;


	public $blocks;
	public $preview = false;




	public $composition_block = array();
	private $_class_row =array(
		'row-1' => array('col-w-100'),
		'row-2' => array('col-w-50','col-w-50'),
		'row-3' => array('col-w-33','col-w-33','col-w-33'),
		'row-4' => array('col-w-50','col-w-50','col-w-50','col-w-50'),
		'row-25-75' => array('col-w-s-25','col-w-75'),
		'row-75-25' => array('col-w-75','col-w-d-25'),

	);


	
	function __construct($id=null,$preview=false,$options=array()){
		$this->id_page = $id;
		$this->preview = $preview;
		

		if( isset($options['no_current']) && $options['no_current'] ){
			$no_default_pagecomposer =  $options['no_current'];
		}else{
			$no_default_pagecomposer = false; //non prendo il pagecomposer objsct currente
		}

		$this->init();

		if(!$no_default_pagecomposer){
			
			self::setCurrent($this);
		}
		
		
		$this->loadMedia();
	}
	

	function checkConditionDevice($row=array()){
		$check = false;

		switch(_MARION_DEVICE_){
			case 'MOBILE':
				$check =  $row['enable_mobile'];
			break;
			case 'TABLET':
				$check =  $row['enable_tablet'];
			break;
			case 'DESKTOP':
				$check =  $row['enable_desktop'];
			break;
		}
		return $check;
	}


	function getClassesDevice($row=array()){

		$class = 'pagecomposer-detect-mobile ';
		if( $row['enable_mobile'] ){
			$class .= 'pagecomposer-mobile-device ';
		}
		if( $row['enable_tablet'] ){
			$class .= 'pagecomposer-tablet-device ';
		}

		if( $row['enable_desktop'] ){
			$class .= 'pagecomposer-desktop-device ';
		}
		return $class;
	}


	function getDeviceCondition(){
		switch(_MARION_DEVICE_){
			case 'MOBILE':
				$condition = "enable_mobile = 1";
			break;
			case 'TABLET':
				$condition = "enable_tablet = 1";
			break;
			case 'DESKTOP':
				$condition = "enable_desktop = 1";
			break;
		}	
		return $condition;
	}


	function getComposition(){
		if( $this->id_page ){
			$database = _obj('Database');
			$data = $database->select('p.id,id_layout,blocks,template,custom_css,custom_css_tmp,custom_js_head,custom_js_head_tmp,custom_js_end,custom_js_end_tmp','page_advanced as p join layout_page as l on l.id=p.id_layout',"p.id={$this->id_page}");
			
			if( okArray($data) ){
				$data = $data[0];
				$this->template_page = $data['template'];
				$this->blocks = json_decode($data['blocks']);
				$this->custom_css = $data['custom_css'];
				$this->custom_css_tmp = $data['custom_css_tmp'];
				$this->custom_js_head = $data['custom_js_head'];
				$this->custom_js_end = $data['custom_js_end'];
				$this->custom_js_head_tmp = $data['custom_js_head_tmp'];
				$this->custom_js_end_tmp= $data['custom_js_end_tmp'];
			}
			if( $this->preview ){
				$select = $database->select('*','composition_page_tmp',"id_adv_page={$this->id_page} AND active=1 order by orderView");
			}else{
				$select = $database->select('*','composition_page',"id_adv_page={$this->id_page} AND active = 1 order by orderView");
			}
			if(okArray($select)){
				foreach($select as $v){
					//debugga($v['detect_mobile_type']);
					if( $v['detect_mobile_type'] == 'server'){
						if( !$this->checkConditionDevice($v) ) continue;
					}
					
					if( $v['detect_mobile_type'] == 'client' ){
						$this->registerJS('plugins/mobile-detect/mobile-detect.min.js','end',1);
						$this->registerJS('plugins/mobile-detect/mobile-detect.modernizr.js','end',1);
						$this->registerJS('modules/pagecomposer/js/pagecomposer_device.js?v=1','end',1);
					}
					if( in_array($v['block'],$this->blocks )){ 
						$this->composition_block[$v['block']][] = $v;
					}
				}
			}
			
			
			
			
		}
	}

	public function init(){
		$this->getComposition();

		
	}

	public function loadMedia(){
		if(okArray($this->composition_block)){
			foreach($this->composition_block as $block => $items){
				foreach($items as $v){ 
					$function = $v['module_function'];
					
					if( class_exists($function) ){
						
						$object = new $function();
						
						$object->registerJS($v);
						$object->registerCSS($v);
						
					}
					if( $v['type'] == 'popup_container'){
						$this->registerJS(_MARION_BASE_URL_.'plugins/slick-modal-css3-powered-popups/src/plugin/js/jquery.slickmodal.min.js','head');
						$this->registerCSS(_MARION_BASE_URL_.'plugins/slick-modal-css3-powered-popups/src/plugin/css/slickmodal.min.css');
					}

					if( $v['type'] == 'accordion_container'){
						
						//$this->registerCSS('/modules/pagecomposer/css/tabs.css');
						$this->registerJS(_MARION_BASE_URL_.'modules/pagecomposer/js/pagecomposer_accordions.js','head');
					}
				}
				
			}
		}
	}


	function setMedia(){
		
	}

	/*function setMedia2(){
		$template = _obj('Template');
		$template->custom_css = $this->custom_css;
		
		if( okArray($template->css_files) ){
			$template->css_files = array_unique(array_intersect($this->css_files,$template->css_files));
		}else{
			$template->css_files = $this->css_files;
		}
		
		$template->css_files = $this->css_files;
		if( okArray($template->javascript_end) ){
			$template->javascript_end = array_unique(array_intersect($this->javascript_files['end'],$template->javascript_end));
		}else{
			$template->javascript_end = $this->javascript_files['end'];
		}

		if( okArray($template->javascript_head) ){
			$template->javascript_head = array_unique(array_intersect($this->javascript_files['head'],$template->javascript_head));
		}else{
			$template->javascript_head = $this->javascript_files['head'];
		}
	}*/

	
	public function addDataToCtrl($ctrl){
		
		if( array_key_exists('pagecomposer_libreries',$GLOBALS) && okArray($GLOBALS['pagecomposer_libreries']) ){
			foreach(array_values($GLOBALS['pagecomposer_libreries']) as $lib){
				
				
				
				$ctrl->loadJS($lib);
			}
		}
		
		
		$ctrl->registerCSS('modules/pagecomposer/css/pagecomposer.css');
		$ctrl->registerCSS('modules/pagecomposer/css/aos.css');

		$ctrl->registerJS('modules/pagecomposer/js/aos.js','head');
		$ctrl->setVar('id_page_composer', $this->id_page);
		if( $this->preview ){
			$ctrl->setVar('custom_js_head', $this->custom_js_head_tmp);
			$ctrl->setVar('custom_js_end', $this->custom_js_end_tmp);
			$ctrl->setVar('custom_css', $this->custom_css_tmp);
		}else{
			
			if( file_exists(_MARION_MODULE_DIR_."pagecomposer/media/js/js_head_{$this->id_page}.js") ){

				$ctrl->registerJS("modules/pagecomposer/media/js/js_head_{$this->id_page}.js",'head');
				
			}	
			if( file_exists(_MARION_MODULE_DIR_."pagecomposer/media/js/js_end_{$this->id_page}.js") ){

				$ctrl->registerJS("modules/pagecomposer/media/js/js_end_{$this->id_page}.js",'end');
				
			}
			
			if( file_exists(_MARION_MODULE_DIR_."pagecomposer/media/css/css_{$this->id_page}.css") ){

				$ctrl->registerCSS("modules/pagecomposer/media/css/css_{$this->id_page}.css");
				
			} 
		}
		//debugga($this);exit;
		$ctrl->setVar('layout_page', $this->template_page);
		$ctrl->setVar('preview_page', $this->preview_page);


		$javascript_head = array();
		$javascript_end = array();
		$css_files = array();
		
		if( isset($GLOBALS['pagecomposer_js_files']['head']) && okArray($GLOBALS['pagecomposer_js_files']['head']) ){
			ksort($GLOBALS['pagecomposer_js_files']['head']);
			foreach($GLOBALS['pagecomposer_js_files']['head'] as $files){
				foreach($files as $file){
					if( okArray($javascript_head) ){
						if( !in_array($file,$javascript_head) ){
							$javascript_head[] = $file;
						}
					}else{
						$javascript_head[] = $file;
					}
				}
			}
		}
		if( isset($GLOBALS['pagecomposer_js_files']['end']) && okArray($GLOBALS['pagecomposer_js_files']['end']) ){
			ksort($GLOBALS['pagecomposer_js_files']['end']);
			foreach($GLOBALS['pagecomposer_js_files']['end'] as $files){
				foreach($files as $file){
					if( okArray($javascript_end) ){
						if( !in_array($file,$javascript_end) ){
							$javascript_end[] = $file;
						}
					}else{
						$javascript_end[] = $file;
					}
				}
			}
		}
		if( isset($GLOBALS['pagecomposer_css_files']) && okArray($GLOBALS['pagecomposer_css_files']) ){
			foreach($GLOBALS['pagecomposer_css_files'] as $file){
				if( okArray($css_files) ){
					if( !in_array($file,$css_files) ){
						$css_files[] = $file;
					}
				}else{
					$css_files[] = $file;
				}
			}
		}

		if( !okArray($javascript_end) || !in_array('modules/pagecomposer/js/pagecomposer.js',$javascript_end) ){
			$javascript_end[] = 'modules/pagecomposer/js/pagecomposer.js';
		}
		

		foreach($javascript_head as $v){
			$ctrl->registerJS($v,'head');
		}
		
		foreach($javascript_end as $v){
			$ctrl->registerJS($v,'end');
		}

		foreach($css_files as $v){
			$ctrl->registerCSS($v);
		}

		
	
	}

	public function render($twig = false){
		//$this->setMedia();
		if( $twig ){
			

			$view = new View();

			$view->addTemplateFunction(
				new \Twig\TwigFunction('page_composer', function ($val) {
					return page_composer($val);
				})
			);
			$view->setVar('id_page_composer', $this->id_page);
			if( $this->preview ){
				$view->setVar('custom_js_head', $this->custom_js_head_tmp);
				$view->setVar('custom_js_end', $this->custom_js_end_tmp);
				$view->setVar('custom_css', $this->custom_css_tmp);
			}else{
				$view->setVar('custom_js_head', $this->custom_js_head);
				$view->setVar('custom_js_end', $this->custom_js_end);
				$view->setVar('custom_css', $this->custom_css);
			}

			if( file_exists('modules/pagecomposer/media/js/js_head_{$this->id_page}.js') ){
				
				$view->setVar('js_head_page_link', 'modules/pagecomposer/media/js/js_head_{$this->id_page}.js');
				
			}	
			if( file_exists('modules/pagecomposer/media/js/js_end_{$this->id_page}.js') ){

				$view->setVar('js_end_page_link', 'modules/pagecomposer/media/js/js_end_{$this->id_page}.js');
				
			}	
			$view->setVar('layout_page', $this->template_page);
			$view->setVar('preview_page', $this->preview_page);
			$view->output('pagecomposer.htm');
		}else{
			// DA ELIMINARE: GESTIONE FLEXY
			/*$template = _obj('Template');
			$template->id_page_composer = $this->id_page;
			if( $this->preview ){
				$template->custom_js_head = $this->custom_js_head_tmp;
				$template->custom_js_end = $this->custom_js_end_tmp;
				$template->custom_css = $this->custom_css_tmp;
			}else{

				
				$template->custom_js_head = $this->custom_js_head;
				$template->custom_js_end = $this->custom_js_end;
				$template->custom_css = $this->custom_css;

			}

			if( file_exists('modules/pagecomposer/media/js/js_head_{$this->id_page}.js') ){
				$template->js_head_page_link = 'modules/pagecomposer/media/js/js_head_{$this->id_page}.js';
			}	
			if( file_exists('modules/pagecomposer/media/js/js_end_{$this->id_page}.js') ){
				$template->js_end_page_link = 'modules/pagecomposer/media/js/js_end_{$this->id_page}.js';
			}	
			
			
			$template->preview_page = $this->preview;
			
			Marion::do_widget($this->template_page);*/
		}
	}





	function duplicate($id_page){
		if( okArray($this->composition_block) ){
			foreach($this->composition_block as $list){
				$tree = $this->buildTree($list);
				foreach($tree as $v){
					$this->duplicateItem($v,$id_page);
				}
			}
		}
		

		
		
	}

	function duplicateItem($v,$id_page){
		$tmp = $v;
		unset($tmp['id']);
		unset($tmp['children']);
		$tmp['id_adv_page'] = $id_page;
		
		$database = _obj('Database');
		$id = $database->insert('composition_page',$tmp);
		
		if( okArray($v['children']) ){
			foreach($v['children'] as $v1){
				
				$v1['parent'] = $id;
				$this->duplicateItem($v1,$id_page);
			}
		}
	}
	

	public static function setCurrent($obj){
		$GLOBALS['pagecomposer_current'] = $obj;
	}

	public static function getCurrent(){

		
		if( !array_key_exists('pagecomposer_current',$GLOBALS) ){
			
			$GLOBALS['pagecomposer_current'] = new PageComposer();
		}
		
		return $GLOBALS['pagecomposer_current'];
	}

	
	public static function loadJS($library){
		$GLOBALS['pagecomposer_libreries'][] = $library;
	}
	
	public static function registerJS($link,$position='end',$priority=99){
		$composer = self::getCurrent();
		$composer->register($link,'js',$position,$priority);
	}



	public static function registerCSS($link){
		$composer = self::getCurrent();
		$composer->register($link,'css');
	}


	function register($link=null,$type=null,$position=null,$priority=99){
		if( !isset($GLOBALS['pagecomposer_files']) ) $GLOBALS['pagecomposer_files'] = array();
		
		if( !array_key_exists($link,$GLOBALS['pagecomposer_files']) ){
			$GLOBALS['pagecomposer_files'][$link] = $link;
			if( strtoupper($type) == 'JS' ){
				$GLOBALS['pagecomposer_js_files'][$position][$priority][] = $link;
			}
			if( strtoupper($type) == 'CSS' ){
				$GLOBALS['pagecomposer_css_files'][] = $link;
			}
		}
	
	}
	


	function buildWidget($v,$parent){
		
		switch($v['type']){
			case 'page':
				$page = Page::withId($v['id_page']);

				
				if( is_object($page) ){
					$locale = $GLOBALS['activelocale'];
					$_html = $page->get('content',$locale);
				}

				break;
			case 'module':
				$function = $v['module_function'];
				if( function_exists($function) ){
					ob_start();
					$function($v);
					$html = ob_get_contents();
					ob_end_clean();
					$_html = $html;

				}
				if( class_exists($function) ){
					$object = new $function();
					ob_start();
					$object->init($v);
					$object->_container_box = $parent['type'];
					$object->build();
					$html = ob_get_contents();
					ob_end_clean();
					$_html = $html;

				}

				break;
			case 'element':
				$_html = $v['content'];
				break;
			case 'tab':
				//debugga($v);exit;
				//$_html = $v['content'];
				break;
			
		}

		return $_html;

	}



	function buildTabsHorizontal(&$v){
		$html = "<nav><ul id='tab_widget_{$v['id']}' class='nav nav-tabs tabs-list'>";
		$first = true;
		foreach($v['children'] as $k1=> $v1){
			$parameters = unserialize($v1['parameters']);
			$id_tab  = $parameters['id_tab'];
			$class_tab  = $parameters['class_tab'];
			if( $first ){
				$class_tab .= ' active';
				$v['children'][$k1]['class'] .= ' in active'; 
			}
			
			$href = "tab".$v1['id'];
			
			$title = $parameters['title'][$GLOBALS['activelocale']];
			$html .= "<li class='{$class_tab}'><a href='#{$href}' data-toggle='tab' id='{$id_tab}'>{$title}</a></li>";
			$first = false;
		}
		
		$html .="</ul></nav><div id='tab_widget_content_{$v['id']}' class='tab-content'>";

		foreach($v['children'] as $k => $v1){
			$html .= $this->buildRow($v1,$v);
		}
		$html .= "</div>";

		return $html;
		
	}

	function buildTabsVertical(&$v,$parameters){

		//creo il menu
		
		if( $parameters['menu_position'] == 'right'){
			$menu_right = true;
		}
		
		
		$menu_dimension = '7';
		$content_dimension = '5';
		if( $parameters['menu_dimension'] ){
			$menu_dimension = $parameters['menu_dimension'];
		}
		if( $parameters['content_dimension'] ){
			$content_dimension = $parameters['content_dimension'];
		}
		$html_menu = " <div class='col-xs-{$menu_dimension}'><ul class='nav nav-tabs nav-stacked'>";



		
		$first = true;
		foreach($v['children'] as $k1=> $v1){
			$parameters = unserialize($v1['parameters']);
			$id_tab  = $parameters['id_tab'];
			$class_tab  = $parameters['class_tab'];
			if( $first ){
				$class_tab .= ' active';
				$v['children'][$k1]['class'] .= ' in active'; 
			}
			
			$href = "tab".$v1['id'];
			
			$title = $parameters['title'][$GLOBALS['activelocale']];
			$html_menu .= "<li class='{$class_tab}'><a href='#{$href}' data-toggle='tab' id='{$id_tab}'>{$title}</a></li>";
			$first = false;
		}
		
		$html_menu .="</ul></div>";

		$html_content = "<div class='col-xs-{$content_dimension}'><div class='tab-content'>";
		foreach($v['children'] as $k => $v1){
			$html_content .= $this->buildRow($v1,$v);
		}
		$html_content .= "</div></div>";


		
		if( $menu_right ){
			$html = $html_content.$html_menu;
		}else{
			$html = $html_menu.$html_content;
		}

		
		//creo il contenuto


		return $html;
		
	}





	function buildRow($v,$parent=NULL){
		$_html = '';
		if( $v['cache'] ){
			$cache = _obj('Cache');	
			$key_cache = 'page_composer_block_'.$v['id']."_".$GLOBALS['activelocale'];
			
			$_html = $cache->get($key_cache);
			if( $_html ){ 
				
				return $_html;
			}
		}
		if( !isset($v['attribute']) ) $v['attribute'] = '';
		
		switch($v['type']){
		
			case 'tabs':
				$parameters = unserialize($v['parameters']);
				if( $parameters['tabs_type'] == 'vertical'){
					$vertical = true;
				}
				
				if( $vertical ){
					$_html .= "<div class='{$v['class']}' {$v['attribute']} id='{$v['id_html']}'>";
				}else{
					$_html .= "<div class='tabcordion tabs tabs-style-bar {$v['class']}' id='{$v['id_html']}'>";
				}
				break;
			case 'accordion_container':
				$img_esplodi = '/modules/pagecomposer/img/ico-piu.png';
				$img_chiudi = '/modules/pagecomposer/img/ico-meno.png';
				
				$parameters = unserialize($v['parameters']);
				if( okArray($parameters) ){
					if( $parameters['custom_image'] ){
						$img_esplodi = '/media/images/'.$parameters['image_plus'];
						$img_chiudi = '/media/images/'.$parameters['image_minus'];
					}
				}

				$v['attribute'] .= " img_plus='{$img_esplodi}' img_minus='{$img_chiudi}' ";
				$_html .= "<div class='pagecomposer-accordion-container {$v['class']}' {$v['attribute']} id='{$v['id_html']}'>";
				break;
			case 'accordion':
				$img_esplodi = '/modules/pagecomposer/img/ico-piu.png';
				if( $parent ){
					$parameters_parent = unserialize($parent['parameters']);
					if( okArray($parameters_parent) ){
						if( $parameters_parent['custom_image'] ){
							$img_esplodi = '/media/images/'.$parameters_parent['image_plus'];
						}
					}
				}
				$parameters = unserialize($v['parameters']);
				$_class_accordion = $parameters['class_tab'];
				$_class_accordion_content = $parameters['class_tab_content'];
				$_id_accordion = $parameters['id_tab'];
				$_titolo_accordion = $parameters['title'][$GLOBALS['activelocale']];
				$_html .= "<div class='faq-composer {$_class_accordion}' id='{$_id_accordion}'><div class='tit-faq'>{$_titolo_accordion}<img src='{$img_esplodi}'></div><div class='content-faq {$_class_accordion_content}'>";
				break;
			case 'popup_container':
				
				
				$_params_popup = unserialize($v['parameters']);
				$_params_popup['popup_closeButtonText'] = trim($_params_popup['close_button_text'][$GLOBALS['activelocale']]);
				$_params_popup['popup_redirectOnCloseUrl'] = trim($_params_popup['redirect_on_close_url'][$GLOBALS['activelocale']]);
				$attribute_popup = '';
				if( trim($_params_popup['popup_css']) ){

					$_tmp_pop = explode(';',$_params_popup['popup_css']);
					foreach($_tmp_pop as $_v){
						$_tmp_pop2 = explode(':',$_v);
						$_arr[trim(trim($_tmp_pop2[0]),"'")] = trim(trim($_tmp_pop2[1]),"'");
					}

					

					$attribute_popup .= ' popup_css='.json_encode($_arr);
				}

				if( trim($_params_popup['overlay_css']) ){
					$_tmp_pop = explode(';',$_params_popup['overlay_css']);
					foreach($_tmp_pop as $_v){
						$_tmp_pop2 = explode(':',$_v);
						$_arr[trim(trim($_tmp_pop2[0]),"'")] = trim(trim($_tmp_pop2[1]),"'");
					}

					

					$attribute_popup .= ' overlay_css='.json_encode($_arr);
				}

				if( trim($_params_popup['mobile_css']) ){
					$_tmp_pop = explode(';',$_params_popup['mobile_css']);
					foreach($_tmp_pop as $_v){
						$_tmp_pop2 = explode(':',$_v);
						$_arr[trim(trim($_tmp_pop2[0]),"'")] = trim(trim($_tmp_pop2[1]),"'");
					}

					

					$attribute_popup .= ' mobile_css='.json_encode($_arr);
				}
				if( trim($_params_popup['restrict_dateRangeStart']) ){
					$_params_popup['restrict_dateRangeStart'] = preg_replace('/\s/',', ',$_params_popup['restrict_dateRangeStart']);
				}

				if( trim($_params_popup['restrict_dateRangeEnd']) ){
					$_params_popup['restrict_dateRangeEnd'] = preg_replace('/\s/',', ',$_params_popup['restrict_dateRangeEnd']);

				}
				unset($_arr);
				unset($_params_popup['popup_css']);
				unset($_params_popup['overlay_css']);
				unset($_params_popup['mobile_css']);
				$_params_json = json_encode($_params_popup);
				
				
				$attribute_popup .= " data-sm-init='true' ";
				$_html .= "<div class='{$v['class']}' {$v['attribute']} {$attribute_popup} pc_conf='{$_params_json}' id='{$v['id_html']}'>";
				
			
				$_html .= "<div class='pagecomposer-popup-content'>";
				break;
			case 'tab':
				$id_tab_content = "tab".$v['id'];
				$parameters = unserialize($v['parameters']);
				$class_tab = $parameters['class_tab_content'];
				$_html .= "<div class='tab-pane fade {$class_tab} {$v['class']}' {$v['attribute']} id='{$id_tab_content}'>";
				break;
			case 'row-with-icon-left':
				$parameters = unserialize($v['parameters']);
				
				$_html .= "<div class='{$v['class']}' {$v['attribute']} id='{$v['id_html']}'><div class='pagecomposer-riga-icona-col-left'><img src='{$parameters['image']}'></div>";
				break;
			default:
				$_html .= "<div class='{$v['class']}' {$v['attribute']} id='{$v['id_html']}'>";
				break;

		}
				
		
		
		
		if( okArray($v['children']) ){
			
			if( $v['type'] == 'tabs' ){
				if( !$vertical ){
					$_html .= $this->buildTabsHorizontal($v);
					
				}else{
					$_html .= $this->buildTabsVertical($v,$parameters);
					

				}
				/*$_tabs_start = '';
				$_tabs_end = '';
				if( $verticale ){
					$_tabs_start .= " <div class='col-xs-6'><ul class='nav nav-tabs nav-stacked'>";
				}else{
					$_tabs_start .= "<ul id='tab_widget_{$v[id]}' class='nav nav-tabs tabs-list'>";
				}
				$first = true;
				foreach($v['children'] as $k1=> $v1){
					$parameters = unserialize($v1['parameters']);
					$id_tab  = $parameters['id_tab'];
					$class_tab  = $parameters['class_tab'];
					if( $first ){
						$class_tab .= ' active';
						$v['children'][$k1]['class'] .= ' in active'; 
					}
					
					$href = "tab".$v1['id'];
					
					$title = $parameters['title'][$GLOBALS['activelocale']];
					$_tabs_start .= "<li class='{$class_tab}'><a href='#{$href}' data-toggle='tab' id='{$id_tab}'>{$title}</a></li>";
					$first = false;
				}
				if( $verticale ){
					$_tabs_start .="</ul></div>";
					if(  $menu_tabs == 'left' ){
						$_tabs_start .= "<div class='col-xs-6'><div class='tab-content'>";
					}
				}else{
					$_tabs_start .="</ul><div id='tab_widget_content_{$v[id]}' class='tab-content'>";
				}

				
			

				if( $verticale && $menu_tabs == 'right' ){
					$_html .= "<div class='col-xs-6'><div class='tab-content'>";
				}else{
					$_html .= $_tabs_start;
				}

				foreach($v['children'] as $k => $v1){
					$_html .= $this->buildRow($v1,$v);
				}
				if( $v['type'] == 'tabs' ){
					$_tabs_end .= "</div>";

					if( $verticale ){
						$_tabs_end .= "</div>";
					}
				}
				if( $verticale && $menu_tabs == 'right' ){
					$_html .= $_tabs_start;
				}

				$_html .= $_tabs_end;*/
			}else{
				foreach($v['children'] as $k => $v1){
					$_html .= $this->buildRow($v1,$v);
				}

			}
			

		}else{
			
			$_html .= $this->buildWidget($v,$parent);
			
			

		}

		if( $v['type'] == 'popup_container' ){
			$_html .= "</div>";
		}

		if( $v['type'] == 'accordion' ){
			$_html .= "</div>";
		}

		



		$_html .= "</div>";

		if( $v['cache'] ){
			$time_cache = $GLOBALS['setting']['default']['CACHE']['time'];
			$cache->set($key_cache,$_html,$time_cache);
		}

		return $_html;







	}

	function build($block){
		//$composer = self::getCurrent();

		
		//$list_tmp = $composer->composition_block[$block];
		$list = $this->buildTreeBlockView($block);
		
		
		//debugga($composer);exit;
		$database = _obj('Database');
		
		/*if( _var('preview_home_page') ){
			//self::registerJS('/js/cms/jquery.nestable.js');
			
			$id_page = _var('id_page');
			//$class_edit = ' edit-page-composer';
			$list_tmp = $database->select('*','composition_page_tmp',"visibility=1 AND id_adv_page={$id_page} AND block='{$block}' order by orderView ASC");
		}else{
			$list_tmp = $database->select('c.*','composition_page as c join homepage as h on h.id=c.id_adv_page',"h.active=1 AND visibility=1 AND block='{$block}' order by orderView ASC");
		}*/

		/*foreach($list_tmp as $v){
			$list[$v['id']] = $v;

		}
		
		foreach($list as  $k => $v){
			
			
			if( $v['parent'] ){
				$type_parent = $list[$v['parent']]['type'];
				$class = $this->_class_row[$type_parent];
				$v['class'] = $class[$v['position']-1];
				$v['container'] = $type_parent;
				$list[$v['parent']]['children'][$v['position']][$v['orderView']] = $v;
				unset($list[$v['id']]);
			}else{
				$list[$v['id']]['class'] = 'pagecomposer-riga'.$class_edit;
				if( _var('preview_page') ){
					//$list[$v['id']]['pre_html'] = "<button class='edit-page-composer-btn'><i class='fa fa-edit'></i></button>";
				}
			}


		}*/

	

		
		//debugga($list);
		
		//$cache = _obj('Cache');	
		$html = '';
		foreach($list as $v){
			
			$html .= $this->buildRow($v);
			//$this->echoSpace();
			
			/*if( $v['class_html'] ){
				echo "<div class='{$v['class']} {$v['class_html']}' id='{$v['id_html']}'>";
			}else{
				echo "<div class='{$v['class']}' id='{$v['id_html']}'>";
			}
			if( $v['pre_html'] ){
				echo $v['pre_html'];
			}

			if( $v['cache'] ){
				$key_cache = 'page_composer_block_'.$v['id'];
				
				$html = $cache->get($key_cache);
			}

			if( $v['cache']  && $html){
				
				echo $html;
			}else{
				if( $v['cache'] ){
					ob_start();
					if( okArray($v['children']) ){
						foreach($v['children'] as $v1){
							if( $v1['class_html'] ){
								echo "<div class='{$v1['class']} {$v1['class_html']}' id='{$v1['id_html']}'>";
							}else{
								echo "<div class='{$v1['class']}' id='{$v1['id_html']}'>";
							}
							
							if( $v1['type'] == 'page' ){
								$page = Page::withId($v1['id_page']);

								
								if( is_object($page) ){
									if( !$locale ) $locale = $GLOBALS['activelocale'];
									echo $page->get('content',$locale);
								}
								
							}
							if( $v1['type'] == 'module' ){
								$function = $v1['module_function'];
								if( function_exists($function) ){
									$function($v1);
								}
								
							}

							if( $v1['type'] == 'element' ){
								echo $v1['content'];
								
							}
							echo "</div>";
						}
						

					}
					$html = ob_get_contents();
					ob_end_clean();
					$time_cache = $GLOBALS['setting']['default']['CACHE']['time'];
					$cache->set($key_cache,$html,$time_cache);
					echo $html;
				}else{

					if( okArray($v['children']) ){
						foreach($v['children'] as $v1){
							if( $v1['class_html'] ){
								echo "<div class='{$v1['class']} {$v1['class_html']}' id='{$v1['id_html']}'>";
							}else{
								echo "<div class='{$v1['class']}' id='{$v1['id_html']}'>";
							}
							
							if( $v1['type'] == 'page' ){
								$page = Page::withId($v1['id_page']);

								
								if( is_object($page) ){
									if( !$locale ) $locale = $GLOBALS['activelocale'];
									echo $page->get('content',$locale);
								}
								
							}
							if( $v1['type'] == 'module' ){
								$function = $v1['module_function'];
								if( function_exists($function) ){
									$function($v1);
								}
								
							}

							if( $v1['type'] == 'element' ){
								echo $v1['content'];
								
							}
							echo "</div>";
						}
						

					}

				}
			}
			echo "</div>";
			//$this->echoSpace();
			

			

			

			*/
	
		}
		echo $html;
		//$this->loadJS2();
		

	}
	function getClassView(&$row){
		$class = '';
		switch($row['type']){
			case 'row-with-icon-left':
				$class= 'pagecomposer-riga-icona';
				break;
			case 'row-with-icon-left-col-right':
				$class= 'pagecomposer-riga-icona-col-right';
				break;
			case 'popup_container':
				$class = 'pagecomposer-popup';
				break;
			case 'col-33':
				
				$class= 'col-w-33';
				break;
			case 'col-50':
				
				$class= 'col-w-50';
				break;
			case 'col-100':
				
				$class= 'col-w-100';
				break;
			case 'col-25':
				
				$class= 'col-w-25';
				break;
			case 'col-75':
				
				$class= 'col-w-75';
				break;
			case 'row':
			case 'row-1':
			case 'row-2':
			case 'row-3':
			case 'row-4':
			case 'row-25-75':
			case 'row-75-25':
				$class="pagecomposer-riga";
				break;
			
			
		}
		if( $row['animate_css'] ){
			if( !isset($row['attribute']) ){ 
				$row['attribute'] = '';
			}
			$row['attribute'] .= "data-aos='{$row['animate_css']}'";
		}
		if( $row['class_html'] ) $class.= " ".$row['class_html'];
		$this->getStyle($row);
		$row['class'] = $class;
		//$row['no_edit'] = $no_edit;

		if( $row['detect_mobile_type'] == 'client' ){
			$row['class'] .= " ".$this->getClassesDevice($row);
		}
		
	}

	function getStyle(&$row){
		$style = array();
		
		if( trim($row['background_url']) || trim($row['background_url_webp']) ){


			if( _MARION_ENABLE_WEBP_ && file_exists("media/images/".$row['background_url_webp'])){
				$image = "media/images/".$row['background_url_webp'];
			}else{
				$image = "media/images/".$row['background_url'];
			}
			

			$style['background'] .= "url('{$image}')";
			if( $row['background_position'] ){
				$style['background-position'] .= "{$row['background_position']}";
			}
			if( $row['background_repeat'] ){
				$style['background-repeat'] .= "{$row['background_repeat']}";
			}
			if( $row['background_size'] ){
				$style['background-size'] .= "{$row['background_size']}";
			}
			if( $row['background_attachment'] ){
				$style['background-attachment'] .= "{$row['background_attachment']}";
			}
		}
		
		$_style ='';
		if( okArray($style) ){
			$_style = 'style="';
			foreach($style as $k => $v){
				$_style .= "{$k}:{$v}; ";
			}
			$_style = preg_replace('/\s$/','"',$_style);
			
			$row['attribute'] .= " ".$_style;
			
		}

		
	}

	function getClassEdit(&$row){
		$class = '';
		switch($row['type']){
			case 'row':
				if( !$row['active'] ){
					$class .= 'row-disabled';
				}
				$no_edit = true;
				break;
			case 'row-with-icon-left':
				$no_edit = true;
				$class= 'box-with-icon-left-edit';
				break;
			case 'row-with-icon-left-col-right':
				$no_edit = false;
				$class= 'box-with-icon-left-col-edit';
				$row['disable_delete'] = true;
				break;
			case 'col-33':
				$no_edit = true;
				$class= 'box-edit-33';
				break;
			case 'col-50':
				$no_edit = true;
				$class= 'box-edit-50';
				break;
			case 'col-100':
				$no_edit = true;
				$class= 'box-edit-100';
				break;
			case 'col-25':
				$no_edit = true;
				if( !$row['class_edit'] ){
					$class= 'box-edit-25';
				}
				break;
			case 'col-75':
				$no_edit = true;
				$class= 'box-edit-75';
				break;
			
			
		}
		if( $row['module'] ){
			$class =  'pc-widget-element';
		}

		if( $row['cache'] ) $class.= " cached";

		$row['class_edit'] .= " ".$class;
		$row['no_edit'] = $no_edit;
	}


	function getUrlEdit(&$row,$widgets){
		if( $row['module'] ){
			$key = $row['module'];
		}else{
			$key = $row['module']."-".$row['type']."-".$row['id_page'];
		}
		if( $row['type'] == 'page'){
			$row['url_edit'] = '/admin/content.php?action=mod_page_ajax&id='.$row['id_page'];
		}elseif( $row['type'] == 'accordion' ){
			$row['url_edit'] = 'index.php?action=edit-accordion&ctrl=PageComposerAdmin&mod=pagecomposer';
		}elseif( $row['type'] == 'tab' ){
			$row['url_edit'] = 'index.php?action=edit-tab&ctrl=PageComposerAdmin&mod=pagecomposer';
		}elseif( $row['type'] == 'tabs' ){
			$row['url_edit'] = 'index.php?action=edit-tabs&ctrl=PageComposerAdmin&mod=pagecomposer';
		}elseif( $row['type'] == 'popup_container' ){
			$row['url_edit'] = 'index.php?action=edit-popup&ctrl=PageComposerAdmin&mod=pagecomposer';
		}elseif( $row['type'] == 'row-with-icon-left' ){
			$row['url_edit'] = 'index.php?action=edit-row-icon-left&ctrl=PageComposerAdmin&mod=pagecomposer';
		}elseif( $row['type'] == 'accordion_container' ){
			$row['url_edit'] =  'index.php?action=edit-accordions&ctrl=PageComposerAdmin&mod=pagecomposer';
		}elseif( $row['type'] == 'row' ){
			$row['url_edit'] = 'index.php?action=edit-row&ctrl=PageComposerAdmin&mod=pagecomposer';
		}else{
			$row['url_edit'] = $widgets[$key]['url_edit'];

		}

		$function = $row['module_function'];
		
		if( class_exists($function) ){
			$object = new $function();
			
			$row['img_logo'] = $object->getLogo($row);
			

		}
	}

	function recurviveOrder(&$row){
		
		uasort($row['children'],function($a,$b){
			 if ($a['orderView'] == $b['orderView']) {
				return 0;
			}
			return ($a['orderView'] < $b['orderView']) ? -1 : 1;
		});
		foreach($row['children'] as $k => $v){
			$this->recurviveOrder($row['children'][$k]);
		}
		
	}


	function getDataWidget(&$row){
		if( $row['type'] == 'module' ){
			
			$function = $row['module_function'];
				
			if( class_exists($function) ){
				$object = new $function();
				$row['disable_edit'] = !$object->isEditable();
				$row['disable_copy'] = !$object->isCopyable();
				$row['disable_css'] = !$object->customCSS();
			}

		}else{

			if( preg_match('/col-/',$row['type']) ){
				$row['disable_edit'] = true;
				$row['disable_copy'] = true;
				$row['sortable'] = true;
			}

			if( $row['type']== 'accordion_container'){
				$row['sortable'] = true;
				$row['disable_copy'] = true;
			}

			if( preg_match('/row/',$row['type']) ){
				$row['disable_copy'] = true;
			}

			if( $row['type']== 'tabs'){
				//$row['sortable'] = true;
				$row['disable_copy'] = true;
			}

			/*if( $row['type'] == 'space'){
				$row['disable_edit'] = true;
				$row['img_logo'] = '/img/composer/space.png';
			}*/
			
		}
		$this->getClassEdit($row);
		
	}

	function buildTreeEdit($widgets,$src_arr, $parent_id = 0,$parent_type='', $tree = array()){
		
		if( okArray($src_arr) ){
			foreach($src_arr as $idx => $row)
			{

				
				$this->getDataWidget($row);
				
				$this->getUrlEdit($row,$widgets);
				$row['parent_type'] = $parent_type;
				if($row['parent'] == $parent_id)
				{
					$tree[$row['id']] = $row;
					unset($src_arr[$idx]);
					$tree[$row['id']]['children']= $this->buildTreeEdit($widgets,$src_arr, $row['id'],$row['type']);
				}
			}
		}
		/*foreach($tree as $k =>$row){
			$this->recurviveOrder($tree[$k]);
		}*/
		return $tree;
		
	}

	function buildTreeBlockEdit($block){
		$database = _obj('Database');
		$list = $database->select('*','composition_page_tmp',"id_adv_page={$this->id_page} AND block='{$block}' order by orderView ASC");
		$widgets = $this->getWidgets();
		return $this->buildTreeEdit($widgets,$list);

	}

	function buildTreeView($src_arr, $parent_id = 0, $tree = array()){
		
		if( okArray($src_arr) ){
			
			foreach($src_arr as $idx => $row)
			{
				$this->getClassView($row);
				
				if($row['parent'] == $parent_id)
				{
					$tree[$row['id']] = $row;
					unset($src_arr[$idx]);
					$tree[$row['id']]['children']= $this->buildTreeView($src_arr, $row['id']);
				}
			}
		}
		
		//ksort($tree);
		return $tree;
		
	}

	function buildTreeBlockView($block,$anteprima=false){

		$list = $this->composition_block[$block];
		
		$return = $this->buildTreeView($list);
		
		return $return;
	}

	function buildTree($src_arr, $parent_id = 0, $tree = array()){
		
		foreach($src_arr as $idx => $row)
		{
			
			
			if($row['parent'] == $parent_id)
			{
				$tree[$row['id']] = $row;
				unset($src_arr[$idx]);
				$tree[$row['id']]['children']= $this->buildTree($src_arr, $row['id']);
			}
		}
		
		//ksort($tree);
		return $tree;
		
	}



	function getWidgets(){

		$database = _obj('Database');
		$module_widgets = $database->select('*','widget');
	
		if( okArray($module_widgets) ){
			foreach($module_widgets as $v){
				$_widgets[] = array(
					'title' => $v['name'],
					'type' => 'module',
					'module' => $v['module'],
					'repeat' => $v['repeatable'],
					'id' => 0,
					'url_edit' => $v['url_conf']
				);

			}
		}
		
		
		$query = Page::prepareQuery();
		
		
		$query->where('theme',Marion::getConfig('SETTING_THEMES','theme'))->where('widget',1);
		

		$page = $query->get();
		if( okArray($page) ){
			foreach($page as $p){
				$_widgets[] = array(
					'title' => $p->get('url'),
					'type' => 'page',
					'module' => '',
					'id' => $p->id,
					'repeat' => 0,
				);

			}
		}

		foreach($_widgets as $v){
			if( $v['module'] ){
				$widgets[$v['module']] = $v;
			}else{
				$widgets[$v['module']."-".$v['type']."-".$v['id']] = $v;
			}
			
		}

		return $widgets;

	}



	public static function removeNode($id){
		$database = _obj('Database');
		$database->delete('composition_page_tmp',"id={$id}");
		$list = $database->select('composition_page_tmp',"parent={$id}");
		foreach($list as $v){
			self::removeNode($v['id']);
		}
		
	}

	public static function copyNode($id_from,$id_to){
		$database = _obj('Database');
		
		$rows = $database->select('*','composition_page_tmp',"id={$id_from} order by orderView");
		if( okArray($rows) ){
			foreach($rows as $row){
				$max = $database->select('max(orderView) as max','composition_page_tmp',"parent={$id_to}");
				unset($row['id']);
				$row['parent'] = $id_to;
				$row['orderView'] = $max[0]['max']+1;
				
				$id = $database->insert('composition_page_tmp',$row);
				$rows2 = $database->select('*','composition_page_tmp',"parent={$id_from} order by orderView");
				if( okArray($rows2) ){
					foreach($rows2 as $row2){
						self::copyNode($row2['id'],$id);
					}
				}
			}
		}

		
	}


}




?>