<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['paths'])) {
  define('path', $_SERVER['DOCUMENT_ROOT'] . '/');
  $_SESSION['paths'] = path . 'includes/path.php';
}
require_once($_SESSION['paths']);

if (!isset($_SESSION['user'])) {
  header("location:" . $routs['login']);
  exit;
}


require_once($enviroment['db']);
require_once($enviroment['header']);
require_once($enviroment['function']);
require_once('actions/searchActions.php');

$records = getRecords();
$available = getAvailableRecords();


?>
<div class="container" style="padding-bottom:400px;">
  <div class="row">
    <div class="col-12 py-4">
      <button class="btn btn-outline-dark" id="add">
        <div>
          <i class="fa fa-plus"></i>
          Agregar nuevo permiso
        </div>
      </button>
      <small class="text-secondary"> <?php echo $available ?> disponibles</small>
    </div>

  </div>
  <div class="row mt-5">
    <div class="col-12 mt-5 table-responsive-sm">
      <table class="table table-hover table-responsive-sm" id="records">
        <thead>
          <th class="no-sort">Número de permiso</th>
          <th class="no-sort">Alias</th>
          <th class="no-sort">Acción</th>
        </thead>
        <tbody>
          <?php foreach ($records as $record) : ?>
            <tr>
              <td><?php echo $record['permiso'] ?></td>
              <td><?php echo $record['alias'] ?></td>
              <td>
                <a href="consulta/<?php echo $record['id'] ?>" class="btn btn-outline-info">Consultar</a>
                <button class="btn btn-outline-success edit" data-alias="<?php echo $record['alias'] ?>" data-permiso="<?php echo $record['id'] ?>">Editar</button>
                <button class="btn btn-outline-danger delete" data-alias="<?php echo $record['alias'] ?>" data-permiso="<?php echo $record['id'] ?>" data-delete="<?php echo $record['canDelete'] ?>">Eliminar</button>
              </td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal modal-add fade" id="modal-add" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agregar permiso</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form class="" action="actions/search" method="POST">
          <div id="search">
            <div class="form-group d-flex align-content-center">
              <div class="flex-grow-1 m-1">
                <input type="text" name="permiso" id="permiso" placeholder="Permiso" class="form-control">
              </div>
              <div class="d-flex align-content-center flex-wrap">
                <button type="button" id="search-btn" class="btn btn-outline-secondary">
                  <i class="fas fa-search"></i>
                  Buscar
                </button>
                <button class="btn btn-outline-secondary" id="search-btn-loading" type="button" disabled style="display:none;">
                  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  <span class="sr-only">Loading...</span>
                </button>
              </div>
            </div>
            <div id="confirm" style="display: none;">
              <p>
                <strong>Permiso:</strong> <span id="detail-permiso"></span>
              </p>
              <p>
                <strong>Dirección:</strong> <span id="detail-address"></span>
              </p>
            </div>
          </div>
          <div id="alias-panel" style="display: none;">
            <p>Asigna un alias para este permiso</p>
            <input type="text" id="alias" name="alias" class="form-control" placeholder="Alias">
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="close" class="btn btn-outline-danger" data-dismiss="modal">Cerrar</button>
        <button type="button" id="back-btn" class="btn btn-outline-secondary" style="display: none;">Atras</button>
        <button type="button" id="continue-btn" class="btn btn-outline-info" style="display: none;">Continuar</button>
        <button type="submit" id="submit" class="btn btn-primary" style="display:none;">Guardar</button>
      </div>
      </form>
    </div>
  </div>
</div>
<div class="modal modal-add fade" id="modal-edit" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Editar permiso</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form class="" action="actions/search" method="POST">
          <div id="alias-panel">
            <p>Asigna un alias para este permiso</p>
            <input type="text" id="alias-edit" name="alias" class="form-control" placeholder="Alias">
            <input type="hidden" name="type" value='edit'>
            <input type="hidden" id="permiso-edit" name="permiso">
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="close-edit" class="btn btn-outline-danger" data-dismiss="modal">Cerrar</button>
        <button type="submit" id="submit-edit" class="btn btn-primary">Guardar</button>
      </div>
      </form>
    </div>
  </div>
</div>
<div class="modal modal-add fade" id="modal-delete" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Eliminar permiso</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <div class="" id="cant-delete">
          <div id="alias-panel">
            <p>Para poder eliminar este permiso deben transcurrir por lo menos
              <strong>15 días</strong> después de su registro.
            </p>
          </div>
        </div>
        <form class="" id="can-delete" action="actions/search" method="POST">
          <div id="alias-panel">
            <p>Estás a punto de eliminar este permiso, ¿Deseas continuar?</p>
            <input type="hidden" name="type" value='delete'>
            <input type="hidden" name="permiso" id="delete-id">
          </div>

          <div class="modal-footer">
            <button type="button" id="close-edit" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
            <button type="submit" id="submit-delete" class="btn btn-outline-danger">Continuar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php
require_once($enviroment['footer']);
if (isset($_SESSION['msg'])) {
  showMessage($_SESSION['msg']);
}
?>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="js/search.js"></script>