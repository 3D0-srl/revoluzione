<?php
class Translate{
	private static $endpoint = "http://www.transltr.org/api/translate";
	
	private static $aviableLanguages =
		array(
			"ar" => 	"Arabic                ",
			"bs" => 	"Bosnian               ",
			"bg" => 	"Bulgarian             ",
			"ca" => 	"Catalan               ",
			"zh-CHS" => "Chinese Simplified    ",
			"zh-CHT" => "Chinese Traditional   ",
			"hr" => 	"Croatian              ",
			"cs" => 	"Czech                 ",
			"da" => 	"Danish                ",
			"nl" => 	"Dutch                 ",
			"en" => 	"English               ",
			"et" => 	"Estonian              ",
			"fi" => 	"Finnish               ",
			"fr" => 	"French                ",
			"de" => 	"German                ",
			"el" => 	"Greek                 ",
			"ht" => 	"Haitian Creole        ",
			"he" => 	"Hebrew                ",
			"hi" => 	"Hindi                 ",
			"mww" =>	" Hmong Daw            ",
			"hu" => 	"Hungarian             ",
			"id" => 	"Indonesian            ",
			"it" => 	"Italian               ",
			"ja" => 	"Japanese              ",
			"sw" => 	"Kiswahili             ",
			"ko" => 	"Korean                ",
			"lv" => 	"Latvian               ",
			"lt" => 	"Lithuanian            ",
			"ms" => 	"Malay                 ",
			"mt" => 	"Maltese               ",
			"no" => 	"Norwegian             ",
			"fa" => 	"Persian               ",
			"pl" => 	"Polish                ",
			"pt" => 	"Portuguese            ",
			"ro" => 	"Romanian              ",
			"ru" => 	"Russian               ",
			"sr-Cyrl" =>"Serbian (Cyrillic)    ",
			"sr-Latn" =>"Serbian (Latin)       ",
			"sk" => 	"Slovak                ",
			"sl" => 	"Slovenian             ",
			"es" => 	"Spanish               ",
			"sv" => 	"Swedish               ",
			"th" => 	"Thai                  ",
			"tr" => 	"Turkish               ",
			"uk" => 	"Ukrainian             ",
			"ur" => 	"Urdu                  ",
			"vi" => 	"Vietnamese            ",
			"cy" => 	"Welsh                 ",
			"yua" => 	"Yucatec Maya          ",
    );

	private static function isAviableCode($code){
		return array_key_exists($code,self::$aviableLanguages);
	}



	public static function get($text,$to,$from=NULL){
		$text = trim($text);
		$to = trim($to);
		$from = trim($from);
		
		if( $text && $to ){
			
			if( !self::isAviableCode($to) ){ 
				throw new Exception("Codice di lingua non valido");
			}

			$url = self::$endpoint;
			$url .="?text='".urlencode($text)."'&to=".$to;
			
			if( $from ){
				if( !self::isAviableCode($from) ){ 
					throw new Exception("Codice di lingua non valido");
				}
				$url .="&from=".$from;
			}
			
			$translate = json_decode(file_get_contents($url));
			if( is_object($translate) ){
				return $translate->translationText;
			}else{
				throw new Exception("Errore nella chiamata");
			}
		}else{
			if( !$to ) throw new Exception("Nessun codice di lingua specificato");
			if( !$text ) throw new Exception("Nessun testo da tradurre specificato");
		}
		
	}



	public static function writePO($key,$value,$file,$mode=FILE_APPEND){
		$string ="msgid \"%s\"\nmsgstr \"%s\"\n\n";
		$string = vsprintf($string,array($key,$value));
		if( file_exists($file) ){
			file_put_contents($file, $string, $mode);
		}

	}

	

}



?>