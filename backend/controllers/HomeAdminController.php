<?php
use Marion\Core\Marion;
use Marion\Entities\Cms\Page;
class HomeAdminController extends \Marion\Controllers\AdminController{
	public $_auth = 'cms';
	
	


	function displayForm(){
		$this->setMenu('edit_home');
		

		createIDform();
		$id =  $this->getID();
		$action =  $this->getAction();
		
		
		if( $this->isSubmitted() ){

			$dati = $this->getFormdata();
			
			if($dati['timer']){
				$_checkdata['endDate']['obbligatorio'] = true;
				$_checkdata['startDate']['obbligatorio'] = true;
			}
			$array = $this->checkDataForm('homepage',$dati,$_checkdata);
			
			if($array[0] == 'ok'){
				if($array['timer']){
					if(strtotime($array['endDate']) < strtotime($array['startDate']) ){
						$array[0] = 'nak';
						$array[1] = "La data di fine deve essere succesiva a quella di inizio";
					}else{
						if( strtotime($array['endDate']) < strtotime( date('Y-m-d') ) ){
							$array[0] = 'nak';
							$array[1] = "La data di fine deve essere succesiva alla data di oggi";
						}
					}

				}
			}
			
			if( $array[0] == 'ok' ){
				unset($array[0]);
				$database = Marion::getDB();
				if(	$action == 'add'){
					$database->insert('homepage',$array);
				}else{
					$database->update('homepage',"id={$array['id']}",$array);
				}
				
				
				
				
				$this->redirectToList(array('saved'=>1));
				
				

				
			}else{
				$dati = $array;

				$this->errors[] = $array[1];
				
				
			}
			

			
		}else{
			
			$dati = NULL;
			if( $action != 'add'){
				$database = Marion::getDB();
				$dati =  $database->select('*','homepage',"id={$id}");
				$dati = $dati[0];
				if( $action == 'duplicate'){
					unset($dati['id']);
					$action = 'add';
				}
			}

		}
		$dataform = $this->getDataForm('homepage',$dati);
			
		$this->setVar('dataform',$dataform);
		$this->output('homepage/form.htm');	

		

	}

	function displayList(){
			$this->setMenu('edit_home');

			if( _var('saved') ){
				$this->displayMessage(_translate('homepage_saved'));
			}
			if( _var('deleted') ){
				$this->displayMessage(_translate('homepage_deleted'),'success');
			}
			
			

			$database = Marion::getDB();
			$list =  $database->select('*','homepage');
			
			$this->setVar('list',$list);
			//$this->setVar('links',$pager_links);
			
			$this->output('homepage/list.htm');
	}


	function delete(){
		$id = $this->getID();
	
		$database = Marion::getDB();
		$database->delete('homepage',"id={$id}");
		$this->redirectToList(array('deleted'=>1));
		

		
	}



	function ajax(){
		$id = $this->getID();
		$action = $this->getAction();
		switch($action){
			case 'active':
				$database = Marion::getDB();
				$database->update('homepage',"1=1",array('active'=>0));
				$database->update('homepage',"id={$id}",array('active'=>1,'timer'=>0));
				
				$risposta = array(
					'result' => 'ok',
				);

				break;
		}

		echo json_encode($risposta);
	}
	

	function array_dinamic_page(){
		$pages = Page::prepareQuery()->get();
		foreach( $pages as $v ){
			$toreturn[$v->id_adv_page] = $v->get('title');
		}

		return $toreturn;
	}
}



?>