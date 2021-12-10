<?php
use ScssPhp\ScssPhp\Compiler;
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;

class ThemeAdminController extends AdminModuleController{
	public $_auth = 'superadmin';

    


	function displayContent(){
		$this->setMenu('developer_themes');
		$action = $this->getAction();
		$theme = _var('theme');
		switch($action){
			case 'css':
				$theme = _var('theme');
				if( $this->isSubmitted()){
					
					$formdata = $this->getFormdata();
					$data = $formdata['data'];
					$scss = new Compiler();
					try{
						$data_tmp = $data;
						
						$parameters = array(
							'BASE_URL' => $this->getBaseUrl(),
							'THEME_DIR' => $this->getBaseUrl()."themes/"._MARION_THEME_,
						);
						$string = '';
						foreach($parameters as $key => $value){
							$string .= '$'.$key.':"'.$value.'";';
						}
						
						$data_tmp = $string.$data_tmp;
						
						$compressed = $scss->compile($data_tmp);
						file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.scss",$data);
						file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.css",$compressed);
						$this->displayMessage('Dati salvati con successo');
					}catch(Exception $e){
						$this->errors[] = $e->getMessage();
						
					}
					
					
					
					
					
				}else{
					$data = file_get_contents(_MARION_THEME_DIR_."/".$theme."/theme.scss");

				}

				$this->setVar('tema',$theme);
				
				
				$this->setVar('data',$data);
				$this->output('themes/css.htm');
				break;
			case 'js':
				$theme = _var('theme');

				if( $this->isSubmitted()){

					$formdata = $this->getFormdata();
					file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.js",$formdata['data']);
					$this->displayMessage('Dati salvati con successo');
				}

				$this->setVar('tema',$theme);
				$data = file_get_contents(_MARION_THEME_DIR_."/".$theme."/theme.js");
				$this->setVar('data',$data);
				$this->output('themes/js.htm');
				break;
		}
	}

    function setMedia()
    {	
		$action = $this->getAction();
		if($action == 'css'){
			
			$this->registerJS('../plugins/codemirror/lib/codemirror.js','head');
			$this->registerJS('../plugins/codemirror/mode/css/css.js','head');
			$this->registerJS('../plugins/codemirror/addon/selection/active-line.js','head');
			$this->registerJS('../plugins/codemirror/addon/selection/matchbrackets.js','head');
			$this->registerCSS('../plugins/codemirror/lib/codemirror.css');
			$this->registerCSS('../plugins/codemirror/theme/panda-syntax.css');
			$this->registerJS('../plugins/codemirror/addon/search/search.js','head');
			$this->registerJS('../plugins/codemirror/addon/search/searchcursor.js','head');
			$this->registerJS('../plugins/codemirror/addon/search/jump-to-line.js','head');
			$this->registerJS('../plugins/codemirror/addon/dialog/dialog.js','head');
			$this->registerJS('../plugins/codemirror/addon/display/fullscreen.js','head');
			$this->registerCSS('../plugins/codemirror/addon/dialog/dialog.css');
		}elseif($action == 'js'){
			$this->registerJS('../plugins/codemirror/lib/codemirror.js','head');
			$this->registerJS('../plugins/codemirror/mode/javascript/javascript.js','head');
			$this->registerJS('../plugins/codemirror/addon/selection/active-line.js','head');
			$this->registerJS('../plugins/codemirror/addon/selection/matchbrackets.js','head');
			$this->registerCSS('../plugins/codemirror/lib/codemirror.css');
			$this->registerCSS('../plugins/codemirror/theme/panda-syntax.css');
			$this->registerJS('../plugins/codemirror/addon/search/search.js','head');
			$this->registerJS('../plugins/codemirror/addon/search/searchcursor.js','head');
			$this->registerJS('../plugins/codemirror/addon/search/jump-to-line.js','head');
			$this->registerJS('../plugins/codemirror/addon/dialog/dialog.js','head');
			$this->registerJS('../plugins/codemirror/addon/display/fullscreen.js','head');
			$this->registerCSS('../plugins/codemirror/addon/dialog/dialog.css');
			

		}else{
			$this->registerJS('../modules/developer/js/theme.js?v=2');
		}
		
       
    }

