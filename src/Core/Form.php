<?php
namespace Marion\Core;
use Marion\Core\Marion;
use \wlString;

class Form {

	 public $codice;
	 public $action;
	 public $url;
	 public $nome;
	 public $commenti;
	 public $method;
	 public $exists;
	 public $campi_aggiunti;
	 public $campi;
	 //public function get($n)
	 
	 
	 
	 public function __construct($form=null){
	   
		$database = Marion::getDB();
		
		//debugga($database);exit;
		$this->campi_aggiunti = array();
		if( $form ){
			if(is_numeric($form)){
				$form = $database->select('*','form',"codice={$form}");
			}else{
				$form = $database->select('*','form',"nome='{$form}'");
			}
			
			if(okArray($form))
			{
				$form = $form[0];
				foreach ($form  as $k => $v){
					$this->$k=$v;
				}
				$this->exists = true;
			}else{
				$this->exists = false;
			}
		}
	 } 
	
	 public function addElements($campi){
		 foreach($campi as $k => $v){
			 $this->campi_aggiunti[$k] = $v;
		 }
		 
		 
	 }


	 public static function delete($form){
		$database = Marion::getDB();
		if(is_numeric($form)){
			$form = $database->select('*','form',"codice={$form}");
		}else{
			$form = $database->select('*','form',"nome='{$form}'");
		}
		if( okArray($form) ){
			$form = $form[0];
			$campi = $database->select('codice','form_campo',"form={$form['codice']}");
			if( okArray($campi) ){
				foreach($campi as $v){
					$lista_campi[] = $v['codice'];
					$valori = $database->select('codice','form_valore',"campo={$v['codice']}");
					//debugga($database->lastquery);exit;
					if( okArray($valori) ){
						
						foreach($valori as $val){
							$lista_valori[] = $val['codice'];
						}
					}
				}
			}
			

		}
		
	 
		if( okArray($lista_valori) ){
			foreach($lista_valori as $v){
				$database->delete('form_valore',"codice={$v}");
			}
		}

		if( okArray($lista_campi) ){
			foreach($lista_campi as $v){
				$database->delete('form_campo',"codice={$v}");
			}
		}

		$database->delete('form',"codice={$form['codice']}");

	}
	 
	 
	 


	 public function getFields($ctrl=null){
		$database = Marion::getDB();
		
		$update = $this->campi_aggiunti;
		 
		$campi = $database->select('*','form_campo',"form={$this->codice} and attivo order by ordine asc");
		

		
		
		
	
		$toreturn = array();
		if(okArray($campi)){
			
			foreach( $campi as $c){
				unset($c['codice_php']);
				unset($c['attivo']);
				unset($c['globale']);
				unset($c['form']);
				$codice = $c['codice'];
				unset($c['codice']);
				
				$c['default'] = $c['default_value'];
				unset($c['default_value']);
				
				if($c['type']){
					$type = self::getType($c['type']);
					$c['type'] = $type[0]['etichetta'];
				}
				if($c['tipo']) {
					$tipo = self::getTipi($c['tipo']);
					$c['tipo'] = $tipo[0]['valore'];
				}
				
				

				if($c['post_function']){
					$list_post_function = explode(',',$c['post_function']);
					
					if(okArray($list_post_function)){
						foreach($list_post_function as $function){
							if( function_exists($function) ){
								$c['post_function_array'][] = $function;
							}
						}
					}

				}
				if($c['pre_function']){
					$list_pre_function = explode(',',$c['pre_function']);
					
					if(okArray($list_pre_function)){
						foreach($list_pre_function as $function){
							if( function_exists($function) ){
								$c['pre_function_array'][] = $function;
							}
						}
					}

				}
				
				if( !$c['type'] == 'checkbox' ){
					unset($c['unique_value']);
				}
				
				if( $c['gettext'] == 1 ){
					//debugga('qua');exit;
					$c['etichetta'] = $GLOBALS['gettext']->strings[$c['etichetta']];
					unset($c['gettext']);
				}
				
				if( $c['type'] == 'textarea' && $c['tipo_textarea']){
					$textarea = self::getTipoTextArea($c['tipo_textarea']);
					if( okArray($textarea) ){
						$textarea = $textarea[0];
						if( $textarea['class'] ){
							$c['class'].= " ".$textarea['class'];
						}
					}	
				}elseif( $c['type'] == 'text' && ( $c['tipo'] == 'Date' || $c['tipo'] == 'DateTime' || $c['tipo'] == 'Time')){
					
					if(  $c['tipo'] == 'Date'){
						if( $c['tipo_data'] ){
							$tipoData = self::getTipoData($c['tipo_data']);
							//debugga($tipoData);exit;
							if( okArray($tipoData) ){
								$tipoData = $tipoData[0];
								
								if( $tipoData['class'] ){
									$c['class'] .= " ".$tipoData['class'];
								}
							}
						}
						$c['post_function_array'][] = 'dataIn';
						$c['pre_function_array'][] = 'dataOut';
					
					}elseif($c['tipo'] == 'Time'){
						$tipoData = self::getTipoTime($c['tipo_time']);
						//debugga($tipoData);exit;
						if( okArray($tipoData) ){
							$tipoData = $tipoData[0];
							
							if( $tipoData['class'] ){
								$c['class'] .= " ".$tipoData['class'];
							}
						}
						$c['pre_function_array'][] = 'tempoOut';
						$c['post_function_array'][] = 'tempoIn';
					}elseif($c['tipo'] == 'DateTime'){
						$tipoData = self::getTipoTimestamp($c['tipo_data']);
						//debugga($tipoData);exit;
						if( okArray($tipoData) ){
							$tipoData = $tipoData[0];
							
							if( $tipoData['class'] ){
								$c['class'] .= " ".$tipoData['class'];
							}
						}
						$c['pre_function_array'][] = 'dataTempoOut';
						$c['post_function_array'][] = 'dataTempoIn';

					}
				}elseif( $type = 'file' && $c['tipo_file']){
						if( $c['tipo_file'] == 1 ){
							$c['extensions'] =  unserialize($c['ext_image']);
						}elseif($c['tipo_file'] == 2){
							$c['extensions'] =  unserialize($c['ext_attach']); 			
						}
					
						$tipoFile = self::getTipoFile($c['tipo_file']);
						if( okArray($tipoFile) ){
							$c['tipo_file'] = $tipoFile[0]['valore']; 
						}
						
						
						
				}
				
				unset($c['ext_attach']);
				unset($c['ext_image']);
				
				unset($c['tipo_textarea']);
				unset($c['tipo_data']);
				
				
				
				
				if( $c['type'] == 'select' || $c['type'] == 'radio' || $c['type'] == 'checkbox' || $c['type'] == 'multiselect' ){
					if( $c['tipo_valori'] ){

						if( $c['multilocale']   ){
							$valori = $database->select('*','form_valore',"campo={$codice} and locale = '{$GLOBALS['activelocale']}' order by ordine asc");
						}else{
							$valori = $database->select('*','form_valore',"campo={$codice} order by ordine asc");
						}
						
						if( okArray($valori) ){
							$options = array();
							
							foreach($valori as $v){
								if( $c['type'] == 'select' || $c['type'] == 'multiselect' || $c['type'] == 'checkbox' || $c['type'] == 'radio' ){
									$options[$v['valore']] = $v['etichetta']; 
								}else{
									$options[] = $v['valore']; 
								}
							}
							$c['options'] = $options;
						}
					}else{
						
						$function = $c['function_template'];
						if( $c['function_template'] && is_object($ctrl) && method_exists($ctrl,$c['function_template']) ){
							if( $c['type'] == 'select' || $c['type'] == 'multiselect' || $c['type'] == 'radio' || $c['type'] == 'checkbox'){
								$c['options'] = $ctrl->$function(); 
							}else{
								$c['options'] = array_keys($ctrl->$function());
							} 	
							
						}elseif( function_exists($c['function_template']) ){
							if( $c['type'] == 'select' || $c['type'] == 'multiselect' || $c['type'] == 'radio' ){
								$c['options'] = $function(); 
							}else{
								$c['options'] = array_keys($function());
							} 
						}
						
						//$c['origine_dati'] = 'php';
						//$c['function_php'] = $c['function_template'];
					}
				} 
				if( $c['type'] == 'checkbox' ){
					if( $c['unique_value']  && $c['tipo'] == 'Boolean'){
						$c['switch'] = 1;
					}
				}
				unset($c['tipo_valori']);
				unset($c['function_template']);
				if( $c['type'] != 'text' && $c['type'] != 'textarea' ){
					unset($c['checklunghezza']);
					unset($c['lunghezzamin']);
					unset($c['lunghezzamax']);
					//unset($c['multilocale']);
				}
				$toreturn[$c['campo']] = $c;
				
			}
			
			
			
			
		}
		
		if( okArray($update) ){
			foreach( $update as $campo => $array){
				if( okArray($campi) && array_key_exists($campo,$campi) ){
					foreach( $array as $k => $v){
						$toreturn[$campo][$k] = $v;
					}
				}else{
					foreach( $array as $k => $v){
						$toreturn[$campo][$k] = $v;
					}
				}
			}
		}


		
		return $toreturn;
	 }

	

