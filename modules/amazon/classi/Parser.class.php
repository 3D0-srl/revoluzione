<?php
class AmazonParserXSD{
	private static $tmp_dir = '/tmp';
	
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
	
	private static $xmlStr  = array('string', 'token', 'normalizedString');
    private static $xmlNum  = array(
        'byte',
        'decimal',
        'int',
        'integer',
        'negativeInteger',
        'nonNegativeInteger',
        'nonPositiveInteger',
        'positiveInteger',
        'short',
        'unsignedLong',
        'unsignedInt',
        'unsignedShort',
        'unsignedByte'
    );
    private static $xmlDate = array(
        'date',
        'time',
        'dateTime',
        'duration',
        'gDay',
        'gMonth',
        'gMonthDay',
        'gYear',
        'gYearMonth'
    );


	private static $xmlBool = array('boolean');

    private static $xmlOther = array('anyURI', 'base64Binary');
	
	
	
	//metodo che prende le unità di misura 
	public static function getUnitMeasureTypes($xsd_file=NULL,$file_xml=NULL){
			
			if( file_exists($xsd_file) ){
				$file_tmp = 'unitMeasures.json';
				if( file_exists("cache/{$file_tmp}") ){
					return json_decode(file_get_contents("cache/{$file_tmp}"));
				}
				$units = self::$unitMeasureTypes;
				$types = self::getSimpleTypes($xsd_file,$file_xml);
				//debugga($types);exit;
				foreach($units as  $v){
					$toreturn[$v] = $types[$v];
				}
				file_put_contents("cache/{$file_tmp}",json_encode($toreturn));
			}
			return $toreturn;
	}


	//metodo che restituisce i tipi di sati semplici e le lore estensioni
	public static function loadTypes($xsd_file=NULL){
		
		if( file_exists($xsd_file) ){
			$file_tmp = 'types.json';
			if( file_exists("cache/{$file_tmp}") ){
				return json_decode(file_get_contents("cache/{$file_tmp}"),true);
			}
			$tmp = uniqid();
			$file_xml = self::$tmp_dir."/".$tmp.".xml";

			$all_fields = array();

			$simple_types = self::getSimpleTypes($xsd_file,$file_xml);
			$all_fields = $simple_types;


			$complex_types = self::getComplexTypes($xsd_file,$file_xml);
			
			
			if( okArray($complex_types) ){
				foreach($complex_types as $k => $v){
					
					if( $simple_types[$v['base']]){
						foreach( $simple_types[$v['base']] as $k1 => $v1 ){
							if( $k1 != 'name'){
								$complex_types[$k][$k1] = $v1;
							}
						}
					}
					
				}
				$all_fields = array_merge($all_fields,$complex_types);
			}
			foreach($all_fields as $k => $v){
				
				if( okArray($v['values']) ){
					$all_fields[$k]['html_type'] = 'select';
				}else{
					$all_fields[$k]['html_type'] = 'input';
					$all_fields[$k]['input_type'] = self::getHtmlEquivalent($v['base']);
				}
			}
			
			
			file_put_contents("cache/{$file_tmp}",json_encode($all_fields));
			return $all_fields;
		}
	}


	//metodo che prende da un file XSD i tipi di dati semplici
	public static function getSimpleTypes($xsd_file=NULL,$file_xml=NULL){
	
		if( file_exists($xsd_file) ){
			
			$tmp = uniqid();
			$doc = new DOMDocument();
			$doc->preserveWhiteSpace = true;
			$doc->load($xsd_file);
			
			if( !$file_xml || !file_exists($file_xml) ){
				$file_xml = self::$tmp_dir."/".$tmp.".xml";
				if( !file_exists( $file_xml) ){
					$doc->save($file_xml);
				}
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
				//unset($tmp['name']);
				$types[$data['name']] = $tmp;
			}
			//debugga($types);exit;
			return $types;
			
		}	

	}


	public static function getComplexTypes($xsd_file=NULL,$file_xml=NULL){
		if( file_exists($xsd_file) ){
			
			$tmp = uniqid();
			$doc = new DOMDocument();
			$doc->preserveWhiteSpace = true;
			$doc->load($xsd_file);

			if( !$file_xml || !file_exists($file_xml)){
				$file_xml = self::$tmp_dir."/".$tmp.".xml";
				if( !file_exists( $file_xml) ){
					$doc->save($file_xml);
				}
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
					
				}else{
					//$list[$v['@attributes']['name']] = $v['@attributes']['name'];
				};
			}
			return $list;
			
		}	
		

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
	

	 public static function getHtmlEquivalent($type)
    {
        if (self::isString($type)) {
            return 'text';
        }

        if (self::isNumeric($type)) {
            return 'number';
        }

        if (self::isDate($type)) {
            return str_replace('xsd:', '', $type);
        }

        if (self::isOther($type)) {
            return 'text';
        }

        if (self::isBoolean($type)) {
            return 'boolean';
        }

        return $type;
    }


	 public static function isString($type)
    {
         return in_array($type, self::$xmlStr);
    }

    /**
     * Check is a value is a Xml Numeric Type
     * @param type $type
     * @return True if the type is found, False otherwise
     */
    public static function isNumeric($type)
    {
   
        return in_array($type, self::$xmlNum);
    }

    /**
     * Check is a value is a Xml Date Type
     * @param type $type
     * @return True if the type is found, False otherwise
     */
    public static function isDate($type)
    {
   
        return in_array($type, self::$xmlDate);
    }

    /**
     * Check is a value is a Xml Type not considered in previous functions
     * @param type $type
     * @return True if the type is found, False otherwise
     */

    public static function isOther($type)
    {
     
        return in_array($type, self::$xmlOther);
    }

    /**
     * Check is a value is a Xml Type Boolean
     * @param type $type
     * @return True if the type is found, False otherwise
     */

    public static function isBoolean($type)
    {
      
        return in_array($type, self::$xmlBool);
    }



}



?>