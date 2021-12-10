<?php
class SyncroController extends ModuleController{

	function getReports(){
	
		$database = _obj('Database');
		$sel =$database->select('*','sincro_reports',"1=1 order by id DESC limit 10");
		foreach($sel as $k => $v){
			$sel[$k]['start'] = strftime('%H:%M:%S',strtotime($v['start']));
			$sel[$k]['end'] = strftime('%H:%M:%S',strtotime($v['end']));
			$sel[$k]['date'] = strftime('%d/%m/%Y',strtotime($v['start']));
			
			$reports = unserialize($v['reports']);
			unset($reports['start']);
			unset($reports['end']);
			unset($reports['duration']);
			unset($reports['type']);
			unset($reports['status']);
			$sel[$k]['reports'] = $reports;

			if( $sel[$k]['date'] == date('d/m/Y') ){
				$sel[$k]['date'] = 'Oggi';
			}
		}
		$this->setVar('reports',$sel);
	}



	function display(){
		$action = $this->getAction();
		$data = '';
		switch($action){
			case 'import':
				$data = file_get_contents('http://'.$_SERVER['SERVER_NAME']._MARION_BASE_URL_.'index.php?mod=sincro&ctrl=Syncro');
				break;
			case 'quantities_and_prices':
				$data = 
				file_get_contents('http://'.$_SERVER['SERVER_NAME']._MARION_BASE_URL_.'index.php?mod=sincro&ctrl=Syncro&action=quantities_and_prices');
				break;
			case 'images':
				$data = 
				file_get_contents('http://'.$_SERVER['SERVER_NAME']._MARION_BASE_URL_.'index.php?mod=sincro&ctrl=Syncro&action=images');
				//debugga($_SERVER['SERVER_NAME']._MARION_BASE_URL_.'index.php?mod=sincro&ctrl=Syncro&action=quantities_and_prices');exit;

				
				break;
		}
		$data = json_decode($data);
		
		if( is_object($data) ){
			if( $data->result == 'SUCCESS' ){
				$this->displayMessage('Operazione effettuata con successo!');
			}else{
				$this->errors[] = $data->message;
			}
		}
		

		$this->getReports();
		$this->output('setting.htm');
	}
}