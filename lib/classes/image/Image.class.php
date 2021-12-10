<?php

define('IMAGE_TABLE', "image");
define('IMAGE_COMPOSED_TABLE', "imageComposed");
require (dirname(__FILE__).'/lib_img/ImageConverter.php');
require (dirname(__FILE__).'/lib_img/class.upload.php');
require (dirname(__FILE__).'/SimpleImage/SimpleImage.php');


/*
$options = array(
	'dirUpload' =>  "/var/www/html/medical/www.medical.com/img_upload",
	'debugg' =>  false,
	'format' => '', //tipo di formato in cui convertire le immagini (gif,jpg,jpeg,png)
	'image_watermark' => '', //path img watermark
	'small_x' => 50,  //ampiezza dell'immagine small
	'small_y' => 50,  //altezza dell'immagine small
	'medium_x' => 200, //ampiezza dell'immagine medium
	'medium_y' => 200, //altezza dell'immagine medium
	'large_x' => 500, //ampiezza dell'immagine large
	'large_y' => 500  //altezza dell'immagine large
);
*/
class Image extends Upload{
	private $_id;
	public $_path_old;
	

	// questo memorizza in $this->_path_old il percorso vecchio del file nel database
	public function setPathOld($old){
		$this->_path_old = $old;
	}
	
	public function setId($id){
		$this->_id=$id;
	}
	
	public function getId(){
		return $this->_id;
	}


	
	
	public function __construct($file,$options_image=array()) {
		parent::__construct($file,'it_IT');
		
		
		if( !okArray($options_image) ){
			$options_image = getConfig('image','options');
		}
		

		
		
		

		$options_image['dirUpload'] = _MARION_UPLOAD_DIR_."images";
		
		

		if(okArray($options_image)){
			
			$this->_resize = array();
			foreach($options_image as $k => $v){
				if( $k == 'resize'){
					$this->_resize = $v;
				}else{
					$key = "_{$k}";
					$this->$key = $v;
				}	
			}	
		}

		
		
		
		
		if( $this->file_src_error ){
			if( $this->_debugg ){ 
				error_log("Errore nell'upload dell'immagine");
			}
			return false;	
		}
		if( !$this->isImage() ){
			if( $this->_debugg ){ 
				error_log("Il file inserito non e' un'immagine");
			}
			return false;	
		}
		
	}
	
	public function isImage(){
		return $this->file_is_image;		
	}
	
	
	function addWatermark(){

		$this->image_watermark = $this->_image_watermark;
		return $this;

	}

	
	

	public static function withPath($file,$options=array()){ 
		$image = new Image($file,$options);	
		return $image;
	}
	
	public static function fromForm($file,$options=array()){ 
		$image = new Image($file,$options);	
		return $image;
	}
	
	
	
	
	public static function withId($id){
		
		$database = _obj('Database');
		$dati = $database->select('*',IMAGE_TABLE,"id={$id}");
		if(okArray($dati)){
			$dati = $dati[0];
			if( defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_ ){
				$path = '../'.$dati['path'];
			}else{
				$path = $dati['path'];
			}

			if( defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_ ){
				$path_webp = '../'.$dati['path_webp'];
			}else{
				$path_webp = $dati['path_webp'];
			}

			
			
			
			$dati['path'] = $path;
			
			
			
			$image = new Image($dati['path']);
			$image->setPathOld($dati['path']);
			$image->path_webp = $path_webp;
			$image->setId($dati['id']);
			$image->_filename_original = $dati['filename_original'];
			return $image;
		}
		return false;
		
	}

	public function build($x = NULL,$y = NULL, $path_destination = NULL){
		if($x && $y){
			$this->image_resize          = true;
			$this->image_ratio           = true;
			$this->image_y               = $y;  
			$this->image_x               = $x;
		}
		
		
		//se l'immagine in questione era presente nel database la cancello prima
		if( $this->_path_old ){ 
			unlink($this->_path_old);
		}
		
		if( $this->_format ){
			$this->image_convert = $this->_format;
	
		}
		
		if( !$path_destination ){
			$this->process($this->_dirUpload);
		}else{
			$this->process($path_destination);	
		}
		//debugga($this);exit;
		
		
		//risetto il nuovo path dell'immagine come path old
		$this->_path_old = $this->file_dst_pathname;
		return $this;
	}
	
	
	public function buildResize($tag,$overwrite=false){
		
		if( $tag && in_array($tag,$this->_resize) ){
			$x = "_".$tag."_x";
			$y = "_".$tag."_y";
			$this->file_overwrite = $overwrite;

			if( $this->image_src_x < $this->$x ){
				$this->build($this->image_src_x ,$this->image_src_y,$this->_dirUpload."/".$tag);
			}else{
				$this->build($this->$x ,$this->$y,$this->_dirUpload."/".$tag);
			}
			
			return $this;
		}else{
			return false;	
		} 	
		
	}


	
	public function removeImage(){
		
		if(	$this->file_src_pathname ){
			
			unlink($this->file_src_pathname);
			unlink($this->path_webp);
			
			
		}
	}
	
