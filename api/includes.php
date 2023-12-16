<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['paths'])) {
    define('path', $_SERVER['DOCUMENT_ROOT'] . '/');
    $_SESSION['paths'] = path . 'includes/path.php';
}

$isApi = true;
require_once($_SESSION['paths']);
require_once($enviroment['function']);
require_once($enviroment['sesion']);
require_once($enviroment['db']);
require_once('response.php');
require_once('request.php');
require_once('jwt/jwt.php');