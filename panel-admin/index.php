<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// print_r($_SESSION);
if (!isset($_SESSION['paths'])) {
  define('path', $_SERVER['DOCUMENT_ROOT'] . '/');
  // define('path',$_SERVER['DOCUMENT_ROOT'] . '/');


  $_SESSION['paths'] = path . 'includes/path.php';
}
require_once($_SESSION['paths']);
// if (!isset($_SESSION['user_admin'])) {
//   header("location:" . $routs['login-admin']);
// }


// echo path;
// require_once(path.'includes/path.php');
require_once($_SESSION['paths']);
require_once($enviroment['db']);
require_once($enviroment['function']);
require_once($enviroment['header-admin']);

$users = getUsers();

function getUsers()
{
  $sql = "SELECT COUNT(1) as total, tuc.id,email, name, permisos FROM TBL_USER_CONSULTAS tuc 
LEFT join TBL_CONSULTAS_USUARIOS_PERMISOS tcup on tuc.id = tcup.`user` 
GROUP BY (tuc.id)";

  $result = execQuery($sql);
  if (!$result) return null;

  return $result;
}

?>

<div class="container">
  <div class="d-flex justify-content-end">
    <button type="button" class="btn btn-outline-info new">Nuevo usuario</button>
  </div>

  <table class="table table-hover mt-5">
    <thead>
      <th>Nombre de usuario</th>
      <th>Permisos registrados</th>
      <th>Acciones</th>
    </thead>
    <tbody>
      <?php foreach ($users as $user) : ?>
        <tr>
          <td><?php echo $user['name'] ?></td>
          <td><?php echo "{$user['total']} / {$user['permisos']}" ?> </td>
          <td class="d-flex justify-content-between">
            <button type="button" class="edit btn btn-sm btn-outline-info" data-id="<?php echo $user['id'] ?>" data-user="<?php echo $user['name'] ?>" data-email="<?php echo $user['email'] ?>" data-allow="<?php echo $user['permisos'] ?>">Editar</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

</div>

<div class="modal fade" id="usersModal" tabindex="-1" role="dialog" aria-labelledby="usersodalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Registra un nuevo usuario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form class="" id="form" action="actions" method="post">
          <div class="form-group">
            <label>Nombre de usuario </label>
            <input type="text" name="user_name" id="name" class="form-control">
          </div>
          <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" id="password" class="form-control">
          </div>
          <div class="form-group">
            <label>Cantidad de permisos permitios</label>
            <input type="number" name="allowed" id="number" class="form-control" value="150">
          </div>
          <input type="hidden" id="action" name="action" value="save">
          <input type="hidden" id="user" name="user" value="">
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
      </form>
    </div>
  </div>
</div>

<?php
require_once($enviroment['footer-admin']);
if (isset($_SESSION['msg'])) {
  showMessage($_SESSION['msg']);
}
?>

<script type="text/javascript">
  $('.edit').click(function() {
    let user = $(this).data('user')
    let id = $(this).data('id')
    let type = $(this).data('type')
    let sname = $(this).data('sname')
    let fname = $(this).data('fname')
    let number = $(this).data('allow')

    $("#form")[0].reset();

    $('#user').val(id);
    $('#name').val(user);
    $('#number').val(number);
    $('#action').val('edit');

    $('.modal').modal('show');

  });

  $('.new').click(function() {
    $("#form")[0].reset();
    $('#action').val('save');

    $('.modal').modal('show');
  });
</script>