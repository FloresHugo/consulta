<!DOCTYPE html>
<html lang="es" dir="ltr">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta charset="utf-8">
  <title></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
  <!-- <link rel="stylesheet" href="/assets/css/bootstrap.min.css"> -->
  <!-- <link rel="stylesheet" href="/assets/css/custom-admin.css"> -->
  <link rel="icon" href="/assets/images/favicon.svg" sizes="any" type="image/svg+xml">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js" crossorigin="anonymous"></script>
  <style>
    .header {
      position: fixed;
      background: #fff;
      z-index: 700;
      top: 0;
      width: 100%;
      -webkit-box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 2px 6px 2px rgba(60, 64, 67, .15);
      box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 2px 6px 2px rgba(60, 64, 67, .15);
      height: auto;
    }

    .footer {
      background: #fff;
      z-index: 700;
      top: 0;
      width: 100%;
      -webkit-box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 2px 6px 2px rgba(60, 64, 67, .15);
      box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 2px 6px 2px rgba(60, 64, 67, .15);
      height: auto;
      overflow: hidden;
    }

    .logo {
      width: 31px;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light header">
    <div class="container">
      <a class="navbar-brand" href="#">
        <img src="https://petrointelligence.com/images/icono.png" class="img-fluid logo" alt="SERVICES">
      </a>
      <ul class="navbar-nav ml-5">

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-secundary" id="userDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="/login/sesion?c">Cerrar sesión</a>
          </div>
        </li>
      </ul>
    </div>
  </nav>
  <div class="mt-5 pt-4">
    <div class=" my-5" id="back" style="display:none; position:absolute; left:13px">
      <a href="javascript:history.back(-1);" class="btn btn-outline-dark">
        <i class="fa fa-angle-left" aria-hidden="true"></i>
        Regresar
      </a>
    </div>
    <div id="alerta"></div>