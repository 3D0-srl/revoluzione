<?php
require ('../../../../../config.inc.php');
$database = _obj('Database');
$template = _obj('Template');
require_once ('../../classes/WidgetInterface.php');
require_once ('../../classes/WidgetBase.class.php');
require_once ('../../classes/MailWidget.class.php');

require_once ('WidgetVetrina.php');
$action = _var('action');

if( $action == 'add_widget_vetrina_prodotti' || $action == 'mod_widget_vetrina_prodotti'){

	
	Marion::setMenu('widgets');
	if( $action == 'mod_widget_vetrina_prodotti' ){
		
		$id = _var('id');
		$obj = WidgetVetrina::withId($id);
		
		
		$dati = $obj->prepareForm();
		$dati = array_merge($dati,$dati['conf']);
		$obj->getComposition();
		
		foreach($obj->items as $v){
			$obj2 = Product::withId($v);
	
			if( is_object($obj2) ){
				$list[] = array(
					'id' => $obj2->id,
					'name' => $obj2->getName(),
					'img' => $obj2->getUrlImage(0,'original'),
				);
			}

		}

		$template->preview = $obj->getContent();
		$template->list = $list;
	}
	get_form($elements,'mail_marketing_vetrina_prodotti',$action."_ok",$dati);

	
	
	$template->output_module('mail_marketing/widgets/WidgetVetrina','form_vetrina_prodotti.htm',$elements);
	exit;
}elseif( $action == 'add_widget_vetrina_prodotti_ok' || $action == 'mod_widget_vetrina_prodotti_ok'){
	Marion::setMenu('widgets');
	$formdata = _var('formdata');
	
	$array = check_form($formdata,'mail_marketing_vetrina_prodotti');


	if( $array[0] == 'ok' ){	
		if( count($formdata['items']) != $array['view'] ){
			$array[0] = 'nak';
			$array[1] = 'Devi specificare '.$array['view']." prodotto/i";
		}
	
	}
	if( $array[0] == 'ok' ){	
		unset($array[0]);
		
		if( $action == 'mod_widget_vetrina_prodotti_ok' ){
			$obj = WidgetVetrina::withId($array['id']);
		}else{
			$obj = WidgetVetrina::create();
		}
		$obj->type='product';
		$obj->setComposition($formdata['items']);
		$obj->set($array);

		
		
		$obj->setConf(
			array(
				'show_prices' => $array['show_prices'],
				'show_name' => $array['show_name'],
				'show_label' => $array['show_label']
			)	
		);
		
		$obj->save();
		
		$template->link = "/admin/modules/mail_marketing/controller.php?action=widgets";
		$template->output('continua.htm');
	}else{


		foreach($formdata['items'] as $v){
			$obj = Product::withId($v);

			if( is_object($obj) ){
				$list[] = array(
					'id' => $obj->id,
					'name' => $obj->getName(),
					'img' => $obj->getUrlImage(0,'original'),
				);
			}

		}
		$template->list = $list;

		$template->errore = $array[1];
		get_form($elements,'mail_marketing_vetrina_prodotti',$action,$array);
		$template->output_module('mail_marketing/widgets/WidgetVetrina','form_vetrina_prodotti.htm',$elements);

	}
} elseif ( $action == 'get_product'){
	
	$name = _var('name');
	$query = Product::prepareQuery()
			->whereExpression("(name like '%{$name}%' OR sku like '%{$name}%')")
			->where('parent',0)
			->where('deleted',0);

	
	$prodotti = $query->get();
	$toreturn = array();
	
	if( okArray($prodotti) ){
		foreach($prodotti as $k => $v){
			$item = array(
				'name' => $v->get('name'),
				'id' => $v->id,
				'img' => $v->getUrlImage(0,'small')
			);
			$toreturn[] = $item;
		}
	}
	$risposta = array(
		'result' => 'ok',
		'data' => $toreturn
	);
	echo json_encode($risposta);
}elseif( $action == 'add_widget_vetrina_news' || $action == 'mod_widget_vetrina_news'){

	
	Marion::setMenu('widgets');
	if( $action == 'mod_widget_vetrina_news' ){
		
		$id = _var('id');
		$obj = WidgetVetrina::withId($id);
		$dati = $obj->prepareForm();
		
		$obj->getComposition();
		
		foreach($obj->items as $v){
			$obj2 = News::withId($v['id_object']);

			if( is_object($obj2) ){
				$list[] = array(
					'id' => $obj2->id,
					'name' => $obj2->get('title'),
					'img' => $obj2->getUrlImage(0,'original'),
				);
			}

		}
		$template->preview = $obj->getContent();
		$template->list = $list;
	}
	get_form($elements,'mail_marketing_vetrina_news',$action."_ok",$dati);
	
	$template->output_module('mail_marketing/widgets/WidgetVetrina','form_vetrina_news.htm',$elements);
	exit;
}elseif( $action == 'add_widget_vetrina_news_ok' || $action == 'mod_widget_vetrina_news_ok'){
	Marion::setMenu('widgets');
	$formdata = _var('formdata');
	
	$array = check_form($formdata,'mail_marketing_vetrina_news');
	

	if( $array[0] == 'ok' ){	
		if( count($formdata['items']) != $array['view'] ){
			$array[0] = 'nak';
			$array[1] = 'Devi specificare '.$array['view']." news";
		}
	
	}

	if( $array[0] == 'ok' ){	
		unset($array[0]);
		
		if( $action == 'mod_widget_vetrina_news_ok' ){
			$obj = WidgetVetrina::withId($array['id']);
		}else{
			$obj = WidgetVetrina::create();
		}
		$obj->type='news';
		$obj->setComposition($formdata['items']);
		$obj->set($array);
		
		
		$obj->setConf(
			array(
				'show_title' => $array['show_title'],
				'show_pulsante' => $array['show_pulsante'],
				'show_description' => $array['show_description']
			)	
		);

		
		
		
		$obj->save();

		
		
		$template->link = "/admin/modules/mail_marketing/controller.php?action=widgets";
		$template->output('continua.htm');
	}else{


		foreach($formdata['items'] as $v){
			$obj = News::withId($v);

			if( is_object($obj) ){
				$list[] = array(
					'id' => $obj->id,
					'name' => $obj->get('title'),
					'img' => $obj->getUrlImage(0,'original'),
				);
			}

		}
		$template->list = $list;

		$template->errore = $array[1];
		get_form($elements,'mail_marketing_vetrina_news',$action,$array);
		$template->output_module('mail_marketing/widgets/WidgetVetrina','form_vetrina_news.htm',$elements);

	}
} elseif ( $action == 'preview'){
	$type = _var('type');
	//$id = _var('id');
	if( $id ){
		$obj = WidgetVetrina::withId($id);
	}else{
		$formdata = _formdata();
		
		$obj = WidgetVetrina::create();
		$obj->set($formdata);
		$obj->type = $type;
		$obj->conf['show_prices'] = $formdata['show_prices'];
		$obj->conf['show_title'] = $formdata['show_title'];
		$obj->conf['show_pulsante'] = $formdata['show_pulsante'];
		$obj->conf['show_name'] = $formdata['show_name'];
		$obj->conf['show_label'] = $formdata['show_label'];
		$obj->conf['show_description'] = $formdata['show_description'];
		
		$obj->items = array_values($formdata['items']);
	}

	
	$html = $obj->getContent();
	
	$risposta = array(
		'result' => 'ok',
		'html' => $html
	);
	echo json_encode($risposta);
	exit;
} elseif ( $action == 'get_news'){
	
	$name = _var('name');
	$query = News::prepareQuery()
			->whereExpression("(title like '%{$name}%')");
			
	
	$prodotti = $query->get();
	$toreturn = array();
	
	if( okArray($prodotti) ){
		foreach($prodotti as $k => $v){
			$item = array(
				'name' => $v->get('title'),
				'id' => $v->id,
				'img' => $v->getUrlImage(0,'small')
			);
			$toreturn[] = $item;
		}
	}
	$risposta = array(
		'result' => 'ok',
		'data' => $toreturn
	);
	echo json_encode($risposta);
}

function array_vetrina_prodotti_layout(){
	return $array = array(
		'1' => '1 prodotto',
		'2' => '2 prodotti',
		'3' => '3 prodotti',
		'4' => '4 prodotti',
	);
}

?>