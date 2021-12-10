<?php
use Marion\Entities\Profile;
use Marion\Entities\User;
use Marion\Entities\Permission;
use Marion\Core\Marion;

class ProfileAdminController extends \Marion\Controllers\AdminController{
	public $_auth = 'user_management';
	
	
	


	function displayContent(){
		$this->setMenu('profiles');
		$action = $this->getAction();
		switch($action){
			case 'add_user':
			case 'users':
				$this->displayUsers();
				break;
			case 'add_to_profile':
				$id_profile = _var('id_profile');
				$id_user = _var('id_user');
				$user = User::withId($id_user);
				
				if( is_object($user) ){
					$user->id_profile = $id_profile;
					
					$user->save();
				}
				header('Location: '._var('url_back'));
				exit;
			/*case 'add_user':
				$this->displayFormUsers();
				break;*/
		}

	}

	function displayFormUsers(){
		$id = $this->getID();
		$obj = Profile::withId($id);
		$this->setVar('profile',$obj);

		
	
		$query = User::prepareQuery();
		
	

		$limit = $this->getLimitList();
		
		$offset = $this->getOffsetList();
		
		$query2 = clone $query;

		if( $limit ){
			$query->limit($limit);
		}
		if( $offset ){
			$query->offset($offset);
		}
		$query->whereExpression('(id_profile is NULL or id_profile = 0)');

		$search = _var('search');

		if( $search ){
			
			$query->whereExpression("(name LIKE '%{$search}%' OR surname LIKE '%{$search}%' OR username LIKE '%{$search}%')");
			$this->setVar('search',$search);
		}
			
		$list = $query->get();
		$tot = $query2->getCount();

		//$pager_links = $this->getPagerList($tot);

		
		
		
		$this->setVar('url_confirm_delete_user',$this->getUrlConfirmDeleteUser());
		
		//$this->setVar('links',$pager_links);
		
		$this->setVar('list',$list);
		$this->output('profile/list_profile_users_add.htm');
	}


