<?php
require_once('auth_middelware.php');
define('include', true);

require_once($_SERVER['DOCUMENT_ROOT']."/actions/data.php");
$params = validateParams(['record']);
if (!$params) {
    Error(401);
    return;
}
mainDetail();

function mainDetail(){
    global $bearer;
    $claims = getClaims($bearer);
    $user = $claims->key;
    $record = $_GET['record'];
    $recordData = getCurrentrecord($record, $user);
    $latitud = $recordData['LATITUD'];
    $longitud = $recordData['LONGITUD'];
    $permit = $recordData['PERMISO'];
    $time = (isset($_GET['time']) && !empty($_GET['time'])) ? $_GET['time'] : getCutOffDate();
    $fuel = (isset($_GET['fuel']) && !empty($_GET['fuel'])) ? $_GET['fuel'] : getFuel($permit);
    $startDate = (isset($_GET['startDate']) && !empty($_GET['startDate'])) ? $_GET['startDate'] : date('Y-m-d');
    $endDate = (isset($_GET['endDate']) && !empty($_GET['endDate'])) ? $_GET['endDate'] : date('Y-m-d');
    $reload = (isset($_GET['reload']) && !empty($_GET['reload'])) ? $_GET['reload'] : false;

    $data = getData(
        $latitud,
        $longitud,
        $permit,
        $startDate,
        $endDate,
        $record,
        $reload,
        $fuel,
        $time
    );

    $payload['auth'] = $user;
    $payload['startDate'] = $startDate;
    $payload['endDate'] = $endDate;
    $payload['permit'] = $permit;
    $payload['fuel'] = $fuel;
    $payload['time'] = $time;
    $payload['current'] = $record;
    $payload['latitud'] = $latitud;
    $payload['longitud'] = $longitud;

    $jwtExternal = generateExternal($payload);
    $protocol = $_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';

    $data['data']['map'] = "{$protocol}://{$_SERVER['SERVER_NAME']}/api/web/map_view?record={$record}&t={$jwtExternal}";
    $data['data']['pdf'] = "{$protocol}://{$_SERVER['SERVER_NAME']}/api/web/history_pdf?record={$record}&t={$jwtExternal}";


    return OK("Response successful", $data['data']);
}


function getCurrentrecord($record,$user){
    $sql = "SELECT TPD.PLACE_ID,TPD.CALLE,TPD.NOMBRE,TPD.PERMISO,TEM.ESTADO_ACENTOS AS ESTADO,
        TEM.MUNICIPIO_ACENTOS AS MUNICIPIO, TPD.CALLE, TPD.MARCA 
        ,TPV.LATITUD,TPV.LONGITUD, tcup.alias
        FROM TBL_PLACES_DA AS TPD 
        LEFT JOIN TBL_PLACES_VAL AS TPV ON TPD.PLACE_ID=TPV.PLACE_ID 
        LEFT JOIN TBL_ESTADOS_MUNICIPIOS AS TEM ON TPV.ID_ESTADO=TEM.ID_ESTADO 
        AND TPV.ID_MUNICIPIO=TEM.ID_MUNICIPIO 
        INNER JOIN TBL_CONSULTAS_USUARIOS_PERMISOS tcup ON tcup.permiso = TPD.PERMISO
        WHERE tcup.id= '{$record}'
        AND user = {$user};";

    $result = mysqli_fetch_assoc(execQuery($sql));
    return $result;
}

function getCutOffDate(){
    $currentDate = date('Y-m-d');
    $sql = "SELECT FECHA_REGISTRO, time(FECHA_REGISTRO) FROM TBL_CARGAS 
        WHERE fecha_registro >= '{$currentDate} 00:00:00' 
        ORDER BY FECHA_REGISTRO 
        DESC LIMIT 1";


    $load = mysqli_fetch_assoc(execQuery($sql));

    $loadHour = date('H', strtotime($load['FECHA_REGISTRO']));
    return $loadHour.':00:00';
}

function getFuel($permit){
    $fuels = getTotalFuel($permit);
    $fuelList = $fuels['fuels'];
    $fuel = $fuelList[0];
    return $fuel;
}