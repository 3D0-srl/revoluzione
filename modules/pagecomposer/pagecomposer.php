<?php
class PagecomposerInstaller extends Module{



    function install(){
        $res = parent::install();
        if($res){

            $db = Marion::getDB();
            $db->insert('page_advanced',
                array(
                    'id' => 1,
                    'id_layout' => 2
                )
            );

        }
    }


    function uninstall(){
        $res = parent::uninstall();
        if($res){
            
        }
    }
}
?>