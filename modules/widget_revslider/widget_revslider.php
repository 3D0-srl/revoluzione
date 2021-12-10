<?php
use Marion\Core\{Module,Form};
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
class WidgetRevslider extends Module{

	

	function install(){
		$res = parent::install();
		if( $res ){

			DB::schema()->create('revolution_slider',function(Blueprint $table){
				$table->id();
				$table->string('title',200)->nullable(true);
				$table->longText('js')->nullable(true);
				$table->longText('css')->nullable(true);
				$table->longText('content')->nullable(true);
				
			});

			if( !file_exists(_MARION_MODULE_DIR_."widget_revslider/sliders")){
				mkdir(_MARION_MODULE_DIR_."widget_revslider/sliders");
			}
			Form::import("{\"form\":{\"gruppo\":\"5\",\"nome\":\"widget_revslider\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"755\",\"campo\":\"id_slider\",\"etichetta\":\"slider\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"1\",\"tipo_valori\":\"0\",\"function_template\":\"sliders\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}}]}");
		}


		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();
		if( $res ){
			
			Form::delete('widget_revslider');
			DB::schema()->dropIfExists('revolution_slider');
		}	
		return $res;
	}

}



?>