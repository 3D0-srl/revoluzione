<?php
global $_routes;
$_routes['/orders/([0-9]+)'] = [
    'controller' => 'OrdersController',
    'module' => 'ecommerce',
    'method' => 'view'
];
$_routes['/orders'] = [
    'controller' => 'OrdersController',
    'module' => 'ecommerce',
    'method' => 'index'
];
$_routes['/addresses/([0-9]+)'] = [
    'controller' => 'AddressController',
    'module' => 'ecommerce',
    'method' => 'add'
];
$_routes['/addresses'] = [
    'controller' => 'AddressController',
    'module' => 'ecommerce',
    'method' => 'index'
];
$_routes['/wishlist/(.*)'] = [
    'controller' => 'WishlistController',
    'module' => 'ecommerce',
    'method' => 'view'
];

$_routes['/wishlist'] = [
    'controller' => 'WishlistController',
    'module' => 'ecommerce',
    'method' => 'index'
];
?>