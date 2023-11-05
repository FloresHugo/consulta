<?php
  if(session_status() !== PHP_SESSION_ACTIVE) session_start();

  // print_r($_SESSION);
  if(!isset($_SESSION['paths'])){
    define('path',$_SERVER['DOCUMENT_ROOT'] . '/');
    // define('path',$_SERVER['DOCUMENT_ROOT'] . '/');


    $_SESSION['paths']= path.'includes/path.php';
  }
  require_once($_SESSION['paths']);
  if(!isset($_SESSION['user_admin'])){
    header("location:". $routs['login']);
  }


  // echo path;
  // require_once(path.'includes/path.php');
  require_once($_SESSION['paths']);
  require_once($enviroment['db']);
  require_once($enviroment['function']);
  require_once($enviroment['header-admin']);

  $users = getUsers();

  function getUsers(){
    $sql = "SELECT usuario,email, nombre_corto, tipo, id_usuario,nombre_largo FROM usuarios_menu where id_usuario <> 1";

    $result = execQuery($sql);
    if (!$result) return null;

    return $result;
  }

 ?>

 <div class="container">
   <div class="d-flex justify-content-end">
     <button type="button" class="btn btn-outline-info new" >Nuevo usuario</button>
   </div>

   <table class="table table-hover mt-5">
     <thead>
       <th>Nombre de usuario</th>
       <th>Nombre corto</th>
       <th>Tipo</th>
       <th>Acciones</th>
     </thead>
     <tbody>
       <?php foreach ($users as $user): ?>
         <tr>
           <td><?php echo $user['usuario'] ?></td>
           <td><?php echo $user['nombre_corto'] ?></td>
           <td><?php echo $user['tipo'] == 'COM' ?  'Comercializador' : 'Almacenador' ?></td>
           <td class="d-flex justify-content-between">
             <button type="button"class="edit btn btn-sm btn-outline-info"
                data-id="<?php echo $user['id_usuario'] ?>"
                data-user="<?php echo $user['usuario'] ?>"
                data-type="<?php echo $user['tipo'] ?>"
                data-sname="<?php echo $user['nombre_corto'] ?>"
                data-fname="<?php echo $user['nombre_largo'] ?>"
             >Editar</button>
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
            <label>Nombre de usuario <small>(Sin espacios)</small> </label>
            <input type="text" name="user_name" id="name" class="form-control">
          </div>
          <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" id="password" class="form-control">
          </div>
          <div class="form-group">
            <label>Nombre completo</label>
            <input type="text" name="nombre_completo" id="nombreF" class="form-control">
          </div>
          <div class="form-group">
            <label>Nombre corto</label>
            <input type="text" name="nombre_corto" id="nombreC" class="form-control">
          </div>
          <div class="form-group">
            <select class="form-control" name="tipo" id="tipo">
              <option value="0">Selecciona un perfil para este usuario</option>
              <option value="ALM">Almacenador</option>
              <option value="COM">Comercializador</option>
            </select>
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
    if(isset($_SESSION['msg'])){
      showMessage($_SESSION['msg']);
    }
?>

  <script type="text/javascript">
        $(document).on('keyup','#name',function(){
          let string = $("#name").val();
          $("#name").val(string.replace(/ /g, ""))
      })
    $('.edit').click(function(){
      let user = $(this).data('user')
      let id = $(this).data('id')
      let type = $(this).data('type')
      let sname = $(this).data('sname')
      let fname = $(this).data('fname')

      $("#form")[0].reset();

      $('#user').val(id);
      $('#name').val(user);
      $('#nombreF').val(fname);
      $('#nombreC').val(sname);
      $('#tipo').val(type);
      $('#action').val('edit');

      $('.modal').modal('show');

    });

    $('.new').click(function(){
      $("#form")[0].reset();
      $('#action').val('save');

      $('.modal').modal('show');
    });
  </script>