	 function getFieldName($row,$loc=NULL,$basename = 'formdata'){
		switch($row['type']){
			case 'multiselect':
				if( $loc ){
					$name = $basename.'['.$row['campo'].']['.$loc.'][]';
				}else{
					$name = $basename.'['.$row['campo'].'][]';
				}
				break;
			case 'checkbox':
				if( !$row['unique_value']){
					if( $loc ){
						$name = $basename.'['.$row['campo'].']['.$loc.'][]';
					}else{
						$name = $basename.'['.$row['campo'].'][]';
					}
					break;
				}
			default:
				if( $loc ){
					$name = $basename.'['.$row['campo'].']['.$loc.']';
				}else{
					$name = $basename.'['.$row['campo'].']';
				}
				break;
		}

		return $name;
	 }
	

	 public function getInfoAttachment(&$data){
		if( $data['tipo_file'] == 'attachment'){
			if( okArray($data['value']) ){
				foreach($data['value'] as $id){
					$attach = Attachment::withId($id);
					if( is_object($attach) ){
						
						$name = explode('.',$attach->filename);
						$ext = $name[count($name)-1];
						$img = 'images/file-icons/512px/'.$ext.".png";
						if( !file_exists($img) ){
							$img = 'images/file-icons/512px/_blank.png';
						}
						$attach->img = $img;
						$data['info'][]= $attach;
					}
				
				}
			}
			
			
		}

		
	 }
	 public function prepareData($data=NULL,$ctrl=null,$basename = 'formdata'){
		$fields = $this->getFields($ctrl);
		//$files = $this->getFilesInput();
		
		if( okArray($this->campi_aggiunti) ){
			if( okArray($fields)){
				$fields = array_merge($this->campi_aggiunti,$fields);
			}else{
				$fields = $this->campi_aggiunti;
			}
		}

		
		
		foreach($fields as $v){
			$etichetta = $v['gettext']? __($v['etichetta']):$v['etichetta'];
			if( isset($v['multilocale']) && $v['multilocale'] ){
				foreach( Marion::getConfig('locale','supportati') as $loc){
					
					$empty = false;
					if( empty($data[$v['campo']][$loc]) && $data[$v['campo']][$loc] != 0 ){
						$empty = true;
					}
					$row = array(
						'name' => $this->getFieldName($v,$loc,$basename),
						'type' => $v['type'],
						'id' => $v['campo']."_".$loc,
						'class' => $v['class'],
						'placeholder' => $v['placeholder'],
						'descrizione' => $v['descrizione'],
						'options' => $v['options'],
						'switch' => $v['switch'],
						'value' => $empty?$v['default']:$data[$v['campo']][$loc],
						'obbligatorio' => $v['obbligatorio'],
						'etichetta' => $etichetta,

					);
					if( $v['type'] == 'file'){

						foreach($v['extensions'] as $v1){
							$row['acceptedFiles'] .= ".{$v1},";
						}
						$row['acceptedFiles'] = preg_replace('/\,$/','',$row['acceptedFiles']);
						//$row['extensions'] = json_encode($v['extensions']);
						$row['tipo_file'] = $v['tipo_file'];
						$row['dimension_resize_default'] = $v['dimension_resize_default'];
						$row['dimension_image'] = $v['dimension_image'];
						$this->getInfoAttachment($row);
					}
					if( okArray($v['pre_function_array']) ){
						foreach($v['pre_function_array'] as $function){
							if(method_exists($this,$function)){
								$row['value'] = $this->$function($row['value']);
							}else{
								if( function_exists($function)){
									$row['value'] = $function($row['value']);
								}
							}
						}
					}
					$toreturn[$v['campo']]['id'] = $v['campo'];
					$toreturn[$v['campo']]['locales'][$loc] = $row;
				}
			}else{
				
				$empty = false;
				if( !okArray($data) || (empty($data[$v['campo']]) && $data[$v['campo']] != 0) ){
					$empty = true;
				}
				
				$row = array(
					'name' => $this->getFieldName($v,NULL,$basename),
					'type' => $v['type'],
					'id' => $v['campo'],
					'class' => $v['class'],
					'placeholder' => $v['placeholder'],
					'options' => $v['options'],
					'switch' => $v['switch'],
					'value' =>  $empty?$v['default']:$data[$v['campo']],
					'obbligatorio' => $v['obbligatorio'],
					'etichetta' => $etichetta,
					'descrizione' => $v['descrizione'],

				);
				if( $v['type'] == 'file'){
					
					foreach($v['extensions'] as $v1){
						$row['acceptedFiles'] .= ".{$v1},";
					}
					$row['acceptedFiles'] = preg_replace('/\,$/','',$row['acceptedFiles']);
					$row['tipo_file'] = $v['tipo_file'];

					$this->getInfoAttachment($row);
				}
				if( okArray($v['pre_function_array']) ){
					foreach($v['pre_function_array'] as $function){
						if(method_exists($this,$function)){
							$row['value'] = $this->$function($row['value']);
						}else{
							if( function_exists($function)){
								$row['value'] = $function($row['value']);
							}
						}
					}
				}
				$toreturn[$v['campo']] = $row;
			}
			
		}
		
		
		return $toreturn;
	 }
	 

