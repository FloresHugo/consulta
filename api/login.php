<?php  
require_once('./includes.php');

main();

function main(){
    $params = paramsFromJson();
    $paramsToValidate = validateParamsJson($params,['user','password']);
    if (!$paramsToValidate) {
        return Error(400);
    }
    $login = loginApi($params['user'],$params['password']);
    if(!$login){
        return Error(401);
    }
    $payload = [
        'user' => $params['user'],
        'key' => $login
    ];
    $jwt = generate($payload);
    return Ok('Login successful',['token' => $jwt]);
}