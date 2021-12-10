<?php
use Marion\Controllers\AdminModuleController;
use Shop\PriceList;
use Marion\Core\Marion;
class PriceListAdminController extends AdminModuleController{
	public $_auth = 'ecommerce';
	


	function displayForm(){
		$this->setMenu('manage_pricelist');
		

		createIDform();
		$id =  $this->getID();
		$action =  $this->getAction();
		
		
		if( $this->isSubmitted() ){

			$dati = $this->getFormdata();
			
		
			$array = $this->checkDataForm('priceList',$dati);
			if( $array[0] == 'ok' ){

				if(	$action == 'add'){
					$obj = PriceList::create();
				}else{
					$obj = PriceList::withId($array['id']);
				}
				if( $action == 'add'){
					$database = Marion::getDB();
					$max = $database->select('max(priority) as max','priceList');
					$array['priority'] = $max[0]['max']+1;
					
				}
				$obj->set($array);
				$res = $obj->save();
				
				
				if(is_object($res)){
					$this->redirectToList(array('saved'=>1));
				}else{
					$this->errors[] = $res;
				}
				//$dati = $array;
				
			}else{
				$dati = $array;
				$this->errors[] = $array[1];
				
				
			}
			

			
		}else{
			
			$dati = NULL;
			if( $action != 'add'){
				$utente = PriceList::withId($id);
				if(is_object($utente) ){
					$dati = $utente->prepareForm2();
					
				}
				if( $action == 'duplicate'){
					unset($dati['id']);
					$action = 'add';
				}
			}

		}
		$dataform = $this->getDataForm('priceList',$dati,$this);
		$this->setVar('dataform',$dataform);
	
		$this->output('form_pricelist.htm');	

		

	}

	function switchOrder($field1,$field2){
		
		$database = Marion::getDB();
		$a =$database->select('*','priceList',"id={$field1}");
		$b = $database->select('*','priceList',"id={$field2}");
		$a = $a[0]['priority'];
		$b = $b[0]['priority'];
		if( $a == 1 || $b == 1 ){
			$this->errors[] = "Non puoi cambiare la priorità del listino di default";
		}else{

		
			$database->update('priceList',"id={$field1}",array('priority'=>$b));
			$database->update('priceList',"id={$field2}",array('priority'=>$a));
			$this->displayMessage('Priorità cambiata con successo!');
		}
	}


