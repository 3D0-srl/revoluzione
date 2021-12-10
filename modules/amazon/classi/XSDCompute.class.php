<?php
class XSDCompute{
	

	//percorso dei file XSD relativi agli universi
	private static $path_xsd_product = "xsd/Product";

	//ptah del file amzn-base di amazon
	private static $path_xsd_amzn_base = "xsd/amzn-base.xsd";
	

	//percorso dei file temporanei
	private static $tmp_dir = '/tmp';

	//percorso dei file in cache
	private static $cache_dir = 'cache';
	
	//abilita il caching dei dati
	private static $cache = false;
	

	//abilita il la traduzione
	private static $translate = true;


	//linguaggio di default per la traduione dei testi
	private static $lang = 'it';

	

	private static $mappingThemes = array(
		'SizeColor' => 'Size-Color',
		'ColorSize' => 'Color-Size'
	);


	
	
	//restituisce gli univerisi di Amazon
	public static function getUniverses(){
		$list = scandir(self::$path_xsd_product);
		if( okArray($list) ){
			foreach($list as $v){
				if( $v != '.' && $v != '..' && $v != 'amzn-base.xsd'){
					$toreturn[] = $v;
				}
			}
		}
		return $toreturn;
	}
	
	
	//restituisce gli univerisi di Amazon nella forma chiave valore
	/*
		identificativo universo => nome dell'universo


	*/
	public static function getUniversesWithLabel(){
		$list = self::getUniverses();
		if( okArray($list) ){
			foreach($list as $v){
				$v = preg_replace('/\.(.*)/','',$v);
				$toreturn[$v] = self::getLabel($v);
			}
		}
		uasort($toreturn,function($a,$b){
			if ($a == $b) {
				return 0;
			}
			return ($a < $b) ? -1 : 1;
		});
		return $toreturn;

	}
	

	//restituisce le tipologie di prodotto presenti nell'universo
	public static function getProductTypesFromUniverse($universe=NULL){
		if( $universe ){
			
			//file in cui i dati verranno salvati per il caching
			$file_cached = self::$cache_dir."/ProductType_".$universe.".json";
			

			//se la cache è abilitata allora prelevo i dati salvati qualora presenti
			if( self::$cache ){
				if( file_exists($file_cached) ){
					return json_decode(file_get_contents($file_cached),true);
				}
			}

			//prendo il file XSD di riferimento
			$file = self::$path_xsd_product."/".$universe.".xsd";
			

			if( file_exists($file) ){

				//creo il file XML a partire dal file XSD
				$xml = self::createXML($file);
				
				if( is_array($xml) ){
					
					
					//prendo il pimo elmento del file XSD cioè quello con name $universe
					if( array_key_exists(0,$xml['element']) ){
						$element = $xml['element'][0];
					}else{
						$element = $xml['element'];
					}
					
					if( $element['@attributes']['name'] == $universe ){
						//prendo i valori di ProductType
						//caso in cui il ProductType è di tipo composto
						$tmp_array = $element['complexType']['sequence']['element'][0]['complexType']['choice'];
						if( !okArray($tmp_array) ){
							//caso in cui il ProductType è di tipo semplice
							$tmp_array = $element['complexType']['sequence']['element'][0]['simpleType']['restriction']['enumeration'];
						}
						
						if( okArray($tmp_array) ){
							foreach($tmp_array as $elements){
								//debugga($elements);exit;
								//caso in cui ci sta solo un tipo di Prodotto nell'universo
								if( $elements['@attributes'] ){
									if( $elements['@attributes']['ref'] ){
										$toreturn[] = $elements['@attributes']['ref'];
									}
									if( $elements['@attributes']['name'] ){
										$toreturn[] = $elements['@attributes']['name'];
									}
									if( $elements['@attributes']['value'] ){
										$toreturn[] = $elements['@attributes']['value'];
									}
								}
								foreach($elements as $v){
									$attributes =$v['@attributes'];
									
									if( $attributes['ref'] ){
										$toreturn[] = $attributes['ref'];
									}

									if( $attributes['name'] ){
										$toreturn[] = $attributes['name'];
									}

								}
							}
						}else{
							$toreturn[] = $universe;
						}
						

					}
					
					file_put_contents($file_cached,json_encode($toreturn));
					return $toreturn;
				}
			}
		}
	}

	//restituisce  le tipologie di prodotto presenti nell'universo nella forma chiave valore
	/*
		identificativo tipologia prodotto => nome tipologia prodotto


	*/

