<?php

class File{
	
	public static function mimeTypeSupportati(){
		$mimeTypeList = self::mimeTypeList();
		$mimeTypeList1 = array();
		/*if(okArray($GLOBALS['setting']['default']['files_supportati'])){
			foreach($GLOBALS['setting']['default']['files_supportati'] as $k=>$v){
				$v  = intVal($v);
				if( $v == 1 ){
					$mimeTypeList1[$k] = $mimeTypeList[$k];
				}
				
			}
			
		}*/
		return $mimeTypeList1;
	}
	public static function downloadFromPath($file, $name = NULL){
		if (file_exists($file)) {
		    header('Content-Description: File Transfer');
		    header('Content-Type: '.self::getMimeType($file));
		    header('Content-Disposition: attachment; filename='.basename($file));
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($file));
		    readfile($file);
		    exit;
		}
	}	
	
	public static function getMimeType( $filename ){
		$ext = strtolower(array_pop(explode('.',$filename)));
		return self::getMimeTypeFromExtension('.'.$ext);
	}
	
	
	public static function getMimeTypeFromExtension( $ext ){
		
		foreach( self::mimeTypeList() as $m=>$exts){
			foreach($exts as $v){
				if( $v == $ext){
					return $m;
				}
			}
		}
		return 'application/octet-stream';
	}
	
	public function checkFile($file,$ext){
		
	}
	
	public static function mimeTypeList(){
		return array (
		  'x-world/x-3dmf' => 
			  array (
			    0 => '.3dm',
			    1 => '.3dmf',
			    2 => '.qd3 ',
			    3 => '.qd3d ',
			  ),
		  'application/octet-stream' => 
			  array (
			    0 => '.a',
			    1 => '.arc',
			    2 => '.arj',
			    3 => '.bin',
			    4 => '.com',
			    5 => '.dump',
			    6 => '.exe',
			    7 => '.lha',
			    8 => '.lhx',
			    9 => '.lzh',
			    10 => '.lzx',
			    11 => '.o',
			    12 => '.psd',
			    13 => '.saveme',
			    14 => '.uu',
			    15 => '.zoo',
			  ),
		  'text/html' => 
			  array (
			    0 => '.acgi',
			    1 => '.htm',
			    2 => '.html',
			    3 => '.htmls',
			    4 => '.htx ',
			    5 => '.shtml ',
			  ),
		  'application/postscript' => 
			  array (
			    0 => '.ai',
			    1 => '.eps',
			    2 => '.ps',
			  ),
		  'audio/aiff' => 
			  array (
			    0 => '.aif',
			    1 => '.aifc',
			    2 => '.aiff',
			  ),
		  'audio/x-aiff' => 
			  array (
			    0 => '.aif',
			    1 => '.aifc',
			    2 => '.aiff',
		  	),
		  'video/x-ms-asf' => 
			  array (
			    0 => '.asf',
			    1 => '.asx',
			  ),
		  'text/x-asm' => 
			  array (
			    0 => '.asm',
			    1 => '.s',
			  ),
		  'audio/basic' => 
			  array (
			    0 => '.au',
			    1 => '.snd',
			  ),
		  'image/bmp' => 
			  array (
			    0 => '.bm',
			    1 => '.bmp',
			  ),
		  'application/book' => 
			  array (
			    0 => '.boo',
			    1 => '.book',
			  ),
		  'application/x-bzip2' => 
			  array (
			    0 => '.boz',
			    1 => '.bz2',
			  ),
		  'application/x-bsh' => 
			  array (
			    0 => '.bsh',
			    1 => '.sh',
			    2 => '.shar',
			  ),
		  'text/plain' => 
			  array (
			    0 => '.c',
			    1 => '.c++',
			    2 => '.cc',
			    3 => '.com',
			    4 => '.conf',
			    5 => '.cxx',
			    6 => '.def',
			    7 => '.f',
			    8 => '.f90',
			    9 => '.for',
			    10 => '.g',
			    11 => '.h',
			    12 => '.hh',
			    13 => '.idc',
			    14 => '.jav',
			    15 => '.java',
			    16 => '.list',
			    17 => '.log ',
			    18 => '.lst ',
			    19 => '.m',
			    20 => '.mar',
			    21 => '.pl',
			    22 => '.sdml',
			    23 => '.text',
			    24 => '.txt',
			  ),
		  'text/x-c' => 
			  array (
			    0 => '.c',
			    1 => '.cc',
			    2 => '.cpp',
			  ),
		  'application/x-netcdf' => 
			  array (
			    0 => '.cdf',
			    1 => '.nc',
			  ),
		  'application/pkix-cert' => 
			  array (
			    0 => '.cer',
			    1 => '.crt',
			  ),
		  'application/x-x509-ca-cert' => 
			  array (
			    0 => '.cer',
			    1 => '.crt',
			    2 => '.der',
			  ),
		  'application/x-chat' => 
			  array (
			    0 => '.cha',
			    1 => '.chat',
			  ),
		  'application/x-director' => 
			  array (
			    0 => '.dcr',
			    1 => '.dir',
			    2 => '.dxr',
			  ),
		  'video/x-dv' => 
			  array (
			    0 => '.dif',
			    1 => '.dv',
			  ),
		  'application/msword' => 
			  array (
			    0 => '.doc',
			    1 => '.dot',
			    2 => '.w6w',
			    3 => '.wiz',
			    4 => '.word ',
			  ),
		  'image/vnd.dwg' => 
			  array (
			    0 => '.dwg',
			    1 => '.dxf',
			    2 => '.svf',
			  ),
		  'image/x-dwg' => 
			  array (
			    0 => '.dwg',
			    1 => '.dxf',
			    2 => '.svf',
			  ),
		  'application/x-envoy' => 
		  	array (
			    0 => '.env',
			    1 => '.evy',
			  ),
		  'text/x-fortran' => 
			  array (
			    0 => '.f',
			    1 => '.f77',
			    2 => '.f90',
			    3 => '.for',
			  ),
		  'image/florian' => 
			  array (
			    0 => '.flo',
			    1 => '.turbot',
			  ),
		  'audio/make' => 
			  array (
			    0 => '.funk',
			    1 => '.my',
			    2 => '.pfunk',
			  ),
		  'audio/x-gsm' => 
			  array (
			    0 => '.gsd',
			    1 => '.gsm',
			  ),
		  'application/x-compressed' => 
			  array (
			    0 => '.gz',
			    1 => '.tgz',
			    2 => '.z',
			    3 => '.zip',
			  ),
		  'application/x-gzip' => 
			  array (
			    0 => '.gz',
			    1 => '.gzip',
			  ),
		  'text/x-h' => 
			  array (
			    0 => '.h',
			    1 => '.hh',
			  ),
		  'application/x-helpfile' => 
			  array (
			    0 => '.help',
			    1 => '.hlp',
			  ),
		  'application/vnd.hp-hpgl' => 
			  array (
			    0 => '.hgl',
			    1 => '.hpg',
			    2 => '.hpgl',
			  ),
		  'image/ief' => 
			  array (
			    0 => '.ief',
			    1 => '.iefs',
			  ),
		  'application/iges' => 
			  array (
			    0 => '.iges',
			    1 => '.igs',
			  ),
		  'model/iges' => 
			  array (
			    0 => '.iges ',
			    1 => '.igs',
			  ),
		  'text/x-java-source' => 
			  array (
			    0 => '.jav',
			    1 => '.java ',
			  ),
		  'image/jpeg' => 
			  array (
			    0 => '.jfif',
			    1 => '.jfif-tbnl',
			    2 => '.jpe',
			    3 => '.jpeg',
			    4 => '.jpg ',
			  ),
		  'image/pjpeg' => 
			  array (
			    0 => '.jfif',
			    1 => '.jpe',
			    2 => '.jpeg',
			    3 => '.jpg ',
			  ),
		  'audio/midi' => 
			  array (
			    0 => '.kar',
			    1 => '.mid',
			    2 => '.midi',
			  ),
		  'audio/nspaudio' => 
			  array (
			    0 => '.la ',
			    1 => '.lma',
			  ),
		  'audio/x-nspaudio' => 
			  array (
			    0 => '.la ',
			    1 => '.lma',
			  ),
		  'application/x-latex' => 
			  array (
			    0 => '.latex ',
			    1 => '.ltx',
			  ),
		  'video/mpeg' => 
			  array (
			    0 => '.m1v',
			    1 => '.m2v',
			    2 => '.mp2',
			    3 => '.mp3',
			    4 => '.mpa',
			    5 => '.mpe',
			    6 => '.mpeg',
			    7 => '.mpg',
			  ),
		  'audio/mpeg' => 
			  array (
			    0 => '.m2a',
			    1 => '.mp2',
			    2 => '.mpa',
			    3 => '.mpg',
			    4 => '.mpga',
			  ),
		  'message/rfc822' => 
			  array (
			    0 => '.mht',
			    1 => '.mhtml',
			    2 => '.mime ',
			  ),
		  'application/x-midi' => 
			  array (
			    0 => '.mid',
			    1 => '.midi',
			  ),
		  'audio/x-mid' => 
			  array (
			    0 => '.mid',
			    1 => '.midi',
			  ),
		  'audio/x-midi' => 
			  array (
			    0 => '.mid',
			    1 => '.midi',
			  ),
		  'music/crescendo' => 
			  array (
			    0 => '.mid',
			    1 => '.midi',
			  ),
		  'x-music/x-midi' => 
			  array (
			    0 => '.mid',
			    1 => '.midi',
			  ),
		  'application/base64' => 
			  array (
			    0 => '.mm',
			    1 => '.mme',
			  ),
		  'video/quicktime' => 
			  array (
			    0 => '.moov',
			    1 => '.mov',
			    2 => '.qt',
			  ),
		  'video/x-sgi-movie' => 
			  array (
			    0 => '.movie',
			    1 => '.mv',
			  ),
		  'video/x-mpeg' => 
			  array (
			    0 => '.mp2',
			    1 => '.mp3',
			  ),
		  'application/x-project' => 
			  array (
			    0 => '.mpc',
			    1 => '.mpt',
			    2 => '.mpv',
			    3 => '.mpx',
			  ),
		  'image/naplps' => 
			  array (
			    0 => '.nap',
			    1 => '.naplps',
			  ),
		  'image/x-niff' => 
			  array (
			    0 => '.nif',
			    1 => '.niff',
			  ),
		  'application/pkcs7-mime' => 
			  array (
			    0 => '.p7c',
			    1 => '.p7m',
			  ),
		  'application/x-pkcs7-mime' => 
			  array (
			    0 => '.p7c',
			    1 => '.p7m',
			  ),
		  'application/pro_eng' => 
			  array (
			    0 => '.part ',
			    1 => '.prt',
			  ),
		  'chemical/x-pdb' => 
			  array (
			    0 => '.pdb',
			    1 => '.xyz',
			  ),
		  'image/pict' => 
			  array (
			    0 => '.pic',
			    1 => '.pict',
			  ),
		  'image/x-xpixmap' => 
			  array (
			    0 => '.pm',
			    1 => '.xpm',
			  ),
		  'application/x-pagemaker' => 
			  array (
			    0 => '.pm4 ',
			    1 => '.pm5',
			  ),
		  'image/png' => 
			  array (
			    0 => '.png',
			    1 => '.x-png',
			  ),
		  'application/mspowerpoint' => 
			  array (
			    0 => '.pot',
			    1 => '.pps',
			    2 => '.ppt',
			    3 => '.ppz',
			  ),
		  'application/vnd.ms-powerpoint' => 
			  array (
			    0 => '.pot',
			    1 => '.ppa',
			    2 => '.pps',
			    3 => '.ppt',
			    4 => '.pwz ',
			  ),
		  'image/x-quicktime' => 
			  array (
			    0 => '.qif',
			    1 => '.qti',
			    2 => '.qtif',
			  ),
		  'audio/x-pn-realaudio' => 
			  array (
			    0 => '.ra',
			    1 => '.ram',
			    2 => '.rm',
			    3 => '.rmm ',
			    4 => '.rmp',
			  ),
		  'audio/x-pn-realaudio-plugin' => 
			  array (
			    0 => '.ra',
			    1 => '.rmp',
			    2 => '.rpm',
			  ),
		  'image/cmu-raster' => 
			  array (
			    0 => '.ras',
			    1 => '.rast',
			  ),
		  'application/x-troff' => 
			  array (
			    0 => '.roff',
			    1 => '.t',
			    2 => '.tr',
			  ),
		  'text/richtext' => 
			  array (
			    0 => '.rt',
			    1 => '.rtf',
			    2 => '.rtx',
			  ),
		  'application/rtf' => 
			  array (
			    0 => '.rtf',
			    1 => '.rtx',
			  ),
		  'application/x-tbook' => 
			  array (
			    0 => '.sbk ',
			    1 => '.tbk',
			  ),
		  'text/sgml' => 
			  array (
			    0 => '.sgm ',
			    1 => '.sgml',
			  ),
		  'text/x-sgml' => 
			  array (
			    0 => '.sgm ',
			    1 => '.sgml',
			  ),
		  'application/x-shar' => 
			  array (
			    0 => '.sh',
			    1 => '.shar',
			  ),
		  'text/x-server-parsed-html' => 
			  array (
			    0 => '.shtml',
			    1 => '.ssi',
			  ),
		  'application/x-koan' => 
			  array (
			    0 => '.skd',
			    1 => '.skm ',
			    2 => '.skp ',
			    3 => '.skt ',
			  ),
		  'application/smil' => 
			  array (
			    0 => '.smi ',
			    1 => '.smil ',
			  ),
		  'text/x-speech' => 
			  array (
			    0 => '.spc ',
			    1 => '.talk',
			  ),
		  'application/x-sprite' => 
			  array (
			    0 => '.spr',
			    1 => '.sprite ',
			  ),
		  'application/x-wais-source' => 
			  array (
			    0 => '.src',
			    1 => '.wsrc',
			  ),
		  'application/step' => 
			  array (
			    0 => '.step',
			    1 => '.stp',
			  ),
		  'application/x-world' => 
			  array (
			    0 => '.svr',
			    1 => '.wrl',
			  ),
		  'application/x-texinfo' => 
			  array (
			    0 => '.texi',
			    1 => '.texinfo',
			  ),
		  'image/tiff' => 
			  array (
			    0 => '.tif',
			    1 => '.tiff',
			  ),
		  'image/x-tiff' => 
			  array (
			    0 => '.tif',
			    1 => '.tiff',
			  ),
		  'text/uri-list' => 
			  array (
			    0 => '.uni',
			    1 => '.unis',
			    2 => '.uri',
			    3 => '.uris',
			  ),
		  'text/x-uuencode' => 
			  array (
			    0 => '.uu',
			    1 => '.uue',
			  ),
		  'video/vivo' => 
			  array (
			    0 => '.viv',
			    1 => '.vivo',
			  ),
		  'video/vnd.vivo' => 
			  array (
			    0 => '.viv',
			    1 => '.vivo',
			  ),
		  'audio/x-twinvq-plugin' => 
			  array (
			    0 => '.vqe',
			    1 => '.vql',
			  ),
		  'model/vrml' => 
			  array (
			    0 => '.vrml',
			    1 => '.wrl',
			    2 => '.wrz',
			  ),
		  'x-world/x-vrml' => 
			  array (
			    0 => '.vrml',
			    1 => '.wrl',
			    2 => '.wrz',
			  ),
		  'application/x-visio' => 
			  array (
			    0 => '.vsd',
			    1 => '.vst',
			    2 => '.vsw ',
			  ),
		  'application/wordperfect6.0' => 
			  array (
			    0 => '.w60',
			    1 => '.wp5',
			  ),
		  'application/wordperfect' => 
			  array (
			    0 => '.wp',
			    1 => '.wp5',
			    2 => '.wp6 ',
			    3 => '.wpd',
			  ),
		  'application/excel' => 
			  array (
			    0 => '.xl',
			    1 => '.xla',
			    2 => '.xlb',
			    3 => '.xlc',
			    4 => '.xld ',
			    5 => '.xlk',
			    6 => '.xll',
			    7 => '.xlm',
			    8 => '.xls',
			    9 => '.xlt',
			    10 => '.xlv',
			    11 => '.xlw',
			  ),
		  'application/x-excel' => 
			  array (
			    0 => '.xla',
			    1 => '.xlb',
			    2 => '.xlc',
			    3 => '.xld ',
			    4 => '.xlk',
			    5 => '.xll',
			    6 => '.xlm',
			    7 => '.xls',
			    8 => '.xlt',
			    9 => '.xlv',
			    10 => '.xlw',
			  ),
		  'application/x-msexcel' => 
			  array (
			    0 => '.xla',
			    1 => '.xls',
			    2 => '.xlw',
			  ),
		  'application/vnd.ms-excel' => 
			  array (
			    0 => '.xlb',
			    1 => '.xlc',
			    2 => '.xll',
			    3 => '.xlm',
			    4 => '.xls',
			    5 => '.xlw',
			  ),
		);
	}
	

}



?>