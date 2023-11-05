<?php
date_default_timezone_set('America/Mexico_City');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['paths'])) {
    define('path', $_SERVER['DOCUMENT_ROOT'] . '/');
    $_SESSION['paths'] = path . 'includes/path.php';
}
require_once($_SESSION['paths']);

if (!isset($_SESSION['user'])) {
    header("location:" . $routs['login']);
    exit;
}

require_once($enviroment['function']);

$params = validateParams(['permiso']);
if (!$params) {
    if (isset($_SERVER['HTTP_REFERER']))
        header('location:' . $_SERVER['HTTP_REFERER']);

    echo "<script>window.history.go(-1);</script>";
    exit;
}

$permiso = $_GET['permiso'];


require_once($enviroment['db']);
require_once($enviroment['header']);

$user = $_SESSION['user'];
$sql = "SELECT TPD.PLACE_ID,TPD.CALLE,TPD.NOMBRE,TPD.PERMISO,TEM.ESTADO_ACENTOS AS ESTADO,
        TEM.MUNICIPIO_ACENTOS AS MUNICIPIO, TPD.CALLE, TPD.MARCA 
        ,TPV.LATITUD,TPV.LONGITUD, tcup.alias
        FROM TBL_PLACES_DA AS TPD 
        LEFT JOIN TBL_PLACES_VAL AS TPV ON TPD.PLACE_ID=TPV.PLACE_ID 
        LEFT JOIN TBL_ESTADOS_MUNICIPIOS AS TEM ON TPV.ID_ESTADO=TEM.ID_ESTADO 
        AND TPV.ID_MUNICIPIO=TEM.ID_MUNICIPIO 
        INNER JOIN TBL_CONSULTAS_USUARIOS_PERMISOS tcup ON tcup.permiso = TPD.PERMISO
        WHERE tcup.id= '{$permiso}'
       	AND user = {$user};";

$result = mysqli_fetch_assoc(execQuery($sql));

// print_r($result);
$currentDate = date('Y-m-d');
$sql = "SELECT FECHA_REGISTRO, time(FECHA_REGISTRO) FROM TBL_CARGAS 
        WHERE fecha_registro >= '{$currentDate} 00:00:00' 
        ORDER BY FECHA_REGISTRO 
        DESC LIMIT 1";


$load = mysqli_fetch_assoc(execQuery($sql));

$loadHour = date('H', strtotime($load['FECHA_REGISTRO']));



if (!is_array($result) || count($result) < 1) {
    // header('location:/');
}

?>

<link rel="stylesheet" href="/assets/css/datepicker-theme.css">
<link rel="stylesheet" href="/assets/css/jquery-ui.css">
<link rel="stylesheet" href="/css/custom.css">

<style>
    .date {
        width: 100% !important;
    }

    .contDate {
        width: 11% !important;
    }

    .invalid-feeback {
        font-size: 7em;
    }

    .th {
        background-color: #e6e6e6;
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
        display: table-cell;
        vertical-align: inherit;
        font-weight: bold;
        text-align: -internal-center;
    }
</style>

