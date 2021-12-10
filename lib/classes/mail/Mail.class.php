<?php
require_once 'Mail.php';
require_once 'Mail/mime.php';

/**
* @author Ciro Napolitano
* @author Ciro Napolitano <ciro.napolitano87@gmail.com>
* @package	Classes
*/





class Mail2{

		
	const X_MAILER = '3d0.it'; 
	const X_ABUSE = 'abuse@3d0.it'; 
	

	/** @var string contiene il mittente della mail (es. "pippo@email.com")*/
	private $_from;
	/** @var string contiene i destinatari della mail separati da una virgola (es. "pluto@email.com, pippo@baudo.it, prova@gmail.com" ) */
	private $_to; 
	/** @var string|null contiene l'oggetto della mail */
	private $_subject; 
	/** @var string|null contiene il testo della mail */
	private $_text;
	/** @var string|null contiene l'html della mail */
	private $_html;
	/** @var string|null contiene i contatti in copia carbone separati da una virgola (es. "pluto@email.com, pippo@baudo.it, prova@gmail.com" ) */
	private $_cc; 
	/** @var string|null contiene il nome della pagina di template da cui generare l'html della mail*/
	private $_templateHtml; 
	/** @var string|null contiene il nome della pagina di template da cui generare il testo della mail*/
	private $_templateText; 
	/** @var object contiene un oggetto di tipo Mali_mime*/
	private $_mime;  

	private $_twig;
	
	
	/**
	* Costruttore della classe.
	* @method void __construct( string $from = NULL, string $to = NULL, string $subject = NULL, string $cc = NULL)
	*/
	public function __construct($from=NULL, $to=NULL, $subject=NULL,$cc=NULL){
	
		$this->_to = $to;
		$this->_from = $from;
		$this->_subject = $subject;
		$this->_cc = $cc;
		$this->setMime();
	}
	
	/**
	 *	Metodo che permette di settare il mittente dalla mail  
	 *	@param string $from contiene la mail del mittente
	 *	@return void 
	 */
	public function setFrom($from){
		$this->_from = $from;
	}
	
	/**
	 *	Metodo che permette di settare i destinatari dalla mail  
	 *	@param string $to contiene la mail dei destinatari
	 *	@return void 
	 */
	public function setTo($to){
		$this->_to = $to;
	}
	
	/**
	 *	Metodo che permette di settare l'oggetto dalla mail  
	 *	@param string $subject contiene l'oggetto della mail
	 *	@return void 
	 */
	public function setSubject($subject){
		$this->_subject = $subject;
	}
	
	/**
	 *	Metodo che permette di settare il cc della mail 
	 *	@param string $cc contiene le mail in copia carbone
	 *	@return void 
	 */
	public function setCc($cc){
		$this->_cc = $cc;
	}
	
	/**
	*	Metodo che setta i parametri l'oggetto Mail_mime. Riceve in input un array conetenente i parametri di configurazione del Mail_mime.
	*	@method void setMime(  array $params = NULL) 
	*/
	public function setMime($params=NULL){
		if($params){
			
			$this->_mime = new Mail_mime($params);
		}else{
			$params['eol'] = "\n";
		    $params['html_charset'] = 'UTF-8';
		    $params['text_charset'] = 'UTF-8';
			$params['text_encoding']  = '8bit';
			$params['html_encoding']  = '8bit';
			$params['head_charset']  = 'UTF-8';
			$this->_mime = new Mail_mime($params);
		}
		
		
	}
	
	
	/**
	*	Metodo che aggiunge un testo, in formato text alla mail.
	*	@method void setText(  string $text) 
	*/
	
	public function setText($text){
		$this->_text = $text;
	}
	
	/**
	*	Metodo che setta il template da cui generare l'html della mail
	*	@param string $page contiene il nome della pagina di template
	*	@return void 
	*/
	public function setTemplateHtml($page,$module=NULL,$out_module=FALSE){
		$this->_templateHtml = $page;
		$this->_module_dir_template_htm = $module;
		$this->_out_module_dir_template_html = $out_module;
	}
	
	/**
	*	Metodo che setta l'html (testo in formato html) della mail
	*	@param string $page contiene il nome della pagina di template
	*   @param string $module se la mail è una mail contenuta in un modulo
	*   @param string $out_module stabilisce se la mail la stampo da fuori il modulo o da dentro
	*	@return void 
	*/
	public function setTemplateText($page,$module=NULL,$out_module=FALSE){
		$this->_templateText = $page;
		$this->_module_dir_template_text = $module;
		$this->_out_module_dir_template_text = $out_module;
	}
	
