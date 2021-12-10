<?php
global $_routes;

// /it/news/{{slug}}/titolo-news
$_routes["/news/([0-9]+)-(.*)"] = [
    "controller" => "NewsController",
    "module" => "news",
    "method" => "view"
];
$_routes["/n/([0-9]+)-(.*)"] = [
    "controller" => "NewsController",
    "module" => "news",
    "method" => "view"
];

$_routes["/news/(.*)"] = [
    "controller" => "NewsController",
    "module" => "news",
    "method" => "all"
];
$_routes["/n/(.*)"] = [
    "controller" => "NewsController",
    "module" => "news",
    "method" => "all"
];

$_routes["/news"] = [
    "controller" => "NewsController",
    "module" => "news",
    "method" => "all"
];
/*
$_routes["/news"] = [
    "controller" => "NewsController",
    "module" => "news",
    "method" => "view"
];
*/
?>
