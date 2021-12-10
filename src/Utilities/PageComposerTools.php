<?php
namespace Marion\Utilities;
use Marion\Core\{Marion,Base};
class PageComposerTools{



    public static function export($id,$file_input=null){
        $database = Marion::getDB();
        $page = $database->select('*','page',"id={$id}");
        $page_lang = $database->select('*','pageLocale',"page={$id}");
        $dati['page'] = $page[0];
        $dati['page_lang'] = $page_lang;
        
        $id_page = $page[0]['id_adv_page'];


       



        if( $id_page ){
            $layout = $database->select('*','page_advanced',"id={$id_page}");
            $layout = $layout[0];
            
        }
       
      
        $path = _MARION_MODULE_DIR_.'pagecomposer/export/'.$id;
        if(!file_exists($path)){
            if (!mkdir($path, 0777, true)) {
                die('Failed to create folders...');
            }
        }
        

        /* copio i file css e js */
        $path_media = _MARION_MODULE_DIR_.'pagecomposer/media/';
        file_put_contents($path."/js_head_tmp.js",file_get_contents($path_media.'js/js_head_tmp_'.$id_page.".js"));
        file_put_contents($path."/js_end_tmp.js",file_get_contents($path_media.'js/js_end_tmp_'.$id_page.".js"));
        file_put_contents($path."/css_tmp.js",file_get_contents($path_media.'css/css_tmp_'.$id_page.".css"));

        file_put_contents($path."/js_head.js",file_get_contents($path_media.'js/js_head_'.$id_page.".js"));
        file_put_contents($path."/js_end.js",file_get_contents($path_media.'js/js_end_'.$id_page.".js"));
        file_put_contents($path."/css.css",file_get_contents($path_media.'css/css_'.$id_page.".css"));



        $database = Marion::getDB();
        $export = $database->select('*','composition_page',"id_adv_page={$id_page} order by orderView,id");
        foreach($export as $v){
            $path_module = $path."/".$v['id'];
            if(!file_exists($path_module)){
                if (!mkdir($path_module, 0777, true)) {
                    die('Failed to create folders...');
                }
            }
            if(!file_exists($path_module."/data_row")){
                if (!mkdir($path_module."/data_row", 0777, true)) {
                    die('Failed to create folders...');
                }
            }
			$field_images = array('background_url','background_url_webp');
			foreach($field_images as $f){
				if( $v[$f] ){
					$file = _MARION_ROOT_DIR_.'media/images/'.$v[$f];
					$file_dest = $path_module."/data_row/".$v[$f];
					if( file_exists($file) ){
						copy($file,$file_dest);
						
					}
				}
			}
            $class = $v['module_function'];
            if( class_exists($class) ){
                
                $obj = new $class();
                $obj->init($v);
                $obj->export($path_module);
                
            }
        
        }

        $export_data = $database->select('*','composition_page',"id_adv_page={$id_page} order by orderView,id");
        
        file_put_contents( $path."/page-composer.json",json_encode($export_data));
        file_put_contents( $path."/dati.json",json_encode($dati));
        file_put_contents( $path."/layout.json",json_encode($layout));
        
        $path_zip = $path.'.zip';
        self::Zip($path,$path_zip);
        self::rrmdir($path);

       
        
        if($file_input){
            rename($path_zip,$file_input);
        }else{
            $file_name = basename($path_zip);

            header("Content-Type: application/zip");
            header("Content-Disposition: attachment; filename=$file_name");
            header("Content-Length: " . filesize($path_zip));

            readfile($path_zip);
            unlink($path_zip);
            exit;
        }
        

    }

