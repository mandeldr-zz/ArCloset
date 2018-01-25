<?php

require 'vendor/autoload.php';
require 'include/DbOperation.php';

$app = new Slim\App();

$app->post('/createUser', function($request, $response, $args) {
    $username = $request->getParsedBodyParam('username');
    $password = $request->getParsedBodyParam('password');

    $db = new DbOperation();
    $db->createUser($username, $password);
});

$app->run();