<?php
require_once('auth_middelware.php');
$include = true;

require_once($_SERVER['DOCUMENT_ROOT'] . "/actions/searchActions.php");

$type = empty($_REQUEST['type']) || !isset($_REQUEST['type']) ? '' : $_REQUEST['type'];
switch ($type) {
    case 'search':
        RequestType(HTTPMETHODS['GET']);
        searchApi();
        break;
    case 'edit':
        RequestType(HTTPMETHODS['POST']);
        editApi();
        break;
    case 'delete':
        RequestType(HTTPMETHODS['DELETE']);
        deleteApi();
        break;

    default:
    return Ok();
        RequestType(HTTPMETHODS['POST']);
        saveApi();

        break;
}

function searchApi()
{
    $params = validateParams(['permiso']);
    if (!$params) {
        Error(401);
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
    // print_r($result); exit;

    if (!$result) {
        Error(404);
        return;
    }
    Ok("",$result);
    return;
}

function saveApi()
{
    global $bearer;
    $params = paramsFromJson();
    $paramsToValidate = validateParamsJson($params,['permit', 'alias']);
    if (!$paramsToValidate || count($params) == 0) {
        return Error(400);
    }
    $permiso = $params['permit'];
    $alias = $params['alias'];
    $claims = getClaims($bearer);
    $user = $claims->key;

    if (isMaxRecordsApi($user)) {        
        return Error(403, 'Record limit reaching');
    }
    $sql = "INSERT INTO TBL_CONSULTAS_USUARIOS_PERMISOS (permiso,alias,user)
	VALUES ('{$permiso}','{$alias}',{$user});";

    $result = execQuery($sql);
    if (!$result) {
        return Error(500);
    }
    return Ok();
}

function isMaxRecordsApi($user){
    $allowedRecords = getAllowedRecords($user);
    $savedRecords = getCountRecords($user);
    return $savedRecords == $allowedRecords;
}

function editApi()
{
    global $bearer;
    $params = paramsFromJson();
    $paramsToValidate = validateParamsJson($params, ['record', 'alias']);
    if (!$paramsToValidate || count($params) == 0) {
        return Error(400);;
    }
    $permiso = $params['record'];
    $alias = $params['alias'];
    $claims = getClaims($bearer);
    $user = $claims->key;

    $sql = "UPDATE TBL_CONSULTAS_USUARIOS_PERMISOS SET alias='{$alias}'
	WHERE id={$permiso} AND user = {$user};";

    $result = execQuery($sql);
    if (!$result) {
        return Error(500);
    }
    return Ok();
}

function deleteApi()
{
    global $bearer;
    $params = paramsFromJson();
    $paramsToValidate = validateParamsJson($params, ['record']);
    if (!$paramsToValidate || count($params) == 0) {
        return Error(400);;
    }
    $permiso = $params['record'];
    $claims = getClaims($bearer);
    $user = $claims->key;

    $sql = "SELECT created_at as date FROM TBL_CONSULTAS_USUARIOS_PERMISOS WHERE id={$permiso} ;";
    $date = mysqli_fetch_assoc(execQuery($sql));
    $delete = CanDelete($date['date']);

    if (!$delete) {
        return Error(403);
    }

    $sql = "DELETE FROM TBL_CONSULTAS_USUARIOS_PERMISOS WHERE id={$permiso} AND user = {$user};";

    $result = execQuery($sql);
    if (!$result) {
        return Error(500);
    }
    return Ok();
}
