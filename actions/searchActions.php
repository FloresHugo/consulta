<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['paths'])) {
    define('path', $_SERVER['DOCUMENT_ROOT'] . '/');
    $_SESSION['paths'] = path . 'includes/path.php';
}
require_once($_SESSION['paths']);


require_once($enviroment['db']);
if(!isset($include)){
    $allowedRecords = getAllowedRecords();
    $savedRecords = getCountRecords();
}
// print_r(getRecords());
function getRecords($user = null)
{
    $user = $user == null ? $_SESSION['user'] : $user;
    $sql = "SELECT * FROM TBL_CONSULTAS_USUARIOS_PERMISOS WHERE user = {$user} ORDER BY created_at desc"  ;
    $result = mysqli_fetch_all(execQuery($sql), MYSQLI_ASSOC);

    $data = [];

    foreach ($result as $row) {
        $data[] = [
            'id' => $row['id'],
            'permiso' => $row['permiso'],
            'alias' => $row['alias'],
            'canDelete' => canDelete($row['created_at'])
        ];
    }
    return $data;
}

function getAllowedRecords($user = null){
    $user = $user == null ? $_SESSION['user'] : $user;
    $sql = "SELECT permisos FROM TBL_USER_CONSULTAS WHERE id = {$user}";
    $result = mysqli_fetch_row(execQuery($sql));
    return $result[0];
}
function getCountRecords($user = null){
    $user = $user == null ? $_SESSION['user'] : $user;
    $sql = "SELECT COUNT(1) as total FROM TBL_CONSULTAS_USUARIOS_PERMISOS WHERE user = {$user}";
    $result = mysqli_fetch_row(execQuery($sql));
    return $result[0];
}

function getAvailableRecords(){
    global $savedRecords;
    global $allowedRecords;

    return $allowedRecords - $savedRecords;
}

function isMaxRecords(){
    global $savedRecords;
    global $allowedRecords;
    return $savedRecords == $allowedRecords;
}

function canDelete($date){
    $currentTime = new DateTime();
    $createdDate = new DateTime($date);

    return $createdDate->diff($currentTime)->days >= 15;
}