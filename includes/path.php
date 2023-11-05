<?php
define('root', $_SERVER['DOCUMENT_ROOT'] . '/');
$ruta = "/";
$server_name = 'http://' . $_SERVER['SERVER_NAME'];


// $assets = ruta;
$enviroment  = array(
  'db' => root . 'db.php',
  'pdoBd' => root . 'DbPdo.php',
  'function' => root . 'includes/functions.php',
  'header' => root . 'layouts/header.php',
  'footer' => root . 'layouts/footer.php',
  'fpdf' => root . 'includes/fpdf/fpdf.php',
  'report' => root . 'panel-admin/nominaciones/report/report.php',
  'sesion' => root . 'login/sesion.php',

  //Admin resources

  'header-admin' => root . 'panel-admin/layouts/header.php',
  'footer-admin' => root . 'panel-admin/layouts/footer.php',
  'analytics' => root . 'includes/analytics.php',
  'adminContent' => root . 'panel-admin/adminContent.php',

);
// global $routs;
$routs = array(
  'home' => $ruta,
  'login-admin' => $ruta . 'panel-admin/login/',
  'login' => $ruta . 'login/',
  'admin' => $ruta . 'panel-admin/',
  // 'clientes' => $ruta . 'clientes/',
  'cliente' => $ruta . 'clientes/',
  'usuarios' => $ruta . 'panel-admin/usuarios/',
  'analytics' => $ruta . 'includes/analytics',
  'salir' => $ruta . 'panel-admin/login/sesion?c',
  'sesion' => $ruta . 'panel-admin/login/sesion.php',

  //Acceso
  //Rutas Admin

);

function assets($valor)
{
  global $ruta;
  echo $ruta . $valor;
}
function css($valor)
{
  global $ruta;
  echo $ruta . 'css/' . $valor;
}
function js($valor)
{
  global $ruta;
  echo $ruta . 'js/' . $valor;
}
function images($valor)
{
  global $ruta;
  echo $ruta . 'images/' . $valor;
}

function route($r)
{
  global $routs;
  echo $routs[$r];
}
// function route($r, $params =''){
//   global $route;
// }

function test()
{
  echo 'test';
}
