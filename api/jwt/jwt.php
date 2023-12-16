<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/api/includes.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

$key = "my_secret_key";

function generate($payload){
    global $key;

    $time = strtotime('+1 month', time());
    $payload['exp'] = $time;

    $jwt = JWT::encode($payload, $key, 'HS256');
    return $jwt;
}
function generateExternal($payload)
{
    global $key;

    $time = strtotime('+10 minutes', time());
    $payload['exp'] = $time;

    $jwt = JWT::encode($payload, $key, 'HS256');
    return $jwt;
}

function validate($jwt){
    global $key;
    try {
        JWT::decode($jwt, new Key($key, 'HS256'), $headers = new stdClass());
        return true;
    } catch (ExpiredException $th) {
        exit;
        return false;
    }

}

function getClaims($jwt){
    global $key;
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'), $headers = new stdClass());
    return $decoded;
}