<div class="container my-5">
    <div class="row overflow-auto">
        <div class="col d-flex justify-content-center flex-wrap">
            <div class="card text-center p-3 mx-md-3 mx-lg-3 mobile-width mx-1">
                <strong class="h">$<span id="price">0</span></strong>
                <span>MXN/Litro</span>
                <span class="mobile-description">Precio promedio
                    <a tabindex="0" role="button" data-toggle="popover" data-trigger="hover" data-content="Precio promedio de los competidores excluyendo precios atípicos y antiguos en el periodo seleccionado.">
                        <i class="fas fa-info-circle"></i>
                    </a>
                </span>
            </div>
            <div class="card text-center p-3 mx-md-3 mx-lg-3 mobile-width mx-1">
                <strong class="h"><span id="average">0</span></strong>
                <span class="mobile-description">Días promedio sin</span>
                <span class="mobile-description">cambio de precio
                    <a tabindex="0" role="button" data-toggle="popover" data-trigger="hover" data-content="Días promedio sin cambiar precio de los competidores excluyendo precios atípicos y antiguos en el periodo seleccionado.">
                        <i class="fas fa-info-circle"></i>
                    </a>
                </span>
            </div>
            <div class="card text-center p-3 mx-md-3 mx-lg-3 mobile-width mx-1">
                <strong class="h"><span id="others">0</span></strong>
                <span class="mobile-description">Número de</span>
                <span class="mobile-description">competidores</span>
            </div>
            <div class="card text-center p-3 mx-md-3 mx-lg-3 mobile-width mx-1">
                <strong class="h"><span id="distance">0</span></strong>
                <span class="mobile-description">Metros</span>
                <span class="mobile-description">distancia promedio</span>
            </div>
            <div class="card text-center p-3 mx-md-3 mx-lg-3 mobile-width mx-1">
                <strong class="h"><span id="products">0</span></strong>
                <span class="mobile-description">Número de</span>
                <span class="mobile-description">expide la estación analizada</span>
            </div>
        </div>
    </div>
    <div class="row mt-5">
        <div class="col d-flex flex-row justify-content-center justify-content-center flex-wrap" style="gap:20px;">
            <p class="mx-2"><?php echo $result['PERMISO'] ?></p>
            <p class="mx-2"><?php echo $result['alias'] ?></p>
            <p for="">Combustible</p>
            <select class="form-control w-auto option" name="fuel" id="fuel">
                <option value="1">REGULAR</option>
                <option value="2">PREMIUM</option>
                <option value="3">DIESEL</option>
            </select>
            <p>Inicio</p>
            <div class="d-flex flex-column contDate">
                <input type="text" class="option form-control date " id="startDate" autocomplete="off" name="startDate" aria-describedby="validationStartDate">
                <div id="validationStartDate" class="invalid-feedback">
                    La fecha de inicio no debe ser mayor a la fecha de fin.
                </div>
            </div>
            <p>Fin</p>
            <div class="d-flex flex-column contDate">
                <input type="text" class="option form-control date" id="endDate" autocomplete="off" name="endDate" aria-describedby="validationEndDate">
                <div id="validationEndDate" class="invalid-feedback">
                    La fecha de fin no debe ser menor a la fecha de inicio.
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5 d-flex">
        <div class="col-md-9 col-sm-12 order-sm-1 order-md-0">
            <div id="map"></div>
            <div id="div_leyenda" class="mt-5">

                <table class="table ">
                    <tbody>
                        <tr>
                            <th colspan="4" class="text-center">Simbología</th>
                        </tr>
                        <tr>
                            <td>Precio máximo</td>
                            <td class="text-center">
                                <img src="https://petrointelligence.com/sistema/rojo_t.png" style="width:15px; margin-top:-5px; margin-left:10px;">
                            </td>
                            <td>Precio mínimo</td>
                            <td class="text-center">
                                <img src="https://petrointelligence.com/sistema/verde_t.png" style="width:15px; margin-top:-5px; margin-left:10px;">
                            </td>
                        </tr>
                        <tr>
                            <td>Precio antiguo<sup>1</sup></td>
                            <td class="text-center">
                                <img src="https://petrointelligence.com/sistema/relojpi.png" style="width:15px; margin-top:-5px; margin-left:10px;">
                            </td>
                            <td ">No disponible<sup>2</sup></td>
                            <td class=" text-center"><img src="https://petrointelligence.com/sistema/nd.png" style="width:15px; margin-top:-5px; margin-left:10px;"></td>
                        </tr>
                        <tr>
                            <td>Incidente Profeco</td>
                            <td class="text-center"><img src="https://petrointelligence.com/sistema/indices/litros.png" style="width:15px; margin-top:-5px; margin-left:10px;"></td>
                            <td>Tarjeta de control</td>
                            <td class="text-center"><img src="https://petrointelligence.com/sistema/indices/tarjeta.png" style="width:20px; margin-top:-5px; margin-left:10px;"></td>
                        </tr>
                        <tr>
                            <td>Tienda de autoservicio</td>
                            <td class="text-center"><img src="https://petrointelligence.com/sistema/indices/tienda.png" style="width:15px; margin-top:-5px; margin-left:10px;"></td>
                            <td>Restaurante</td>
                            <td class="text-center"><img src="https://petrointelligence.com/sistema/indices/restaurante.png" style="width:20px; margin-top:-5px; margin-left:10px;"></td>
                        </tr>
                    </tbody>
                </table>
                <p style="font-size:10px; line-height:1.5em; font-weight:normal;"><br><sup>1</sup> Los precios antiguos son aquellos que tienen más de 80 días sin ser actualizados en las zonas urbanas y 90 días, en las zonas rurales.<br><sup>2</sup> No disponible hace referencia a precios atípicos, no reportados o cuando la gasolinera no expende el combustible.</p>
                <div style="clear:both"></div>
                <br>
                <br>
            </div>
        </div>
        <div class="col-md-3 col-sm-12 order-sm-0 order-md-1">
            <p>Cortes</p>
            <div class="d-flex justify-content-between">
                <div class="d-flex flex-column">
                    <div class="custom-control custom-switch">
                        <input type="radio" class="option loads custom-control-input" id="c-1" name="corte" value='01:00:00'>
                        <label class="custom-control-label" for="c-1">1:00</label>
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="radio" class="option loads custom-control-input" id="c-6" name="corte" value='06:00:00'>
                        <label class="custom-control-label" for="c-6">6:00</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="radio" class="option loads custom-control-input" id="c-8" name="corte" value='08:00:00'>
                        <label class="custom-control-label" for="c-8">8:00</label>
                    </div>
                </div>
                <div class="d-flex flex-column">
                    <div class="custom-control custom-switch">
                        <input type="radio" class="option loads custom-control-input" id="c-11" name="corte" value='11:00:00'>
                        <label class="custom-control-label" for="c-11">11:00</label>
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="radio" class="option loads custom-control-input" id="c-15" name="corte" value='15:00:00'>
                        <label class="custom-control-label" for="c-15">15:00</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="radio" class="option loads custom-control-input" id="c-19" name="corte" value='19:00:00'>
                        <label class="custom-control-label" for="c-19">19:00</label>
                    </div>
                </div>

            </div>
            <small class="text-secondary">Hora del centro del país</small>

            <div class="w-100 mt-5 search-btn" id="search-btn" style="display: none;">
                <button class="w-100 btn btn-outline-dark">Consultar</button>
            </div>
        </div>

    </div>
    <div class="row mt-5">
        <div class="col-12 overflow-auto">
            <div class="d-flex align-content-center">
                <label class="d-flex mr-4">
                    <div class="here info"></div>
                    <p class="text-secondary ml-3">Permiso actual</p>
                </label>
                <label class="d-flex mr-4">
                    <div class="atypical info"></div>
                    <p class="text-secondary ml-3">Atípico*</p>
                </label>
                <label class="d-flex mr-4">
                    <div class="longSt info"></div>
                    <p class="text-secondary ml-3">Antiguo**</p>
                </label>
                <label class="d-flex mr-4">
                    <div class="at-lon info"></div>
                    <p class="text-secondary ml-3">Atípico y antiguo</p>
                </label>
            </div>
            <div class="text-right">
                <button href="#" class="btn btn-success btn-sm download download-btn " style="margin:2%;">Descargar Excel</button>
                <button class="btn btn-success btn-sm download-btn " id="download_all" style="margin:2%;">Descargar todo (Excel)</button>
                <button class="btn btn-primary btn-sm search-btn d-none" id="update_table">Actualizar tabla</button>
                <button class="btn btn-warning btn-sm  d-none" id="delete_selected">Eliminar selección</button>

            </div>
            <table class="table table-data" id="table-data">
                <thead>
                    <tr id="data-headers">
                        <td class="th" data-exclude="true">Seleciona competidor</td>
                        <th data-fill-color="e6e6e6" data-f-bold="true">Competidor</th>
                        <th data-fill-color="e6e6e6" data-f-bold="true">Nombre o razón social</th>
                        <th data-fill-color="e6e6e6" data-f-bold="true">Imagen comercial</th>
                        <th data-fill-color="e6e6e6" data-f-bold="true">Distancia lineal</th>
                    </tr>
                </thead>
                <tbody id="data">

                </tbody>
            </table>
            <p class="text-justify" style="font-size:.7rem;margin:0; ">* Los precios <span class="font-weight-bold">antiguos</span> son aquellos que tienen más de 80 días sin ser actualizados en las zonas urbanas y 90 días, en las zonas rurales.</p>
            <p class="text-justify" style="font-size:.7rem;margin:0;">** Los valores <span class="font-weight-bold">atípicos</span> son aquellos valores excesivamente altos o bajos, en relación al mercado respectivo.</p>
            <p class="text-justify" style="font-size:.7rem;margin:0;">*** Considera los competidores ubicados dentro de los 40 km a la redonda o los 20 más cercanos.</p>
        </div>
        <div id="all" class="d-none">
            <table class="table table-data" id="table-data-all">
                <thead>
                    <tr id="type_fuel">
                        <td colspan="4"></td>
                    </tr>
                    <tr id="data-headers-all">

                        <th data-fill-color="e6e6e6" data-f-bold="true">Competidor</th>
                        <th data-fill-color="e6e6e6" data-f-bold="true">Nombre o razón social</th>
                        <th data-fill-color="e6e6e6" data-f-bold="true">Imagen comercial</th>
                        <th data-fill-color="e6e6e6" data-f-bold="true">Distancia lineal</th>
                    </tr>
                </thead>
                <tbody id="data-all">

                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade  justify-content-center align-content-center align-items-center" id="loading" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog d-flex justify-content-center align-content-center align-items-center">
        <div class="spinner-border text-light" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>

<?php
require_once($enviroment['footer']);
if (isset($_SESSION['msg'])) {
    showMessage($_SESSION['msg']);
}
?>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB1M46qsHc1CSmoEaLa1cwRFihbT4sQOvo&amp;sensor=false"></script>
<script src="/js/consultas.js"></script>
<script src="/assets/js/jquery-ui.js" charset="utf-8"></script>
<script src="/js/dates.js"></script>
<script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>


<script>
    var lat = <?php echo $result['LATITUD'] ?>;
    var lng = <?php echo $result['LONGITUD'] ?>;
    var permiso = '<?php echo $result['PERMISO'] ?>';
    var loadTime = <?php echo $loadHour ?>;
    var originPermiso = <?php echo $permiso ?>;

    $(function() {
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
        $(function() {
            $('[data-toggle="popover"]').popover()
        })
        setDate();
        $('#loading').modal('show')
        main();
    });
</script>