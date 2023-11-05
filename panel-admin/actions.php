<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// print_r($_SESSION);
if (!isset($_SESSION['paths'])) {
  define('path', $_SERVER['DOCUMENT_ROOT'] . '/');
  // define('path',$_SERVER['DOCUMENT_ROOT'] . '/');


  $_SESSION['paths'] = path . 'includes/path.php';
}
require_once($_SESSION['paths']);
if (!isset($_SESSION['user_admin'])) {
  header("location:" . $routs['login']);
}


// echo path;
// require_once(path.'includes/path.php');
require_once($_SESSION['paths']);
require_once($enviroment['function']);
require_once($enviroment['db']);

$action = $_POST['action'];

switch ($action) {
  case 'save':
    save();
    break;
  case 'edit':
    if (!isset($_POST['password']) || empty($_POST['password'])) {
      editWithoutPassword();
    } else {
      edit();
    }
    break;

  default:
    // code...
    break;
}

function save()
{
  $params = validateParams(['user_name', 'password']);
  if (!$params) {
    $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Debe llenar todos los campos");
    echo "<script>window.history.go(-1);</script>";
    exit;
  }
  $user = $_POST['user_name'];
  $password = $_POST['password'];
  $allowed = $_POST['allowed'];
  $password = password_hash($password, PASSWORD_DEFAULT);

  $sql = "INSERT INTO TBL_USER_CONSULTAS (email, password, name,permisos)
        VALUES ( NULL, '{$password}','{$user}',{$allowed});";

  $result = execQuery($sql);

  if ($result) {
    $_SESSION['msg'] = array("tipo" => "OK", "msg" => "Usuario guardado");
    echo "<script>window.history.go(-1);</script>";
    exit;
  }
  $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Error al guardar el usuario, intenta más tarde");
  echo "<script>window.history.go(-1);</script>";
  exit;
}

function edit()
{
  $params = validateParams(['user_name', 'password']);
  if (!$params) {
    $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Debe llenar todos los campos");
    echo "<script>window.history.go(-1);</script>";
    exit;
  }
  $user = $_POST['user_name'];
  $password = $_POST['password'];
  $id = $_POST['user'];
  $allowed = $_POST['allowed'];
  $password = password_hash($password, PASSWORD_DEFAULT);

  $sql = "UPDATE TBL_USER_CONSULTAS SET name = '{$user}',
    password = '{$password}',  permisos = '{$allowed}'
    WHERE (id = '{$id}');";

  $result = execQuery($sql);
  if ($result) {
    $_SESSION['msg'] = array("tipo" => "OK", "msg" => "Usuario actualizado");
    echo "<script>window.history.go(-1);</script>";
    exit;
  }
  $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Error al editar el usuario, intenta más tarde");
  echo "<script>window.history.go(-1);</script>";
  exit;
}
function editWithoutPassword()
{
  $params = validateParams(['user_name']);
  if (!$params) {
    $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Debe llenar todos los campos");
    echo "<script>window.history.go(-1);</script>";
    exit;
  }
  $user = $_POST['user_name'];
  $id = $_POST['user'];
  $allowed = $_POST['allowed'];

  $sql = "UPDATE TBL_USER_CONSULTAS SET name = '{$user}',
    permisos = '{$allowed}' WHERE (id = '{$id}');";

  $result = execQuery($sql);
  if ($result) {
    $_SESSION['msg'] = array("tipo" => "OK", "msg" => "Usuario actualizado");
    echo "<script>window.history.go(-1);</script>";
    exit;
  }
  $_SESSION['msg'] = array("tipo" => "ERROR", "msg" => "Error al editar el usuario, intenta más tarde");
  echo "<script>window.history.go(-1);</script>";
  exit;
}