	/**
	*	Metodo che setta il body (testo in formato html) della mail
	*	@method void setHtml(  string $html) 
	*/
	public function setHtml($html){
		$this->_html = $html;
	}


	/**
	 *	Metodo che permette di aggiungere allegati alla mail  
	 *	@param array $files array contenente i percorsi dei file da allegare alla mail
	 *	@return void 
	 *	@example esempio_addFiles.php
	 */
	public function addFiles($files){
		
		if( is_array($files) ){
			foreach($files as $f){
				$this->_mime->addAttachment($f,$this->mime_content_type($f));
			}
		}
		
	}

	public function addFiles2($files){
		
		if( is_array($files) ){
			foreach($files as $f){
				$this->_mime->addAttachment($f['path'],$f['type'],$f['name']);
			}
		}
		
	}
	
	/**
	 *	Metodo che permette di aggiungere allegati in formato data alla mail
	 *	@param array $datafiles array contenente i dati dei file da allegare alla mail. 
	 *	@return void 
	 *	@example esempio_addDataFiles.php
	 */
	public function addDataFiles($datafiles){
		
		if( is_array($datafiles) ){
			foreach($datafiles as $f){
				$this->_mime->addAttachment($f['data'],$f['type'],$f['name'],false);
			}
		}
		
		
	}
	/**
	*	Metodo che inserisce gli indirizzi in copia carbone a partire da un array
	*	@method void setCcFromArray( array $emails)
	*/
	public function setCcFromArray($emails){
		if(is_array($emails)){
			foreach( $emails as $email){
				$this->_cc .= trim($email).",";
			}
			$this->_cc = preg_replace('/,$/','',$this->_cc);
	
		}
	}
	
	/**
	*	Metodo che inserisce gli indirizzi email dei destinatari a partire da un array
	*	@method void setToFromArray( array $emails) 
	*/
	public function setToFromArray($emails){
		if(is_array($emails)){
			foreach( $emails as $email){
				$this->_to .= trim($email).",";
			}
			$this->_to = preg_replace('/,$/','',$this->_to);
			
		}
	}

	/**
	*	Metodo che imposta il templete engin TWIG
	*	@method void setTwig( boolean $bool)  
	*/
	public function setTwig($bool=false){
		$this->_twig = $bool;
	}



	
	
	/**
	*	Metodo che invia la mail qualora i campi $from e $to siano settati
	*	@method void send()  
	*/
	public function send(){
	
		$from = new wlString($this->_from);
			
	
		if($from->isEmail() && $this->_to){
			
			$emails_to = explode(',',$this->_to);
			foreach($emails_to as $v){
				$v1 = new wlString(trim($v));
				if( !$v1->isEmail()){
					error_log("MAIL:: qualche email di destinazione non e' corretta");
				}
			}
			
			$headers = array(
			  'From'	=> $this->_from,
			  'Subject' => $this->_subject,
			  'Return-Path'   => $this->_from,
			  'Content-Type'  => 'text/html; charset=UTF-8'
			);
			
		
			
			$headers['X-Mailer'] = STATIC::X_MAILER;
            $headers['X-Complaints-To'] = STATIC::X_ABUSE;


			if( $this->_cc){
				$emails_cc = explode(',',$this->_cc);
				foreach($emails_cc as $v){
					$v1 = new wlString(trim($v));
					if( !$v1->isEmail()){
						error_log("MAIL:: qualche email CC non e' corretta");
					}
				}
				$headers['Cc'] = $this->_cc;
			}  
			
		
			
			if( $this->_text ){
				$this->_mime->setTXTBody($this->_text);
			}
			if( $this->_html ){
				$this->_mime->setHTMLBody($this->_html);
			}
			
			//debugga($this->_html);exit;
		
			$body = $this->_mime->get();
			
			$headers = $this->_mime->headers($headers);
			
			$mail =& Mail::factory('sendmail');
			
			return $mail->send($this->_to, $headers, $body);
		}else{
			error_log("MAIL:: la mail di invio non e' corretta");
		}
	}
	
	//ritorna il body html della mail
	function getHtmlBody(){
		return $this->_html;
	}

	//ritorna il body text della mail
	function getTextlBody(){
		return $this->_text;
	}

	function mime_content_type($filename) {

		$mime_types = array(

			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);

		$ext = strtolower(array_pop(explode('.',$filename)));
		if (array_key_exists($ext, $mime_types)) {
			return $mime_types[$ext];
		}
		elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}
		else {
			return 'application/octet-stream';
		}
	}
	
	
}


?>