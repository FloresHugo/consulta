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

if (!defined('include')) {
    main();
}

function main(){
    if($_SERVER['REQUEST_METHOD']  == "DELETE"){
        parse_str(file_get_contents("php://input"), $delete_vars);
        $current =  $delete_vars['current'];
        return removeAssociated($current);
    }else{
        // sleep(3);
        getData();
    }
}

function getData($lat = 0,$lng = 0, $permiso ='',$startDate = '', $endDate = '',$id = 0, $reload = '',$fuel = '', $time = '')
{

    if (!defined('include')) {
        $lat = $_GET['lat'];
        $lng = $_GET['lng'];
        $permiso = trim($_GET['permiso']);
        $startDate = validateDate($_GET['startDate']);
        $endDate = $_GET['endDate'];
        $id  = $_GET['current'];

        $time = $_GET['time'];
        $reload = $_GET['reload'];
        $fuels = getTotalFuel($permiso);
        if (!isset($_GET['fuel']) && empty($_GET['fuel'])) {
            $fuelList = $fuels['fuels'];
            $fuel = $fuelList[0];
        } else {
            $fuel = $_GET['fuel'];
        }
    }else{
        $fuels = getTotalFuel($permiso);
        if (!isset($fuel) && empty($fuel)) {
            $fuelList = $fuels['fuels'];
            $fuel = $fuelList[0];
        } else {
            $fuel = $fuel;
        }
    }


    $associate = getAssociated($id);
    $locations = getLocations($lat, $lng,$permiso,$fuel);

    if (count($associate) > 0) {
        $locations = joinAssociatedLocations($locations, $associate,$permiso);
    }

    $permissionsToStr = "'{$permiso}'" . locationsToString($locations);
    $history = setDistance(getHistory($startDate,$endDate,$permissionsToStr,$fuel,$time), $locations);
    $associate = getAssociated($id);
    $extra = [];
    $selected = false;
    if (count($associate) > 0) {

        $joinHistory = joinAssociated($history, $associate);
        $history = $joinHistory['contains'];
        $extra = $joinHistory['no_contains'];
        $selected = true;
    }
    $clenPrices = cleanPrices($history, $permiso);
    $maxMin = getMinMax($clenPrices);
    $averages = getAverage($clenPrices);
    // $prices = getPricesByPeriod($permiso,$fuel,$startDate,$endDate);
    $days = getAverageDays($permiso, $fuel, $startDate, $endDate);

    $infoLocations = setInfoToLocations($locations, $history);
    
    $response['data']['locations'] = $infoLocations;
    $response['data']['history'] = $history;
    $response['data']['extra'] = $extra;
    $response['data']['selected'] = $selected;
    $response['data']['days'] = getIntervalDays($startDate, $endDate);
    $response['data']['maxMin'] = $maxMin;
    $response['data']['averages'] = $averages;
    $response['data']['cards']['stations'] = count($infoLocations) - 1;
    $response['data']['cards']['distance'] = getAverageDistance($locations);
    $response['data']['cards']['products'] = $fuels['total'];
    // $response['data']['cards']['average'] = getAveragePrices($prices);
    $response['data']['cards']['average'] = averageAllStations($averages);
    $response['data']['cards']['days'] = $days;
    $response['data']['fuels']['keys'] = $fuels['fuels'];
    $response['data']['fuels']['products'] = setProducts($fuels['fuels']);

    if(defined('include')){
        return $response;
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
}

function setProducts($products){
    
    $data= array_map(function($p){
        $productsList = [
            1 => 'REGULAR',
            2 => 'PREMIUM',
            3 => 'DIESEL'
        ];
        return $productsList[$p];
    },$products);

    return $data;
}

function averageAllStations($averages){
    $averagesLength = count($averages);
    if($averagesLength == 1) return array_values($averages)[0];

    $sum = 0;

    foreach ($averages as $key => $average) {
        $sum += $average;
    }
    if($averagesLength == 0) 
        $averagesLength =1;
    if ($sum == 0)
        $sum = 1;

    return round($sum / $averagesLength, 2);
}


function validateDate($date){
    $valueDate = new DateTime($date);
    $currentDate = new DateTime();

    $twoMonths = $currentDate->sub(new DateInterval('P2M'));

    return ($valueDate > $twoMonths) ?  $date : $twoMonths->format('Y-m-d');
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
            'vales' =>(int) $r['VALES'],
            'profeco' => (int) $r['PROFECO'],
            'wc' => (int) $r['BANOS'],
            'food' => (int) $r['RESTAURANTE'],
            'shop' => (int) $r['TIENDA'],
            'isCurrent' => $r['PERMISO'] == $permiso ? (int) 1 : (int) 0
        ];
    }
    
    return $locations;

    
}