    function displayList(){
        $this->setMenu('developer_themes');


       
        

        $fields = array(
			0 => array(
				'name' => 'Nome',
				'field_value' => 'name',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'name',
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			),
			1 => array(
				'name' => 'Tipo',
				'field_value' => 'kind',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'kind',
				'search_name' => 'kind',
				'search_value' => _var('kind'),
				'search_type' => 'input',
			),
			2 => array(
				'name' => 'Descrizione',
				'field_value' => 'description',
				'function' => '',
				'sortable' => true,
				'sort_id' => 'title',
				'searchable' => true,
				'search_name' => 'description',
				'search_value' => _var('description'),
				'search_type' => 'input',
			),
			3 => array(
				'name' => '',
				'field_value' => '',
				'function_type' => 'row',
				'function' => 'url_attiva',
				
			),
		
		);

		$bulk_actions = $this->getListOption('bulk_actions');
		$bulk_actions['enabled'] = 0;
		$this->setListOption('bulk_actions',$bulk_actions);
		$actions = $this->getListOption('row_actions');
		
		$actions['actions'] = array(
			'css' => array(
				'text' => 'CSS',
				'target_blank' => 1,
				'icon_type' => 'icon',
				'icon' => 'fa fa-code',
				'url_function' => "url_css"
			),
			'js' => array(
				'text' => 'Javascript',
				'target_blank' => 1,
				'icon_type' => 'icon',
				'icon' => 'fa fa-code',
				'url_function' => "url_js"
			),

		);
		
		$this->setListOption('row_actions',$actions);
		$this->setListOption('fields',$fields);
       
        $list = scandir(_MARION_THEME_DIR_);
        if( okArray($list) ){
            foreach($list as $v){
                if( $v != '.' && $v != '..'){
                    $file = _MARION_THEME_DIR_.$v."/config.xml";
                    if( file_exists($file) ){

                        $data_xml = simplexml_load_file($file);

                        $info = $data_xml->info;
                        $info->directory = $v;
                        if($info->directory == $GLOBALS['activetheme']){
                            $info->current = 1;
                        }
                        $info->dir = $v;
                        $info->description = (string)$info->description;
                        $themes[] = (array)$info;
                    }
                    
                }
            }
		}
	
		$this->setTitle('Temi');
        $this->setListOption('total_items',count($themes));
		$this->setDataList($themes);
		
		//debugga($themes);exit;
        
        parent::displayList();
      
        
	}

	function url_attiva($row){
		if( $row['current'] ){
			return '';
		}else{
			return "<a href='#' onclick='active_theme(\"{$row['dir']}\"); return false;' class='btn btn-success btn-sm'><i class='fa fa-arrow-up'></i> attiva</a>";
		}
		
	}
	

	function url_css($row){
		return "index.php?ctrl=ThemeAdmin&mod=developer&action=css&theme=".$row['tag'];
	}
	function url_js($row){
		return "index.php?ctrl=ThemeAdmin&mod=developer&action=js&theme=".$row['tag'];
	}


    function ajax(){
        $action = $this->getAction();
       
        switch($action){
            case 'active':
                $theme = _var('theme');
                Marion::setConfig('theme_setting','active',$theme);
                
               
                Marion::refresh_config();
                $response = array(
                    'result' => 'ok'
                );
				break;
			case 'css':
				$theme = _var('theme');
				$error = null;
					
				
				
				$data = _var('code');
				
				$scss = new Compiler();
				try{
					$data_tmp = $data;
					
					$parameters = array(
						'BASE_URL' => $this->getBaseUrl(),
						'THEME_DIR' => $this->getBaseUrl()."themes/"._MARION_THEME_,
					);
					$string = '';
					foreach($parameters as $key => $value){
						$string .= '$'.$key.':"'.$value.'";';
					}
					
					$data_tmp = $string.$data_tmp;
					
					$compressed = $scss->compile($data_tmp);

					
					file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.scss",$data);
					file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.css",$compressed);
					
				}catch(Exception $e){
					$error = $e->getMessage();
					
				}

				
				if($error){
					$response = array(
						'result' => 'nak',
						'error' => $error
					);
				}else{
					$response = array(
						'result' => 'ok'
					);
				}
				
				break;
			case 'js':
				$theme = _var('theme');

				

				$data = _var('code');
				file_put_contents(_MARION_THEME_DIR_."/".$theme."/theme.js",$data);
				$response = array(
					'result' => 'ok'
				);
				
				break;
        }
        echo json_encode($response);
        exit;
    }
    
}