	 public function checkData($formdata=null,$ctrl=null){
		
		$form_ = $this->getFields($ctrl);
		
		$form = array();
		
		
		foreach( $form_ as $form_campo ){
			
			if( isset($form_campo['multilocale']) && $form_campo['multilocale'] ){
					foreach( $GLOBALS['setting']['default']['LOCALE']['supportati'] as $locale_iter){
							$form[$form_campo['campo']."_{$locale_iter}"] = $form_campo;
							$form[$form_campo['campo']."_{$locale_iter}"]['campo'] = $form_campo['campo']."_{$locale_iter}";
							$form[$form_campo['campo']."_{$locale_iter}"]['etichetta'] = $form_campo['etichetta']." ({$locale_iter})";
							$form[$form_campo['campo']."_{$locale_iter}"]['padre'] = $form_campo['campo'];
							$form[$form_campo['campo']."_{$locale_iter}"]['locale'] = $locale_iter;

							$formdata[$form_campo['campo']."_{$locale_iter}"] = $formdata[$form_campo['campo']][$locale_iter];
							
					}
					unset($formdata[$form_campo['campo']]);
			}else{
				$form[$form_campo['campo']] = $form_campo;
			}
								
						
		}
		
		
		
		unset($formdata['submit']);
		if( okArray($formdata) ){
			foreach($formdata as $k=>$v){
				if( !in_array($k,array_keys($form)) ){
					unset( $formdata[$k] );
				}
			}
		}
		
		
		foreach($form as $k => $v1){
				if( okArray($formdata) && array_key_exists($v1['campo'],$formdata) ){
					$v = $formdata[$v1['campo']];

					//prendo il tipo di dato
					$type = $v1['type'];

					if( $type == 'submit' ){
						$campo_submit = $v1['campo'];
					}

					
					$options = $v1['options'];
					
					
					//controllo dei dati di tipo checkbox
					if( $type == 'checkbox' ){
						if( is_array($v) ){
							foreach($v as $k2=>$v2){
								if(!array_key_exists($v2,$options)){
								//if( !in_array($v2,$options) ){
									unset($v[$k2]);
									
								}else{
									
									if( $v1['tipo'] ){
										if( !$this->verificaValiditaCampo($k2,$v1['tipo']) ){
											unset($v[$k2]);
										}
									}
								}
							}
						}
						
						if($v1['obbligatorio']=='t' && count($v)==0){
								$error= $this->errore_campo_mancante($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'EMPTY_FIELD';
								break;
						}
						$formdata[$v1['campo']] = $v;

					//controllo dei dati di tipo select
					} elseif ( $type == 'select' ){

						// se la variabile e' settata e non e' un valore tra quelli possibili
						if( isset($v) ){
							if(!in_array($v,array_keys($options))){
								$error= $this->errore_campo_non_ammesso($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'ILLEGAL_FIELD';
								break;
							}
						}
						//debugga($v1);exit;
						if( $v1['obbligatorio'] ){
							//se non e' ammesso il valore zero	
							if( !$v1['valuezero']  &&	empty($v)){
								$error= $this->errore_campo_mancante($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'EMPTY_FIELD';
								break;
							}elseif( !$v1['valuezero']  &&	empty($v) && $v !== 0){
								$error= $this->errore_campo_mancante($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'EMPTY_FIELD';
								break;	
							}
							
						}
						$formdata[$v1['campo']] = $v;
					} elseif ( $type == 'multiselect' ){
						// se la variabile e' settata e non e' un valore tra quelli possibili
						if( okArray($v) ){
							$check_muliselect = true;
							foreach($v as $m){

								if(!array_key_exists($m,$options)){
									$check_muliselect = false;
									break;
								}
							}
							if(!$check_muliselect){
								$error= $this->errore_campo_non_ammesso($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'ILLEGAL_FIELD';
								break;
							}
						}
						
						if( $v1['obbligatorio'] ){
							
							if( !okArray($v) ){
								$error= $this->errore_campo_mancante($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'EMPTY_FIELD';
								break;	
							}
						}
						
						$formdata[$v1['campo']] = $v;
						
						
					} elseif( $type == 'radio' ){

						if( $v1['obbligatorio'] && !in_array($v,array_keys($v1['options']))){
							$error= $this->errore_campo_mancante($v1['etichetta']);
							$campo = $v1['campo'];
							$_error_type = 'EMPTY_FIELD';
							break;
						}


					//controllo dei dati di tipo file
					} elseif( $type == 'file' ){

						
						if( $v1['obbligatorio'] && !okArray($v)){
							$error= $this->errore_campo_mancante($v1['etichetta']);
							$campo = $v1['campo'];
							$_error_type = 'EMPTY_FIELD';
							break;
						}else{
							if(okArray($v)){
								$formdata[$v1['campo']] = $v;
							}
						}
					} elseif( $type == 'captcha' ){
						   
							if($v != $v1['value_real']){
								$error= $this->errore_campo_captcha();
								$campo = $v1['campo'];
								break;
							}
					}else{
						//nel caso il campo è di tipo input
						$value = new wlString($v);
						
						if( $value->isEmpty() && $value->isAlpha()){
							
							if($v1['obbligatorio'] ){

								$error= $this->errore_campo_mancante($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'EMPTY_FIELD';
								break;
							}
							
						}else{
							if( isset($v1['tipo']) && $v1['tipo']  ){
								
								if( isset($v1['postfunction']) ){
									$formato = $this->verificaValiditaCampo($this->generaDatiPHP($v1['postfunction'],$v),$v1['tipo']);
								}else{
									$formato = $this->verificaValiditaCampo($v,$v1['tipo']);
									
								}
								
								if( !$formato ){
									$error= $this->errore_campo_non_valido($v1['etichetta'],$v1['tipo']);
									$campo = $v1['campo'];
									$_error_type = 'ILLEGAL_FIELD';
									break;
								}
							}
							if( isset($v1['checklunghezza']) && $v1['checklunghezza']  ){
								if( isset($v1['lunghezzamin']) && $v1['lunghezzamin'] > 0){
	
									$s = new wlString($v);
	
									if( $s->length() <	$v1['lunghezzamin'] ){
										$error= $this->errore_campo_lunghezza($v1['etichetta'],$v1['lunghezzamin'],"min");
										$campo = $v1['campo'];
										break;
									}
								}
								if( isset($v1['lunghezzamax']) && $v1['lunghezzamax'] > 0){
									$s = new wlString($v);
									if( $s->length() >	$v1['lunghezzamax'] ){
										$error= $this->errore_campo_lunghezza($v1['etichetta'],$v1['lunghezzamax'],"max");
										$campo = $v1['campo'];
										break;
									}
								}
							}


						}
						
						$formdata[$v1['campo']] = $v;

					}
				}else{
					if($v1['obbligatorio'] ){
						$error= $this->errore_campo_mancante($v1['etichetta']);
						$campo = $v1['campo'];
						$_error_type = 'EMPTY_FIELD';
						break;
					}
					
				}
				
				

		}

		if( $_SESSION['captcha_text'] ){
			$code = md5($_SESSION['captcha_text']);
			
			$captcha = _obj('Captcha');
			$captcha->destroy($code);
		}

		if(!$error && !$campo){
			foreach( $form as $k => $v){
				
				//se il campo è nullo
				if( $v['ifisnull'] &&  !$formdata[$k]){
					if( $v['ifisnull'] == 1){
						unset($formdata[$k]);
					}elseif($v['ifisnull'] == 2){
						$formdata[$k] = $v['value_ifisnull'];
					}
			   }
				
				if( $v['type'] == 'checkbox' && $v['unique_value'] && !array_key_exists($v['campo'],$formdata) ){
					$formdata[$k] = NULL;
				}
				
				if( $v['type'] == 'checkbox'){

					if( $v['unique_value'] && !array_key_exists($v['campo'],$formdata) ){
						
						$formdata[$k] = $v['value_ifisnull'];
					}
				}
				
				
				
			   
			   if( array_key_exists('postfunction',$v) && $formdata[$k] ){
				   $formdata[$k] = $this->generaDatiPHP($v['postfunction'],$formdata[$k]);
			   }
				
			    
			   if(okArray($v['post_function_array'])){
				   foreach($v['post_function_array'] as $function){
						if(method_exists($this,$function)){
							
							$formdata[$k] = $this->generaDatiPHP($function,$formdata[$k]);
						}elseif(function_exists($function)){
							$formdata[$k] = call_user_func($function,$formdata[$k]);
						}
				   }
				}

			   if( $v['padre'] ){
					$formdata['_locale_data'][$v['locale']][$v['padre']] =  $formdata[$k];
					unset($formdata[$k]); 
			   }
			   if( $v['type'] == 'captcha'){
				   unset($formdata[$k]);
			   }
			}
			
			$formdata[0] = 'ok';
			unset($formdata[$campo_submit]);
		}else{
			
				
			foreach($form as $k => $v){
				if(okArray($v['post_function_array'])){
					foreach($v['post_function_array'] as $function){
						
						if(method_exists($this,$function)){
							$formdata[$k] = $this->generaDatiPHP($function,$formdata[$k]);
						}elseif(function_exists($function)){
							$formdata[$k] = call_user_func($function,$formdata[$k]);
						}
					}
					
				}
			}
			
			
			
			$formdata[0] = 'nak';
			$formdata[1]= $error;
			$formdata[2] = $campo;
			if( $_error_type ){
				$formdata[3] = $_error_type;
			}

			
		}
		
		foreach( $form_ as $chiave => $form_campo ){
			
			if( isset($form_campo['multilocale']) && $form_campo['multilocale'] ){
					foreach( $GLOBALS['setting']['default']['LOCALE']['supportati'] as $locale_iter){
							$formdata[$chiave]["{$locale_iter}"] = $formdata['_locale_data'][$locale_iter][$chiave];
							
							unset($formdata[$chiave."_{$locale_iter}"]);
							
					}
					
			}
								
						
		}
		
		

		return $formdata;
	 }

	
	 
	 public function checkData2($data=null,$ctrl=null){
		
		$form_ = $this->getFields($ctrl);
		
		$form = array();
		$formdata = $data;
		
		foreach( $form_ as $form_campo ){
			
			if( isset($form_campo['multilocale']) && $form_campo['multilocale'] ){
					foreach( $GLOBALS['setting']['default']['LOCALE']['supportati'] as $locale_iter){
							$form[$form_campo['campo']."_{$locale_iter}"] = $form_campo;
							$form[$form_campo['campo']."_{$locale_iter}"]['campo'] = $form_campo['campo']."_{$locale_iter}";
							$form[$form_campo['campo']."_{$locale_iter}"]['etichetta'] = $form_campo['etichetta']." ({$locale_iter})";
							$form[$form_campo['campo']."_{$locale_iter}"]['padre'] = $form_campo['campo'];
							$form[$form_campo['campo']."_{$locale_iter}"]['locale'] = $locale_iter;

							$formdata[$form_campo['campo']."_{$locale_iter}"] = $formdata[$form_campo['campo']][$locale_iter];
							
					}
					unset($formdata[$form_campo['campo']]);
			}else{
				$form[$form_campo['campo']] = $form_campo;
			}
								
						
		}
		
		
		
		unset($formdata['submit']);
		if( okArray($formdata) ){
			foreach($formdata as $k=>$v){
				if( !in_array($k,array_keys($form)) ){
					unset( $formdata[$k] );
				}
			}
		}
		
		$ok_data = array();
		$errors = array();
		
		foreach($form as $k => $v1){
				if( okArray($formdata) && array_key_exists($v1['campo'],$formdata) ){
					$v = $formdata[$v1['campo']];

					//prendo il tipo di dato
					$type = $v1['type'];

					if( $type == 'submit' ){
						$campo_submit = $v1['campo'];
					}

					
					
					$options = $v1['options'];
					
					
					//controllo dei dati di tipo checkbox
					if( $type == 'checkbox' ){
						if( is_array($v) ){
							foreach($v as $k2=>$v2){
								if(!array_key_exists($v2,$options)){
								//if( !in_array($v2,$options) ){
									unset($v[$k2]);
									
								}else{
									
									if( $v1['tipo'] ){
										if( !$this->verificaValiditaCampo($k2,$v1['tipo']) ){
											unset($v[$k2]);
										}
									}
								}
							}
						}
						
						if($v1['obbligatorio']  && count($v)==0){
								$error= $this->errore_campo_mancante($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'EMPTY_FIELD';
								$errors[$v1['campo']] = array(
									'error_code' => $_error_type,
									'error_message' => $error
								);
						}
						if(!$errors[$v1['campo']]){
							$ok_data[$v1['campo']] = $v;
						}
						
						
						

					//controllo dei dati di tipo select
					} elseif ( $type == 'select' ){

						// se la variabile e' settata e non e' un valore tra quelli possibili
						if( isset($v) ){
							
							if(!in_array($v,array_keys($options))){
								$error= $this->errore_campo_non_ammesso($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'ILLEGAL_FIELD';
								$errors[$v1['campo']] = array(
									'error_code' => $_error_type,
									'error_message' => $error
								);
							}
						}
						
						if( $v1['obbligatorio'] ){
							//se non e' ammesso il valore zero	
							if( !$v1['valuezero']  &&	empty($v)){
								$error= $this->errore_campo_mancante($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'EMPTY_FIELD';
								$errors[$v1['campo']] = array(
									'error_code' => $_error_type,
									'error_message' => $error
								);
							}elseif( !$v1['valuezero']  &&	empty($v) && $v !== 0){
								$error= $this->errore_campo_mancante($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'EMPTY_FIELD';
								$errors[$v1['campo']] = array(
									'error_code' => $_error_type,
									'error_message' => $error
								);
							}
							
						}
						if(!$errors[$v1['campo']]){
							$ok_data[$v1['campo']] = $v;
						}
					} elseif ( $type == 'multiselect' ){
						// se la variabile e' settata e non e' un valore tra quelli possibili
						if( okArray($v) ){
							$check_muliselect = true;
							foreach($v as $m){

								if(!array_key_exists($m,$options)){
									$check_muliselect = false;
									break;
								}
							}
							if(!$check_muliselect){
								$error= $this->errore_campo_non_ammesso($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'ILLEGAL_FIELD';
								$errors[$v1['campo']] = array(
									'error_code' => $_error_type,
									'error_message' => $error
								);
							}
						}
						
						if( $v1['obbligatorio'] ){
							
							if( !okArray($v) ){
								$error= $this->errore_campo_mancante($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'EMPTY_FIELD';
								$errors[$v1['campo']] = array(
									'error_code' => $_error_type,
									'error_message' => $error
								);	
							}
						}
						
						if(!$errors[$v1['campo']]){
							$ok_data[$v1['campo']] = $v;
						}
						
						
					} elseif( $type == 'radio' ){

						if( $v1['obbligatorio'] && !in_array($v,array_keys($v1['options']))){
							$error= $this->errore_campo_mancante($v1['etichetta']);
							$campo = $v1['campo'];
							$_error_type = 'EMPTY_FIELD';
							$errors[$v1['campo']] = array(
								'error_code' => $_error_type,
								'error_message' => $error
							);
						}

						if(!$errors[$v1['campo']]){
							$ok_data[$v1['campo']] = $v;
						}


					//controllo dei dati di tipo file
					} elseif( $type == 'file' ){

						
						if( $v1['obbligatorio'] && !okArray($v)){
							$error= $this->errore_campo_mancante($v1['etichetta']);
							$campo = $v1['campo'];
							$_error_type = 'EMPTY_FIELD';
							$errors[$v1['campo']] = array(
								'error_code' => $_error_type,
								'error_message' => $error
							);
						}
						if(!$errors[$v1['campo']]){
							$ok_data[$v1['campo']] = $v;
						}
					
					
					}else{
						//nel caso il campo è di tipo input
						$value = new wlString($v);
						
						if( $value->isEmpty() && $value->isAlpha()){
							
							if($v1['obbligatorio'] ){

								$error= $this->errore_campo_mancante($v1['etichetta']);
								$campo = $v1['campo'];
								$_error_type = 'EMPTY_FIELD';
								$errors[$v1['campo']] = array(
									'error_code' => $_error_type,
									'error_message' => $error
								);
							}
							
						}else{
							if( isset($v1['tipo']) && $v1['tipo']  ){
								
								if( isset($v1['postfunction']) ){
									$formato = $this->verificaValiditaCampo($this->generaDatiPHP($v1['postfunction'],$v),$v1['tipo']);
								}else{
									$formato = $this->verificaValiditaCampo($v,$v1['tipo']);
									
								}
								
								if( !$formato ){
									$error= $this->errore_campo_non_valido($v1['etichetta'],$v1['tipo']);
									$campo = $v1['campo'];
									$_error_type = 'ILLEGAL_FIELD';
									$errors[$v1['campo']] = array(
										'error_code' => $_error_type,
										'error_message' => $error
									);
								}
							}
							if( isset($v1['checklunghezza']) && $v1['checklunghezza']  ){
								if( isset($v1['lunghezzamin']) && $v1['lunghezzamin'] > 0){
	
									$s = new wlString($v);
	
									if( $s->length() <	$v1['lunghezzamin'] ){
										$error= $this->errore_campo_lunghezza($v1['etichetta'],$v1['lunghezzamin'],"min");
										$campo = $v1['campo'];
										$errors[$v1['campo']] = array(
											'error_code' => $_error_type,
											'error_message' => $error
										);
									}
								}
								if( isset($v1['lunghezzamax']) && $v1['lunghezzamax'] > 0){
									$s = new wlString($v);
									if( $s->length() >	$v1['lunghezzamax'] ){
										$error= $this->errore_campo_lunghezza($v1['etichetta'],$v1['lunghezzamax'],"max");
										$campo = $v1['campo'];
										$errors[$v1['campo']] = array(
											'error_code' => $_error_type,
											'error_message' => $error
										);
									}
								}
							}


						}
					
						if(!$errors[$v1['campo']]){
							$ok_data[$v1['campo']] = $v;
						}

					}
				}else{
					if($v1['obbligatorio'] ){
						$error= $this->errore_campo_mancante($v1['etichetta']);
						$campo = $v1['campo'];
						$_error_type = 'EMPTY_FIELD';
						$errors[$v1['campo']] = array(
							'error_code' => $_error_type,
							'error_message' => $error
						);
					}
					
				}
				
				

		}

		if( !okArray($errors) ){
			foreach( $form as $k => $v){
				
				//se il campo è nullo
				if( $v['ifisnull'] &&  !$ok_data[$k]){
					if( $v['ifisnull'] == 1){
						unset($ok_data[$k]);
					}elseif($v['ifisnull'] == 2){
						$ok_data[$k] = $v['value_ifisnull'];
					}
			   }
				
				if( $v['type'] == 'checkbox' && $v['unique_value'] && !array_key_exists($v['campo'],$ok_data) ){
					$ok_data[$k] = NULL;
				}
				
				if( $v['type'] == 'checkbox'){

					if( $v['unique_value'] && !array_key_exists($v['campo'],$ok_data) ){
						
						$ok_data[$k] = $v['value_ifisnull'];
					}
				}
				
				
				
			   
			   if( array_key_exists('postfunction',$v) && $ok_data[$k] ){
				   $ok_data[$k] = $this->generaDatiPHP($v['postfunction'],$ok_data[$k]);
			   }
				
			    
			   if(okArray($v['post_function_array'])){
				   foreach($v['post_function_array'] as $function){
						if(method_exists($this,$function)){
							
							$ok_data[$k] = $this->generaDatiPHP($function,$ok_data[$k]);
						}elseif(function_exists($function)){
							$ok_data[$k] = call_user_func($function,$ok_data[$k]);
						}
				   }
				}

			   if( $v['padre'] ){
					$ok_data['_locale_data'][$v['locale']][$v['padre']] =  $ok_data[$k];
					unset($ok_data[$k]); 
			   }
			   if( $v['type'] == 'captcha'){
				   unset($ok_data[$k]);
			   }
			}
			
			
			unset($formdata[$campo_submit]);
		}


		if( okArray($errors) ){
			$_errors = [];
			$_error_messages = [];
			foreach($errors as $k => $v){
				$_error_messages[] = $v['error_message'];
				$v['field'] = $k;
				$_errors[] = $v;
			}
			$result = array(
				'success' => false,
				'errors' => $_error_messages,
				'error_details' => $_errors
			);
		}else{
			$result = array(
				'success' => false,
				'data' => $ok_data
			);
		}
		
	
		return $result;
	}



	 //funzione che generai dati da PHP. 
	function generaDatiPHP($nome_funzione,$dati=null){
		if( $dati ){
			return $this->$nome_funzione($dati);
		} else {
			return $this->$nome_funzione();
		}

	}	 
	
	 public static function getTipi($codice=''){
		 	$database = Marion::getDB();
			
			if( $codice ){
				$select = $database->select('*','form_tipo',"codice={$codice}");
			}else{
				$select = $database->select('*','form_tipo',"1=1 order by ordine asc, etichetta asc");
			}
			
			if( okArray($select) ){
				return $select;
			}else{
				return false;
			}
	 }
	 
	  public static function getType($codice=''){
		    
            $database = Marion::getDB();
			
			if( $codice ){
				$select = $database->select('*','form_type',"codice={$codice}");
			}else{
				$select = $database->select('*','form_type',"1=1 order by etichetta asc");
			}
			
			if( okArray($select) ){
				return $select;
			}else{
				return false;
			}
	 }
	 
	 public static function getTipoTextArea($codice=''){
		    
            $database = Marion::getDB();
			
			if( $codice ){
				$select = $database->select('*','form_tipo_textarea',"codice={$codice}");
			}else{
				$select = $database->select('*','form_tipo_textarea',"1=1 order by ordine,etichetta asc");
			}
			
			if( okArray($select) ){
				return $select;
			}else{
				return false;
			}
	 }
	 
	 public static function getTipoData($codice=''){
		    
            $database = Marion::getDB();
			
			if( $codice ){
				$select = $database->select('*','form_tipo_data',"codice={$codice}");
			}else{
				$select = $database->select('*','form_tipo_data',"1=1 order by ordine,etichetta asc");
			}
			
			if( okArray($select) ){
				return $select;
			}else{
				return false;
			}
	 }
	 public static function getTipoTimestamp($codice=''){
		    
            $database = Marion::getDB();
			
			if( $codice ){
				$select = $database->select('*','form_tipo_timestamp',"codice={$codice}");
			}else{
				$select = $database->select('*','form_tipo_timestamp',"1=1 order by ordine,etichetta asc");
			}
			
			if( okArray($select) ){
				return $select;
			}else{
				return false;
			}
	 }
	 
	 public static function getTipoTime($codice=''){
		    
            $database = Marion::getDB();
			
			if( $codice ){
				$select = $database->select('*','form_tipo_time',"codice={$codice}");
			}else{
				$select = $database->select('*','form_tipo_time',"1=1 order by ordine,etichetta asc");
			}
			
			if( okArray($select) ){
				return $select;
			}else{
				return false;
			}
	 }
	 
	 public static function getTipoFile($codice=''){
		    
            $database = Marion::getDB();
			
			if( $codice ){
				$select = $database->select('*','form_tipo_file',"codice={$codice}");
			}else{
				$select = $database->select('*','form_tipo_file',"1=1 order by ordine,etichetta asc");
			}
			
			if( okArray($select) ){
				return $select;
			}else{
				return false;
			}
	 }
	 
	 public function trueFalse($v){
		 if($v) return 't';
		 else return 'f';
	 }
	 
	 
	 public function getCampi(){
		if( okArray($this->campi) ){
			$campi = array();
			foreach( $this->campi as $v ){
				$campi[] = new Campo($v);

			 }
			return $campi;
		 }else{
			
			$database = Marion::getDB();
			
			//$campi_select = $database->select('*','form_campo',"form={$this->codice} and attivo order by ordine asc");
			$campi_select = $database->select('f.*,t.etichetta as type_valore,t1.etichetta as tipo_valore','(form_campo as f join form_type as t on f.type=t.codice) left outer join form_tipo as t1 on t1.codice=f.tipo',"f.form={$this->codice} and attivo order by ordine asc");
			if( okArray($campi_select) ){
				$campi = array();
				foreach( $campi_select as $v ){
					$campi[] = new Campo($v);
				 }
			}
			return $campi;
		 }
		 return false;	 
	 }
	 
	 public function getCampo($campo){
		 if( okArray($this->campi) ){
			 foreach( $this->campi as $v ){
				 if( $v['campo'] == $campo ){
					 return new Campo($v);
				 }
			 }
		 }else{
			
			$database = Marion::getDB();
			
			$campo = $database->select('f.*,t.etichetta as type_valore,t1.etichetta as tipo_valore','(form_campo as f join form_type as t on f.type=t.codice) join form_tipo as t1 on t1.codice=f.tipo',"f.form={$this->codice} and f.campo='{$campo}'");
			
			if( okArray($campo) ){
				return new Campo($campo[0]);
			}
		 }
		 return false;
		 
		 
	 }
	 
	 public function getDatiHtml(){
		 $html_dati = array();
		 foreach($this->getCampi() as $campo){
		 	if( $campo->type == 3 || $campo->type == 4){
			 	foreach($campo->getOptions($GLOBALS['activelocale']) as $valore){
				 	$html_dati[$campo->campo][$valore['valore']] = $valore['etichetta'];
			 	}			 	
		 	} elseif( $campo->type == 2){
		 	}
	 	}
	 	if(okArray($html_dati)){
	 		return $html_dati;
 		}else{
	 		return false;
 		}
	 }
	 
	public function viewData($dati=null){
		$campi = $this->getCampi();
		//debugga($campi);exit;
		$toreturn = array();
		foreach($campi as $c){
			
			if($c->type_valore == 'select' || $c->type_valore == 'radio' || $c->type_valore == 'checkbox' ){
				$c->getOptions();
				//debugga($c->options);
				if(okArray($c->options)){
					foreach($c->options as $v){
	
						if($v['valore'] == $dati[$c->campo]){
							if( $c->type_valore != 'select' || ($c->type_valore == 'select' && $v['valore'] != 0)){ 
								$toreturn[$c->campo]['etichetta'] = $c->etichetta;
								$toreturn[$c->campo]['valore'] = $v['etichetta'];
							}else{
								$toreturn[$c->campo]['valore'] = '';
								$toreturn[$c->campo]['etichetta'] = '';
							}
							//debugga($toreturn);
							//debugga($v['etichetta']);
							break;
						}
					}

				}
			}else{
				$toreturn[$c->campo]['etichetta'] = $c->etichetta;
				$toreturn[$c->campo]['valore'] = $dati[$c->campo];
			}
			unset($dati[$c->campo]);
			
		}
		//debugga($toreturn);
		if(okArray($dati)){
			foreach($dati as $k=>$v){
				$toreturn[$k]['etichetta'] = $k;
				$toreturn[$k]['valore'] = $v;
			}
		}
		
		//exit;
		return $toreturn;
		
	}
	
	
	public function generaHtml($locale=NULL,$delimeter="<br>"){
		
		$data = '';
		$br = $delimeter;
		$campi = $this->getCampi();
		
		if($this->method) $method = $this->method;
		else $method = 'POST';
		$data .= "<form role='form' action='{$this->url}' name='{$this->nome}' id='{$this->nome}' method='{$method}'>\n";
		
		
		foreach( $campi as $campo){
			$class = '';
			if( $campo->type == 8 && $campo->tipo_textarea){
				$textarea = self::getTipoTextArea($campo->tipo_textarea);
				if( okArray($textarea) ){
					$textarea = $textarea[0];
					if( $textarea['class'] ){
						$class = 	$textarea['class'];
					}
				}	
			}elseif( $campo->type == 1 && $campo->tipo == 7){
				$tipoData = self::getTipoData($campo->tipo_data);
				if( okArray($tipoData) ){
					$tipoData = $tipoData[0];
					if( $tipoData['class'] ){
						$class = 	$tipoData['class'];
					}
				}
			}
			
			
			
			$html = $campo->generaHtml($locale,$class)."\n";
			$data .= $html;
		
			
			
		}
;
		
		//$data .= "<input type='hidden' name='action' value='{$this->action}'>".$br;
		
		
		if( $this->captcha == 1){
			$data.= $this->generaCaptcha();	
		}
		
	
		$data .= "<input type='hidden' name='formID'>\n".$br;
		$data .= "<input type='hidden' name='action'>\n".$br;
		$data .= "<input type=\"submit\" class=\"btn btn-default\" value=\"salva\">\n".$br;
		
		$data .= "</form>\n";
		
		return $data;
	}
	
	
	public static function outputJS(){
		echo "<script type=\"text/javascript\" src=\"/js/tinymce/tinymce.min.js\"></script>";
		echo "<script src=\"/plugins/pickadate/picker.js\"></script>";
	    echo "<script src=\"/plugins/pickadate/picker.date.js\"></script>";
	    echo "<script src=\"/js/js_cms/js_form.js\"></script>";
	}
	public static function outputCSS(){
		echo "<link href=\"/plugins/pickadate/themes/default.css\" rel=\"stylesheet\">";
	    echo "<link href=\"/plugins/pickadate/themes/default.date.css\" rel=\"stylesheet\">";
	}

	function generaCaptcha(){
		$template = _obj('Template');
		ob_start();
		$template->output('form_captcha.htm');
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	public static function export($name){
		
		$database = Marion::getDB();
		$dati = $database->select('*','form',"nome='{$name}'");
		
		$dati = $dati[0];
		
		$codice = $dati['codice'];
		unset($dati['codice']);
		$query['form'] = $dati;
		

		//unset($dati['codice']);
		$dati_campi = $database->select('*','form_campo',"form={$codice}");
		
		foreach($dati_campi as $k => $v){
			$codice_campo = $v['codice'];
			unset($v['codice']);
			//if( $k > 0) continue;
			$query['campi'][$k]['campo'] = $v;
			
			
			$valori = $database->select('*','form_valore',"campo={$codice_campo}");
			
			if( okArray($valori) ){
				foreach($valori as  $val){
					
					unset($val['codice']);
					$query['campi'][$k]['valori'][] = $val;
				}
			}
		}
		//debugga($query);exit;
		//debugga(json_encode($query));exit;
		//return $database->injectionPrevent(json_encode($query));
		$json = addcslashes(json_encode($query), '"\\/');
		
		return $json;
	}

	public static function import($data){
		
		$query = json_decode($data,true);
		
		
		$database = Marion::getDB();
		
		//prendo i dati del form
		$dati = $query['form'];
		unset($dati['codice']);
		
		if( okArray($dati) ){
			//inserisco i dati del form
			$_codice_form =$database->insert('form',$dati);
			
			$campi = $query['campi'];
			

			foreach($campi as $v){
				//debugga($v);exit;
				
				$dati_campo = $v['campo'];
				unset($dati_campo['form']);
				unset($dati_campo['codice']);
				$dati_campo['form'] = $_codice_form;

				//inserisco i dati del campo
				$_codice_campo =$database->insert('form_campo',$dati_campo);
				
				$valori_campo = $v['valori'];
				if( okArray($valori_campo) ){
					foreach($valori_campo as $valore){
						unset($valore['codice']);
						unset($valore['campo']);
						$valore['campo'] = $_codice_campo;
						$database->insert('form_valore',$valore);
					}
				}
			
			}
		}
		return true;

	}

	// FUNZIONI CONTROLLO FORM
		// FUNZIONI DI ERRORE DEL FORM
	function errore_campo_mancante($etichetta){
		$format = $GLOBALS['gettext']->strings['mancante'];
		$errore = sprintf($format, $etichetta);
		return $errore;
	}

	function errore_campo_non_valido($etichetta,$tipo_campo){
		$format = $GLOBALS['gettext']->strings['validita'];

		$errore = sprintf($format, $etichetta,$tipo_campo);
		return $errore;
	}


	function errore_campo_non_ammesso($etichetta){
		$format = $GLOBALS['gettext']->strings['non_ammesso'];

		$errore = sprintf($format, $etichetta);
		return $errore;
	}
	
	function errore_campo_captcha(){
		$errore = $GLOBALS['gettext']->strings['captcha'];
		return $errore;
	}

	function errore_campo_lunghezza($etichetta,$lunghezza,$tipo_lunghezza="min"){
		if( $tipo_lunghezza == 'min' ){
			$format = $GLOBALS['gettext']->strings['lunghezzamin'];
			$errore = sprintf($format, $etichetta,$lunghezza);
		}elseif( $tipo_lunghezza == 'max' ){
			$format = $GLOBALS['gettext']->strings['lunghezzamax'];
			$errore = sprintf($format, $etichetta,$lunghezza);
		}
		return $errore;
	}

	function verificaValiditaCampo($valore,$campo){
		 
		 if( !is_null($valore) && $campo){
			if( $campo == 'Boolean'){
				return $this->isBool($valore);
			}elseif( $campo == 'noSpace'){
				$valore_no_space = str_replace(' ', '', $valore);
				if(strlen($valore_no_space) == strlen($valore)) {
					return true;
				}else{
					return false;	
				}
			}elseif( $campo == 'prezzo'){
				return $this->_isPrice($valore);
			}elseif( $campo == 'username'){
				return $this->_isUsername($valore);
			}elseif( $campo == 'password'){
				return $this->_isPassword($valore);
			}elseif( $campo == 'cf'){
				return $this->_isCodiceFiscale($valore);
			}elseif( $campo == 'piva'){
				return $this->_isPIVA($valore);
			}else{
				if( $campo == 'Date' ){
					$valore = $this->dataIn($valore);
				}
				if( $campo == 'DateTime' ){
					$valore = $this->dataTempoIn($valore);
					//debugga($valore);exit;
					$campo = 'Date';
				}
				if( $campo == 'Time' ){
					return $this->isTime($valore);
				}
				$str = new wlString($valore);
				$chiamata = 'is'.$campo;
				return call_user_func(array($str, $chiamata));
			}
		}else{
			error_log('Errore verificaCampo di Template');
		}

	}

	// verifica se il valore inserito è un boolenao
	function isBool($var) {
	  if (!is_string($var)) return (bool) $var;
	  switch (strtolower($var)) {
	    case '1':
	    case '0':
	    case 'true':
		case 'false':
	    case 't':
	    case 'f':
	      return true;
	    default:
	      return false;
	  }
	}

	//verifica se un il valore è un prezzo valido
	function _isPrice($value){
		if( is_numeric($value) && $value >= 0){
			return true;
		}else{
			return false;
		}
	}


	//verifica se un il valore è una valida username
	function _isUsername($value){
		
		if( preg_match('/[^a-zA-Z0-9\-_]/',$value) ){
			return false;
		}
		
		return true;
	}

	//verifica se un il valore è una valida password
	function _isPassword($value){
		if( strlen($value) >= 6 ){
			if( preg_match('/[^a-zA-Z0-9\-_]/',$value) ){
				return false;
			}
		}
		return true;
	}

	//verifica se un il valore inserito è un codice fiscale valido in Italia
	function _isCodiceFiscale($cf){
		if( $cf == '' )  return false;
		if( strlen($cf) != 16 ) return false;
		$cf = strtoupper($cf);
		if( ! preg_match("/^[A-Z0-9]+$/", $cf) ){
			return false;
		}

		$s = 0;
		for( $i = 1; $i <= 13; $i += 2 ){
			$c = $cf[$i];
			if( '0' <= $c && $c <= '9' ) $s += ord($c) - ord('0');
			else $s += ord($c) - ord('A');
		}
		for( $i = 0; $i <= 14; $i += 2 ){
			$c = $cf[$i];
			switch( $c ){
				case '0':  $s += 1;  break;
				case '1':  $s += 0;  break;
				case '2':  $s += 5;  break;
				case '3':  $s += 7;  break;
				case '4':  $s += 9;  break;
				case '5':  $s += 13;  break;
				case '6':  $s += 15;  break;
				case '7':  $s += 17;  break;
				case '8':  $s += 19;  break;
				case '9':  $s += 21;  break;
				case 'A':  $s += 1;  break;
				case 'B':  $s += 0;  break;
				case 'C':  $s += 5;  break;
				case 'D':  $s += 7;  break;
				case 'E':  $s += 9;  break;
				case 'F':  $s += 13;  break;
				case 'G':  $s += 15;  break;
				case 'H':  $s += 17;  break;
				case 'I':  $s += 19;  break;
				case 'J':  $s += 21;  break;
				case 'K':  $s += 2;  break;
				case 'L':  $s += 4;  break;
				case 'M':  $s += 18;  break;
				case 'N':  $s += 20;  break;
				case 'O':  $s += 11;  break;
				case 'P':  $s += 3;  break;
				case 'Q':  $s += 6;  break;
				case 'R':  $s += 8;  break;
				case 'S':  $s += 12;  break;
				case 'T':  $s += 14;  break;
				case 'U':  $s += 16;  break;
				case 'V':  $s += 10;  break;
				case 'W':  $s += 22;  break;
				case 'X':  $s += 25;  break;
				case 'Y':  $s += 24;  break;
				case 'Z':  $s += 23;  break;
			}
		}
		if( chr($s%26 + ord('A')) != $cf[15] ) return false;
		return true;
	}

	//verifica se un il valore inserito è una partita IVA  valida
	function _isPIVA($pi){
		if( $pi == '' )  return false;
		if( strlen($pi) != 11 ) return false;
		if( !preg_match("/^[0-9]+$/", $pi) ) return false;
		$s = 0;
		for( $i = 0; $i <= 9; $i += 2 ) $s += ord($pi[$i]) - ord('0');
		for( $i = 1; $i <= 9; $i += 2 ){
			$c = 2*( ord($pi[$i]) - ord('0') );
			if( $c > 9 )  $c = $c - 9;
			$s += $c;
		}
		if( ( 10 - $s%10 )%10 != ord($pi[10]) - ord('0') ) return false;
		return true;
	}
	




	
	
	function dataOut($val=NULL){
		if($val){
			return strftime("%d/%m/%Y",strtotime($val));
		}else{
			return false;
		}
	}
	
	function dataIn($val=NULL){
		if($val){
			list($d,$m,$Y) = explode('/',$val);
			if( is_numeric($d) && is_numeric($m) && is_numeric($Y) && (strlen($d) == 2 || strlen($d) == 1) && strlen($m) == 2 && strlen($Y) == 4){
				return $Y."-".$m."-".$d;
			}else{
				return false;
			}
		}
		return false;
		
	}
	function dataTempoIn($val=null){
		if($val){
			
			list($data,$ora) = explode(' ',$val);
			
			list($d,$m,$Y) = explode('/',$data);
			
			if( !$this->isTime($ora) ) return false;
			
			list($H,$M) = explode(':',$ora);
			
			if( is_numeric($d) && is_numeric($m) && is_numeric($Y) && (strlen($d) == 2 || strlen($d) == 1) && strlen($m) == 2 && strlen($Y) == 4 && is_numeric($H) && is_numeric($M)){
				return $Y."-".$m."-".$d." {$H}:{$M}:00";
			}else{
				return false;
			}
		}
		//return false;
		
	}
	
	function dataTempoOut($val){
		if($val){
			return strftime("%d/%m/%Y %H:%M",strtotime($val));
		}else{
			return false;
		}
	}
	

	//verifica se un'orario inserito è nel formato corretto
	function isTime($time){
		if (preg_match('/^\d{2}:\d{2}$/', $time)) {
			list($h,$m) = explode(':',$time);
			if( (0<= (int)$m && (int)$m <=59) && (0<=(int)$h && (int)$h <=23)){
				return true;
			}
		    return false;
		} else {
		    return false;
		}	
	}
	
	//formatta un orario per l'inserimento nel database
	function tempoIn($time){
		if($time) return $time.":00";
		else return false;
	}


	//formatta un orario per la visualizzazione
	function tempoOut($time){
		
		if($time){
			return preg_replace("/:00$/",'',$time);
		}else{
			return "";
		}

	}

	/**************************************************** OVERRIDE FUNZIONI NATIVE DEL PHP **********************************************************************/


	function serialize($dati=NULL){
		return serialize($dati);
	}
	function unserialize($dati=NULL){
		return unserialize($dati);
	}

	function strtoupper($val){
		return strtoupper($val);
	}
	
	function strtolower($val=NULL){
		return strtolower($val);
	}
	

	                     
}    

class Campo{
	public $options;
	public function __construct($dati){
		
		if(okArray($dati)){
			foreach( $dati as $k => $v){
				$this->$k = $v;
			}
		}
	}
	
	public function getOptions($locale='all'){
		if( $this->type != 2 && $this->type != 3 && $this->type != 4) return false;
		
		if( $this->tipo_valori == 1){
			
			$options = $this->options;
			if( okArray($options) ){
				return $options;
			}else{
				
				
				$database = Marion::getDB();
				
				if( $locale && $locale != 'all'){	
						
					$options = $database->select('*','form_valore',"campo={$this->codice} and locale='{$locale}'");
				}else{
					
					$options = $database->select('*','form_valore',"campo={$this->codice}");
					
				}
				if( okArray($options) ){
					$this->options = $options;
					return $options;
				}
			}
		}else{
			/*$template = _obj('Template');
			if( $this->function_template && method_exists($template,$this->function_template) ){
				$function = $this->function_template;
				$opzioni = $template->$function();	
				//debugga($opzioni);exit;
				foreach($opzioni as $k => $v){
					$options[] = array(
						'valore' => $k,
						'etichetta' => $v
					);
				}
				$this->options = $options;
				//debugga($this->options);exit;
				return $options;
			}*/
		}
		return false;
			
	}
	
	
	
	
	

	
	
}            


?>