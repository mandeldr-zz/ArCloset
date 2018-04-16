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

$app->post('/createAvatar', function($request, $response, $args) {
    $gender = $request->getParsedBodyParam('gender');
    $height = $request->getParsedBodyParam('height');
    $skinColor = $request->getParsedBodyParam('skinColor');
    $hairColor = $request->getParsedBodyParam('hairColor');
    $hairLength = $request->getParsedBodyParam('hairLength');
    $apiKey = $request->getParsedBodyParam('apiKey');

    $db = new DbOperation();
    $success = $db->createAvatar($gender, $height, $skinColor, $hairColor, $hairLength, $apiKey);

    if($success == 0){
        $data = array('error' => false, 'message' => 'Avatar created successfully');
        return $response->withJson($data, 201);
    }
    elseif ($success == 1){
        $data = array('error' => true, 'message' => 'Failed to create avatar');
        return $response->withJson($data, 400);
    }
    else {
        $data = array('error' => true, 'message' => 'Avatar already exists');
        return $response->withJson($data, 400);
    }
});

$app->get('/login', function ($request, $response, $args) {
    $username = $request->getHeaderLine('username');
    $password = $request->getHeaderLine('password');

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

$app->get('/getAvatar', function ($request, $response, $args) {
    $apiKey = $request->getHeaderLine('apiKey');

    $db = new DbOperation();
    $success = $db->getAvatar($apiKey);

    if($success != null) {
        return $response->withJson($success, 200);
    }
    else {
        $data = array('error' => true, 'message' => 'API key not recognized');
        return $response->withJson($data, 400);
    }
});

$app->put('/updateAvatar', function ($request, $response, $args) {
    $gender = $request->getHeaderLine('gender');
    $height = $request->getHeaderLine('height');
    $skinColor = $request->getHeaderLine('skinColor');
    $hairColor = $request->getHeaderLine('hairColor');
    $hairLength = $request->getHeaderLine('hairLength');
    $apiKey = $request->getHeaderLine('apiKey');

    $db = new DbOperation();
    $success = $db->updateAvatar($gender, $height, $skinColor, $hairColor, $hairLength, $apiKey);

    if($success != null) {
        return $response->withJson($success, 200);
    }
    else {
        $data = array('error' => true, 'message' => 'API key not recognized');
        return $response->withJson($data, 400);
    }
});

//  ***************************************************
//                  Clothing Item Endpoints
//  ***************************************************

$app->post('/addClothingItem', function ($request, $response, $args) {
    $clothingID = $request->getParsedBodyParam('clothingID');
    $clothingType = $request->getParsedBodyParam('clothingType');
    $clothingMaterial = $request->getParsedBodyParam('clothingMaterial');
    $apiKey = $request->getParsedBodyParam('apiKey');

    $db = new DbOperation();
    $success = $db->addClothingItem($clothingID, $clothingType, $clothingMaterial, $apiKey);

    if($success == 0){
        $data = array('error' => false, 'message' => 'Clothing item added successfully');
        return $response->withJson($data, 201);
    }
    elseif ($success == 1){
        $data = array('error' => true, 'message' => 'Failed to add clothing item');
        return $response->withJson($data, 400);
    }
    else {
        $data = array('error' => true, 'message' => 'Clothing item already exists');
        return $response->withJson($data, 400);
    }

});

$app->put('/updateClothingItem', function ($request, $response, $args) {
    $clothingID = $request->getHeaderLine('clothingID');
    $clothingType = $request->getHeaderLine('clothingType');
    $clothingMaterial = $request->getHeaderLine('clothingMaterial');
    $apiKey = $request->getHeaderLine('apiKey');

    $db = new DbOperation();
    $success = $db->updateClothingItem($clothingID, $clothingType, $clothingMaterial, $apiKey);

    if($success == 0){
        $data = array('error' => false, 'message' => 'Clothing item updated successfully');
        return $response->withJson($data, 201);
    }
    elseif ($success == 1){
        $data = array('error' => true, 'message' => 'Failed to update clothing item');
        return $response->withJson($data, 400);
    }
    else {
        $data = array('error' => true, 'message' => 'Clothing item does not exist');
        return $response->withJson($data, 400);
    }

});

$app->get('/getClothingItem', function ($request, $response, $args) {
    $clothingID = $request->getHeaderLine('clothingID');

    $db = new DbOperation();
    $success = $db->getClothingItem($clothingID);

    if($success != null) {
        return $response->withJson($success, 200);
    }
    else {
        $data = array('error' => true, 'message' => 'Clothing ID not recognized');
        return $response->withJson($data, 400);
    }
});

$app->delete('/deleteClothingItem', function ($request, $response, $args) {
    $clothingID = $request->getHeaderLine('clothingID');

    $db = new DbOperation();
    $success = $db->deleteClothingItem($clothingID);

    if($success == 0) {
        array('error' => false, 'message' => 'Clothing item deleted successfully');
        return $response->withJson($data, 200);
    }
    elseif($success == 1) {
        $data = array('error' => true, 'message' => 'Unable to delete clothing item', 'resultStatus' => $success, 'clothingID' => $clothingID);
        return $response->withJson($data, 400);
    }
    else {
        $data = array('error' => true, 'message' => 'Clothing item does not exist', 'resultStatus' => $success, 'clothingID' => $clothingID);
        return $response->withJson($data, 400);
    }
});

$app->run();