function makeQuery($lat, $lng, $permiso, $fuel = 1, $ratio = 40 ){
    
    $limit = 21;
    $list = [
        'PL/5473/EXP/ES/2015' => 25,
    ];

    if(array_key_exists($permiso,$list)){
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

function getHistory($startDate, $endDate, $permissions,$fuel,$time)
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
            TPH.actualizado,
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
        WHERE TPH.combustible={$fuel}
        AND TPL.PERMISO in ({$permissions})
    ";
    // echo $sql; exit;
    $result = mysqli_fetch_all(execQuery($sql),MYSQLI_ASSOC);
    $ordered = orderData($startDate, $endDate,$result);
    return validatePricesDifference($ordered);


}

function locationsToString($locations){
    $permisos = '';
    foreach ($locations as $location) {
        
        $permisos .= ",";
        $permisos .= "'{$location['permiso']}'";
    }

    return $permisos;
}

function orderData($startDay,$endDay,$history){
    
    $data = [];

    foreach ($history as $h) {
        if(array_key_exists($h['permiso'],$data)){
            $data[$h['permiso']]['days'] = fillDays($data[$h['permiso']]['days'], $h['fecha_registro'], $h['precio'], $h['ATIPICO'], $h['ANTIGUO']);
        }else{
            $data[$h['permiso']] = array(
                'permiso' => $h['permiso'],
                'value' => $h['id'],
                'name' => $h['nombre'],
                'brand' => $h['imagen'],
                'distance' => 0,
                'days' => getInterval($startDay, $endDay, $h['fecha_registro'], $h['precio'],$h['ATIPICO'],$h['ANTIGUO']),
                'updated_at' => $h['actualizado'],
            );
        }
    }
    return $data;
}

function getInterval($startDay, $endDay,$day,$price,$atypical,$longSt){
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
        $days[$dayF] = array('day' => $day, 'price' => $price,'at' => intval($atypical),'longSt' => intval($longSt),'diff' => '');
    }

    return $days;
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

function fillDays($arr,$day,$price, $atypical, $longSt){
    $dayF = explode(" ", $day)[0];
    // echo $day;
    if (key_exists($dayF, $arr)) {
        $arr[$dayF] = array('day' => $day,'price'=> $price, 'at' => intval($atypical), 'longSt' => intval($longSt), 'diff' => '');
    }
    // $arr[$day] = 0;
    return $arr;
}

function setDistance($order,$distances){
    
    foreach ($distances as $distance) {
        if(key_exists($distance['permiso'],$order)){
            $order[$distance['permiso']]['distance'] = $distance['distance'];
        }
    }
    return $order;
}

function getMinMax($days){
    $maxMin = [];

    foreach ($days as $key => $day) {
        $daysOrdered = orderprices($day);
        $maxMin[$key]['max'] = $daysOrdered[count($daysOrdered) -1];
        $maxMin[$key]['min'] = $daysOrdered[0];
    }
    return $maxMin;
}

function cleanPrices($history, $permiso){
    if(key_exists($permiso,$history)){
        unset($history[$permiso]);
    }
    $days = [];

    foreach ($history as $h) {
        foreach ($h['days'] as $key => $day) {
            if ($day['at'] || $day['longSt'])
                continue;

            if (key_exists($key, $days)) {
                
                    $days[$key][] = $day['price'];
            } else {
                $days[$key][] = $day['price'];
            }
        }
    }

    return $days;
}

function orderPrices($prices){
    sort($prices);
    return $prices;
}

function getAverage($prices){
    $averages = [];
    foreach ($prices as $key => $day) {
        $averages[$key] = round(array_sum($day) / count($day),2);
    }
    return $averages;
}

function setInfoToLocations($locations,$history){  
    // print_r($history);  exit;
    if(empty ($history)) return $locations;

    $locationsNew = [];
    foreach ($locations as $key => $location) {
        if(!key_exists($location['permiso'],$history))
        continue;

        $MaxDay = array_pop($history[$location['permiso']]['days']);
        $locations[$key]['price']= $MaxDay['price'];
        $locations[$key]['at'] = $MaxDay['at'];
        $locations[$key]['long'] = $MaxDay['longSt'];
        $locationsNew[]= $locations[$key];
        // print_r($locationsNew);
        // exit;
    }

    $priceMin = array_reduce($locationsNew, "getMinPrice", $locationsNew[0]);
    $priceMax = array_reduce($locationsNew, "getMaxPrice", $locationsNew[0]);
    
    foreach ($locationsNew as $key => $location) {;
        $locationsNew[$key]['max'] = $locationsNew[$key]['permiso'] == $priceMax['permiso']
        ? 1 : 0;
        $locationsNew[$key]['min'] = $locationsNew[$key]['permiso'] == $priceMin['permiso']
        ? 1 : 0;
    }

    
    return $locationsNew;
}

function getMinPrice($min, $location)
{
    if ($location['price'] < $min['price']) {
        $min = $location;
    }
    return $min;
}

function getMaxPrice($max, $location)
{
    if ($location["price"] > $max['price']) {
        $max = $location;
    }
    return $max;
}

function getAverageDistance($locations){
    $distances = 0;

    foreach ($locations as $location) {
        $distances += $location['distance'];
    }

    $distance = round(($distances / (count($locations) - 1) * 1000),0);
    return number_format($distance);
}

function getTotalFuel($permiso){
    $sql = "SELECT COMBUSTIBLE FROM TBL_APP_DATOS WHERE PERMISO = '{$permiso}' ORDER BY COMBUSTIBLE ASC";
    $result = mysqli_fetch_all(execQuery($sql), MYSQLI_ASSOC);

    $data = [
        'total' => count($result), 
        'fuels' => array_map( function ($value) {
            return (int) $value["COMBUSTIBLE"];
        }, $result)
    ];
    // print_r($data); exit;
    return $data;
}

function getPrices($permiso, $fuel){
    $to = date('Y-m-d', strtotime(date('Y-m-d') . "+ 1 days"));
    $from = date('Y-m-d', strtotime($to . "- 2 months"));
    $corte = getCorte();

    $sql = "SELECT
            TPH.precio,
            TPH.actualizado
        FROM   TBL_PRECIOS_DA_HISTORICOS2 AS TPH
        INNER JOIN 
            (SELECT fecha_registro, Max(id_carga) AS ID_CARGA
            FROM   TBL_CARGAS
            WHERE  fecha_registro >= '{$from} 00:00:00'
            AND fecha_registro < '{$to} 00:00:00'
            and Time(fecha_registro) IN ('{$corte}')
            GROUP  BY fecha_registro ) MAX_C
        ON TPH.id_carga = MAX_C.id_carga
        INNER JOIN 
            (SELECT TP.permiso,
                TP.place_id,
                TRF.id_combustible
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
                TRF.id_combustible)
            AS TPL
        ON TPH.place_id = TPL.place_id
        AND TPH.combustible = TPL.id_combustible
        WHERE TPH.combustible={$fuel}
        AND TPL.PERMISO = '{$permiso}'
        ORDER BY actualizado DESC
    ";

    $result = execQuery($sql);
    $prices = [];
    foreach ($result as $r) {
        $prices[] = $r['precio'];
    }

    return $prices;
}
function getPricesByPeriod($permiso, $fuel, $startDate, $endDate)
{
    $to = date('Y-m-d', strtotime($endDate. "+ 1 days"));
    $from = date('Y-m-d', strtotime($startDate));
    $corte = getCorte();

    $sql = "SELECT
            TPH.precio,
            TPH.actualizado
        FROM   TBL_PRECIOS_DA_HISTORICOS2 AS TPH
        INNER JOIN 
            (SELECT fecha_registro, Max(id_carga) AS ID_CARGA
            FROM   TBL_CARGAS
            WHERE  fecha_registro >= '{$from} 00:00:00'
            AND fecha_registro < '{$to} 00:00:00'
            and Time(fecha_registro) IN ('{$corte}')
            GROUP  BY fecha_registro ) MAX_C
        ON TPH.id_carga = MAX_C.id_carga
        INNER JOIN 
            (SELECT TP.permiso,
                TP.place_id,
                TRF.id_combustible
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
                TRF.id_combustible)
            AS TPL
        ON TPH.place_id = TPL.place_id
        AND TPH.combustible = TPL.id_combustible
        WHERE TPH.combustible={$fuel}
        AND TPL.PERMISO = '{$permiso}'
        ORDER BY actualizado DESC
    ";
    // echo $sql; exit;

    $result = mysqli_fetch_all(execQuery($sql),MYSQLI_ASSOC);
    // print_r($result); exit;
    $prices = [];
    foreach ($result as $r) {
        $prices[] = $r['precio'];
    }
    // $a = mysqli_fetch_all($result);
    // print_r($prices); exit;
    return $prices;
}

function getAverageDays($permiso, $fuel, $startDate, $endDate)
{
    $to = date('Y-m-d', strtotime($endDate . "+ 1 days"));
    $from = date('Y-m-d', strtotime($startDate));
    $corte = getCorte();

    $sql = "SELECT DISTINCT 
            TPH.precio,
            Date(TPH.actualizado) as actualizado
        FROM   TBL_PRECIOS_DA_HISTORICOS2 AS TPH
        INNER JOIN 
            (SELECT fecha_registro, Max(id_carga) AS ID_CARGA
            FROM   TBL_CARGAS
            WHERE  fecha_registro >= '{$from} 00:00:00'
            AND fecha_registro < '{$to} 00:00:00'
            and Time(fecha_registro) IN ('{$corte}')
            GROUP  BY fecha_registro) MAX_C
        ON TPH.id_carga = MAX_C.id_carga
        INNER JOIN 
            (SELECT TP.permiso,
                TP.place_id,
                TRF.id_combustible
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
                TRF.id_combustible)
            AS TPL
        ON TPH.place_id = TPL.place_id
        AND TPH.combustible = TPL.id_combustible
        WHERE TPH.combustible={$fuel}
        AND TPL.PERMISO = '{$permiso}'
        ORDER BY actualizado DESC limit 1
    ";
    // echo $sql; exit;

    $result = mysqli_fetch_assoc(execQuery($sql));
    $date1 = new DateTime($result['actualizado']);
    $date2 = new DateTime($endDate);

    $diff = $date1->diff($date2);
    // $a = mysqli_fetch_all($result);
    // print_r($prices); exit;
    return $diff->format('%a');
}

function getAveragePrices($prices){
    if (count($prices) == 0) return 0;
    $average = array_sum($prices) / count($prices);

    return number_format($average,2, '.', '');
}

function getAverageChange($prices){
    if (count($prices) == 1) return 0;
    $actual = $prices[0];
    $counter = 0;

    // if(count($prices)==1) return '1';

    foreach ($prices as $price) {

        if($actual !== $price){
            return $counter;
        }
        $counter++;
    }

    return $counter;
}

function getCorte(){
    $hour = date('H');
    
    if ($hour >= 0 && $hour < 6) return '01:00:00';
    if ($hour >= 6 && $hour < 8) return '06:00:00';
    if ($hour >= 8 && $hour < 11) return '08:00:00';
    if ($hour >= 11 && $hour < 15) return '11:00:00';
    if ($hour >= 15 && $hour < 19) return '15:00:00';
    if ($hour >= 19 || $hour == 0 ) return '19:00:00';
}

function getAssociated($id){
    $sql = "SELECT * FROM TBL_VER_PERMISOS_SCPI WHERE registro = '$id'";
    $result = execQuery($sql);
    if(!$result){
        return [];
    }
    $associate = [];
    foreach ($result as $r) {
        $associate[] = $r['permiso'];
    }
    return $associate;
}

function joinAssociated($original, $associate){
    // print_r($original); exit;
    $permiso = trim($_GET['permiso']);
    $contains = [];
    $noContains = [];
    $contains[$original[$permiso]['permiso']] = $original[$permiso];
    foreach ($original as $ori) {
        if(in_array($ori['permiso'],$associate)){
            $contains[$ori['permiso']] = $ori;
        }else{
            $noContains[$ori['permiso']] = $ori;
        }
    }

    return ['contains' => $contains, 'no_contains' => $noContains];
}

function joinAssociatedLocations($original, $associate,$current)
{
    // print_r($original); exit;
    $permiso = trim($_GET['permiso']);
    $contains = [];
    $noContains = [];
    foreach ($original as $ori) {
        if($ori['permiso'] == $current){
            $contains[] = $ori;
        }
        if (in_array($ori['permiso'], $associate)) {
            $contains[] = $ori;
        } else {
            $noContains[] = $ori;
        }
    }

    return $contains;
}

function removeAssociated($id){
    $sql = "DELETE FROM TBL_VER_PERMISOS_SCPI WHERE registro = '$id'";
    execQuery($sql);
    return ;
}

function validatePricesDifference($prices){
    // $prices['PL/19330/EXP/ES/2016']['days'] = getDiffPrice($prices["PL/19330/EXP/ES/2016"]['days']);
    foreach ($prices as $key => $price) {
        $prices[$key]['days'] = getDiffPrice($price['days']);
    }
    // print_r($prices['PL/19330/EXP/ES/2016']);exit;

    return $prices;
}

function getDiffPrice($prices){
    $values = array_values($prices);
    $keys = array_keys($prices);
    for ($i=0; $i < count($values); $i++) {
        if($i != 0){            
            if ($values[$i - 1]['price'] > $values[$i]['price']) {
                $values[$i]['diff'] = 'down';
            }
            if ($values[$i - 1]['price'] < $values[$i]['price']) {
                $values[$i]['diff'] = 'up';
            }
        }

        
    }
    $prices = array_combine($keys, $values);
    return $prices;
}