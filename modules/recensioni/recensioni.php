
<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
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
				
				$table->string("nickname")->nullable(false);
				$table->boolean("confermato")->default(true);
				$table->timestamp("data_inserimento")->useCurrent();
				$table->text("message")->nullable(false);
			});

			
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
