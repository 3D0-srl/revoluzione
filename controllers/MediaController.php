<?php 

class MediaController{
    public $_image_cache = true;
    public $_image_cache_dir = 'cache';

    public $_image_watermark = false;
    public $_image_watermark_font = 'assets/fonts/DejaVuSerif.ttf';
    public $_image_watermark_text = 'napolitano';
    public $_image_watermark_position = 'diagonal';
    public $_image_watermak_positions_list = array(
        'diagonal', 
        'bottom_right', 
        'bottom_left', 
        'top_right', 
        'top_left', 
        'center',
    );


    function loadClasses(){
       
        //require_once(_MARION_LIB_.'classes/db/Database2.class.php');
        //require_once(_MARION_LIB_.'classes/db/DatabaseCache.class.php');
        require_once(_MARION_LIB_.'classes/image/ImageDisplay.class.php');
        require_once(_MARION_LIB_.'classes/attachment/Attachment.class.php');
        require_once(_MARION_LIB_.'functions.php');
    }
    

    function __construct(){
        
        $this->loadClasses();
        $this->_image_cache = _MARION_CACHE_IMAGES_;
        $action = $_GET['action'];
        
        switch($action){
            case 'image':
                $this->displayImage();
                break;
            case 'attachment':
                $this->displayAttachment();
                break;
        }
    }

    function displayAttachment(){
        $id = $_GET['id'];
        $type = $_GET['type'];
        $attach = Attachment::withId($id);
        if( is_object($attach) ){
            switch($type){
                case 'download':
                    $attach->download();
                    break;
                default:
                    $attach->display();	
                    break;
            }
        }
        
    }

  

    function displayImage(){

		if($this->_image_cache ){
            $encode = base64_encode($_SERVER['REQUEST_URI']);
			$file = _MARION_ROOT_DIR_.$this->_image_cache_dir."/".$encode;
            if(file_exists($file) ){
			  
			   
			   $image_data = unserialize(file_get_contents($file));
			   if( isset($image_data['byte']) ){
				   header('Content-type: ' . $image_data['mime']);
				   echo $image_data['byte'];
				   ob_end_flush();
				   exit;
			   }else{
				   header('Content-type: ' . $image_data['mime']);
				   readfile($image_data['path_webp']);
			   }
            }
        }
       
        $type = $_GET['type'];
        $id = $_GET['id'];
        $type = explode('-',$type);
        $no_watermark = $type[1];
        $type = $type[0];

        switch( $type ){
            case 'th':
                $type = 'thumbnail';
                break;
            case 'sm':
                $type = 'small';
                break;
            case 'md':
                $type = 'medium';
                break;
            case 'lg':
                $type = 'large';
                break;
            case 'or':
                $type = 'original';
                break;
            
            $id = $_GET['id'];

        }
        
       
        
       
        $image = new ImageDisplay();
        $image->get($id,$type);
		
        if($this->_image_cache ){
			// debugga(_MARION_ROOT_DIR_.$this->_image_cache_dir."/".$encode);exit;
            if($this->_image_watermark ){
				
                $image->setFontWatermark($this->_image_watermark_font);
                $image->setTextWatermark($this->_image_watermark_text);
                $data = $image->getDataWithWatermark($this->_image_watermark_position);
				$image_data = array(
					'byte' => $data,
					'mime' => $image->mime
				);
            }else{
				
                //$data = $image->getData();
				$image_data = (array)$image;
				//debugga($data);exit;
            }
			
			$file = _MARION_ROOT_DIR_.$this->_image_cache_dir."/".$encode;
            file_put_contents($file,serialize($image_data));

			
			if( isset($image_data['byte']) ){
			   header('Content-type: ' . $image_data['mime']);
			   echo $image_data['byte'];
			   ob_end_flush();
			   exit;
		   }else{
			   header('Content-type: ' . $image_data['mime']);
			   readfile($image_data['path_webp']);
		   }
           
        }else{
            if($this->_image_watermark ){
                $image->setFontWatermark($this->_image_watermark_font);
                $image->setTextWatermark($this->_image_watermark_text);
                $image->displayWithWatermark($this->_image_watermark_position);
            }else{
                $image->display();
            }
        }
    }
    

    

}

?>