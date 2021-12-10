<?php
require ('../../../config.inc.php');
$template = _obj('Template');

$action = _var('action');
if( $action == 'get_category_form'){
	$category = _var('category');
	$id_profile = _var('id');
	$market = _var('market');
	
	require('classes/AmazonCategory.class.php');
	require('classes/AmazonProfile.class.php');
	require('classes/AmazonTool.class.php');
	
	if( $id_profile ){
		$obj = AmazonProfile::withId($id_profile);
	}

	if( !is_object($obj) ){
		$obj = AmazonProfile::create();
		
	}
	
	$obj->setCategory($category);
	
	$obj->getDataMarket($market);
	
	$html = $obj->getForm($market);
		
	
	
	/*$attributes = Attribute::prepareQuery()->get();
	$template->attributes = $attributes;
	ob_start();
	$template->output_module(basename(__DIR__),'profile_'.$category.'.htm',$elements);
	$html = ob_get_contents();
	ob_end_clean();
	*/

	

	$risposta = array(
		'result' => 'ok',
		'html' => $html
	);
	echo json_encode($risposta);
	exit;
}elseif( $action == 'change_theme'){
	$category = _var('category');
	$theme = _var('theme');
	require('classes/AmazonCategory.class.php');
	require_once('category/'.$category.".php");
	$obj = new $category();
	

	$risposta = array(
		'result' => 'ok',
		'attributes' => $obj->getAttributesTheme($theme)
	);
	echo json_encode($risposta);
	exit;
	
}elseif( $action == 'save_profile'){
	$database = _obj('Database');
	$formdata = _formdata();
	require('classes/AmazonCategory.class.php');
	require('classes/AmazonProfile.class.php');
	require('classes/AmazonTool.class.php');

	if( !$formdata['id'] ){
		$obj = AmazonProfile::create();
		
	}else{
		$id = $formdata['id'];
		$obj = AmazonProfile::withId($id);
		
	}

	$obj->name = $formdata['name'];
	$obj->save();
	$id = $obj->id;

	if( $formdata['category'] ){
		$obj->setCategory($formdata['category']);
		
		$errore = $obj->checkForm($formdata);
		
		if( $errore){
			$risposta = array(
				'result' => 'nak',
				'error' => $errore['errore'],
				'campo' => $errore['campo'],
			);
			echo json_encode($risposta);
			exit;
		}
		unset($array[0]);
		//debugga($array);exit;
	}


	

	$dati = $database->select('*','amazon_profile_marketplace',"market='{$formdata['marketplace']}' AND id_profile={$id}");
	
	$data = array(
		'data' => serialize($formdata),
		'id_profile' => $id,
		'market' => $formdata['marketplace']
	);
	if( okArray($dati) ){
		$id2 = $dati[0]['id'];
		$database->update('amazon_profile_marketplace',"id={$id2}",$data);
	}else{
		$database->insert('amazon_profile_marketplace',$data);
	}

	$risposta = array(
			'result' => 'ok',
			'id' => $id
	);

	echo json_encode($risposta);
	exit;
}elseif( $action == 'get_profile_market'){
	$market = _var('market');
	$id = _var('id');
	$database = _obj('Database');
	$dati = $database->select('*','amazon_profile_marketplace',"market='{$market}' AND id_profile={$id}");
	if( okArray($dati) ){
		$dati = $dati[0];

		$data = unserialize($dati['data']);

		$risposta = array(
			'result' => 'ok',
			'category' => $data['category']
		);
		
	}else{
		$risposta = array(
			'result' => 'ok',
			'category' => ''
		);
	}
	echo json_encode($risposta);
	exit;


/*}elseif( $action == 'get_variation_theme'){
	
	$category = _var('category');
	switch($category){
		case 'Clothing':
			$list = array(
				'Size' => 'Taglia',
				'Color' => 'Colore',
				'SizeColor' => 'Taglia e Colore',
			);
			break;
		case 'ShoesAccessory':
			$list = array(
				'Size' => 'Taglia',
				'Color' => 'Colore',
				'SizeColor' => 'Taglia e Colore',
			);

			break;
		case 'Shoes':
			$list = array(
				//'Size' => 'Taglia',
				//'Color' => 'Colore',
				'SizeColor' => 'Taglia e Colore',
			);

			break;
		case 'ShoeAccessory':
			$list = array(
				'Size' => 'Taglia',
				'Color' => 'Colore',
				'SizeColor' => 'Taglia e Colore',
			);

			break;
		case 'Handbag':
			$list = array(
				'Size' => 'Taglia',
				'Color' => 'Colore',
				'SizeColor' => 'Taglia e Colore',
			);

			break;
		case 'Eyewear':
			$list = array(
				'Size' => 'Taglia',
				'Color' => 'Colore',
				'SizeColor' => 'Taglia e Colore',
			);

			break;

	}

	$risposta = array(
		'result' => 'ok',
		'select' => $list
	);

	
	echo json_encode($risposta);
	exit;*/
}
?>