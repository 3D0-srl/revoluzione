<?php
class IndexController extends AdminModuleController{

	function getList(){
		$db = Marion::getDB();
		$list = $db->select('i.*,l.text','slider_top_item as i join slider_top_item_lang as l on l.id_item=i.id',"lang='"._MARION_LANG_."'");
		
		$tot =  $db->select('count(*) as tot','slider_top_item as i join slider_top_item_lang as l on l.id_item=i.id',"lang='"._MARION_LANG_."'");
		$this->getListContainer()
				->setTotalItems($tot[0]['tot'])
				->setDataList($list);
	}

	function displayList(){
		$this->setMenu('slider_top');
		$this->setTitle('Slider Top');


		if( _var('success') ){
			$this->displayMessage('Dati salvati con successo');
		}
		if( _var('deleted') ){
			$this->displayMessage('slide eliminata con successo');
		}

		$this->getList();
	

		$this->getListContainer()
			
			->addEditActionRowButton()
			->addDeleteActionRowButton()
			->enableBulkActions(false)
			->setFieldsFromArray(
					array(
						[
							'name' => 'testo',
							'field_value' => 'text',
							'function_type' => 'value',
							'function' => function($text){
								$text = preg_replace('/\[\[/',"<b>",$text);
								$text = preg_replace('/\]\]/',"</b>",$text);
								return $text;
							}
						],
						[
							'name' => 'countdown',
							'field_value' => 'enable_countdown',
							'function_type' => 'value',
							'function' => function($val){
								if( $val) {
									return "<span class='label label-success'>".strtoupper(_translate('YES'))."</span>";
								}else{
									return "<span class='label label-danger'>".strtoupper(_translate('NO'))."</span>";
								}
							}
						],
						[
							'name' => 'online',
							'field_value' => 'online',
							'function_type' => 'value',
							'function' => function($val){
								if( $val) {
									return "<span class='label label-success'>".strtoupper(_translate('YES'))."</span>";
								}else{
									return "<span class='label label-danger'>".strtoupper(_translate('NO'))."</span>";
								}
								
							}
						],
						[
							'name' => 'ordine visualiz.',
							'field_value' => 'order_view',
							'function_type' => 'value',
						],
						[
							'name' => 'validità',
							'function_type' => 'row',
							'function' => function($row){
								if(!$row['time_range']){
									return 'SEMPRE';
								}else{
									return strftime('%d/%m/%Y %H:%M',strtotime($row['date_start']))." - ".strftime('%d/%m/%Y %H:%M',strtotime($row['date_end']));
								}

							}
						],

					)
		)->build();
		
		parent::displayList();
		
	}

	function setMedia(){
		parent::setMedia();
		$this->loadJS('spectrum');
	}

	function displayForm(){
		$this->setTitle('Slider Top Item');
		$this->setMenu('slider_top');
		$action = $this->getAction();
		if( $this->isSubmitted() ){
			$dati = $this->getFormdata();
			$campi_aggiuntivi = [];
			if($dati['enable_countdown']){
				$campi_aggiuntivi['countdown']['obbligatorio'] = 1;
			}
			if($dati['time_range']){
				$campi_aggiuntivi['date_start']['obbligatorio'] = 1;
				$campi_aggiuntivi['date_end']['obbligatorio'] = 1;
			}
			$array = $this->checkDataForm('slider_top_item',$dati,$campi_aggiuntivi);
			if( $array[0]  == 'ok'){
				if( $action == 'add'){
					$obj = SliderTopItem::create();
				}else{
					$obj = SliderTopItem::withId($array['id']);
				}
				$obj->set($array)->save();
				$this->redirectToList(array('succes'=>1));
				
			}else{
				$this->errors[] = $array[1];
			}
		}else{
			if( $action == 'edit'){
				$id = $this->getID();
				$obj = SliderTopItem::withId($id);
				if( is_object($obj)){
					$dati = $obj->prepareForm2();
				}
			}
		}
		$dataform = $this->getDataForm('slider_top_item',$dati);
		$this->setVar('dataform',$dataform);
		$this->output('form.htm');
	}


	function delete(){

		$id = $this->getID();
		$obj = SliderTopItem::withId($id);
		if( is_object($obj)){
			$obj->delete();
		}

		$this->redirectToList(array('deleted'=>1));

	}
}


?>