<?php


require ('../../../config.inc.php');

$template = _obj('Template');

$database = _obj('Database');

$action = _var('action');
require_once('classes/ImageMap.class.php');
if( $action == 'gallery'){
	Marion::setMenu('slider_tag');
	
	
	
	$params = array(
		'mode' => 'Jumping',
		'append' => 1,
		'urlVar' => 'pageID',
		'perPage' => 10,
		'httpMethod' => 'GET',
		'formID' => '',
		'useSessions' => 1,
		'sessionVar' => '',
		'path' => '/admin/modules/instagram_gallery',
		'fileName' =>'',
	);
	
	$next = _var('pageID');
	
	//limite sulla select dei prodotti
	$limit = $params['perPage'];
	if( $next ){
		$offset = ($next-1)*$params['perPage'];
	}


	$list = ImageMap::prepareQuery()->orderBy('created_time','DESC')->limit($limit)->offset($offset)->get();

	
	$tot = $database->select('count(*) as tot','instagram_image','1=1');
	if( okArray($tot) ){
		$tot = $tot[0]['tot'];
	}
	if( $tot ){
	   $params['totalItems'] = $tot;
	}else{
	   $params['itemData'] = $list;
	}

	
	require_once 'Pager.php';
	
	$pager = &Pager::factory($params);
	
	if( $tot ){
		$data = $list;
	}else{
		$data = $pager->getPageData();
	}

	
	//prendo i link del pager
	$links = $pager->getLinks();

	$template->list = $data;
	$template->links = $links;

	//debugga($list);exit;
	
	$template->output_module(basename(__DIR__),'list_gallery.htm',$elements);
}elseif( $action == 'setting_gallery'){
	Marion::setMenu('instagram_gallery');
	$dati = Marion::getConfig('instagram_gallery');
	get_form($elements,'instagram_gallery_tag',$action."_ok",$dati);
	$template->output_module(basename(__DIR__),'gallery_setting.htm',$elements);
}elseif( $action == 'change_status'){
	$id = _var('id');
	$res = $database->select('*','image_map',"id=" . $id);
	if( okArray($res) ){
		$status=!$res[0]['visibility'];
		
		$database->update('image_map',"id=" . $id,array('visibility' => $status));

		if( $status ){
			$html = "<span class='label label-success' style='cursor:pointer' onclick='change_status_image_instagram({$id}); return false;'>ONLINE</span>";
		}else{
			$html = "<span class='label label-danger' style='cursor:pointer' onclick='change_status_image_instagram({$id}); return false;'>OFFLINE</span>";
		}
		$risposta = array(
			'result' => 'ok',
			'html' => $html
		);
	}else{
		$risposta = array(
			'result' => 'nak'
		);
	}
	echo json_encode($risposta);
	exit;
}elseif( $action == 'taglist'){
	// fetch all tags
	$res = $database->select('*','image_map_tag',"pic_id=" . $_POST[ 'pic_id' ]);
	
	$data['boxes'] = '';
	$data['lists'] = '';
	
	foreach($res as $rs){
		$rs['pic_x'] += 20;
		$data['boxes'] .= '<div class="tagview" style="left:' . $rs['pic_x'] . 'px;top:' . $rs['pic_y'] . 'px;" id="view_'.$rs['id'].'">';
		$data['boxes'] .= '<div class="square" style="height:20px;"></div>';
		$data['boxes'] .= '<div class="person" style="left:' . $rs['pic_x'] . 'px;top:' . $rs['pic_y']  . 'px;"><img src="' . $rs['icon'] . '"></div>';
		$data['boxes'] .= '</div>';
		
		$data['lists'] .= '<li id="'.$rs['id'].'"><div class="icon_div"><img src="'.$rs['icon'].'"></div> <a>' . $rs['name'] . '</a> <a class="remove"><i class="fa fa-trash-o"></i></a></li>';
	}
	
	echo json_encode( $data );
	exit;
}elseif( $action == 'tag'){
	if( !empty( $_POST['type'] ) && $_POST['type'] == "insert" )
	{
	  $id = _var('pic_id');  
	  
	  $formdata = _formdata();
	 

	  $name = trim($formdata['tag']);
	  $id_product = $formdata['product'];
		
	 

	  $pic_x = _var('pic_x');
	  $pic_y = _var('pic_y');
	  $color = _var('color');
	  $height = _var('height');
	  $width = _var('width');
	  $icon = _var('icon');
	  if( !$color ) $color = '';
	  if( !$id_product ) $id_product = 0;

	

	  $toinsert = $formdata;
	  
	  $toinsert['pic_id'] = $id;
	  $toinsert['pic_x'] = $pic_x;
	  $toinsert['pic_y'] = $pic_y;
	  $toinsert['height'] = $height;
	  $toinsert['width'] = $width;
	  $toinsert['icon'] = $icon;
	 

	  $database->insert('image_map_tag',$toinsert);
	  //debugga($database->error);exit;

	  //$sql = "INSERT INTO image_map_tag (pic_id,name,pic_x,pic_y,width,height,icon,associazioneurl,product) VALUES ( $id, '$name', $pic_x, $pic_y,$width,$height,'$icon')";
	
	  //$database->execute($sql);
	
	}

	if( !empty( $_POST['type'] ) && $_POST['type'] == "remove")
	{
	  $tag_id = $_POST['tag_id'];
	  $sql = "DELETE FROM image_map_tag WHERE id = '".$tag_id."'";
	  $database->execute($sql);
	}
}elseif( $action == 'preview'){	
	
	$id = _var('id');
	
	$obj = ImageMap::withId($id);
	$template->images[] =$obj->getDivWithTags();
	$template->output_module(basename(__DIR__),'preview_image2.htm',$elements);
}elseif( $action == 'slider'){	
	
	
	
	$list = ImageMap::prepareQuery()->get();
	
	foreach($list as $v){
		$template->images[] =$v->getDivWithTags();
	}
	
	$template->output_module(basename(__DIR__),'preview_image2.htm');
}elseif( $action == 'mod'){
	Marion::setMenu('slider_tag');
	$id = _var('id');
	

	$icons = scandir('icons');
	foreach($icons as $v){
		if( !in_array($v,array('.','..') )) {
			$list_icon[] = 'icons/'.$v;
		}
	}
	$template->icons = json_encode($list_icon);

	
	$obj = ImageMap::withId($id);
	$template->image = $obj;
	$template->output_module(basename(__DIR__),'mod_image.htm',$elements);
}elseif( $action == 'get_images'){
	$dati = Marion::getConfig('instagram_gallery');
	require_once('classes/InstagramApi.php');
	require_once('classes/Instagram.class.php');
	$obj = new InstagramApi($dati['access_token']);
	$tags = explode(PHP_EOL,$dati['tags']);
	foreach($tags as $v){
		$images = $obj->getImagesFromTag(trim($v));

		
		foreach($images as $v1){
			$o = InstagramImage::create();
			$o->set($v1);
			$o->save();
			
		}
	}
	
	$template->link = "/admin/modules/instagram_gallery/index.php?action=gallery";
	$template->output('continua.htm');
}elseif( $action == 'setting_gallery_ok'){
	Marion::setMenu('instagram_gallery');
	$formdata =_var('formdata');
	$array = check_form($formdata,'instagram_gallery_tag');
	if( $array[0] == 'ok'){
		unset($array[0]);
		foreach($array as $k => $v){
			Marion::setConfig('instagram_gallery',$k,$v);
		}
		Marion::refresh_config();
		$template->link = "/admin/modules/instagram_gallery/index.php?action=gallery";
		$template->output('continua.htm');
		
	}else{
		$template->errore = $array[1];

		get_form($elements,'instagram_gallery_tag',$action,$array);
		$template->output_module(basename(__DIR__),'gallery_setting.htm',$elements);
	}


	
}elseif( $action == 'setting_ok'){
	$formdata = _var('formdata');

	$array = check_form($formdata,'instagram_gallery_setting');
	$url_redirect = "http://".Marion::getConfig('generale','baseurl').'/modules/instagram_gallery/index.php';
	
	if( $array[0] == 'ok'){
		unset($array[0]);
		foreach($array as $k => $v){
			Marion::setConfig('instagram_gallery',$k,$v);
		}
		Marion::refresh_config();
		$client_id = trim($array['client_id']);
		$url = "http://instagram.com/oauth/authorize/?client_id={$client_id}&redirect_uri={$url_redirect}&response_type=token";
		header('Location:'.$url);
		
	}
}elseif( $action == 'add_image' || $action == 'mod_image'){
	if( $action == 'mod_image' ){
		$id = _var('id');
		$obj = ImageMap::withId($id);
		$dati = $obj->prepareForm();
	}
	Marion::setMenu('slider_tag');
	get_form($elements,'slider_tag_image',$action."_ok",$dati);
	$template->output_module(basename(__DIR__),'form_image.htm',$elements);
}elseif( $action == 'add_image_ok' || $action == 'mod_image_ok'){
	Marion::setMenu('slider_tag');
	
	$formdata = _var('formdata');
	
	$array = check_form($formdata,'slider_tag_image');
	
	if( $array[0] == 'ok'){
		if( $action == 'add_image_ok'){
			$obj = ImageMap::create();
		}else{
			$obj = ImageMap::withId($array['id']);
		}
		//debugga($obj);exit;
		$obj->set($array);
		$obj->save();
		$template->link = "/admin/modules/slider_tag/index.php?action=gallery";
		$template->output('continua.htm');
		
	}else{
		get_form($elements,'slider_tag_image',$action,$array);
		$template->output_module(basename(__DIR__),'form_image.htm',$elements);
	}
}elseif( $action == 'del_image'){
	Marion::setMenu('slider_tag');
	$id = _var('id');
	$obj = ImageMap::withId($id);
	$obj->delete();
	$template->link = "/admin/modules/slider_tag/index.php?action=gallery";
	$template->output('continua.htm');
}elseif( $action == 'setting'){

	
	Marion::setMenu('setting_social_login');
	
	$template->url_redirect = "http://".Marion::getConfig('generale','baseurl').'/modules/instagram_gallery/index.php';

	$dati = Marion::getConfig('instagram_gallery');
	
	if( $dati['access_token'] ){
		$template->access_token = $dati['access_token'];
	}
	get_form($elements,'instagram_gallery_setting','setting_ok',$dati);
	$template->output_module(basename(__DIR__),'setting.htm',$elements);
}elseif( $action == 'show'){
	require_once('classes/Instagram.class.php');
	
	$list = InstagramImage::prepareQuery()->where('visibility',1)->orderBy('created_time','DESC')->limit(10)->get();
	
	$template->list = $list;
	$template->output_module(basename(__DIR__),'gallery.htm',$elements);
}elseif( $action == 'load_ajax'){
	$limit = 5;
	

	$type = _var('type');


	$offset =_var('offset');
	require_once('classes/Instagram.class.php');
	$list = InstagramImage::prepareQuery()->where('visibility',1)->orderBy('created_time','DESC')->limit($limit)->offset($offset)->get();
	foreach($list as $k => $v){
		$v->ordine = $offset+$k;
	}
	$template->list = $list;
	
	if( $type ){
		ob_start();
		$template->output_module(basename(__DIR__),'other_feed_slider.htm');
		$html2 = ob_get_contents();
		ob_end_clean();
		
	}else{
	
	

		ob_start();
		$template->output_module(basename(__DIR__),'other_feed.htm');
		$html = ob_get_contents();
		ob_end_clean();
	}
	
	

	
	
	
	
	
	$risposta = array(
		'result' => 'ok',
		'offset' => $offset+$limit,
		'html' => $html,
		'html2' => $html2,
	);
	if( count($list) < $limit ){
		$risposta['last'] = 1;	
	}

	echo json_encode($risposta);
	exit;

}



?>