	public static function getProductTypesWithLabelFromUniverse($universe=NULL){
		$toreturn = array();
		if( $universe ){
			$productTypes = self::getProductTypesFromUniverse($universe);
		
			if( okArray($productTypes) ){
				foreach($productTypes as $v){
					
					$toreturn[$v] = self::getLabel($v);
				}
				uasort($toreturn,function($a,$b){
					if ($a == $b) {
						return 0;
					}
					return ($a < $b) ? -1 : 1;
				});
			}
		}
		return $toreturn;
	}

	
	//restituisce gli attributi generali di un universo
	public static function getGeneralAttributesFromUniverse($universe=NULL){
		
		if( $universe ){
			//prendo i tipi definiti per amazon
			$types = AmazonParserXSD::loadTypes(self::$path_xsd_amzn_base);
			
			//file in cui i dati verranno salvati per il caching
			$file_cached = self::$cache_dir."/GeneralAttrinutes_".$universe.".json";
			
			//se la cache è abilitata allora prelevo i dati salvati qualora presenti
			if( self::$cache ){
				if( file_exists($file_cached) ){
					return json_decode(file_get_contents($file_cached),true);
				}
			}

			//prendo il file XSD di riferimento
			$file = self::$path_xsd_product."/".$universe.".xsd";
			
			
			if( file_exists($file) ){
				//creo il file XML a partire dal file XSD
				$xml = self::createXML($file);
				if( is_array($xml) ){
					if( array_key_exists(0,$xml['element']) ){
						$elements = $xml['element'][0]['complexType']['sequence']['element'];
					}else{
						$elements = $xml['element']['complexType']['sequence']['element'];
					}
					
					//verifico se sono definiti altri attributi di tupo semplice
					foreach($elements as $k => $v){
						$attributes =$v['@attributes'];

						if( $v['simpleType'] ){
							
							$data = AmazonParserXSD::parseSimpleType($v['simpleType']);

							
							foreach($attributes as $k => $v){
								$data[$k] = $v;
							}

							$data['type']['input_type'] = AmazonParserXSD::getHtmlEquivalent($data['b']);
							
							if( okArray($data['values']) ){
								$data['type']['html_type'] = 'select';
							}else{
								$data['type']['html_type'] = 'input';
							}
							
							
							//$toreturn[$attributes['name']]['type'] = $tmp;
							$toreturn[$attributes['name']] = $data;
						}else{

							//escludo per il moneto gli attributi di tipo composto
							if( $attributes['ref'] ) continue;
							if( $attributes['name'] == 'ProductType' ) continue;

	
							
							if( $attributes['name'] != $universe ){
								$toreturn[$attributes['name']] = $attributes;
								
								
								//se è un tipo di quelli base
								if( $types[$attributes['type']] ){
									$toreturn[$attributes['name']]['type'] = $types[$attributes['type']];
								}else{
									
									$tmp = array(
										'base' => $attributes['type'],
										'input_type' => AmazonParserXSD::getHtmlEquivalent($attributes['type'])
									);
									if( okArray($tmp['values']) ){
										$tmp['html_type'] = 'select';
									}else{
										$tmp['html_type'] = 'input';
									}
									
									
									$toreturn[$attributes['name']]['type'] = $tmp;
								}

								
								
							}
						}
						
					}
					
					file_put_contents($file_cached,json_encode($toreturn));
					return $toreturn;
				}
			}

		}
	}
	

	public static function getGeneralAttributesWithLabelFromUniverse($universe=NULL){
		$toreturn = array();
		if( $universe ){
			$list = self::getGeneralAttributesFromUniverse($universe);
			if( okArray($list) ){
				foreach($list as $k => $v){
					$row = array(
						'name' => $v['name'],
						'html_type' => $v['type']['html_type'],
						'label' => self::getLabel($v['name'])
					);
					if(  $v['type']['html_type'] == 'input' ){
						if( $v['type']['input_type'] == 'boolean' ){
							$row['values'] = array(
								0 => 'FALSE',
								1 => 'TRUE'
							);
							$row['html_type'] = 'select';
						}else{
							$row['type'] =  $v['type']['input_type'];
						}
					}

					if( $v['type']['html_type'] == 'select' ){
						foreach($v['values'] as $t){
							$row['values'][$t] = $t;
						}
					}
					if( $v['minOccurs'] > 0 ){
						$row['required'] = 1;
					}
					$toreturn[] = $row;
				}

				uasort($toreturn,function($a,$b){
					if ($a['label'] == $b['label']) {
						return 0;
					}
					return ($a['label'] < $b['label']) ? -1 : 1;
				});
				
			}

		}
		return $toreturn;
	}
	


