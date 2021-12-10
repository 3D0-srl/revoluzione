<?php
use Marion\Entities\Cms\LinkMenuFrontend;
class LinkMenuFrontendAdminController extends \Marion\Controllers\AdminController{
	public $_auth = 'cms';
	public $url_type;


	


	function displayForm(){
		$this->setMenu('link_menu');
		
		$action = $this->getAction();

		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			$this->url_type = $dati['url_type'];
			if( !$dati['static_url'] ){
				$campi_aggiuntivi['url_type']['obbligatorio'] = 0;
			}
			$array = $this->checkDataForm('linkMenuFrontend',$dati,$campi_aggiuntivi);
			
			if( $array[0] == 'ok'){

				if( $action == 'add'){
				$obj = LinkMenuFrontend::create();
				}else{
					$obj = LinkMenuFrontend::withId($array['id']);
				}
				$obj->set($array);
				
				
				

				$res = $obj->save();
				if( is_object($res) ){
					$this->saved();
				}else{
					$this->errors[] = $res;
				}


			}else{
				$this->errors[] = $array[1];
			}

			
			

		}else{
		
			createIDform();
		
			$id = $this->getID();
			
			if($action != 'add'){
				$obj = LinkMenuFrontend::withId($id);
				
				$dati =  $obj->prepareForm2();
				

				if($action == 'duplicate'){
					unset($dati['id']);
					unset($dati['images']);
					$action = "add";
				}
			}else{
				$dati = NULL;
			}
		}
		$this->url_type = $dati['url_type'];

		$dataform = $this->getDataForm('linkMenuFrontend',$dati);
			
		$this->setVar('dataform',$dataform);
		$this->output('link_menu/form.htm');
		

	}

	function setMedia(){
		$this->registerJS('js/function.js','head');
	}


	function displayList(){
		$this->setMenu('link_menu');
		$this->showMessage();
		
		$tree = LinkMenuFrontend::getTree(1);
		$this->setVar('items',$tree);
		$this->output('link_menu/list.htm');
			
	}

	
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Link salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Link eliminato con successo','success');
		}
	}

	function saved(){
		$this->redirectTolist(array('saved'=>1));
	}


	function delete(){
		$id = $this->getID();

		$obj = LinkMenuFrontend::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));

	}




	
	

	function ajax(){
		$action = $this->getAction();

		switch($action){
			case 'get_link_dinamic_menu':
				$type = _var('type');
	
				$links = LinkMenuFrontend::listPages($type);
				
				$toreturn = array(__('seleziona'));
				foreach($links as $k => $v){
					$toreturn[$k] = $v;
				}

				$risposta = array(
					'result' => 'ok',
					'options' => $toreturn
				);

				break;
		}

		
		echo json_encode($risposta);
		exit;


	}



	function array_url_page(){
		$pages = LinkMenuFrontend::listPages($this->url_type);
		
		$toreturn[0] = __('seleziona');
		
		
		if( okArray($pages) ){
			foreach($pages as $k => $v){
				$toreturn[$k] = $v;
			}
		}
		return $toreturn;

	}


	public function getAll($locale='it'){
		
		
		$links = LinkMenuFrontend::prepareQuery()->get();
		$tree = LinkMenuFrontend::buildTree($links);
		
		
		foreach($tree as $level1){
			$toreturn[$level1->id] = $level1->get('title');
			if( okArray($level1->children ) ){
				foreach($level1->children as $level2){
					$toreturn[$level2->id] = $level1->get('title')." / ".$level2->get('title');
					if( okArray($level2->children ) ){
						foreach($level2->children as $level3){
							$toreturn[$level3->id] = $level1->get('title')." / ".$level2->get('title')." / ".$level3->get('title');
							if( okArray($level3->children ) ){
								foreach($level3->children as $level4){
									$toreturn[$level4->id] = $level1->get('title')." / ".$level2->get('title')." / ".$level3->get('title')." / ".$level4->get('title');
									if( okArray($level4->children ) ){
										foreach($level4->children as $level5){
											$toreturn[$level5->id] = $level1->get('title')." / ".$level2->get('title')." / ".$level3->get('title')." / ".$level4->get('title')." / ".$level5->get('title');
												
											if( okArray($level5->children ) ){
												foreach($level5->children as $level6){
													$toreturn[$level6->id] = $level1->get('title')." / ".$level2->get('title')." / ".$level3->get('title')." / ".$level4->get('title')." / ".$level5->get('title')." / ".$level6->get('title');
												}
											}
										}
									}
								}
							}
						}
					}
				}

			}
		}
		uasort($toreturn,function($a,$b){
			 if ($a == $b) {
				return 0;
			}
			return ($a < $b) ? -1 : 1;
		});
			
		return $toreturn;
		
	}



	function array_link_menu_frontend(){
		$toreturn[0] = 'Nessuno';
		
		$list = $this->getAll();
		
		$toreturn[0] = 'Nessuno';
		if( okArray($list) ){
			foreach($list as $k => $v){
				$toreturn[$k] = $v;
			}
		}
		return $toreturn;
	}


	function url_types(){
		
		return LinkMenuFrontend::listGroupPages();
	
	}

}



?>