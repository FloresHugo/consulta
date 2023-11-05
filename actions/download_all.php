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

getData();

function getData()
{
    $lat = $_GET['lat'];
    $lng = $_GET['lng'];
    $permiso = trim($_GET['permiso']);
    $startDate = validateDate($_GET['startDate']);
    $endDate = $_GET['endDate'];
    $id  = $_GET['current'];
    
    $time = $_GET['time'];
    $fuels = getTotalFuel($permiso);
    $fuelList = $fuels['fuels'];
    $dataFuel = [];
    foreach ($fuelList as $fuel) {
        $locations = getLocations($lat, $lng, $permiso, $fuel);
        $permissionsToStr = "'{$permiso}'" . locationsToString($locations);
        $history = setDistance(getHistory($startDate, $endDate, $permissionsToStr, $fuel, $time), $locations);
        $dataFuel[$fuel] = $history;
    }

    $joinFuels = joinFuels($dataFuel);

    $response['data']['history'] = $joinFuels;
    $response['data']['total_fuels'] = $fuels['total'];
    $response['data']['fuels'] = $fuelList;
    $response['data']['days'] = getIntervalDays($startDate, $endDate);
    // $response['data']['other'] = $joinFuels;
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
}

function setDistance($order, $distances)
{

    foreach ($distances as $distance) {
        if (key_exists($distance['permiso'], $order)) {
            $order[$distance['permiso']]['distance'] = $distance['distance'];
        }
    }
    return $order;
}

function validateDate($date)
{
    $valueDate = new DateTime($date);
    $currentDate = new DateTime();

    $twoMonths = $currentDate->sub(new DateInterval('P2M'));

    return ($valueDate > $twoMonths) ?  $date : $twoMonths->format('Y-m-d');
}

function getTotalFuel($permiso)
{
    $sql = "SELECT COMBUSTIBLE FROM TBL_APP_DATOS WHERE PERMISO = '{$permiso}' ORDER BY COMBUSTIBLE ASC";
    $result = mysqli_fetch_all(execQuery($sql), MYSQLI_ASSOC);

    $data = [
        'total' => count($result),
        'fuels' => array_map(function ($value) {
            return (int) $value["COMBUSTIBLE"];
        }, $result)
    ];
    // print_r($data); exit;
    return $data;
}

function getLocations($lat, $lng, $permiso, $fuel = 1, $ratio = 40)
{
    $sql = makeQuery($lat, $lng, $permiso, $fuel, $ratio);
    $result = execQuery($sql);

    while ($result->num_rows < 21 && $ratio <= 70) {
        $ratio += 30;
        $sql = makeQuery($lat, $lng, $permiso, $fuel, $ratio);
        $result = execQuery($sql);
    }

    foreach ($result as $r) {
        $locations[] = [
            'lat' => $r['LATITUD'],
            'lng' => $r['LONGITUD'],
            'permiso' => $r['PERMISO'],
            'distance' => $r['DISTANCE'],
            'icon' => $r['ICONO'],
            'category' => $r['CATEGORIA'],
            'vales' => (int) $r['VALES'],
            'profeco' => (int) $r['PROFECO'],
            'wc' => (int) $r['BANOS'],
            'food' => (int) $r['RESTAURANTE'],
            'shop' => (int) $r['TIENDA'],
        ];
    }

    return $locations;
}

