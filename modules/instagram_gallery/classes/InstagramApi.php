<?php
class InstagramApi{
	private $_token ='';	

	private $_uri = 'https://api.instagram.com/v1/';

	
	public function __construct($options){
		$this->_token = $options['access_token'];
		$this->_type = 'tag';//$options['type'];
		$this->_lat = $options['lat'];
		$this->_long = $options['long'];
	}
	

	function getMediaSearchEndpoint($dist){
		$url = $this->getFormatUrl('media');
		$url = vsprintf($url,array($this->_lat,$this->_long,$dist,$this->_token));
		return $url;
	}
	

	function getRecentByTagEndpoint(){
		$url = $this->getFormatUrl('tag');
		$url = vsprintf($url,array($this->_token));
		return $url;
	}



	function getFormatUrl($type){
		$url = $this->_uri;
		switch($type){
			case 'tag':
				//$url .= 'tags/%s/media/recent?access_token=%s';
				$url .= 'users/self/media/recent?access_token=%s';
				break;
			case 'media':
				$url .= 'media/search?lat=%s&lng=%s&distance=%s&access_token=%s';
				break;
			case 'comments':
				$url .= 'media/%s/comments?access_token=%s';
				break;
			default:
				return '';

		}
		return $url;
	}

	function getImagesFromTags($tags){
		if( $this->_type == 'geo'){
			$url = $this->getMediaSearchEndpoint(5000);
		}else{
			$url = $this->getRecentByTagEndpoint();
		}

		$res = $this->call($url);
		
		if( okArray( $res->data )){

			
			
			foreach($res->data as $v){
				
				$_tags = $v->tags;
				$intersect = array_intersect($_tags,$tags);
				
				if( !okArray($intersect)) continue;
				$data = array(
					'id_instagram' => $v->id,
					'url_image' => 	$v->images->standard_resolution->url,
					'num_likes' => $v->likes->count,
					'tags' => $v->tags,
					'num_comments' => $v->comments->count,
					'link' => $v->link,
					'created_time' => strftime('%Y-%m-%d',$v->created_time),
					'text' => $v->caption->text
				);

				
				//$comments = $this->getCommentsFromMedia($v->id);
				//debugga($data);exit;
				$images[] = $data;

			}

		}
		
		return $images;
	}
	
	

	function getImagesFromTag($tag){
		if( $this->_type == 'geo'){
			$url = $this->getMediaSearchEndpoint(5000);
		}else{
			$url = $this->getRecentByTagEndpoint();
		}


		
		
		
		
		
	
		$res = $this->call($url);
		
		if( okArray( $res->data )){

			
			
			foreach($res->data as $v){
				$_tags = $v->tags;
				$intersect = array_intersect($_tags,$tags);
				if( !okArray($intersect)) continue;
				$data = array(
					'id_instagram' => $v->id,
					'url_image' => 	$v->images->standard_resolution->url,
					//'likes' => $v->likes->count,
					'tags' => $v->tags,
					//'count_comments' => $v->comments->count,
					'link' => $v->link,
					'created_time' => strftime('%Y-%m-%d',$v->created_time),
					'text' => $v->caption->text
				);

				
				//$comments = $this->getCommentsFromMedia($v->id);
				//debugga($data);exit;
				$images[] = $data;
			}
		}
		debugga($images);exit;
		return $images;
	}



	/*function getCommentsFromMedia($media){
		$url = $this->getFormatUrl('comments');
		$url = vsprintf($url,array($media,$this->_token));
		debugga($url);exit;

	}*/




	
	function call($api_url ){
		$connection_c = curl_init(); // initializing
		curl_setopt( $connection_c, CURLOPT_URL, $api_url ); // API URL to connect
		curl_setopt( $connection_c, CURLOPT_RETURNTRANSFER, 1 ); // return the result, do not print
		curl_setopt( $connection_c, CURLOPT_TIMEOUT, 20 );
		$json_return = curl_exec( $connection_c ); // connect and get json data
		curl_close( $connection_c ); // close connection
		return json_decode( $json_return ); // decode and return
	}


	public static function install(){
		$database = _obj('Database');
	}


	public static function uninstall(){
		$database = _obj('Database');
	}


}


?>