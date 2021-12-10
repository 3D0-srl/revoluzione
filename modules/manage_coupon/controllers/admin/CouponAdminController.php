<?php
class CouponAdminController extends AdminModuleController{
	public $_auth = 'ecommerce';
	public $_twig = true;

	function displayContent(){
		

		
	}

	function setMedia(){
		if( $this->getAction() != 'list'){
			
			$this->registerJS($this->getBaseUrl().'modules/manage_coupon/js/conf.js','end');
		}
	}

	function displayList(){
		$this->setMenu('coupon_manage');

		$this->showMessage();
		$list = Coupon::prepareQuery()->orderBy('orderView')->get();


		$categories = UserCategory::prepareQuery()->get();
		foreach($categories as $v){
			$category_name[$v->id] = $v->get('name');
		}

		foreach($list as $k => $v){
				
				if( $v->use_limit == 'specific_users' ){
					
					$users = explode(';',$v->users);
					if( okArray($users) ){
						$v->limit = "<b>Utenti:</b><br>";
						foreach($users as $v2){
							$v->limit.="{$v2},<br>";
						}
						$v->limit = preg_replace('/\,<br>$/','',$v->limit);
					}
				}elseif( $v->use_limit == 'category_users' ){
					
					if( okArray($v->user_category) ){
						$v->limit = "<b>Categorie utente:</b><br>";
						foreach($v->user_category as $v2){
							$v->limit.=$category_name[$v2].",<br>";
						}
						$v->limit = preg_replace('/\,<br>$/','',$v->limit);
						
					}
				}

				$v->discount_type = $this->check_discount_type($v->discount_type);
		}

		$this->setVar('list',$list);
		$this->output('list.htm');
	}



	function displayForm(){
		$this->setMenu('coupon_manage');
		

		createIDform();
		$id =  $this->getID();
		$action =  $this->getAction();
		
		
		if( $this->isSubmitted() ){

			$dati = $this->getFormdata();
			
		
			$array = $this->checkDataForm('manage_coupon_data',$dati);
			if( $array[0] == 'ok' ){
				if( !$array['name'] ){
					$array['name']= $this->createName();
				}
				if(	$action == 'add'){
					$obj = Coupon::create();
				}else{
					$obj = Coupon::withId($array['id']);
				}
				$obj->set($array);
				$res = $obj->save();
				
				
				if(is_object($res)){
					$this->redirectToList(array('saved'=>1));
				}else{
					$this->errors[] = $res;
				}
				
				
			}else{
				
				$this->errors[] = $array[1];
				
				
			}
			

			
		}else{
			
			$dati = NULL;
			if( $action != 'add'){
				$utente = Coupon::withId($id);
				if(is_object($utente) ){
					$dati = $utente->prepareForm2();
					
				}
				if( $action == 'duplicate'){
					unset($dati['id']);
					$action = 'add';
				}
			}

		}

		$dataform = $this->getDataForm('manage_coupon_data',$dati);
		$this->setVar('dataform',$dataform);
		
		$this->output('form.htm');	

		

	}


	function delete(){
		$id = $this->getID();

		$obj = Coupon::withId($id);
		
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));
		

		
	}

	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Coupon salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Coupon eliminato con successo','success');
		}
	}


	function check_discount_type($type) {

		if($type=="fixed")
			return "€";
		elseif($type=="percentage")
			return "%";
		else
			return "";
	}

	function createName() {

		$toreturn = substr ( md5(uniqid(rand(), true)), 0, 8);

		return $toreturn;

	}






	//FORM
	function manage_coupon_array_usercategory() {
		$categorie = UserCategory::prepareQuery()->get();
		
		foreach($categorie as $v){
			$toreturn[$v->id] = ucwords($v->get('name'));
	
		}
	
		return $toreturn;
	
	}


}



?>