function makeQuery($lat, $lng, $permiso, $fuel = 1, $ratio = 40)
{

    $limit = 21;
    $list = [
        'PL/5473/EXP/ES/2015' => 25,
    ];

    if (array_key_exists($permiso, $list)) {
        $limit = $list[$permiso] + 1;
    }

    $sql = "
        SELECT distinct * FROM 
        (
            SELECT TPD.PLACE_ID as id ,TPD.CALLE,TPD.NOMBRE,TPD.PERMISO,TPV.LATITUD,TPV.LONGITUD, TPV.ICONO,
            (ST_Distance_Sphere(
                point(TPV.LONGITUD,TPV.LATITUD), point({$lng},{$lat})
            ) / 1000) DISTANCE,
            IFNULL(REP.CATEGORIA,'OK') AS CATEGORIA,
            CASE WHEN TPRL.PERMISO IS NULL THEN 0 ELSE 1 END AS PROFECO,
	        CASE WHEN TVL.PERMISO IS NULL THEN 0 ELSE 1 END AS VALES,
	        CASE WHEN TIN.BANOS = 0 THEN 0 ELSE 1 END AS BANOS,
	        CASE WHEN TIN.RESTAURANTE = 0 THEN 0 ELSE 1 END AS RESTAURANTE,
	        CASE WHEN TIN.TIENDA = 'Sin tienda' THEN 0 ELSE 1 END AS TIENDA,
            TAD.COMBUSTIBLE
            FROM TBL_PLACES_DA AS TPD
            inner join TBL_APP_DATOS TAD  on TPD.PERMISO = TAD.PERMISO
            LEFT JOIN TBL_PLACES_VAL AS TPV
            ON TPD.PLACE_ID=TPV.PLACE_ID
            LEFT JOIN (
                SELECT 
                    CASE CATEGORIA 
                        WHEN 1 
                            THEN 'COM' 
                        WHEN 2 
                            THEN 'DT' 
                        WHEN 3 
                            THEN 'DP' 
                        WHEN 4 
                            THEN 'OK' 
                        WHEN 5 
                            THEN 'CLOSED' 
                    END AS CATEGORIA,PERMISO 
                FROM TBL_REPORTES 
                ORDER BY ID_REPORTE DESC LIMIT 1
            ) AS REP
            ON TPD.PERMISO=REP.PERMISO
            LEFT JOIN TBL_PROFECO_LITROS AS TPRL ON TPD.PERMISO=TPRL.PERMISO
            LEFT JOIN TBL_INFRA AS TIN ON TPD.PERMISO=TIN.NO_PERMISO
            LEFT JOIN TBL_VALES AS TVL ON TPD.PERMISO=TVL.PERMISO
            WHERE TAD.COMBUSTIBLE = {$fuel}
        ) as T
        where DISTANCE < {$ratio}
        ORDER  by DISTANCE ASC 
        LIMIT {$limit};
    ";
    return $sql;
}

function getHistory($startDate, $endDate, $permissions, $fuel, $time)
{

    $from = $startDate;
    $to = date("Y-m-d", strtotime($endDate . "+ 1 days"));
    $sql = "
        SELECT
            TPL.id,
            TPL.permiso,
            TPL.nombre,
            TPL.marca,
            TPL.gie,
            TPL.imagen,
            TPH.precio,
            TPH.combustible,
            MAX_C.fecha_registro as fecha_registro,
            CASE
            WHEN TPL.urbana = 1
                    AND Timestampdiff(day, TPH.actualizado, MAX_C.fecha_registro) > 90
            THEN 1
            WHEN TPL.urbana = 0
                    AND Timestampdiff(day, TPH.actualizado, MAX_C.fecha_registro) > 80
            THEN 1
            ELSE 0
            END AS ANTIGUO,
            CASE
            WHEN TPH.precio BETWEEN TPL.min AND TPL.max THEN 0
            ELSE 1
            END AS ATIPICO
        FROM   TBL_PRECIOS_DA_HISTORICOS2 AS TPH
        INNER JOIN 
            (SELECT fecha_registro, Max(id_carga) AS ID_CARGA
            FROM   TBL_CARGAS
            WHERE  fecha_registro >= '{$from} 00:00:00'
            AND fecha_registro < '{$to} 00:00:00'
            and Time(fecha_registro) IN ('{$time}' )
            GROUP  BY fecha_registro ) MAX_C
        ON TPH.id_carga = MAX_C.id_carga
        INNER JOIN 
            (SELECT TP.id,TP.permiso,
                TP.place_id,
                TPV.id_municipio,
                TPV.id_estado,
                TPV.latitud,
                TPV.longitud,
                TP.nombre,
                TPV.marca,
                TPV.gie,
                TPV.imagen,
                TEM.urbana,
                TRF.id_combustible,
                TRF.min,
                TRF.max
            FROM   TBL_PLACES_DA AS TP
            INNER JOIN TBL_PLACES_VAL AS TPV
            ON TP.place_id = TPV.place_id
            LEFT JOIN TBL_ESTADOS_MUNICIPIOS AS TEM
            ON TPV.id_estado = TEM.id_estado
            AND TPV.id_municipio = TEM.id_municipio
            LEFT JOIN TBL_RANGOS_FC AS TRF
            ON TEM.frontera = TRF.frontera
            GROUP BY
                TP.permiso,
                TP.place_id,
                TPV.id_municipio,
                TPV.id_estado,
                TPV.latitud,
                TPV.longitud,
                TP.nombre,
                TPV.marca,
                TPV.gie,
                TPV.imagen,
                TEM.urbana,
                TRF.id_combustible)
            AS TPL
        ON TPH.place_id = TPL.place_id
        AND TPH.combustible = TPL.id_combustible
        WHERE TPH.combustible = {$fuel}
        AND TPL.PERMISO in ({$permissions})
    ";
    $result = mysqli_fetch_all(execQuery($sql), MYSQLI_ASSOC);
    return orderData($startDate, $endDate, $result);
}

