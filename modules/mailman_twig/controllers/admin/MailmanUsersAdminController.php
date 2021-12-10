<?php

use Mailman\Mailman;
use Mailman\MailmanSubscriber;
use Marion\Controllers\AdminModuleController;

class MailmanUsersAdminController extends AdminModuleController
{
	public $_auth = 'cms';

	/*function setMedia()
	{
		parent::setMedia();
	}*/

	function displayForm()
	{
		$this->setMenu('mm_list');
		$action = $this->getAction();

		if ($this->isSubmitted()) {

			$dati = $this->getFormdata();
			$array = $this->checkDataForm('mailman_admin_action', $dati);
			
			if ($array[0] == 'ok') {
				
				
				$obj = Mailman::withId($array['id_list']);
				
				$obj->subscribe_admin($array['email']);
				$res = true;
				

				if ($res) {
					
					$this->redirectToList(array('created' => 1, 'list' => $array['id_list']));
					
				} else {
					$this->errors[] = $res;
				}
			} else {
				$this->errors[] = $array[1];
			}
		} else {
		
			if( _var('list') ){
				$dati['id_list'] = _var('list');
			}
		}

		$dataform = $this->getDataForm('mailman_admin_action', $dati);

		$this->setVar('id_list', _var('list'));
		$this->setVar('dataform', $dataform);
		$this->output('users_add.htm');
	}

	function displayList()
	{
		$this->setMenu('mm_list');
        $this->displayMessages();
        $id_list = _var('list');
		$list = Mailman::withId($id_list)->getSubscribers();

        $this->setVar('id_list', $id_list);
		$this->setVar('list', $list);
		$this->output('users_list.htm');
	}

	function displayMessages()
	{
		if (_var('deleted')) {
			$this->displayMessage('Email eliminata con successo');
		}
		if (_var('created')) {
			$this->displayMessage('Email inserita con successo');
		}
		if (_var('updated')) {
			$this->displayMessage('Email aggiornata con successo');
		}
	}

	function delete()
	{
		
		$id = $this->getId();
		$obj = MailmanSubscriber::withId($id);
		
		if (is_object($obj)) {
			$list = Mailman::withId($obj->list);
			
			if( is_object($list) ){
				$list->unsubscribe($obj->email);
			}
			
			//unsubscribe
			$obj->delete();
		}
		$this->redirectToList(array('deleted' => 1,'list'=>$obj->list));
	}
	


	function displayContent(){
		$action = $this->getAction();
		switch($action){
			case 'mass':

				$this->mass();
				break;
			case 'export':

				$this->export();
				break;
		}
	}

	function mass()
    {

        $this->setMenu('mm_list');

        /*
            quando il form viene sottomesso
        */

        if ($this->isSubmitted()) {

			$dati = $this->getFormdata();
			$array = $this->checkDataForm('mailman_admin_mass_action', $dati);
			
			if( $array[0] == 'nak'){
				$this->errors[] = $array[1];
			}else{
				$action = $array['azione'];
				$emails = $array['emails'];

				$mails_array = explode("\n", $emails);

				$obj = Mailman::withId($array['id_list']);

				if ($action == 'add') {
					foreach ($mails_array as $mail) {
						$mail = trim($mail);
						$obj->subscribe_admin($mail);
					}
				} else {
					foreach ($mails_array as $mail) {
						$mail = trim($mail);
						$obj->unsubscribe_admin($mail);
					}
				}

				
				$this->displayMessage('Azione eseguita con successo!');
			}
        }else{
			
			$dati['id_list'] = _var('list');
			$dati['azione'] = 'add';
		}

		$this->setVar('id_list', $dati['id_list']);
		$dataform = $this->getDataForm('mailman_admin_mass_action', $dati);
		$this->setVar('dataform', $dataform);
		
		
        
        $this->output('user_mass_action.htm');
    }

	function export()
    {
        $list = _var('list');
        $list = Mailman::withId($list);
        $iscritti = $list->getSubscribers();
		$this->setVar('righe',$iscritti);
        header("Cache-Control: ");
        header("Pragma: ");
        header("Accept-Ranges: bytes");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Language: eng-US");
        header("Content-Disposition: attachment; filename=\"" . date('Y-m-d') . ".xls\"");
        header("Content-Transfer-Encoding: binary");

        header("Content-Encoding: ");

        ob_start(); //"ob_gzhandler");
       
        $this->output('export_list.htm');
        $size = ob_get_length();
		$now = time();
        $diff = date('Z', $now);
        $gmt_mtime = date('D, d M Y H:i:s', $now - $diff) . ' GMT';

        header("Last-Modified: " . $gmt_mtime);
        header("Expires: " . $gmt_mtime);

        header("Content-Length: $size");
        sleep(1);

        ob_end_flush();
    }

	
	function massActionsOptions(){
		
		return array(
			'add' => 'Aggiungi',
			'remove' => 'Rimuovi',
		);
	}
}
