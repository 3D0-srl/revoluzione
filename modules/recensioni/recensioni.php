
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
			/*
			//per creare una tabella
			DB::schema()->create("table",function(Blueprint $table){
				$table->id(); //crea un campo id (autoincremnet,unsigned,bigint(20))
				$table->string("field");
			});

			*/
		}


		return $res;
	}


	/*
		OVERRIDE UNINSTALL
	*/
	function uninstall(){
		$res = parent::uninstall();
		if( $res ){
			/*
			//per cancellare una tabella
			DB::schema()->dropIfExists("table");
			*/

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
