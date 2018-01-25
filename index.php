<?php

require 'vendor/autoload.php';
require 'include/DbOperation.php';

$app = new Slim\App();

//$app->get('/', function ($request, $response, $args) {
//    $response->write("Welcome to Slim!");
//    return $response;
//});
//$app->get('/hello[/{name}]', function ($request, $response, $args) {
//    $response->write("Hello, " . $args['name']);
//    return $response;
//})->setArgument('name', 'World!');

$app->get('/login', function($request, $response, $args) {
    $db = new DbOperation();
    return $response->write($request + '');
});

$app->run();