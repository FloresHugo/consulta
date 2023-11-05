$(function () {
  var date = new Date();
  $(".date").datepicker({
    defaultDate: date,
    changeMonth: true,
    changeYear: true,
    closeText: 'Cerrar',
    prevText: '<Ant',
    nextText: 'Sig>',
    currentText: 'Hoy',
    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
    dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb'],
    dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
    weekHeader: 'Sm',
    firstDay: 1,
    showMonthAfterYear: false,
    dateFormat: 'yy-mm-dd',
    maxDate : 0,
    minDate:"-2m"
  });
  var date = new Date();
  date.setDate(date.getDate() );
  $(".date").datepicker("setDate", date);
  // $("#endDate").datepicker("setDate", date);

  
  
});
