<?php
define('include', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/auth_middelware_web.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/actions/data.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/api/web/Table.php");

$params = validateParams(['record']);
if (!$params) {
    // Error(401);
    exit;
}
$record = $_GET['record'];
$claims = getClaims($bearer);
$auth = $claims->auth;
$startDate = $claims->startDate;
$endDate = $claims->endDate;
$endDate = $claims->endDate;
$permit = $claims->permit;
$fuel = $claims->fuel;
$time = $claims->time;
$current = $record;
$latitud = $claims->latitud;
$longitud = $claims->longitud;
$reload = false;

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
$l = $data['data']['history'];
usort($data['data']['history'], 'sortByDistance');
// json_response([$data['data']['history'],$l]); exit;
$size = [216 , 220];
$days = count($data['data']['days']);
if($days > 1){
    $size[1] = $size[0] + 23 * $days;
    $size[0] = $size[0] + 50;
    
}
$pdf = new Table('L', 'mm', $size);

$pdf->AddPage();
$widths = [33,55,25,25];
$headers = ['Competidor', '	Nombre o razón social', 'Imagen comercial', 'Distancia lineal'];
foreach ($data['data']['days'] as $day) {
    $headers[] = $day;
    $widths[] = 23; 
}

$headers[] = 'Última actualización';
$widths[] = 33; 
$pdf->SetWidths($widths);
$pdf->SetHeader($headers);
$isFirts = true;
$fill = true;
foreach ($data['data']['history'] as $h) {
    $row = [$h['permiso'], $h['name'], $h['brand'], round($h['distance'], 2)];
    foreach ($h['days'] as $day) {
        $t = '';
        $t = $day['at'] ? 'at': '';
        $t = $day['longSt'] ? 'longSt' : '';
        $t = $day['longSt'] && $day['at'] ? 'twice' : '';
        $row[] = "\${$day['price']}#$t";
    }
    $row[] = $h['updated_at'];
    $pdf->Row($row,$fill,$isFirts);
    $fill = !$fill;
    $isFirts = false;
}


$pdf->Output();


function sortByDistance($a, $b)
{
    $distanceA = floatval($a['distance']);
    $distanceB = floatval($b['distance']);

    if ($distanceA == $distanceB) {
        return 0;
    }

    return ($distanceA < $distanceB) ? -1 : 1;
}