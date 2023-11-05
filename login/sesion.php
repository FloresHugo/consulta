<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
  if(session_status() !== PHP_SESSION_ACTIVE) session_start();

  // print_r($_SESSION); exit;
  // session_destroy(); exit;
  if(!isset($_SESSION['paths'])){
    // define('path',$_SERVER['DOCUMENT_ROOT'] . '/aemsa/');
    define('path',$_SERVER['DOCUMENT_ROOT'] . '/');
    $_SESSION['paths']= path.'includes/path.php';
  }
  // echo $_SESSION['paths'];
  require_once($_SESSION['paths']);
  require_once($enviroment['function']);
  require_once($enviroment['db']);
  // global $routs;

  $login = false;
  if($_SERVER['REQUEST_METHOD'] == 'POST'){

    login();
  }

  if(isset($_GET['q'])){
    login();
    //logout();
  }
  if(isset($_GET['c'])){
    //login();
    if(session_status() !== PHP_SESSION_ACTIVE) session_start();
    logout();
  }
//
  function usuarioLogeado(){

      return (isset($_SESSION['user_admin'])) ? true :  false ;
  }

  function login(){
    $uri = $_SERVER["HTTP_REFERER"];
    $isClient = preg_match('/\/clientes\/login/',$uri);
    global $routs;
    // session_start();
    if(usuarioLogeado()){
      // header('location: /aemsa/panel-admin');
      header('location:'. $routs['home']);
    }

    $user=$_POST['user'];
    $pasword=$_POST['ps'];

    //VERIFICAMOS SI EXISTE REGISTRO PREVIO
		$sql="select id,password from TBL_USER_CONSULTAS where name = '{$user}'";

		$registro=execQuery($sql);
    // print_r($registro); exit();

		if ($registro->num_rows>0) {
      foreach ($registro as $r) {

        if(password_verify($pasword, $r['password'])){
          // echo 'Correcto';
          $_SESSION['user']=$r['id'];
        // redirect('panel');
        header('location:' . $routs['home']);

        }else{
          $_SESSION['msg']=array('tipo' => 'ERROR','msg'=> 'Usuario o contraseña incorrectos');
          header('Location:' . getenv('HTTP_REFERER'));
        }

      }

    }else{
      $_SESSION['msg']=array('tipo' => 'ERROR','msg'=> 'Usuario o contraseña incorrectos');
      header('Location:' . getenv('HTTP_REFERER'));
    }

    // print_r($_SESSION);
  }

  function logout(){
    unset($_SESSION['user_admin']);
    unset($_SESSION['user_type']);
    session_destroy();
    redirect('../../');
  }

  function reload(){
    echo '
      <script type="text/javascript">
        location.assign("login");
      </script>';
  }
 ?>
