<?php
class FormTabsContainer{
	

	private $dataform;
	private $tabs;
	private $html;
	private $fields;

	function __construct(){
		
    }
    
    function setDataForm($dataform){
        $this->dataform = $dataform;
        return $this;
    }
	



	
	
	

	function setFields($array){
		foreach($array as $k => $v){
			$this->fields[$k]=$v;
		}
	}
	
	
	function addTab(FormContainer $tab){
		
		$this->tabs[] = $tab;
		return $this;
	}


	function build(){
		$this->html = "{% import 'macro/form.htm' as form %}";
		$this->html .= '<ul id="myTab" class="nav nav-tabs">';
		foreach($this->tabs as $k =>$v){
			if(	$k == 0 ){
				$this->html .= '<li class="active"><a href="#tab'.$k.'" data-toggle="tab">'.$v->getName().'</a></li>';
			}else{
				$this->html .= '<li class=""><a href="#tab'.$k.'" data-toggle="tab">'.$v->getName().'</a></li>';
			}

		}
		$this->html .= "</ul>";
		$this->html .= '<div id="myTabContent" class="tab-content">';
				

		foreach($this->tabs as $k => $v){
			if(	$k == 0 ){
				$this->html .= '<div class="tab-pane fade active in" id="tab'.$k.'">';
			}else{
				$this->html .= '<div class="tab-pane fade in" id="tab'.$k.'">';
			}
			$this->html.="<div class='row'>";
			
			$v->build();
			$this->html .= $v->getHtml();
			//$this->html .= $v->getHtml(); 
			$this->html .= '</div></div>';
			
		
		}
		//debugga($this->html);exit;
		$this->html .= '</div>';

		//debugga($this->html);exit;
		/*foreach($this->dataform as $k => $v){
			$this->html .= '{{form.buildCol(dataform.'.$k.',"col-md-12")}}';
			
		}*/
	}

	function getHtml(){
		return $this->html;
	}

	



	



}


class FormContainer{
	
	private $name;
	private $name_data_form='dataform';
	private $fields;
	private $html = '';
	public $template;

	function setName($name){
		$this->name = $name;
		return $this;
	}

	function setNameDataForm($name){
		$this->name_data_form = $name;
		return $this;
	}

	function getName(){
		return $this->name;
		
	}


	function setTemplate(string $template){
		$this->template = $template;
		return $this;
	}

	function setFields(array $fields){
		$this->fields = $fields;
		return $this;
	}

	function getFields(){
		return $this->fields;
	}


	function build(){
		$this->html = '';
		if( $this->template ){
			$this->html .= "{% include '{$this->template}' %}";
		}else{
			
			if( okArray($fields = $this->getFields())){
				$this->html .= '{% import "macro/form.htm" as form %}';
				foreach($fields as $v1){
					if( $v1['custom']){
						$this->html .= $v1['custom'];
					}else{
						$this->html .= "{{form.buildCol({$this->name_data_form}.{$v1['name']},'{$v1['class']}')}}";
					}
					
				}
			}
		}
	}

	function getHtml(){
		return $this->html;
	}

	
	
}

?>