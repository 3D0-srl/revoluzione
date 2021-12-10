<?php
class FormHelper{
	

	private $layout = 'linear';
	private $dataform;
	private $tabs;
	private $html;
	private $fields;

	function __construct($dataform=array()){
		$this->dataform = $dataform;
	}
	


	function setLayout($layout){
		$this->layout = $layout;
	}

	function build(){
		switch($this->layout){
			
			case 'tabs':

				$this->buildTabs();
				break;
			default:

				$this->buildLinear();
				break;
			
		}

		return $this->html;
	}
	
	

	function setFields($array){
		foreach($array as $k => $v){
			$this->fields[$k]=$v;
		}
	}
	
	
	function addTab(FormHelperTab $tab){
		
		$this->tabs[] = $tab;
	}

	function buildLinear(){
		

		$this->html = "{% import 'macro/form.htm' as form %}";
		foreach($this->fields as $k => $v){
			$this->html .= '{{form.buildCol(dataform.'.$k.',"'.$v.'")}}';
			
		}
	}


	function buildTabs(){
		$this->html = "{% import 'macro/form.htm' as form %}";
		$this->html .= '<ul id="myTab" class="nav nav-tabs">';
		foreach($this->tabs as $k =>$v){
			if(	$k == 0 ){
				$this->html .= '<li class="active"><a href="#tab'.$k.'" data-toggle="tab">'.$v->name.'</a></li>';
			}else{
				$this->html .= '<li class=""><a href="#tab'.$k.'" data-toggle="tab">'.$v->name.'</a></li>';
			}

		}
		$this->html .= "</ul>";
		$this->html .= '<div id="myTabContent" class="tab-content">';
				

		foreach($this->tabs as $k => $v){
			if(	$k == 0 ){
				$this->html .= '<div class="tab-pane fade active in" id="tab'.$k.'"><div class="row"><div class="col-md-12">';
			}else{
				$this->html .= '<div class="tab-pane fade in" id="tab'.$k.'"><div class="row"><div class="col-md-12">';
			}
			foreach($v->fields as  $k1 => $v1){
				$this->html .= '{{form.buildCol(dataform.'.$k1.',"'.$v1.'")}}';
			}
			$this->html .= '</div></div></div>';
			
		
		}
		$this->html .= '</div>';

		
		/*foreach($this->dataform as $k => $v){
			$this->html .= '{{form.buildCol(dataform.'.$k.',"col-md-12")}}';
			
		}*/
	}



	



}


class FormHelperTab{
	
	public $name;
	public $fields;



	public function getContent(){

	}
}


?>