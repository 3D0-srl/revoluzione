<?php
    use \Marion\Core\Marion;
    function google_analytics_snippet_header(){
        $html = Marion::getConfig('google_analytics_snippet','header');
        echo $html;
    }


    


    Marion::add_action('display_header','google_analytics_snippet_header');

?>