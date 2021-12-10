<?php
use Marion\Controllers\AdminModuleController;
use Shop\{PaymentMethod,UserCategory};
use Marion\Core\Marion;
class PaymentMethodAdminController extends AdminModuleController{
	public $_auth = 'ecommerce';


	function displayForm(){
		$this->setMenu('manage_payments');
		
		$action = $this->getAction();


		

		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			
			$array = $this->checkDataForm('payment',$dati);
			
			if( $array[0] == 'ok'){

				if( $action == 'add'){
				$obj = PaymentMethod::create();
				}else{
					$obj = PaymentMethod::withId($array['id']);
				}
				$obj->setUserCategories($array['userCategories']);
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

			//$dati = $array;

			
			

		}else{
		
			createIDform();
		
			$id = $this->getID();
			
			if($action != 'add'){
				$obj = PaymentMethod::withId($id);
				
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
		$dataform = $this->getDataForm('payment',$dati);
			
		$this->setVar('dataform',$dataform);

		$this->output('form_payment_method.htm');

	}


	function setMedia(){
		$this->registerJS($this->getBaseUrl().'modules/ecommerce/js/admin/payment_method.js','end');
	}



	function getList(){
		$database = Marion::getDB();
		
		$condizione = "locale = '"._MARION_LANG_."' AND enabled = 1 AND ";
		
		
		$limit = $this->getListContainer()->getPerPage();
		
		if( $name = _var('name') ){

			$condizione .= "name LIKE '%{$name}%' AND ";
		}

		if( $code = _var('code') ){

			$condizione .= "code = '{$code}' AND ";
		}

		$visibility = _var('visibility');
			
		if( isset($_GET['visibility']) && $visibility != -1 ){
			$condizione .= "visibility = {$visibility} AND ";
		}
		
			
		
	
		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		$condizione = preg_replace('/AND $/','',$condizione);
	

		$tot = $database->select('count(*) as tot','paymentMethod as p join paymentMethodLocale as l on l.paymentMethod=p.id',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			
			$condizione .= " ORDER BY {$order} {$order_type}";
			
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('id,image,code,visibility,name','paymentMethod as p join paymentMethodLocale as l on l.paymentMethod=p.id',$condizione);
		
		
		$this->getListContainer()->setTotalItems($tot[0]['tot']);
		if( $tot[0]['tot'] > 0 ){
			$this->getListContainer()->setDataList($list);
		}
		
	}

	function displayList(){
		$this->setMenu('manage_payments');
		$this->showMessage();

		$fields = array(
				0 => array(
					'name' => '',
					'field_value' => 'image',
					'function_type' => 'row',
					'function' => function($row){
						$id_image = $row['image'];
						if( $id_image ){
							$path_image = _MARION_BASE_URL_.'img/'.$id_image.'/th/image.jpg';
							
							return '<img class="imgprodlist" src="'.$path_image.'" alt=""/>';
						}
						return '';
					}
				),
				1 => array(
					'name' => 'ID',
					'field_value' => 'id',
					'searchable' => true,
					'sortable' => true,
					'sort_id' => 'id',
					'search_name' => 'id',
					'search_value' => '',
					'search_type' => 'input',
				),
				2 => array(
					'name' => 'Codice pagamento',
					'field_value' => 'code',
					'sortable' => true,
					'sort_id' => 'code',
					'searchable' => true,
					'search_name' => 'code',
					'search_value' => _var('code'),
					'search_type' => 'input',
				),
				3 => array(
					'name' => 'nome',
					'field_value' => 'name',
					'sortable' => true,
					'sort_id' => 'name',
					'searchable' => true,
					'search_name' => 'name',
					'search_value' => _var('name'),
					'search_type' => 'input',
				),
				4 => array(
					'name' => 'visibilità',
					'function' => function($row){
						if( _var('export') ){
							if ($row['visibility'] ){
								$html = strtoupper(_translate('online'));
							}else{
								$html = strtoupper(_translate('offline'));
							}
						}else{
							if ($row['visibility'] ){
								$html = "<span class='label label-success'  id='status_{$row['id']}' style='cursor:pointer;' onclick='change_visibility({$row['id']}); return false;'>".strtoupper(_translate('online'))."</span>";
							}else{
								$html = "<span class='label label-danger' id='status_{$row['id']}' style='cursor:pointer;' onclick='change_visibility({$row['id']}); return false;'>".strtoupper(_translate('offline'))."</span>";
							}
						}
				
						return $html;
					},
					'function_type' => 'row',
					'sortable' => true,
					'sort_id' => 'visibility',
					'searchable' => true,
					'search_name' => 'visibility',
					'search_value' => (isset($_GET['visibility']))? _var('visibility'):-1,
					'search_type' => 'select',
					'search_options' => array(
						-1 => 'seleziona..',
						1 => 'online',
						0 => 'offline'
					)
				),


			);
			
			$this->getListContainer()
				->enableBulkActions(false)
				->setFieldsFromArray($fields)
				->addEditActionRowButton()
				->addDeleteActionRowButton()
				->build();
			
			$this->enableBulkActions(false);

			$this->setTitle('Metodi di pagamento');
			$this->getList();
			
			parent::displayList();
			
	}


	
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Metodo di pagamento salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Metodo di pagamento eliminato con successo','success');
		}

	}

	function saved(){
		$this->redirectTolist(array('saved'=>1));
	}





	function ajax(){
		
		$action = $this->getAction();
		$id = $this->getID();
		switch($action){
			case 'change_visibility':
				$obj = PaymentMethod::withId($id);
				if( is_object($obj) ){
					if( $obj->visibility ){
						$obj->visibility = 0;
					}else{
						$obj->visibility = 1;
					}
					
					$obj->save();
					$risposta = array(
						'result' => 'ok',
						'status' => $obj->visibility,
						'text' => $obj->visibility? strtoupper(_translate('online')):strtoupper(_translate('offline')),
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


	function array_userCategory(){
		$categorie = UserCategory::prepareQuery()->get();
		$select = array();
		foreach($categorie as $v){
			$select[$v->getId()] = $v->get('name');
		}
		return $select;
	}

	

}



?>