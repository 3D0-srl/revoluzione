
<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Elearning\CourseDetail;
use Elearning\CourseUnit;
use Marion\Components\WidgetComponent;
use Marion\Core\{Marion,Module};
class Elearning extends Module{
	/*
		OVERRIDE INSTALL
	*/
	function install(){
		$res = parent::install();
		if( $res ){
			
			
			DB::schema()->create("course_detail",function(Blueprint $table){
				$table->id(); //crea un campo id (autoincremnet,unsigned,bigint(20))
				$table->bigInteger("course_id")->unsigned(true)->index('course_id');
				$table->string("telegram_group")->nullable(true);
				$table->string("facebook_group")->nullable(true);
			});
			DB::schema()->create("course_detail_lang",function(Blueprint $table){
				$table->bigInteger("course_detail_id")->unsigned(true);
				$table->string("lang",3)->default('it');
				$table->string("youtube_link",255)->nullable(true);
				$table->foreign('course_detail_id')->references('id')->on('course_detail')->onDelete('cascade');
			});

			DB::schema()->create("course_unit",function(Blueprint $table){
				$table->id(); //crea un campo id (autoincremnet,unsigned,bigint(20))
				$table->bigInteger("course_id")->unsigned(true)->index('course_id');
				$table->bigInteger("video_id")->unsigned(true)->index('video_id');
				$table->integer("order_view")->unsigned(true)->default(1);
			});
			DB::schema()->create("course_unit_lang",function(Blueprint $table){
				$table->bigInteger("course_unit_id")->unsigned(true);
				$table->string("lang",3)->default('it');
				$table->string("title",255)->nullable(true);
				$table->foreign('course_unit_id')->references('id')->on('course_unit')->onDelete('cascade');
			});

			DB::schema()->create("course_video",function(Blueprint $table){
				$table->id(); //crea un campo id (autoincremnet,unsigned,bigint(20))
				$table->string("path",255);
				$table->string("name",255);
			});

			$this->installForms();

			
		}


		return $res;
	}


	/*
		OVERRIDE UNINSTALL
	*/
	function uninstall(){
		$res = parent::uninstall();
		if( $res ){
			
			//per cancellare una tabella
			DB::schema()->dropIfExists("course_detail_lang");
			DB::schema()->dropIfExists("course_detail");
			DB::schema()->dropIfExists("course_unit_lang");
			DB::schema()->dropIfExists("course_unit");
			DB::schema()->dropIfExists("course_video");

			$this->uninstallForms();
			

		}	
		return $res;
	}

	/*
		OVERRIDE ACTIVE
	*/
	function active()
	{	
		
		parent::active();
	}

	/*
		OVERRIDE DISABLE
	*/
	function disable()
	{
		
		parent::disable();
		
	}

	/**
		OVERRIDE SEEDER
	**/
	function seeder(){
		$faker = $this->getFaker();
	}



