<?php
define('include', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/auth_middelware_web.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/actions/data.php");

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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map View</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js" crossorigin="anonymous"></script>
    <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB1M46qsHc1CSmoEaLa1cwRFihbT4sQOvo&amp;sensor=false&callback=initMap"></script>
</head>

<body>
    <div id="map" style="height: 100vh;"></div>
    <script>
        let lat = <?php echo $latitud; ?>;
        let lng = <?php echo $longitud; ?>;
        let markers = [];
        let marker;
        let map;



        function initMap() {
            const uluru = {
                lat: lat,
                lng: lng
            };
            // The map, centered at Uluru
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 9,
                center: uluru,
                disableDefaultUI: true,
            });



            new google.maps.Circle({
                strokeColor: "#04c3fd",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#04c3fd",
                fillOpacity: 0.35,
                map,
                center: uluru,
                radius: 40 * 1000,
            });
            let locationsRaw = JSON.stringify(<?php echo json_encode($data['data']['locations']) ?>);
            let locations = JSON.parse(locationsRaw);
            AddMarker(map, locations);
        }

        function setMapOnAll(map) {
            for (let i = 0; i < markers.length; i++) {
                markers[i].setMap(map);
            }
        }

        function AddMarker(map, locations) {

            let permiso;
            const url = 'https://petrointelligence.com/sistema/indices/';
            let j = 0;
            for (const i of locations) {
                permiso = i.permiso;
                let category = ({
                    'C': '1.png',
                    'DT': '2.png',
                    'DP': '3.png',
                    'OK': '4.png',
                    'CLOSED': 'cerrado.png',
                })[i.category];
                let pre = '';
                let services = '';
                if (i.long)
                    pre = `<img src="${url}../relojpi.png" style="width:15px;">`;
                if (i.min)
                    pre = `<img src="${url}../verde_t.png" style="width:15px;">`;
                if (i.max)
                    pre = `<img src="${url}../rojo_t.png" style="width:15px;">`;

                if (i.profeco)
                    services += `<img src="${url}litros.png" style="width:9px;">`;
                if (i.vales)
                    services += `<img src="${url}tarjeta.png" style="width:15px;">`;
                if (i.wc)
                    services += `<img src="${url}wc.png" style="width:15px;">`;
                if (i.food)
                    services += `<img src="${url}restaurante.png" style="width:15px;">`;
                if (i.shop)
                    services += `<img src="${url}tienda.png" style="width:15px;">`;

                current = '';
                if (i.isCurrent) {
                    current = '<i class="fas fa-location-arrow" style="color: #00b34d;"></i>';
                }

                let content = `
            <a style="font-size:12px;"> ${current} ${permiso}</a>
            <br>
            ${pre}
            <a style="font-size:15px;">${i.price}</a>
            <a style="width:15px;" href="https://petrointelligence.com/sistema/indices/gasolinera.php?permiso=${permiso}" target="_blank"> Más...</a>
            <br>
            <img src="${url}${category}" style="width:15px;"> 
            ${services}
        `;
                current = '';

                let infowindow = new google.maps.InfoWindow({
                    content: content,
                    ariaLabel: permiso,
                });
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(i.lat, i.lng),
                    title: permiso,
                    icon: `${url}${i.icon}`,
                    map: map
                });
                markers.push(marker);
                google.maps.event.addListener(marker, 'click', (function(marker) {
                    return function() {
                        infowindow.open(map, marker);
                    }
                })(marker, i));

                infowindow.open(map, marker);
            }
        }

        // main();
    </script>
</body>


</html>