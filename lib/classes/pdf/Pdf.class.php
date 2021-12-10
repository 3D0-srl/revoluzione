<?php
require_once("dompdf/dompdf_config.inc.php");

class PDF extends DOMPDF{
	/*function __construct(){
		parent::__construct();
	}*/
	
	public function setTemplate($template){
		$this->template = $template;
	}
	

	public function build(){
		$template = _obj('Template');
		
		foreach($this as $k => $v){
			$template->$k = $v;
		}
		ob_start();
		$template->output($this->template);
		$data = ob_get_contents();
		ob_end_clean();
		
		$this->load($data);
	}

	public function load($html){
		
		$this->load_html($html);
		
	}

	public function show($name=NULL){
		
		$this->render();
		if(empty($name)) $name = 'output.pdf';
		$this->stream($name,array("Attachment" => false));
	}

	public function save($path){
		
		$this->render();
		
		$output = $this->output();
		
		file_put_contents($path, $output); 
		
		return true;
	}

}


?>
