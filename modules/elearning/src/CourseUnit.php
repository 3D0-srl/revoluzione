<?php
namespace Elearning;
use Marion\Core\Base;
use Illuminate\Database\Capsule\Manager as DB;
class CourseUnit extends Base{
    	// COSTANTI DI BASE
	const TABLE = 'course_unit'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'course_unit_lang'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'course_unit_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = ''; // email a cui inviare la notifica




    function afterSave(){
        parent::afterSave();
       
        if( $this->_oldObject->video_id  && $this->_oldObject->video_id  != $this->video_id ){
            $video = DB::table('course_video')->where('id',$this->_oldObject->video_id)->first();
           
            if( $video ){
                $filepath = str_replace(" ", "\\ ", $video->path);
                
                unlink(_MARION_MODULE_DIR_.'elearning/uploads/'.$filepath);
                DB::table('course_video')->where('id',$this->_oldObject->video_id)->delete();
                //debugga($filepath);exit;
            }
        }   
    }


     function getVideo(){
        $video = DB::table('course_video')->where('id',$this->video_id)->first();
        return $video;
     }
}

?>