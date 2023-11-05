<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['paths'])) {
    define('path', $_SERVER['DOCUMENT_ROOT'] . '/');
    $_SESSION['paths'] = path . 'includes/path.php';
}

require_once($_SESSION['paths']);

if (isset($_SESSION['user_admin'])) {
    header('location:' . $routs['admin']);
}

require_once($enviroment['function']);
require_once($enviroment['sesion']);


// unset($_SESSION['msg']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes" />
    <meta name="description" content="" />
    <meta name="author" content="HugoFlores" />
    <title>Login</title>
    <link href="<?php assets('css/styles-ap.css'); ?>" rel="stylesheet" />
    <style media="screen">
        .body {
            background-color: #ebebeb !important;
            background-image: linear-gradient(rgba(255, 255, 255, 0.5),
                    rgba(105, 175, 210, 0.8)), url(<?php assets('images/logo.png') ?>);
            background-position: bottom;
            background-repeat: no-repeat;
            background-size: contain;

        }

        .c-brand {
            width: 9rem;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="container text-center">
                                <div class="" id="alerta"></div>
                            </div>
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <div class="d-flex justify-content-center">
                                        <picture>
                                            <img src="https://petrointelligence.com/logo.png" alt="Services" class="img-fluid c-brand">
                                            <!-- <h5>Consultas</h5> -->
                                        </picture>
                                    </div>
                                    <h3 class="text-center font-weight-light my-4">Iniciar sesión</h3>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="sesion.php" id="login">
                                        <div class="form-group">
                                            <label class="small mb-1" for="inputUser">Usuario</label>
                                            <input class="form-control py-4" id="inputUser" type="text" name="user" placeholder="Ingresa el usuario" required />
                                        </div>
                                        <div class="form-group">
                                            <label class="small mb-1" for="inputPassword">Contraseña</label>
                                            <input class="form-control py-4" id="inputPassword" name='ps' type="password" placeholder="Ingresa la contraseña" required />
                                        </div>
                                        <div class="form-group d-flex align-items-center justify-content-end mt-4 mb-0">

                                            <!-- <a class="small" href="password.html">Forgot Password?</a> -->
                                            <button type="submit" class="btn btn-outline-dark" name="button">Login</button>

                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center">
                                    <div class="small">
                                        <p> </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div id="">
            <footer class="py-4  mt-auto">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-center small">
                        <div class="" style="color:#fff;"></div>
                        <div>

                        </div>
                    </div>
                </div>
            </footer>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
    
    <script src="<?php assets('js/functions.js'); ?>"></script>

    <?php
    if (isset($_SESSION['msg'])) {
        showMessage($_SESSION['msg']);
    }

    ?>
</body>

</html>


</form>