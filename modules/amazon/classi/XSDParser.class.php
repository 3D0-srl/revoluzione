<?php
class XSDParser{

	//unità di misura
	public static $unitMeasureTypes = array(
		'AreaUnitOfMeasure',
		'BurnTimeUnitOfMeasure',
		'LengthUnitOfMeasure',
		'VolumeUnitOfMeasure',
		'WeightUnitOfMeasure',
		'JewelryLengthUnitOfMeasure',
		'JewelryWeightUnitOfMeasure',
		'DegreeUnitOfMeasure',
		'MemorySizeUnitOfMeasure',
		'FrequencyUnitOfMeasure',
		'AmperageUnitOfMeasure',
		'TimeUnitOfMeasure',
		'BatteryAverageLifeUnitOfMeasure',
		'DataTransferUnitOfMeasure',
		'ResistanceUnitOfMeasure',
		'DateUnitOfMeasure',
		'AssemblyTimeUnitOfMeasure',
		'AgeRecommendedUnitOfMeasure',
		'BatteryPowerUnitOfMeasure',
		'LuminanceUnitOfMeasure',
		'LuminousIntensityUnitOfMeasure',
		'VoltageUnitOfMeasure',
		'WattageUnitOfMeasure',
		'MillimeterUnitOfMeasure',
		'TemperatureRatingUnitOfMeasure',
		'ClothingSizeUnitOfMeasure',
		'PowerUnitOfMeasure'

	);

	public static $excludedFields = array(
		'ProductSubtype', 
		'ColorMap', 
		'SizeMap', 
		'ProductName', 
		'Manufacturer'
	);
	
	//cartelle contenente i file xsd e xml
	public $_dirXSD = "xsd/Product";
	public $_dirXML = "xml/Product";
	



	//costruttore
	function __construct($config = array()){
		
		if( $config['_dirXSD'] ){
			$this->_dirXSD = $config['_dirXSD'];
		}

		if( $config['_dirXML'] ){
			$this->_dirXML = $config['_dirXML'];
		}
		
	}


	//metodo che prende le unità di misura 
	public static function getUnitMeasureTypes(){
			$units = self::$unitMeasureTypes;
			$types = self::getSimpleTypes();
			//debugga($types);exit;
			foreach($units as  $v){
				$toreturn[$v] = $types[$v];
			}

			return $toreturn;

	}
	

	

	public static function loadType(){
		$xml = "xsd/amzn-base.xsd";
		$data = file_get_contents($xml);
		debugga($data);exit;

	}

	public static function parseSimpleType($input=NULL){
		if( $input ){

			//prendo il nome del tipo
			$name = $input['@attributes']['name'];
			
			$data['name'] = $name;
			//prendo le restrizioni
			if( okArray($input['restriction']) ){
				

				if( $input['restriction']['@attributes'] ){
					$data['base'] = $input['restriction']['@attributes']['base'];
				}
				//debugga($input);exit;
				//prendo la massima lunghezza
				if( $input['restriction']['maxLength'] ){
					$data['maxLength'] = $input['restriction']['maxLength']['@attributes']['value'];
				}
				
				//prendo la minima lunghezza
				if( $input['restriction']['minLength'] ){
					$data['minLength'] = $input['restriction']['minLength']['@attributes']['value'];
				}

				//prendo la minima lunghezza
				if( $input['restriction']['pattern'] ){
					$tipi[$name]['pattern'] = $input['restriction']['pattern']['@attributes']['value'];
				}

				//prendo la minima lunghezza
				if( $input['restriction']['totalDigits'] ){
					$data['totalDigits'] = $input['restriction']['totalDigits']['@attributes']['value'];
				}

				//prendo la minima lunghezza
				if( $input['restriction']['enumeration'] ){
					$enumerations = array();
					if(!$input['restriction']['enumeration'][0]){
						$enumerations[0] = $input['restriction']['enumeration'];
					}else{
						$enumerations = $input['restriction']['enumeration'];
					}
					foreach($enumerations as $input1){
						$data['values'][] = $input1['@attributes']['value'];
					}
				}
			}
			

		}
		return $data;

	}


