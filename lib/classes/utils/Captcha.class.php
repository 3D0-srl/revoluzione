<?php
require_once 'Text/CAPTCHA.php';
class Captcha{
	function __construct($options){
		
		if(!okArray($options)) return;
		$temp = $options;
		if( isDev()){
			//debugga($temp);exit;
		}
		$imageOptions = array(
			'font_size'        => $temp['font_size'],
		    'font_path'        => $temp['font_path'],
		    'font_file'        => $temp['font_file'],
		    'text_color'       => $temp['text_color'],
		    'lines_color'      => $temp['lines_color'],
		    'background_color' => $temp['background_color']		
		);
		$options = array(
		    'width' =>  (int)$temp['width'],
		    'height' => (int)$temp['height'],
		    'output' => $temp['output'],
		    'imageOptions' => $imageOptions
		);
		$this->path_file = $temp['path_file'];
		$this->options = $options;
		
		
		//$this->image = "<img  src='data:image/{$this->options['output']};base64, $image'/>";		
	}
	


	//creo il captcha
	function create(){
		$c = Text_CAPTCHA::factory('Image');
		
		$retval = $c->init($this->options);
		
	
		if (PEAR::isError($retval)) {
		    printf('Error initializing CAPTCHA: %s!',
		        $retval->getMessage());
		    exit;
		}
		
		$this->text = $c->getPhrase();
		
		
		$image = $c->getCAPTCHA();
		if (PEAR::isError($image)) {
		    echo 'Error generating CAPTCHA!';
		    echo $image->getMessage();
		    exit;
		
		}
		//$image = base64_encode($image);
		
		//creo il file
		$file_captcha = $this->path_file."captcha_".md5($this->text);

		
		file_put_contents($file_captcha, $image);


		$this->link_captcha = "/mail.php?action=show_captcha&code=".md5($this->text);
		$this->code_captcha = md5($this->text);
	}


	function destroy($code){
		if( $code ){
			$path_file = $this->path_file."captcha_".$code;
			
			if( file_exists($path_file) ){
				unlink($path_file);
			}
			
		}
	}
	
	
	
	
	
	
	
	
}




?>