    public static function import($file,$destination=false){
        $database = Marion::getDB();

        if( !$destination ){
            $destination = _MARION_MODULE_DIR_.'pagecomposer/export/tmp';
            self::rrmdir($destination);
          
            $zip = new \ZipArchive;
            if ($zip->open($file) === TRUE) {
                $zip->extractTo($destination);
                $zip->close();
            
            } 
        }

       

        $dati = json_decode(file_get_contents($destination."/dati.json"),true);
        $layout = json_decode(file_get_contents($destination."/layout.json"),true);
        $id_page = 0;
        if( okArray($layout) ){
            unset($layout['id']);
            $id_page = $database->insert('page_advanced',$layout);

        }

         /* copio i file css e js */
         $path_media = _MARION_MODULE_DIR_.'pagecomposer/media';
        
         file_put_contents($path_media."/js/js_head_tmp_".$id_page.".js",file_get_contents($destination.'/js_head_tmp.js'));
         file_put_contents($path_media."/js/js_end_tmp_".$id_page.".js",file_get_contents($destination.'/js_end_tmp.js'));
         file_put_contents($path_media."/css/css_tmp_".$id_page.".css",file_get_contents($destination.'/css_tmp.css'));

         file_put_contents($path_media."/js/js_head_".$id_page.".js",file_get_contents($destination.'/js_head.js'));
         file_put_contents($path_media."/js/js_end_".$id_page.".js",file_get_contents($destination.'/js_end.js'));
         file_put_contents($path_media."/css/css_".$id_page.".css",file_get_contents($destination.'/css.css'));
         




        $dati_pagina = $dati['page'];
        unset($dati_pagina['id']);
        $dati_pagina['id_adv_page'] = $id_page;
        $id = $database->insert('page',$dati_pagina);

        $dati_pagina_lang = $dati['page_lang'];
        foreach($dati_pagina_lang as $lang){
            $lang['page'] = $id;
            $database->insert('pageLocale',$lang);
        }
        
       
        //$sostituisci = _var('sostituisci');
        //$id_parent = _var('id_box');
        $data1 = json_decode(file_get_contents($destination."/page-composer.json"),true);
        
       
        
        foreach($data1 as $k => $v){
            $path_module = $destination."/".$v['id'];
           
            $class = $v['module_function'];
            if( class_exists($class) ){
                
                $obj = new $class();
                $obj->init($v);
                $obj->import($path_module);
                $data1[$k]['parameters'] = serialize($obj->getParameters());

                
            }

            $field_images = array('background_url','background_url_webp');
            foreach($field_images as $f){
                
                if( $v[$f] ){
                    $file_dest = _MARION_ROOT_DIR_.'media/images/'.$v[$f];
                    $file = $path_module."/data_row/".$v[$f];
                    
                    if( file_exists($file) ){
                        copy($file,$file_dest);
                        
                    }
                }
            }
           
        
        }
       
        $data2 = json_decode(json_encode($data1));
        
        
        $tree = Base::buildTree($data2);

       
        
    

        uasort($tree,function ($a, $b) {
            if($a->orderView == $b->orderView) {
                return 0;
            }
            return ($a->orderView > $b->orderView) ? -1 : 1;
        });

        
      
       
        $database->delete('composition_page',"id_adv_page={$id_page}");
      
        
        

        

        foreach($tree as $v){
            self::importRow($id_page,$v);
        }

        $database->delete('composition_page_tmp',"id_adv_page={$id_page}");
		$database->execute("INSERT composition_page_tmp SELECT * FROM composition_page where id_adv_page={$id_page}");


       
        return $id;

    }


    public static function Zip($source, $destination)
	{
		if (!extension_loaded('zip') || !file_exists($source)) {
			return false;
		}

		$zip = new \ZipArchive();
		if (!$zip->open($destination, \ZIPARCHIVE::CREATE)) {
			return false;
		}

		$source = str_replace('\\', '/', realpath($source));

		if (is_dir($source) === true)
		{
			$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

			foreach ($files as $file)
			{
				$file = str_replace('\\', '/', $file);

				// Ignore "." and ".." folders
				if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
					continue;

				$file = realpath($file);

				if (is_dir($file) === true)
				{
					$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
				}
				else if (is_file($file) === true)
				{
					$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
				}
			}
		}
		else if (is_file($source) === true)
		{
			$zip->addFromString(basename($source), file_get_contents($source));
		}

		return $zip->close();
	}
	
	


	public static function rrmdir($dir) { 
		if (is_dir($dir)) { 
		  $objects = scandir($dir);
		  foreach ($objects as $object) { 
			if ($object != "." && $object != "..") { 
			  if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
				self::rrmdir($dir. DIRECTORY_SEPARATOR .$object);
			  else
				unlink($dir. DIRECTORY_SEPARATOR .$object); 
			} 
		  }
		  rmdir($dir); 
		} 
      }
      
      public static function importRow($id_page,$data,$id_parent=0){

		$data = (array)$data;
		$data['parent'] = $id_parent;
		$data['id_adv_page'] = $id_page;
		unset($data['id']);
		$database = Marion::getDB();
		$children = $data['children'];
		unset($data['children']);

        $id_parent = $database->insert('composition_page',$data);
		if( okArray($children) ){
			uasort($children,function ($a, $b) {
				if($a->orderView == $b->orderView) {
					return 0;
				}
				return ($a->orderView > $b->orderView) ? -1 : 1;
			});
			foreach($children as $v){
				self::importRow($id_page,$v,$id_parent);
				
			}
		}

		return;

	}
}
?>