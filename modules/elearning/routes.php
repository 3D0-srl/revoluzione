<?php
global $_routes;
$_routes['/courses/([0-9]+)'] = [
    'controller' => 'CourseController',
    'module' => 'elearning',
    'method' => 'view'
];
$_routes['/courses'] = [
    'controller' => 'CourseController',
    'module' => 'elearning',
    'method' => 'index'
];
?>