	public function delete(){
		
		if($this->_id){
			$database = _obj('Database');
			$this->removeImage();
			$database->delete(IMAGE_TABLE,"id={$this->_id}");
		}	
		
	}
	
	
	public function download(){
		ob_end_clean();
		header('Content-type: ' . $this->file_src_mime);
		header("Content-Disposition: attachment; filename=".rawurlencode($this->file_src_name).";");
		echo $this->Process();
		exit;
	}
	
	public function display(){
		
		header('Content-type: ' . $this->file_src_mime);
		readfile($this->file_src_pathname);
		exit;
	}

	function compress_png($path_to_png_file, $max_quality = 90)
	{
		if (!file_exists($path_to_png_file)) {
			throw new Exception("File does not exist: $path_to_png_file");
		}

		// guarantee that quality won't be worse than that.
		$min_quality = 60;

		// '-' makes it use stdout, required to save to $compressed_png_content variable
		// '<' makes it read from the given file path
		// escapeshellarg() makes this safe to use with any path
		
		
		$cmd = _MARION_LIB_."classes_"._MARION_VERSION_."/lib_img/pngquant-2.12.5/pngquant --quality=$min_quality-$max_quality - < ".escapeshellarg(    $path_to_png_file);
		//debugga($cmd);
		

		//debugga(ini_get('disable_functions'));

		if( is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec') ){
		
			$compressed_png_content = shell_exec($cmd);
		}else{
			//debugga('non funziona');
		}
		//debugga(_MARION_LIB_."classes_"._MARION_VERSION_."/lib_img/pngquant-2.12.5/pngquant --quality=$min_quality-$max_quality - < ".escapeshellarg(    $path_to_png_file),$compressed_png_content);

		//debugga($compressed_png_content);exit;
		if (!$compressed_png_content) {
			throw new Exception("Conversion to compressed PNG failed. Is pngquant 1.8+ installed on the server?");
		}

		return $compressed_png_content;
	}

	function createWebP(&$data){
		$ext = $data['ext'];
		if( !$ext ){
			$get_ext = explode('.',$data['path']);
			$ext = $get_ext[count($get_ext)-1];
		}
		$new_path = preg_replace("/".$ext."$/",'webp',$data['path']);
		
		$file_path = _MARION_ROOT_DIR_.$data['path'];
		/* LA CONVERSIONE IN WEBP DI UNA PNG NON FUNZIONA QUINDI FACCIO UNA MODIFICA */
		//if( strtolower($ext) == '3png'){
			
			/*try{
				$compressed_png_content = $this->compress_png($file_path);
				//debugga($compressed_png_content);exit;
				$tmpfname = tempnam(sys_get_temp_dir(), 'png_compressed');
				file_put_contents($tmpfname, $compressed_png_content);
				$img = imagecreatefrompng($tmpfname);
				imagepalettetotruecolor($img);
				imagealphablending($img, true);
				imagesavealpha($img, true);
				imagewebp($img, _MARION_ROOT_DIR_.$new_path,_MARION_QUALITY_WEBP_CONVERT_);
				imagedestroy($img);
				$data['path_webp'] = $new_path;
			}catch( Exception $e ){
				debugga($e);
			}*/
			/*debugga($file_path);
			$data['path_webp'] = $file_path;

		}else{*/
			try{
				//debugga($data['path']);
				if(\ImageConverter\convert(_MARION_ROOT_DIR_.$data['path'] , _MARION_ROOT_DIR_.$new_path, _MARION_QUALITY_WEBP_CONVERT_) ){
					$data['path_webp'] = $new_path;
				}else{
					$data['path_webp'] = $file_path;
				}
			}catch( Exception $e ){
				$data['path_webp'] = $file_path;
			}
		//}
		return true;
		
		
	}
	
	
	public function save(){
			$toinsert = array(
				'filename' => $this->file_dst_name,
				'filename_original' => $this->file_src_name
			);
			
			if( $this->parent_name){
				$toinsert['filename_original'] = $this->parent_name;
			}
			
			if($this->file_dst_pathname){
				$toinsert['path'] = $this->file_dst_pathname;
			}else{
				$toinsert['path'] = $this->file_src_pathname;
			}
			
			
			
			$toinsert['mime'] = $this->file_src_mime;
			$toinsert['ext'] = $this->file_src_name_ext;
			$toinsert['width'] = $this->image_dst_x;
			$toinsert['height'] = $this->image_dst_y;
			$path_root = preg_replace('/\//','\/',_MARION_ROOT_DIR_);
			$toinsert['path'] = preg_replace("/{$path_root}/",'',$toinsert['path']);
			
			$this->createWebP($toinsert);

			$database = _obj('Database');
			if(!$this->_id){
				$this->_id = $database->insert(IMAGE_TABLE,$toinsert);	
			}else{
				$database->update(IMAGE_TABLE,"id={$this->_id}",$toinsert);
			}
			
			if($this->_id){
				$image_new = Image::withId($this->_id);
				foreach($image_new as $k => $v){
					$this->$k = $v;
				}	
			}
			$this->file_overwrite = false;
			$this->image_watermark = NULL;
			return $this;
			
			
	}
	