	private function installForms(){
		Form::import("{\"form\":{\"gruppo\":\"0\",\"nome\":\"elearning_corso\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":null,\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"1063\",\"campo\":\"id\",\"etichetta\":\"id\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1063\",\"campo\":\"name\",\"etichetta\":\"nome\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"2\",\"lunghezzamax\":\"200\",\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":\"inserisci il nome del corso\"}},{\"campo\":{\"form\":\"1063\",\"campo\":\"price\",\"etichetta\":\"prezzo\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"12\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":\"inserisci il prezzo del corso\"}},{\"campo\":{\"form\":\"1063\",\"campo\":\"visibility\",\"etichetta\":\"online\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":\"0\",\"dropzone\":\"0\",\"descrizione\":\"Stabilisce se il corso deve essere visibile o meno all'utente\",\"placeholder\":null},\"valori\":[{\"campo\":\"7824\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":null},{\"campo\":\"7824\",\"etichetta\":\"NO\",\"valore\":\"0\",\"locale\":\"it\",\"ordine\":null}]},{\"campo\":{\"form\":\"1063\",\"campo\":\"description\",\"etichetta\":\"descrizione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"8\",\"tipo\":null,\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":\"5\",\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":\"inserisci una descrizione per il corso\"}},{\"campo\":{\"form\":\"1063\",\"campo\":\"section\",\"etichetta\":\"categoria\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"1\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":\"categorie\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1063\",\"campo\":\"youtube_link\",\"etichetta\":\"Youtube video\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":\"inserisci l'url del video youtube\"}},{\"campo\":{\"form\":\"1063\",\"campo\":\"image\",\"etichetta\":\"Immagine di anteprima\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"1\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1063\",\"campo\":\"orderView\",\"etichetta\":\"ordine di visualizzazione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"1\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":\"inserisci l'ordine di visualizzazione del corso\"}},{\"campo\":{\"form\":\"1063\",\"campo\":\"sku\",\"etichetta\":\"Codice corso\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":\"inserisce il codice del corso\"}},{\"campo\":{\"form\":\"1063\",\"campo\":\"telegram_group\",\"etichetta\":\"Gruppo telegram\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":\"inserisci l'url del gruppo telegram\"}},{\"campo\":{\"form\":\"1063\",\"campo\":\"facebook_group\",\"etichetta\":\"Gruppo facebook\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":\"inserisci l'url del gruppo facebook\"}}]}");
		Form::import("{\"form\":{\"gruppo\":\"0\",\"nome\":\"elearning_unita\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":null,\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"1064\",\"campo\":\"id\",\"etichetta\":\"id\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"1\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1064\",\"campo\":\"title\",\"etichetta\":\"titolo\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"2\",\"lunghezzamax\":\"200\",\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"1\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":\"inserisci il titolo dell'unit\\u00e0\"}},{\"campo\":{\"form\":\"1064\",\"campo\":\"course_id\",\"etichetta\":\"id corso\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"1\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1064\",\"campo\":\"order_view\",\"etichetta\":\"ordine di visualizzazione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"1\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":\"inserisci l'ordine di visualizzazione dell'unit\\u00e0\"}},{\"campo\":{\"form\":\"1064\",\"campo\":\"video_id\",\"etichetta\":\"id video\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"7\",\"tipo\":\"1\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");
		Form::import("{\"form\":{\"gruppo\":\"0\",\"nome\":\"elearning_support\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":null,\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"1069\",\"campo\":\"subject\",\"etichetta\":\"oggetto\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"3\",\"lunghezzamax\":\"200\",\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1069\",\"campo\":\"message\",\"etichetta\":\"Messaggio\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"3\",\"lunghezzamax\":\"500\",\"type\":\"8\",\"tipo\":null,\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");


		Form::import("{\"form\":{\"gruppo\":\"0\",\"nome\":\"elearning_setting\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":null,\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"1107\",\"campo\":\"cart_status\",\"etichetta\":\"Stato ordine pagato\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"9\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":\"cartStatus\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");
	}

	private function uninstallForms(){
		Form::delete('elearning_corso');
		Form::delete('elearning_unita');
		Form::delete('elearning_support');
		Form::delete('elearning_setting');
	}



	function previewCourse($product=null){

		
        $details = CourseDetail::prepareQuery()
					->where('course_id',$product->id)
					->getOne();
       
       
		
		$widget = new WidgetComponent('elearning');
		$widget->setVar('details',$details);
		
		$widget->setVar('product',$product);
		$widget->output('preview.htm');
	}

	function unitsCourse($product=null){
		
        $check = elearning_check_course($product->id);
		$widget = new WidgetComponent('elearning');
		$units = CourseUnit::prepareQuery()->where('course_id',$product->id)->orderBy('order_view','ASC')->get();

		$widget->setVar('disabled',!$check);
		$widget->setVar('product',$product);
        
        $widget->setVar('units',$units);
		$widget->output('units.htm');
	}


	function displayBackendHome(){
		$user = Marion::getUser();
        $orders = DB::table('cart','c')
        ->join('cartRow as r','r.cart','=','c.id')
        ->whereIn('c.status',elearnig_status_cart())
        ->where('c.user',$user->id)
		->orderBy('evacuationdate','DESC')
		->limit(5)
		->get(['product'])->toArray();
		
        foreach($orders as $k => $o){
            $product = Product::withId($o->product);
            $products[] = $product;
        }
		$widget = new WidgetComponent('elearning');
        $widget->setVar('courses',$products);
		$widget->output('last_courses.htm');
	}

}
?>
