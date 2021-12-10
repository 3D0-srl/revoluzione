<?php
use Marion\Entities\User;
use Marion\Core\Marion;
use Marion\Entities\Cms\Notification;
use Marion\Entities\{Country,UserCategory};
class UserAdminController extends \Marion\Controllers\AdminController{
	public $_auth = 'user_management';


	/**
	 * Override metodo setMedia
	 */

	function setMedia(){
		parent::setMedia();
		$this->registerJS($this->getBaseUrlBackend().'js/user.js','end');
	}

	function displayForm(){
		$this->setMenu('manage_users');

		createIDform();
		$id =  $this->getID();
		$action =  $this->getAction();


		if( $this->isSubmitted() ){

			$formdata = $this->getFormdata();

			$user_admin = Marion::getUser();
			if( $formdata['id'] == $user_admin->id ){
				$formdata['active'] = 1;
			}



			if( $action == 'add'){
				$campi_aggiuntivi['password']['obbligatorio'] = 1;
			}else{
				if( !$formdata['reset_password'] ){
					$campi_aggiuntivi['password']['obbligatorio'] = 0;
					unset($formdata['password']);
				}

			}

			$array = $this->checkDataForm('user',$formdata,$campi_aggiuntivi);

			if( $array[0] == 'ok' ){
				if( $array['password'] ){
					$_tmp_password = $array['password'];
					$array['password'] = password_hash($array['password'], PASSWORD_DEFAULT);
				}
				if(	$action == 'add'){
					$user = User::create();
				}else{
					$user = User::withId($array['id']);
				}
				$user->set($array);
				$res = $user->save();


				if(is_object($res)){

					$this->redirectToList(array('saved'=>1));
				}else{
					if( $_tmp_password ){
						$array['password'] = $_tmp_password;
					}
					$this->errors[] = $res;
				}
				$dati = $array;

			}else{
				$dati = $array;
				$this->errors[] = $array[1];


			}



		}else{
			//elimino la notifica se esiste
			$current_user = Marion::getUser();
			$notification = Notification::prepareQuery()
				->where('receiver',$current_user->id)
				->where('custom',$id)
				->getOne();

			if( is_object($notification) ){
				$notification->set(
						array('view'=>1)
					)->save();
			}
			$dati = NULL;
			if( $action != 'add'){
				$utente = User::withId($id);
				if(is_object($utente) ){
					$dati = $utente->prepareForm();

				}
				if( $action == 'duplicate'){
					unset($dati['id']);
					$action = 'add';
				}
			}
			unset($dati['password']);

		}

		$dataform = $this->getDataForm('user',$dati);

		$this->setVar('dataform',$dataform);
		$this->output('user/form.htm');





	}