	public function getPath(){
		return $this->file_src_pathname;	
	}


	
	public function getSrc(){
	    return $this->file_src_pathname;
	}
	
	
	
	
	
	/*public function copy(){
		$new = clone $this;
		unset($new->_id);
		unset($new->_path_old);
		return $new;
	}*/
	
	
	public static function getExtensionFromMime($mime){
		$mimes = array(
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
            'flv' => 'video/x-flv',
            'js' => 'application/x-javascript',
            'json' => 'application/json',
            'tiff' => 'image/tiff',
            'css' => 'text/css',
            'xml' => 'application/xml',
            'doc' => 'application/msword',
            'docx' => 'application/msword',
            'xls' => 'application/vnd.ms-excel',
            'xlt' => 'application/vnd.ms-excel',
            'xlm' => 'application/vnd.ms-excel',
            'xld' => 'application/vnd.ms-excel',
            'xla' => 'application/vnd.ms-excel',
            'xlc' => 'application/vnd.ms-excel',
            'xlw' => 'application/vnd.ms-excel',
            'xll' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pps' => 'application/vnd.ms-powerpoint',
            'rtf' => 'application/rtf',
            'pdf' => 'application/pdf',
            'html' => 'text/html',
            'htm' => 'text/html',
            'php' => 'text/html',
            'txt' => 'text/plain',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'mp3' => 'audio/mpeg3',
            'wav' => 'audio/wav',
            'aiff' => 'audio/aiff',
            'aif' => 'audio/aiff',
            'avi' => 'video/msvideo',
            'wmv' => 'video/x-ms-wmv',
            'mov' => 'video/quicktime',
            'zip' => 'application/zip',
            'tar' => 'application/x-tar',
            'swf' => 'application/x-shockwave-flash',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ott' => 'application/vnd.oasis.opendocument.text-template',
            'oth' => 'application/vnd.oasis.opendocument.text-web',
            'odm' => 'application/vnd.oasis.opendocument.text-master',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'otg' => 'application/vnd.oasis.opendocument.graphics-template',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'otp' => 'application/vnd.oasis.opendocument.presentation-template',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odi' => 'application/vnd.oasis.opendocument.image',
            'oxt' => 'application/vnd.openofficeorg.extension',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'thmx' => 'application/vnd.ms-officetheme',
            'onetoc' => 'application/onenote',
            'onetoc2' => 'application/onenote',
            'onetmp' => 'application/onenote',
            'onepkg' => 'application/onenote',
        );
		
		 $ext = array_search($mime,$mimes);
		 if($ext){
			return $ext;	 
		 }
		 return false;
		
		
	}
	
	public static function fromString($data,$filename){
		$data = base64_decode($data);
		//prendo il mime type
		$f = finfo_open();
		$mime_type = finfo_buffer($f, $data, FILEINFO_MIME_TYPE);
		$im = imagecreatefromstring($data);
		$ext = self::getExtensionFromMime($mime_type);
		if($ext){
			$path = _MARION_TMP_DIR_."/".$filename.".".$ext;
			switch( $ext ){
				case 'png':	
					imagepng($im,$path);
					break;
				case 'gif':	
					imagegif($im, $path);
					break;
				case 'jpe':
				case 'jpeg':	
					imagejpeg($im, $path);
					break;
			}
			return self::withPath($path);
		}else{
			/*if( $this->_debugg ){ 
				debugga('file non supportato');	
			}*/
			return false;	
		}
		
	}
	
	public function encode64(){
		return base64_encode(file_get_contents($this->file_src_pathname));
	}
	
	public function decode64($data){
		$data = base64_decode($data);
		$im = imagecreatefromstring($data);
		
	}

	
	
}


class ImageComposed{
	
	public function setId($id){
		$this->_id=$id;
	}
	
