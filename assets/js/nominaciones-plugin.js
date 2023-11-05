tinymce.PluginManager.add('HF-Plugin', function(editor, url) {
  var openDialog = function () {
    return editor.windowManager.open({
      title: 'Nominaciones',
      body: {
        type: 'panel',
        items: [
          {
            type: 'input',
            name: 'title',
            label: 'Titulo. (Campo oblicatorio)'
          },
          {
            type: 'input',
            name: 'month',
            label: 'Mes a insertar. (Solo ingresa numeros, ej, 01, 02, 03...)'
          },
          {
            type :'input',
            name :'year',
            label: 'Año. (Ej, 2023)'
          }
        ]
      },
      buttons: [
        {
          type: 'cancel',
          text: 'Close'
        },
        {
          type: 'submit',
          text: 'Save',
          primary: true
        }
      ],
      onSubmit: function (api) {
        var data = api.getData();
        editor.insertContent(`<a href="#" class="nominacion-link" data-href ='https://nfeboletinelectronico.com/nominaciones?month=${data.month}&year=${data.year}'">${data.title}</a>`);
        api.close();
      }
    });
  };
  editor.ui.registry.addButton('HF-Plugin', {
    text: 'Agregar nominación',
    onAction: function () {
      openDialog();
    }
  });


});