	function getList(){
		$database = Marion::getDB();
		
		$condizione = "locale = '"._MARION_LANG_."' AND ";
		
		$limit = $this->getListContainer()->getPerPage();
		
		if( $name = _var('name') ){
			$condizione .= "name LIKE '%{$name}%' AND ";
		}
		if( $label = _var('label') ){
			$condizione .= "label LIKE '%{$label}%' AND ";
		}

		if( isset($_GET['active']) ){
			$active = _var('active');
			if( $active != -1){
				$condizione .= "active = {$active} AND ";
			}
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','priceList as p join priceListLocale as l on l.priceList=p.id',$condizione);

		
		

		
		$condizione .= " ORDER BY priority";
		


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('p.id,p.label,l.name,p.active,p.priority,p.image','priceList as p join priceListLocale as l on l.priceList=p.id',$condizione);
		
		foreach($list as $k => $v){
			if( $k > 0 ){
				$list[$k]['prec'] = $list[$k-1]['id'];
				
			}

			if( $k < count($list)-1 ){
				$list[$k]['succ'] = $list[$k+1]['id'];
			}

			
		}
		$this->getListContainer()->setTotalItems($tot[0]['tot']);
		if( $tot[0]['tot'] > 0){
			$this->getListContainer()->setDataList($list);
		}

	}
	function displayList(){
		$this->setMenu('manage_pricelist');

		$move = _var('switch');
		if( $move ){
			$field1 = _var('field1');
			$field2 = _var('field2');
			$this->switchOrder($field1,$field2);
		}

		if( _var('saved') ){
			$this->displayMessage(_translate('price_list_saved'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('price_list_deleted'),'success');
		}
			
			
		$fields = array(
			
			0 => array(
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				//'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',
			),
			1 => array(
				'name' => '',
				'function_type' => 'row',
				'function' => function($row){
					$html = '';
					 if($row['image'] ){
						$url = _MARION_BASE_URL_."img/{$row['image']}/or-nw/image.png";
						$html = "<img src='{$url}'/>";
					 }
					 return $html;
				},
				//'sortable' => true,
				'sort_id' => 'active',
			),
					
			2 => array(
				'name' => 'Etichetta',
				'field_value' => 'label',
				//'sortable' => true,
				'sort_id' => 'label',
				'searchable' => true,
				'search_name' => 'label',
				'search_value' => _var('label'),
				'search_type' => 'input',
			),
			3 => array(
				'name' => 'Nome',
				'field_value' => 'name',
				//'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			),
			4 => array(
				'name' => 'Descrizione',
				'field_value' => 'description',
				//'sortable' => true,
				'sort_id' => 'description',
				//'searchable' => true,
				'search_name' => 'description',
				'search_value' => _var('description'),
				'search_type' => 'input',
			),
			5 => array(
				'name' => '',
				'function_type' => 'row',
				'function' => function($row){
					if( _var('export') ){
						if ($row['active'] ){
							$html = strtoupper(_translate('online'));
						}else{
							$html = strtoupper(_translate('offline'));
						}
					}else{
						if ($row['active'] ){
							$html = "<span class='label label-success'  id='status_{$row['id']}' style='cursor:pointer;' onclick='change_visibility({$row['id']}); return false;'>".strtoupper(_translate('online'))."</span>";
						}else{
							$html = "<span class='label label-danger' id='status_{$row['id']}' style='cursor:pointer;' onclick='change_visibility({$row['id']}); return false;'>".strtoupper(_translate('offline'))."</span>";
						}
					}
			
					return $html;
				},
				//'sortable' => true,
				'sort_id' => 'active',
				'searchable' => true,
				'search_name' => 'active',
				'search_value' => (isset($_GET['active']))? _var('active'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => '--SELECT--',
					0 => 'NO',
					1 => 'SI',
					
				),
			),
			6 => array(
				'name' => 'Priorità',
				'function_type' => 'row',
				'function' => function($row){
					$html = '';
					if ($row['prec']){
						$html .= "<button onclick=\"document.location.href='index.php?ctrl=PriceListAdmin&mod=ecommerce&action=list&switch=up&field1={$row['id']}&field2={$row['prec']}'\"><i class='fa fa-arrow-up'></i></button>";
					}
			
					if ($row['succ']){
						$html .= "<button onclick=\"document.location.href='index.php?ctrl=PriceListAdmin&mod=ecommerce&action=list&switch=down&field1={$row['id']}&field2={$row['succ']}'\"><i class='fa fa-arrow-down'></i></button>";
					}
					return $html;
				},
			),
			
			

		);

		$this->getListContainer()
			->enableBulkActions(false)
			->setFieldsFromArray($fields)
			->addEditActionRowButton()
			->addDeleteActionRowButton()
			->build();
		
		$this->setTitle('Listini prezzi');
		$this->getList();

		parent::displayList();
	}


	

	

	


	function delete(){
		$id = $this->getID();

		$obj = PriceList::withId($id);
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
				$obj = PriceList::withId($id);
				
				if( is_object($obj) ){
					if( $obj->active ){
						$obj->active = 0;
					}else{
						$obj->active = 1;
					}
					
					$obj->save();
					$risposta = array(
						'result' => 'ok',
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

	function setMedia(){
		if( $this->getAction() == 'list'){
			$this->registerJS($this->getBaseUrl().'modules/ecommerce/js/admin/pricelist.js','end');

		}
	}


}



?>