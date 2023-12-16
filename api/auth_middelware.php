<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/api/includes.php');

$headers = apache_request_headers();
// print_r();
try {
    if(!isset($headers['Authorization'])) {
        Error(401); 
        exit;
    }
    $authorization = $headers['Authorization'];
    $bearer = explode(' ', $authorization)[1];

    $isValid = validate($bearer);

    if (!$isValid) {
        return Error(401, 'Invalid session token');
    }
} catch (Exception $th) {
    return Error(401, 'Invalid session token');
}

