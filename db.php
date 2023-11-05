<?php
  global $conn;

  $conn= mysqli_connect(
        'petrointelligence.com',
        'petroint__web',
        'Patito_123_',
        'petroint_BD_PETRO'
      );

  if (!$conn) {
      die('Connect Error (' . mysqli_connect_errno() . ') -' . mysqli_connect_error());
  }else{


  }
  mysqli_set_charset($conn,'utf8');

  function execQuery($sql){
    global $conn;
    $result= mysqli_query($conn,$sql);
    if (!$result) {
            printf("Errormessage: %s\n", $conn->error);
            echo 'Consulta: '.$sql;
      echo 'error';
      return 0;
      exit;
    }
     // printf((!$result) ? 'error':'');

    //mysqli_close($conn);
    // mysqli_next_result($conn);
    return $result;
  }
?>
