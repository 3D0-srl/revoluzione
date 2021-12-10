<?php

    use \Marion\Core\Marion;
    function google_tag_manager_snippet_header(){
        $html = Marion::getConfig('google_tag_manager_snippet','header');
        echo $html;
    }


    function google_tag_manager_snippet_body(){
        $html = Marion::getConfig('google_tag_manager_snippet','body');
        echo $html;
    }


    Marion::add_action('display_header','google_tag_manager_snippet_header');
    Marion::add_action('display_before_body','google_tag_manager_snippet_body');

?>