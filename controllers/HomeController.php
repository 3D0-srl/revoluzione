<?php
use Marion\Core\Marion;
use Marion\Entities\Country;
use Marion\Entities\Cms\HomeButton;
class HomeController extends \Marion\Controllers\BackendController{
	
	function personalData(){
		$this->setMenu('personal_data');
		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			
			if( !$dati['reset_password'] ){
				$campi_aggiuntivi['password']['obbligatorio'] = 0;
				unset($dati['password']);
			}
			
			$array = $this->checkDataForm('user',$dati,$campi_aggiuntivi);

			
			if($array[0] == 'ok'){

				if( $array['password'] ){
					$_tmp_password = $array['password'];
					$array['password'] = password_hash($array['password'], PASSWORD_DEFAULT); 
				}
				$user = Marion::getUser();
				$array['active'] = 1;
				$array['deleted'] = 0;

				$user->set($array);
				
				$res = $user->save();
				if(is_object($res)){
					Marion::setUser($res);
					$this->displayMessage('Dati salvati con successo!');

				}else{
					$this->errors[] = $GLOBALS['gettext']->strings[$res];
				}

			}else{
				$this->errors[] = $array[1];
			}


		}else{
			$user = Marion::getUser();
			$dati = $user->prepareform2();
			unset($dati['password']);
		}
		$dataform = $this->getDataForm('user',$dati);
		$this->setVar('dataform',$dataform);
		$this->output('home/personal_data.htm');
	}


	function display(){
		if( !authUser() ) $this->notAuth();
		switch($this->getAction()){
			case 'personal_data':

				$this->personalData();
				break;
			default:
				$buttons = HomeButton::prepareQuery()->where('active',1)->orderBy('orderView','ASC')->get();
				$this->setVar('buttons',$buttons);
				$this->output('home/home.htm');
				break;
		}

		
		
	}


	//FUNZIONI FORM
	function array_province(){
		$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$database = Marion::getDB();;
		$prov = $database->select('sigla','provincia','','sigla ASC');
		if( okArray($prov) ){			
			foreach($prov as $v){
				$toreturn[$v['sigla']] = $v['sigla'];
			}
		}
		
		return $toreturn;
	}
	function array_type_buyer(){
		$labels = array('private','company');
		foreach($labels as $label){
			$toreturn[$label] = __("type_buyer_".$label);
		}
		return $toreturn;
	}
	function array_nazioni(){
		//$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$nazioni = Country::getAll();
		foreach($nazioni as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		return $toreturn;
	}



	
}


?>