	public function getId(){
		return $this->_id;
	}
	
	
	public function __construct($options=array()) {
		if( !okArray($options) ){
			$options = getConfig('image','options');
		}

		
		$this->_options_resize = $options;
		$resize = $options['resize'];

		if( okArray($resize) ){
			$this->_resize = $resize;
			
		}
	}
	//prima si chiamava init
	public static function create($options=array()){
		return new ImageComposed($options);
	}
	
	
	public static function withFile($file,$options=array()){ 
		$image = new ImageComposed($options);
		$image->_file = $file;
		return $image;		
	}

	public static function fromUrl($url,$filename,$options=array()){
		if( filter_var($url, FILTER_VALIDATE_URL) && $filename ){
			$path_tmp = sys_get_temp_dir()."/".$filename;
			try {
				copy($url, $path_tmp);
			} catch (Exception $e) {
				return false;
			}
			return self::withFile($path_tmp,$options);
		}
		return false;
	}

	public static function fromByte($data,$filename,$options=array()){
		
		$path_tmp = sys_get_temp_dir()."/".$filename;
		$res = file_put_contents($path_tmp,$data);
		
		return self::withFile($path_tmp,$options);
		
		return false;
	}
	
	public static function fromForm($file,$options=array()){ 
		$image = new ImageComposed($options);
		$image->_file = $file;
		
		$image->_from_form = true;
		return $image;		
	}
	
	
	public static function withId($id){
			$database = _obj('Database');
			$dati = $database->select('*',IMAGE_COMPOSED_TABLE,"id={$id}");
			
			if(okArray($dati)){
				$dati=$dati[0];
				$image = new ImageComposed();
				foreach($dati as $k => $v){
					$key = "_{$k}";
					$image->$key = $v;
				}	
				return $image;
			}
			return false;
	}
	
	public function setFile($pathFile){
		$this->_file = $pathFile;
		return $this;
	}
	
	public function get($type){
		if( $type ){
			$type = strtolower($type);
		}else{
			$type = 'original';	
		}
		$type = "_{$type}";
		return Image::withId($this->$type);	
	}
	
	public function display($type){
		if($type){
			$this->get($type)->display();	
		}else{
			$this->get('original')->display();	
		}
		
	}
	
	public function save(){

		
		if($this->_id){
			$old_img = array();
			foreach($this as $k => $v){
				if( $k != '_id'){
					$old_img[] = $v;
				}	
				
			}	
		}
		
		if($this->_file){
			if( $this->_from_form ){
				$original = Image::fromForm($this->_file,$this->_options_resize);
				
			}else{
				if( file_exists($this->_file) ){
					
				}
				$original = Image::withPath($this->_file,$this->_options_resize);	
				
			}
			
			$file_name_original = $original->file_src_name;
			
			//creo l'immagine originale
			$original = $original->build();
			$file_path_original = $original->file_src_pathname;
			
			$original = $original->save();
			
			$toinsert['original'] = $original->getId();
			

			//prendo il path dell'immagine originale
			
			
			
			foreach($this->_resize as $v){
				//$image = $original->copy();
				$image = Image::withPath($file_path_original,$this->_options_resize);	
				$image->parent_name = $file_name_original;
				$toinsert[$v] = $image->buildResize($v,true)->save()->getId();	
				
			}
			
			$database = _obj('Database');
			if(!$this->_id){
				$this->_id = $database->insert(IMAGE_COMPOSED_TABLE,$toinsert);
				foreach($toinsert as $key => $value){
					$key = "_{$key}";
					$this->$key = $value;
				}
			}else{
				$database->update(IMAGE_COMPOSED_TABLE, "id={$this->id}", $toinsert);
			}
			$image_new = self::withId($this->_id);
			foreach( $image_new as $k => $v){
				$this->$k = $v;	
			}
			if(okArray($old_img)){
				foreach( $old_img as $v){
					Image::withId($v)->delete();	
				}	
			}
			
		}
		
		return $this;
		
	}
	
	public function delete(){
		if( $this->_id){
			
			$database = _obj('Database');
			
			$obj = $this->get('original');
			
			if( $obj ){
				$obj->delete();
			}
			
			foreach($this->_resize as $v){
				
				$obj = $this->get($v);

				if( is_object($obj) ){
					$obj->delete();
				}
				
			}
			$database->delete(IMAGE_COMPOSED_TABLE,"id={$this->_id}");
				
		}
	}



	public function duplicate(){
		if( $this->_id ){
			
			$options_resize = getConfig('image','options');
			foreach($options_resize['resize'] as $resize){
				$key = "_{$resize}";
				if($this->$key){
					$resize_aviable[] = $resize;
				}
			}
			$options_resize['resize'] = $resize_aviable;
			
			$image = $this->get('original');
			
			
			if( is_object($image) ){
				
				$image_new = self::withFile($image->file_src_pathname);
				
				
				$res = $image_new->save();
				return $res;
			}

			
		}

		return false;

	}
	
}


?>