	public static function getSimpleTypes(){
		$obj = new XSDParser();
		$file = 'amzn-base';
		
		$xml = $obj->_dirXSD."/".$file.".xsd";
		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = true;
		$doc->load($xml);
		$file_xml = $obj->_dirXML."/".$file.'.xml';
		if( !file_exists( $obj->_dirXML."/".$file.'.xml') ){
			$doc->save($file_xml);
		}
		

		
		
		
		$myxmlfile = file_get_contents($file_xml);
		
		
		$parseObj = str_replace($doc->lastChild->prefix.':',"",$myxmlfile);
		
		$xml_string= simplexml_load_string($parseObj);
		
		
		
		$json = json_encode($xml_string);
		$data = json_decode($json, true);
		
		$types = array();
		foreach($data['simpleType'] as $v){
			$data = self::parseSimpleType($v);
			$tmp = $data;
			unset($tmp['name']);
			$types[$data['name']] = $tmp;
		}

		return $types;
	}

	public static function getComplexTypes(){
		$obj = new XSDParser();
		$file = 'amzn-base';
		$xml = $obj->_dirXSD."/".$file.".xsd";
		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = true;
		$doc->load($xml);
		$file_xml = $obj->_dirXML."/".$file.'.xml';
		if( !file_exists( $obj->_dirXML."/".$file.'.xml') ){
			$doc->save($file_xml);
		}
		
		$myxmlfile = file_get_contents($file_xml);
		
		
		$parseObj = str_replace($doc->lastChild->prefix.':',"",$myxmlfile);
		
		$xml_string= simplexml_load_string($parseObj);
		
		
		
		$json = json_encode($xml_string);
		$data = json_decode($json, true);
		
		$types = array();
		
		foreach($data['complexType'] as $v){
			
			if( $v['simpleContent']['extension'] ){
				$list[$v['@attributes']['name']] =  array(
					'base' => $v['simpleContent']['extension']['@attributes']['base']
				);
				//debugga($v);exit;
			}else{
				//$list[$v['@attributes']['name']] = $v['@attributes']['name'];
			}
			//$data = self::parseSimpleType($v);
			//$types[$data['name']] = $data;
		}
		

		return $list;

	}

	public static function loadBase($config=array()){
		$obj = new XSDParser($config);
		$file = 'amzn-base';
		$xml = $obj->_dirXSD."/".$file.".xsd";
		
		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = true;
		$doc->load($xml);
		
		$file_xml = $obj->_dirXML."/".$file.'.xml';
		$doc->save($file_xml);
		

		
		
		
		$myxmlfile = file_get_contents($file_xml);
		
		
		$parseObj = str_replace($doc->lastChild->prefix.':',"",$myxmlfile);
		
		$xml_string= simplexml_load_string($parseObj);
		
		
		
		$json = json_encode($xml_string);
		$data = json_decode($json, true);
		
		$tipi = array();
		foreach($data['simpleType'] as $v){
			$data = self::parseSimpleType($v);
			$tipi[$data['name']] = $data;
		}

		debugga($tipi);exit;

		exit;

	}

	public static function load($file,$config = array()){
		
		$obj = new XSDParser($config);

		$xml = $obj->_dirXSD."/".$file.".xsd";
		
		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = true;
		$doc->load($xml);
		
		$file_xml = $obj->_dirXML."/".$file.'.xml';
		$doc->save($file_xml);
		

		
		
		
		$myxmlfile = file_get_contents($file_xml);
		
		
		$parseObj = str_replace($doc->lastChild->prefix.':',"",$myxmlfile);
		
		$xml_string= simplexml_load_string($parseObj);
		
		
		
		$json = json_encode($xml_string);
		$data = json_decode($json, true);
		
		
		debugga($data);exit;
		if( okArray($data) ){
			if( array_key_exists(0,$data['element']) ){
				foreach($data['element'] as $k => $v){
					if( $data['element'][$k]['@attributes']['name'] == $file ){
						$data = $v;
					}else{
						
						$data_ref[$v['@attributes']['name']] = $obj->createField($v);
					}
				}
			}else{
				$data = $data['element'];
				$data_ref[$data['element']['@attributes']['name']] = $obj->createField($data['element']);
			}
		}
		
		
		$GLOBALS['dentro'] = 1;
		$data = $obj->createField($data,$data_ref);
		
		//debugga($data);exit;
		return $data;
			
	}



