<?php

  function redirect($url, $permanent = false)
  {
      if (headers_sent() === false)
      {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);

      }
      exit();
  }

  function errorMessage($msg){
    echo "
      <script> alertaErrorCorto('{$msg}'); console.log('Mostrando mensaje')</script>
    ";
  }

  function showMessage($msg){
    if($msg['tipo']=='OK'){
      echo "
        <script> alertaCorrecto('{$msg['msg']}'); </script>
      ";
    }elseif ($msg['tipo']=='ERROR') {
      echo "
        <script> alertaError('{$msg['msg']}'); </script>
      ";
    }
    elseif ($msg['tipo']=='_OK_') {
      echo "
        <script> alertaCorrectoS('{$msg['msg']}'); </script>
      ";
    }

    unset($_SESSION['msg']);
  }

  function validateParams($params){
    for ($i=0; $i < count($params); $i++) {
      if(!isset($_REQUEST[$params[$i]]) || empty($_REQUEST[$params[$i]])){
        return false;
        // break;
      }
    }
    return true;
  }
function validateParamsJson($paramsJson,$params)
{
  for ($i = 0; $i < count($params); $i++) {
    if (!isset($paramsJson[$params[$i]]) || empty($paramsJson[$params[$i]])) {
      return false;
    }
  }
  return true;
}

function paramsFromJson(){
  $json = file_get_contents('php://input');
  $paramsJson = json_decode($json, true);
  return $paramsJson;
}
 ?>
