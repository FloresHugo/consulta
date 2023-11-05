$(document).ready(function () {
    $("#records").DataTable({
        order: [],
        language: {
            decimal: "",
            emptyTable: "No hay información",
            info: "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
            infoEmpty: "Mostrando 0 de 0 de 0 Entradas",
            infoFiltered: "(Filtrado de _MAX_ total entradas)",
            infoPostFix: "",
            thousands: ",",
            order: [],
            lengthMenu: "Mostrar _MENU_ Entradas",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "Sin resultados encontrados",
            paginate: {
                first: "Primero",
                last: "Ultimo",
                next: "Siguiente",
                previous: "Anterior",
            },
        },
    });
});
$("#back").hide();

$("#add").click(function () {
    $("#modal-add").modal("show");
});
$(".edit").click(function () {
    $("#modal-edit").modal("show");
    let alias = $(this).data('alias');
    let permiso = $(this).data('permiso');
    $('#alias-edit').val(alias);
    $('#permiso-edit').val(permiso);
});

$("#search-btn").click(function () {
    clearSearch();
    $(this).hide();
    $("#search-btn-loading").show();
    search();
});
$("#continue-btn").click(function () {
    $("#search").hide();
    $("#alias-panel").show();
    $("#continue-btn").hide();
    $("#submit").show();
    $("#back-btn").show();
});

$("#back-btn").click(function () {
    $("#search").show();
    $("#alias-panel").hide();
    $("#continue-btn").show();
    $("#submit").hide();
    $("#back-btn").hide();
});

$("#close").click(function () {
    $("#search").show();

    $("#continue-btn").show();
    $("#submit").hide();
    $("#back-btn").hide();
    $("#confirm").hide();
    $("#continue-btn").hide();
    clearSearch();
});

$(".delete").click(function(){
    let canDelete = $(this).data('delete');
    if(canDelete){
        let permiso = $(this).data('permiso');
        $('#can-delete').show();
        $('#submit-delete').show();
        $('#cant-delete').hide();
        $('#delete-id').val(permiso);
    }else{
        $('#cant-delete').show();
        $('#can-delete').hide();
        $('#submit-delete').hide();
    }
    $('#modal-delete').modal('show');
});

function search() {
    let data = {
        type: "search",
        permiso: $("#permiso").val(),
    };

    $.ajax({
        method: "GET",
        url: `/actions/search`,
        data: data,
        dataType: "JSON",
        success: function (data) {
            $("#search-btn-loading").hide();
            $("#search-btn").show();
            fillDetail(data.data);
        },
        error: function (e) {
            $("#search-btn-loading").hide();
            $("#search-btn").show();
            alertaError("Error inesperado, vuelve a interlo");
        },
    });
}

function fillDetail(data) {
    $("#confirm").show();
    $("#continue-btn").show();
    $("#detail-permiso").text(data.PERMISO);
    $("#detail-address").text(
        `${data.CALLE}, ${data.MUNICIPIO}, ${data.ESTADO}.`
    );
    $("#detail-brand").text(data.MARCA);
}

function clearSearch() {
    $("#detail-permiso").text("");
    $("#detail-address").text("");
    $("#detail-brand").text("");
    $("#alias-panel").hide();
}