	public static function getAttributesFromProductType($universe,$productType=NULL){
		$toreturn = array();
		if( $universe && $productType){
			//prendo i tipi definiti per amazon
			$types = AmazonParserXSD::loadTypes(self::$path_xsd_amzn_base);
			
			//file in cui i dati verranno salvati per il caching
			$file_cached = self::$cache_dir."/ProductTypeAttributes_".$universe."_".$productType.".json";
			
			//se la cache è abilitata allora prelevo i dati salvati qualora presenti
			if( self::$cache ){
				if( file_exists($file_cached) ){
					return json_decode(file_get_contents($file_cached),true);
				}
			}

			//prendo il file XSD di riferimento
			$file = self::$path_xsd_product."/".$universe.".xsd";
			
			
			if( file_exists($file) ){
				//creo il file XML a partire dal file XSD
				$xml = self::createXML($file);
				if( is_array($xml) ){
					$elements_parent = $xml['element'];
					
					if( $universe == $productType ){
						if( $productType == 'Home' ){
							$elements_parent = $elements_parent[0]['complexType']['sequence']['element'][0]['complexType']['choice']['element'];
						}
					}

					
						/*$tmp_array = $element['complexType']['sequence']['element'][0]['complexType']['choice'];
						if( !okArray($tmp_array) ){
							//caso in cui il ProductType è di tipo semplice
							$tmp_array = $element['complexType']['sequence']['element'][0]['simpleType']['restriction']['enumeration'];
						}*/

					foreach($elements_parent as $v){
						if( $v['@attributes']['name'] == $productType ){
							$elements = $v['complexType']['sequence']['element'];
						
							if( okArray($elements) ){
								foreach($elements as $k => $v){
									$attributes =$v['@attributes'];
									if( $v['complexType'] ) continue;
									
									if( $v['simpleType'] ){
										
										$data = AmazonParserXSD::parseSimpleType($v['simpleType']);

										
										foreach($attributes as $k => $v){
											$data[$k] = $v;
										}
										
										$data['type']['input_type'] = AmazonParserXSD::getHtmlEquivalent($data['b']);
										
										if( okArray($data['values']) ){
											$data['type']['html_type'] = 'select';
										}else{
											$data['type']['html_type'] = 'input';
										}
										
										
										//$toreturn[$attributes['name']]['type'] = $tmp;
										$toreturn[$attributes['name']] = $data;
									}else{
										//escludo per il moneto gli attributi di tipo composto
										if( $attributes['ref'] ) continue;
										$toreturn[$attributes['name']] = $attributes;
										
										
										//se è un tipo di quelli base
										if( $types[$attributes['type']] ){
											$toreturn[$attributes['name']]['type'] = $types[$attributes['type']];
										}else{
											
											$tmp = array(
												'base' => $attributes['type'],
												'input_type' => AmazonParserXSD::getHtmlEquivalent($attributes['type'])
											);
											if( okArray($tmp['values']) ){
												$tmp['html_type'] = 'select';
											}else{
												$tmp['html_type'] = 'input';
											}
											
											
											$toreturn[$attributes['name']]['type'] = $tmp;
										}	
										
									}
									
								}
								
								file_put_contents($file_cached,json_encode($toreturn));
								
							}
						}
					}
					
				}
			}
		}
		return $toreturn;
	}

	public static function getAttributesFromProductTypeWithLabel($universe,$productType=NULL){
		$toreturn = array();

		$list = self::getAttributesFromProductType($universe,$productType);
		
		if( okArray($list) ){
			
			foreach($list as $k => $v){
				$row = array(
					'name' => $v['name'],
					'html_type' => $v['type']['html_type'],
					'label' => self::getLabel($v['name'])
				);
				if(  $v['type']['html_type'] == 'input' ){
					
					if( $v['type']['input_type'] == 'boolean' ){
						$row['values'] = array(
								0 => 'FALSE',
								1 => 'TRUE'
						);
						$row['html_type'] = 'select';
					}else{
						$row['type'] =  $v['type']['input_type'];
					}
				}else{
					
					if( $v['type']['html_type'] == 'select' ){
						
						foreach($v['values'] as $t){
							$row['values'][$t] = $t;
						}
					}
				}
				if( $v['minOccurs'] > 0 ){
					$row['required'] = 1;
				}
				$toreturn[] = $row;
			}

			uasort($toreturn,function($a,$b){
				if ($a['label'] == $b['label']) {
					return 0;
				}
				return ($a['label'] < $b['label']) ? -1 : 1;
			});
			
		}

		
		return $toreturn;

	}

	