	function createField($dati,$dati_ref=NULL){
		
		//debugga($dati);exit;

		$tmp = $dati;
		$iter = 0;
		foreach($dati['@attributes'] as $k => $t){
			$toreturn['check'][$k] = $t;
		
		}

		if( $GLOBALS['dentro'] ){
			//debugga($dati);exit;
		}
		
		if( okArray($dati['complexType']) ){
			if( okArray($dati['complexType']['sequence']) || okArray($dati['complexType']['choice']) ){
				if( $dati['complexType']['sequence'] ){
					$toreturn['check']['complexType'] = 'sequence';
					$elements = $dati['complexType']['sequence']['element'];
				}else{
					$toreturn['check']['complexType'] = 'choice';
					$elements = $dati['complexType']['choice']['element'];

				}

				
		
				

				if( $elements['@attributes'] || count($elements) == 1  ){
					$tmp = $elements;
					unset($elements);
					$elements[0] = $tmp;
				}
				
				
				
				
				foreach( $elements as $v){

					if( $GLOBALS['dentro'] ){
						//debugga($v);exit;
					}
					
					if( $v['complexType'] /*&& !$v['complexType']['choice']['element']['@attributes']['ref'] && !$v['complexType']['sequence']['element']['@attributes']['ref']*/){
						
						if(	okArray($dati_ref) ){
							$GLOBALS['dentro'] = 1;
						}	
					
						$res = $this->createField($v,$dati_ref);

						if( okArray($dati_ref) ){
							//debugga($res);exit;
						}
						
						$toreturn['fields'][$v['@attributes']['name']] = $res;
					}else{
						if( $GLOBALS['dentro'] ){
							//debugga($elements);exit;
						}
						
						if( $v['@attributes']['ref'] ){

							

							if( $dati_ref[$v['@attributes']['ref']] ){
								$toreturn['fields'][$v['@attributes']['ref']] = $dati_ref[$v['@attributes']['ref']];
							}else{
								$toreturn['fields'][$v['@attributes']['ref']]['check'] = $v['@attributes'];
							}
							
						}

						if( $v['@attributes']['name'] ){
							$toreturn['fields'][$v['@attributes']['name']]['check'] = $v['@attributes'];

							
							//unset($toreturn['fields'][$v['@attributes']['name']]['check']['name']);
							
							//se il campo è di tipo semplice
							if( $v['simpleType']['restriction'] ){
								if( okArray($v['simpleType']['restriction']['@attributes']) ){
									foreach( $v['simpleType']['restriction']['@attributes'] as $k2 => $t2){
										
										$toreturn['fields'][$v['@attributes']['name']]['check'][$k2] = $t2;
									}
								}
								if( okArray($v['simpleType']['restriction']) ){
									
									//valori ammessi
									if( okArray($v['simpleType']['restriction']['enumeration']) ){
										foreach( $v['simpleType']['restriction']['enumeration'] as $k2 => $t2){
											$toreturn['fields'][$v['@attributes']['name']]['values'][] = $t2['@attributes']['value'];
										}
									}

									//valori_ammessi
									if( $v['simpleType']['restriction']['minInclusive'] ){
										$toreturn['fields'][$v['@attributes']['name']]['check']['minValue'] = $v['simpleType']['restriction']['minInclusive'];
									}

									if( $v['simpleType']['restriction']['maxInclusive'] ){
										$toreturn['fields'][$v['@attributes']['name']]['check']['maxValue'] = $v['simpleType']['restriction']['maxInclusive'];
									}

									if( $v['simpleType']['restriction']['maxLength'] ){
										$toreturn['fields'][$v['@attributes']['name']]['check']['maxLength'] = $v['simpleType']['restriction']['maxLength']['@attributes']['value'];
									}
								}

							}
							

							//se il campo è di tipo composto

						}
					}
				}
			}
			
		
		}

		
		
		return $toreturn;
		
	}

	public static function getCategories($config = array()){
		
		$obj = new XSDParser($config);
		$list = scandir($obj->_dirXSD);
		
		
		foreach($list as $l){
			if( preg_match('/xsd/',$l)){
				$toreturn[] = preg_replace('/\.xsd/','',$l);
			}
		}

		return $toreturn;
	}
	


	public static function getTypeValue($type){
		switch( $type ){
			//case ''
		}
	}

	function createCheck(){
		

	}


}



?>