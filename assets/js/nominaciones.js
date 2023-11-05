$('.nominacion-link').click(function(){
  console.log('Click');
  let link = $(this).data('href');
  window.open(link,'Nominaciones','scrollbars=NO,status=no,directories=no,menubar=no,toolbar=no,scrollbars=no,location=no,resizable=no,titlebar=no')

});
