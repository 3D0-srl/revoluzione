<?php
class IndexController extends FrontendController{
	

	function setMedia(){
		parent::setMedia();
		$action = $this->getAction();
		if( $action == 'show'){
			$options = Marion::getConfig('instagram_gallery');
			$this->registerCSS('modules/instagram_gallery/css/frontend.css');
			
				
			$this->loadJS('bxslider');
			$this->registerJS('modules/instagram_gallery/js/frontend.js','end');
			
		}

	}

	function display(){
			$action = _var('action');
			switch($action){
				case 'show':
					$this->showGallery();
					break;
				case 'cron':
					$this->cron();
					break;
				default:
					$this->getToken();
					break;
			}
	}


	function showGallery(){

		require_once('modules/instagram_gallery/classes/Instagram.class.php');
	
		$list = InstagramImage::prepareQuery()->where('visibility',1)->orderBy('created_time','DESC')->limit(10)->get();
		

		$options = Marion::getConfig('instagram_gallery');

		if( $options['date_format'] ){
			$date_format = $options['date_format'];
			foreach($list as $v){
				$v->created_time = strftime($date_format,strtotime($v->created_time));
				
			}
		}
		$this->setVar('show_data',$options['show_info_images']);
		$this->setVar('show_slider',$options['show_slider']);
		$this->setVar('list',$list);
		$this->output('show.htm');
	}



	function getToken(){
		$action = $this->getAction();
		if( $action ) exit;
			if( !_var('code') ){

				echo "
				<html>
				<head>
				<script>
			 
				var query = location.href.split('?');
				
				var url = 'index.php?mod=instagram_gallery' + '&' +query[1];
				
				setTimeout(function () {
					document.location.href = url;
				}, 1000);
				</script>
				</head>
				<body></body>
				</html>";
			
			}else{
		 
				$code = _var('code');

				$url = 'https://api.instagram.com/oauth/access_token';
				$data = [
					'client_id' => $_SESSION['instagram_config']['client_id'],
					'client_secret' => $_SESSION['instagram_config']['client_secret'],
					'grant_type' => 'authorization_code',
					'redirect_uri' => $_SESSION['instagram_config']['url_redirect'],
					'code' => $code
				];

				$options = array(
					'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => http_build_query($data)
					)
				);

				$context  = stream_context_create($options);
				$result = json_decode(file_get_contents($url, false, $context));

				$access_token = $result->access_token;

				$client_secret = $data['client_secret'];

				$long_token = json_decode(file_get_contents("https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret={$client_secret}&access_token={$access_token}"))->access_token;
				
				Marion::setConfig('instagram_gallery', 'client_id', $_SESSION['instagram_config']['client_id']);
				Marion::setConfig('instagram_gallery', 'client_secret', $_SESSION['instagram_config']['client_secret']);
				Marion::setConfig('instagram_gallery', 'user_id', $result->user_id);
				Marion::setConfig('instagram_gallery', 'access_token', $long_token);
				Marion::refresh_config();
				header('Location: /backend/index.php?ctrl=Conf&mod=instagram_gallery');
			}
	}


	function ajax(){
		$action = $this->getAction();
		$risposta = array();
		switch($action){
			case 'load_others':
				//$risposta = $this->loadImages();
				$risposta = $this->loadImages2();
				break;
		}

		echo json_encode($risposta);
	}


	function cron() {
		$config = Marion::getConfig('instagram_gallery');
		$access_token = $config['access_token'];

		$new_token = file_get_contents("https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token={$access_token}");

		echo $new_token;

		Marion::setConfig('instagram_gallery', 'access_token', json_decode($new_token)->access_token);
		Marion::refresh_config();
	}

	function loadImages2(){
		$access_token = Marion::getConfig('instagram_gallery','access_token');
		$immagini = file_get_contents("https://graph.instagram.com/me/media?fields=id,caption,media_type,media_url,username,permalink,thumbnail_url,timestamp&access_token={$access_token}");

		
		debugga(json_decode($immagini));exit;
	}

	function loadImages(){
		
		$limit = 5;
	

		$type = _var('type');


		$offset =_var('offset');
		require_once('modules/instagram_gallery/classes/Instagram.class.php');
		$list = InstagramImage::prepareQuery()->where('visibility',1)->orderBy('created_time','DESC')->limit($limit)->offset($offset)->get();
		$options = Marion::getConfig('instagram_gallery');
		if( $options['date_format'] ){
			$date_format = $options['date_format'];
			foreach($list as $v){
				$v->created_time = strftime($date_format,strtotime($v->created_time));
				
			}
		}
		$this->setVar('show_data',$options['show_info_images']);
		$this->setVar('show_slider',$options['show_slider']);
		foreach($list as $k => $v){
			$v->ordine = $offset+$k;
		}
		$this->setVar('list',$list);
		
		if( $type ){

			ob_start();
			$this->output('other_feed_slider.htm');
			$html2 = ob_get_contents();
			ob_end_clean();
		}else{
			ob_start();
			$this->output('other_feed.htm');
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
	
		return $risposta;

	}

}