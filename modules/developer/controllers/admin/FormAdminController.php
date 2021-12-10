<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\{Marion,Form};
class FormAdminController extends AdminModuleController{
	public $_auth = 'superadmin';

	

	function getFields(){
		$database = Marion::getDB();
		$gruppi_form = $database->select('*','form_gruppo');
		$select_gruppi_form = array("Seleziona...");
		if(okArray($gruppi_form)){
			foreach($gruppi_form as $v){
				$select_gruppi_form[$v['codice']] = $v['nome'];
			}
		}
		$campi_form =array(
			'codice' => array(
				'campo' => 'codice',
				'type' => 'hidden',
				'default' => '',
				'obbligatorio' => false,
				'etichetta' => 'codice form',
			),
			'gruppo'=>array(
				'campo'=>'gruppo',
				'type'=>'select',
				/*'origine_dati' => 'php',
				'function_php' => 'array_type',*/
				'options' => $select_gruppi_form,
				'obbligatorio'=>false,
				'default'=>'0',
				'etichetta'=>'gruppo appartenenza'
			),
			'nome' => array(
				'campo' => 'nome',
				'type' => 'text',
				'default' => '',
				'lunghezzamin' => 2,
				//'postfunction' => 'strtolower',
				'obbligatorio' => true,
				'etichetta' => 'Nome',
			),
			/* 'method'=>array(
				'campo'=>'method',
				'type'=>'radio',
				
				'options' => array('GET','POST'),
				'obbligatorio'=>true,
				'default'=>'POST',
				'etichetta'=>'method form'
			),
			'action' => array(
				'campo' => 'action',
				'type' => 'text',
				'default' => '',
				'lunghezzamin' => 2,
				'postfunction' => 'strtolower',
				'obbligatorio' => false,
				'etichetta' => 'action form',
			),
			'url' => array(
				'campo' => 'url',
				'type' => 'text',
				'default' => '',
				'lunghezzamin' => 2,
				'postfunction' => 'strtolower',
				'obbligatorio' => false,
				'etichetta' => 'url form',
			),*/
			'commenti' => array(
				'campo' => 'commenti',
				'type' => 'text',
				'default' => '',
				'lunghezzamin' => 2,
				'obbligatorio' => false,
				'etichetta' => 'commenti form',
			),
			/*'campi_default'=>array(
				'campo'=>'campi_default',
				'type'=>'checkbox',
				'options'=>$campi_globali_form,
				'default'=>'', //i campi selezionati default vengono messi in un array
				'obbligatorio'=>false,
				//'postfunction' => 'serialize',
				//'prefunction' => 'unserialize',
				'etichetta'=>'campi di default',
			),
			'captcha'=>array(
				'campo'=>'captcha',
				'type'=>'radio',
				
				'options' => array(0,1),
				'obbligatorio'=>true,
				'default'=> 0,
				'etichetta'=>'captcha'
			),
			'submit'=>array(
				'campo'=>'submit',
				'type'=>'submit',
				'default'=>'Salva',
			),*/
		);

		return $campi_form;
	}


	

	function displayForm(){
			$this->setMenu('developer_forms');
			$database = Marion::getDB();
			$fields = $this->getFields();
			$form = new Form();
			$form->addElements($fields);
			$id = $this->getID();
			if( $this->isSubmitted()){
				
				$formdata = $this->getFormdata();
				$id = $formdata['codice'];
				$array = $form->checkData($formdata);
				if( $array[0] == 'ok'){
					
					unset($array[0]);
					if( $formdata['codice'] ){
						//debugga($id);
						$database->update('form',"codice={$id}",$array);
						//debugga($database->lastquery);exit;
					}else{
						$database->insert('form',$array);
					}
					$this->redirectToList(array('saved'=>1));
				}else{
					$this->errors[] = $array[1];
					$dati = $formdata;
				}
			}else{
				$database = Marion::getDB();
				
				$dati = $database->select('*','form',"codice={$id}");
				if( okArray($dati) ) $dati = $dati[0];
			}


			$action = $this->getAction();
			if( $action == 'edit'){
				$dati['id'] = $dati['codice'];
			}
			
			if( $action == 'duplicate'){
				unset($dati['codice']);
			}
			
			$dataform = $form->prepareData($dati);
			$this->setVar('dataform',$dataform);
			$this->output('form_form.htm');
	}

	function getList(){
		
		

		$database = Marion::getDB();
		
		$condizione = "1=1 AND ";

		
		
		$limit = $this->getListOption('per_page');
		
		if( $nome = _var('nome') ){
			$condizione .= "nome LIKE '%{$nome}%' AND ";
		}
		
		
		

		if( $id = _var('id') ){
			$condizione .= "codice = {$id} AND ";
		}


	

	
		
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','form',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}

		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		//$list = $database->select('id,name,sku,visibility,section,type,images','product as p left outer join productLocale as l on l.product=p.id',$condizione);
		
		$list = $database->select("codice as id,nome",'form',"{$condizione}");
		
		$total_items = $tot[0]['tot'];

		
		$this->setListOption('total_items',$total_items);
		$this->setDataList($list);

	}
	

