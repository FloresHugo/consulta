<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['paths'])) {
    define('path', $_SERVER['DOCUMENT_ROOT'] . '/');
    $_SESSION['paths'] = path . 'includes/path.php';
}
require_once($_SESSION['paths']);


require_once($enviroment['db']);
require_once($enviroment['function']);
require_once('searchActions.php');
$type = empty($_REQUEST['type']) || !isset($_REQUEST['type']) ? '' : $_REQUEST['type'];

switch ($type) {
    case 'search':
        search();
        exit;
        break;
    case 'edit':
        edit();
        exit;
        break;
    case 'delete':
        delete();
        exit;
        break;

    default:
        if ($_SERVER['REQUEST_METHOD'] == 'GET'){
            http_response_code(404);
            exit;
        }
        save();

        break;
}

function search()
{
    header('Content-Type: application/json; charset=utf-8');
    $params = validateParams(['permiso']);
    if (!$params) {
        $response['status'] = 'ERROR';
        $response['msg'] = 'ERROR';
        echo json_encode($response);
        return;
    }

    $value = $_GET['permiso'];

    $sql = "
        SELECT TPD.PLACE_ID,TPD.CALLE,TPD.NOMBRE,TPD.PERMISO,TEM.ESTADO_ACENTOS AS ESTADO,
        TEM.MUNICIPIO_ACENTOS AS MUNICIPIO, TPD.CALLE, TPD.MARCA 
        FROM TBL_PLACES_DA AS TPD 
        LEFT JOIN TBL_PLACES_VAL AS TPV ON TPD.PLACE_ID=TPV.PLACE_ID 
        LEFT JOIN TBL_ESTADOS_MUNICIPIOS AS TEM ON TPV.ID_ESTADO=TEM.ID_ESTADO 
        AND TPV.ID_MUNICIPIO=TEM.ID_MUNICIPIO 
        WHERE TPD.PERMISO='{$value}'
    ";

    $result = mysqli_fetch_assoc(execQuery($sql));

    if (!$result) {
        $response['status'] = 'ERROR';
        $response['msg'] = 'ERROR';
        echo json_encode($response);
        return;
    }
    $response['status'] = 'OK';
    $response['data'] = $result;
    echo json_encode($response);
    return;
}

function save()
{
    $params = validateParams(['permiso', 'alias']);
    if (!$params) {
        $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Debe llenar todos los campos solicitados");
        echo "<script>window.history.go(-1);</script>"; 
        return;
    }
    $permiso = $_POST['permiso'];
    $alias = $_POST['alias'];
    $user = $_SESSION['user'];

    if (isMaxRecords()){
        $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Registro máximo alcanzado");
        echo "<script>window.history.go(-1);</script>";
        return;
    }
    $sql = "INSERT INTO TBL_CONSULTAS_USUARIOS_PERMISOS (permiso,alias,user)
	VALUES ('{$permiso}','{$alias}',{$user});";

    $result = execQuery($sql);
    if (!$result) {
        $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Error al registrar el permiso");
        echo "<script>window.history.go(-1);</script>";
        return;
    }
    $_SESSION['msg'] = array("tipo" => "OK", "msg" => "Permiso registrado correctamente");
    echo "<script>window.history.go(-1);</script>";
    return;
}

function edit()
{
    $params = validateParams(['permiso', 'alias']);
    if (!$params) {
        $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Debe llenar todos los campos solicitados");
        echo "<script>window.history.go(-1);</script>";
        return;
    }
    $permiso = $_POST['permiso'];
    $alias = $_POST['alias'];
    $user = $_SESSION['user'];

    $sql = "UPDATE TBL_CONSULTAS_USUARIOS_PERMISOS SET alias='{$alias}'
	WHERE id={$permiso} AND user = {$user};";

    $result = execQuery($sql);
    if (!$result) {
        $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Error al registrar el permiso");
        echo "<script>window.history.go(-1);</script>";
        return;
    }
    $_SESSION['msg'] = array("tipo" => "OK", "msg" => "Permiso registrado correctamente");
    echo "<script>window.history.go(-1);</script>";
    return;
}

function delete(){
    $permiso = $_POST['permiso'];
    $user = $_SESSION['user'];

    $sql = "SELECT created_at as date FROM TBL_CONSULTAS_USUARIOS_PERMISOS WHERE id={$permiso} ;";

    $date = mysqli_fetch_assoc(execQuery($sql));

    $delete = CanDelete($date['date']);

    if(!$delete){
        $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "No puedes eliminar el permiso");
        echo "<script>window.history.go(-1);</script>";
        return;
    }


    $sql = "DELETE FROM TBL_CONSULTAS_USUARIOS_PERMISOS WHERE id={$permiso} AND user = {$user};";

    $result = execQuery($sql);
    if (!$result) {
        $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Error al eliminar el permiso");
        echo "<script>window.history.go(-1);</script>";
        return;
    }
    $_SESSION['msg'] = array("tipo" => "OK", "msg" => "Permiso registrado correctamente");
    echo "<script>window.history.go(-1);</script>";
    return;
}


