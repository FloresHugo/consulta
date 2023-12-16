<?php
require_once('auth_middelware.php');
$include = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/actions/searchActions.php");
$include = true;

$claims = getClaims($bearer);
$userId = $claims->key;
$records = getRecords($userId);
$allowed = getAllowedRecords($userId);
$registered = getCountRecords($userId);
$response['records'] = $records;
$response['allowed'] = $allowed;
$response['registered'] = $registered;
$response['available'] = $allowed - $registered;
Ok('',$response);