	function getList(){
		$db = Marion::getDB();

		$condizione = "1=1 AND ";


		$limit = $this->getListContainer()->getPerPage();

		if( $name = _var('name') ){

			$condizione .= "(name LIKE '%{$name}%' OR surname LIKE '%{$name}%') AND ";
		}

		if( $username = _var('username') ){
			$condizione .= "username LIKE '%{$username}%' AND ";
		}

		if( $email = _var('email') ){
			$condizione .= "email LIKE '%{$email}%' AND ";
		}

		if( isset($_GET['active']) ){
			$active = _var('active');
			if( $active != -1 ){
				$condizione .= "active = {$active} AND ";
			}
		}


		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		$condizione = preg_replace('/AND $/','',$condizione);


		$tot = $db->select('count(*) as tot','user',$condizione);




		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			if( $order_type == 'name' ){
				$condizione .= " ORDER BY name {$order_type}, surname {$order_type}";
			}else{
				$condizione .= " ORDER BY {$order} {$order_type}";
			}
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);

		}




		$list = $db->select('*','user',$condizione);


		$this->getListContainer()
			->setTotalItems($tot[0]['tot']);

		if( okarray($list)){
			$this->getListContainer()->setDataList($list);
		}


	}

	function displayList(){
			$this->setMenu('manage_users');
			$this->setTitle(_translate('Users'));

			if( _var('saved') ){
				$this->displayMessage(_translate('user_saved'));
			}
			if( _var('deleted') ){
				$this->displayMessage(_translate('user_deleted'),'success');
			}

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
					'name' => 'Utente',
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
				2 => array(
					'name' => 'Username',
					'field_value' => 'username',
					'searchable' => true,
					'sortable' => true,
					'sort_id' => 'username',
					'search_name' => 'username',
					'search_value' => _var('username'),
					'search_type' => 'input',
				),
				3 => array(
					'name' => 'Email',
					'field_value' => 'email',
					'searchable' => true,
					'sortable' => true,
					'sort_id' => 'email',
					'search_name' => 'email',
					'search_value' => _var('email'),
					'search_type' => 'input',
				),
				4 => array(
					'name' => 'Attivo',
					'function_type' => 'row',
					'function' => function($row){
						if( _var('export') ){
							if ($row['active'] ){
								$html = strtoupper(_translate('active'));
							}else{
								$html = strtoupper(_translate('inactive'));
							}
						}else{
							if ($row['active'] ){
								$html = "<span class='label label-success'  id='status_{$row['id']}' style='cursor:pointer;' onclick='change_visibility({$row['id']}); return false;'>".strtoupper(_translate('active'))."</span>";
							}else{
								$html = "<span class='label label-danger' id='status_{$row['id']}' style='cursor:pointer;' onclick='change_visibility({$row['id']}); return false;'>".strtoupper(_translate('inactive'))."</span>";
							}
						}
						return $html;
					},
					'searchable' => true,
					'search_name' => 'active',
					'search_value' => (isset($_GET['active']))? _var('active'):-1,
					'search_type' => 'select',
					'search_options' => array(
						-1 => 'seleziona..',
						0 => 'inattivo',
						1 => 'attivo',

					),
				),


			);


			$container = $this->getListContainer()
				->enableExport(true)
				->setPerPage(25)
				->setExportTypes(['pdf','csv','excel'])
				->enableBulkActions(true)
				->enableSearch(true)
				->setFieldsFromArray($fields)
				->addEditActionRowButton()
				->addCopyActionRowButton()
				->addDeleteActionRowButton()
				->addActionBulkButtons(
					[
						(new \Marion\Controllers\Elements\ListActionBulkButton('active'))
							->setConfirm(true)
							->setConfirmMessage('Sicuro di voler attivare gli account selezionati?')
							->setText('attiva')
							->setIconType('icon')
							->setIcon('fa fa-eye'),


						(new \Marion\Controllers\Elements\ListActionBulkButton('inactive'))

							->setConfirm(true)
							->setConfirmMessage('Sicuro di voler disattivare gli account selezionati?')
							->setIconType('icon')
							->setIcon('fa fa-eye-slash')
							->setText('disattiva')
					]);

			$this->getList();
			$container->build();





			parent::displayList();

	}




	function bulk(){
		$action = $this->getBulkAction();
		$ids = $this->getBulkIds();


		switch($action){
			case 'active':

				foreach($ids as $id){
					$user = User::withId($id);
					if( is_object($user) ){
						$user->active = 1;
						$user->save();
					}
				}
				break;
			case 'inactive':
				foreach($ids as $id){
					$user = User::withId($id);
					if( is_object($user) ){
						$user->active = 0;
						$user->save();
					}

				}
				break;
			case 'delete':
				foreach($ids as $id){
					$user = User::withId($id);
					if( is_object($user) ){
						$user->deleted = 1;
						$user->save();
					}
				}
				break;
		}
		parent::bulk();
	}




	function delete(){
		$id = $this->getID();

		$obj = User::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));



	}

	function ajax(){

		$action = $this->getAction();
		$id = $this->getID();
		switch($action){
			case 'change_visibility':
				$obj = User::withId($id);
				if( is_object($obj) ){
					if( $obj->active ){
						$obj->active = 0;
					}else{
						$obj->active = 1;
					}

					$obj->save();
					$risposta = array(
						'result' => 'ok',
						'text' => $obj->active? strtoupper(_translate('active')):strtoupper(_translate('inactive')),
						'status' => $obj->active
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



	// FUNZIONI DI TEMPLATE
	function array_nazioni(){
		$nazioni = Country::getAll();
		foreach($nazioni as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		return $toreturn;
	}

	function array_province(){
		$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$database = _obj('Database');
		$prov = $database->select('sigla','provincia','','sigla ASC');
		if( okArray($prov) ){
			foreach($prov as $v){
				$toreturn[$v['sigla']] = $v['sigla'];
			}
		}

		return $toreturn;
	}




	function array_userCategory(){
		$categorie = UserCategory::prepareQuery()->get();
		$select = array();
		foreach($categorie as $v){
			$select[$v->getId()] = $v->get('name');
		}
		return $select;
	}

	function array_type_buyer(){
		$labels = array('private','company');
		foreach($labels as $label){
			$toreturn[$label] = __("type_buyer_".$label);
		}
		return $toreturn;
	}


	function array_userCategory_search(){
		$categorie = UserCategory::prepareQuery()->get();
		$select = array(
			0 => 'seleziona...'
		);
		foreach($categorie as $v){
			$select[$v->getId()] = $v->get('name');
		}
		return $select;
	}

	function active_search(){

		$select = array(
			0 => 'seleziona...',
			1 => 'SI',
			-1 => 'NO'
		);

		return $select;
	}

}



?>
