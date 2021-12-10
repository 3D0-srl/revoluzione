<?php

use Marion\Core\Marion;
class FooterAdminController extends \Marion\Controllers\AdminController{
	public $_auth = 'cms';

	
	function createDinamicPage(){
		$database = Marion::getDB();
		$toinsert = array(
			'id_layout' => 3,
		);
		$id =$database->insert('page_advanced',$toinsert);
		return $id;
	}

	function displayForm(){
		$this->setMenu('edit_footer');
		

		createIDform();
		$id =  $this->getID();
		$action =  $this->getAction();
		
		
		if( $this->isSubmitted() ){

			$dati = $this->getFormdata();
			
			
			$array = $this->checkDataform('footer',$dati);
			//$array = check_form2($formdata,'footer');
			
			if( $array[0] == 'ok' ){
				unset($array[0]);
				$database = Marion::getDB();
				if(	$action == 'add'){
					$array['id_page'] = $this->createDinamicPage();
					$database->insert('footer',$array);
				}else{
					$database->update('footer',"id={$array['id']}",$array);
				}
				
				
				
				
				$this->redirectToList(array('saved'=>1));
				
				//$dati = $array;
				
			}else{
				//$dati = $array;
				$this->errors[] = $array[1];
				
				
			}
			

			
		}else{
			
			$dati = NULL;
			if( $action != 'add'){
				$database = Marion::getDB();
				$dati =  $database->select('*','footer',"id={$id}");
				$dati = $dati[0];
				if( $action == 'duplicate'){
					unset($dati['id']);
					$action = 'add';
				}
			}

		}
		$dataform = $this->getDataForm('footer',$dati);
		$this->setVar('dataform',$dataform);
		//get_form2($elements,'footer',$action,$dati);	
		$this->output('footer/form.htm');	

		

	}

	function displayList(){
			$this->setMenu('edit_footer');

			if( _var('saved') ){
				$this->displayMessage(_translate('footer_saved'));
			}
			if( _var('deleted') ){
				$this->displayMessage(_translate('footer_deleted'),'success');
			}
			
			

			$database = Marion::getDB();
			$list =  $database->select('*','footer');
			
			$this->setVar('list',$list);
			//$this->setVar('links',$pager_links);
			
			$this->output('footer/list.htm');
	}


	function delete(){
		$id = $this->getID();
	
		$database = Marion::getDB();
		$database->delete('footer',"id={$id}");
		$this->redirectToList(array('deleted'=>1));
		

		
	}



	function ajax(){
		$id = $this->getID();
		$action = $this->getAction();
		switch($action){
			case 'active':
				$database = Marion::getDB();
				$database->update('footer',"1=1",array('active'=>0));
				$database->update('footer',"id={$id}",array('active'=>1));
				
				$risposta = array(
					'result' => 'ok',
				);

				break;
		}

		echo json_encode($risposta);
	}

}

?>