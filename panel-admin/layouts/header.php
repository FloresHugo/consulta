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
  header("location:" . $routs['login-admin']);
  exit;
}

require_once($_SESSION['paths']);
?>
<!DOCTYPE html>
<html lang="es" dir="ltr">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta charset="utf-8">
  <title></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
  <!-- <link rel="stylesheet" href="/assets/css/bootstrap.min.css"> -->
  <link rel="stylesheet" href="/assets/css/custom-admin.css">
  <link rel="icon" href="/assets/images/favicon.svg" sizes="any" type="image/svg+xml">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js" crossorigin="anonymous"></script>
  <style>
    .header {
      background: #fff;
      top: 0;
      width: 100%;
      -webkit-box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 2px 6px 2px rgba(60, 64, 67, .15);
      box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 2px 6px 2px rgba(60, 64, 67, .15);
      height: auto;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light header mb-5">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">SCPI | Panel</a>
      <div class="d-flex align-items-center justify-content-between ">
        <a href="<?php route('admin') ?>" class="mx-3 nav-link text-dark">Administración</a>
        <ul class="navbar-nav ml-5">

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-dark" id="userDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
              <a class="dropdown-item" href="<?php route('salir') ?>">Cerrar sesión</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="container-fluid my-2 " id="back" style="display:none">
    <a href="javascript:history.back(-1);" class="btn btn-outline-dark">
      <i class="fa fa-angle-left" aria-hidden="true"></i>
      Regresar
    </a>
  </div>
  <div id="alerta"></div>