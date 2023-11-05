<?php
date_default_timezone_set('America/Mexico_City');
error_reporting(E_ALL);
ini_set('display_errors', '1');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['paths'])) {
    define('path', $_SERVER['DOCUMENT_ROOT'] . '/');
    $_SESSION['paths'] = path . 'includes/path.php';
}
require_once($_SESSION['paths']);


require_once($enviroment['db']);
require_once($enviroment['function']);

main();

function main(){
    $params = validateParams(['com','current', 'type']);
    if (!$params) {
        return response('ERROR','Información incompleta');
    }

    $com = $_POST['com'];
    $current = $_POST['current'];
    $type = $_POST['type'];

    if($type == 'false') return removeCom($current, $com);

    return addCom($current, $com);

}

function response($status,$msg='', $data = []){
    $response['status'] = $status;
    $response['data'] = $data;
    $response['msg'] = $msg;

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
}

function addCom($current, $com){
    $sql = "INSERT INTO TBL_VER_PERMISOS_SCPI (registro, permiso) VALUES ('{$current}', '{$com}')";
    $result = execQuery($sql);
    if(!$result) return response('ERROR','Error al registrar intenta nuevamente');

    return response('OK','Permiso registrado');
}

function removeCom($current, $com)
{
    $sql = "DELETE FROM TBL_VER_PERMISOS_SCPI WHERE registro = '$current' AND permiso = '$com'";
    $result = execQuery($sql);
    if (!$result) return response('ERROR', 'Error al registrar intenta nuevamente');

    return response('OK', 'Permiso actualizado');
}