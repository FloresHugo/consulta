var url = window.location
var ori = `${url.origin}/panel-admin/`
if(ori !== url.href){
  $('#back').show();
}