	function getListUsers(){
		$id = _var('id_profile');
		$action = $this->getAction();
		if( $action == 'users'){
			$condizione = "id_profile = {$id} AND ";
		}else{
			$condizione = "(id_profile is NULL or id_profile = 0) AND ";
		}

		$db = Marion::getDB();
		
		
		$user = Marion::getUser();
		if( !$user->auth('superadmin') ){
			$condizione .= "superadmin = 0 AND ";
		}
		
		$limit = $this->getListOption('per_page');
		
		if( $name = _var('name') ){

			$condizione .= "name LIKE '%{$name}%' AND ";
		}

		
	
		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $db->select('count(*) as tot','user',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			
			$condizione .= " ORDER BY {$order} {$order_type}";
			
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $db->select('id,name,surname,id_profile','user',$condizione);
		
		if( okArray($list) ){
			
			foreach($list as $k => $v){
				

				$cont = $db->select('count(*) as cont','user',"id_profile={$v['id']}");
				$list[$k]['tot'] = $cont[0]['cont'];
			}
		}
		
		
		$this->setListOption('total_items',$tot[0]['tot']);
		$this->setDataList($list);

	}
	
	function displayUsers(){
		
		
		$id_profile = _var('id_profile');
		$action = $this->getAction();
		$obj = Profile::withId($id_profile);
		if( is_object($obj) ){
			if( $action == 'users'){
				$title = "Utenti del profilo <b>".$obj->get('name')."</b>";
			}else{
				$title = "Aggungi utenti al profilo <b>".$obj->get('name')."</b>";
			}
			
		}
		$this->setTitle($title);
		

	
		$fields = array(
			0 => array(
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',
			),
			1 => array(
				'name' => 'Nome',
				'function_type' => 'row',
				'function' => function($row){
					return $row['name']." ".$row['surname'];
				},
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			),

		);

		$container = $this->getListContainer()
					->setFieldsFromArray($fields);

		if( $action == 'add_user'){
			$container->addActionBulkButton(
				(new \Marion\Controllers\Elements\ListActionBulkButton('add_profile'))
				
				->setConfirmMessage("Sicuro di volere associare il profilo <b>".$obj->get('name')."</b> agli utenti selezionati?")
				->setCustomFields(
					[
						'id_profile' => $id_profile
					]
				)
				->setConfirm(true)
				->setIconType('icon')
				->setIcon('fa fa-plus')
				->setText('associa al profilo')
				
			)->addActionRowButton(
				(new \Marion\Controllers\Elements\ListActionRowButton('add'))
				->setUrlFunction(function($row){
					$url = $this->getUrlScript()."&action=add_to_profile&id_profile="._var('id_profile')."&url_back=".urlencode($this->getUrlCurrent())."&id_user=".$row['id'];
					return $url;
				})
				->setIconType('icon')
				->setIcon('fa fa-plus')
				->setText('aggiungi')
				
			);
			$row_actions['actions'] = array(
				'add' =>  array(
					'text' => 'aggiungi',
					'icon_type' => 'icon',
					'icon' => 'fa fa-plus',
					'url_function' => 'addToProfile'
				),
			);
			

			$this->addToolButton(
				(new \Marion\Controllers\Elements\UrlButton('back'))
				->setText(_translate('back'))
				->setUrl($this->getUrlScript()."&action=users&id_profile=".$id_profile)
				->setIconType('icon')
				->setClass('btn btn-secondario')
				->setIcon('fa fa-arrow-left')
			);
		}else{

		

			$container->addActionBulkButton(
				(new \Marion\Controllers\Elements\ListActionBulkButton('remove_profile'))
				->setCustomFields(
					[
						'id_profile' => $id_profile
					])
				->setConfirm(true)
				->setConfirmMessage("Sicuro di volere rimuover dal profilo <b>".$obj->get('name')."</b> agli utenti selezionati?")
				->setIconType('icon')
				->setIcon('fa fa-trash-o')
				->setText('rimuovi dal profilo')
				
			)->addDeleteActionRowButton();
			$container->getActionRowButton('delete')->setUrlFunction(function($row){
				$url = $this->getUrlScript()."&action=confirm_delete&id="._var('id_profile')."&type=user&url_back=".urlencode($this->getUrlCurrent())."&id_user=".$row['id'];
				if( $this->_page_id ){
					$url .= "&pageID=".$this->_page_id;
				}
				return $url;
			});

			

			


			$this->addToolButtons([
				(new \Marion\Controllers\Elements\UrlButton('add'))
				->setText('aggiungi utente')
				->setUrl($this->getUrlScript()."&action=add_user&id_profile=".$id_profile)
				->setIconType('icon')
				->setClass('btn btn-principale')
				->setIcon('fa fa-plus'),

				(new \Marion\Controllers\Elements\UrlButton('back'))
				->setText('torna ai profili')
				->setUrl($this->getUrlList())
				->setIconType('icon')
				->setClass('btn btn-secondario')
				->setIcon('fa fa-arrow-left')
				]
			);
			
			
		}
		
		$container->build();

		
		$this->getListUsers();
			
		parent::displayList();
	}

	function displayForm(){
		$this->setMenu('profiles');
		
		$action = $this->getAction();

		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			
			$array = $this->checkDataForm('profile',$formdata);
			if( $array[0] == 'ok'){

				if( $action == 'add'){
				$obj = Profile::create();
				}else{
					$obj = Profile::withId($array['id']);
				}

				
				$obj->set($array);
				$obj->setPermissions($array['permissions']);

			
				
				

				$res = $obj->save();
				if( is_object($res) ){
					$this->saved();
				}else{
					$this->errors[] = $res;
				}


			}else{
				$this->errors[] = $array[1];
			}

			$dati = $formdata;
			

		}else{
		
			createIDform();
		
			$id = $this->getID();
			
			if($action != 'add'){
				$obj = Profile::withId($id);
				
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
		

		$dataform = $this->getDataForm('profile',$dati);
			
		$this->setVar('dataform',$dataform);
		$this->output('profile/form.htm');
		

	}


	function setMedia(){
		
		if( $this->getAction() == 'add_user'){
			$this->registerJS('js/profiles.js');
		}else{
			if( $this->getAction() != 'list'){
				$this->loadJS('multiselect');
			}
		}
	}


	function getList(){
		$db = Marion::getDB();
		
		$condizione = "1=1 AND ";
		$user = Marion::getUser();
		if( !$user->auth('superadmin') ){
			$condizione .= "superadmin = 0 AND ";
		}
		
		$limit = $this->getListContainer()->getPerPage();
		
		if( $name = _var('name') ){

			$condizione .= "name LIKE '%{$name}%' AND ";
		}

		
	
		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $db->select('count(*) as tot','profile',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			
			$condizione .= " ORDER BY {$order} {$order_type}";
			
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $db->select('*','profile',$condizione);

		if( okArray($list) ){
			
			foreach($list as $k => $v){
				

				$cont = $db->select('count(*) as cont','user',"id_profile={$v['id']}");
				$list[$k]['tot'] = $cont[0]['cont'];
			}
		}


		$total_items = $tot[0]['tot'];
		$container = $this->getListContainer();
		$container->setTotalItems($total_items);
		if( $total_items ){
				$container->setDataList($list);	
		}
		

		
	}

	function displayList(){
		$this->setMenu('profiles');
		$this->showMessage();

		$fields = array(
				0 => array(
					'name' => 'ID',
					'field_value' => 'id',
					'searchable' => true,
					'sortable' => true,
					'sort_id' => 'id',
					'search_name' => 'id',
					'search_value' => '',
					'search_type' => 'input',
				),
				1 => array(
					'name' => 'Nome',
					'field_value' => 'name',
					'sortable' => true,
					'sort_id' => 'name',
					'searchable' => true,
					'search_name' => 'name',
					'search_value' => _var('name'),
					'search_type' => 'input',
				),
				2 => array(
					'name' => '',
					'function_type' => 'row',
					'function' => function($row){
						return "<a href='".$this->getUrlScript()."&action=users&id_profile={$row['id']}' class='btn btn-sm btn-default'><i class='fa fa-users'></i> {$row['tot']} "._translate('users')."</a>";
					}
				),


			);
		$this->setTitle('Profili');
		

		$container = $this->getListContainer();
		$container->setFieldsFromArray($fields)
				  ->addEditActionRowButton()
				  ->addCopyActionRowButton()
				  ->addDeleteActionRowButton();
		
		
		$del_btn = $container->getActionRowButton('delete');
		$del_btn->setEnableFunction(function($row){
			return  !$row['superadmin'];
		});
		$edit_btn = $container->getActionRowButton('delete');
		$edit_btn->setEnableFunction(function($row){
			return  !$row['superadmin'];
		});
		$copy_btn = $container->getActionRowButton('delete');
		$copy_btn->setEnableFunction(function($row){
			return  !$row['superadmin'];
		});
		
		
		$this->getList();
		$container->build();

		parent::displayList();
			
	}




	
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Profilo salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Profilo eliminato con successo','success');
		}
	}

	function saved(){
		$this->redirectTolist(array('saved'=>1));
	}


	function delete(){
		$id = $this->getID();
		
		$obj = Profile::withId($id);
		if( is_object($obj) ){

			if( _var('type') == 'user' ){
				
				$obj->removeUser(_var('id_user'));
				header('Location: index.php?ctrl=ProfileAdmin&action=users&id_profile='.$id);
				exit;
			}

			if( $obj->superadmin ){
				$this->errors[] = "Il profilo <b>{$obj->get('name')}</b> non può essere eliminato";
			}else{
				$obj->delete();
			}
		}
		parent::delete();
		
		

		
	}

	function bulk(){
		
		$action = $this->getBulkAction();
		$ids = $this->getBulkIds();
		$db = Marion::getDB();
		
		switch($action){
			case 'add_profile':
				$id_profile = _var('id_profile');
				if( $id_profile ){
					foreach($ids as $id){
						$user = User::withId($id);
						if( is_object($user) ){
							$user->id_profile = $id_profile;
							$user->save();

						}
					}
				}	
				break;
			case 'remove_profile':
				foreach($ids as $id){
					$user = User::withId($id);
					if( is_object($user) ){
						$user->id_profile = 0;
						$user->save();

					}
				}
				break;
			case 'delete':
				foreach($ids as $id){
					$obj = Profile::withId($id);
					if( is_object($obj) ){
						if( $obj->superadmin ){
							$this->errors[] = "Il profilo <b>{$obj->get('name')}</b> non può essere eliminato";
						}else{
						
							$obj->delete();
						}

					}
				}
				break;
		}
		parent::bulk();
	}


	function getUrlDelete(){
		if( $this->getID()){
			$url = $this->getUrlScript()."&action=delete&id=".$this->getID();
		}else{
			$url = $this->getUrlScript()."&action=delete";
		}
		$action = $this->getAction();
		if( _var('type') == 'user'){
			$url .= "&type=user";
			$url .= "&id_user="._var('id_user');
		}
		if( $this->_page_id ){
			$url .= "&pageID=".$this->_page_id;
		}
		
		return $url;
		
	}


	function getUrlConfirmDeleteUser($id_user=0){
		
		$url = $this->getUrlScript()."&action=confirm_delete&id=".$this->getID()."&type=user&url_back=".urlencode($this->getUrlCurrent())."&id_user=";

		if( $id_user ){
			$url .= $id_user;
		}
		
		if( $this->_page_id ){
			$url .= "&pageID=".$this->_page_id;
		}
		return $url;
	}

	
	function ajax(){
		
		$action = $this->getAction();
		$id = $this->getID();
		switch($action){
			case 'add_profile_user':
				$user = User::withId($id);
				
				if( is_object($user) ){
					$user->id_profile = _var('profile');
					
					$user->save();
					$risposta = array(
						'result' => 'ok',
					);
				}else{
					$risposta = array(
						'result' => 'nak'	
					);
				}
				break;
				
		}

		echo json_encode($risposta);
		
	}

	function permissions(){
		$user = Marion::getUser();
		
		$query = Permission::prepareQuery()
			->where('active',1)
			->where('label','base','<>');
			
		if( is_object($user) && !$user->auth('superadmin') ){
			$query->where('label','admin','<>');
			$query->where('label','config','<>')->where('label','superadmin','<>');
		}
			
		$permessi = $query->orderBy('orderView')->get();
		
		foreach($permessi as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		
		return $toreturn;
	}



}



?>