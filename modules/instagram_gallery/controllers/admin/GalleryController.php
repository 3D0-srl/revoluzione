<?php
class GalleryController extends AdminModuleController{
	public $_auth = 'cms';
	public $_twig = true;
	


	function setMedia(){
		$action = $this->getAction();
		if( $action == 'edit'){
			$this->registerJS('/modules/instagram_gallery/js/edit_image.js','end');
			$this->registerCSS('/modules/instagram_gallery/css/tagging.css','end');
		}
		if( $action == 'list'){
			$this->registerJS('/modules/instagram_gallery/js/script.js','end');
			
		}
	}


	function displayContent(){
		$action = $this->getAction();
		switch($action){
			case 'update':
				$this->update();
				break;
			case 'setting':
				$this->setting();
				break;
		}
	}


	
	function setting(){
		$this->setMenu('instagram_gallery');

	

		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('instagram_gallery_conf',$dati);
			if( $array[0] == 'ok'){

				unset($array[0]);
				foreach($array as $k => $v){
					Marion::setConfig('instagram_gallery',$k,$v);
				}
				Marion::refresh_config();
				$this->displayMessage('Dati salvati con successo!');

			}else{
				$this->errors = $array[1];
			}
		}else{
			$dati = Marion::getConfig('instagram_gallery');
		}

		$dataform = $this->getDataForm('instagram_gallery_conf',$dati);
		$this->setVar('dataform',$dataform);

		
		
		
		$this->output('setting_gallery.htm');
	}

	function update(){

		$access_token = Marion::getConfig('instagram_gallery','access_token');
		$dati = Marion::getConfig('instagram_gallery');
		$images = json_decode(file_get_contents("https://graph.instagram.com/me/media?fields=id,caption,media_type,media_url,username,permalink,thumbnail_url,timestamp,comment_count&access_token={$access_token}"))->data;
		$tags = explode(PHP_EOL,$dati['tags']);
		foreach($tags as $k => $v){
			$tags[$k] = trim($v);
		}
		$candidati = [];
		foreach($images as $image) {
			foreach($tags as $tag) {
				$pattern = "/#{$tag}/";
				$match = preg_match($pattern, $image->caption);
				if( $match ){
					$candidati[] = $image;
					break;
				}
			}
		}
		
		
		foreach($candidati as $candidato){

			$dt = new DateTime($candidato->timestamp);

			$converted = [
				'id_instagram' => $candidato->id,
				'tags' => '',
				'link' => $candidato->permalink,
				'created_time' => $dt->format('Y-m-d'),
				'text' => $candidato->caption,
				'url_image' => $candidato->media_url,
				'visibility' => 1,
				'num_likes' => 0,
				'num_comments' => 0,
				'last_update' => $dt->format('Y-m-d h:m:s'),
			];
	
			$o = InstagramImage::create();
			$o->set($converted);
			$o->save();	
			
		}
		
		$this->redirectToList(array('updated'=>1));
	}


	function displayForm(){
		$this->setMenu('instagram_gallery');
		$id = _var('id');
		$obj = InstagramImage::withId($id);
		$this->setVar('image',$obj);

		$this->output('edit_image.htm');
	}

	function displayList(){
		

		$this->setMenu('instagram_gallery');
		if( _var('updated') ){
			$this->displayMessage('Dati aggiornati con successo!');
		}
		if( _var('deleted') ){
			$this->displayMessage('Immagine eliminata con successo!');
		}

		$params = array(
			'mode' => 'Jumping',
			'append' => 1,
			'urlVar' => 'pageID',
			'perPage' => 10,
			'httpMethod' => 'GET',
			'formID' => '',
			'useSessions' => 1,
			'sessionVar' => '',
			'fileName' =>'',
		);
		
		$next = _var('pageID');
		
		//limite sulla select dei prodotti
		$limit = $params['perPage'];
		if( $next ){
			$offset = ($next-1)*$params['perPage'];
		}
		$query = InstagramImage::prepareQuery()->orderBy('created_time','DESC')->limit($limit)->offset($offset);
		
		
		$list = $query->get();
		$database = _obj('Database');
		
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

		$this->setVar('list',$data);
		$this->setVar('links',$links);
		
		

		$this->output('list_gallery.htm');
		
		
	}


	function ajax(){
		

		$id = _var('id');
		$database = _obj('Database');
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

	}



	function delete(){
		
		$id = $this->getID();
		$obj = InstagramImage::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));
		
	}


	//FORM
	function listDateFormats(){
		

		return array(
			'%d/%m/%Y' => "dd/mm/yyyy",
			'%d-%m-%Y' => "dd-mm-yyyy",
			'%Y-%m-%d' => "yyyy-mm-dd",
		);
	}



	function widgetLayouts(){
		
		return array(

			0 => 'standard',
			1 => 'con immagine centrale',
		);
	}


}



?>