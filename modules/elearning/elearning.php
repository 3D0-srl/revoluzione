
<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
class Elearning extends Marion\Core\Module{
	/*
		OVERRIDE INSTALL
	*/
	function install(){
		$res = parent::install();
		if( $res ){
			
			
			DB::schema()->create("course_detail",function(Blueprint $table){
				$table->id(); //crea un campo id (autoincremnet,unsigned,bigint(20))
				$table->bigInteger("course_id")->unsigned(true)->index('course_id');
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


}
?>
