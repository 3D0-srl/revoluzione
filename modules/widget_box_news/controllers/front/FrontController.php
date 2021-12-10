<?php
use Marion\Controllers\FrontendController;
use News\{News,NewsType};
class FrontController extends FrontendController
{

	function display()
	{

		$action = $this->getAction();

		//debugga($parameters['box_id']);exit;
		//debugga($action);exit;

		switch ($action) {
			case 'get_news':
				$id_type = _var('id');
				if ($id_type) {
					if ($id_type == -1) {
						$news = News::prepareQuery()->orderBy('date', 'desc')->get();
						$result = NewsType::prepareQuery()->get();

						foreach($result as $row) {
						$id = $row->id;
						$name = $row->get('name');

						$array[$id] = $name;
						}
						$this->setVar('news', $news);
						$this->setVar('id_type', $id_type);
						$this->setVar('categories', $array);
					} else {
						$news = News::prepareQuery()->where('type_news', $id_type)->orderBy('date', 'desc')->get();
						$category_name = NewsType::prepareQuery()->where('id', $id_type)->get()[0]->_localeData['it']['name'];
						$this->setVar('news', $news);
						$this->setVar('category_name', $category_name);
					}
					$this->setVar('box_id', $_GET['box_id']);
					ob_start();
					$this->output('box.htm');
					$html = ob_get_contents();
					ob_end_clean();
					$risposta = array(
						'result' => 'ok',
						'data' => $html
					);
				} else {
					$risposta = array(
						'result' => 'nak',
						'error' => 'missing id type'
					);
				}
				//$news = News::prepareQuery()->where('type_news',$id_type)->orderBy('date','desc')->get();
				echo json_encode($risposta);
				exit;
		}

	}







	
}