	function displayList(){
		
		$this->setMenu('developer_forms');
		if( _var('saved') ){
			$this->displayMessage(_translate('form_saved'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('form_deleted'),'success');
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
				'name' => 'Nome',
				'field_value' => 'nome',
				'sortable' => true,
				'sort_id' => 'nome',
				'searchable' => true,
				'search_name' => 'nome',
				'search_value' => _var('nome'),
				'search_type' => 'input',
			),
			2 => array(
				'name' => '',
				'function_type' => 'row',
				'function' => 'getLinkFields',
			),
			
			

		);
		$row_actions = $this->getListOption('row_actions');
		
		$row_actions['actions']['export'] = array(
			'text' => 'esporta',
            'icon_type' => 'icon',
            'icon' => 'fa fa-download',
			'target_blank' => 1,
            'url' => "{{script_url}}&action=export&id={{field_id}}"
		);
		
		$this->setListOption('row_actions',$row_actions);

		$this->setTitle('Forms');
		$this->setListOption('fields',$fields);
		$this->getList();

		parent::displayList();
	}


	function getLinkFields($row){
		 return "<a href='index.php?mod=developer&ctrl=FormFieldAdmin&action=list&id_form={$row['id']}' class='btn btn-default btn-sm'><i class='fa fa-list'></i> visualizza campi</a>";
	}

	function bulk(){
		$action = $this->getBulkAction();
		$ids = $this->getBulkIds();
		$database = Marion::getDB();
		switch($action){
			
			case 'delete':
				foreach($ids as $id){
					
					$database->delete('form',"codice={$id}");
					
				}
				break;

			
		}
		parent::bulk();
	}


	function displayList2(){
		$this->setMenu('developer_forms');
		if( _var('saved') ){
			$this->displayMessage(_translate('form_saved'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('form_deleted'),'success');
		}
		
		//TWIG
		$this->addTemplateFunction(
			new \Twig\TwigFunction('getGroupForm', function ($id_group) {
				
				if( $GLOBALS['_tmp_form_group'][$id_group]){
					return $GLOBALS['_tmp_form_group'][$id_group];
				}else{
					$database = Marion::getDB();
					$gruppo_form = $database->select('*','form_gruppo',"codice={$id_group}");
					if(okArray($gruppo_form)){
						$GLOBALS['_tmp_form_group'][$id_group] = $gruppo_form[0]['nome'];
						return $gruppo_form[0]['nome'];
					}
					return '';
				}
			})
		);

		$database = Marion::getDB();
		$search = _var('search');
		$this->setVar('search',$search);

		$limit = $this->getLimitList();
		$offset = $this->getOffsetList();
		if($search){
			$cont = $database->select('count(*) as cont','form',"nome LIKE '%{$search}%'");
			if( $offset ) $limit .= ' offset '.$offset;
			$list = $database->select('*','form',"nome LIKE '%{$search}%'",null,$limit);
		}else{
			$cont = $database->select('count(*) as cont','form');
			if( $offset ) $limit .= ' offset '.$offset;
			$list = $database->select('*','form','1=1',null,$limit);
		}
		
		
		
		//$pager_links = $this->getPagerList($cont[0]['cont']);

			
			
		
			
		//$this->setVar('links',$pager_links);
		
		//debugga($list);exit;
		$this->setVar('list',$list);
		$this->output('list_form.htm');
	}




	

	function delete(){
		$id = $this->getID();
	
		$database = Marion::getDB();
		$database->delete('form',"codice={$id}");
		parent::delete();
		

		
	}


	function export(){
		$id = $this->getId();
		$database = Marion::getDB();
		$form = $database->select('*','form',"codice={$id}");
		
		if( okArray($form) ){
			$name = $form[0]['nome'];
			
			if( class_exists('Form')){
				
				$query = Form::export($name);
			}
			echo $query;

		}
	}

	function displayContent(){
		$action = $this->getAction();
		switch($action){
			case 'export':
				$this->export();
				exit;
		}
		$id = $this->getId();
		
		$form = new Form($id);
		$data =  null;
		$dataform = $form->prepareData($data,$this);
		
		


		$helper = new FormHelper($dataform);
		$helper->setLayout('tabs');
		/*$helper->setFields(
			array(
			'uncampo' => 'col-md-3',
			'unaselect' => 'col-md-9',
			'unaradio' => 'col-md-12'
			)
		);*/
		
		
		$tab = new FormHelperTab();
		$tab->name = 'General';
		$tab->fields =  array(
			'uncampo' => 'col-md-3',
			'unaselect' => 'col-md-9'
		);
		$helper->addTab($tab);
		$tab = new FormHelperTab();
		$tab->name = 'seconda';
		$tab->fields =  array(
			'multiselct' => 'col-md-3',
			'textarea' => 'col-md-12',
			'password' => 'col-md-12',
			'radio' => 'col-md-12'
		);
		


		
		$helper->addTab($tab);
		
		$html = $helper->build();
		



		
		$this->setVar('dataform',$dataform);
		$this->setVar('template_form',$html);
		
		//debugga($dataform);exit;

		$this->output('form_display.htm');
	}



	function opzioni(){
		
		return array(
			1 => 'uno',
			2 => 'due',
		);
	}


}