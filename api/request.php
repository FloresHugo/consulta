<?php
define('HTTPMETHODS', array(
    'GET' => 'GET',
    'POST' => 'POST',
    'PUT' => 'PUT',
    'DELETE' => 'DELETE'
));

function requestType($type){
    // echo "{$_SERVER['REQUEST_METHOD']} !== {$type}"; exit;
    if ($_SERVER['REQUEST_METHOD'] !== $type) {
        Error(405);
        exit;
    }
}