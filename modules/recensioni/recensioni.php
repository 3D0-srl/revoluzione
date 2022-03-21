
<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Marion\Core\Form;
class Recensioni extends Marion\Core\Module{
	/*
		OVERRIDE INSTALL
	*/
	function install(){
		$res = parent::install();
		if( $res ){
			
			//per creare una tabella
			DB::schema()->create("recensioni",function(Blueprint $table){
				$table->id(); //crea un campo id (autoincremnet,unsigned,bigint(20))
				$table->bigInteger("user_id")->nullable(false)->unsigned(true);
				$table->string("nickname")->nullable(false);
				$table->boolean("confermato")->default(false);
				$table->timestamp("data_inserimento")->useCurrent();
				$table->text("message")->nullable(false);
			});

			Form::import("{\"form\":{\"gruppo\":\"0\",\"nome\":\"recensioni_form\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":null,\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"1112\",\"campo\":\"nickname\",\"etichetta\":\"Nickname\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1112\",\"campo\":\"message\",\"etichetta\":\"Messaggio\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"8\",\"tipo\":null,\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");

			
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
			DB::schema()->dropIfExists("recensioni");
			
			Form::delete('recensioni_form');
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


}
?>
