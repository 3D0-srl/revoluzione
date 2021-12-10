<?php
use Marion\Controllers\AdminModuleController;
use Mailman\Mailman;
class MailmanAdminController extends AdminModuleController
{
	public $_auth = 'cms';

	function setMedia()
	{
		parent::setMedia();
	}

	function displayForm()
	{
		$this->setMenu('mm_new');
		$action = $this->getAction();

		if ($this->isSubmitted()) {

			$dati = $this->getFormdata();
			$array = $this->checkDataForm('mailman_list', $dati);

			if ($array[0] == 'ok') {
				if ($action == 'edit') {
					$obj = Mailman::withId($array['id']);
				} else {
					$obj = Mailman::create();
				}

				$obj->set($array);
				$res = $obj->save();

				if (is_object($res)) {
					if ($action == 'edit') {
						$this->redirectToList(array('updated' => $this->getId()));
					} else {

						$this->redirectToList(array('created' => $this->getId()));
					}
				} else {
					$this->errors[] = $res;
				}
			} else {
				$this->errors[] = $array[1];
			}
		} else {
			if ($action == 'edit') {
				$id = $this->getId();
				$obj = Mailman::withId($id);
				if (is_object($obj)) {
					$dati = $obj->prepareForm2();
				}
			}
		}

		$dataform = $this->getDataForm('mailman_list', $dati);

		$this->setVar('dataform', $dataform);
		$this->output('mm_add.htm');
	}

	function displayList()
	{
		$this->setMenu('mm_list');
		$this->displayMessages();
		$list = Mailman::prepareQuery()->get();

		for($i = 0; $i < count($list); $i++) {
			$query = Mailman::withId($list[$i]->id);

			$list[$i]->count = $query->getCountSubscribe();
		}

		$this->setVar('list', $list);
		$this->output('mm_list.htm');
	}

	function displayMessages()
	{
		if (_var('deleted')) {
			$this->displayMessage('Lista eliminata con successo');
		}
		if (_var('created')) {
			$this->displayMessage('Lista inserita con successo');
		}
		if (_var('updated')) {
			$this->displayMessage('Lista aggiornata con successo');
		}
	}

	function delete()
	{
		$id = $this->getId();
		$obj = Mailman::withId($id);
		if (is_object($obj)) {
			$obj->delete();
		}
		$this->redirectToList(array('deleted' => $id));
	}
}
