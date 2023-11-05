var contenido="";
function alertaError(msg){
  contenido+='<div id="alertjs" class="alert alert-danger alert-dismissible">';
  contenido+='<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
  contenido+='<h4><i class="icon fa fa-ban"></i><span style="padding-left:0.5em;">Ha ocurrido un error</span> </h4>'+msg;
  contenido+='</div>';
  $('#alerta').html(contenido);
  eliminar_alerta(5000);
}
function alertaCorrecto(msg){
  contenido+='<div id="alertjs" class="alert alert-success alert-dismissible">';
  contenido+='<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
  contenido+='<h4><i class="icon fa fa-check"> </i> <span style="padding-left:0.5em;">Todo salió bien</span></h4>'+msg;
  contenido+='</div>';
  $('#alerta').html(contenido);
  eliminar_alerta(5000);
}
function eliminar_alerta(time){
  $('#alertjs').delay(time).hide(600);
  $("body,html").animate({ // aplicamos la función animate a los tags body y html
      scrollTop: 0 //al colocar el valor 0 a scrollTop me volverá a la parte inicial de la página
    },700);

  //$('#alerta').empty();
  contenido="";
}
