<?php

use Marion\Core\{Module,Form};
use Marion\Entities\Permission;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class News extends Module
{
	
	function install()
	{
		$res = parent::install();
		if ($res) {
			Form::import("{\"form\":{\"gruppo\":\"5\",\"nome\":\"news\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"208\",\"campo\":\"id\",\"etichetta\":\"codice\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"1\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"208\",\"campo\":\"title\",\"etichetta\":\"titolo\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"2\",\"lunghezzamax\":\"80\",\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"4\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"208\",\"campo\":\"content\",\"etichetta\":\"contenuto\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"8\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"6\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"5\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"208\",\"campo\":\"orderView\",\"etichetta\":\"ordine\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"1\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"7\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"208\",\"campo\":\"visibility\",\"etichetta\":\"visibile\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"6\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"8\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"2\",\"value_ifisnull\":\"0\",\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null},\"valori\":[{\"campo\":\"1284\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"}]},{\"campo\":{\"form\":\"208\",\"campo\":\"images\",\"etichetta\":\"immagini\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"5\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"9\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"1\",\"tipo_timestamp\":\"0\",\"ext_image\":\"a:4:{i:0;s:3:\\\"gif\\\";i:1;s:3:\\\"png\\\";i:2;s:4:\\\"jpeg\\\";i:3;s:3:\\\"jpg\\\";}\",\"resize_image\":\"a:4:{i:0;s:9:\\\"thumbnail\\\";i:1;s:5:\\\"small\\\";i:2;s:6:\\\"medium\\\";i:3;s:5:\\\"large\\\";}\",\"dimension_resize_default\":\"1\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"208\",\"campo\":\"type_news\",\"etichetta\":\"tipologia news\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"0\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"0\",\"function_template\":\"array_type_news\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"208\",\"campo\":\"slug\",\"etichetta\":\"slug\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"11\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"3\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"208\",\"campo\":\"date\",\"etichetta\":\"data\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"7\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"5\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"1\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"208\",\"campo\":\"urlType\",\"etichetta\":\"tipologia url\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"1\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"10\",\"tipo_valori\":\"0\",\"function_template\":\"array_type_url_news\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");
			Form::import("{\"form\":{\"gruppo\":\"5\",\"nome\":\"type_news\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"207\",\"campo\":\"id\",\"etichetta\":\"codice\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"1\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"207\",\"campo\":\"name\",\"etichetta\":\"nome\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"2\",\"lunghezzamax\":\"80\",\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"3\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"207\",\"campo\":\"slug\",\"etichetta\":\"slug\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"11\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"207\",\"campo\":\"default_news\",\"etichetta\":\"default\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"1\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"4\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"2\",\"value_ifisnull\":\"0\",\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null},\"valori\":[{\"campo\":\"1292\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"}]},{\"campo\":{\"form\":\"207\",\"campo\":\"urlType\",\"etichetta\":\"tipologia url\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"3\",\"tipo\":\"1\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"5\",\"tipo_valori\":\"0\",\"function_template\":\"array_type_url_news_type\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");

			
			DB::schema()->create('news',function(Blueprint $table){
				$table->id();
				$table->string('type_news',20)->nullable(true);
				$table->boolean('visibility')->nullable(true)->default(true);
				$table->integer('orderView')->nullable(true);
				$table->tinyInteger('urlType')->nullable(true);
				$table->date('date')->nullable(true);
				$table->date('dateLastUpdate')->nullable(true);
				$table->string('images',200)->nullable(true);
			});

			DB::schema()->create('news_lang',function(Blueprint $table){
				$table->bigInteger('news_id');
				$table->string('slug',100)->nullable(true);
				$table->string('title',200)->nullable(true);
				$table->longText('content')->nullable(true);
				$table->string('lang',3)->nullable(true);
				//$table->foreign('news_id')->references('id')->on('news');
			});

			DB::schema()->create('news_type',function(Blueprint $table){
				$table->id();
				$table->string('template',100)->nullable(true);
				$table->boolean('visibility')->nullable(true)->default(true);
				$table->integer('default_news')->nullable(true);
				$table->tinyInteger('urlType')->nullable(true);
				//$table->date('dateLastUpdate')->nullable(true);
			});

			DB::schema()->create('news_type_lang',function(Blueprint $table){
				$table->bigInteger('news_type_id');
				$table->string('slug',100)->nullable(true);
				$table->string('name',200)->nullable(true);
				$table->string('lang',3)->nullable(true);
				//$table->foreign('news_type_id')->references('id')->on('news_type');
			});

	


			//widget
			
			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"widget_last_news\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"732\",\"campo\":\"id_category_news\",\"etichetta\":\"categoria news\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"0\",\"function_template\":\"categories\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\"}}]}");


			

			//inserisco il permesso delle news
			/*$permission = Permission::create();
			$data_permission = array(
				'label' => 'cms_news',
				'active' => 1,
				'orderView' => 10,
				'code' => 16384,
			);

			$data_permission_locale = array(
				'name' => 'CMS News',
				'description' => 'Permette di modificare le news del sito',
			);
			
			$permission->set($data_permission)->setData($data_permission_locale,'it')->save();*/
		
		}


		return $res;
	}



	function uninstall()
	{
		$res = parent::uninstall();
		if ($res) {
			Form::delete('news');
			Form::delete('type_news');
			Form::delete('widget_last_news');

			DB::schema()->dropIfExists('news_locale');
			DB::schema()->dropIfExists('news');
			DB::schema()->dropIfExists('news_type_locale');
			DB::schema()->dropIfExists('news_type');


			$permission = Permission::prepareQuery()->where('label','cms_news')->getOne();
			if( is_object($permission) ){
				$permission->delete();
			}
		}
		return $res;
	}
}
