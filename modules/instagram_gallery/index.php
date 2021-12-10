<?php


require ('../../../include.inc.php');

$template = _obj('Template');

$database = _obj('Database');

$action = _var('action');

if( $action == 'gallery'){
	Marion::setMenu('instagram_gallery');
	require_once('classes/Instagram.class.php');
	
	

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
	$list = InstagramImage::prepareQuery()->orderBy('created_time','DESC')->limit($limit)->offset($offset)->get();
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
	get_form($elements,'instagram_gallery_conf',$action."_ok",$dati);
	$template->output_module(basename(__DIR__),'gallery_setting.htm',$elements);
}elseif( $action == 'change_status'){
	$id = _var('id');
	$res = $database->select('*','instagram_image',"id=" . $id);
	if( okArray($res) ){
		$status=!$res[0]['visibility'];
		
		$database->update('instagram_image',"id=" . $id,array('visibility' => $status));

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
	$res = $database->select('*','instagram_image_tag',"pic_id=" . $_POST[ 'pic_id' ]);
	
	$data['boxes'] = '';
	$data['lists'] = '';
	foreach($res as $rs){
		if(!$rs[ 'name' ]){
			if( $rs['id_product'] ){
				$product = Product::withId($rs['id_product']);
				if( is_object($product) ){
					$rs['name'] = $product->get('name');

				}
			}
		}

		$data['boxes'] .= '<div class="tagview" style="left:' . $rs['pic_x'] . 'px;top:' . $rs['pic_y'] . 'px;" id="view_'.$rs['id'].'">';
		$data['boxes'] .= '<div class="square"></div>';
		$data['boxes'] .= '<div class="person" style="left:' . $rs['pic_x'] . 'px;top:' . $rs['pic_y']  . 'px;">' . $rs[ 'name' ] . '</div>';
		$data['boxes'] .= '</div>';
		if( $rs['id_product'] ){
			$product = Product::withId($rs['id_product']);
			if( is_object($product) ){
				$rs['name'] .= " - <b><span>".$product->get('name')."</span></b> "; 
			}
		}
		$data['lists'] .= '<li id="'.$rs['id'].'"><div style="width:20px; display:inline-block; border: 1px solid #333; border-radius:45px;background-color:'.$rs['color'].'">&nbsp;</div> <a>' . $rs['name'] . '</a> <a class="remove"><i class="fa fa-trash-o"></i></a></li>';
	}
	
	echo json_encode( $data );
	exit;
}elseif( $action == 'tag'){
	
	if( !empty( $_POST['type'] ) && $_POST['type'] == "insert" )
	{
	  $id = _var('pic_id');  
	  $name = trim(_var('name'));
	  $id_product = _var('id_product');
		
	 

	  $pic_x = _var('pic_x');
	  $pic_y = _var('pic_y');
	  $color = _var('color');
	 
	  if( !$id_product ) $id_product = 0;
	  $sql = "INSERT INTO instagram_image_tag (pic_id,name,pic_x,pic_y,id_product,color) VALUES ( $id, '$name', $pic_x, $pic_y,$id_product,'$color')";
	
	  $database->execute($sql);

	 
	}

	if( !empty( $_POST['type'] ) && $_POST['type'] == "remove")
	{
	  $tag_id = $_POST['tag_id'];
	  $sql = "DELETE FROM instagram_image_tag WHERE id = '".$tag_id."'";
	  $database->execute($sql);
	}
	

}elseif( $action == 'mod'){
	Marion::setMenu('instagram_gallery');
	$id = _var('id');
	require_once('classes/Instagram.class.php');
	$obj = InstagramImage::withId($id);
	$template->image = $obj;
	$template->output_module(basename(__DIR__),'mod_image.htm',$elements);
}elseif( $action == 'get_images'){
	$dati = Marion::getConfig('instagram_gallery');
	require_once('classes/InstagramApi.php');
	require_once('classes/Instagram.class.php');
	$obj = new InstagramApi($dati);
	$tags = explode(PHP_EOL,$dati['tags']);
	foreach($tags as $k => $v){
		$tags[$k] = trim($v);
	}
	$images = $obj->getImagesFromTags($tags);

	
	foreach($images as $v1){
		$o = InstagramImage::create();
		$o->set($v1);
		$o->save();

		
		
	}
	
	
	$template->link = "/admin/modules/instagram_gallery/index.php?action=gallery";
	$template->output('continua.htm');
}elseif( $action == 'setting_gallery_ok'){
	Marion::setMenu('instagram_gallery');
	$formdata =_var('formdata');
	$array = check_form($formdata,'instagram_gallery_conf');
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
		$url = "http://instagram.com/oauth/authorize/?client_id={$client_id}&redirect_uri={$url_redirect}&response_type=token&scope=basic+comments+follower_list+likes+relationships+public_content";
		header('Location:'.$url);
		
	}
	
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
}elseif( $action == 'reset_token'){
	Marion::delConfig('instagram_gallery','access_token');
	Marion::refresh_config();
	$template->link = "/admin/modules/instagram_gallery/index.php?action=setting";
	$template->output('continua.htm');
}else{
	if( $action ) exit;
	if( !_var('access_token') ){

		echo "
		<html>
		<head>
		<script>
	 
		var query = location.href.split('#');
		
		var url = query[0] +'?'+query[1];
		
		
		location.href = url;
		
		setTimeout(function () {
			location.reload()
		}, 1000);
		</script>
		</head>
		<body></body>
		</html>";
	
	}else{
 
		$token = _var('access_token');
		
		Marion::setConfig('instagram_gallery','access_token',$token);
		Marion::refresh_config();
		$template->link = "/admin/modules/instagram_gallery/index.php?action=setting";
		$template->output('continua.htm');
	}
}



?>