<?php
require ('config/include.inc.php');
if( _MARION_ADMIN_REDIRECT_ENABLED_ ){
    header('Location: '._MARION_BASE_URL_.'backend/index.php');
}


//richiamo il router per smistare la richiesta al controller di competenza
$router = new Marion\Core\Router();
$router->dispatch();


?>
