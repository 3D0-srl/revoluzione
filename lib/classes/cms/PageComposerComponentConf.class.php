<?php
class PageComposerComponentConf{
	public $_action;
	public $_formdata;
	public $_template_page;
	public $_id_box;
	public $_form_control;
	public $_module_dir;
	public $_error;
	public $_template;

	function __construct($options=array()){

		if( $options['form_control'] ){
			$this->_form_control = $options['form_control'];
		}
		if( $options['module'] ){
			$this->_module_dir = $options['module'];
		}
		if( $options['template_html'] ){
			$this->_template_page = $options['template_html'];
		}
		
		
		if( $options['controller'] ){
			$this->_ctrl = $options['controller'];
		}
		$this->init();

		
		
	}

	function isAjaxRequest(){
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		}else{
			return false;	
		}
	}
	
	

	function isSubmit(){
		return okArray($this->_formdata);
	}

	function init(){
		$this->_action = _var('action');
		$this->_id_box = _var('id_box');
		$this->_formdata = _var('formdata');
		$this->_template = $this->getTemplateObj();

		
	}

	function getTemplateObj(){
		if( defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_ ){
			$options = array(
				'templateDir'   => "templates". PATH_SEPARATOR ."../modules/".$this->_module_dir."/templates/admin",
				'compileDir'    => ".."._MARION_TMP_DIR_,
				'multiSource'	=> true,
				'globalfunctions' => true,
				'textdomain'    => 'messages', 
				'globals'       => false,
				'allowPHP'      => false, 
				'locale'        => $GLOBALS['activelocale'],
				'debug'         => false
			);
			
			return  _obj('Template',$options);
		}else{
			return _obj('Template');
		}
	}



	function checkForm(){
		

		$array = check_form2($this->_formdata,$this->_form_control);
		
		if( $array[0] == 'nak'){
			$this->_error = $array[1];
		}
		foreach($array as $k => $v){
			if( $k != '_locale_data'){
				$dati[$k] = $v;
			}
		}
		foreach($array['_locale_data'] as $k =>$v){
			foreach($v as $k1 => $v1){
				$dati[$k1][$k] = $v1;
			}
		}
		return $dati;
	}

	function getData(){
		if( $this->_id_box ){
			$database = _obj('Database');
			$data = $database->select('*','composition_page_tmp',"id={$this->_id_box}");
			
			if( okArray($data) ){
				$dati = unserialize($data[0]['parameters']);
			}

			$dati['id_box'] = $this->_id_box;
		}
		return $dati;
	}
	

	function saveData($dati){
		$database = _obj('Database');
		$data = serialize($dati);
		$database->update('composition_page_tmp',"id={$dati['id_box']}",array('parameters'=>$data));
		


	}


	function set($key,$val){
		$this->_template->$key = $val;
	}

	

	function render(){
		
		if( $this->isSubmit()){
			$dati = $this->checkForm();
			if( is_object($this->_ctrl)){
				if( $this->_error ){
					$this->_ctrl->setVar('errore',$this->_error);
					
				}else{
					$this->saveData($dati);
					$this->_ctrl->setVar('ok_success',"Dati salvati con successo");
					
				}
			}else{
				if( $this->_error ){
					$this->set('errore',$this->_error);
					
				}else{
					$this->saveData($dati);
					$this->set('ok_success',"Dati salvati con successo");
					
				}
			}
		}else{
			$dati = $this->getData();
		}
		

		

		
		
		get_form2($elements,$this->_form_control,$this->_action,$dati);
		
		$this->set('locales',Marion::getConfig('locale','supportati'));
		if( $this->isAjaxRequest()){
			ob_start();
			if( is_object($this->_ctrl)){
				
				$this->_ctrl->output($this->_template_page,$elements);
			}else{
				$this->_template->output_module($this->_module_dir,$this->_template_page,$elements);
			}
			$html = ob_get_contents();
			ob_end_clean();
			$risposta = array(
				'result'=>'ok',
				'html' => $html
			);
			echo json_encode($risposta);
			exit;

		}else{
			if( is_object($this->_ctrl)){
				
				$this->_ctrl->output($this->_template_page,$elements);
			}else{
				$this->_template->output_module($this->_module_dir,$this->_template_page,$elements);
			}
		}
		
	}



}

?>