	public static function getVariationThemes($universe,$productType=NULL){
		$toreturn = array();
		if( $universe && $productType){
			//prendo i tipi definiti per amazon
			$types = AmazonParserXSD::loadTypes(self::$path_xsd_amzn_base);
			
			//file in cui i dati verranno salvati per il caching
			$file_cached = self::$cache_dir."/VariationThemes_".$universe."_".$productType.".json";
			
			//se la cache è abilitata allora prelevo i dati salvati qualora presenti
			if( self::$cache ){
				if( file_exists($file_cached) ){
					return json_decode(file_get_contents($file_cached),true);
				}
			}

			//prendo il file XSD di riferimento
			$file = self::$path_xsd_product."/".$universe.".xsd";
			
			
			if( file_exists($file) ){
				//creo il file XML a partire dal file XSD
				$xml = self::createXML($file);
				if( is_array($xml) ){

					$elements_parent = $xml['element'];
					//debugga($elements_parent);exit;
					if( $universe == $productType ){
						if( $productType == 'Home' ){
							$elements_parent = $elements_parent[0]['complexType']['sequence']['element'][0]['complexType']['choice']['element'];
						}
					}
					foreach($elements_parent as $element){

						if( $element['@attributes']['name'] == $productType ){

							$list = $element['complexType']['sequence']['element'];
							
							foreach($list as $v){
								if( $v['@attributes']['name'] == 'VariationData' ){
									$tmp = $v['complexType']['sequence']['element'];
									foreach($tmp as $t){
										if( $t['@attributes']['name'] == 'VariationTheme'){
											$themes = $t['simpleType']['restriction']['enumeration'];
											
											if( array_key_exists(0,$themes) ){
												foreach($themes as $r){
													
													$toreturn[$r['@attributes']['value']] = $r['@attributes']['value'];
												}
											}else{
												$toreturn[$themes['@attributes']['value']] = $themes['@attributes']['value'];
											}


											foreach($toreturn as $j => $n){
												if( self::$mappingThemes[$n]){
													
													$toreturn[$j] = self::$mappingThemes[$n];
												}
												if (strpos($toreturn[$j], '-')) {
													
													$composition_themes[$toreturn[$j]] = explode('-',$toreturn[$j]);
												}else{
													$composition_themes[$n] = array($n);
												}
											}
											$array['themes'] = $toreturn;
											$array['composition_themes'] = $composition_themes;
											//file_put_contents($file_cached,json_encode($toreturn));
										}else{
											$array['attributes'][] = array(
												'name' => $t['@attributes']['name'],
												 'type' => array(
														'input_type' => 'text',
														'html_type' => 'input',
													),
												'minOccurs' => $t['@attributes']['minOccurs'],
												);
										}
									}
								}
							}
						}
					}
					
				}
			}
		}
		
		file_put_contents($file_cached,json_encode($array));
		
		return $array;
	}


	//traduce il nome di un attributo
	public static function getLabel($name){
		
		if( self::$translate ){
			$tmp = __module('amazon',$name);
			if( !$tmp ){
				$text = trim(preg_replace("/([A-Z][a-z_])/"," $1",$name));
				try {
					$translate = Translate::get($text,'it','en');
				} catch (Exception $e) {
					$translate = $text;
				}
				Translate::writePO($name,$translate,'locale/'.self::$lang.'/LC_MESSAGES/messages.po');
				
				return $translate;
			}else{
				return $tmp;
			}

		}else{
			$text = trim(preg_replace("/([A-Z][a-z_])/"," $1",$name));
			return $text;
		}
		
	}






	
	public static function createXML($xsd_file=NULL){
		
		if( file_exists($xsd_file) ){

			$tmp = uniqid();
			$doc = new DOMDocument();
			$doc->preserveWhiteSpace = true;
			$doc->load($xsd_file);

			
			$file_xml = self::$tmp_dir."/".$tmp.".xml";
			if( !file_exists( $file_xml) ){
				$doc->save($file_xml);
			}
			
			

			$myxmlfile = file_get_contents($file_xml);
			$parseObj = str_replace($doc->lastChild->prefix.':',"",$myxmlfile);
			$xml_string= simplexml_load_string($parseObj);
			$json = json_encode($xml_string);
			$data = json_decode($json, true);
			return $data;
		}else{
			return '';
		}
	}
	
}


?>