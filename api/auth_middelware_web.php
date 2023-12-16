<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/api/includes.php');

$headers = apache_request_headers();
// print_r();
try {
    $bearer = $_GET['t'];

    $isValid = validate($bearer);

    if (!$isValid) {
        return Error(401, 'Invalid session token');
    }
} catch (Exception $th) {
    return Error(401, 'Invalid session token');
}

