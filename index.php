<?php

require 'vendor/autoload.php';
require 'include/DbOperation.php';

$app = new Slim\App();

$app->post('/createUser', function($request, $response, $args) {
    $username = $request->getParsedBodyParam('username');
    $password = $request->getParsedBodyParam('password');

    $db = new DbOperation();
    $success = $db->createUser($username, $password);

    if($success == 0){
        $data = array('error' => false, 'message' => 'User created successfully');
        return $response->withJson($data, 201);
    }
    elseif ($success == 1){
        $data = array('error' => true, 'message' => 'Failed to create user');
        return $response->withJson($data, 400);
    }
    else {
        $data = array('error' => true, 'message' => 'User already exists');
        return $response->withJson($data, 400);
    }
});

$app->get('/login', function ($request, $response, $args) {
    $username = $request->getHeaderLine('username');
    $password = $request->getHeaderLine('password');
    echo $username;
    echo $password;
    $db = new DbOperation();
    $success = $db->userLogin($username, $password);

    if($success == 0) {
        $data = array('error' => true, 'message' => 'Credentials are not recognized');
        return $response->withJson($data, 400);
    }
    elseif($success != 0) {
        $data = $db->getUserCredentials($username);
        return $response->withJson($data, 200);
    }
});

$app->run();