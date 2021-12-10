<?php
require_once ( dirname(__FILE__).'/IniParser.php');
require_once(dirname(__FILE__).'/Database2.class.php');
require_once(dirname(__FILE__).'/DatabaseCache.class.php');


if( preg_match('/\/modules\//',$_SERVER['REQUEST_URI']) ){
	$parser = new IniParser("../../../config.ini");
}else{
	$parser = new IniParser("../config.ini");
}
$GLOBALS['setting']['default'] = $parser->parse();



class ImageDisplayCache{
	private $_database;
	private $_config;
	function __construct(){

		$options = $GLOBALS['setting']['default']['DATABASE']['options'];
		$this->_database = new DatabaseCache($options);


		$select = $this->_database->select('*','setting',"gruppo='image'");
		if( okArray($select) ){
			foreach($select as $v){
				$this->_config[$v['chiave']] = $v['valore'];
			}
		}
		
	}

	
	function get($id,$type='original'){
		
		$database = _obj('Database');
		$img = $this->_database->select('i.*',"image as i join imageComposed as c on c.{$type}=i.id","c.id={$id}");
		
		if(!empty($img) && is_array($img) && count($img)>0){
			foreach($img[0] as $k => $v){
				$this->$k = $v;
			}
		}
	}



	function display(){
		//$this->path = "/var/www/html/eshop/www/img_upload/Chrysanthemum.jpg";
		header('Content-type: ' . $this->mime);
		ob_clean();
		flush();
		readfile($this->path);
		exit;

	}

	function setFontWatermark($fontPath){
		$this->fontWatermarkPath = $fontPath;
	}

	function setTextWatermark($text){
		$this->textWatermark = $text;
	}



	function displayWithWatermark($position='DIAGONAL'){
			$text = $this->textWatermark;
			


			$font = $this->fontWatermarkPath;
			if( !file_exists($font) ){
				$this->display();
				exit;
			}
			
			$len_text = strlen($text);
			
			
			$image = $this->createImage();

			

			if( $this->width ){
				$width = $this->width; 
			}else{
				$width = imagesx($image);  
			}
			if( $this->height ){
				$height = $this->height;
			}else{
				$height = imagesy($image);
			}
			
			$font_size = (int)((2*$width)/100);
			if(!$font_size) $font_size = 1;


			
			$white = imagecolorallocatealpha($image, 211, 211, 211, 80);
			
			
			$step = $font_size*10;
			$margin_x = (int)($width*(7/100));
			$margin_y = (int)($height*(7/100));

			
			switch($position){
				case 'diagonal':
					for($i=0;$i<$width; $i += $step){
						for($j=0;$j<$height; $j += $step){
							ImageTTFText ($image, $font_size, -30, $i, $j, $white, $font,$text);
						}
					}
					break;
				case 'center':
					$bbox = imagettfbbox($font_size, 0, $font, $text);
					
					$text_width = $bbox[2]-$bbox[0];
					$text_height = $bbox[7]-$bbox[1];
					
					$x = ($width/2) - ($text_width/2);
					$y = ($height/2) - ($text_height/2);

					ImageTTFText ($image, $font_size, 0, $x, $y, $white, $font,$text);
					break;
				case 'bottom_right':
					$bbox = imagettfbbox($font_size, 0, $font, $text);
					
					$text_width = $bbox[2]-$bbox[0];
					$text_height = $bbox[7]-$bbox[1];
					
					$x = $width - $text_width - $margin_x;
					$y = $height - $text_height -$margin_y;

					ImageTTFText ($image, $font_size, 0, $x, $y, $white, $font,$text);
					break;
				case 'bottom_left':
					$bbox = imagettfbbox($font_size, 0, $font, $text);
					
					$text_width = $bbox[2]-$bbox[0];
					$text_height = $bbox[7]-$bbox[1];
					
					$x = $margin_x;
					
					$y = $height - $text_height - $margin_y;
					ImageTTFText ($image, $font_size, 0, $x, $y, $white, $font,$text);
					break;
				case 'top_left':
					$bbox = imagettfbbox($font_size, 0, $font, $text);
					
					$text_width = $bbox[2]-$bbox[0];
					$text_height = $bbox[7]-$bbox[1];
					
					$x = $margin_x;
					$y = $margin_y;

					ImageTTFText ($image, $font_size, 0, $x, $y, $white, $font,$text);
					break;
				case 'top_right':
					$bbox = imagettfbbox($font_size, 0, $font, $text);
					
					$text_width = $bbox[2]-$bbox[0];
					$text_height = $bbox[7]-$bbox[1];
					
					$x = $width - $text_width - $margin_x;
					$y = $margin_y;

					ImageTTFText ($image, $font_size, 0, $x, $y, $white, $font,$text);
					break;
			}
			
			
			//imagecolortransparent($image, imagecolorallocate($image, 0,0,0));
			header("Content-type: ".$this->mime);
			ob_clean();
			flush();
			if( $this->mime == 'image/png' ){
				imagepng($image);
			}elseif( $this->mime == 'image/jpeg' ){
				imagejpeg($image);
			}elseif($this->mime == 'image/gif'){
				imagegif($image);
			}
			imagejpeg($image);
			imagedestroy($image);
			exit;

	}



	function createImage(){

		switch($this->mime){
			case 'image/jpeg':
				$image = imagecreatefromjpeg($this->path);
				break;
			case 'image/png':
				$image = imagecreatefrompng($this->path);
				imageAlphaBlending($image, true);
				imageSaveAlpha($image, true);
				break;
			case 'image/gif':
				$image = imagecreatefromgif($this->path);
				break;
			default:
				$image = false;
		}
		

		
		return $image;


	}




}






?>