function orderData($startDay, $endDay, $history)
{

    $data = [];

    foreach ($history as $h) {
        if (array_key_exists($h['permiso'], $data)) {
            $data[$h['permiso']]['days'] = fillDays($data[$h['permiso']]['days'], $h['fecha_registro'], $h['precio'], $h['ATIPICO'], $h['ANTIGUO']);
        } else {
            $data[$h['permiso']] = array(
                'permiso' => $h['permiso'],
                'value' => $h['id'],
                'name' => $h['nombre'],
                'brand' => $h['imagen'],
                'distance' => 0,
                'days' => getInterval($startDay, $endDay, $h['fecha_registro'], $h['precio'], $h['ATIPICO'], $h['ANTIGUO'])
            );
        }
    }
    return $data;
}

function getInterval($startDay, $endDay, $day, $price, $atypical, $longSt)
{
    $startDate = new DateTime($startDay);
    $endDate = new DateTime($endDay);
    // Necesitamos modificar la fecha final en 1 día para que aparezca en el bucle
    $endDate = $endDate->modify('+1 day');

    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($startDate, $interval, $endDate);

    $days = [];

    foreach ($period as $dt) {
        $days[$dt->format("Y-m-d")] = array('day' => $dt->format("Y-m-d"), 'price' => 0);
    }
    $dayF = explode(" ", $day)[0];
    if (key_exists($dayF, $days)) {
        $days[$dayF] = array('day' => $day, 'price' => $price, 'at' => intval($atypical), 'longSt' => intval($longSt));
    }

    return $days;
}

function locationsToString($locations)
{
    $permisos = '';
    foreach ($locations as $location) {

        $permisos .= ",";
        $permisos .= "'{$location['permiso']}'";
    }

    return $permisos;
}

function fillDays($arr, $day, $price, $atypical, $longSt)
{
    $dayF = explode(" ", $day)[0];
    // echo $day;
    if (key_exists($dayF, $arr)) {
        $arr[$dayF] = array('day' => $day, 'price' => $price, 'at' => intval($atypical), 'longSt' => intval($longSt));
    }
    // $arr[$day] = 0;
    return $arr;
}

function joinFuels($data){
    $joined = [];
    // echo json_encode($data);
    foreach ($data as $key => $value) {
        $fuelType = $key;
        foreach ($value as $h) {
            if (array_key_exists($h['permiso'], $joined)) {
                $joined[$h['permiso']]['fuels'][$fuelType]['days'] = $h['days'];
            } else {
                $joined[$h['permiso']] = array(
                    'permiso' => $h['permiso'],
                    'value' => $h['value'],
                    'name' => $h['name'],
                    'brand' => $h['brand'],
                    'distance' => $h['distance'],
                    'fuels' => [
                            $fuelType => [
                                'type' => $fuelType,
                                'days' => $h['days']
                            ]
                    ]
                    
                );
            }
        }
        

    }
    return $joined;
}

function getIntervalDays($startDay, $endDay)
{
    $startDate = new DateTime($startDay);
    $endDate = new DateTime($endDay);
    $endDate = $endDate->modify('+1 day');

    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($startDate, $interval, $endDate);

    $days = [];

    foreach ($period as $dt) {
        $days[] = $dt->format("Y-m-d");
    }

    return $days;
}